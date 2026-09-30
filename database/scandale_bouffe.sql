-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: scandale_bouffe
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (5,'Epicerie','Articles de prix fixe',NULL,NULL),(6,'Revente','Articles de revente',NULL,NULL),(9,'Comptoir',NULL,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(10,'Vitrine',NULL,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(11,'Restaurant',NULL,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(12,'Street Food',NULL,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(13,'Catering',NULL,'2026-09-30 10:09:29','2026-09-30 10:09:29');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_deposits`
--

DROP TABLE IF EXISTS `daily_deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_deposits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deposit_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `daily_deposits_deposit_date` (`user_id`,`deposit_date`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_deposits`
--

LOCK TABLES `daily_deposits` WRITE;
/*!40000 ALTER TABLE `daily_deposits` DISABLE KEYS */;
INSERT INTO `daily_deposits` VALUES (14,1,448400.00,'2026-01-12','2026-09-30 13:35:50'),(15,1,402000.00,'2026-01-13','2026-09-30 13:35:50'),(16,1,405600.00,'2026-01-19','2026-09-30 13:35:50'),(17,1,445900.00,'2026-01-20','2026-09-30 13:35:50');
/*!40000 ALTER TABLE `daily_deposits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_paiements`
--

DROP TABLE IF EXISTS `daily_paiements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_paiements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paiement_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `daily_paiements_user_id` (`user_id`),
  KEY `daily_paiements_paiement_date` (`user_id`,`paiement_date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_paiements`
--

LOCK TABLES `daily_paiements` WRITE;
/*!40000 ALTER TABLE `daily_paiements` DISABLE KEYS */;
INSERT INTO `daily_paiements` VALUES (5,1,3000.00,'2026-01-12','2026-09-30 13:35:50'),(6,1,21000.00,'2026-01-19','2026-09-30 13:35:50');
/*!40000 ALTER TABLE `daily_paiements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_relais`
--

DROP TABLE IF EXISTS `daily_relais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_relais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `relais_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `daily_relais_user_id` (`user_id`),
  KEY `daily_relais_relais_date` (`user_id`,`relais_date`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_relais`
--

LOCK TABLES `daily_relais` WRITE;
/*!40000 ALTER TABLE `daily_relais` DISABLE KEYS */;
INSERT INTO `daily_relais` VALUES (6,1,19000.00,'2026-01-12','2026-09-30 13:35:50'),(7,1,19000.00,'2026-01-13','2026-09-30 13:35:50'),(8,1,12000.00,'2026-01-19','2026-09-30 13:35:50'),(9,1,20000.00,'2026-01-20','2026-09-30 13:35:50');
/*!40000 ALTER TABLE `daily_relais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_reports`
--

DROP TABLE IF EXISTS `daily_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `prepaye` decimal(12,2) NOT NULL DEFAULT 0.00,
  `trosa` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paiement` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nofy` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date_user` (`user_id`,`report_date`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_reports`
--

LOCK TABLES `daily_reports` WRITE;
/*!40000 ALTER TABLE `daily_reports` DISABLE KEYS */;
INSERT INTO `daily_reports` VALUES (88,1,'2026-01-12',9000.00,20500.00,0.00,0.00,'2026-09-30 13:35:50'),(89,1,'2026-01-13',15000.00,18000.00,0.00,0.00,'2026-09-30 13:35:50'),(90,1,'2026-01-19',9000.00,21500.00,0.00,5000.00,'2026-09-30 13:35:50'),(91,1,'2026-01-20',9000.00,25700.00,0.00,6000.00,'2026-09-30 13:35:50');
/*!40000 ALTER TABLE `daily_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (7,'0001_01_01_000000_create_users_table',1),(8,'0001_01_01_000001_create_cache_table',1),(9,'0001_01_01_000002_create_jobs_table',1),(10,'2026_08_12_103017_create_products_table',1),(11,'2026_08_12_164131_create_roles_table',1),(12,'2026_08_12_164156_create_roles_users_table',1),(13,'2026_08_13_100000_create_sales_table',2),(14,'2026_08_13_100001_create_sale_items_table',2),(15,'2026_08_13_110000_create_categories_table',3),(16,'2026_08_13_110001_add_category_id_to_products_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 5,
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (81,'Cahier 100p PF',5,'',1700.00,100,5,'pcs',NULL,1,'2026-09-27 14:13:26','2026-09-30 10:09:29'),(82,'Cahier 200p PF',5,'',3400.00,100,5,'pcs',NULL,1,'2026-09-27 14:13:48','2026-09-30 10:09:29'),(87,'Cristalline',6,'Eau minérale',2500.00,100,5,'pcs',NULL,1,'2026-09-27 14:18:38','2026-09-30 10:09:29'),(88,'Yaourt',6,'',1000.00,100,10,'pcs',NULL,1,'2026-09-27 14:19:19','2026-09-30 10:09:29'),(89,'Yaourt à boire',6,'',1500.00,100,10,'pcs',NULL,1,'2026-09-27 14:19:42','2026-09-30 10:09:29'),(93,'Thé',9,'',1000.00,100,5,'pcs',NULL,1,'2026-09-27 14:25:32','2026-09-30 10:09:29'),(102,'Cookies',10,'',500.00,88,5,'pcs',NULL,1,'2026-09-27 14:35:36','2026-09-30 10:09:29'),(105,'Donut',10,'',1000.00,90,5,'pcs',NULL,1,'2026-09-27 14:36:46','2026-09-30 10:09:29'),(106,'Pudding',10,'',1000.00,96,5,'pcs',NULL,1,'2026-09-27 14:37:27','2026-09-30 10:09:29'),(107,'Pao',10,'',600.00,95,5,'pcs',NULL,1,'2026-09-27 14:37:57','2026-09-30 10:09:29'),(108,'Pizza',10,'',1200.00,75,5,'pcs',NULL,1,'2026-09-27 14:38:34','2026-09-30 10:09:29'),(109,'Panini',10,'',1500.00,90,5,'pcs',NULL,1,'2026-09-27 14:39:07','2026-09-30 10:09:29'),(110,'Hot dog',10,'',1500.00,90,5,'pcs',NULL,1,'2026-09-27 14:39:29','2026-09-30 10:09:29'),(112,'Sandwich',10,'',1000.00,92,5,'pcs',NULL,1,'2026-09-27 14:40:24','2026-09-30 10:09:29'),(116,'Soupe',11,'',3000.00,95,5,'pcs',NULL,1,'2026-09-27 14:43:28','2026-09-30 10:09:29'),(117,'Composé',11,'',1000.00,96,5,'pcs',NULL,1,'2026-09-27 14:43:47','2026-09-30 10:09:29'),(124,'Jus sur place',9,'',4000.00,100,5,'L',NULL,1,'2026-09-27 15:44:30','2026-09-30 10:09:29'),(126,'Sachet',5,NULL,300.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(127,'Cache bouche',5,NULL,500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(128,'Mouchoir',5,NULL,700.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(129,'Cahier 100p GF',5,NULL,3300.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(130,'Cahier 200p GF',5,NULL,5500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(131,'Stylo',5,NULL,800.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(132,'Lion',5,NULL,1500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(133,'Doritos PM',5,NULL,2000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(134,'Doritos GM',5,NULL,3000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(135,'Salto chips',5,NULL,800.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(136,'Valopy',5,NULL,200.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(137,'Gaufre',6,NULL,1000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(138,'Flan',6,NULL,1000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(139,'Miel',6,NULL,7000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(140,'Tsam PM',6,NULL,200.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(141,'Tsam GM',6,NULL,500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(142,'Woco',6,NULL,2000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(143,'Woco canette',6,NULL,3000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(144,'Woco GM',6,NULL,5000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(145,'Eau vive',6,NULL,2000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(146,'Nems',6,NULL,500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(147,'Fromage',6,NULL,4000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(148,'Jus gobelet',9,NULL,1500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(149,'Jus Tinah',9,NULL,0.00,0,5,'pcs',NULL,0,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(150,'Jus bogido',9,NULL,1300.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(151,'Café',9,NULL,1000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(152,'Lait',9,NULL,1000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(153,'Chocolat chaud',9,NULL,1500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(154,'Gobelet chaud',9,NULL,500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(155,'Salade de fruits',9,NULL,1000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(156,'Pain au choco PM',10,NULL,500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(157,'Pain au choco GM',10,NULL,1500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(158,'Cake',10,NULL,5000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(159,'Cake individuelle',10,NULL,700.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(160,'Croque M.',10,NULL,2000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(161,'Beef burger',10,NULL,2000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(162,'Cheese burger',10,NULL,2500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(163,'Chicken burger',10,NULL,3000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(164,'Tarte',10,NULL,1500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(165,'Demi-lune fromage',10,NULL,500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(166,'Misao',11,NULL,3500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(167,'Pommes frites',12,NULL,5000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(168,'Tacos',12,NULL,1500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(169,'Crêpe',12,NULL,1500.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29'),(170,'Cantine',13,NULL,3000.00,0,5,'pcs',NULL,1,'2026-09-30 10:09:29','2026-09-30 10:09:29');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products_qty_backup_20260927`
--

DROP TABLE IF EXISTS `products_qty_backup_20260927`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_qty_backup_20260927` (
  `id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products_qty_backup_20260927`
--

LOCK TABLES `products_qty_backup_20260927` WRITE;
/*!40000 ALTER TABLE `products_qty_backup_20260927` DISABLE KEYS */;
INSERT INTO `products_qty_backup_20260927` VALUES (78,'Salto',26),(79,'Salto frite',28),(80,'Milay Be',17),(81,'Cahier 100p PF',50),(82,'Cahier 200p PF',50),(83,'Nem',4),(84,'Tsam',30),(85,'Tsam',16),(86,'Eauvive',5),(87,'Cristalline',20),(88,'Yaourt',9),(89,'Yaourt à boire',6),(90,'Jus simple',25),(91,'Mofo mangahazo',30),(92,'Gauffre',10),(93,'Thé',15),(94,'Coca',4),(95,'Cantine PM',50),(96,'Cantine MM',50),(97,'Bonbon',50),(98,'Crèpes',20),(99,'Pain choco PM',15),(100,'Pain choco GM',15),(101,'Croissant',15),(102,'Cookies',15),(103,'Cake PM',50),(104,'Cake GM',50),(105,'Donut',15),(106,'Pudding',15),(107,'Pao',15),(108,'Pizza',15),(109,'Panini',15),(110,'Hot dog',15),(111,'Croquette',15),(112,'Sandwich',15),(113,'Beef',50),(114,'Cheese',50),(115,'Chicken',50),(116,'Soupe',15),(117,'Compose',15),(120,'Mine sao',10),(121,'Mouchoir à jeter',20),(124,'Jus sur place',15);
/*!40000 ALTER TABLE `products_qty_backup_20260927` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products_qty_backup_avant100`
--

DROP TABLE IF EXISTS `products_qty_backup_avant100`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_qty_backup_avant100` (
  `id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products_qty_backup_avant100`
--

LOCK TABLES `products_qty_backup_avant100` WRITE;
/*!40000 ALTER TABLE `products_qty_backup_avant100` DISABLE KEYS */;
INSERT INTO `products_qty_backup_avant100` VALUES (78,'Salto',50),(79,'Salto frite',50),(80,'Milay Be',50),(81,'Cahier 100p PF',50),(82,'Cahier 200p PF',50),(83,'Nem',50),(84,'Tsam',50),(85,'Tsam',50),(86,'Eauvive',50),(87,'Cristalline',50),(88,'Yaourt',50),(89,'Yaourt à boire',50),(90,'Jus simple',50),(91,'Mofo mangahazo',50),(92,'Gauffre',50),(93,'Thé',50),(94,'Coca',50),(95,'Cantine PM',50),(96,'Cantine MM',50),(97,'Bonbon',49),(98,'Crèpes',50),(99,'Pain choco PM',44),(100,'Pain choco GM',45),(101,'Croissant',46),(102,'Cookies',34),(103,'Cake PM',46),(104,'Cake GM',49),(105,'Donut',40),(106,'Pudding',50),(107,'Pao',50),(108,'Pizza',28),(109,'Panini',42),(110,'Hot dog',45),(111,'Croquette',41),(112,'Sandwich',44),(113,'Beef',30),(114,'Cheese',48),(115,'Chicken',46),(116,'Soupe',35),(117,'Composé',46),(120,'Mine sao',48),(121,'Mouchoir à jeter',50),(124,'Jus sur place',50);
/*!40000 ALTER TABLE `products_qty_backup_avant100` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'administrateur','Rôle avec droits complets sur le système','2026-08-12 14:02:52','2026-08-12 14:02:52'),(2,'vendeur','Rôle pour gérer les ventes et le stock','2026-08-12 14:02:52','2026-08-12 14:02:52');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_users`
--

DROP TABLE IF EXISTS `roles_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roles_users_user_id_foreign` (`user_id`),
  KEY `roles_users_role_id_foreign` (`role_id`),
  CONSTRAINT `roles_users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roles_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_users`
--

LOCK TABLES `roles_users` WRITE;
/*!40000 ALTER TABLE `roles_users` DISABLE KEYS */;
INSERT INTO `roles_users` VALUES (1,1,1,NULL,NULL),(2,6,2,NULL,NULL);
/*!40000 ALTER TABLE `roles_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `pay_method` varchar(20) NOT NULL DEFAULT 'cash',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=441 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (288,146,126,'Sachet',3,300.00,NULL,NULL),(289,146,127,'Cache bouche',2,500.00,NULL,NULL),(290,146,128,'Mouchoir',1,700.00,NULL,NULL),(291,146,133,'Doritos PM',12,2000.00,NULL,NULL),(292,146,134,'Doritos GM',2,3000.00,NULL,NULL),(293,146,135,'Salto chips',2,800.00,NULL,NULL),(294,146,88,'Yaourt',5,1000.00,NULL,NULL),(295,146,89,'Yaourt à boire',8,1500.00,NULL,NULL),(296,146,137,'Gaufre',12,1000.00,NULL,NULL),(297,146,138,'Flan',8,1000.00,NULL,NULL),(298,146,140,'Tsam PM',184,200.00,NULL,NULL),(299,146,141,'Tsam GM',23,500.00,NULL,NULL),(300,146,142,'Woco',5,2000.00,NULL,NULL),(301,146,87,'Cristalline',6,2500.00,NULL,NULL),(302,146,145,'Eau vive',1,2000.00,NULL,NULL),(303,146,146,'Nems',1,500.00,NULL,NULL),(304,146,124,'Jus sur place',1,3000.00,NULL,NULL),(305,146,148,'Jus gobelet',2,1500.00,NULL,NULL),(306,146,156,'Pain au choco PM',11,500.00,NULL,NULL),(307,146,157,'Pain au choco GM',7,1500.00,NULL,NULL),(308,146,102,'Cookies',12,500.00,NULL,NULL),(309,146,159,'Cake individuelle',6,700.00,NULL,NULL),(310,146,112,'Sandwich',1,1000.00,NULL,NULL),(311,146,160,'Croque M.',9,2000.00,NULL,NULL),(312,146,161,'Beef burger',26,2000.00,NULL,NULL),(313,146,162,'Cheese burger',2,2500.00,NULL,NULL),(314,146,163,'Chicken burger',7,3000.00,NULL,NULL),(315,146,109,'Panini',9,1500.00,NULL,NULL),(316,146,110,'Hot dog',8,1500.00,NULL,NULL),(317,146,108,'Pizza',6,1200.00,NULL,NULL),(318,146,164,'Tarte',3,1500.00,NULL,NULL),(319,146,105,'Donut',8,1000.00,NULL,NULL),(320,146,117,'Composé',2,1000.00,NULL,NULL),(321,146,166,'Misao',3,3500.00,NULL,NULL),(322,146,116,'Soupe',4,3000.00,NULL,NULL),(323,146,167,'Pommes frites',10,5000.00,NULL,NULL),(324,146,169,'Crêpe',12,1458.33,NULL,NULL),(325,146,170,'Cantine',14,3000.00,NULL,NULL),(326,147,126,'Sachet',2,300.00,NULL,NULL),(327,147,127,'Cache bouche',2,500.00,NULL,NULL),(328,147,128,'Mouchoir',1,700.00,NULL,NULL),(329,147,133,'Doritos PM',12,2000.00,NULL,NULL),(330,147,134,'Doritos GM',1,3000.00,NULL,NULL),(331,147,135,'Salto chips',9,800.00,NULL,NULL),(332,147,89,'Yaourt à boire',3,1500.00,NULL,NULL),(333,147,137,'Gaufre',12,1000.00,NULL,NULL),(334,147,138,'Flan',4,1000.00,NULL,NULL),(335,147,140,'Tsam PM',106,200.00,NULL,NULL),(336,147,141,'Tsam GM',22,500.00,NULL,NULL),(337,147,142,'Woco',6,2000.00,NULL,NULL),(338,147,87,'Cristalline',1,2500.00,NULL,NULL),(339,147,145,'Eau vive',5,2000.00,NULL,NULL),(340,147,146,'Nems',6,500.00,NULL,NULL),(341,147,124,'Jus sur place',2,3750.00,NULL,NULL),(342,147,148,'Jus gobelet',1,1500.00,NULL,NULL),(343,147,153,'Chocolat chaud',1,1500.00,NULL,NULL),(344,147,154,'Gobelet chaud',1,500.00,NULL,NULL),(345,147,156,'Pain au choco PM',5,500.00,NULL,NULL),(346,147,157,'Pain au choco GM',7,1500.00,NULL,NULL),(347,147,102,'Cookies',8,500.00,NULL,NULL),(348,147,159,'Cake individuelle',7,700.00,NULL,NULL),(349,147,112,'Sandwich',5,1000.00,NULL,NULL),(350,147,160,'Croque M.',5,2000.00,NULL,NULL),(351,147,161,'Beef burger',20,2000.00,NULL,NULL),(352,147,162,'Cheese burger',4,2500.00,NULL,NULL),(353,147,163,'Chicken burger',2,3000.00,NULL,NULL),(354,147,109,'Panini',8,1500.00,NULL,NULL),(355,147,110,'Hot dog',5,1500.00,NULL,NULL),(356,147,108,'Pizza',11,1200.00,NULL,NULL),(357,147,164,'Tarte',2,1500.00,NULL,NULL),(358,147,106,'Pudding',8,1000.00,NULL,NULL),(359,147,105,'Donut',8,1000.00,NULL,NULL),(360,147,117,'Composé',2,1000.00,NULL,NULL),(361,147,116,'Soupe',3,3000.00,NULL,NULL),(362,147,167,'Pommes frites',12,4791.67,NULL,NULL),(363,147,169,'Crêpe',19,1500.00,NULL,NULL),(364,147,170,'Cantine',16,2937.50,NULL,NULL),(365,148,127,'Cache bouche',6,500.00,NULL,NULL),(366,148,133,'Doritos PM',6,2000.00,NULL,NULL),(367,148,134,'Doritos GM',4,3000.00,NULL,NULL),(368,148,88,'Yaourt',2,1000.00,NULL,NULL),(369,148,89,'Yaourt à boire',8,1500.00,NULL,NULL),(370,148,137,'Gaufre',13,1000.00,NULL,NULL),(371,148,138,'Flan',2,1000.00,NULL,NULL),(372,148,140,'Tsam PM',135,200.00,NULL,NULL),(373,148,141,'Tsam GM',7,500.00,NULL,NULL),(374,148,142,'Woco',7,2000.00,NULL,NULL),(375,148,87,'Cristalline',3,2500.00,NULL,NULL),(376,148,145,'Eau vive',3,2000.00,NULL,NULL),(377,148,124,'Jus sur place',1,500.00,NULL,NULL),(378,148,150,'Jus bogido',4,1300.00,NULL,NULL),(379,148,151,'Café',1,1000.00,NULL,NULL),(380,148,156,'Pain au choco PM',7,500.00,NULL,NULL),(381,148,157,'Pain au choco GM',4,1500.00,NULL,NULL),(382,148,102,'Cookies',7,500.00,NULL,NULL),(383,148,159,'Cake individuelle',2,700.00,NULL,NULL),(384,148,112,'Sandwich',5,1000.00,NULL,NULL),(385,148,160,'Croque M.',10,2000.00,NULL,NULL),(386,148,161,'Beef burger',21,2000.00,NULL,NULL),(387,148,162,'Cheese burger',4,2500.00,NULL,NULL),(388,148,163,'Chicken burger',4,3000.00,NULL,NULL),(389,148,109,'Panini',8,1500.00,NULL,NULL),(390,148,110,'Hot dog',8,1500.00,NULL,NULL),(391,148,108,'Pizza',11,1200.00,NULL,NULL),(392,148,164,'Tarte',1,1500.00,NULL,NULL),(393,148,106,'Pudding',9,1000.00,NULL,NULL),(394,148,105,'Donut',10,1000.00,NULL,NULL),(395,148,117,'Composé',1,1000.00,NULL,NULL),(396,148,166,'Misao',1,3500.00,NULL,NULL),(397,148,116,'Soupe',1,3000.00,NULL,NULL),(398,148,167,'Pommes frites',12,5000.00,NULL,NULL),(399,148,169,'Crêpe',10,1500.00,NULL,NULL),(400,148,170,'Cantine',14,3000.00,NULL,NULL),(401,149,127,'Cache bouche',6,500.00,NULL,NULL),(402,149,128,'Mouchoir',1,700.00,NULL,NULL),(403,149,133,'Doritos PM',5,2000.00,NULL,NULL),(404,149,134,'Doritos GM',2,3000.00,NULL,NULL),(405,149,88,'Yaourt',3,1000.00,NULL,NULL),(406,149,89,'Yaourt à boire',12,1500.00,NULL,NULL),(407,149,137,'Gaufre',13,1000.00,NULL,NULL),(408,149,138,'Flan',5,1000.00,NULL,NULL),(409,149,140,'Tsam PM',195,200.00,NULL,NULL),(410,149,141,'Tsam GM',25,500.00,NULL,NULL),(411,149,142,'Woco',7,2000.00,NULL,NULL),(412,149,87,'Cristalline',2,2500.00,NULL,NULL),(413,149,145,'Eau vive',6,2000.00,NULL,NULL),(414,149,146,'Nems',11,500.00,NULL,NULL),(415,149,124,'Jus sur place',1,4000.00,NULL,NULL),(416,149,148,'Jus gobelet',1,1500.00,NULL,NULL),(417,149,153,'Chocolat chaud',1,1500.00,NULL,NULL),(418,149,154,'Gobelet chaud',1,500.00,NULL,NULL),(419,149,156,'Pain au choco PM',7,500.00,NULL,NULL),(420,149,157,'Pain au choco GM',7,1500.00,NULL,NULL),(421,149,102,'Cookies',1,500.00,NULL,NULL),(422,149,158,'Cake',1,5000.00,NULL,NULL),(423,149,159,'Cake individuelle',5,700.00,NULL,NULL),(424,149,112,'Sandwich',6,1000.00,NULL,NULL),(425,149,160,'Croque M.',7,2000.00,NULL,NULL),(426,149,161,'Beef burger',27,2000.00,NULL,NULL),(427,149,162,'Cheese burger',3,2500.00,NULL,NULL),(428,149,163,'Chicken burger',6,3000.00,NULL,NULL),(429,149,109,'Panini',5,1500.00,NULL,NULL),(430,149,110,'Hot dog',3,1500.00,NULL,NULL),(431,149,108,'Pizza',4,1200.00,NULL,NULL),(432,149,164,'Tarte',3,1500.00,NULL,NULL),(433,149,106,'Pudding',5,1000.00,NULL,NULL),(434,149,105,'Donut',10,1000.00,NULL,NULL),(435,149,117,'Composé',3,1000.00,NULL,NULL),(436,149,166,'Misao',4,3500.00,NULL,NULL),(437,149,116,'Soupe',7,3000.00,NULL,NULL),(438,149,167,'Pommes frites',12,4833.33,NULL,NULL),(439,149,169,'Crêpe',16,1500.00,NULL,NULL),(440,149,170,'Cantine',13,3000.00,NULL,NULL);
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL DEFAULT 'cash',
  PRIMARY KEY (`id`),
  KEY `sales_user_id_foreign` (`user_id`),
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (146,1,455400.00,'2026-01-12 09:00:00',NULL,'cash'),(147,1,416300.00,'2026-01-13 09:00:00',NULL,'cash'),(148,1,406300.00,'2026-01-19 09:00:00',NULL,'cash'),(149,1,472000.00,'2026-01-20 09:00:00',NULL,'cash');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('6Jpg1WjSrGYMRi2PJ8iraLzjDdJ8oOgxpWFSW7BZ',6,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiazVZcGNneDZNekpXT2JyODlycEw4Zk03U2hlSk9zbmI3SjVNVFJNOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Njt9',1786735184),('HBp67bUYwmQ5ITQAO33uJAD6bucRYWtRAS0G4jwX',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicmpXd2VGTjhCVld4RXlUUXJLSWNKS2p0MjlZcWNvMTRPU3dUVGVDbSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjp2bDRnblZvTHI3QnVvMTBFIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1787314524),('rJka5WROyw21H0BrYrlVoNiaAaaDfIwV0g0svvlP',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoieXRyeDM3Y3dsSkNpNXhGZm0xOElaTHBXU2dDaFp3WWFVeGlOSjZnOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjp2bDRnblZvTHI3QnVvMTBFIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1787346569),('SQ2HhRwDkJqxDSdaZP2Wgo2i1bVg5F7krUKgNCJG',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWURuUlJ1RVhXejB3YVBaNE9vVUpYSVZxblpMVk5CQWZOb2Rtb09kVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoyNzoiZ2VuZXJhdGVkOjp2bDRnblZvTHI3QnVvMTBFIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1787169775),('X6h1G3leIJO27UrgwjFI5V4366ze3t81b8N2ViOu',6,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoieHBVeUU2T1V3SjRuMEJPVGtmVHJBYlBwS25kWWZ6MXVvNzc4bHF0RSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NjtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3BvcyI7czo1OiJyb3V0ZSI7czo5OiJwb3MuaW5kZXgiO319',1787405757);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('administrateur','vendeur') NOT NULL DEFAULT 'vendeur',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@scandalebouffe.com','2026-08-13 08:01:11','$2y$12$Bs69K1AQ8gV8MfQsdVS2/uazucTFG.ZnlEhZXgn69MiwO5zlLv1Rm','administrateur','aTpVPNmKAIcCjM7IHcFkwcKFCRnVVDAllQGdjguxhudG7bywkgMlQt6BllCS','2026-08-12 14:02:53','2026-08-13 08:01:11'),(3,'Test User','test@example.com','2026-08-13 07:20:01','$2y$12$8b1OUN0b9CnVnbmq.qn5fuQOouMUHUQobsW1RGpE7QNvEFO9zBbTa','vendeur','lSbuOvL9FB','2026-08-13 07:20:02','2026-08-13 07:20:02'),(6,'Vendeur','vendeur@scandalebouffe.com','2026-08-13 08:01:11','$2y$12$b.FNw1v3GOiYiqloLCzI4u7Kk7xNBC9b7ZT2MnS.bb8vLSYrT5cle','vendeur',NULL,'2026-08-13 07:20:27','2026-08-13 08:01:11'),(9,'Fanompoana','fanompoana@gmail.com',NULL,'$2y$10$jy8amdX7FQLw5C0UnDpaWOUZVYcbcr9gpCxPIpkeggWEJRuo8aJaO','vendeur',NULL,'2026-08-21 09:20:18',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-30 13:36:20
