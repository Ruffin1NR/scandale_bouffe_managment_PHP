<?php
/**
 * Front Controller - Scandale Bouffe PHP Pur
 * Gestion complète des routes, authentification et rôles
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../config/db.php';

// ==========================================
// PARSE DE L'URL COURANTE
// ==========================================
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($uri, PHP_URL_PATH);
$uri = rawurldecode($uri);

$base = BASE_URL;
if ($base !== '' && strpos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}
$uri = trim($uri, '/');

// Fichiers statiques
if ($uri !== '') {
    $staticFile = realpath(__DIR__ . '/../' . $uri);
    $projectRoot = realpath(__DIR__ . '/..');
    if ($staticFile !== false && strpos($staticFile, $projectRoot . DIRECTORY_SEPARATOR) === 0 && is_file($staticFile)) {
        return false;
    }
}

$segments = $uri === '' ? [] : explode('/', $uri);
$page = $segments[0] ?? '';
$pageParams = array_slice($segments, 1);

// ==========================================
// AUTHENTIFICATION
// ==========================================
$protectedPages = ['inventory', 'vendeurs', 'vendeur', 'pos', 'sales', 'ventes', 'dashboard'];
$isProtected = in_array($page, $protectedPages);

if ($isProtected && !isset($_SESSION['user_id'])) {
    header('Location: ' . url('login'));
    exit;
}

// Pages admin-only (pas vendeur)
// Exception : les vendeurs doivent pouvoir enregistrer leur rapport du jour depuis /pos
$isSaveReport = in_array($page, ['sales', 'ventes']) && (($pageParams[0] ?? '') === 'save-report');
$adminPages = ['inventory', 'vendeurs', 'categories', 'sales', 'ventes', 'dashboard'];
        $isAdminOnly = (in_array($page, $adminPages) && !$isSaveReport) || ($page === 'inventory' && in_array($pageParams[0] ?? '', ['store', 'update', 'bulk-destroy', 'create', 'edit']));

if ($isAdminOnly && isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'administrateur') {
    http_response_code(403);
    require __DIR__ . '/../pages/403.php';
    exit;
}

// ==========================================
// ROUTES
// ==========================================
switch ($page) {

    // ---- Accueil ----
    case '':
    case 'home':
        require __DIR__ . '/../pages/home.php';
        break;

    // ---- Authentification ----
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../auth/login.php';
        } else {
            header('Location: ' . url('?auth=login'));
        }
        exit;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../pages/register.php';
        } else {
            header('Location: ' . url('?auth=register'));
        }
        exit;

    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_destroy();
            header('Location: ' . url(''));
            exit;
        }
        header('Location: ' . url(''));
        exit;

    // ==========================================
    // INVENTAIRE (Admin)
    // ==========================================
    case 'inventory':
        $action = $pageParams[0] ?? '';

        switch ($action) {
            case 'create':
                require __DIR__ . '/../pages/inventory/create.php';
                break;

            case 'edit':
                $id = (int)($_GET['id'] ?? ($pageParams[1] ?? 0));
                if ($id <= 0) {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                require __DIR__ . '/../pages/inventory/edit.php';
                break;

            case 'show':
                require __DIR__ . '/../pages/inventory/show.php';
                break;

            case 'store':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $name = trim($_POST['name'] ?? '');
                $price = (float)($_POST['price'] ?? 0);
                $description = trim($_POST['description'] ?? '');
                $category_id = (int)($_POST['category_id'] ?? 0);
                $unit = $_POST['unit'] ?? 'pcs';
                $min_stock = (int)($_POST['min_stock'] ?? 5);
                $quantity = (float)($_POST['quantity'] ?? 0);
                $image = null;

                if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = realpath(__DIR__ . '/../uploads');
                    if ($uploadDir === false) {
                        $uploadDir = __DIR__ . '/../uploads';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                    }
                    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                    finfo_close($finfo);

                    if (in_array($mime, $allowed, true) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
                        $ext = match($mime) { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
                        $image = bin2hex(random_bytes(16)) . '.' . $ext;
                        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . '/' . $image);
                    } else {
                        $_SESSION['error'] = "Image invalide (max 2 Mo, JPG/PNG/WebP).";
                        header('Location: ' . url('inventory/create'));
                        exit;
                    }
                }

                if ($name !== '') {
                    query("INSERT INTO products (name, description, price, category_id, unit, min_stock, quantity, image, active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())", [$name, $description, $price, $category_id, $unit, $min_stock, $quantity, $image]);
                    $_SESSION['success'] = "Produit créé avec succès.";
                } else {
                    $_SESSION['error'] = "Le nom du produit est obligatoire.";
                }
                header('Location: ' . url('inventory'));
                exit;

            case 'update':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $name = trim($_POST['name'] ?? '');
                $price = (float)($_POST['price'] ?? 0);
                $description = trim($_POST['description'] ?? '');
                $category_id = (int)($_POST['category_id'] ?? 0);
                $unit = $_POST['unit'] ?? 'pcs';
                $min_stock = (int)($_POST['min_stock'] ?? 0);
                $quantity = (float)($_POST['quantity'] ?? 0);
                $currentImage = $_POST['current_image'] ?? '';
                $image = $currentImage !== '' ? $currentImage : null;

                if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = realpath(__DIR__ . '/../uploads');
                    if ($uploadDir === false) {
                        $uploadDir = __DIR__ . '/../uploads';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                    }
                    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                    finfo_close($finfo);

                    if (in_array($mime, $allowed, true) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
                        if ($currentImage !== '' && file_exists($uploadDir . '/' . $currentImage)) {
                            unlink($uploadDir . '/' . $currentImage);
                        }
                        $ext = match($mime) { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
                        $image = bin2hex(random_bytes(16)) . '.' . $ext;
                        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . '/' . $image);
                    } else {
                        $_SESSION['error'] = "Image invalide (max 2 Mo, JPG/PNG/WebP).";
                        header('Location: ' . url('inventory/edit?id=' . $id));
                        exit;
                    }
                }

                if ($name !== '') {
                    query("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, unit = ?, min_stock = ?, quantity = ?, image = ?, updated_at = NOW() WHERE id = ?", [$name, $description, $price, $category_id, $unit, $min_stock, $quantity, $image, $id]);
                    $_SESSION['success'] = "Produit modifié avec succès.";
                } else {
                    $_SESSION['error'] = "Le nom du produit est obligatoire.";
                }
                header('Location: ' . url('inventory'));
                exit;

            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['id'])) {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $id = (int)($_GET['id'] ?? 0);
                if ($id > 0) {
                    query("UPDATE products SET active = 0, updated_at = NOW() WHERE id = ?", [$id]);
                    $_SESSION['success'] = "Produit supprim\u00e9 avec succ\u00e8s.";
                }
                header('Location: ' . url('inventory'));
                exit;

            case 'bulk-destroy':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $ids = array_map('intval', (array)($_POST['ids'] ?? []));
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    query("UPDATE products SET active = 0, updated_at = NOW() WHERE id IN ($placeholders)", $ids);
                    $_SESSION['success'] = "Produit(s) supprim\u00e9(s) avec succ\u00e8s.";
                }
                header('Location: ' . url('inventory'));
                exit;

            default:
                require __DIR__ . '/../pages/inventory/index.php';
                break;
        }
        break;

    // ==========================================
    // CATEGORIES (Admin)
    // ==========================================
    case 'categories':
        $catAction = $pageParams[0] ?? '';
        switch ($catAction) {
            case 'create':
                require __DIR__ . '/../pages/admin/category_form.php';
                break;
            case 'edit':
                require __DIR__ . '/../pages/admin/category_form.php';
                break;
            case 'store':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('categories')); exit; }
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if ($name === '') { $_SESSION['error'] = "Le nom est obligatoire."; header('Location: ' . url('categories')); exit; }
                if ($id > 0) {
                    $existing = fetchAssoc("SELECT id FROM categories WHERE name = ? AND id != ?", [$name, $id]);
                    if ($existing) { $_SESSION['error'] = "Une categorie avec ce nom existe deja."; header('Location: ' . url('categories/edit?id=' . $id)); exit; }
                    query("UPDATE categories SET name = ?, description = ?, updated_at = NOW() WHERE id = ?", [$name, $description, $id]);
                    $_SESSION['success'] = "Categorie modifiee.";
                } else {
                    $existing = fetchAssoc("SELECT id FROM categories WHERE name = ?", [$name]);
                    if ($existing) { $_SESSION['error'] = "Une categorie avec ce nom existe deja."; header('Location: ' . url('categories/create')); exit; }
                    query("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())", [$name, $description]);
                    $_SESSION['success'] = "Categorie creee.";
                }
                header('Location: ' . url('categories'));
                exit;
            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('categories')); exit; }
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $count = fetchAssoc("SELECT COUNT(*) as c FROM products WHERE category_id = ? AND active = 1", [$id]);
                    if (($count['c'] ?? 0) > 0) {
                        $_SESSION['error'] = "Impossible de supprimer : des produits sont associes a cette categorie.";
                    } else {
                        query("DELETE FROM categories WHERE id = ?", [$id]);
                        $_SESSION['success'] = "Categorie supprimee.";
                    }
                }
                header('Location: ' . url('categories'));
                exit;
            default:
                require __DIR__ . '/../pages/admin/categories.php';
                break;
        }
        break;

    // ==========================================
    // VENDEURS (Admin)
    // ==========================================
    case 'vendeurs':
        if (($pageParams[0] ?? '') === 'create') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = trim($_POST['password'] ?? '');

                    if ($name !== '' && $email !== '' && $password !== '') {
                    $existing = fetchAssoc("SELECT id FROM users WHERE email = ?", [$email]);
                    if ($existing) {
                        $_SESSION['error'] = "Un utilisateur avec cet email existe déjà.";
                    } else {
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $pdo = $GLOBALS['pdo'];
                        $pdo->beginTransaction();
                        try {
                            query("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())", [$name, $email, $hashed]);
                            $userId = $pdo->lastInsertId();
                            $vendorRole = fetchAssoc("SELECT id FROM roles WHERE name = 'vendeur'");
                            if ($vendorRole) {
                                query("INSERT INTO roles_users (user_id, role_id, created_at) VALUES (?, ?, NOW())", [$userId, $vendorRole['id']]);
                            }
                            $pdo->commit();
                            $_SESSION['success'] = "Vendeur créé avec succès.";
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            $_SESSION['error'] = "Erreur : " . $e->getMessage();
                        }
                    }
                } else {
                    $_SESSION['error'] = "Tous les champs sont obligatoires.";
                }
                header('Location: ' . url('vendeurs'));
                exit;
            }
            require __DIR__ . '/../pages/admin/vendor_create.php';
            break;
        }

        if (isset($pageParams[0]) && is_numeric($pageParams[0])) {
            $_GET['id'] = (int)$pageParams[0];
            require __DIR__ . '/../pages/admin/vendor_detail.php';
            break;
        }

        require __DIR__ . '/../pages/admin/vendors.php';
        break;

    // ---- Ventes admin ----
    case 'sales':
    case 'ventes':
        $salesAction = $pageParams[0] ?? '';
        switch ($salesAction) {
            case 'create':
                require __DIR__ . '/../pages/admin/sale_create.php';
                break;
            case 'save-report':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
                    exit;
                }
                $reportDate = $_POST['date'] ?? date('Y-m-d');
                $prepaye = max(0, (float)($_POST['prepaye'] ?? 0));
                $trosaVal = max(0, (float)($_POST['trosa'] ?? 0));
                $paiementVal = max(0, (float)($_POST['paiement'] ?? 0));
                $nofyVal = max(0, (float)($_POST['nofy'] ?? 0));
                query("INSERT INTO daily_reports (user_id, report_date, prepaye, trosa, paiement, nofy, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE prepaye = VALUES(prepaye), trosa = VALUES(trosa), paiement = VALUES(paiement), nofy = VALUES(nofy)", [$_SESSION['user_id'], $reportDate, $prepaye, $trosaVal, $paiementVal, $nofyVal]);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Rapport enregistré.']);
                exit;
            default:
                require __DIR__ . '/../pages/admin/sales.php';
                break;
        }
        break;

    // ---- Dashboard admin ----
    case 'dashboard':
        require __DIR__ . '/../pages/dashboard.php';
        break;

    // ==========================================
    // VENDEUR - Point de Vente (POS)
    // ==========================================
    case 'pos':
        $action = $pageParams[0] ?? '';

        switch ($action) {
            case 'save-versement':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
                    exit;
                }
                $amount = max(0, (float)($_POST['amount'] ?? 0));
                if ($amount <= 0) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Le montant doit être supérieur à 0.']);
                    exit;
                }
                query("INSERT INTO daily_deposits (user_id, amount, deposit_date, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $amount, date('Y-m-d')]);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Versement de ' . number_format($amount, 0, ',', ' ') . ' Ar enregistré.']);
                exit;

            case 'save-relais':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
                    exit;
                }
                $relaisAmount = max(0, (float)($_POST['amount'] ?? 0));
                if ($relaisAmount <= 0) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Le montant doit être supérieur à 0.']);
                    exit;
                }
                query("INSERT INTO daily_relais (user_id, amount, relais_date, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $relaisAmount, date('Y-m-d')]);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Relais de ' . number_format($relaisAmount, 0, ',', ' ') . ' Ar enregistré.']);
                exit;

            case 'save-paiement':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
                    exit;
                }
                $paiementAmount = max(0, (float)($_POST['amount'] ?? 0));
                if ($paiementAmount <= 0) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Le montant doit être supérieur à 0.']);
                    exit;
                }
                query("INSERT INTO daily_paiements (user_id, amount, paiement_date, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $paiementAmount, date('Y-m-d')]);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Paiement de ' . number_format($paiementAmount, 0, ',', ' ') . ' Ar enregistré.']);
                exit;

            case 'checkout':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('pos'));
                    exit;
                }

                $items = $_POST['items'] ?? [];
                $total = 0;
                $validItems = [];

                if (empty($items)) {
                    $_SESSION['error'] = "Le panier est vide.";
                    header('Location: ' . url('pos'));
                    exit;
                }

                // Valider et préparer les articles
                $payTotals = ['prepaye' => 0, 'trosa' => 0, 'cash' => 0, 'mobile' => 0];
                foreach ($items as $item) {
                    $product_id = (int)($item['product_id'] ?? 0);
                    $qty = (int)($item['quantity'] ?? 0);

                    if ($product_id <= 0 || $qty <= 0) continue;

                    $product = fetchAssoc("SELECT id, name, price, quantity, active FROM products WHERE id = ?", [$product_id]);

                    if (!$product || !$product['active']) {
                        $_SESSION['error'] = "Produit #$product_id indisponible.";
                        header('Location: ' . url('pos'));
                        exit;
                    }
                    if ($product['quantity'] < $qty) {
                        $_SESSION['error'] = "Stock insuffisant pour \"" . $product['name'] . "\" (disponible : " . $product['quantity'] . ").";
                        header('Location: ' . url('pos'));
                        exit;
                    }

                    $lineTotal = $product['price'] * $qty;
                    $total += $lineTotal;
                    $payMethod = $item['pay_method'] ?? 'cash';
                    if (!in_array($payMethod, ['cash', 'prepaye', 'trosa', 'mobile'], true)) {
                        $payMethod = 'cash';
                    }
                    $payTotals[$payMethod] += $lineTotal;
                    $validItems[] = [
                        'product_id' => $product['id'],
                        'product_name' => $product['name'],
                        'quantity' => $qty,
                        'unit_price' => $product['price'],
                        'line_total' => $lineTotal,
                        'pay_method' => $payMethod,
                    ];
                }

                if (empty($validItems)) {
                    $_SESSION['error'] = "Aucun article valide.";
                    header('Location: ' . url('pos'));
                    exit;
                }

                // Transaction DB : créer la vente + décrémenter le stock
                $paymentMethod = in_array($_POST['payment_method'] ?? '', ['cash', 'mobile_money', 'credit']) ? $_POST['payment_method'] : 'cash';
                $versement = max(0, (float)($_POST['versement'] ?? 0));
                $pdo = $GLOBALS['pdo'];
                $pdo->beginTransaction();
                try {
                    // Créer la vente
                    query("INSERT INTO sales (user_id, total, payment_method, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $total, $paymentMethod]);
                    $saleId = $pdo->lastInsertId();

                    foreach ($validItems as $item) {
                        // Créer la ligne de vente
                        query("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)", [$saleId, $item['product_id'], $item['quantity'], $item['unit_price']]);

                        // Décrémenter le stock
                        query("UPDATE products SET quantity = quantity - ? WHERE id = ?", [$item['quantity'], $item['product_id']]);
                    }

                    // Enregistrer le versement du jour
                    if ($versement > 0) {
                        query("INSERT INTO daily_deposits (user_id, amount, deposit_date, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $versement, date('Y-m-d')]);
                    }

                    // Ventiler la vente dans le rapport du jour (additif, même date PHP que l'affichage /pos)
                    $reportDate = date('Y-m-d');
                    query("INSERT INTO daily_reports (user_id, report_date, prepaye, trosa, paiement, nofy, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE prepaye = prepaye + VALUES(prepaye), trosa = trosa + VALUES(trosa), paiement = paiement + VALUES(paiement), nofy = nofy + VALUES(nofy)", [$_SESSION['user_id'], $reportDate, $payTotals['prepaye'], $payTotals['trosa'], $payTotals['cash'], $payTotals['mobile']]);

                    $pdo->commit();
                    $methodLabel = match($paymentMethod) { 'mobile_money' => 'Mobile Money', 'credit' => 'Crédit', default => 'Cash' };
                    $_SESSION['success'] = "Vente enregistrée (" . number_format($total, 0, ',', ' ') . " Ar — $methodLabel)";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
                }

                header('Location: ' . url('pos'));
                exit;

            case 'historique':
                require __DIR__ . '/../pages/vendor/history.php';
                exit;

            default:
                require __DIR__ . '/../pages/vendor/pos.php';
                break;
        }
        break;

    // ==========================================
    // VENDEUR - Historique des ventes
    // ==========================================
    case 'vendeur':
        if (($pageParams[0] ?? '') === 'historique') {
            require __DIR__ . '/../pages/vendor/history.php';
            break;
        }
        require __DIR__ . '/../pages/vendor/dashboard.php';
        break;

    // ==========================================
    // PROFIL
    // ==========================================
    case 'profile':
        $action = $pageParams[0] ?? '';
        switch ($action) {
            case 'update':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('profile')); exit; }
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                if ($name !== '' && $email !== '') {
                    $existing = fetchAssoc("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']]);
                    if ($existing) {
                        $_SESSION['error'] = "Cet email est déjà utilisé.";
                    } else {
                        query("UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?", [$name, $email, $_SESSION['user_id']]);
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['success'] = "Profil mis à jour.";
                    }
                } else {
                    $_SESSION['error'] = "Tous les champs sont obligatoires.";
                }
                header('Location: ' . url('profile'));
                exit;

            case 'password':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('profile')); exit; }
                $current = $_POST['current_password'] ?? '';
                $new = $_POST['new_password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';
                $user = fetchAssoc("SELECT password FROM users WHERE id = ?", [$_SESSION['user_id']]);
                if (!$user || !password_verify($current, $user['password'])) {
                    $_SESSION['error'] = "Mot de passe actuel incorrect.";
                } elseif ($new !== $confirm) {
                    $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
                } elseif (strlen($new) < 6) {
                    $_SESSION['error'] = "Le mot de passe doit faire au moins 6 caractères.";
                } else {
                    query("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?", [password_hash($new, PASSWORD_DEFAULT), $_SESSION['user_id']]);
                    $_SESSION['success'] = "Mot de passe changé.";
                }
                header('Location: ' . url('profile'));
                exit;

            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('profile')); exit; }
                query("DELETE FROM users WHERE id = ?", [$_SESSION['user_id']]);
                session_destroy();
                header('Location: ' . url(''));
                exit;

            default:
                require __DIR__ . '/../pages/profile/edit.php';
                break;
        }
        break;

    // ---- 404 ----
    default:
        http_response_code(404);
        require __DIR__ . '/../pages/404.php';
        break;
}
