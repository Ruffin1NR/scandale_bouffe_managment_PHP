<?php
/**
 * Configuration de la base de données MySQL
 * Projet : Scandale Bouffe - PHP Pur
 */

// Paramètres de connexion à la base de données
$dbHost = 'localhost';
$dbName = 'scandale_bouffe';
$dbUser = 'root';
$dbPass = '';  // Mot de passe MySQL par défaut XAMPP est vide

// ==========================================
// BASE_URL - racine de l'application
// Détecte automatiquement le sous-dossier (ex: /scandale_bouffe_management)
// ==========================================
// Surcharge manuelle possible : define('BASE_URL_OVERRIDE', '/mon/chemin');
if (!defined('BASE_URL')) {
    $projectDir = realpath(__DIR__ . '/..');
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;

    $basePath = '';
    if ($docRoot !== false && $docRoot !== '' && $projectDir !== false) {
        $docRootNorm = rtrim(str_replace('\\', '/', $docRoot), '/');
        $projectNorm = str_replace('\\', '/', $projectDir);
        if (stripos($projectNorm, $docRootNorm) === 0) {
            $basePath = substr($projectNorm, strlen($docRootNorm));
        }
    }
    $basePath = rtrim($basePath, '/');
    if (defined('BASE_URL_OVERRIDE')) {
        $basePath = rtrim(BASE_URL_OVERRIDE, '/');
    }
    define('BASE_URL', $basePath);
}

// Fonction utilitaire pour générer une URL absolue
function url(string $path = ''): string {
    $base = BASE_URL === '' ? '' : BASE_URL . '/';
    return $base . ltrim($path, '/');
}

try {
    // Création de la connexion PDO
    // ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION pour les erreurs visibles
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    // Optionnel : définir le jeu de caractères par défaut
    // $pdo->exec("SET NAMES utf8mb4");
    
} catch (PDOException $e) {
    // En production, afficher un message d'erreur générique
    error_log("Erreur de connexion PDO : " . $e->getMessage());
    die("Impossible de se connecter à la base de données. Veuillez réessayer plus tard.");
}

// Fonction utilitaire pour exécuter une requête sans résultats
function query(string $sql, array $params = []): PDOStatement {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Fonction utilitaire pour exécuter une requête et récupérer une seule ligne
function fetchAssoc(string $sql, array $params = []): array {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: [];
}

// Fonction utilitaire pour exécuter une requête et récupérer toutes les lignes
function fetchAllAssoc(string $sql, array $params = []): array {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}