[CmdletBinding()]
param(
    [Parameter(Position = 0)]
    [ValidateSet('check', 'start', 'stop', 'status')]
    [string]$Action = 'check',

    [switch]$NoBrowser,
    [switch]$NoPause,
    [switch]$Force,

    [string]$ProjectDir,
    [string]$XamppHome
)

$ErrorActionPreference = 'Continue'

$script:OkCount = 0
$script:WarnCount = 0
$script:FailCount = 0
$script:ExitCode = 0
$script:StartedApache = $false
$script:StartedMysql = $false
$script:ScriptDir = $PSScriptRoot
$script:LogDir = Join-Path $script:ScriptDir 'logs'
$script:RuntimeDir = Join-Path $script:ScriptDir '.runtime'

New-Item -ItemType Directory -Force -Path $script:LogDir, $script:RuntimeDir | Out-Null

# ---------------------------------------------------------------------------
# Affichage
# ---------------------------------------------------------------------------

function Write-Section {
    param([string]$Title)
    Write-Host ""
    Write-Host "=== $Title ===" -ForegroundColor White
}

function Write-Status {
    param(
        [ValidateSet('OK', 'WARN', 'FAIL', 'INFO', 'STEP')]
        [string]$Level,
        [string]$Message
    )

    switch ($Level) {
        'OK'   { Write-Host "[OK]        $Message" -ForegroundColor Green }
        'WARN' { Write-Host "[ATTENTION] $Message" -ForegroundColor Yellow }
        'FAIL' {
            Write-Host "[MANQUANT]  $Message" -ForegroundColor Red
            $script:ExitCode = 1
        }
        'INFO' { Write-Host "[INFO]      $Message" -ForegroundColor Cyan }
        'STEP' { Write-Host "-> $Message" -ForegroundColor White }
    }
}

function Write-Result {
    param([string]$Level, [string]$Message)
    Write-Status -Level $Level -Message $Message
    switch ($Level) {
        'OK'   { $script:OkCount++ }
        'WARN' { $script:WarnCount++ }
        'FAIL' { $script:FailCount++ }
    }
}

function Write-CheckSummary {
    Write-Host ""
    Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
    $color = if ($script:FailCount -gt 0) { 'Red' } elseif ($script:WarnCount -gt 0) { 'Yellow' } else { 'Green' }
    Write-Host ("Resultat : {0} OK, {1} avertissement(s), {2} manquant(s)" -f $script:OkCount, $script:WarnCount, $script:FailCount) -ForegroundColor $color
    if ($script:FailCount -gt 0) {
        Write-Host "Corrigez les points [MANQUANT] avant de lancer l'application." -ForegroundColor Red
    } elseif ($script:WarnCount -gt 0) {
        Write-Host "L'application peut fonctionner, mais verifiez les points [ATTENTION]." -ForegroundColor Yellow
    } else {
        Write-Host "Tout est pret. Lancez start_scandale.bat." -ForegroundColor Green
    }
}

function Pause-IfNeeded {
    if (-not $NoPause -and [Environment]::UserInteractive -and $Host.Name -eq 'ConsoleHost') {
        Write-Host ""
        Read-Host "Appuyez sur Entree pour fermer"
    }
}

# ---------------------------------------------------------------------------
# Chemins / configuration
# ---------------------------------------------------------------------------

