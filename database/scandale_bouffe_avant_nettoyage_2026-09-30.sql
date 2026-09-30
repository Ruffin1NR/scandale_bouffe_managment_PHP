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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_deposits`
--

LOCK TABLES `daily_deposits` WRITE;
/*!40000 ALTER TABLE `daily_deposits` DISABLE KEYS */;
INSERT INTO `daily_deposits` VALUES (12,1,287600.00,'2026-09-25','2026-09-25 18:57:41'),(13,1,199400.00,'2026-09-26','2026-09-26 21:18:40');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_paiements`
--

LOCK TABLES `daily_paiements` WRITE;
/*!40000 ALTER TABLE `daily_paiements` DISABLE KEYS */;
INSERT INTO `daily_paiements` VALUES (4,1,2000.00,'2026-09-26','2026-09-26 21:19:07');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_relais`
--

LOCK TABLES `daily_relais` WRITE;
/*!40000 ALTER TABLE `daily_relais` DISABLE KEYS */;
INSERT INTO `daily_relais` VALUES (4,1,16000.00,'2026-09-25','2026-09-25 20:50:53'),(5,1,1600.00,'2026-09-26','2026-09-26 21:19:25');
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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_reports`
--

LOCK TABLES `daily_reports` WRITE;
/*!40000 ALTER TABLE `daily_reports` DISABLE KEYS */;
INSERT INTO `daily_reports` VALUES (42,1,'2026-09-25',20200.00,10000.00,193200.00,34000.00,'2026-09-25 20:37:45'),(68,1,'2026-09-26',0.00,43000.00,215800.00,11500.00,'2026-09-26 21:10:08');
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=288 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (229,95,88,'Yaourt',1,500.00,NULL,NULL),(230,96,88,'Yaourt',5,500.00,NULL,NULL),(231,97,89,'Yaourt à boire',8,1000.00,NULL,NULL),(232,98,89,'Yaourt à boire',11,1000.00,NULL,NULL),(244,110,102,'Cookies',13,1000.00,NULL,NULL),(245,111,102,'Cookies',3,1000.00,NULL,NULL),(249,115,105,'Donut',10,1000.00,NULL,NULL),(250,116,108,'Pizza',22,1200.00,NULL,NULL),(251,117,109,'Panini',8,1500.00,NULL,NULL),(252,118,110,'Hot dog',1,1500.00,NULL,NULL),(253,119,110,'Hot dog',4,1500.00,NULL,NULL),(255,121,112,'Sandwich',6,2000.00,NULL,NULL),(259,125,116,'Soupe',10,3000.00,NULL,NULL),(260,126,116,'Soupe',5,3000.00,NULL,NULL),(261,127,117,'Composé',4,1000.00,NULL,NULL),(267,133,102,'Cookies',11,1000.00,NULL,NULL),(268,134,102,'Cookies',1,1000.00,NULL,NULL),(270,136,105,'Donut',10,1000.00,NULL,NULL),(271,137,107,'Pao',5,1000.00,NULL,NULL),(272,138,112,'Sandwich',8,2000.00,NULL,NULL),(273,139,108,'Pizza',25,1200.00,NULL,NULL),(274,139,109,'Panini',10,1500.00,NULL,NULL),(275,139,110,'Hot dog',7,1500.00,NULL,NULL),(277,140,110,'Hot dog',3,1500.00,NULL,NULL),(280,143,106,'Pudding',4,1000.00,NULL,NULL),(283,143,116,'Soupe',2,3000.00,NULL,NULL),(284,144,116,'Soupe',3,3000.00,NULL,NULL),(285,144,117,'Composé',4,1000.00,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (85,1,3600.00,'2026-09-25 15:57:41',NULL,'cash'),(86,1,3600.00,'2026-09-25 15:58:10',NULL,'cash'),(87,1,800.00,'2026-09-25 15:59:38',NULL,'cash'),(88,1,500.00,'2026-09-25 15:59:48',NULL,'cash'),(89,1,2400.00,'2026-09-25 17:00:24',NULL,'cash'),(90,1,2800.00,'2026-09-25 17:00:37',NULL,'cash'),(91,1,10000.00,'2026-09-25 17:01:53',NULL,'cash'),(92,1,6800.00,'2026-09-25 17:02:09',NULL,'cash'),(93,1,2000.00,'2026-09-25 17:02:33',NULL,'cash'),(94,1,13000.00,'2026-09-25 17:02:43',NULL,'cash'),(95,1,500.00,'2026-09-25 17:06:01',NULL,'cash'),(96,1,2500.00,'2026-09-25 17:06:11',NULL,'cash'),(97,1,8000.00,'2026-09-25 17:06:49',NULL,'cash'),(98,1,11000.00,'2026-09-25 17:06:59',NULL,'cash'),(99,1,6000.00,'2026-09-25 17:07:25',NULL,'cash'),(100,1,4000.00,'2026-09-25 17:07:35',NULL,'cash'),(101,1,16500.00,'2026-09-25 17:07:53',NULL,'cash'),(102,1,7500.00,'2026-09-25 17:08:02',NULL,'cash'),(103,1,4000.00,'2026-09-25 17:09:43',NULL,'cash'),(104,1,27000.00,'2026-09-25 17:09:57',NULL,'cash'),(105,1,500.00,'2026-09-25 17:37:45',NULL,'cash'),(106,1,6000.00,'2026-09-25 17:39:34',NULL,'cash'),(107,1,7500.00,'2026-09-25 17:39:45',NULL,'cash'),(108,1,1000.00,'2026-09-25 17:41:51',NULL,'cash'),(109,1,3000.00,'2026-09-25 17:42:01',NULL,'cash'),(110,1,13000.00,'2026-09-25 17:42:26',NULL,'cash'),(111,1,3000.00,'2026-09-25 17:42:36',NULL,'cash'),(112,1,2000.00,'2026-09-25 17:43:12',NULL,'cash'),(113,1,2000.00,'2026-09-25 17:43:20',NULL,'cash'),(114,1,1500.00,'2026-09-25 17:43:27',NULL,'cash'),(115,1,10000.00,'2026-09-25 17:43:42',NULL,'cash'),(116,1,26400.00,'2026-09-25 17:44:06',NULL,'cash'),(117,1,12000.00,'2026-09-25 17:44:29',NULL,'cash'),(118,1,1500.00,'2026-09-25 17:44:34',NULL,'cash'),(119,1,6000.00,'2026-09-25 17:44:41',NULL,'cash'),(120,1,10800.00,'2026-09-25 17:44:55',NULL,'cash'),(121,1,12000.00,'2026-09-25 17:45:18',NULL,'cash'),(122,1,40000.00,'2026-09-25 17:45:37',NULL,'cash'),(123,1,5000.00,'2026-09-25 17:46:00',NULL,'cash'),(124,1,8000.00,'2026-09-25 17:46:14',NULL,'cash'),(125,1,30000.00,'2026-09-25 17:46:20',NULL,'cash'),(126,1,15000.00,'2026-09-25 17:46:28',NULL,'cash'),(127,1,4000.00,'2026-09-25 17:49:46',NULL,'cash'),(128,1,3500.00,'2026-09-25 17:49:54',NULL,'cash'),(129,1,3500.00,'2026-09-25 17:50:02',NULL,'cash'),(130,1,6000.00,'2026-09-26 18:10:08',NULL,'cash'),(131,1,7500.00,'2026-09-26 18:10:26',NULL,'cash'),(132,1,3000.00,'2026-09-26 18:11:47',NULL,'cash'),(133,1,11000.00,'2026-09-26 18:12:01',NULL,'cash'),(134,1,1000.00,'2026-09-26 18:12:06',NULL,'cash'),(135,1,1000.00,'2026-09-26 18:12:18',NULL,'cash'),(136,1,10000.00,'2026-09-26 18:12:43',NULL,'cash'),(137,1,5000.00,'2026-09-26 18:12:56',NULL,'cash'),(138,1,16000.00,'2026-09-26 18:13:13',NULL,'cash'),(139,1,66300.00,'2026-09-26 18:15:07',NULL,'cash'),(140,1,4500.00,'2026-09-26 18:15:14',NULL,'cash'),(141,1,48000.00,'2026-09-26 18:15:30',NULL,'cash'),(142,1,4000.00,'2026-09-26 18:15:36',NULL,'cash'),(143,1,20500.00,'2026-09-26 18:16:19',NULL,'cash'),(144,1,16500.00,'2026-09-26 18:16:49',NULL,'cash'),(145,1,7000.00,'2026-09-26 18:16:55',NULL,'cash');
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

-- Dump completed on 2026-09-30 13:12:47
