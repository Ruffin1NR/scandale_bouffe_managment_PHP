<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . url($_SESSION['user_role'] === 'administrateur' ? 'dashboard' : 'vendeur'));
    exit;
}

// POST handler (from modal or standalone form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $user = fetchAssoc("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles_users ru ON ru.user_id = u.id LEFT JOIN roles r ON r.id = ru.role_id WHERE u.email = ?", [$email]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role_name'] ?? 'vendeur';
        $_SESSION['login_time'] = time();
        header('Location: ' . url(($user['role_name'] ?? '') === 'administrateur' ? 'dashboard' : 'vendeur'));
        exit;
    } else {
        $_SESSION['auth_error'] = "Identifiants incorrects. Veuillez réessayer.";
        header('Location: ' . url('?auth=login'));
        exit;
    }
}

// GET request — redirect to home with modal
header('Location: ' . url('?auth=login'));
exit;