function Resolve-ProjectDir {
    param([string]$Override)
    if ($Override) {
        if (-not (Test-Path $Override)) { throw "Dossier projet introuvable : $Override" }
        return (Resolve-Path $Override).Path
    }
    return (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
}

function Resolve-XamppHome {
    param([string]$Override, [string]$ProjectRoot)

    $candidates = @()
    if ($Override) { $candidates += $Override }
    if ($env:XAMPP_HOME) { $candidates += $env:XAMPP_HOME }
    $candidates += 'C:\xampp'

    $parent = Split-Path $ProjectRoot -Parent
    if ((Split-Path $parent -Leaf) -ieq 'htdocs') {
        $candidates += (Split-Path $parent -Parent)
    }

    foreach ($candidate in $candidates) {
        if ($candidate -and (Test-Path (Join-Path $candidate 'apache\bin\httpd.exe'))) {
            return (Resolve-Path $candidate).Path
        }
    }
    return 'C:\xampp'
}

function Get-ApacheListenInfo {
    param([string]$HttpdConf)

    $info = [pscustomobject]@{
        Host = '0.0.0.0'
        Port = 80
        Raw  = 'Listen 80'
    }

    if (-not (Test-Path $HttpdConf)) { return $info }

    $line = Get-Content $HttpdConf -ErrorAction SilentlyContinue |
        Where-Object { $_ -match '^\s*Listen\s+' -and $_ -notmatch '^\s*#' } |
        Select-Object -First 1

    if (-not $line) { return $info }

    $info.Raw = $line.Trim()

    if ($info.Raw -match '^\s*Listen\s+\[([^\]]+)\]:(\d+)') {
        $info.Host = $Matches[1]
        $info.Port = [int]$Matches[2]
    } elseif ($info.Raw -match '^\s*Listen\s+([^:]+):(\d+)') {
        $info.Host = $Matches[1]
        $info.Port = [int]$Matches[2]
    } elseif ($info.Raw -match '^\s*Listen\s+(\d+)') {
        $info.Port = [int]$Matches[1]
    }

    return $info
}

function Get-ProjectUrl {
    param([int]$Port)
    $name = [uri]::EscapeDataString($script:ProjectName)
    $portPart = if ($Port -ne 80) { ":$Port" } else { '' }
    return "http://localhost$portPart/$name/"
}

function Get-DbConfig {
    $file = Join-Path $script:ProjectDir 'config\db.php'
    $cfg = [pscustomobject]@{ Host = 'localhost'; Name = 'scandale_bouffe'; User = 'root'; Pass = '' }
    if (-not (Test-Path $file)) { return $cfg }

    $text = Get-Content $file -Raw
    if ($text -match '\$dbHost\s*=\s*[''"]([^''"]*)[''"]') { $cfg.Host = $Matches[1] }
    if ($text -match '\$dbName\s*=\s*[''"]([^''"]*)[''"]') { $cfg.Name = $Matches[1] }
    if ($text -match '\$dbUser\s*=\s*[''"]([^''"]*)[''"]') { $cfg.User = $Matches[1] }
    if ($text -match '\$dbPass\s*=\s*[''"]([^''"]*)[''"]') { $cfg.Pass = $Matches[1] }
    return $cfg
}

function Get-LineValue {
    param([string]$Text, [string]$Key)
    if ($Text -match "(?m)^$([regex]::Escape($Key))=(.*)$") {
        return $Matches[1].Trim()
    }
    return $null
}

function Convert-ToBytes {
    param([string]$Value)
    if ($Value -match '^\s*(\d+)\s*([KMG]?)') {
        $number = [int64]$Matches[1]
        switch ($Matches[2].ToUpperInvariant()) {
            'K' { $number = $number * 1KB }
            'M' { $number = $number * 1MB }
            'G' { $number = $number * 1GB }
        }
        return $number
    }
    return 0
}

# ---------------------------------------------------------------------------
# Processus / ports
# ---------------------------------------------------------------------------

function Test-IsAdmin {
    try {
        $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
        $principal = New-Object Security.Principal.WindowsPrincipal($identity)
        return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    } catch {
        return $false
    }
}

function Test-Port {
    param(
        [string]$ComputerName = '127.0.0.1',
        [int]$Port,
        [int]$TimeoutMs = 800
    )

    $client = New-Object System.Net.Sockets.TcpClient
    try {
        $async = $client.BeginConnect($ComputerName, $Port, $null, $null)
        if (-not $async.AsyncWaitHandle.WaitOne($TimeoutMs, $false)) { return $false }
        $client.EndConnect($async)
        return $true
    } catch {
        return $false
    } finally {
        $client.Close()
    }
}

function Test-RemotePort {
    param(
        [string]$HostName,
        [int]$Port,
        [int]$TimeoutMs = 3000
    )

    try {
        $addresses = [System.Net.Dns]::GetHostAddresses($HostName)
    } catch {
        return $false
    }

    foreach ($address in $addresses) {
        if (Test-Port -ComputerName $address.IPAddressToString -Port $Port -TimeoutMs $TimeoutMs) {
            return $true
        }
    }
    return $false
}

function Wait-Port {
    param([int]$Port, [int]$TimeoutSec = 30)
    $deadline = (Get-Date).AddSeconds($TimeoutSec)
    while ((Get-Date) -lt $deadline) {
        if (Test-Port -Port $Port) { return $true }
        Start-Sleep -Milliseconds 500
    }
    return $false
}

function Wait-PortClosed {
    param([int]$Port, [int]$TimeoutSec = 10)
    $deadline = (Get-Date).AddSeconds($TimeoutSec)
    while ((Get-Date) -lt $deadline) {
        if (-not (Test-Port -Port $Port)) { return $true }
        Start-Sleep -Milliseconds 500
    }
    return $false
}

function Get-PortOwner {
    param([int]$Port)

    try {
        $connection = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction Stop | Select-Object -First 1
        if ($connection) {
            $process = Get-Process -Id $connection.OwningProcess -ErrorAction SilentlyContinue
            $path = $null
            try { $path = $process.Path } catch {}
            return [pscustomobject]@{
                Port    = $Port
                PID     = [int]$connection.OwningProcess
                Name    = if ($process) { $process.ProcessName } else { 'inconnu' }
                Path    = $path
                Address = $connection.LocalAddress
            }
        }
    } catch {
        $line = netstat -ano | Select-String -Pattern ":$Port\s+.*LISTENING" | Select-Object -First 1
        if ($line) {
            $parts = ($line.Line -replace '\s+', ' ').Trim().Split(' ')
            $processId = [int]$parts[-1]
            $process = Get-Process -Id $processId -ErrorAction SilentlyContinue
            $path = $null
            try { $path = $process.Path } catch {}
            return [pscustomobject]@{
                Port    = $Port
                PID     = $processId
                Name    = if ($process) { $process.ProcessName } else { 'inconnu' }
                Path    = $path
                Address = ''
            }
        }
    }

    return $null
}

function Get-XamppProcesses {
    param([string]$SubDir, [string]$Name)
    $base = Join-Path $script:XamppHome $SubDir
    $result = @()
    foreach ($process in (Get-Process -Name $Name -ErrorAction SilentlyContinue)) {
        $path = $null
        try { $path = $process.Path } catch {}
        if (-not $path -or $path.StartsWith($base, [StringComparison]::OrdinalIgnoreCase)) {
            $result += [pscustomobject]@{ Process = $process; Path = $path }
        }
    }
    return $result
}

function Get-LanAddresses {
    $result = @()
    try {
        $configs = Get-NetIPConfiguration -ErrorAction Stop | Where-Object {
            $_.NetAdapter.Status -eq 'Up' -and $_.IPv4Address -and $_.IPv4DefaultGateway
        }
        foreach ($config in $configs) {
            foreach ($ip in $config.IPv4Address) {
                if ($ip.IPAddress -notmatch '^(127\.|169\.254\.)') {
                    $result += [pscustomobject]@{
                        IP        = $ip.IPAddress
                        Interface = $config.InterfaceAlias
                        Gateway   = ($config.IPv4DefaultGateway | Select-Object -First 1).NextHop
                    }
                }
            }
        }
    } catch {}

    if ($result.Count -eq 0) {
        try {
            $ips = Get-NetIPAddress -AddressFamily IPv4 -ErrorAction Stop |
                Where-Object { $_.IPAddress -notmatch '^(127\.|169\.254\.)' }
            foreach ($ip in $ips) {
                $result += [pscustomobject]@{ IP = $ip.IPAddress; Interface = $ip.InterfaceAlias; Gateway = '' }
            }
        } catch {}
    }

    return $result | Select-Object IP, Interface, Gateway -Unique
}

function Get-FirewallRulesForPort {
    param([int]$Port)
    $found = @()
    foreach ($pattern in @('*Apache*', '*XAMPP*', '*httpd*')) {
        try {
            $rules = Get-NetFirewallRule -DisplayName $pattern -ErrorAction Stop |
                Where-Object { $_.Direction -eq 'Inbound' -and $_.Action -eq 'Allow' -and $_.Enabled -eq $true }
        } catch {
            $rules = @()
        }
        foreach ($rule in $rules) {
            try {
                $portFilter = $rule | Get-NetFirewallPortFilter
                if ($portFilter.Protocol -eq 'TCP' -and ($portFilter.LocalPort -eq "$Port" -or $portFilter.LocalPort -eq 'Any')) {
                    $appFilter = $rule | Get-NetFirewallApplicationFilter
                    $found += [pscustomobject]@{
                        Name    = $rule.DisplayName
                        Profile = [string]$rule.Profile
                        Program = $appFilter.Program
                    }
                }
            } catch {}
        }
    }
    return $found
}

function Find-Browser {
    $candidates = @()
    if (${env:ProgramFiles(x86)}) {
        $candidates += [pscustomobject]@{ Name = 'msedge'; Path = (Join-Path ${env:ProgramFiles(x86)} 'Microsoft\Edge\Application\msedge.exe') }
        $candidates += [pscustomobject]@{ Name = 'chrome'; Path = (Join-Path ${env:ProgramFiles(x86)} 'Google\Chrome\Application\chrome.exe') }
    }
    if ($env:ProgramFiles) {
        $candidates += [pscustomobject]@{ Name = 'msedge'; Path = (Join-Path $env:ProgramFiles 'Microsoft\Edge\Application\msedge.exe') }
        $candidates += [pscustomobject]@{ Name = 'chrome'; Path = (Join-Path $env:ProgramFiles 'Google\Chrome\Application\chrome.exe') }
    }
    if ($env:LOCALAPPDATA) {
        $candidates += [pscustomobject]@{ Name = 'msedge'; Path = (Join-Path $env:LOCALAPPDATA 'Microsoft\Edge\Application\msedge.exe') }
        $candidates += [pscustomobject]@{ Name = 'chrome'; Path = (Join-Path $env:LOCALAPPDATA 'Google\Chrome\Application\chrome.exe') }
    }

    foreach ($candidate in $candidates) {
        if ($candidate.Path -and (Test-Path $candidate.Path)) { return $candidate }
    }
    return $null
}

function Start-HiddenProcess {
    param(
        [string]$FilePath,
        [string[]]$ArgumentList,
        [string]$WorkingDirectory,
        [string]$OutLog,
        [string]$ErrLog
    )

    $params = @{
        FilePath               = $FilePath
        WorkingDirectory       = $WorkingDirectory
        WindowStyle            = 'Hidden'
        PassThru               = $true
        RedirectStandardOutput = $OutLog
        RedirectStandardError  = $ErrLog
    }
    if ($ArgumentList -and $ArgumentList.Count -gt 0) {
        $params['ArgumentList'] = $ArgumentList
    }
    return Start-Process @params
}

function Invoke-PhpSnippet {
    param([string]$Code, [int]$TimeoutSec = 20)

    $php = Join-Path $script:XamppHome 'php\php.exe'
    if (-not (Test-Path $php)) {
        return [pscustomobject]@{ Ok = $false; Output = 'php.exe introuvable'; Code = 1 }
    }

    $tmp = Join-Path $env:TEMP ("scandale_{0}.php" -f ([guid]::NewGuid().ToString('N')))
    $utf8 = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($tmp, $Code, $utf8)

    try {
        $output = & $php $tmp 2>&1
        $code = $LASTEXITCODE
        return [pscustomobject]@{
            Ok     = ($code -eq 0)
            Output = (($output | Out-String).Trim())
            Code   = $code
        }
    } catch {
        return [pscustomobject]@{ Ok = $false; Output = $_.Exception.Message; Code = 1 }
    } finally {
        Remove-Item $tmp -Force -ErrorAction SilentlyContinue
    }
}

# ---------------------------------------------------------------------------
# Verification des prerequis
# ---------------------------------------------------------------------------

function Invoke-Check {
    Write-Section "Verification des prerequis - Scandale Bouffe"
    Write-Status INFO "Projet : $script:ProjectDir"
    Write-Status INFO "XAMPP  : $script:XamppHome"
    Write-Status INFO "URL    : $script:LocalUrl"
    Write-Status INFO "Date   : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"

    $phpExe = Join-Path $script:XamppHome 'php\php.exe'
    $apacheExe = Join-Path $script:XamppHome 'apache\bin\httpd.exe'
    $mysqlExe = Join-Path $script:XamppHome 'mysql\bin\mysqld.exe'
    $httpdConf = Join-Path $script:XamppHome 'apache\conf\httpd.conf'
    $phpIni = Join-Path $script:XamppHome 'php\php.ini'

    # -----------------------------------------------------------------------
    Write-Section "Systeme"
    Write-Status INFO "Windows : $([Environment]::OSVersion.VersionString)"
    Write-Status INFO "PowerShell : $($PSVersionTable.PSVersion)"
    if (Test-IsAdmin) {
        Write-Status INFO "Session : administrateur."
    } else {
        Write-Status INFO "Session : utilisateur standard (suffisant pour lancer Apache et MySQL en direct)."
    }

    # -----------------------------------------------------------------------
    Write-Section "XAMPP"
    if (Test-Path $script:XamppHome) {
        Write-Result OK "Dossier XAMPP : $script:XamppHome"
    } else {
        Write-Result FAIL "Dossier XAMPP introuvable : $script:XamppHome"
    }

    if (Test-Path $apacheExe) {
        Write-Result OK "Apache : $apacheExe"
    } else {
        Write-Result FAIL "Apache introuvable : $apacheExe"
    }

    if (Test-Path $phpExe) {
        Write-Result OK "PHP : $phpExe"
    } else {
        Write-Result FAIL "PHP introuvable : $phpExe"
    }

    if (Test-Path $mysqlExe) {
        Write-Result OK "MySQL : $mysqlExe"
    } else {
        Write-Result FAIL "MySQL introuvable : $mysqlExe"
    }

    if (Test-Path (Join-Path $script:XamppHome 'phpMyAdmin')) {
        Write-Result OK "phpMyAdmin present."
    } else {
        Write-Result WARN "phpMyAdmin absent (non indispensable au fonctionnement)."
    }

    if (Test-Path (Join-Path $script:XamppHome 'xampp-control.exe')) {
        Write-Result OK "XAMPP Control Panel present."
    } else {
        Write-Result WARN "XAMPP Control Panel introuvable."
    }

    if (Test-Path $phpExe) {
        $phpVersion = ((& $phpExe -d display_errors=0 -r "echo PHP_VERSION;" 2>$null) | Out-String).Trim()
        if ($phpVersion) {
            Write-Status INFO "Version PHP : $phpVersion"
            try {
                if ([version]($phpVersion -replace '[^0-9.].*$', '') -ge [version]'8.0') {
                    Write-Result OK "PHP 8.0 ou superieur : OK."
                } else {
                    Write-Result FAIL "PHP 8.0 ou superieur requis (version trouvee : $phpVersion)."
                }
            } catch {
                Write-Result WARN "Impossible de comparer la version PHP : $phpVersion."
            }
        }
    }

    if (Test-Path $apacheExe) {
        $apacheVersion = (& $apacheExe -v 2>&1 | Select-String -Pattern 'Apache/\d' | Select-Object -First 1)
        if ($apacheVersion) { Write-Status INFO $apacheVersion.Line.Trim() }
    }

    if (Test-Path $mysqlExe) {
        $mysqlVersion = (& $mysqlExe --version 2>&1 | Select-Object -First 1)
        if ($mysqlVersion) { Write-Status INFO $mysqlVersion.Trim() }
    }

    # -----------------------------------------------------------------------
    Write-Section "Projet"
    $required = @(
        @{ Path = (Join-Path $script:ProjectDir 'index.php'); Label = 'index.php'; Must = $true },
        @{ Path = (Join-Path $script:ProjectDir 'public\index.php'); Label = 'public/index.php'; Must = $true },
        @{ Path = (Join-Path $script:ProjectDir 'config\db.php'); Label = 'config/db.php'; Must = $true },
        @{ Path = (Join-Path $script:ProjectDir '.htaccess'); Label = '.htaccess'; Must = $true },
        @{ Path = (Join-Path $script:ProjectDir 'css\tailwind.css'); Label = 'css/tailwind.css'; Must = $true },
        @{ Path = (Join-Path $script:ProjectDir 'css\styles.css'); Label = 'css/styles.css'; Must = $true },
        @{ Path = (Join-Path $script:ProjectDir 'uploads'); Label = 'dossier uploads'; Must = $true },
        @{ Path = (Join-Path $script:ProjectDir 'uploads\.htaccess'); Label = 'uploads/.htaccess'; Must = $false }
    )

    foreach ($item in $required) {
        if (Test-Path $item.Path) {
            Write-Result OK "$($item.Label) present."
        } elseif ($item.Must) {
            Write-Result FAIL "$($item.Label) manquant : $($item.Path)"
        } else {
            Write-Result WARN "$($item.Label) manquant : $($item.Path)"
        }
    }

    $htaccess = Join-Path $script:ProjectDir '.htaccess'
    if (Test-Path $htaccess) {
        $content = Get-Content $htaccess -Raw
        if ($content -match 'RewriteEngine\s+On' -and $content -match 'RewriteRule') {
            Write-Result OK "Le .htaccess contient les regles de routage."
        } else {
            Write-Result FAIL "Le .htaccess ne contient pas les regles de routage attendues."
        }
    }

    $uploads = Join-Path $script:ProjectDir 'uploads'
    if (Test-Path $uploads) {
        try {
            $testFile = Join-Path $uploads ('.write_test_' + [guid]::NewGuid().ToString('N'))
            [System.IO.File]::WriteAllText($testFile, 'test')
            Remove-Item $testFile -Force -ErrorAction Stop
            Write-Result OK "Dossier uploads accessible en ecriture."
        } catch {
            Write-Result FAIL "Dossier uploads non accessible en ecriture : $($_.Exception.Message)"
        }
    }

    $dbDir = Join-Path $script:ProjectDir 'database'
    if (Test-Path $dbDir) {
        $dump = Get-ChildItem $dbDir -Filter '*.sql' -File -ErrorAction SilentlyContinue |
            Sort-Object LastWriteTime -Descending | Select-Object -First 1
        if ($dump) {
            Write-Result OK "Dernier dump SQL : $($dump.Name) ($($dump.LastWriteTime.ToString('yyyy-MM-dd HH:mm')))."
        } else {
            Write-Result WARN "Aucun fichier .sql dans database/."
        }
    } else {
        Write-Result WARN "Dossier database/ absent."
    }

    if (Test-Path (Join-Path $script:ProjectDir 'database.sql')) {
        Write-Status INFO "database.sql (ancien schema) present : ne pas l'importer."
    }

    if (Test-Path (Join-Path $script:ProjectDir '.git')) {
        Write-Result WARN "Dossier .git present : il peut etre accessible via Apache. A bloquer ou retirer."
    }

    # -----------------------------------------------------------------------
    Write-Section "PHP"
    if (-not (Test-Path $phpExe)) {
        Write-Result FAIL "php.exe introuvable : impossible de verifier PHP."
    } else {
        $iniOutput = (& $phpExe --ini 2>&1 | Out-String)
        $loadedIni = ($iniOutput -split "`r?`n" | Where-Object { $_ -match 'Loaded Configuration File' } | Select-Object -First 1)
        if ($loadedIni) {
            Write-Status INFO $loadedIni.Trim()
        } else {
            Write-Result WARN "Aucun php.ini charge."
        }

        $extensionsJson = ((& $phpExe -d display_errors=0 -r "echo json_encode(['pdo_mysql'=>extension_loaded('pdo_mysql'),'fileinfo'=>extension_loaded('fileinfo'),'mbstring'=>extension_loaded('mbstring'),'mysqli'=>extension_loaded('mysqli'),'openssl'=>extension_loaded('openssl'),'curl'=>extension_loaded('curl'),'gd'=>extension_loaded('gd')]);" 2>$null) | Out-String).Trim()
        try {
            $extensions = $extensionsJson | ConvertFrom-Json
        } catch {
            $extensions = $null
        }

        $extensionChecks = @(
            @{ Key = 'pdo_mysql'; Label = 'PDO MySQL'; Required = $true },
            @{ Key = 'fileinfo'; Label = 'Fileinfo (upload images)'; Required = $true },
            @{ Key = 'mbstring'; Label = 'Mbstring (POS)'; Required = $true },
            @{ Key = 'mysqli'; Label = 'MySQLi'; Required = $false },
            @{ Key = 'openssl'; Label = 'OpenSSL'; Required = $false },
            @{ Key = 'curl'; Label = 'cURL'; Required = $false },
            @{ Key = 'gd'; Label = 'GD'; Required = $false }
        )

        if ($extensions) {
            foreach ($check in $extensionChecks) {
                $value = $extensions.($check.Key)
                if ($value -eq $true) {
                    Write-Result OK "$($check.Label) : active."
                } elseif ($check.Required) {
                    Write-Result FAIL "$($check.Label) : extension manquante."
                } else {
                    Write-Status INFO "$($check.Label) : non active (non indispensable)."
                }
            }
        } else {
            Write-Result FAIL "Impossible de lire les extensions PHP."
        }

        $settingsJson = ((& $phpExe -d error_reporting=0 -r "echo json_encode(['timezone'=>date_default_timezone_get(),'display_errors'=>ini_get('display_errors'),'upload_max_filesize'=>ini_get('upload_max_filesize'),'post_max_size'=>ini_get('post_max_size'),'session_gc_maxlifetime'=>ini_get('session.gc_maxlifetime'),'memory_limit'=>ini_get('memory_limit')]);" 2>$null) | Out-String).Trim()
        try {
            $settings = $settingsJson | ConvertFrom-Json
        } catch {
            $settings = $null
        }

        if ($settings) {
            Write-Status INFO "Fuseau PHP : $($settings.timezone)"
            if ($settings.timezone -eq 'Indian/Antananarivo') {
                Write-Result OK "Fuseau horaire PHP attendu : OK."
            } else {
                Write-Result WARN "Fuseau PHP '$($settings.timezone)' : attendu 'Indian/Antananarivo'."
            }

            if ($settings.display_errors -eq '1' -or $settings.display_errors -eq 'On') {
                Write-Result WARN "display_errors est actif : a passer a Off apres les tests."
            } else {
                Write-Result OK "display_errors est desactive."
            }

            $uploadBytes = Convert-ToBytes $settings.upload_max_filesize
            $postBytes = Convert-ToBytes $settings.post_max_size
            if ($uploadBytes -ge 2MB -and $postBytes -ge 2MB) {
                Write-Result OK "Limites upload suffisantes ($($settings.upload_max_filesize) / $($settings.post_max_size))."
            } else {
                Write-Result WARN "Limites upload trop basses ($($settings.upload_max_filesize) / $($settings.post_max_size)) : 2 Mo minimum."
            }

            $gcLifetime = 0
            [void][int]::TryParse([string]$settings.session_gc_maxlifetime, [ref]$gcLifetime)
            if ($gcLifetime -ge 3600) {
                Write-Result OK "Duree de session : $gcLifetime secondes."
            } else {
                Write-Result WARN "Duree de session faible ($gcLifetime secondes) : risque de deconnexion rapide."
            }

            Write-Status INFO "memory_limit : $($settings.memory_limit)"
        }

        if (Test-Path $phpIni) {
            Write-Status INFO "php.ini : $phpIni"
        }
    }

    # -----------------------------------------------------------------------
    Write-Section "Apache"
    if (Test-Path $apacheExe) {
        if (Test-Path $httpdConf) {
            $listen = Get-ApacheListenInfo -HttpdConf $httpdConf
            Write-Status INFO "Configuration Apache : $($listen.Raw)"
            if ($listen.Host -in @('127.0.0.1', 'localhost', '::1')) {
                Write-Result WARN "Apache n'ecoute que localement ($($listen.Host):$($listen.Port)) : le telephone ne pourra pas se connecter."
            } else {
                Write-Result OK "Apache ecoute sur toutes les interfaces (port $($listen.Port))."
            }

            $confText = Get-Content $httpdConf -Raw
            if ($confText -match '(?m)^\s*LoadModule\s+rewrite_module') {
                Write-Result OK "mod_rewrite active dans httpd.conf."
            } else {
                Write-Result FAIL "mod_rewrite non active dans httpd.conf."
            }

            $htdocsBlock = [regex]::Match($confText, '<Directory\s+"[^"]*htdocs"[^>]*>(.*?)</Directory>', [System.Text.RegularExpressions.RegexOptions]::Singleline)
            if ($htdocsBlock.Success -and $htdocsBlock.Groups[1].Value -match 'AllowOverride\s+All') {
                Write-Result OK "AllowOverride All actif pour htdocs."
            } else {
                Write-Result FAIL "AllowOverride All absent pour htdocs : .htaccess ignore."
            }

            $docRoot = ($confText -split "`r?`n" | Where-Object { $_ -match '^\s*DocumentRoot\s+' } | Select-Object -First 1)
            if ($docRoot) { Write-Status INFO $docRoot.Trim() }
        } else {
            Write-Result FAIL "httpd.conf introuvable : $httpdConf"
        }

        $syntax = (& $apacheExe -t 2>&1 | Out-String).Trim()
        if ($LASTEXITCODE -eq 0) {
            Write-Result OK "Syntaxe Apache valide."
        } else {
            Write-Result FAIL "Syntaxe Apache invalide : $syntax"
        }

        $modules = (& $apacheExe -M 2>&1 | Out-String)
        if ($modules -match 'rewrite_module') {
            Write-Result OK "Module rewrite_module charge."
        } else {
            Write-Result FAIL "Module rewrite_module non charge."
        }
    }

    $apacheOwner = Get-PortOwner -Port $script:ApachePort
    if ($apacheOwner) {
        if ($apacheOwner.Name -eq 'httpd') {
            Write-Result OK "Apache deja demarre sur le port $($script:ApachePort) (PID $($apacheOwner.PID))."
        } else {
            Write-Result FAIL "Port $($script:ApachePort) occupe par $($apacheOwner.Name) (PID $($apacheOwner.PID))."
        }
    } else {
        Write-Status INFO "Apache n'est pas demarre (port $($script:ApachePort) libre)."
    }

    # -----------------------------------------------------------------------
    Write-Section "MySQL / Base de donnees"
    $mysqlOwner = Get-PortOwner -Port 3306
    if ($mysqlOwner) {
        if ($mysqlOwner.Name -eq 'mysqld') {
            Write-Result OK "MySQL deja demarre sur le port 3306 (PID $($mysqlOwner.PID))."
        } else {
            Write-Result FAIL "Port 3306 occupe par $($mysqlOwner.Name) (PID $($mysqlOwner.PID))."
        }
    } else {
        Write-Result WARN "MySQL n'est pas demarre. Lancez start_scandale.bat puis relancez la verification."
    }

    $services = Get-Service -ErrorAction SilentlyContinue | Where-Object { $_.Name -match 'mysql|maria' }
    if ($services) {
        foreach ($service in $services) {
            Write-Status INFO "Service $($service.Name) : $($service.Status)"
        }
    }

    $dbConfig = Get-DbConfig
    Write-Status INFO "Base configuree : $($dbConfig.Name) | utilisateur : $($dbConfig.User) | hote : $($dbConfig.Host)"

    if ($mysqlOwner -and $mysqlOwner.Name -eq 'mysqld') {
        $projectPath = $script:ProjectDir -replace '\\', '/'
        $projectPath = $projectPath -replace "'", "\'"
        $dbCode = @"
<?php
require '$projectPath/config/db.php';
echo 'DB_OK' . "\n";
try {
    echo 'TABLES=' . count(fetchAllAssoc('SHOW TABLES')) . "\n";
    `$users = fetchAssoc('SELECT COUNT(*) AS c FROM users');
    echo 'USERS=' . (isset(`$users['c']) ? `$users['c'] : 0) . "\n";
    `$admins = fetchAssoc("SELECT COUNT(*) AS c FROM roles_users ru JOIN roles r ON r.id = ru.role_id WHERE r.name = 'administrateur'");
    echo 'ADMINS=' . (isset(`$admins['c']) ? `$admins['c'] : 0) . "\n";
    echo 'PHP_NOW=' . date('Y-m-d H:i:s') . "\n";
    `$now = fetchAssoc('SELECT NOW() AS n');
    echo 'DB_NOW=' . (isset(`$now['n']) ? `$now['n'] : '') . "\n";
} catch (Throwable `$e) {
    echo 'DB_ERROR=' . `$e->getMessage() . "\n";
}
"@
        $dbResult = Invoke-PhpSnippet -Code $dbCode -TimeoutSec 20
        $out = $dbResult.Output

        if ($out -match 'DB_OK') {
            Write-Result OK "Connexion PDO a la base OK."
        } else {
            $lastLine = ($out -split "`r?`n" | Where-Object { $_ -ne '' } | Select-Object -Last 1)
            Write-Result FAIL "Connexion PDO impossible : $lastLine"
        }

        $tables = Get-LineValue $out 'TABLES'
        if ($tables -ne $null) {
            $tableCount = 0
            [void][int]::TryParse($tables, [ref]$tableCount)
            if ($tableCount -ge 20) {
                Write-Result OK "Base initialisee : $tableCount tables."
            } elseif ($tableCount -eq 0) {
                Write-Result FAIL "Base vide : importez database/scandale_bouffe_30_09.sql."
            } else {
                Write-Result WARN "Base partielle : $tableCount tables (21 attendues)."
            }
        }

        $users = Get-LineValue $out 'USERS'
        if ($users -ne $null -and [int]$users -gt 0) {
            Write-Result OK "Comptes utilisateurs : $users."
        } elseif ($out -match 'DB_OK') {
            Write-Result WARN "Aucun compte utilisateur."
        }

        $admins = Get-LineValue $out 'ADMINS'
        if ($admins -ne $null -and [int]$admins -gt 0) {
            Write-Result OK "Compte(s) administrateur : $admins."
        } elseif ($out -match 'DB_OK') {
            Write-Result FAIL "Aucun compte administrateur (roles_users)."
        }

        if ($out -match 'DB_OK' -and $out -match 'DB_ERROR') {
            $dbError = ($out -split "`r?`n" | Where-Object { $_ -match '^DB_ERROR=' } | Select-Object -First 1)
            Write-Result FAIL "Erreur pendant la lecture de la base : $dbError"
        }

        $phpNow = Get-LineValue $out 'PHP_NOW'
        $dbNow = Get-LineValue $out 'DB_NOW'
        if ($phpNow -and $dbNow) {
            try {
                $difference = [math]::Abs(((Get-Date $phpNow) - (Get-Date $dbNow)).TotalMinutes)
                if ($difference -le 5) {
                    Write-Result OK "Heures PHP et MySQL coherentes."
                } else {
                    Write-Result WARN ("Ecart PHP/MySQL : {0:N0} min. Verifiez les fuseaux horaires." -f $difference)
                }
            } catch {
                Write-Result WARN "Impossible de comparer les heures PHP/MySQL."
            }
        }
    }

    # -----------------------------------------------------------------------
    Write-Section "Reseau / Telephone"
    try {
        $profiles = Get-NetConnectionProfile -ErrorAction Stop
        foreach ($profile in $profiles) {
            if ([string]$profile.NetworkCategory -eq 'Public') {
                Write-Result WARN "Reseau $($profile.Name) ($($profile.InterfaceAlias)) : Public (le pare-feu peut bloquer)."
            } else {
                Write-Result OK "Reseau $($profile.Name) ($($profile.InterfaceAlias)) : $($profile.NetworkCategory)."
            }
        }
    } catch {
        Write-Result WARN "Impossible de lire le profil reseau."
    }

    $lan = @(Get-LanAddresses)
    if ($lan.Count -gt 0) {
        foreach ($address in $lan) {
            $portPart = if ($script:ApachePort -ne 80) { ":$($script:ApachePort)" } else { '' }
            $url = "http://$($address.IP)$portPart/$([uri]::EscapeDataString($script:ProjectName))/"
            Write-Status OK "URL telephone ($($address.Interface)) : $url"
        }
    } else {
        Write-Result WARN "Aucune adresse IPv4 LAN detectee."
    }

    $firewallRules = @(Get-FirewallRulesForPort -Port $script:ApachePort)
    if ($firewallRules.Count -gt 0) {
        foreach ($rule in $firewallRules) {
            if ($rule.Profile -match 'Any|Private') {
                Write-Result OK "Pare-feu : regle '$($rule.Name)' (profils : $($rule.Profile))."
            } else {
                Write-Result WARN "Pare-feu : regle '$($rule.Name)' ne couvre pas le profil Prive (profils : $($rule.Profile))."
            }
        }
    } else {
        Write-Result WARN "Aucune regle pare-feu Apache trouvee pour le port $($script:ApachePort). Le telephone peut etre bloque."
    }

    # -----------------------------------------------------------------------
    Write-Section "Internet / Assets"
    $onlineChecks = @(
        @{ Host = 'fonts.bunny.net'; Label = 'Police (fonts.bunny.net)' },
        @{ Host = 'cdn.jsdelivr.net'; Label = 'Chart.js (cdn.jsdelivr.net)' },
        @{ Host = 'scandalebouffe.netlify.app'; Label = 'Logo (scandalebouffe.netlify.app)' }
    )
    foreach ($online in $onlineChecks) {
        if (Test-RemotePort -HostName $online.Host -Port 443 -TimeoutMs 4000) {
            Write-Result OK "$($online.Label) accessible."
        } else {
            Write-Result WARN "$($online.Label) inaccessible (mode hors ligne partiel)."
        }
    }

    $browser = Find-Browser
    if ($browser) {
        Write-Result OK "Navigateur dedie : $($browser.Name) ($($browser.Path))."
    } else {
        Write-Result WARN "Edge/Chrome introuvable : start utilisera le navigateur par defaut et ne pourra pas fermer l'onglet automatiquement."
    }

    foreach ($command in @('node', 'npm', 'git')) {
        $found = Get-Command $command -ErrorAction SilentlyContinue
        if ($found) {
            Write-Status INFO "$command : $($found.Source)"
        } else {
            Write-Status INFO "$command : non installe (optionnel)."
        }
    }

    # -----------------------------------------------------------------------
    Write-Section "Securite / Exploitation"
    if (Test-Path $dbDir) {
        $sqlFiles = @(Get-ChildItem $dbDir -Filter '*.sql' -File -ErrorAction SilentlyContinue)
        if ($sqlFiles.Count -gt 0) {
            Write-Result WARN "Les dumps SQL de database/ sont telechargeables via Apache. A bloquer apres import."
        }
    }

    if ($dbConfig.Pass -eq '') {
        Write-Result WARN "Mot de passe MySQL vide dans config/db.php. A durcir si le reseau local est partage."
    }

    $xamppConf = Join-Path $script:XamppHome 'apache\conf\extra\httpd-xampp.conf'
    if (Test-Path $xamppConf) {
        $xamppText = Get-Content $xamppConf -Raw
        if ($xamppText -match 'Alias\s+/phpmyadmin' -and $xamppText -match 'Require\s+local') {
            Write-Result OK "phpMyAdmin limite au poste local."
        } else {
            Write-Status INFO "Verifiez la restriction de phpMyAdmin dans httpd-xampp.conf."
        }
    }

    Write-Status INFO "Sauvegarde conseillee : export SQL + copie du dossier uploads/."

    Write-CheckSummary
}

# ---------------------------------------------------------------------------
# Navigateur
# ---------------------------------------------------------------------------

function Open-ProjectBrowser {
    $browser = Find-Browser
    if (-not $browser) {
        Write-Status WARN "Edge/Chrome introuvable. Ouverture avec le navigateur par defaut."
        try {
            Start-Process $script:LocalUrl
            Write-Status INFO "L'onglet devra etre ferme manuellement avec le navigateur par defaut."
        } catch {
            Write-Status WARN "Impossible d'ouvrir le navigateur : $($_.Exception.Message)"
        }
        return $null
    }

    New-Item -ItemType Directory -Force -Path $script:ProfileDir | Out-Null
    $arguments = @(
        "--user-data-dir=`"$($script:ProfileDir)`"",
        "--no-first-run",
        "--no-default-browser-check",
        "--new-window",
        $script:LocalUrl
    )
    if ($browser.Name -eq 'msedge') {
        $arguments = @("--disable-features=msEdgeFirstRunExperience") + $arguments
    }

    try {
        $process = Start-Process -FilePath $browser.Path -ArgumentList $arguments -PassThru
        Set-Content -Path (Join-Path $script:RuntimeDir 'browser.pid') -Value $process.Id -Encoding ASCII
        Write-Status OK "Navigateur ouvert : $($browser.Name) (PID $($process.Id))."
        return $process
    } catch {
        Write-Status WARN "Impossible d'ouvrir $($browser.Name) : $($_.Exception.Message)"
        try { Start-Process $script:LocalUrl } catch {}
        return $null
    }
}

function Stop-ProjectBrowser {
    $killed = 0
    $pidFile = Join-Path $script:RuntimeDir 'browser.pid'

    if (Test-Path $pidFile) {
        $browserPid = 0
        [void][int]::TryParse(((Get-Content $pidFile -Raw) -replace '\s', ''), [ref]$browserPid)
        if ($browserPid -gt 0) {
            $process = Get-Process -Id $browserPid -ErrorAction SilentlyContinue
            if ($process -and $process.ProcessName -in @('msedge', 'chrome')) {
                & taskkill.exe /PID $browserPid /T /F 2>&1 | Out-Null
                $killed++
            }
        }
    }

    try {
        $profileProcesses = Get-CimInstance Win32_Process -Filter "Name='msedge.exe' OR Name='chrome.exe'" -ErrorAction SilentlyContinue |
            Where-Object { $_.CommandLine -and $_.CommandLine -like "*$($script:ProfileDir)*" }
        foreach ($process in $profileProcesses) {
            & taskkill.exe /PID $process.ProcessId /T /F 2>&1 | Out-Null
            $killed++
        }
    } catch {}

    if ($killed -eq 0) {
        $windows = Get-Process -Name msedge, chrome, firefox -ErrorAction SilentlyContinue |
            Where-Object { $_.MainWindowTitle -match 'Scandale Bouffe' }
        foreach ($window in $windows) {
            if ($window.CloseMainWindow()) { $killed++ }
        }
    }

    if ($killed -gt 0) {
        Write-Status OK "Fenetre navigateur du projet fermee."
    } else {
        Write-Status INFO "Aucune fenetre navigateur dediee trouvee."
    }
    Remove-Item $pidFile -Force -ErrorAction SilentlyContinue
}

# ---------------------------------------------------------------------------
# Arret MySQL
# ---------------------------------------------------------------------------

function Stop-Mysql {
    $port = 3306
    $owner = Get-PortOwner -Port $port
    if (-not $owner) {
        Write-Status INFO "MySQL n'est pas demarre."
        return
    }

    $mysqlBase = Join-Path $script:XamppHome 'mysql'
    if ($owner.Path -and -not $owner.Path.StartsWith($mysqlBase, [StringComparison]::OrdinalIgnoreCase)) {
        Write-Status WARN "Le port 3306 est utilise par un autre programme ($($owner.Name) - $($owner.Path)). MySQL XAMPP n'est pas arrete."
        return
    }

    Write-Status STEP "Arret de MySQL..."
    $projectPath = $script:ProjectDir -replace '\\', '/'
    $projectPath = $projectPath -replace "'", "\'"
    $phpCode = @"
<?php
require '$projectPath/config/db.php';
try {
    `$pdo->exec('SHUTDOWN');
    echo 'SHUTDOWN_OK';
} catch (Throwable `$e) {
    echo 'SHUTDOWN_ERROR=' . `$e->getMessage();
}
"@
    [void](Invoke-PhpSnippet -Code $phpCode -TimeoutSec 15)

    if (Wait-PortClosed -Port $port -TimeoutSec 12) {
        Write-Status OK "MySQL arrete proprement."
        Remove-Item (Join-Path $script:RuntimeDir 'mysql.pid') -Force -ErrorAction SilentlyContinue
        return
    }

    $mysqlAdmin = Join-Path $script:XamppHome 'mysql\bin\mysqladmin.exe'
    if (Test-Path $mysqlAdmin) {
        $db = Get-DbConfig
        $previousPassword = $env:MYSQL_PWD
        if ($null -ne $db.Pass) { $env:MYSQL_PWD = $db.Pass }
        try {
            & $mysqlAdmin --user=$($db.User) shutdown 2>&1 | Out-Null
        } catch {}
        if ($null -eq $previousPassword) {
            Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
        } else {
            $env:MYSQL_PWD = $previousPassword
        }
        if (Wait-PortClosed -Port $port -TimeoutSec 10) {
            Write-Status OK "MySQL arrete (mysqladmin)."
            Remove-Item (Join-Path $script:RuntimeDir 'mysql.pid') -Force -ErrorAction SilentlyContinue
            return
        }
    }

    $services = Get-Service -ErrorAction SilentlyContinue | Where-Object { $_.Name -match 'mysql|maria' -and $_.Status -eq 'Running' }
    foreach ($service in $services) {
        try { Stop-Service -Name $service.Name -Force -ErrorAction Stop } catch {}
    }
    if (Wait-PortClosed -Port $port -TimeoutSec 10) {
        Write-Status OK "MySQL arrete (service)."
        Remove-Item (Join-Path $script:RuntimeDir 'mysql.pid') -Force -ErrorAction SilentlyContinue
        return
    }

    foreach ($item in (Get-XamppProcesses -SubDir 'mysql' -Name 'mysqld')) {
        & taskkill.exe /PID $item.Process.Id /T /F 2>&1 | Out-Null
    }
    if (Wait-PortClosed -Port $port -TimeoutSec 10) {
        Write-Status WARN "MySQL a ete force a s'arreter. Verifiez les donnees."
    } else {
        Write-Status FAIL "Impossible d'arreter MySQL sur le port 3306."
    }
    Remove-Item (Join-Path $script:RuntimeDir 'mysql.pid') -Force -ErrorAction SilentlyContinue
}

# ---------------------------------------------------------------------------
# Arret Apache
# ---------------------------------------------------------------------------

function Stop-Apache {
    $port = $script:ApachePort
    $owner = Get-PortOwner -Port $port
    if (-not $owner) {
        Write-Status INFO "Apache n'est pas demarre."
        return
    }

    $apacheBase = Join-Path $script:XamppHome 'apache'
    if ($owner.Path -and -not $owner.Path.StartsWith($apacheBase, [StringComparison]::OrdinalIgnoreCase)) {
        Write-Status WARN "Le port $port est utilise par un autre programme ($($owner.Name) - $($owner.Path)). Apache XAMPP n'est pas arrete."
        return
    }

    Write-Status STEP "Arret d'Apache..."
    $apacheExe = Join-Path $script:XamppHome 'apache\bin\httpd.exe'
    if (Test-Path $apacheExe) {
        try { & $apacheExe -k shutdown 2>&1 | Out-Null } catch {}
        if (Wait-PortClosed -Port $port -TimeoutSec 10) {
            Write-Status OK "Apache arrete proprement."
            Remove-Item (Join-Path $script:XamppHome 'apache\logs\httpd.pid') -Force -ErrorAction SilentlyContinue
            Remove-Item (Join-Path $script:RuntimeDir 'apache.pid') -Force -ErrorAction SilentlyContinue
            return
        }
    }

    $services = Get-Service -ErrorAction SilentlyContinue | Where-Object { $_.Name -match '^Apache' -and $_.Status -eq 'Running' }
    foreach ($service in $services) {
        try { Stop-Service -Name $service.Name -Force -ErrorAction Stop } catch {}
    }
    if (Wait-PortClosed -Port $port -TimeoutSec 10) {
        Write-Status OK "Apache arrete (service)."
        Remove-Item (Join-Path $script:XamppHome 'apache\logs\httpd.pid') -Force -ErrorAction SilentlyContinue
        Remove-Item (Join-Path $script:RuntimeDir 'apache.pid') -Force -ErrorAction SilentlyContinue
        return
    }

    foreach ($item in (Get-XamppProcesses -SubDir 'apache' -Name 'httpd')) {
        & taskkill.exe /PID $item.Process.Id /T /F 2>&1 | Out-Null
    }
    if (Wait-PortClosed -Port $port -TimeoutSec 10) {
        Write-Status OK "Apache arrete."
    } else {
        Write-Status FAIL "Impossible d'arreter Apache sur le port $port."
    }
    Remove-Item (Join-Path $script:XamppHome 'apache\logs\httpd.pid') -Force -ErrorAction SilentlyContinue
    Remove-Item (Join-Path $script:RuntimeDir 'apache.pid') -Force -ErrorAction SilentlyContinue
}

# ---------------------------------------------------------------------------
# Arret XAMPP
# ---------------------------------------------------------------------------

function Stop-Xampp {
    $processes = Get-Process -ErrorAction SilentlyContinue | Where-Object {
        $_.ProcessName -like 'xampp-*' -or $_.ProcessName -in @('xampp_start', 'xampp_stop', 'xampp-control')
    }
    $count = 0
    foreach ($process in $processes) {
        & taskkill.exe /PID $process.Id /T /F 2>&1 | Out-Null
        $count++
    }
    if ($count -gt 0) {
        Write-Status OK "XAMPP ferme."
    } else {
        Write-Status INFO "XAMPP Control Panel n'etait pas ouvert."
    }
}

# ---------------------------------------------------------------------------
# Demarrage
# ---------------------------------------------------------------------------

function Invoke-Start {
    Write-Section "Demarrage de Scandale Bouffe"
    Write-Status INFO "Projet : $script:ProjectDir"
    Write-Status INFO "URL    : $script:LocalUrl"

    $apacheExe = Join-Path $script:XamppHome 'apache\bin\httpd.exe'
    $mysqlExe = Join-Path $script:XamppHome 'mysql\bin\mysqld.exe'
    $mysqlIni = Join-Path $script:XamppHome 'mysql\bin\my.ini'

    if (-not (Test-Path $apacheExe)) { throw "Apache introuvable : $apacheExe" }
    if (-not (Test-Path $mysqlExe)) { throw "MySQL introuvable : $mysqlExe" }
    if (-not (Test-Path $mysqlIni)) { throw "my.ini introuvable : $mysqlIni" }

    try {
        $apacheOwner = Get-PortOwner -Port $script:ApachePort
        if ($apacheOwner) {
            $apacheBase = Join-Path $script:XamppHome 'apache'
            $isXamppApache = $apacheOwner.Name -eq 'httpd' -and (-not $apacheOwner.Path -or $apacheOwner.Path.StartsWith($apacheBase, [StringComparison]::OrdinalIgnoreCase))
            if ($isXamppApache) {
                Write-Status OK "Apache deja demarre (PID $($apacheOwner.PID))."
                Set-Content -Path (Join-Path $script:RuntimeDir 'apache.pid') -Value $apacheOwner.PID -Encoding ASCII
            } else {
                throw "Le port $($script:ApachePort) est occupe par $($apacheOwner.Name) (PID $($apacheOwner.PID)). Arretez ce programme ou changez le port Apache."
            }
        } else {
            Write-Status STEP "Demarrage d'Apache..."
            $outLog = Join-Path $script:LogDir 'apache-start.out.log'
            $errLog = Join-Path $script:LogDir 'apache-start.err.log'
            Remove-Item $outLog, $errLog -Force -ErrorAction SilentlyContinue
            $apacheProcess = Start-HiddenProcess -FilePath $apacheExe -WorkingDirectory (Join-Path $script:XamppHome 'apache') -OutLog $outLog -ErrLog $errLog
            Set-Content -Path (Join-Path $script:RuntimeDir 'apache.pid') -Value $apacheProcess.Id -Encoding ASCII
            $script:StartedApache = $true
            if (-not (Wait-Port -Port $script:ApachePort -TimeoutSec 30)) {
                $tail = @()
                foreach ($file in @($errLog, (Join-Path $script:XamppHome 'apache\logs\error.log'))) {
                    if (Test-Path $file) { $tail += (Get-Content $file -Tail 15 -ErrorAction SilentlyContinue) }
                }
                throw "Apache n'a pas demarre sur le port $($script:ApachePort).`n$($tail -join "`n")"
            }
            Write-Status OK "Apache demarre (PID $($apacheProcess.Id))."
        }

        $mysqlOwner = Get-PortOwner -Port 3306
        if ($mysqlOwner) {
            $mysqlBase = Join-Path $script:XamppHome 'mysql'
            $isXamppMysql = $mysqlOwner.Name -eq 'mysqld' -and (-not $mysqlOwner.Path -or $mysqlOwner.Path.StartsWith($mysqlBase, [StringComparison]::OrdinalIgnoreCase))
            if ($isXamppMysql) {
                Write-Status OK "MySQL deja demarre (PID $($mysqlOwner.PID))."
                Set-Content -Path (Join-Path $script:RuntimeDir 'mysql.pid') -Value $mysqlOwner.PID -Encoding ASCII
            } else {
                throw "Le port 3306 est occupe par $($mysqlOwner.Name) (PID $($mysqlOwner.PID)). Arretez ce programme."
            }
        } else {
            Write-Status STEP "Demarrage de MySQL..."
            $outLog = Join-Path $script:LogDir 'mysql-start.out.log'
            $errLog = Join-Path $script:LogDir 'mysql-start.err.log'
            Remove-Item $outLog, $errLog -Force -ErrorAction SilentlyContinue
            $argumentList = @("--defaults-file=`"$mysqlIni`"", '--standalone')
            $mysqlProcess = Start-HiddenProcess -FilePath $mysqlExe -ArgumentList $argumentList -WorkingDirectory (Join-Path $script:XamppHome 'mysql') -OutLog $outLog -ErrLog $errLog
            Set-Content -Path (Join-Path $script:RuntimeDir 'mysql.pid') -Value $mysqlProcess.Id -Encoding ASCII
            $script:StartedMysql = $true
            if (-not (Wait-Port -Port 3306 -TimeoutSec 45)) {
                $tail = @()
                foreach ($file in @($errLog, (Join-Path $script:XamppHome 'mysql\data\mysql_error.log'))) {
                    if (Test-Path $file) { $tail += (Get-Content $file -Tail 15 -ErrorAction SilentlyContinue) }
                }
                throw "MySQL n'a pas demarre sur le port 3306.`n$($tail -join "`n")"
            }
            Write-Status OK "MySQL demarre (PID $($mysqlProcess.Id))."
        }
    } catch {
        if ($script:StartedMysql) { Stop-Mysql }
        if ($script:StartedApache) { Stop-Apache }
        throw
    }

    Write-Status STEP "Verification de la base..."
    $projectPath = $script:ProjectDir -replace '\\', '/'
    $projectPath = $projectPath -replace "'", "\'"
    $dbCheck = Invoke-PhpSnippet -Code "<?php require '$projectPath/config/db.php'; echo 'DB_OK';" -TimeoutSec 15
    if ($dbCheck.Output -match 'DB_OK') {
        Write-Status OK "Base de donnees accessible."
    } else {
        Write-Status WARN "Base de donnees non accessible : importez le dump SQL ou verifiez config/db.php."
    }

    Write-Status STEP "Verification de l'application..."
    try {
        $response = Invoke-WebRequest -Uri $script:LocalUrl -UseBasicParsing -TimeoutSec 15 -MaximumRedirection 5
        if ($response.StatusCode -ge 200 -and $response.StatusCode -lt 400) {
            Write-Status OK "Application accessible (HTTP $($response.StatusCode))."
        } else {
            Write-Status WARN "Application accessible avec HTTP $($response.StatusCode)."
        }
    } catch {
        $statusCode = $null
        try { $statusCode = $_.Exception.Response.StatusCode.value__ } catch {}
        if ($statusCode) {
            Write-Status WARN "Application repond avec HTTP $statusCode. Verifiez les logs Apache/PHP."
        } else {
            Write-Status WARN "Application non accessible : $($_.Exception.Message)"
        }
    }

    $lan = @(Get-LanAddresses)
    if ($lan.Count -gt 0) {
        Write-Status INFO "URL depuis le telephone :"
        foreach ($address in $lan) {
            $portPart = if ($script:ApachePort -ne 80) { ":$($script:ApachePort)" } else { '' }
            $url = "http://$($address.IP)$portPart/$([uri]::EscapeDataString($script:ProjectName))/"
            Write-Host "    $url  ($($address.Interface))" -ForegroundColor Cyan
        }
    } else {
        Write-Status WARN "Aucune adresse LAN detectee. Verifiez le Wi-Fi/Ethernet."
    }

    if (-not $NoBrowser) {
        [void](Open-ProjectBrowser)
    } else {
        Write-Status INFO "Ouverture du navigateur desactivee (-NoBrowser)."
    }

    $apachePid = $null
    $mysqlPid = $null
    $apachePidFile = Join-Path $script:RuntimeDir 'apache.pid'
    $mysqlPidFile = Join-Path $script:RuntimeDir 'mysql.pid'
    if (Test-Path $apachePidFile) {
        $parsedApachePid = 0
        [void][int]::TryParse(((Get-Content $apachePidFile -Raw) -replace '\s', ''), [ref]$parsedApachePid)
        if ($parsedApachePid -gt 0) { $apachePid = $parsedApachePid }
    }
    if (Test-Path $mysqlPidFile) {
        $parsedMysqlPid = 0
        [void][int]::TryParse(((Get-Content $mysqlPidFile -Raw) -replace '\s', ''), [ref]$parsedMysqlPid)
        if ($parsedMysqlPid -gt 0) { $mysqlPid = $parsedMysqlPid }
    }

    $runtime = [pscustomobject]@{
        startedAt      = (Get-Date).ToString('s')
        projectDir     = $script:ProjectDir
        xamppHome      = $script:XamppHome
        localUrl       = $script:LocalUrl
        apachePort     = $script:ApachePort
        apachePid      = $apachePid
        mysqlPid       = $mysqlPid
        browserProfile = $script:ProfileDir
    }
    $runtime | ConvertTo-Json | Set-Content -Path (Join-Path $script:RuntimeDir 'session.json') -Encoding UTF8

    Write-Status OK "Demarrage termine."
}

