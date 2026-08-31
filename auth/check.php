<?php
/**
 * Check d'authentification - Scandale Bouffe PHP Pur
 * Fichier inclus par les pages protégées pour vérifier la session
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('login'));
    exit;
}

// NB : le contrôle des rôles (administrateur/vendeur) est géré par le
// front controller (public/index.php) selon la route demandée.