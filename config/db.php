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

// ==========================================
// MODES DE PAIEMENT
// ==========================================

/**
 * Modes de paiement utilisés par le POS et les lignes de vente.
 * L'ordre est aussi utilisé pour l'affichage des filtres et des badges.
 */
function salePaymentMethods(): array {
    return ['cash', 'prepaye', 'trosa', 'mobile'];
}

/** Libellé lisible d'un mode de paiement. */
function salePaymentMethodLabel(string $method): string {
    return match ($method) {
        'prepaye' => 'Prépayé',
        'trosa' => 'Trosa',
        'mobile' => 'Mobile',
        'mixed' => 'Mixte',
        default => 'Cash',
    };
}

/** Classes Tailwind du badge d'un mode de paiement. */
function salePaymentMethodBadgeClass(string $method): string {
    return match ($method) {
        'prepaye' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'trosa' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        'mobile' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'mixed' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        default => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    };
}

/**
 * Déduit le mode de paiement global d'une vente à partir des modes de ses lignes.
 * Retourne le mode unique, ou "mixed" si plusieurs modes coexistent.
 */
function deriveSalePaymentMethod(array $payMethods): string {
    $allowed = salePaymentMethods();
    $unique = [];
    foreach ($payMethods as $method) {
        if (is_string($method) && in_array($method, $allowed, true)) {
            $unique[$method] = true;
        }
    }
    if (count($unique) === 0) {
        return 'cash';
    }
    if (count($unique) === 1) {
        return array_key_first($unique);
    }
    return 'mixed';
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
 *
 * Sans dates : stock initial de référence, entrées et sorties cumulées.
 * Avec une période : le stock initial correspond au stock au début de la
 * période (mouvements antérieurs inclus), puis seules les entrées et les
 * sorties de la période sont comptées.
 */
function stockMovementTotals(int $productId, ?string $dateFrom = null, ?string $dateTo = null): array {
    $periodConditions = [];
    $periodParams = [];

    if ($dateFrom !== null && $dateFrom !== '') {
        $periodConditions[] = 'created_at >= ?';
        $periodParams[] = $dateFrom . ' 00:00:00';
    }
    if ($dateTo !== null && $dateTo !== '') {
        $periodConditions[] = 'created_at <= ?';
        $periodParams[] = $dateTo . ' 23:59:59';
    }

    $periodSql = $periodConditions ? ' AND ' . implode(' AND ', $periodConditions) : '';

    // Mouvements d'entrée/sortie antérieurs au début de la période : ils
    // font partie du stock initial de la période.
    $openingSql = '0';
    $openingParams = [];
    if ($dateFrom !== null && $dateFrom !== '') {
        $openingSql = "COALESCE(SUM(
            CASE
                WHEN movement_type IN ('in', 'out') AND created_at < ?
                THEN CASE WHEN movement_type = 'in' THEN quantity ELSE -quantity END
                ELSE 0
            END
        ), 0)";
        $openingParams[] = $dateFrom . ' 00:00:00';
    }

    $row = fetchAssoc("
        SELECT
            COALESCE(SUM(CASE WHEN movement_type = 'initial' THEN quantity ELSE 0 END), 0) AS initial_qty,
            COALESCE(SUM(CASE WHEN movement_type = 'in'{$periodSql} THEN quantity ELSE 0 END), 0) AS in_qty,
            COALESCE(SUM(CASE WHEN movement_type = 'out'{$periodSql} THEN quantity ELSE 0 END), 0) AS out_qty,
            {$openingSql} AS opening_delta
        FROM stock_movements
        WHERE product_id = ?
    ", array_merge($periodParams, $periodParams, $openingParams, [$productId]));

    $initial = (int)($row['initial_qty'] ?? 0) + (int)($row['opening_delta'] ?? 0);
    $in = (int)($row['in_qty'] ?? 0);
    $out = (int)($row['out_qty'] ?? 0);

    return [
        'initial' => $initial,
        'in' => $in,
        'out' => $out,
        'final' => $initial + $in - $out,
    ];
}