# ---------------------------------------------------------------------------
# Arret
# ---------------------------------------------------------------------------

function Invoke-Stop {
    Write-Section "Arret de Scandale Bouffe"
    Stop-ProjectBrowser
    Stop-Mysql
    Stop-Apache
    Stop-Xampp
    Remove-Item (Join-Path $script:RuntimeDir 'session.json') -Force -ErrorAction SilentlyContinue
    if ($script:ExitCode -eq 0) {
        Write-Status OK "Arret termine."
    } else {
        Write-Status WARN "Arret termine avec des erreurs."
    }
}

# ---------------------------------------------------------------------------
# Etat
# ---------------------------------------------------------------------------

function Invoke-Status {
    Write-Section "Etat de Scandale Bouffe"
    Write-Status INFO "Projet : $script:ProjectDir"
    Write-Status INFO "URL    : $script:LocalUrl"

    $apacheOwner = Get-PortOwner -Port $script:ApachePort
    $mysqlOwner = Get-PortOwner -Port 3306
    if ($apacheOwner) {
        Write-Status OK "Apache : $($apacheOwner.Name) (PID $($apacheOwner.PID)) sur le port $($script:ApachePort)."
    } else {
        Write-Status WARN "Apache : arrete."
    }
    if ($mysqlOwner) {
        Write-Status OK "MySQL : $($mysqlOwner.Name) (PID $($mysqlOwner.PID)) sur le port 3306."
    } else {
        Write-Status WARN "MySQL : arrete."
    }

    try {
        $browserProcesses = @(Get-CimInstance Win32_Process -Filter "Name='msedge.exe' OR Name='chrome.exe'" -ErrorAction SilentlyContinue |
            Where-Object { $_.CommandLine -and $_.CommandLine -like "*$($script:ProfileDir)*" })
        if ($browserProcesses.Count -gt 0) {
            Write-Status OK "Navigateur du projet : $($browserProcesses.Count) processus."
        } else {
            Write-Status INFO "Navigateur du projet : ferme."
        }
    } catch {}

    $lan = @(Get-LanAddresses)
    foreach ($address in $lan) {
        $portPart = if ($script:ApachePort -ne 80) { ":$($script:ApachePort)" } else { '' }
        Write-Status INFO "URL telephone ($($address.Interface)) : http://$($address.IP)$portPart/$([uri]::EscapeDataString($script:ProjectName))/"
    }

    $xamppProcesses = @(Get-Process -ErrorAction SilentlyContinue | Where-Object {
        $_.ProcessName -like 'xampp-*' -or $_.ProcessName -in @('xampp_start', 'xampp_stop', 'xampp-control')
    })
    if ($xamppProcesses.Count -gt 0) {
        Write-Status INFO "XAMPP Control Panel ouvert."
    } else {
        Write-Status INFO "XAMPP Control Panel ferme."
    }
}

