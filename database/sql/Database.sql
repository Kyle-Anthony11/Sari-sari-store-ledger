CREATE DATABASE  IF NOT EXISTS `sari_sari_ledger` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `sari_sari_ledger`;
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: sari_sari_ledger
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba','i:2;',1791775125),('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1791775125;',1791775125);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `credit_limit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `risk_level` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LOW',
  `days_since_last_payment` int NOT NULL DEFAULT '0',
  `accepted_terms_at` timestamp NULL DEFAULT NULL,
  `terms_attachment_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_name_unique` (`name`),
  KEY `customers_is_active_index` (`is_active`),
  KEY `customers_risk_level_index` (`risk_level`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'Maria Santos','639171234567','Purok 1, Brgy. San Jose',1,500.00,126.00,'LOW',-1,'2026-09-30 00:09:53',NULL,'2026-09-30 00:09:53','2026-09-30 00:11:54'),(2,'Juan Dela Cruz','639181234567','Purok 2, Brgy. San Jose',1,500.00,140.00,'MEDIUM',21,'2026-09-30 00:09:53',NULL,'2026-09-30 00:09:53','2026-09-30 00:11:54'),(3,'Ana Reyes','639191234567','Purok 3, Brgy. San Jose',1,500.00,212.00,'MEDIUM',15,'2026-09-30 00:09:53',NULL,'2026-09-30 00:09:53','2026-09-30 00:11:54'),(4,'Pedro Bautista','639201234567','Purok 4, Brgy. San Jose',1,500.00,86.00,'MEDIUM',12,'2026-09-30 00:09:53',NULL,'2026-09-30 00:09:53','2026-09-30 00:11:54'),(5,'Liza Mercado','639211234567','Purok 5, Brgy. San Jose',1,500.00,300.00,'MEDIUM',12,'2026-09-30 00:09:53',NULL,'2026-09-30 00:09:53','2026-09-30 00:11:54'),(6,'Ramon Villanueva','639221234567','Purok 6, Brgy. San Jose',1,500.00,34.00,'LOW',5,'2026-09-30 00:09:53',NULL,'2026-09-30 00:09:53','2026-09-30 00:11:54'),(7,'Jeje Santillan','+639216151864','Tugas Madridejos Cebu',1,300.00,0.00,'LOW',0,'2026-10-11 19:19:06','terms_attachments/k9veWlOVwRZb3l1bugSi8KpONCadGxHZ51JFW28W.jpg','2026-10-11 19:19:06','2026-10-11 19:42:20'),(8,'Rowen Bayles','+639171234567','Tugas',1,400.00,1000.00,'HIGH',365,'2026-10-11 19:32:06',NULL,'2026-10-11 19:32:06','2026-10-11 19:35:58');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_09_30_081103_change_transaction_number_to_index_on_transactions',1),(2,'2026_09_30_193702_add_min_credit_limit_to_store_settings',2),(3,'2026_09_30_193856_update_contact_number',3),(4,'2026_10_11_205225_add_min_credit_limit_to_store_settings_table',4),(5,'2026_10_12_033100_add_high_risk_test_customer',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_otps`
--

