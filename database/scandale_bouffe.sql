-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 17 sep. 2026 à 13:15
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `scandale_bouffe`
--

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(5, 'Epicerie', 'Articles de prix fixe', NULL, NULL),
(6, 'Revente', 'Articles de revente', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `daily_deposits`
--

CREATE TABLE `daily_deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `daily_deposits`
--

INSERT INTO `daily_deposits` (`id`, `user_id`, `amount`, `created_at`) VALUES
(1, 1, 100000.00, '2026-09-16 18:07:05'),
(2, 1, 10000.00, '2026-09-16 19:49:32'),
(3, 1, 10000.00, '2026-09-16 19:50:56'),
(4, 1, 10000.00, '2026-09-16 20:34:38'),
(5, 1, 10000.00, '2026-09-16 20:47:45'),
(6, 1, 20000.00, '2026-09-16 20:49:19'),
(7, 1, 2000.00, '2026-09-17 13:23:23');

-- --------------------------------------------------------

--
-- Structure de la table `daily_reports`
--

CREATE TABLE `daily_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `prepaye` decimal(12,2) NOT NULL DEFAULT 0.00,
  `trosa` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paiement` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nofy` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `daily_reports`
--

INSERT INTO `daily_reports` (`id`, `user_id`, `report_date`, `prepaye`, `trosa`, `paiement`, `nofy`, `created_at`) VALUES
(1, 1, '2026-09-16', 0.00, 10000.00, 0.00, 0.00, '2026-09-16 19:41:02'),
(3, 1, '2026-09-17', 0.00, 1700.00, 0.00, 0.00, '2026-09-17 13:23:23');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(7, '0001_01_01_000000_create_users_table', 1),
(8, '0001_01_01_000001_create_cache_table', 1),
(9, '0001_01_01_000002_create_jobs_table', 1),
(10, '2026_08_12_103017_create_products_table', 1),
(11, '2026_08_12_164131_create_roles_table', 1),
(12, '2026_08_12_164156_create_roles_users_table', 1),
(13, '2026_08_13_100000_create_sales_table', 2),
(14, '2026_08_13_100001_create_sale_items_table', 2),
(15, '2026_08_13_110000_create_categories_table', 3),
(16, '2026_08_13_110001_add_category_id_to_products_table', 3);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 5,
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `description`, `price`, `quantity`, `min_stock`, `unit`, `image`, `active`, `created_at`, `updated_at`) VALUES
(57, 'Sachet', 5, NULL, 300.00, 77, 50, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(58, 'Cache bouche', 5, NULL, 500.00, 159, 50, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(59, 'Mouchoir', 5, NULL, 700.00, 55, 50, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(60, 'Cahier 100p PF', 5, NULL, 1700.00, 0, 20, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(61, 'Cahier 100p GF', 5, NULL, 3300.00, 23, 20, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(62, 'Cahier 200p PF', 5, NULL, 3400.00, 30, 20, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(63, 'Cahier 200p GF', 5, NULL, 5500.00, 0, 20, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(64, 'Stylo', 5, NULL, 800.00, 178, 50, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(65, 'Lion', 5, NULL, 1500.00, 41, 30, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(66, 'Doritos PM', 5, NULL, 2000.00, 48, 20, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(67, 'Doritos GM', 5, NULL, 3000.00, 74, 20, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(68, 'Salto Chips', 5, NULL, 800.00, 35, 30, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(69, 'Valopy', 5, NULL, 200.00, 188, 50, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(70, 'Yaourt', 6, NULL, 1000.00, 18, 30, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(71, 'Yaourt a boire', 6, NULL, 1500.00, 37, 30, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(72, 'Gauffre', 6, NULL, 1000.00, 24, 30, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(73, 'Flan', 6, NULL, 1000.00, 31, 30, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(74, 'Miel', 6, NULL, 7000.00, 11, 10, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(75, 'Tsam PM', 6, NULL, 200.00, 71, 50, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL),
(76, 'Tsam GM', 6, NULL, 500.00, 92, 50, 'pcs', NULL, 1, '2026-08-30 19:53:11', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'administrateur', 'Rôle avec droits complets sur le système', '2026-08-12 14:02:52', '2026-08-12 14:02:52'),
(2, 'vendeur', 'Rôle pour gérer les ventes et le stock', '2026-08-12 14:02:52', '2026-08-12 14:02:52');

-- --------------------------------------------------------

--
-- Structure de la table `roles_users`
--

CREATE TABLE `roles_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles_users`
--

INSERT INTO `roles_users` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 6, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL DEFAULT 'cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `total`, `created_at`, `updated_at`, `payment_method`) VALUES
(13, 1, 15800.00, '2026-08-03 06:15:00', NULL, 'cash'),
(14, 1, 9200.00, '2026-08-03 11:30:00', NULL, 'cash'),
(15, 1, 22500.00, '2026-08-04 07:00:00', NULL, 'credit'),
(16, 1, 7300.00, '2026-08-04 13:20:00', NULL, 'mobile_money'),
(17, 1, 31000.00, '2026-08-05 05:45:00', NULL, 'cash'),
(18, 1, 11600.00, '2026-08-05 10:10:00', NULL, 'credit'),
(19, 1, 18900.00, '2026-08-06 06:30:00', NULL, 'mobile_money'),
(20, 1, 5600.00, '2026-08-06 12:00:00', NULL, 'cash'),
(21, 1, 27300.00, '2026-08-07 07:15:00', NULL, 'credit'),
(22, 1, 8400.00, '2026-08-07 14:00:00', NULL, 'cash'),
(23, 1, 14700.00, '2026-08-08 06:00:00', NULL, 'mobile_money'),
(24, 1, 3500.00, '2026-08-08 09:30:00', NULL, 'cash'),
(25, 1, 19800.00, '2026-08-10 05:30:00', NULL, 'cash'),
(26, 1, 6200.00, '2026-08-10 11:45:00', NULL, 'mobile_money'),
(27, 1, 41500.00, '2026-08-11 06:00:00', NULL, 'credit'),
(28, 1, 12300.00, '2026-08-11 13:00:00', NULL, 'cash'),
(29, 1, 8900.00, '2026-08-12 07:30:00', NULL, 'cash'),
(30, 1, 25600.00, '2026-08-12 10:00:00', NULL, 'mobile_money'),
(31, 1, 15200.00, '2026-08-13 06:15:00', NULL, 'credit'),
(32, 1, 4100.00, '2026-08-13 12:30:00', NULL, 'cash'),
(33, 1, 33800.00, '2026-08-14 05:45:00', NULL, 'cash'),
(34, 1, 9700.00, '2026-08-14 11:00:00', NULL, 'mobile_money'),
(35, 1, 21400.00, '2026-08-15 06:30:00', NULL, 'cash'),
(36, 1, 6800.00, '2026-08-15 13:15:00', NULL, 'cash'),
(37, 1, 28700.00, '2026-08-17 06:00:00', NULL, 'credit'),
(38, 1, 11100.00, '2026-08-17 10:45:00', NULL, 'mobile_money'),
(39, 1, 36500.00, '2026-08-18 05:30:00', NULL, 'cash'),
(40, 1, 7600.00, '2026-08-18 12:00:00', NULL, 'cash'),
(41, 1, 19300.00, '2026-08-19 07:00:00', NULL, 'credit'),
(42, 1, 13400.00, '2026-08-19 11:30:00', NULL, 'cash'),
(43, 1, 44200.00, '2026-08-20 06:15:00', NULL, 'mobile_money'),
(44, 1, 5900.00, '2026-08-20 13:00:00', NULL, 'cash'),
(45, 1, 24600.00, '2026-08-21 05:45:00', NULL, 'credit'),
(46, 1, 10500.00, '2026-08-21 10:00:00', NULL, 'cash'),
(47, 1, 17800.00, '2026-08-22 06:00:00', NULL, 'mobile_money'),
(48, 1, 4800.00, '2026-08-22 09:15:00', NULL, 'cash'),
(49, 1, 32100.00, '2026-08-24 06:30:00', NULL, 'cash'),
(50, 1, 8200.00, '2026-08-24 12:30:00', NULL, 'cash'),
(51, 1, 26800.00, '2026-08-25 05:45:00', NULL, 'cash'),
(52, 1, 14500.00, '2026-08-25 11:00:00', NULL, 'cash'),
(53, 1, 38900.00, '2026-08-26 06:00:00', NULL, 'cash'),
(54, 1, 6300.00, '2026-08-26 13:15:00', NULL, 'cash'),
(55, 1, 21700.00, '2026-08-27 07:30:00', NULL, 'cash'),
(56, 1, 9800.00, '2026-08-27 10:30:00', NULL, 'cash'),
(57, 1, 45300.00, '2026-08-28 05:30:00', NULL, 'cash'),
(58, 1, 12900.00, '2026-08-28 12:00:00', NULL, 'cash'),
(59, 1, 16400.00, '2026-08-29 06:15:00', NULL, 'cash'),
(60, 1, 7500.00, '2026-08-29 09:45:00', NULL, 'cash'),
(61, 1, 12400.00, '2026-08-31 15:23:21', NULL, 'credit'),
(62, 1, 4000.00, '2026-08-31 15:25:26', NULL, 'mobile_money'),
(63, 1, 7200.00, '2026-09-01 18:11:16', NULL, 'cash'),
(64, 1, 5000.00, '2026-09-01 18:13:05', NULL, 'credit'),
(65, 1, 2000.00, '2026-09-16 14:01:19', NULL, 'cash'),
(66, 1, 5200.00, '2026-09-16 14:21:43', NULL, 'cash'),
(67, 1, 1500.00, '2026-09-16 14:37:29', NULL, 'cash'),
(68, 1, 7600.00, '2026-09-16 15:06:01', NULL, 'cash'),
(69, 1, 5600.00, '2026-09-16 15:06:57', NULL, 'cash'),
(70, 1, 3300.00, '2026-09-16 17:34:20', NULL, 'cash'),
(71, 1, 21000.00, '2026-09-16 17:49:19', NULL, 'cash'),
(72, 1, 40800.00, '2026-09-16 17:55:27', NULL, 'cash'),
(73, 1, 9700.00, '2026-09-17 10:23:09', NULL, 'cash');

-- --------------------------------------------------------

--
-- Structure de la table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `created_at`, `updated_at`) VALUES
(19, 13, 57, 'Sachet', 5, 300.00, NULL, NULL),
(20, 13, 66, 'Doritos PM', 3, 2000.00, NULL, NULL),
(21, 13, 70, 'Yaourt', 2, 1000.00, NULL, NULL),
(22, 13, 64, 'Stylo', 2, 800.00, NULL, NULL),
(23, 14, 60, 'Cahier 100p PF', 2, 1700.00, NULL, NULL),
(24, 14, 71, 'Yaourt a boire', 2, 1500.00, NULL, NULL),
(25, 14, 68, 'Salto Chips', 3, 800.00, NULL, NULL),
(26, 15, 63, 'Cahier 200p GF', 2, 5500.00, NULL, NULL),
(27, 15, 67, 'Doritos GM', 2, 3000.00, NULL, NULL),
(28, 15, 74, 'Miel', 1, 7000.00, NULL, NULL),
(29, 16, 58, 'Cache bouche', 4, 500.00, NULL, NULL),
(30, 16, 65, 'Lion', 2, 1500.00, NULL, NULL),
(31, 16, 72, 'Gauffre', 1, 1000.00, NULL, NULL),
(32, 17, 63, 'Cahier 200p GF', 3, 5500.00, NULL, NULL),
(33, 17, 61, 'Cahier 100p GF', 2, 3300.00, NULL, NULL),
(34, 17, 67, 'Doritos GM', 1, 3000.00, NULL, NULL),
(35, 17, 70, 'Yaourt', 3, 1000.00, NULL, NULL),
(36, 18, 62, 'Cahier 200p PF', 2, 3400.00, NULL, NULL),
(37, 18, 64, 'Stylo', 3, 800.00, NULL, NULL),
(38, 18, 75, 'Tsam PM', 5, 200.00, NULL, NULL),
(39, 19, 67, 'Doritos GM', 3, 3000.00, NULL, NULL),
(40, 19, 61, 'Cahier 100p GF', 2, 3300.00, NULL, NULL),
(41, 19, 73, 'Flan', 3, 1000.00, NULL, NULL),
(42, 20, 59, 'Mouchoir', 4, 700.00, NULL, NULL),
(43, 20, 68, 'Salto Chips', 2, 800.00, NULL, NULL),
(44, 20, 76, 'Tsam GM', 2, 500.00, NULL, NULL),
(45, 21, 63, 'Cahier 200p GF', 3, 5500.00, NULL, NULL),
(46, 21, 66, 'Doritos PM', 2, 2000.00, NULL, NULL),
(47, 21, 60, 'Cahier 100p PF', 1, 1700.00, NULL, NULL),
(48, 22, 70, 'Yaourt', 4, 1000.00, NULL, NULL),
(49, 22, 71, 'Yaourt a boire', 2, 1500.00, NULL, NULL),
(50, 22, 72, 'Gauffre', 1, 1000.00, NULL, NULL),
(51, 23, 62, 'Cahier 200p PF', 2, 3400.00, NULL, NULL),
(52, 23, 65, 'Lion', 3, 1500.00, NULL, NULL),
(53, 23, 64, 'Stylo', 2, 800.00, NULL, NULL),
(54, 24, 57, 'Sachet', 5, 300.00, NULL, NULL),
(55, 24, 75, 'Tsam PM', 5, 200.00, NULL, NULL),
(56, 24, 69, 'Valopy', 5, 200.00, NULL, NULL),
(57, 25, 63, 'Cahier 200p GF', 2, 5500.00, NULL, NULL),
(58, 25, 67, 'Doritos GM', 2, 3000.00, NULL, NULL),
(59, 25, 74, 'Miel', 1, 7000.00, NULL, NULL),
(60, 26, 58, 'Cache bouche', 4, 500.00, NULL, NULL),
(61, 26, 59, 'Mouchoir', 3, 700.00, NULL, NULL),
(62, 26, 68, 'Salto Chips', 2, 800.00, NULL, NULL),
(63, 27, 63, 'Cahier 200p GF', 4, 5500.00, NULL, NULL),
(64, 27, 61, 'Cahier 100p GF', 2, 3300.00, NULL, NULL),
(65, 27, 66, 'Doritos PM', 3, 2000.00, NULL, NULL),
(66, 27, 74, 'Miel', 1, 7000.00, NULL, NULL),
(67, 28, 60, 'Cahier 100p PF', 3, 1700.00, NULL, NULL),
(68, 28, 70, 'Yaourt', 3, 1000.00, NULL, NULL),
(69, 28, 72, 'Gauffre', 2, 1000.00, NULL, NULL),
(70, 29, 65, 'Lion', 3, 1500.00, NULL, NULL),
(71, 29, 64, 'Stylo', 2, 800.00, NULL, NULL),
(72, 29, 71, 'Yaourt a boire', 1, 1500.00, NULL, NULL),
(73, 30, 63, 'Cahier 200p GF', 3, 5500.00, NULL, NULL),
(74, 30, 62, 'Cahier 200p PF', 2, 3400.00, NULL, NULL),
(75, 30, 67, 'Doritos GM', 1, 3000.00, NULL, NULL),
(76, 31, 61, 'Cahier 100p GF', 2, 3300.00, NULL, NULL),
(77, 31, 60, 'Cahier 100p PF', 2, 1700.00, NULL, NULL),
(78, 31, 66, 'Doritos PM', 2, 2000.00, NULL, NULL),
(79, 31, 68, 'Salto Chips', 1, 800.00, NULL, NULL),
(80, 32, 57, 'Sachet', 3, 300.00, NULL, NULL),
(81, 32, 75, 'Tsam PM', 4, 200.00, NULL, NULL),
(82, 32, 69, 'Valopy', 5, 200.00, NULL, NULL),
(83, 33, 63, 'Cahier 200p GF', 3, 5500.00, NULL, NULL),
(84, 33, 67, 'Doritos GM', 3, 3000.00, NULL, NULL),
(85, 33, 74, 'Miel', 1, 7000.00, NULL, NULL),
(86, 34, 59, 'Mouchoir', 3, 700.00, NULL, NULL),
(87, 34, 70, 'Yaourt', 3, 1000.00, NULL, NULL),
(88, 34, 73, 'Flan', 2, 1000.00, NULL, NULL),
(89, 35, 62, 'Cahier 200p PF', 3, 3400.00, NULL, NULL),
(90, 35, 65, 'Lion', 2, 1500.00, NULL, NULL),
(91, 35, 61, 'Cahier 100p GF', 1, 3300.00, NULL, NULL),
(92, 36, 58, 'Cache bouche', 4, 500.00, NULL, NULL),
(93, 36, 64, 'Stylo', 3, 800.00, NULL, NULL),
(94, 36, 76, 'Tsam GM', 2, 500.00, NULL, NULL),
(95, 37, 63, 'Cahier 200p GF', 3, 5500.00, NULL, NULL),
(96, 37, 66, 'Doritos PM', 2, 2000.00, NULL, NULL),
(97, 37, 67, 'Doritos GM', 1, 3000.00, NULL, NULL),
(98, 37, 70, 'Yaourt', 3, 1000.00, NULL, NULL),
(99, 38, 60, 'Cahier 100p PF', 2, 1700.00, NULL, NULL),
(100, 38, 65, 'Lion', 2, 1500.00, NULL, NULL),
(101, 38, 71, 'Yaourt a boire', 2, 1500.00, NULL, NULL),
(102, 38, 72, 'Gauffre', 1, 1000.00, NULL, NULL),
(103, 39, 63, 'Cahier 200p GF', 4, 5500.00, NULL, NULL),
(104, 39, 61, 'Cahier 100p GF', 2, 3300.00, NULL, NULL),
(105, 39, 74, 'Miel', 1, 7000.00, NULL, NULL),
(106, 40, 59, 'Mouchoir', 3, 700.00, NULL, NULL),
(107, 40, 68, 'Salto Chips', 4, 800.00, NULL, NULL),
(108, 40, 75, 'Tsam PM', 3, 200.00, NULL, NULL),
(109, 41, 62, 'Cahier 200p PF', 2, 3400.00, NULL, NULL),
(110, 41, 67, 'Doritos GM', 2, 3000.00, NULL, NULL),
(111, 41, 66, 'Doritos PM', 1, 2000.00, NULL, NULL),
(112, 41, 64, 'Stylo', 2, 800.00, NULL, NULL),
(113, 42, 60, 'Cahier 100p PF', 3, 1700.00, NULL, NULL),
(114, 42, 70, 'Yaourt', 3, 1000.00, NULL, NULL),
(115, 42, 73, 'Flan', 2, 1000.00, NULL, NULL),
(116, 43, 63, 'Cahier 200p GF', 4, 5500.00, NULL, NULL),
(117, 43, 61, 'Cahier 100p GF', 2, 3300.00, NULL, NULL),
(118, 43, 67, 'Doritos GM', 2, 3000.00, NULL, NULL),
(119, 43, 74, 'Miel', 1, 7000.00, NULL, NULL),
(120, 44, 57, 'Sachet', 5, 300.00, NULL, NULL),
(121, 44, 58, 'Cache bouche', 3, 500.00, NULL, NULL),
(122, 44, 75, 'Tsam PM', 4, 200.00, NULL, NULL),
(123, 45, 63, 'Cahier 200p GF', 3, 5500.00, NULL, NULL),
(124, 45, 62, 'Cahier 200p PF', 1, 3400.00, NULL, NULL),
(125, 45, 66, 'Doritos PM', 2, 2000.00, NULL, NULL),
(126, 45, 71, 'Yaourt a boire', 1, 1500.00, NULL, NULL),
(127, 46, 65, 'Lion', 3, 1500.00, NULL, NULL),
(128, 46, 64, 'Stylo', 3, 800.00, NULL, NULL),
(129, 46, 72, 'Gauffre', 2, 1000.00, NULL, NULL),
(130, 47, 67, 'Doritos GM', 3, 3000.00, NULL, NULL),
(131, 47, 60, 'Cahier 100p PF', 2, 1700.00, NULL, NULL),
(132, 47, 70, 'Yaourt', 2, 1000.00, NULL, NULL),
(133, 48, 58, 'Cache bouche', 4, 500.00, NULL, NULL),
(134, 48, 76, 'Tsam GM', 4, 500.00, NULL, NULL),
(135, 48, 69, 'Valopy', 2, 200.00, NULL, NULL),
(136, 49, 63, 'Cahier 200p GF', 3, 5500.00, NULL, NULL),
(137, 49, 66, 'Doritos PM', 3, 2000.00, NULL, NULL),
(138, 49, 61, 'Cahier 100p GF', 1, 3300.00, NULL, NULL),
(139, 49, 74, 'Miel', 1, 7000.00, NULL, NULL),
(140, 50, 59, 'Mouchoir', 2, 700.00, NULL, NULL),
(141, 50, 70, 'Yaourt', 3, 1000.00, NULL, NULL),
(142, 50, 73, 'Flan', 2, 1000.00, NULL, NULL),
(143, 50, 68, 'Salto Chips', 1, 800.00, NULL, NULL),
(144, 51, 63, 'Cahier 200p GF', 3, 5500.00, NULL, NULL),
(145, 51, 62, 'Cahier 200p PF', 2, 3400.00, NULL, NULL),
(146, 51, 67, 'Doritos GM', 1, 3000.00, NULL, NULL),
(147, 52, 60, 'Cahier 100p PF', 3, 1700.00, NULL, NULL),
(148, 52, 65, 'Lion', 2, 1500.00, NULL, NULL),
(149, 52, 71, 'Yaourt a boire', 2, 1500.00, NULL, NULL),
(150, 52, 75, 'Tsam PM', 3, 200.00, NULL, NULL),
(151, 53, 63, 'Cahier 200p GF', 4, 5500.00, NULL, NULL),
(152, 53, 61, 'Cahier 100p GF', 2, 3300.00, NULL, NULL),
(153, 53, 67, 'Doritos GM', 1, 3000.00, NULL, NULL),
(154, 53, 74, 'Miel', 1, 7000.00, NULL, NULL),
(155, 54, 57, 'Sachet', 5, 300.00, NULL, NULL),
(156, 54, 58, 'Cache bouche', 3, 500.00, NULL, NULL),
(157, 54, 64, 'Stylo', 2, 800.00, NULL, NULL),
(158, 55, 62, 'Cahier 200p PF', 3, 3400.00, NULL, NULL),
(159, 55, 66, 'Doritos PM', 2, 2000.00, NULL, NULL),
(160, 55, 70, 'Yaourt', 3, 1000.00, NULL, NULL),
(161, 56, 65, 'Lion', 2, 1500.00, NULL, NULL),
(162, 56, 64, 'Stylo', 3, 800.00, NULL, NULL),
(163, 56, 72, 'Gauffre', 3, 1000.00, NULL, NULL),
(164, 57, 63, 'Cahier 200p GF', 5, 5500.00, NULL, NULL),
(165, 57, 61, 'Cahier 100p GF', 2, 3300.00, NULL, NULL),
(166, 57, 74, 'Miel', 1, 7000.00, NULL, NULL),
(167, 58, 60, 'Cahier 100p PF', 2, 1700.00, NULL, NULL),
(168, 58, 71, 'Yaourt a boire', 3, 1500.00, NULL, NULL),
(169, 58, 73, 'Flan', 2, 1000.00, NULL, NULL),
(170, 59, 67, 'Doritos GM', 2, 3000.00, NULL, NULL),
(171, 59, 62, 'Cahier 200p PF', 1, 3400.00, NULL, NULL),
(172, 59, 66, 'Doritos PM', 2, 2000.00, NULL, NULL),
(173, 59, 68, 'Salto Chips', 2, 800.00, NULL, NULL),
(174, 60, 70, 'Yaourt', 3, 1000.00, NULL, NULL),
(175, 60, 72, 'Gauffre', 2, 1000.00, NULL, NULL),
(176, 60, 75, 'Tsam PM', 5, 200.00, NULL, NULL),
(177, 61, 58, NULL, 5, 500.00, NULL, NULL),
(178, 61, 61, NULL, 3, 3300.00, NULL, NULL),
(179, 62, 72, NULL, 2, 1000.00, NULL, NULL),
(180, 62, 73, NULL, 2, 1000.00, NULL, NULL),
(181, 63, 58, NULL, 1, 500.00, NULL, NULL),
(182, 63, 60, NULL, 2, 1700.00, NULL, NULL),
(183, 63, 61, NULL, 1, 3300.00, NULL, NULL),
(184, 64, 60, NULL, 1, 1700.00, NULL, NULL),
(185, 64, 61, NULL, 1, 3300.00, NULL, NULL),
(186, 65, 58, NULL, 4, 500.00, NULL, NULL),
(187, 66, 58, NULL, 1, 500.00, NULL, NULL),
(188, 66, 60, NULL, 1, 1700.00, NULL, NULL),
(189, 66, 67, NULL, 1, 3000.00, NULL, NULL),
(190, 67, 58, NULL, 3, 500.00, NULL, NULL),
(191, 68, 58, NULL, 2, 500.00, NULL, NULL),
(192, 68, 61, NULL, 2, 3300.00, NULL, NULL),
(193, 69, 58, NULL, 1, 500.00, NULL, NULL),
(194, 69, 60, NULL, 1, 1700.00, NULL, NULL),
(195, 69, 62, NULL, 1, 3400.00, NULL, NULL),
(196, 70, 61, NULL, 1, 3300.00, NULL, NULL),
(197, 71, 72, NULL, 21, 1000.00, NULL, NULL),
(198, 72, 60, NULL, 24, 1700.00, NULL, NULL),
(199, 73, 61, NULL, 1, 3300.00, NULL, NULL),
(200, 73, 62, NULL, 1, 3400.00, NULL, NULL),
(201, 73, 67, NULL, 1, 3000.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('6Jpg1WjSrGYMRi2PJ8iraLzjDdJ8oOgxpWFSW7BZ', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiazVZcGNneDZNekpXT2JyODlycEw4Zk03U2hlSk9zbmI3SjVNVFJNOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Njt9', 1786735184),
('HBp67bUYwmQ5ITQAO33uJAD6bucRYWtRAS0G4jwX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicmpXd2VGTjhCVld4RXlUUXJLSWNKS2p0MjlZcWNvMTRPU3dUVGVDbSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjp2bDRnblZvTHI3QnVvMTBFIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787314524),
('rJka5WROyw21H0BrYrlVoNiaAaaDfIwV0g0svvlP', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieXRyeDM3Y3dsSkNpNXhGZm0xOElaTHBXU2dDaFp3WWFVeGlOSjZnOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjp2bDRnblZvTHI3QnVvMTBFIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1787346569),
('SQ2HhRwDkJqxDSdaZP2Wgo2i1bVg5F7krUKgNCJG', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWURuUlJ1RVhXejB3YVBaNE9vVUpYSVZxblpMVk5CQWZOb2Rtb09kVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjp2bDRnblZvTHI3QnVvMTBFIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1787169775),
('X6h1G3leIJO27UrgwjFI5V4366ze3t81b8N2ViOu', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieHBVeUU2T1V3SjRuMEJPVGtmVHJBYlBwS25kWWZ6MXVvNzc4bHF0RSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NjtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3BvcyI7czo1OiJyb3V0ZSI7czo5OiJwb3MuaW5kZXgiO319', 1787405757);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('administrateur','vendeur') NOT NULL DEFAULT 'vendeur',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@scandalebouffe.com', '2026-08-13 08:01:11', '$2y$12$Bs69K1AQ8gV8MfQsdVS2/uazucTFG.ZnlEhZXgn69MiwO5zlLv1Rm', 'administrateur', 'aTpVPNmKAIcCjM7IHcFkwcKFCRnVVDAllQGdjguxhudG7bywkgMlQt6BllCS', '2026-08-12 14:02:53', '2026-08-13 08:01:11'),
(3, 'Test User', 'test@example.com', '2026-08-13 07:20:01', '$2y$12$8b1OUN0b9CnVnbmq.qn5fuQOouMUHUQobsW1RGpE7QNvEFO9zBbTa', 'vendeur', 'lSbuOvL9FB', '2026-08-13 07:20:02', '2026-08-13 07:20:02'),
(6, 'Vendeur', 'vendeur@scandalebouffe.com', '2026-08-13 08:01:11', '$2y$12$b.FNw1v3GOiYiqloLCzI4u7Kk7xNBC9b7ZT2MnS.bb8vLSYrT5cle', 'vendeur', NULL, '2026-08-13 07:20:27', '2026-08-13 08:01:11'),
(9, 'Fanompoana', 'fanompoana@gmail.com', NULL, '$2y$10$jy8amdX7FQLw5C0UnDpaWOUZVYcbcr9gpCxPIpkeggWEJRuo8aJaO', 'vendeur', NULL, '2026-08-21 09:20:18', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_name_unique` (`name`);

--
-- Index pour la table `daily_deposits`
--
ALTER TABLE `daily_deposits`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date_user` (`user_id`,`report_date`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `roles_users`
--
ALTER TABLE `roles_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roles_users_user_id_foreign` (`user_id`),
  ADD KEY `roles_users_role_id_foreign` (`role_id`);

--
-- Index pour la table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_user_id_foreign` (`user_id`);

--
-- Index pour la table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_items_sale_id_foreign` (`sale_id`),
  ADD KEY `sale_items_product_id_foreign` (`product_id`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `daily_deposits`
--
ALTER TABLE `daily_deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `roles_users`
--
ALTER TABLE `roles_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT pour la table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `roles_users`
--
ALTER TABLE `roles_users`
  ADD CONSTRAINT `roles_users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