# ---------------------------------------------------------------------------
# Point d'entree
# ---------------------------------------------------------------------------

$logFile = Join-Path $script:LogDir ("{0}_{1}.log" -f $Action, (Get-Date -Format 'yyyyMMdd_HHmmss'))
try { Start-Transcript -Path $logFile -Force | Out-Null } catch {}

try {
    $script:ProjectDir = Resolve-ProjectDir -Override $ProjectDir
    $script:XamppHome = Resolve-XamppHome -Override $XamppHome -ProjectRoot $script:ProjectDir
    $script:ProjectName = Split-Path $script:ProjectDir -Leaf

    $httpdConf = Join-Path $script:XamppHome 'apache\conf\httpd.conf'
    $script:ApachePort = (Get-ApacheListenInfo -HttpdConf $httpdConf).Port
    $script:LocalUrl = Get-ProjectUrl -Port $script:ApachePort

    $localAppData = if ($env:LOCALAPPDATA) { $env:LOCALAPPDATA } else { $env:TEMP }
    $script:ProfileDir = Join-Path $localAppData 'ScandaleBouffe\browser-profile'

    switch ($Action) {
        'check'  { Invoke-Check }
        'start'  { Invoke-Start }
        'stop'   { Invoke-Stop }
        'status' { Invoke-Status }
    }
} catch {
    Write-Status FAIL $_.Exception.Message
} finally {
    try { Stop-Transcript | Out-Null } catch {}
    Pause-IfNeeded
}

exit $script:ExitCode
