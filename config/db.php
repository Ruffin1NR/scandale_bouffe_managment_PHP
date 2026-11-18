<?php
/**
 * Configuration de la base de données MySQL
 * Projet : Scandale Bouffe - PHP Pur
 */

// Jour d'activité aligné sur l'heure locale et MySQL (évite un décalage de date en soirée)
date_default_timezone_set('Indian/Antananarivo');

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

// Normalise une date au format Y-m-d (retourne $fallback si vide ou invalide)
function normalizeDate($value, ?string $fallback = null): string {
    $fallback = $fallback ?? date('Y-m-d');
    if (!is_string($value)) {
        return $fallback;
    }
    $value = trim($value);
    if ($value === '') {
        return $fallback;
    }
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    if ($date === false || $date->format('Y-m-d') !== $value) {
        return $fallback;
    }
    return $value;
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

// ==========================================
// MOUVEMENTS DE STOCK
// ==========================================

/**
 * Enregistre un mouvement de stock.
 * Types : initial (stock initial), in (entrée), out (sortie).
 */
function addStockMovement(int $productId, string $movementType, int $quantity, string $reason, ?string $note = null, ?int $saleId = null, ?int $userId = null): void {
    if (!in_array($movementType, ['initial', 'in', 'out'], true)) {
        throw new InvalidArgumentException('Type de mouvement de stock invalide.');
    }

    $quantity = max(0, $quantity);
    if ($movementType !== 'initial' && $quantity === 0) {
        return;
    }

    query(
        "INSERT INTO stock_movements (product_id, movement_type, quantity, reason, note, sale_id, user_id, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
        [$productId, $movementType, $quantity, $reason, $note, $saleId, $userId]
    );
}

/**
 * Crée un stock initial uniquement si le produit n'a encore aucun mouvement.
 * Cela permet de démarrer la continuité sans fausser les produits déjà suivis.
 */
function ensureInitialStockMovement(int $productId, int $quantity, string $note = 'Stock initial', ?int $userId = null): void {
    $existing = fetchAssoc("SELECT id FROM stock_movements WHERE product_id = ? LIMIT 1", [$productId]);
    if (empty($existing)) {
        addStockMovement($productId, 'initial', max(0, $quantity), 'initial', $note, null, $userId);
    }
}

/**
 * Retourne les totaux de continuité d'un produit.
 */
function stockMovementTotals(int $productId): array {
    $row = fetchAssoc("
        SELECT
            COALESCE(SUM(CASE WHEN movement_type = 'initial' THEN quantity ELSE 0 END), 0) AS initial_qty,
            COALESCE(SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END), 0) AS in_qty,
            COALESCE(SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END), 0) AS out_qty
        FROM stock_movements
        WHERE product_id = ?
    ", [$productId]);

    $initial = (int)($row['initial_qty'] ?? 0);
    $in = (int)($row['in_qty'] ?? 0);
    $out = (int)($row['out_qty'] ?? 0);

    return [
        'initial' => $initial,
        'in' => $in,
        'out' => $out,
        'final' => $initial + $in - $out,
    ];
}