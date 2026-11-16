# Scripts de lancement - Scandale Bouffe

Ces scripts servent a exploiter l'application en local sur le PC, sans ouvrir
manuellement XAMPP et sans lancer Apache/MySQL a la main.

## Fichiers

| Fichier | Role |
|---|---|
| `check_requirements.bat` | Verifie les prerequis du PC et de l'application. |
| `start_scandale.bat` | Demarre Apache + MySQL, verifie la base et ouvre le projet dans le navigateur. |
| `stop_scandale.bat` | Ferme la fenetre du projet, arrete MySQL et Apache, puis ferme XAMPP. |
| `scandale.ps1` | Moteur commun aux trois scripts. Ne pas supprimer. |

## Utilisation

### 1. Verification

Double-cliquer sur :

```text
check_requirements.bat
```

Le script affiche :

- `[OK]` : point conforme ;
- `[ATTENTION]` : point a verifier, mais non bloquant ;
- `[MANQUANT]` : point a corriger avant de lancer l'application.

Exemples de controles : XAMPP, PHP 8+, extensions `pdo_mysql`, `fileinfo`,
`mbstring`, `mod_rewrite`, `AllowOverride`, `.htaccess`, CSS compile, dossier
`uploads`, dump SQL, MySQL, base `scandale_bouffe`, compte administrateur,
adresse IP locale, pare-feu et acces Internet aux assets externes.

### 2. Demarrage

Double-cliquer sur :

```text
start_scandale.bat
```

Le script :

1. demarre Apache en arriere-plan (fenetre cachee) ;
2. demarre MySQL en arriere-plan ;
3. verifie que la base est accessible ;
4. verifie que l'application repond en HTTP ;
5. affiche l'adresse a utiliser depuis le telephone ;
6. ouvre Edge ou Chrome dans une fenetre dediee au projet.

Le PC et le telephone doivent etre sur le meme reseau Wi-Fi/LAN.
Depuis le telephone, utiliser l'adresse affichee, par exemple :

```text
http://192.168.1.50/scandale_bouffe_management/
```

Ne pas utiliser `http://localhost/...` sur le telephone : `localhost` designe
le telephone lui-meme.

#### Options utiles

```bat
start_scandale.bat -NoBrowser
start_scandale.bat -NoPause
start_scandale.bat -XamppHome "D:\xampp"
start_scandale.bat -ProjectDir "D:\projets\scandale_bouffe_management"
```

- `-NoBrowser` : demarre uniquement les serveurs, sans ouvrir le navigateur.
- `-NoPause` : ne pas attendre la touche Entree a la fin.
- `-XamppHome` : chemin de XAMPP si different de `C:\xampp`.
- `-ProjectDir` : chemin du projet si different du dossier parent de `scripts`.

### 3. Arret

Double-cliquer sur :

```text
stop_scandale.bat
```

Le script :

1. ferme la fenetre Edge/Chrome dediee au projet ;
2. arrete MySQL proprement ;
3. arrete Apache ;
4. ferme XAMPP Control Panel s'il est ouvert ;
5. supprime les fichiers d'etat temporaires.

## Navigateur dedie

Pour pouvoir fermer uniquement la fenetre du projet, `start_scandale.bat`
utilise un profil Edge/Chrome separe :

```text
%LOCALAPPDATA%\ScandaleBouffe\browser-profile
```

Ce profil est independant du navigateur habituel : les onglets personnels ne
sont pas fermes par `stop_scandale.bat`.

Si Edge et Chrome sont absents, le navigateur par defaut est utilise. Dans ce
cas, l'onglet devra etre ferme manuellement : les navigateurs ne permettent pas
de fermer un onglet precis depuis un script.

## Journaux et fichiers temporaires

- `scripts/logs/` : journaux de verification, de demarrage et d'arret.
- `scripts/.runtime/` : PID des processus lances et etat de la session.

Ces deux dossiers sont ignores par Git.

## Remarques

- Le script lance Apache et MySQL en processus caches, sans passer par
  XAMPP Control Panel. C'est volontaire : l'utilisateur n'a rien a cliquer.
- Si Apache ou MySQL est deja demarre, le script le detecte et ne lance pas
  un deuxieme processus.
- Si le port 80 ou 3306 est occupe par un autre programme, le demarrage est
  refuse avec un message explicite.
- Si Apache/MySQL sont installes comme services Windows, l'arret peut demander
  les droits administrateur. Lancer le script en administrateur dans ce cas.
- Le premier demarrage peut prendre quelques secondes (jusqu'a 30 s pour
  Apache, 45 s pour MySQL).
- Les scripts ne configurent pas le pare-feu a la place de l'utilisateur.
  `check_requirements.bat` signale les points a corriger.

## Depannage rapide

| Probleme | Piste |
|---|---|
| `[MANQUANT] Apache introuvable` | Verifier `XAMPP_HOME` ou le chemin `C:\xampp`. |
| `Port 80 occupe` | Arreter IIS/Skype/autre Apache, ou changer le port dans `httpd.conf`. |
| `MySQL n'a pas demarre` | Consulter `scripts/logs/mysql-start.err.log` et `C:\xampp\mysql\data\mysql_error.log`. |
| `Base de donnees non accessible` | Importer `database/scandale_bouffe_30_09.sql` dans phpMyAdmin. |
| Le telephone ne repond pas | Verifier pare-feu Windows, profil reseau Prive, IP, Wi-Fi et isolation client. |
| Les graphiques sont vides | Le PC n'a pas acces a `cdn.jsdelivr.net` ; l'application reste utilisable. |
| L'onglet ne se ferme pas | Navigateur par defaut utilise : fermer l'onglet manuellement. |

## Securite

Ces scripts sont prevus pour un usage en **reseau local**. Ne pas rediriger le
port 80 vers Internet et ne pas exposer phpMyAdmin ou MySQL sur le reseau.
