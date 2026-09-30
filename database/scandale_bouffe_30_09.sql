-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 30 sep. 2026 à 13:37
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
(6, 'Revente', 'Articles de revente', NULL, NULL),
(9, 'Comptoir', NULL, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(10, 'Vitrine', NULL, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(11, 'Restaurant', NULL, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(12, 'Street Food', NULL, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(13, 'Catering', NULL, '2026-09-30 10:09:29', '2026-09-30 10:09:29');

-- --------------------------------------------------------

--
-- Structure de la table `daily_deposits`
--

CREATE TABLE `daily_deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposit_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `daily_deposits`
--

INSERT INTO `daily_deposits` (`id`, `user_id`, `amount`, `deposit_date`, `created_at`) VALUES
(14, 1, 448400.00, '2026-01-12', '2026-09-30 13:35:50'),
(15, 1, 402000.00, '2026-01-13', '2026-09-30 13:35:50'),
(16, 1, 405600.00, '2026-01-19', '2026-09-30 13:35:50'),
(17, 1, 445900.00, '2026-01-20', '2026-09-30 13:35:50');

-- --------------------------------------------------------

--
-- Structure de la table `daily_paiements`
--

CREATE TABLE `daily_paiements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paiement_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `daily_paiements`
--

INSERT INTO `daily_paiements` (`id`, `user_id`, `amount`, `paiement_date`, `created_at`) VALUES
(5, 1, 3000.00, '2026-01-12', '2026-09-30 13:35:50'),
(6, 1, 21000.00, '2026-01-19', '2026-09-30 13:35:50');

-- --------------------------------------------------------

--
-- Structure de la table `daily_relais`
--

CREATE TABLE `daily_relais` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `relais_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `daily_relais`
--

INSERT INTO `daily_relais` (`id`, `user_id`, `amount`, `relais_date`, `created_at`) VALUES
(6, 1, 19000.00, '2026-01-12', '2026-09-30 13:35:50'),
(7, 1, 19000.00, '2026-01-13', '2026-09-30 13:35:50'),
(8, 1, 12000.00, '2026-01-19', '2026-09-30 13:35:50'),
(9, 1, 20000.00, '2026-01-20', '2026-09-30 13:35:50');

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
(88, 1, '2026-01-12', 9000.00, 20500.00, 0.00, 0.00, '2026-09-30 13:35:50'),
(89, 1, '2026-01-13', 15000.00, 18000.00, 0.00, 0.00, '2026-09-30 13:35:50'),
(90, 1, '2026-01-19', 9000.00, 21500.00, 0.00, 5000.00, '2026-09-30 13:35:50'),
(91, 1, '2026-01-20', 9000.00, 25700.00, 0.00, 6000.00, '2026-09-30 13:35:50');

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
(81, 'Cahier 100p PF', 5, '', 1700.00, 100, 5, 'pcs', NULL, 1, '2026-09-27 14:13:26', '2026-09-30 10:09:29'),
(82, 'Cahier 200p PF', 5, '', 3400.00, 100, 5, 'pcs', NULL, 1, '2026-09-27 14:13:48', '2026-09-30 10:09:29'),
(87, 'Cristalline', 6, 'Eau minérale', 2500.00, 100, 5, 'pcs', NULL, 1, '2026-09-27 14:18:38', '2026-09-30 10:09:29'),
(88, 'Yaourt', 6, '', 1000.00, 100, 10, 'pcs', NULL, 1, '2026-09-27 14:19:19', '2026-09-30 10:09:29'),
(89, 'Yaourt à boire', 6, '', 1500.00, 100, 10, 'pcs', NULL, 1, '2026-09-27 14:19:42', '2026-09-30 10:09:29'),
(93, 'Thé', 9, '', 1000.00, 100, 5, 'pcs', NULL, 1, '2026-09-27 14:25:32', '2026-09-30 10:09:29'),
(102, 'Cookies', 10, '', 500.00, 88, 5, 'pcs', NULL, 1, '2026-09-27 14:35:36', '2026-09-30 10:09:29'),
(105, 'Donut', 10, '', 1000.00, 90, 5, 'pcs', NULL, 1, '2026-09-27 14:36:46', '2026-09-30 10:09:29'),
(106, 'Pudding', 10, '', 1000.00, 96, 5, 'pcs', NULL, 1, '2026-09-27 14:37:27', '2026-09-30 10:09:29'),
(107, 'Pao', 10, '', 600.00, 95, 5, 'pcs', NULL, 1, '2026-09-27 14:37:57', '2026-09-30 10:09:29'),
(108, 'Pizza', 10, '', 1200.00, 75, 5, 'pcs', NULL, 1, '2026-09-27 14:38:34', '2026-09-30 10:09:29'),
(109, 'Panini', 10, '', 1500.00, 90, 5, 'pcs', NULL, 1, '2026-09-27 14:39:07', '2026-09-30 10:09:29'),
(110, 'Hot dog', 10, '', 1500.00, 90, 5, 'pcs', NULL, 1, '2026-09-27 14:39:29', '2026-09-30 10:09:29'),
(112, 'Sandwich', 10, '', 1000.00, 92, 5, 'pcs', NULL, 1, '2026-09-27 14:40:24', '2026-09-30 10:09:29'),
(116, 'Soupe', 11, '', 3000.00, 95, 5, 'pcs', NULL, 1, '2026-09-27 14:43:28', '2026-09-30 10:09:29'),
(117, 'Composé', 11, '', 1000.00, 96, 5, 'pcs', NULL, 1, '2026-09-27 14:43:47', '2026-09-30 10:09:29'),
(124, 'Jus sur place', 9, '', 4000.00, 100, 5, 'L', NULL, 1, '2026-09-27 15:44:30', '2026-09-30 10:09:29'),
(126, 'Sachet', 5, NULL, 300.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(127, 'Cache bouche', 5, NULL, 500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(128, 'Mouchoir', 5, NULL, 700.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(129, 'Cahier 100p GF', 5, NULL, 3300.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(130, 'Cahier 200p GF', 5, NULL, 5500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(131, 'Stylo', 5, NULL, 800.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(132, 'Lion', 5, NULL, 1500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(133, 'Doritos PM', 5, NULL, 2000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(134, 'Doritos GM', 5, NULL, 3000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(135, 'Salto chips', 5, NULL, 800.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(136, 'Valopy', 5, NULL, 200.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(137, 'Gaufre', 6, NULL, 1000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(138, 'Flan', 6, NULL, 1000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(139, 'Miel', 6, NULL, 7000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(140, 'Tsam PM', 6, NULL, 200.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(141, 'Tsam GM', 6, NULL, 500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(142, 'Woco', 6, NULL, 2000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(143, 'Woco canette', 6, NULL, 3000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(144, 'Woco GM', 6, NULL, 5000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(145, 'Eau vive', 6, '', 2000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:00:12'),
(146, 'Nems', 6, NULL, 500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(147, 'Fromage', 6, NULL, 4000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(148, 'Jus gobelet', 9, NULL, 1500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(149, 'Jus Tinah', 9, NULL, 0.00, 100, 5, 'pcs', NULL, 0, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(150, 'Jus bogido', 9, NULL, 1300.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(151, 'Café', 9, NULL, 1000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(152, 'Lait', 9, NULL, 1000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(153, 'Chocolat chaud', 9, NULL, 1500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(154, 'Gobelet chaud', 9, NULL, 500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(155, 'Salade de fruits', 9, NULL, 1000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(156, 'Pain au choco PM', 10, NULL, 500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(157, 'Pain au choco GM', 10, NULL, 1500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(158, 'Cake', 10, NULL, 5000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(159, 'Cake individuelle', 10, NULL, 700.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(160, 'Croque M.', 10, NULL, 2000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(161, 'Beef burger', 10, '', 2000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:00:19'),
(162, 'Cheese burger', 10, NULL, 2500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(163, 'Chicken burger', 10, NULL, 3000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(164, 'Tarte', 10, NULL, 1500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(165, 'Demi-lune fromage', 10, NULL, 500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(166, 'Misao', 11, NULL, 3500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(167, 'Pommes frites', 12, NULL, 5000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 11:01:58'),
(168, 'Tacos', 12, NULL, 1500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(169, 'Crêpe', 12, NULL, 1500.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29'),
(170, 'Cantine', 13, NULL, 3000.00, 100, 5, 'pcs', NULL, 1, '2026-09-30 10:09:29', '2026-09-30 10:09:29');

-- --------------------------------------------------------

--
-- Structure de la table `products_qty_backup_20260927`
--

CREATE TABLE `products_qty_backup_20260927` (
  `id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products_qty_backup_20260927`
--

INSERT INTO `products_qty_backup_20260927` (`id`, `name`, `quantity`) VALUES
(78, 'Salto', 26),
(79, 'Salto frite', 28),
(80, 'Milay Be', 17),
(81, 'Cahier 100p PF', 50),
(82, 'Cahier 200p PF', 50),
(83, 'Nem', 4),
(84, 'Tsam', 30),
(85, 'Tsam', 16),
(86, 'Eauvive', 5),
(87, 'Cristalline', 20),
(88, 'Yaourt', 9),
(89, 'Yaourt à boire', 6),
(90, 'Jus simple', 25),
(91, 'Mofo mangahazo', 30),
(92, 'Gauffre', 10),
(93, 'Thé', 15),
(94, 'Coca', 4),
(95, 'Cantine PM', 50),
(96, 'Cantine MM', 50),
(97, 'Bonbon', 50),
(98, 'Crèpes', 20),
(99, 'Pain choco PM', 15),
(100, 'Pain choco GM', 15),
(101, 'Croissant', 15),
(102, 'Cookies', 15),
(103, 'Cake PM', 50),
(104, 'Cake GM', 50),
(105, 'Donut', 15),
(106, 'Pudding', 15),
(107, 'Pao', 15),
(108, 'Pizza', 15),
(109, 'Panini', 15),
(110, 'Hot dog', 15),
(111, 'Croquette', 15),
(112, 'Sandwich', 15),
(113, 'Beef', 50),
(114, 'Cheese', 50),
(115, 'Chicken', 50),
(116, 'Soupe', 15),
(117, 'Compose', 15),
(120, 'Mine sao', 10),
(121, 'Mouchoir à jeter', 20),
(124, 'Jus sur place', 15);

-- --------------------------------------------------------

--
-- Structure de la table `products_qty_backup_avant100`
--

CREATE TABLE `products_qty_backup_avant100` (
  `id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products_qty_backup_avant100`
--

INSERT INTO `products_qty_backup_avant100` (`id`, `name`, `quantity`) VALUES
(78, 'Salto', 50),
(79, 'Salto frite', 50),
(80, 'Milay Be', 50),
(81, 'Cahier 100p PF', 50),
(82, 'Cahier 200p PF', 50),
(83, 'Nem', 50),
(84, 'Tsam', 50),
(85, 'Tsam', 50),
(86, 'Eauvive', 50),
(87, 'Cristalline', 50),
(88, 'Yaourt', 50),
(89, 'Yaourt à boire', 50),
(90, 'Jus simple', 50),
(91, 'Mofo mangahazo', 50),
(92, 'Gauffre', 50),
(93, 'Thé', 50),
(94, 'Coca', 50),
(95, 'Cantine PM', 50),
(96, 'Cantine MM', 50),
(97, 'Bonbon', 49),
(98, 'Crèpes', 50),
(99, 'Pain choco PM', 44),
(100, 'Pain choco GM', 45),
(101, 'Croissant', 46),
(102, 'Cookies', 34),
(103, 'Cake PM', 46),
(104, 'Cake GM', 49),
(105, 'Donut', 40),
(106, 'Pudding', 50),
(107, 'Pao', 50),
(108, 'Pizza', 28),
(109, 'Panini', 42),
(110, 'Hot dog', 45),
(111, 'Croquette', 41),
(112, 'Sandwich', 44),
(113, 'Beef', 30),
(114, 'Cheese', 48),
(115, 'Chicken', 46),
(116, 'Soupe', 35),
(117, 'Composé', 46),
(120, 'Mine sao', 48),
(121, 'Mouchoir à jeter', 50),
(124, 'Jus sur place', 50);

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
(146, 1, 455400.00, '2026-01-12 09:00:00', NULL, 'cash'),
(147, 1, 416300.00, '2026-01-13 09:00:00', NULL, 'cash'),
(148, 1, 406300.00, '2026-01-19 09:00:00', NULL, 'cash'),
(149, 1, 472000.00, '2026-01-20 09:00:00', NULL, 'cash');

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
  `pay_method` varchar(20) NOT NULL DEFAULT 'cash',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `pay_method`, `created_at`, `updated_at`) VALUES
(288, 146, 126, 'Sachet', 3, 300.00, 'cash', NULL, NULL),
(289, 146, 127, 'Cache bouche', 2, 500.00, 'cash', NULL, NULL),
(290, 146, 128, 'Mouchoir', 1, 700.00, 'cash', NULL, NULL),
(291, 146, 133, 'Doritos PM', 12, 2000.00, 'cash', NULL, NULL),
(292, 146, 134, 'Doritos GM', 2, 3000.00, 'cash', NULL, NULL),
(293, 146, 135, 'Salto chips', 2, 800.00, 'cash', NULL, NULL),
(294, 146, 88, 'Yaourt', 5, 1000.00, 'cash', NULL, NULL),
(295, 146, 89, 'Yaourt à boire', 8, 1500.00, 'cash', NULL, NULL),
(296, 146, 137, 'Gaufre', 12, 1000.00, 'cash', NULL, NULL),
(297, 146, 138, 'Flan', 8, 1000.00, 'cash', NULL, NULL),
(298, 146, 140, 'Tsam PM', 184, 200.00, 'cash', NULL, NULL),
(299, 146, 141, 'Tsam GM', 23, 500.00, 'cash', NULL, NULL),
(300, 146, 142, 'Woco', 5, 2000.00, 'cash', NULL, NULL),
(301, 146, 87, 'Cristalline', 6, 2500.00, 'cash', NULL, NULL),
(302, 146, 145, 'Eau vive', 1, 2000.00, 'cash', NULL, NULL),
(303, 146, 146, 'Nems', 1, 500.00, 'cash', NULL, NULL),
(304, 146, 124, 'Jus sur place', 1, 3000.00, 'cash', NULL, NULL),
(305, 146, 148, 'Jus gobelet', 2, 1500.00, 'cash', NULL, NULL),
(306, 146, 156, 'Pain au choco PM', 11, 500.00, 'cash', NULL, NULL),
(307, 146, 157, 'Pain au choco GM', 7, 1500.00, 'cash', NULL, NULL),
(308, 146, 102, 'Cookies', 12, 500.00, 'cash', NULL, NULL),
(309, 146, 159, 'Cake individuelle', 6, 700.00, 'cash', NULL, NULL),
(310, 146, 112, 'Sandwich', 1, 1000.00, 'cash', NULL, NULL),
(311, 146, 160, 'Croque M.', 9, 2000.00, 'cash', NULL, NULL),
(312, 146, 161, 'Beef burger', 26, 2000.00, 'cash', NULL, NULL),
(313, 146, 162, 'Cheese burger', 2, 2500.00, 'cash', NULL, NULL),
(314, 146, 163, 'Chicken burger', 7, 3000.00, 'cash', NULL, NULL),
(315, 146, 109, 'Panini', 9, 1500.00, 'cash', NULL, NULL),
(316, 146, 110, 'Hot dog', 8, 1500.00, 'cash', NULL, NULL),
(317, 146, 108, 'Pizza', 6, 1200.00, 'cash', NULL, NULL),
(318, 146, 164, 'Tarte', 3, 1500.00, 'cash', NULL, NULL),
(319, 146, 105, 'Donut', 8, 1000.00, 'cash', NULL, NULL),
(320, 146, 117, 'Composé', 2, 1000.00, 'cash', NULL, NULL),
(321, 146, 166, 'Misao', 3, 3500.00, 'cash', NULL, NULL),
(322, 146, 116, 'Soupe', 4, 3000.00, 'cash', NULL, NULL),
(323, 146, 167, 'Pommes frites', 10, 5000.00, 'cash', NULL, NULL),
(324, 146, 169, 'Crêpe', 12, 1458.33, 'cash', NULL, NULL),
(325, 146, 170, 'Cantine', 14, 3000.00, 'cash', NULL, NULL),
(326, 147, 126, 'Sachet', 2, 300.00, 'cash', NULL, NULL),
(327, 147, 127, 'Cache bouche', 2, 500.00, 'cash', NULL, NULL),
(328, 147, 128, 'Mouchoir', 1, 700.00, 'cash', NULL, NULL),
(329, 147, 133, 'Doritos PM', 12, 2000.00, 'cash', NULL, NULL),
(330, 147, 134, 'Doritos GM', 1, 3000.00, 'cash', NULL, NULL),
(331, 147, 135, 'Salto chips', 9, 800.00, 'cash', NULL, NULL),
(332, 147, 89, 'Yaourt à boire', 3, 1500.00, 'cash', NULL, NULL),
(333, 147, 137, 'Gaufre', 12, 1000.00, 'cash', NULL, NULL),
(334, 147, 138, 'Flan', 4, 1000.00, 'cash', NULL, NULL),
(335, 147, 140, 'Tsam PM', 106, 200.00, 'cash', NULL, NULL),
(336, 147, 141, 'Tsam GM', 22, 500.00, 'cash', NULL, NULL),
(337, 147, 142, 'Woco', 6, 2000.00, 'cash', NULL, NULL),
(338, 147, 87, 'Cristalline', 1, 2500.00, 'cash', NULL, NULL),
(339, 147, 145, 'Eau vive', 5, 2000.00, 'cash', NULL, NULL),
(340, 147, 146, 'Nems', 6, 500.00, 'cash', NULL, NULL),
(341, 147, 124, 'Jus sur place', 2, 3750.00, 'cash', NULL, NULL),
(342, 147, 148, 'Jus gobelet', 1, 1500.00, 'cash', NULL, NULL),
(343, 147, 153, 'Chocolat chaud', 1, 1500.00, 'cash', NULL, NULL),
(344, 147, 154, 'Gobelet chaud', 1, 500.00, 'cash', NULL, NULL),
(345, 147, 156, 'Pain au choco PM', 5, 500.00, 'cash', NULL, NULL),
(346, 147, 157, 'Pain au choco GM', 7, 1500.00, 'cash', NULL, NULL),
(347, 147, 102, 'Cookies', 8, 500.00, 'cash', NULL, NULL),
(348, 147, 159, 'Cake individuelle', 7, 700.00, 'cash', NULL, NULL),
(349, 147, 112, 'Sandwich', 5, 1000.00, 'cash', NULL, NULL),
(350, 147, 160, 'Croque M.', 5, 2000.00, 'cash', NULL, NULL),
(351, 147, 161, 'Beef burger', 20, 2000.00, 'cash', NULL, NULL),
(352, 147, 162, 'Cheese burger', 4, 2500.00, 'cash', NULL, NULL),
(353, 147, 163, 'Chicken burger', 2, 3000.00, 'cash', NULL, NULL),
(354, 147, 109, 'Panini', 8, 1500.00, 'cash', NULL, NULL),
(355, 147, 110, 'Hot dog', 5, 1500.00, 'cash', NULL, NULL),
(356, 147, 108, 'Pizza', 11, 1200.00, 'cash', NULL, NULL),
(357, 147, 164, 'Tarte', 2, 1500.00, 'cash', NULL, NULL),
(358, 147, 106, 'Pudding', 8, 1000.00, 'cash', NULL, NULL),
(359, 147, 105, 'Donut', 8, 1000.00, 'cash', NULL, NULL),
(360, 147, 117, 'Composé', 2, 1000.00, 'cash', NULL, NULL),
(361, 147, 116, 'Soupe', 3, 3000.00, 'cash', NULL, NULL),
(362, 147, 167, 'Pommes frites', 12, 4791.67, 'cash', NULL, NULL),
(363, 147, 169, 'Crêpe', 19, 1500.00, 'cash', NULL, NULL),
(364, 147, 170, 'Cantine', 16, 2937.50, 'cash', NULL, NULL),
(365, 148, 127, 'Cache bouche', 6, 500.00, 'cash', NULL, NULL),
(366, 148, 133, 'Doritos PM', 6, 2000.00, 'cash', NULL, NULL),
(367, 148, 134, 'Doritos GM', 4, 3000.00, 'cash', NULL, NULL),
(368, 148, 88, 'Yaourt', 2, 1000.00, 'cash', NULL, NULL),
(369, 148, 89, 'Yaourt à boire', 8, 1500.00, 'cash', NULL, NULL),
(370, 148, 137, 'Gaufre', 13, 1000.00, 'cash', NULL, NULL),
(371, 148, 138, 'Flan', 2, 1000.00, 'cash', NULL, NULL),
(372, 148, 140, 'Tsam PM', 135, 200.00, 'cash', NULL, NULL),
(373, 148, 141, 'Tsam GM', 7, 500.00, 'cash', NULL, NULL),
(374, 148, 142, 'Woco', 7, 2000.00, 'cash', NULL, NULL),
(375, 148, 87, 'Cristalline', 3, 2500.00, 'cash', NULL, NULL),
(376, 148, 145, 'Eau vive', 3, 2000.00, 'cash', NULL, NULL),
(377, 148, 124, 'Jus sur place', 1, 500.00, 'cash', NULL, NULL),
(378, 148, 150, 'Jus bogido', 4, 1300.00, 'cash', NULL, NULL),
(379, 148, 151, 'Café', 1, 1000.00, 'cash', NULL, NULL),
(380, 148, 156, 'Pain au choco PM', 7, 500.00, 'cash', NULL, NULL),
(381, 148, 157, 'Pain au choco GM', 4, 1500.00, 'cash', NULL, NULL),
(382, 148, 102, 'Cookies', 7, 500.00, 'cash', NULL, NULL),
(383, 148, 159, 'Cake individuelle', 2, 700.00, 'cash', NULL, NULL),
(384, 148, 112, 'Sandwich', 5, 1000.00, 'cash', NULL, NULL),
(385, 148, 160, 'Croque M.', 10, 2000.00, 'cash', NULL, NULL),
(386, 148, 161, 'Beef burger', 21, 2000.00, 'cash', NULL, NULL),
(387, 148, 162, 'Cheese burger', 4, 2500.00, 'cash', NULL, NULL),
(388, 148, 163, 'Chicken burger', 4, 3000.00, 'cash', NULL, NULL),
(389, 148, 109, 'Panini', 8, 1500.00, 'cash', NULL, NULL),
(390, 148, 110, 'Hot dog', 8, 1500.00, 'cash', NULL, NULL),
(391, 148, 108, 'Pizza', 11, 1200.00, 'cash', NULL, NULL),
(392, 148, 164, 'Tarte', 1, 1500.00, 'cash', NULL, NULL),
(393, 148, 106, 'Pudding', 9, 1000.00, 'cash', NULL, NULL),
(394, 148, 105, 'Donut', 10, 1000.00, 'cash', NULL, NULL),
(395, 148, 117, 'Composé', 1, 1000.00, 'cash', NULL, NULL),
(396, 148, 166, 'Misao', 1, 3500.00, 'cash', NULL, NULL),
(397, 148, 116, 'Soupe', 1, 3000.00, 'cash', NULL, NULL),
(398, 148, 167, 'Pommes frites', 12, 5000.00, 'cash', NULL, NULL),
(399, 148, 169, 'Crêpe', 10, 1500.00, 'cash', NULL, NULL),
(400, 148, 170, 'Cantine', 14, 3000.00, 'cash', NULL, NULL),
(401, 149, 127, 'Cache bouche', 6, 500.00, 'cash', NULL, NULL),
(402, 149, 128, 'Mouchoir', 1, 700.00, 'cash', NULL, NULL),
(403, 149, 133, 'Doritos PM', 5, 2000.00, 'cash', NULL, NULL),
(404, 149, 134, 'Doritos GM', 2, 3000.00, 'cash', NULL, NULL),
(405, 149, 88, 'Yaourt', 3, 1000.00, 'cash', NULL, NULL),
(406, 149, 89, 'Yaourt à boire', 12, 1500.00, 'cash', NULL, NULL),
(407, 149, 137, 'Gaufre', 13, 1000.00, 'cash', NULL, NULL),
(408, 149, 138, 'Flan', 5, 1000.00, 'cash', NULL, NULL),
(409, 149, 140, 'Tsam PM', 195, 200.00, 'cash', NULL, NULL),
(410, 149, 141, 'Tsam GM', 25, 500.00, 'cash', NULL, NULL),
(411, 149, 142, 'Woco', 7, 2000.00, 'cash', NULL, NULL),
(412, 149, 87, 'Cristalline', 2, 2500.00, 'cash', NULL, NULL),
(413, 149, 145, 'Eau vive', 6, 2000.00, 'cash', NULL, NULL),
(414, 149, 146, 'Nems', 11, 500.00, 'cash', NULL, NULL),
(415, 149, 124, 'Jus sur place', 1, 4000.00, 'cash', NULL, NULL),
(416, 149, 148, 'Jus gobelet', 1, 1500.00, 'cash', NULL, NULL),
(417, 149, 153, 'Chocolat chaud', 1, 1500.00, 'cash', NULL, NULL),
(418, 149, 154, 'Gobelet chaud', 1, 500.00, 'cash', NULL, NULL),
(419, 149, 156, 'Pain au choco PM', 7, 500.00, 'cash', NULL, NULL),
(420, 149, 157, 'Pain au choco GM', 7, 1500.00, 'cash', NULL, NULL),
(421, 149, 102, 'Cookies', 1, 500.00, 'cash', NULL, NULL),
(422, 149, 158, 'Cake', 1, 5000.00, 'cash', NULL, NULL),
(423, 149, 159, 'Cake individuelle', 5, 700.00, 'cash', NULL, NULL),
(424, 149, 112, 'Sandwich', 6, 1000.00, 'cash', NULL, NULL),
(425, 149, 160, 'Croque M.', 7, 2000.00, 'cash', NULL, NULL),
(426, 149, 161, 'Beef burger', 27, 2000.00, 'cash', NULL, NULL),
(427, 149, 162, 'Cheese burger', 3, 2500.00, 'cash', NULL, NULL),
(428, 149, 163, 'Chicken burger', 6, 3000.00, 'cash', NULL, NULL),
(429, 149, 109, 'Panini', 5, 1500.00, 'cash', NULL, NULL),
(430, 149, 110, 'Hot dog', 3, 1500.00, 'cash', NULL, NULL),
(431, 149, 108, 'Pizza', 4, 1200.00, 'cash', NULL, NULL),
(432, 149, 164, 'Tarte', 3, 1500.00, 'cash', NULL, NULL),
(433, 149, 106, 'Pudding', 5, 1000.00, 'cash', NULL, NULL),
(434, 149, 105, 'Donut', 10, 1000.00, 'cash', NULL, NULL),
(435, 149, 117, 'Composé', 3, 1000.00, 'cash', NULL, NULL),
(436, 149, 166, 'Misao', 4, 3500.00, 'cash', NULL, NULL),
(437, 149, 116, 'Soupe', 7, 3000.00, 'cash', NULL, NULL),
(438, 149, 167, 'Pommes frites', 12, 4833.33, 'cash', NULL, NULL),
(439, 149, 169, 'Crêpe', 16, 1500.00, 'cash', NULL, NULL),
(440, 149, 170, 'Cantine', 13, 3000.00, 'cash', NULL, NULL);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_deposits_deposit_date` (`user_id`,`deposit_date`);

--
-- Index pour la table `daily_paiements`
--
ALTER TABLE `daily_paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_paiements_user_id` (`user_id`),
  ADD KEY `daily_paiements_paiement_date` (`user_id`,`paiement_date`);

--
-- Index pour la table `daily_relais`
--
ALTER TABLE `daily_relais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_relais_user_id` (`user_id`),
  ADD KEY `daily_relais_relais_date` (`user_id`,`relais_date`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `daily_deposits`
--
ALTER TABLE `daily_deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `daily_paiements`
--
ALTER TABLE `daily_paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `daily_relais`
--
ALTER TABLE `daily_relais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT pour la table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=446;

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