DROP TABLE IF EXISTS `password_reset_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_otps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_credential_id` bigint unsigned NOT NULL,
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_reset_otps_store_credential_id_foreign` (`store_credential_id`),
  KEY `password_reset_otps_expires_at_index` (`expires_at`),
  CONSTRAINT `password_reset_otps_store_credential_id_foreign` FOREIGN KEY (`store_credential_id`) REFERENCES `store_credentials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_otps`
--

LOCK TABLES `password_reset_otps` WRITE;
/*!40000 ALTER TABLE `password_reset_otps` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transactions_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cash_given` decimal(15,2) NOT NULL DEFAULT '0.00',
  `payment_date` datetime NOT NULL,
  `change` decimal(15,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_transactions_id_foreign` (`transactions_id`),
  KEY `payments_customer_id_foreign` (`customer_id`),
  KEY `payments_payment_date_index` (`payment_date`),
  CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_transactions_id_foreign` FOREIGN KEY (`transactions_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (2,4,1,34.00,54.00,'2026-09-02 10:30:00',20.00,'cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(3,5,2,126.00,146.00,'2026-09-09 10:30:00',20.00,'gcash','GC-1001-2025','2026-09-30 00:11:54','2026-09-30 00:11:54'),(4,7,3,80.00,100.00,'2026-09-15 10:30:00',20.00,'cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(5,8,4,180.00,200.00,'2026-09-18 10:30:00',20.00,'cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(6,10,5,120.00,140.00,'2026-09-18 10:30:00',20.00,'gcash','GC-1002-2025','2026-09-30 00:11:54','2026-09-30 00:11:54'),(7,11,6,240.00,260.00,'2026-09-25 10:30:00',20.00,'cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(8,13,1,100.00,120.00,'2026-10-01 10:30:00',20.00,'cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(9,29,7,63.00,63.00,'2026-10-12 03:42:20',0.00,'cash',NULL,'2026-10-11 19:42:20','2026-10-11 19:42:20');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_name_index` (`name`),
  KEY `products_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Skyflakes Crackers','Pieces',12.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(2,'Pancit Canton','Pieces',18.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(3,'Coke in Can','Pieces',35.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(4,'Bear Brand 33g','Pieces',25.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(5,'Rice (Sinandomeng)','Kilos',55.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(6,'Sugar (White)','Kilos',65.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(7,'Cooking Oil 500ml','Pieces',45.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(8,'Eggs (Medium)','Pieces',8.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(9,'Sardinas 155g','Pieces',25.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53'),(10,'Piattos Cheese','Pieces',35.00,1,'2026-09-30 00:09:53','2026-09-30 00:09:53');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
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
INSERT INTO `sessions` VALUES ('3sF9tZlyM75T1TAlZE8gvv6hZVtT3o9CO2UteEdW',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJOS0w1Vm5Ga1I3WEpqVHFzOXdwOE44bHB5SVJwaVQxbFptaXFPSEcyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInN0b3JlX2NyZWRlbnRpYWxfaWQiOjEsInN0b3JlX3Byb2ZpbGVfaWQiOjF9',1792148991),('8FaVaX9Rzk61Ksr0revEEwVPZjigm8mGTFmpJLai',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIxeGtySUxnYWlXTVJpTVU1UEdGeVN4N3cwa2p2Y1NkdE0xRGRya3FVIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sInN0b3JlX2NyZWRlbnRpYWxfaWQiOjEsInN0b3JlX3Byb2ZpbGVfaWQiOjF9',1791786483),('HpnJtavaPLfWEM8Pm4gaCLNbSIid8izi4WGxWsiT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJuNTR5dzExQ1hSbjhDbTdsRHdDTGlCOTRCUW1ITVJPZHRqNldSZjNKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1792148955);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_credentials`
--

DROP TABLE IF EXISTS `store_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `store_credentials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_profile_id` bigint unsigned NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_credentials_email_unique` (`email`),
  KEY `store_credentials_store_profile_id_foreign` (`store_profile_id`),
  CONSTRAINT `store_credentials_store_profile_id_foreign` FOREIGN KEY (`store_profile_id`) REFERENCES `store_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_credentials`
--

LOCK TABLES `store_credentials` WRITE;
/*!40000 ALTER TABLE `store_credentials` DISABLE KEYS */;
INSERT INTO `store_credentials` VALUES (1,1,'i.am.ylean1@gmail.com','$2y$12$n0cAsAuDu9orm/jrvDUyo.Ifp/4HTVIdIQkfu6RYFiyXtFFMPyDhy','$2y$12$6qDl2vsYUCcsZUv3EXQY4O1sIh1SY.55bbjiG3V846laXK.mBmJs6','2026-09-30 00:06:18',NULL,'2026-09-30 00:02:33','2026-09-30 00:06:18');
/*!40000 ALTER TABLE `store_credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_profiles`
--

DROP TABLE IF EXISTS `store_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `store_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_suffix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_notes` text COLLATE utf8mb4_unicode_ci,
  `store_credentials_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_profiles_store_credentials_id_unique` (`store_credentials_id`),
  CONSTRAINT `store_profiles_store_credentials_id_foreign` FOREIGN KEY (`store_credentials_id`) REFERENCES `store_credentials` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_profiles`
--

LOCK TABLES `store_profiles` WRITE;
/*!40000 ALTER TABLE `store_profiles` DISABLE KEYS */;
INSERT INTO `store_profiles` VALUES (1,'Fatima','Boltron','Sevilla','','SÑ Variety Shop','63090524458','Madridejos Public Market, Talangnan, Madridejos, Cebu','',1,'2026-09-30 00:02:32','2026-09-30 00:02:33');
/*!40000 ALTER TABLE `store_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_settings`
--

DROP TABLE IF EXISTS `store_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `store_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_profile_id` bigint unsigned NOT NULL,
  `surcharge` decimal(15,2) NOT NULL DEFAULT '0.00',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `transaction_duration_days` int NOT NULL DEFAULT '30',
  `max_credit_limit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `min_credit_limit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_settings_store_profile_id_foreign` (`store_profile_id`),
  CONSTRAINT `store_settings_store_profile_id_foreign` FOREIGN KEY (`store_profile_id`) REFERENCES `store_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_settings`
--

LOCK TABLES `store_settings` WRITE;
/*!40000 ALTER TABLE `store_settings` DISABLE KEYS */;
INSERT INTO `store_settings` VALUES (1,1,5.00,6.00,7,500.00,100.00,'2026-09-30 00:02:33','2026-10-03 04:56:58');
/*!40000 ALTER TABLE `store_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_history`
--

DROP TABLE IF EXISTS `transaction_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `transactions_id` bigint unsigned DEFAULT NULL,
  `payments_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `surcharge_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `duration_days` int NOT NULL DEFAULT '0',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cash_given` decimal(15,2) NOT NULL DEFAULT '0.00',
  `change` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PAID',
  `payment_method` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_history_customer_id_foreign` (`customer_id`),
  KEY `transaction_history_transactions_id_foreign` (`transactions_id`),
  KEY `transaction_history_payments_id_foreign` (`payments_id`),
  KEY `transaction_history_payment_date_index` (`payment_date`),
  KEY `transaction_history_status_index` (`status`),
  CONSTRAINT `transaction_history_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_history_payments_id_foreign` FOREIGN KEY (`payments_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_history_transactions_id_foreign` FOREIGN KEY (`transactions_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_history`
--

LOCK TABLES `transaction_history` WRITE;
/*!40000 ALTER TABLE `transaction_history` DISABLE KEYS */;
INSERT INTO `transaction_history` VALUES (2,1,4,2,'Maria Santos','Skyflakes Crackers','2026-08-29','2026-09-05','2026-09-02 10:30:00',10.00,0.00,7,34.00,54.00,20.00,'PAID','cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(3,2,5,3,'Juan Dela Cruz','Pancit Canton','2026-09-04','2026-09-11','2026-09-09 10:30:00',10.00,0.00,7,46.00,146.00,20.00,'PAID','gcash','GC-1001-2025','2026-09-30 00:11:54','2026-09-30 00:11:54'),(4,2,6,3,'Juan Dela Cruz','Coke in Can','2026-09-04','2026-09-11','2026-09-09 10:30:00',10.00,0.00,7,80.00,146.00,20.00,'PAID','gcash','GC-1001-2025','2026-09-30 00:11:54','2026-09-30 00:11:54'),(5,3,7,4,'Ana Reyes','Coke in Can','2026-09-08','2026-09-15','2026-09-15 10:30:00',10.00,0.00,7,80.00,100.00,20.00,'PAID','cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(6,4,8,5,'Pedro Bautista','Bear Brand 33g','2026-09-12','2026-09-19','2026-09-18 10:30:00',10.00,0.00,7,60.00,200.00,20.00,'PAID','cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(7,4,9,5,'Pedro Bautista','Rice (Sinandomeng)','2026-09-12','2026-09-19','2026-09-18 10:30:00',10.00,0.00,7,120.00,200.00,20.00,'PAID','cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(8,5,10,6,'Liza Mercado','Rice (Sinandomeng)','2026-09-15','2026-09-22','2026-09-18 10:30:00',10.00,0.00,7,120.00,140.00,20.00,'PAID','gcash','GC-1002-2025','2026-09-30 00:11:54','2026-09-30 00:11:54'),(9,6,11,7,'Ramon Villanueva','Sugar (White)','2026-09-20','2026-09-27','2026-09-25 10:30:00',10.00,0.00,7,140.00,260.00,20.00,'PAID','cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(10,6,12,7,'Ramon Villanueva','Cooking Oil 500ml','2026-09-20','2026-09-27','2026-09-25 10:30:00',10.00,0.00,7,100.00,260.00,20.00,'PAID','cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(11,1,13,8,'Maria Santos','Cooking Oil 500ml','2026-09-24','2026-10-01','2026-10-01 10:30:00',10.00,0.00,7,100.00,120.00,20.00,'PAID','cash',NULL,'2026-09-30 00:11:54','2026-09-30 00:11:54'),(12,7,29,9,'Jeje Santillan','Pancit Canton','2026-10-12','2026-10-19','2026-10-12 03:42:20',5.00,6.00,7,23.00,63.00,0.00,'PAID','cash',NULL,'2026-10-11 19:42:20','2026-10-11 19:42:20'),(13,7,30,9,'Jeje Santillan','Piattos Cheese','2026-10-12','2026-10-19','2026-10-12 03:42:20',5.00,6.00,7,40.00,63.00,0.00,'PAID','cash',NULL,'2026-10-11 19:42:20','2026-10-11 19:42:20');
/*!40000 ALTER TABLE `transaction_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_number_sequences`
--

DROP TABLE IF EXISTS `transaction_number_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_number_sequences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `last_number` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_number_sequences_date_unique` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_number_sequences`
--

LOCK TABLES `transaction_number_sequences` WRITE;
/*!40000 ALTER TABLE `transaction_number_sequences` DISABLE KEYS */;
INSERT INTO `transaction_number_sequences` VALUES (2,'2026-09-30',16),(3,'2026-10-12',1);
/*!40000 ALTER TABLE `transaction_number_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_reminders`
--

DROP TABLE IF EXISTS `transaction_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_reminders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `week_number` int NOT NULL DEFAULT '0',
  `sent_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_reminders_unique` (`group_id`,`type`,`week_number`),
  KEY `transaction_reminders_group_id_index` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_reminders`
--

LOCK TABLES `transaction_reminders` WRITE;
/*!40000 ALTER TABLE `transaction_reminders` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction_reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `products_id` bigint unsigned NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `base_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `surcharge_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `transaction_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `payment_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surcharge_at_creation` decimal(15,2) NOT NULL DEFAULT '0.00',
  `interest_rate_at_creation` decimal(5,2) NOT NULL DEFAULT '0.00',
  `duration_days_at_creation` int NOT NULL DEFAULT '0',
  `group_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_products_id_foreign` (`products_id`),
  KEY `transactions_customer_id_foreign` (`customer_id`),
  KEY `transactions_payment_id_foreign` (`payment_id`),
  KEY `transactions_group_id_index` (`group_id`),
  KEY `transactions_status_index` (`status`),
  KEY `transactions_due_date_index` (`due_date`),
  KEY `transactions_transaction_date_index` (`transaction_date`),
  KEY `transactions_transaction_number_index` (`transaction_number`),
  CONSTRAINT `transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_products_id_foreign` FOREIGN KEY (`products_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (4,1,NULL,1,2.00,34.00,24.00,10.00,0.00,'2026-08-29','2026-09-05','PAID',2,'','Skyflakes Crackers','Pieces',5.00,5.00,7,'327a0fc2-ebb6-4102-a0bd-4f93ceba108a','2026093000001','2026-09-30 00:11:54','2026-09-30 00:11:54'),(5,2,NULL,2,2.00,46.00,36.00,10.00,0.00,'2026-09-04','2026-09-11','PAID',3,'','Pancit Canton','Pieces',5.00,5.00,7,'6a82ad1b-fc49-4ed6-9cad-209c2281802a','2026093000002','2026-09-30 00:11:54','2026-09-30 00:11:54'),(6,3,NULL,2,2.00,80.00,70.00,10.00,0.00,'2026-09-04','2026-09-11','PAID',3,'','Coke in Can','Pieces',5.00,5.00,7,'6a82ad1b-fc49-4ed6-9cad-209c2281802a','2026093000002','2026-09-30 00:11:54','2026-09-30 00:11:54'),(7,3,NULL,3,2.00,80.00,70.00,10.00,0.00,'2026-09-08','2026-09-15','PAID',4,'','Coke in Can','Pieces',5.00,5.00,7,'41ff0776-e606-4c7e-ad33-2b6ae60c5675','2026093000003','2026-09-30 00:11:54','2026-09-30 00:11:54'),(8,4,NULL,4,2.00,60.00,50.00,10.00,0.00,'2026-09-12','2026-09-19','PAID',5,'','Bear Brand 33g','Pieces',5.00,5.00,7,'5ddd8b8e-0ece-4875-82a4-756b51d993da','2026093000004','2026-09-30 00:11:54','2026-09-30 00:11:54'),(9,5,NULL,4,2.00,120.00,110.00,10.00,0.00,'2026-09-12','2026-09-19','PAID',5,'','Rice (Sinandomeng)','Kilos',5.00,5.00,7,'5ddd8b8e-0ece-4875-82a4-756b51d993da','2026093000004','2026-09-30 00:11:54','2026-09-30 00:11:54'),(10,5,NULL,5,2.00,120.00,110.00,10.00,0.00,'2026-09-15','2026-09-22','PAID',6,'','Rice (Sinandomeng)','Kilos',5.00,5.00,7,'d2fcefc9-0bdc-472e-a667-91112b1ff3fb','2026093000005','2026-09-30 00:11:54','2026-09-30 00:11:54'),(11,6,NULL,6,2.00,140.00,130.00,10.00,0.00,'2026-09-20','2026-09-27','PAID',7,'','Sugar (White)','Kilos',5.00,5.00,7,'b4643de8-defa-49b0-8b12-5256ad4a7d35','2026093000006','2026-09-30 00:11:54','2026-09-30 00:11:54'),(12,7,NULL,6,2.00,100.00,90.00,10.00,0.00,'2026-09-20','2026-09-27','PAID',7,'','Cooking Oil 500ml','Pieces',5.00,5.00,7,'b4643de8-defa-49b0-8b12-5256ad4a7d35','2026093000006','2026-09-30 00:11:54','2026-09-30 00:11:54'),(13,7,NULL,1,2.00,100.00,90.00,10.00,0.00,'2026-09-24','2026-10-01','PAID',8,'','Cooking Oil 500ml','Pieces',5.00,5.00,7,'49ea6ca1-a725-4f0c-9bc9-09c6210a3641','2026093000007','2026-09-30 00:11:54','2026-09-30 00:11:54'),(14,8,NULL,3,2.00,26.00,16.00,10.00,5.00,'2026-09-25','2026-10-02','PENDING',NULL,'','Eggs (Medium)','Pieces',5.00,5.00,7,'77e4cf0a-82f6-45b1-8767-58910aa63b9b','2026093000008','2026-09-30 00:11:54','2026-09-30 00:11:54'),(15,9,NULL,3,2.00,60.00,50.00,10.00,5.00,'2026-09-25','2026-10-02','PENDING',NULL,'','Sardinas 155g','Pieces',5.00,5.00,7,'77e4cf0a-82f6-45b1-8767-58910aa63b9b','2026093000008','2026-09-30 00:11:54','2026-09-30 00:11:54'),(16,9,NULL,4,2.00,60.00,50.00,10.00,5.00,'2026-09-27','2026-10-04','PENDING',NULL,'','Sardinas 155g','Pieces',5.00,5.00,7,'668c7fd2-2d00-445d-ae58-fa9041a74661','2026093000009','2026-09-30 00:11:54','2026-09-30 00:11:54'),(17,10,NULL,5,2.00,80.00,70.00,10.00,5.00,'2026-09-28','2026-10-05','PENDING',NULL,'','Piattos Cheese','Pieces',5.00,5.00,7,'bfff00f7-6e59-4e2d-a605-d53279a402a5','2026093000010','2026-09-30 00:11:54','2026-09-30 00:11:54'),(18,1,NULL,5,2.00,34.00,24.00,10.00,5.00,'2026-09-28','2026-10-05','PENDING',NULL,'','Skyflakes Crackers','Pieces',5.00,5.00,7,'bfff00f7-6e59-4e2d-a605-d53279a402a5','2026093000010','2026-09-30 00:11:54','2026-09-30 00:11:54'),(19,2,NULL,5,2.00,46.00,36.00,10.00,5.00,'2026-09-28','2026-10-05','PENDING',NULL,'','Pancit Canton','Pieces',5.00,5.00,7,'bfff00f7-6e59-4e2d-a605-d53279a402a5','2026093000010','2026-09-30 00:11:54','2026-09-30 00:11:54'),(20,1,NULL,6,2.00,34.00,24.00,10.00,5.00,'2026-09-29','2026-10-06','PENDING',NULL,'','Skyflakes Crackers','Pieces',5.00,5.00,7,'b2ccdc2a-50a1-4259-9b51-97b2e7e51faf','2026093000011','2026-09-30 00:11:54','2026-09-30 00:11:54'),(21,2,NULL,1,2.00,46.00,36.00,10.00,5.00,'2026-09-30','2026-10-07','PENDING',NULL,'','Pancit Canton','Pieces',5.00,5.00,7,'de25161e-6c75-4919-b609-235e37bafe74','2026093000012','2026-09-30 00:11:54','2026-09-30 00:11:54'),(22,3,NULL,1,2.00,80.00,70.00,10.00,5.00,'2026-09-30','2026-10-07','PENDING',NULL,'','Coke in Can','Pieces',5.00,5.00,7,'de25161e-6c75-4919-b609-235e37bafe74','2026093000012','2026-09-30 00:11:54','2026-09-30 00:11:54'),(23,6,NULL,2,2.00,140.00,130.00,10.00,5.00,'2026-09-18','2026-09-25','OVERDUE',NULL,'','Sugar (White)','Kilos',5.00,5.00,7,'9bdf67e2-2806-4395-88c1-ff6108cac050','2026093000013','2026-09-30 00:11:54','2026-09-30 00:11:54'),(24,7,NULL,3,2.00,100.00,90.00,10.00,5.00,'2026-09-12','2026-09-19','OVERDUE',NULL,'','Cooking Oil 500ml','Pieces',5.00,5.00,7,'6ab3bc4c-2a3f-4c66-b6c2-f60f4d869332','2026093000014','2026-09-30 00:11:54','2026-09-30 00:11:54'),(25,8,NULL,3,2.00,26.00,16.00,10.00,5.00,'2026-09-12','2026-09-19','OVERDUE',NULL,'','Eggs (Medium)','Pieces',5.00,5.00,7,'6ab3bc4c-2a3f-4c66-b6c2-f60f4d869332','2026093000014','2026-09-30 00:11:54','2026-09-30 00:11:54'),(26,8,NULL,4,2.00,26.00,16.00,10.00,5.00,'2026-09-05','2026-09-12','OVERDUE',NULL,'','Eggs (Medium)','Pieces',5.00,5.00,7,'5b765216-9294-41f6-8747-26ce26b5f5c0','2026093000015','2026-09-30 00:11:54','2026-09-30 00:11:54'),(27,9,NULL,5,2.00,60.00,50.00,10.00,5.00,'2026-08-21','2026-08-28','OVERDUE',NULL,'','Sardinas 155g','Pieces',5.00,5.00,7,'d9dd068c-f83c-4f4c-8894-6d45297273ba','2026093000016','2026-09-30 00:11:54','2026-09-30 00:11:54'),(28,10,NULL,5,2.00,80.00,70.00,10.00,5.00,'2026-08-21','2026-08-28','OVERDUE',NULL,'','Piattos Cheese','Pieces',5.00,5.00,7,'d9dd068c-f83c-4f4c-8894-6d45297273ba','2026093000016','2026-09-30 00:11:54','2026-09-30 00:11:54'),(29,2,NULL,7,1.00,23.00,18.00,5.00,6.00,'2026-10-12','2026-10-19','PAID',9,'','Pancit Canton','Pieces',5.00,6.00,7,'2308c20e-d081-4713-8329-9ff8e6a99ee6','2026101200001','2026-10-11 19:19:17','2026-10-11 19:42:20'),(30,10,NULL,7,1.00,40.00,35.00,5.00,6.00,'2026-10-12','2026-10-19','PAID',9,'','Piattos Cheese','Pieces',5.00,6.00,7,'2308c20e-d081-4713-8329-9ff8e6a99ee6','2026101200001','2026-10-11 19:19:17','2026-10-11 19:42:20'),(31,1,NULL,8,1.00,1000.00,900.00,100.00,5.00,'2026-08-13','2026-09-12','OVERDUE',NULL,NULL,'Skyflakes Crackers','Pieces',100.00,5.00,30,'142408a8-09d1-422e-b295-4634d622cfeb',NULL,'2026-10-11 19:32:08','2026-10-11 19:32:08');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2026-10-19 10:10:49
