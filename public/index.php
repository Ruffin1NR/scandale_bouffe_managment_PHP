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

            case 'movements':
                require __DIR__ . '/../pages/inventory/movements.php';
                break;

            case 'movement':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $movementProductId = (int)($_POST['product_id'] ?? 0);
                $movementType = (string)($_POST['movement_type'] ?? '');
                $movementQuantity = max(0, (int)($_POST['quantity'] ?? 0));
                $movementNote = mb_substr(trim((string)($_POST['note'] ?? '')), 0, 255);
                if ($movementProductId <= 0 || !in_array($movementType, ['in', 'out'], true) || $movementQuantity <= 0) {
                    $_SESSION['error'] = "Mouvement de stock invalide.";
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $movementUserId = (int)($_SESSION['user_id'] ?? 0) ?: null;
                $pdo = $GLOBALS['pdo'];
                $pdo->beginTransaction();
                try {
                    $movementProduct = fetchAssoc("
                        SELECT p.id, p.name, p.quantity, COALESCE(c.track_stock, 0) AS track_stock
                        FROM products p
                        LEFT JOIN categories c ON c.id = p.category_id
                        WHERE p.id = ? AND p.active = 1
                        FOR UPDATE
                    ", [$movementProductId]);
                    if (empty($movementProduct) || (int)$movementProduct['track_stock'] !== 1) {
                        throw new Exception("Produit introuvable ou non suivi.");
                    }
                    $currentQuantity = (int)$movementProduct['quantity'];
                    if ($movementType === 'out' && $movementQuantity > $currentQuantity) {
                        throw new Exception("Stock insuffisant pour \"" . $movementProduct['name'] . "\" (disponible : $currentQuantity).");
                    }

                    ensureInitialStockMovement($movementProductId, $currentQuantity, 'Stock initial', $movementUserId);

                    if ($movementType === 'in') {
                        query("UPDATE products SET quantity = quantity + ?, updated_at = NOW() WHERE id = ?", [$movementQuantity, $movementProductId]);
                    } else {
                        query("UPDATE products SET quantity = quantity - ?, updated_at = NOW() WHERE id = ?", [$movementQuantity, $movementProductId]);
                    }

                    if ($movementNote === '') {
                        $movementNote = $movementType === 'in' ? 'Entrée manuelle' : 'Sortie manuelle';
                    }
                    addStockMovement($movementProductId, $movementType, $movementQuantity, 'manual', $movementNote, null, $movementUserId);

                    $pdo->commit();
                    $_SESSION['success'] = "Mouvement de stock enregistré.";
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $_SESSION['error'] = "Erreur lors de l'enregistrement du mouvement.";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['error'] = $e->getMessage();
                }
                header('Location: ' . url('inventory/movements?id=' . $movementProductId));
                exit;

            case 'store':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $name = trim($_POST['name'] ?? '');
                $price = (float)($_POST['price'] ?? 0);
                $description = trim($_POST['description'] ?? '');
                $categoryRaw = trim((string)($_POST['category_id'] ?? ''));
                $category_id = (int)$categoryRaw;
                $catExists = $category_id > 0 ? fetchAssoc("SELECT id, track_stock FROM categories WHERE id = ?", [$category_id]) : [];
                if (empty($catExists)) {
                    $_SESSION['error'] = "La catégorie est obligatoire. Veuillez en choisir une dans la liste.";
                    header('Location: ' . url('inventory/create'));
                    exit;
                }
                $unit = $_POST['unit'] ?? 'pcs';
                $trackStock = (int)($catExists['track_stock'] ?? 0) === 1;
                $min_stock = $trackStock ? max(0, (int)($_POST['min_stock'] ?? 0)) : 0;
                $quantity = $trackStock ? max(0, (int)($_POST['quantity'] ?? 0)) : 0;
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
                    $pdo = $GLOBALS['pdo'];
                    $pdo->beginTransaction();
                    try {
                        query("INSERT INTO products (name, description, price, category_id, unit, min_stock, quantity, image, active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())", [$name, $description, $price, $category_id, $unit, $min_stock, $quantity, $image]);
                        $newProductId = (int)$pdo->lastInsertId();
                        if ($trackStock) {
                            addStockMovement($newProductId, 'initial', $quantity, 'initial', 'Création du produit', null, (int)($_SESSION['user_id'] ?? 0) ?: null);
                        }
                        $pdo->commit();
                        $_SESSION['success'] = "Produit créé avec succès.";
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $_SESSION['error'] = "Impossible de créer le produit. Vérifiez les informations saisies.";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $_SESSION['error'] = $e->getMessage();
                    }
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
                $existingProduct = fetchAssoc("
                    SELECT p.id, p.quantity, COALESCE(c.track_stock, 0) AS track_stock
                    FROM products p
                    LEFT JOIN categories c ON c.id = p.category_id
                    WHERE p.id = ? AND p.active = 1
                ", [$id]);
                if (empty($existingProduct)) {
                    $_SESSION['error'] = "Produit introuvable.";
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $oldTrackStock = (int)$existingProduct['track_stock'] === 1;
                $oldQuantity = (int)$existingProduct['quantity'];
                $name = trim($_POST['name'] ?? '');
                $price = (float)($_POST['price'] ?? 0);
                $description = trim($_POST['description'] ?? '');
                $categoryRaw = trim((string)($_POST['category_id'] ?? ''));
                $category_id = (int)$categoryRaw;
                $catExists = $category_id > 0 ? fetchAssoc("SELECT id, track_stock FROM categories WHERE id = ?", [$category_id]) : [];
                if (empty($catExists)) {
                    $_SESSION['error'] = "La catégorie est obligatoire. Veuillez en choisir une dans la liste.";
                    header('Location: ' . url('inventory/edit?id=' . $id));
                    exit;
                }
                $unit = $_POST['unit'] ?? 'pcs';
                $trackStock = (int)($catExists['track_stock'] ?? 0) === 1;
                $min_stock = $trackStock ? max(0, (int)($_POST['min_stock'] ?? 0)) : 0;
                $quantity = $trackStock ? max(0, (int)($_POST['quantity'] ?? 0)) : 0;
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
                    $pdo = $GLOBALS['pdo'];
                    $pdo->beginTransaction();
                    try {
                        query("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, unit = ?, min_stock = ?, quantity = ?, image = ?, updated_at = NOW() WHERE id = ?", [$name, $description, $price, $category_id, $unit, $min_stock, $quantity, $image, $id]);

                        $movementUserId = (int)($_SESSION['user_id'] ?? 0) ?: null;
                        if ($oldTrackStock) {
                            ensureInitialStockMovement($id, $oldQuantity, 'Stock initial', $movementUserId);
                            if ($trackStock) {
                                $delta = $quantity - $oldQuantity;
                                if ($delta > 0) {
                                    addStockMovement($id, 'in', $delta, 'manual', 'Ajustement manuel du stock', null, $movementUserId);
                                } elseif ($delta < 0) {
                                    addStockMovement($id, 'out', abs($delta), 'manual', 'Ajustement manuel du stock', null, $movementUserId);
                                }
                            } elseif ($oldQuantity > 0) {
                                addStockMovement($id, 'out', $oldQuantity, 'tracking_disabled', 'Produit déplacé vers une catégorie non suivie', null, $movementUserId);
                            }
                        } elseif ($trackStock) {
                            $hasMovement = fetchAssoc("SELECT id FROM stock_movements WHERE product_id = ? LIMIT 1", [$id]);
                            if (empty($hasMovement)) {
                                addStockMovement($id, 'initial', $quantity, 'tracking_enabled', 'Activation du suivi pour ce produit', null, $movementUserId);
                            } elseif ($quantity > 0) {
                                addStockMovement($id, 'in', $quantity, 'tracking_enabled', 'Activation du suivi pour ce produit', null, $movementUserId);
                            }
                        }

                        $pdo->commit();
                        $_SESSION['success'] = "Produit modifié avec succès.";
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $_SESSION['error'] = "Impossible de modifier le produit. Vérifiez les informations saisies.";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $_SESSION['error'] = $e->getMessage();
                    }
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
                $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = query("UPDATE products SET active = 0, updated_at = NOW() WHERE id = ? AND active = 1", [$id]);
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['success'] = "Produit supprimé avec succès.";
                    } else {
                        $_SESSION['error'] = "Produit introuvable ou déjà supprimé.";
                    }
                } else {
                    $_SESSION['error'] = "Produit introuvable.";
                }
                header('Location: ' . url('inventory'));
                exit;

            case 'bulk-destroy':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('inventory'));
                    exit;
                }
                $ids = array_values(array_filter(array_map('intval', (array)($_POST['ids'] ?? [])), fn($id) => $id > 0));
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = query("UPDATE products SET active = 0, updated_at = NOW() WHERE id IN ($placeholders) AND active = 1", $ids);
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['success'] = "Produit(s) supprimé(s) avec succès.";
                    } else {
                        $_SESSION['error'] = "Aucun produit à supprimer (introuvable ou déjà supprimé).";
                    }
                } else {
                    $_SESSION['error'] = "Aucun produit sélectionné.";
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
                $track_stock = isset($_POST['track_stock']) ? 1 : 0;
                if ($name === '') { $_SESSION['error'] = "Le nom est obligatoire."; header('Location: ' . url('categories')); exit; }
                if ($id > 0) {
                    $existing = fetchAssoc("SELECT id FROM categories WHERE name = ? AND id != ?", [$name, $id]);
                    if ($existing) { $_SESSION['error'] = "Une categorie avec ce nom existe deja."; header('Location: ' . url('categories/edit?id=' . $id)); exit; }
                    $oldCategory = fetchAssoc("SELECT id, track_stock FROM categories WHERE id = ?", [$id]);
                    if (empty($oldCategory)) { $_SESSION['error'] = "Categorie introuvable."; header('Location: ' . url('categories')); exit; }
                    $oldTrackStock = (int)$oldCategory['track_stock'] === 1;
                    $categoryUserId = (int)($_SESSION['user_id'] ?? 0) ?: null;
                    $pdo = $GLOBALS['pdo'];
                    $pdo->beginTransaction();
                    try {
                        query("UPDATE categories SET name = ?, description = ?, track_stock = ?, updated_at = NOW() WHERE id = ?", [$name, $description, $track_stock, $id]);

                        if ($track_stock === 0) {
                            $categoryProducts = fetchAllAssoc("SELECT id, quantity FROM products WHERE category_id = ?", [$id]);
                            if ($oldTrackStock) {
                                foreach ($categoryProducts as $categoryProduct) {
                                    $productQuantity = (int)$categoryProduct['quantity'];
                                    ensureInitialStockMovement((int)$categoryProduct['id'], $productQuantity, 'Stock initial', $categoryUserId);
                                    if ($productQuantity > 0) {
                                        addStockMovement((int)$categoryProduct['id'], 'out', $productQuantity, 'tracking_disabled', 'Désactivation du suivi de la catégorie', null, $categoryUserId);
                                    }
                                }
                            }
                            query("UPDATE products SET quantity = 0, min_stock = 0, updated_at = NOW() WHERE category_id = ?", [$id]);
                        } elseif (!$oldTrackStock) {
                            $categoryProducts = fetchAllAssoc("SELECT id, quantity FROM products WHERE category_id = ?", [$id]);
                            foreach ($categoryProducts as $categoryProduct) {
                                ensureInitialStockMovement((int)$categoryProduct['id'], (int)$categoryProduct['quantity'], 'Activation du suivi de la catégorie', $categoryUserId);
                            }
                        }

                        $pdo->commit();
                        $_SESSION['success'] = "Categorie modifiee.";
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        $_SESSION['error'] = "Impossible de modifier la categorie.";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $_SESSION['error'] = $e->getMessage();
                    }
                } else {
                    $existing = fetchAssoc("SELECT id FROM categories WHERE name = ?", [$name]);
                    if ($existing) { $_SESSION['error'] = "Une categorie avec ce nom existe deja."; header('Location: ' . url('categories/create')); exit; }
                    query("INSERT INTO categories (name, description, track_stock, created_at) VALUES (?, ?, ?, NOW())", [$name, $description, $track_stock]);
                    $_SESSION['success'] = "Categorie creee.";
                }
                header('Location: ' . url('categories'));
                exit;
            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . url('categories')); exit; }
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $category = fetchAssoc("SELECT id, track_stock FROM categories WHERE id = ?", [$id]);
                    if (empty($category)) {
                        $_SESSION['error'] = "Categorie introuvable.";
                    } else {
                        $count = fetchAssoc("SELECT COUNT(*) as c FROM products WHERE category_id = ? AND active = 1", [$id]);
                        if (($count['c'] ?? 0) > 0) {
                            $_SESSION['error'] = "Impossible de supprimer : des produits sont associes a cette categorie.";
                        } else {
                            $categoryUserId = (int)($_SESSION['user_id'] ?? 0) ?: null;
                            $pdo = $GLOBALS['pdo'];
                            $pdo->beginTransaction();
                            try {
                                if ((int)$category['track_stock'] === 1) {
                                    $categoryProducts = fetchAllAssoc("SELECT id, quantity FROM products WHERE category_id = ?", [$id]);
                                    foreach ($categoryProducts as $categoryProduct) {
                                        $productQuantity = (int)$categoryProduct['quantity'];
                                        ensureInitialStockMovement((int)$categoryProduct['id'], $productQuantity, 'Stock initial', $categoryUserId);
                                        if ($productQuantity > 0) {
                                            addStockMovement((int)$categoryProduct['id'], 'out', $productQuantity, 'tracking_disabled', 'Suppression de la catégorie', null, $categoryUserId);
                                        }
                                    }
                                }
                                query("UPDATE products SET quantity = 0, min_stock = 0, updated_at = NOW() WHERE category_id = ?", [$id]);
                                $stmt = query("DELETE FROM categories WHERE id = ?", [$id]);
                                if ($stmt->rowCount() > 0) {
                                    $pdo->commit();
                                    $_SESSION['success'] = "Categorie supprimee.";
                                } else {
                                    $pdo->rollBack();
                                    $_SESSION['error'] = "Categorie introuvable.";
                                }
                            } catch (PDOException $e) {
                                $pdo->rollBack();
                                $_SESSION['error'] = "Impossible de supprimer la categorie.";
                            } catch (Exception $e) {
                                $pdo->rollBack();
                                $_SESSION['error'] = $e->getMessage();
                            }
                        }
                    }
                } else {
                    $_SESSION['error'] = "Categorie introuvable.";
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
            case 'edit':
                $_GET['id'] = (int)($_GET['id'] ?? ($pageParams[1] ?? 0));
                require __DIR__ . '/../pages/admin/sale_edit.php';
                break;
            case 'delete':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('sales'));
                    exit;
                }
                $saleId = (int)($_POST['id'] ?? 0);
                if ($saleId <= 0) {
                    $_SESSION['error'] = "Vente introuvable.";
                    header('Location: ' . url('sales'));
                    exit;
                }
                $pdo = $GLOBALS['pdo'];
                $pdo->beginTransaction();
                try {
                    $sale = fetchAssoc("SELECT id FROM sales WHERE id = ? FOR UPDATE", [$saleId]);
                    if (empty($sale)) {
                        throw new Exception("Vente introuvable.");
                    }
                    // Contribution de cette vente au rapport journalier (POS uniquement).
                    // Elle doit être retirée du rapport en même temps que la vente.
                    $reportContribution = fetchAssoc(
                        "SELECT user_id, report_date, prepaye, trosa, paiement, nofy FROM sale_report_contributions WHERE sale_id = ?",
                        [$saleId]
                    );
                    // Remettre en stock les quantités vendues avant de supprimer la vente,
                    // uniquement pour les produits dont la catégorie est suivie.
                    $soldItems = fetchAllAssoc("
                        SELECT si.product_id, si.quantity, p.quantity AS current_quantity, COALESCE(c.track_stock, 0) AS track_stock
                        FROM sale_items si
                        LEFT JOIN products p ON p.id = si.product_id
                        LEFT JOIN categories c ON c.id = p.category_id
                        WHERE si.sale_id = ?
                        FOR UPDATE
                    ", [$saleId]);
                    $saleUserId = (int)($_SESSION['user_id'] ?? 0) ?: null;
                    foreach ($soldItems as $soldItem) {
                        if ((int)$soldItem['track_stock'] === 1) {
                            $soldProductId = (int)$soldItem['product_id'];
                            $soldQuantity = (int)$soldItem['quantity'];
                            ensureInitialStockMovement($soldProductId, (int)($soldItem['current_quantity'] ?? 0), 'Stock initial', $saleUserId);
                            query("UPDATE products SET quantity = quantity + ? WHERE id = ?", [$soldQuantity, $soldProductId]);
                            addStockMovement($soldProductId, 'in', $soldQuantity, 'sale_delete', 'Suppression vente #' . $saleId, $saleId, $saleUserId);
                        }
                    }
                    // Reverser la contribution de la vente dans le rapport journalier.
                    // GREATEST évite un montant négatif si le rapport a été corrigé manuellement.
                    if (!empty($reportContribution)) {
                        query(
                            "UPDATE daily_reports
                             SET prepaye = GREATEST(prepaye - ?, 0),
                                 trosa = GREATEST(trosa - ?, 0),
                                 paiement = GREATEST(paiement - ?, 0),
                                 nofy = GREATEST(nofy - ?, 0)
                             WHERE user_id = ? AND report_date = ?",
                            [
                                $reportContribution['prepaye'],
                                $reportContribution['trosa'],
                                $reportContribution['paiement'],
                                $reportContribution['nofy'],
                                $reportContribution['user_id'],
                                $reportContribution['report_date'],
                            ]
                        );
                        // Si le rapport ne contenait que cette vente, on supprime la ligne vide.
                        query(
                            "DELETE FROM daily_reports
                             WHERE user_id = ? AND report_date = ?
                               AND prepaye = 0 AND trosa = 0 AND paiement = 0 AND nofy = 0",
                            [$reportContribution['user_id'], $reportContribution['report_date']]
                        );
                    }
                    query("DELETE FROM sales WHERE id = ?", [$saleId]);
                    $pdo->commit();
                    $_SESSION['success'] = "Vente #$saleId supprimée. Le stock des produits suivis a été réajusté et le rapport journalier a été mis à jour.";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['error'] = $e->getMessage();
                }
                header('Location: ' . url('sales'));
                exit;
            case 'save-report':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
                    exit;
                }
                $reportDate = normalizeDate($_POST['date'] ?? null);
                if ($reportDate > date('Y-m-d')) {
                    $reportDate = date('Y-m-d');
                }
                $prepaye = max(0, (float)($_POST['prepaye'] ?? 0));
                $trosaVal = max(0, (float)($_POST['trosa'] ?? 0));
                $paiementVal = max(0, (float)($_POST['paiement'] ?? 0));
                $nofyVal = max(0, (float)($_POST['nofy'] ?? 0));
                query("INSERT INTO daily_reports (user_id, report_date, prepaye, trosa, paiement, nofy, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE prepaye = VALUES(prepaye), trosa = VALUES(trosa), paiement = VALUES(paiement), nofy = VALUES(nofy)", [$_SESSION['user_id'], $reportDate, $prepaye, $trosaVal, $paiementVal, $nofyVal]);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Rapport enregistré.']);
                exit;
            case 'entry-update':
            case 'entry-delete':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('sales'));
                    exit;
                }
                $entryType = (string)($_POST['type'] ?? '');
                $entryId = (int)($_POST['id'] ?? 0);
                $entryTypes = cashEntryTypes();
                $returnTo = (string)($_POST['return'] ?? 'sales');
                if (!preg_match('/^sales(\?[A-Za-z0-9_%&=.\-]*)?$/', $returnTo)) {
                    $returnTo = 'sales';
                }
                if ($entryId <= 0 || !isset($entryTypes[$entryType])) {
                    $_SESSION['error'] = "Mouvement de caisse introuvable.";
                    header('Location: ' . url($returnTo));
                    exit;
                }
                $entryConfig = $entryTypes[$entryType];
                $entryTable = $entryConfig['table'];
                $entryDateColumn = $entryConfig['date_column'];
                $entryLabel = $entryConfig['label'];
                $entryAgreement = $entryType === 'depense' ? 'e' : '';
                $entryExists = fetchAssoc(
                    "SELECT id FROM {$entryTable} WHERE id = ? AND user_id = ?",
                    [$entryId, $_SESSION['user_id']]
                );
                if (empty($entryExists)) {
                    $_SESSION['error'] = "Mouvement de caisse introuvable.";
                    header('Location: ' . url($returnTo));
                    exit;
                }

                if ($salesAction === 'entry-delete') {
                    query("DELETE FROM {$entryTable} WHERE id = ? AND user_id = ?", [$entryId, $_SESSION['user_id']]);
                    $_SESSION['success'] = $entryLabel . ' supprimé' . $entryAgreement . '.';
                    header('Location: ' . url($returnTo));
                    exit;
                }

                $entryAmount = max(0, (float)($_POST['amount'] ?? 0));
                $entryDate = normalizeDate($_POST['date'] ?? null);
                if ($entryAmount <= 0) {
                    $_SESSION['error'] = "Le montant doit être supérieur à 0.";
                    header('Location: ' . url($returnTo));
                    exit;
                }
                if ($entryDate > date('Y-m-d')) {
                    $entryDate = date('Y-m-d');
                }
                query(
                    "UPDATE {$entryTable} SET amount = ?, {$entryDateColumn} = ? WHERE id = ? AND user_id = ?",
                    [$entryAmount, $entryDate, $entryId, $_SESSION['user_id']]
                );
                $_SESSION['success'] = $entryLabel . ' modifié' . $entryAgreement . '.';
                header('Location: ' . url($returnTo));
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
                $entryDate = normalizeDate($_POST['date'] ?? null);
                if ($entryDate > date('Y-m-d')) {
                    $entryDate = date('Y-m-d');
                }
                query("INSERT INTO daily_deposits (user_id, amount, deposit_date, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $amount, $entryDate]);
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
                $entryDate = normalizeDate($_POST['date'] ?? null);
                if ($entryDate > date('Y-m-d')) {
                    $entryDate = date('Y-m-d');
                }
                query("INSERT INTO daily_relais (user_id, amount, relais_date, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $relaisAmount, $entryDate]);
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
                $entryDate = normalizeDate($_POST['date'] ?? null);
                if ($entryDate > date('Y-m-d')) {
                    $entryDate = date('Y-m-d');
                }
                query("INSERT INTO daily_paiements (user_id, amount, paiement_date, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $paiementAmount, $entryDate]);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Paiement de ' . number_format($paiementAmount, 0, ',', ' ') . ' Ar enregistré.']);
                exit;

            case 'checkout':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('pos'));
                    exit;
                }

                // Jour d'activité choisi dans /pos (aujourd'hui par défaut, jamais dans le futur)
                $saleDate = normalizeDate($_POST['sale_date'] ?? null);
                if ($saleDate > date('Y-m-d')) {
                    $saleDate = date('Y-m-d');
                }
                $redirectAfterCheckout = url('pos') . ($saleDate !== date('Y-m-d') ? '?date=' . urlencode($saleDate) : '');

                $items = $_POST['items'] ?? [];
                $total = 0;
                $validItems = [];

                if (empty($items)) {
                    $_SESSION['error'] = "Le panier est vide.";
                    header('Location: ' . $redirectAfterCheckout);
                    exit;
                }

                // Valider et préparer les articles (une vente peut mixer les moyens :
                // plusieurs lignes pour le même produit, ex. Café ×3 cash + ×2 prépayé)
                $payTotals = ['prepaye' => 0, 'trosa' => 0, 'cash' => 0, 'mobile' => 0];
                $qtyByProduct = [];
                foreach ($items as $item) {
                    $product_id = (int)($item['product_id'] ?? 0);
                    $qty = (int)($item['quantity'] ?? 0);
                    if ($product_id <= 0 || $qty <= 0) continue;
                    $qtyByProduct[$product_id] = ($qtyByProduct[$product_id] ?? 0) + $qty;
                }
                foreach ($items as $item) {
                    $product_id = (int)($item['product_id'] ?? 0);
                    $qty = (int)($item['quantity'] ?? 0);

                    if ($product_id <= 0 || $qty <= 0) continue;

                    $product = fetchAssoc("
                        SELECT p.id, p.name, p.price, p.quantity, p.active, COALESCE(c.track_stock, 0) AS track_stock
                        FROM products p
                        LEFT JOIN categories c ON c.id = p.category_id
                        WHERE p.id = ?
                    ", [$product_id]);

                    if (!$product || !$product['active']) {
                        $_SESSION['error'] = "Produit #$product_id indisponible.";
                        header('Location: ' . $redirectAfterCheckout);
                        exit;
                    }
                    // Stock contrôlé sur le cumul du produit (toutes méthodes confondues),
                    // uniquement pour les catégories avec suivi de stock.
                    if ((int)$product['track_stock'] === 1 && $product['quantity'] < $qtyByProduct[$product_id]) {
                        $_SESSION['error'] = "Stock insuffisant pour \"" . $product['name'] . "\" (demandé : " . $qtyByProduct[$product_id] . ", disponible : " . $product['quantity'] . ").";
                        header('Location: ' . $redirectAfterCheckout);
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
                        'track_stock' => (int)$product['track_stock'],
                    ];
                }

                if (empty($validItems)) {
                    $_SESSION['error'] = "Aucun article valide.";
                    header('Location: ' . $redirectAfterCheckout);
                    exit;
                }

                // Transaction DB : créer la vente + décrémenter le stock
                // Le mode global de la vente est déduit des modes choisis ligne par ligne.
                $paymentMethod = deriveSalePaymentMethod(array_column($validItems, 'pay_method'));
                $versement = max(0, (float)($_POST['versement'] ?? 0));
                $saleUserId = (int)($_SESSION['user_id'] ?? 0) ?: null;
                $pdo = $GLOBALS['pdo'];
                $pdo->beginTransaction();
                try {
                    // Créer la vente à la date d'activité choisie (heure de saisie conservée)
                    $saleDateTime = $saleDate . ' ' . date('H:i:s');
                    query("INSERT INTO sales (user_id, total, payment_method, created_at) VALUES (?, ?, ?, ?)", [$_SESSION['user_id'], $total, $paymentMethod, $saleDateTime]);
                    $saleId = $pdo->lastInsertId();

                    foreach ($validItems as $item) {
                        // Créer la ligne de vente (avec son moyen de paiement)
                        query("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, pay_method) VALUES (?, ?, ?, ?, ?)", [$saleId, $item['product_id'], $item['quantity'], $item['unit_price'], $item['pay_method']]);

                        // Décrémenter le stock uniquement pour les produits suivis
                        if ((int)($item['track_stock'] ?? 0) === 1) {
                            $movementProductId = (int)($item['product_id'] ?? 0);
                            $movementQuantity = (int)($item['quantity'] ?? 0);
                            ensureInitialStockMovement($movementProductId, 0, 'Stock initial', $saleUserId);
                            query("UPDATE products SET quantity = quantity - ? WHERE id = ?", [$movementQuantity, $movementProductId]);
                            addStockMovement($movementProductId, 'out', $movementQuantity, 'sale', 'Nouvelle vente #' . $saleId, $saleId, $saleUserId);
                        }
                    }

                    // Enregistrer le versement du jour choisi
                    if ($versement > 0) {
                        query("INSERT INTO daily_deposits (user_id, amount, deposit_date, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $versement, $saleDate]);
                    }

                    // Ventiler la vente dans le rapport du jour choisi (additif, même date que l'affichage /pos)
                    $reportDate = $saleDate;
                    query("INSERT INTO daily_reports (user_id, report_date, prepaye, trosa, paiement, nofy, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE prepaye = prepaye + VALUES(prepaye), trosa = trosa + VALUES(trosa), paiement = paiement + VALUES(paiement), nofy = nofy + VALUES(nofy)", [$_SESSION['user_id'], $reportDate, $payTotals['prepaye'], $payTotals['trosa'], $payTotals['cash'], $payTotals['mobile']]);

                    // Mémoriser la contribution exacte de cette vente au rapport,
                    // pour pouvoir la retirer si la vente est supprimée plus tard.
                    query(
                        "INSERT INTO sale_report_contributions (sale_id, user_id, report_date, prepaye, trosa, paiement, nofy) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$saleId, $_SESSION['user_id'], $reportDate, $payTotals['prepaye'], $payTotals['trosa'], $payTotals['cash'], $payTotals['mobile']]
                    );

                    $pdo->commit();
                    $methodLabel = salePaymentMethodLabel($paymentMethod);
                    $dateSuffix = $saleDate === date('Y-m-d') ? '' : ' pour le ' . date('d/m/Y', strtotime($saleDate));
                    $_SESSION['success'] = "Vente enregistrée (" . number_format($total, 0, ',', ' ') . " Ar — $methodLabel)$dateSuffix";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
                }

                header('Location: ' . $redirectAfterCheckout);
                exit;

            case 'historique':
                require __DIR__ . '/../pages/vendor/history.php';
                exit;

            case 'add_stock':
                if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrateur') {
                    header('Location: ' . url('pos'));
                    exit;
                }
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    header('Location: ' . url('pos'));
                    exit;
                }
                $productId = (int)($_POST['product_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 0);
                if ($productId <= 0 || $quantity <= 0) {
                    $_SESSION['error'] = "Données invalides.";
                    header('Location: ' . url('pos'));
                    exit;
                }
                $pdo = $GLOBALS['pdo'];
                $pdo->beginTransaction();
                try {
                    $product = fetchAssoc("
                        SELECT p.id, p.name, p.quantity, p.unit, COALESCE(c.track_stock, 0) AS track_stock
                        FROM products p
                        LEFT JOIN categories c ON c.id = p.category_id
                        WHERE p.id = ? AND p.active = 1
                        FOR UPDATE
                    ", [$productId]);
                    if (empty($product) || (int)$product['track_stock'] !== 1) {
                        throw new Exception("Produit introuvable ou non suivi.");
                    }
                    $userId = (int)($_SESSION['user_id'] ?? 0);
                    // S'assurer que la continuité démarre au stock actuel si aucun mouvement n'existe.
                    ensureInitialStockMovement($productId, (int)$product['quantity'], 'Stock initial', $userId);
                    $newQuantity = (int)$product['quantity'] + $quantity;
                    query("UPDATE products SET quantity = ?, updated_at = NOW() WHERE id = ?", [$newQuantity, $productId]);
                    addStockMovement($productId, 'in', $quantity, 'pos_add', 'Ajout de stock via POS', null, $userId);
                    $pdo->commit();
                    $unitLabel = trim((string)($product['unit'] ?? ''));
                    $quantityLabel = $quantity . ($unitLabel !== '' ? ' ' . $unitLabel : '');
                    $_SESSION['success'] = "Stock de " . $quantityLabel . " ajouté à " . $product['name'] . " (stock final : " . $newQuantity . ").";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['error'] = $e->getMessage();
                }
                header('Location: ' . url('pos'));
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
