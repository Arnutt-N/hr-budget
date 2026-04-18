-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: 
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!50606 SET @OLD_INNODB_STATS_AUTO_RECALC=@@INNODB_STATS_AUTO_RECALC */;
/*!50606 SET GLOBAL INNODB_STATS_AUTO_RECALC=OFF */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Current Database: `hr_budget`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `hr_budget` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `hr_budget`;

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL COMMENT 'FK: activities.id (Parent Activity)',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `fiscal_year` int DEFAULT '2568',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธ',
  `updated_by` int DEFAULT NULL COMMENT 'เธเธนเนเนเธเนเนเธเธฅเนเธฒเธชเธธเธ',
  `level` int DEFAULT '0' COMMENT 'Level: 0=Root, 1=Sub, 2=Sub-Sub',
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project_id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_activities_deleted` (`deleted_at`),
  KEY `idx_activities_parent` (`parent_id`),
  KEY `idx_activities_level` (`level`),
  CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activities_ibfk_parent` FOREIGN KEY (`parent_id`) REFERENCES `activities` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธดเธเธเธฃเธฃเธก';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
INSERT INTO `activities` VALUES (31,21,NULL,'AC-c7c4d0','รายการค่าใช้จ่ายบุคลากรภาครัฐ',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL,0),(32,22,NULL,'AC-6a15c2','การอำนวยการการด้านการบริหารจัดการให้แก่หน่วยงานในสังกัด',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(33,22,NULL,'AC-5521b3','การเสริมสร้างความร่วมมือกับประชาชมระหว่างประเทศด้านกฎหมายและกระบวนการยุติธรรม',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(34,22,NULL,'AC-1bb627','การขับเคลื่อนและเตรียมความพร้อมประเทศไทยสู่การเข้าเป็นภาคีญาสหประชาชาติว่าด้วยสัญญาซื้อขายสินค้าระหว่างประเทศ',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(35,22,NULL,'AC-683acd','การประชาสัมพันธ์สร้างการรับรู้ด้านกฎหมายและกระบวนการยุติธรรมแก่ประชาชน',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(36,23,NULL,'AC-9ad906','ส่งเสริมการอำนวยความยุติธรรมของกระทรวงยุติธรรมที่สอดคล้องกับวิถีชีวิตของประชาชนในพื้นที่จังหวัดชายแดนภาคใต้',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(37,24,NULL,'AC-0a392a','การพัฒนาระบบงานยุติธรรมและส่งเสริมให้ประชาชนเข้าถึงความเป็นธรรม',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(38,24,NULL,'AC-60665f','การให้ความช่วยเหลือประชาชนที่ไม่ได้รับความเป็นธรรม',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(39,25,NULL,'AC-db2d7c','การพัฒนากฎหมาย',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(40,26,NULL,'AC-ad8a5c','การขับเคลื่อนงานศูนย์ยุติธรรมชุมชน',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(41,26,NULL,'AC-528548','ส่งเสริม และสนับสนุน และสร้างความร่วมมือในการสร้างงาน สร้างอาชีพ เพื่อแก้ไขปัญหาการกระทำผิดซ้ำ',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(42,27,NULL,'AC-41198b','ส่งเสริมความปลอดภัยด้านการท่องเที่ยว',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(43,28,NULL,'AC-a6c3ad','การพัฒนาทักษะดิจิทัลสำหรับบุคลากรภาครัฐเพื่อการขับเคลื่อนรัฐบาลดิจิทัล',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(44,29,NULL,'AC-e5853f','สนับสนุนการดำเนินงานตามนโยบายการใช้คลาวด์เป็นหลัก (Cloud First Policy)',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(45,30,NULL,'AC-35963d','พัฒนาระบบบริหารเพื่อต่อต้านการทุจริตและส่งเสริมคุ้มครองจริยธรรม',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0);
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = tis620 */ ;
/*!50003 SET character_set_results = tis620 */ ;
/*!50003 SET collation_connection  = tis620_thai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_activities_check_circular_insert` BEFORE INSERT ON `activities` FOR EACH ROW BEGIN
    IF NEW.parent_id IS NOT NULL AND NEW.parent_id = 0 THEN 
         SET NEW.parent_id = NULL; 
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = tis620 */ ;
/*!50003 SET character_set_results = tis620 */ ;
/*!50003 SET collation_connection  = tis620_thai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_activities_check_circular_update` BEFORE UPDATE ON `activities` FOR EACH ROW BEGIN
    DECLARE current_parent INT;
    
    IF NEW.parent_id IS NOT NULL AND (OLD.parent_id IS NULL OR NEW.parent_id != OLD.parent_id) THEN
        IF NEW.parent_id = NEW.id THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Activity cannot be its own parent.';
        END IF;
        
        SET current_parent = NEW.parent_id;
        
        WHILE current_parent IS NOT NULL DO
            IF current_parent = NEW.id THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Circular reference detected in activity hierarchy.';
            END IF;
            
            SELECT parent_id INTO current_parent FROM activities WHERE id = current_parent;
            IF current_parent = 0 THEN SET current_parent = NULL; END IF;
        END WHILE;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_activity_logs_user_id` (`user_id`),
  CONSTRAINT `fk_activity_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,2,'logout','User logged out','::1','2025-12-14 05:16:17'),(2,2,'logout','User logged out','::1','2025-12-14 05:17:28'),(3,2,'logout','User logged out','::1','2025-12-14 05:19:11'),(4,2,'logout','User logged out','::1','2025-12-14 05:25:53'),(5,2,'login','User logged in successfully','::1','2025-12-14 05:28:27'),(6,2,'logout','User logged out','::1','2025-12-14 05:29:10'),(7,2,'login','User logged in successfully','::1','2025-12-14 05:29:12'),(8,2,'logout','User logged out','::1','2025-12-14 07:02:05'),(9,2,'login','User logged in successfully','::1','2025-12-14 07:02:09'),(10,2,'logout','User logged out','::1','2025-12-14 07:21:31'),(11,2,'login','User logged in successfully','::1','2025-12-14 09:56:06'),(12,2,'logout','User logged out','::1','2025-12-14 10:38:32'),(13,2,'login','User logged in successfully','::1','2025-12-14 10:38:34'),(14,2,'logout','User logged out','::1','2025-12-14 11:31:19'),(15,2,'login','User logged in successfully','::1','2025-12-14 11:31:22'),(16,2,'login','User logged in successfully','::1','2025-12-14 13:32:28'),(17,2,'logout','User logged out','::1','2025-12-14 14:18:21'),(18,2,'login','User logged in successfully','::1','2025-12-14 14:18:23'),(19,2,'login','User logged in successfully','::1','2025-12-14 16:18:41'),(20,2,'logout','User logged out','::1','2025-12-14 17:28:55'),(21,2,'login','User logged in successfully','::1','2025-12-14 17:29:01'),(22,2,'login','User logged in successfully','::1','2025-12-14 19:33:14'),(23,2,'logout','User logged out','::1','2025-12-14 19:48:21'),(24,2,'login','User logged in successfully','::1','2025-12-14 19:48:23'),(25,2,'logout','User logged out','::1','2025-12-14 20:01:26'),(26,2,'login','User logged in successfully','::1','2025-12-14 20:01:28'),(27,2,'logout','User logged out','::1','2025-12-14 21:38:27'),(28,2,'login','User logged in successfully','::1','2025-12-14 21:38:29'),(29,2,'login','User logged in successfully','::1','2025-12-16 00:10:36'),(30,2,'logout','User logged out','::1','2025-12-16 00:11:39'),(31,2,'login','User logged in successfully','::1','2025-12-16 00:12:47'),(32,2,'login','User logged in successfully','::1','2025-12-17 13:15:58'),(33,2,'login','User logged in successfully','::1','2025-12-17 15:05:50'),(34,2,'login','User logged in successfully','::1','2025-12-17 15:06:05'),(35,2,'login','User logged in successfully','::1','2025-12-17 17:20:36'),(36,189,'login','User logged in via ThaID (Mock)','::1','2025-12-18 11:30:52'),(37,2,'login','User logged in successfully','::1','2025-12-18 11:53:05'),(38,2,'login','User logged in successfully','::1','2025-12-18 13:57:17'),(39,2,'login','User logged in successfully','::1','2025-12-18 14:15:07'),(40,2,'login','User logged in successfully','::1','2025-12-18 16:42:02'),(41,2,'login','User logged in successfully','::1','2025-12-18 17:01:58'),(42,2,'login','User logged in successfully','::1','2025-12-19 09:44:22'),(43,2,'login','User logged in successfully','::1','2025-12-19 11:28:41'),(44,2,'login','User logged in successfully','::1','2025-12-19 11:51:18'),(45,189,'login','User logged in via ThaID (Mock)','::1','2025-12-19 14:01:37'),(46,2,'login','User logged in successfully','::1','2025-12-19 14:03:04'),(47,2,'login','User logged in successfully','::1','2025-12-19 16:09:28'),(48,2,'login','User logged in successfully','::1','2025-12-19 16:09:54'),(49,2,'login','User logged in successfully','::1','2025-12-20 01:28:31'),(50,2,'login','User logged in successfully','::1','2025-12-20 03:51:02'),(51,2,'login','User logged in successfully','::1','2025-12-20 04:06:28'),(52,2,'logout','User logged out','::1','2025-12-20 04:13:59'),(53,2,'login','User logged in successfully','::1','2025-12-20 04:14:01'),(54,2,'logout','User logged out','::1','2025-12-20 04:14:07'),(55,2,'login','User logged in successfully','::1','2025-12-20 04:14:16'),(56,2,'login','User logged in successfully','::1','2025-12-20 06:11:46'),(57,2,'login','User logged in successfully','::1','2025-12-20 06:14:48'),(58,2,'login','User logged in successfully','::1','2025-12-20 08:37:27'),(59,2,'login','User logged in successfully','::1','2025-12-21 03:14:21'),(60,2,'login','User logged in successfully','::1','2025-12-21 03:22:31'),(61,2,'login','User logged in successfully','127.0.0.1','2025-12-21 06:57:17'),(62,189,'login','User logged in via ThaID (Mock)','::1','2025-12-22 00:44:10'),(63,189,'logout','User logged out','::1','2025-12-22 01:51:24'),(64,189,'login','User logged in via ThaID (Mock)','::1','2025-12-22 01:51:38'),(65,189,'logout','User logged out','::1','2025-12-22 01:52:12'),(66,2,'login','User logged in successfully','127.0.0.1','2025-12-23 15:49:24'),(67,2,'login','User logged in successfully','127.0.0.1','2025-12-24 11:54:33'),(68,2,'login','User logged in successfully','127.0.0.1','2025-12-25 14:10:22'),(69,2,'login','User logged in successfully','127.0.0.1','2025-12-25 14:50:03'),(70,2,'login','User logged in successfully','127.0.0.1','2025-12-27 05:48:58'),(71,2,'login','User logged in successfully','::1','2025-12-27 06:26:08'),(72,2,'login','User logged in successfully','::1','2025-12-27 08:27:31'),(73,2,'login','User logged in successfully','::1','2025-12-27 11:30:33'),(74,2,'login','User logged in successfully','::1','2025-12-28 05:31:37'),(75,2,'login','User logged in successfully','::1','2025-12-29 06:34:28'),(76,2,'login','User logged in successfully','::1','2025-12-29 08:51:26'),(77,2,'login','User logged in successfully','::1','2025-12-29 12:01:20'),(78,2,'login','User logged in successfully','::1','2025-12-29 12:34:54'),(79,2,'login','User logged in successfully','::1','2025-12-29 16:38:56'),(80,2,'login','User logged in successfully','::1','2025-12-29 18:05:40'),(81,2,'login','User logged in successfully','::1','2025-12-30 14:41:13'),(82,2,'login','User logged in successfully','::1','2025-12-31 07:39:03'),(83,2,'logout','User logged out','::1','2025-12-31 07:40:09'),(84,2,'login','User logged in successfully','::1','2025-12-31 07:40:38'),(85,2,'login','User logged in successfully','::1','2025-12-31 09:47:08'),(86,2,'login','User logged in successfully','::1','2025-12-31 10:53:26'),(87,2,'logout','User logged out','::1','2025-12-31 11:05:17'),(88,2,'login','User logged in successfully','::1','2025-12-31 11:18:02'),(89,2,'login','User logged in successfully','::1','2025-12-31 11:48:03'),(90,2,'login','User logged in successfully','::1','2025-12-31 14:27:28'),(91,2,'login','User logged in successfully','::1','2025-12-31 16:28:29'),(92,2,'login','User logged in successfully','::1','2026-01-01 06:29:10'),(93,2,'login','User logged in successfully','::1','2026-01-01 08:35:25'),(94,2,'login','User logged in successfully','::1','2026-01-01 09:58:12'),(95,2,'login','User logged in successfully','::1','2026-01-02 07:10:07'),(96,2,'login','User logged in successfully','::1','2026-01-02 07:10:20'),(97,2,'login','User logged in successfully','::1','2026-01-02 09:59:56'),(98,2,'login','User logged in successfully','::1','2026-01-02 13:42:48'),(99,189,'login','User logged in via ThaID (Mock)','127.0.0.1','2026-01-02 16:55:28'),(100,2,'login','User logged in successfully','::1','2026-01-02 17:10:15'),(101,2,'login','User logged in successfully','::1','2026-01-03 15:06:53'),(102,2,'logout','User logged out','::1','2026-01-03 15:17:20'),(103,2,'login','User logged in successfully','::1','2026-01-03 15:17:25'),(104,2,'login','User logged in successfully','::1','2026-01-03 17:22:01'),(105,2,'login','User logged in successfully','::1','2026-01-03 22:31:51'),(106,2,'login','User logged in successfully','::1','2026-01-04 10:54:51'),(107,2,'login','User logged in successfully','::1','2026-01-04 12:55:02'),(108,2,'login','User logged in successfully','::1','2026-01-04 15:30:55'),(109,2,'login','User logged in successfully','::1','2026-01-04 17:35:41'),(110,2,'login','User logged in successfully','::1','2026-01-04 18:19:38'),(111,2,'login','User logged in successfully','::1','2026-01-04 20:21:15'),(112,2,'login','User logged in successfully','::1','2026-01-05 14:06:31'),(113,2,'logout','User logged out','::1','2026-01-05 14:07:08'),(114,2,'login','User logged in successfully','::1','2026-01-05 14:07:30'),(115,2,'logout','User logged out','::1','2026-01-05 15:27:48'),(116,2,'login','User logged in successfully','::1','2026-01-05 15:27:53'),(117,2,'login','User logged in successfully','::1','2026-01-06 01:40:57'),(118,2,'login','User logged in successfully','::1','2026-01-06 11:44:31'),(119,2,'login','User logged in successfully','::1','2026-01-06 13:43:59'),(120,2,'login','User logged in successfully','::1','2026-01-06 15:57:20'),(121,2,'login','User logged in successfully','::1','2026-01-06 16:09:37'),(122,2,'login','User logged in successfully','::1','2026-01-06 18:25:45'),(123,2,'login','User logged in successfully','127.0.0.1','2026-01-07 11:52:49'),(124,2,'login','User logged in successfully','::1','2026-01-07 20:28:10'),(125,2,'login','User logged in successfully','::1','2026-01-07 22:34:54'),(126,2,'login','User logged in successfully','::1','2026-01-08 00:37:30'),(127,2,'login','User logged in successfully','::1','2026-01-08 10:44:51'),(128,2,'login','User logged in successfully','::1','2026-01-08 14:26:10'),(129,2,'login','User logged in successfully','::1','2026-01-09 12:13:18'),(130,2,'login','User logged in successfully','::1','2026-01-09 21:25:19'),(131,2,'login','User logged in successfully','::1','2026-01-10 03:17:17'),(132,2,'login','User logged in successfully','::1','2026-01-10 08:02:31'),(133,2,'login','User logged in successfully','::1','2026-01-10 09:22:26'),(134,2,'login','User logged in successfully','::1','2026-01-10 10:38:58'),(135,2,'login','User logged in successfully','::1','2026-01-10 11:48:34'),(136,2,'login','User logged in successfully','::1','2026-01-10 12:53:03'),(137,2,'login','User logged in successfully','::1','2026-01-10 14:59:15'),(138,2,'login','User logged in successfully','::1','2026-01-11 03:25:25'),(139,2,'login','User logged in successfully','::1','2026-01-11 04:11:14'),(140,2,'login','User logged in successfully','::1','2026-01-11 05:52:45'),(141,2,'login','User logged in successfully','::1','2026-01-11 12:26:34'),(142,2,'login','User logged in successfully','::1','2026-01-11 14:26:37'),(143,2,'login','User logged in successfully','::1','2026-01-11 14:56:48'),(144,2,'login','User logged in successfully','::1','2026-01-11 16:26:47'),(145,2,'login','User logged in successfully','::1','2026-01-12 10:39:14'),(146,2,'logout','User logged out','::1','2026-01-12 12:29:19'),(147,2,'login','User logged in successfully','::1','2026-01-12 12:29:24'),(148,2,'login','User logged in successfully','::1','2026-01-12 14:52:12'),(149,2,'login','User logged in successfully','::1','2026-01-12 16:58:55'),(150,2,'login','User logged in successfully','::1','2026-01-13 11:35:40'),(151,2,'logout','User logged out','::1','2026-01-13 12:50:05'),(152,2,'login','User logged in successfully','::1','2026-01-13 12:50:07'),(153,2,'logout','User logged out','::1','2026-01-13 13:56:10'),(154,2,'login','User logged in successfully','::1','2026-01-13 13:56:12'),(155,2,'login','User logged in successfully','::1','2026-01-13 17:23:39'),(156,2,'logout','User logged out','::1','2026-01-13 17:24:14'),(157,2,'login','User logged in successfully','::1','2026-01-13 17:24:45'),(158,2,'login','User logged in successfully','::1','2026-01-14 10:48:29'),(159,2,'logout','User logged out','::1','2026-01-14 11:16:23'),(160,2,'login','User logged in successfully','::1','2026-01-14 11:16:25'),(161,2,'login','User logged in successfully','::1','2026-01-15 13:20:35'),(162,2,'login','User logged in successfully','::1','2026-01-15 15:22:20'),(163,2,'login','User logged in successfully','::1','2026-01-15 17:34:24'),(164,2,'login','User logged in successfully','::1','2026-01-16 12:06:51'),(165,2,'login','User logged in successfully','::1','2026-01-16 14:24:46'),(166,2,'login','User logged in successfully','::1','2026-01-16 16:36:52'),(167,2,'login','User logged in successfully','::1','2026-01-17 02:12:06'),(168,2,'login','User logged in successfully','::1','2026-01-17 03:06:54'),(169,2,'login','User logged in successfully','::1','2026-01-17 03:12:10');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_settings`
--

DROP TABLE IF EXISTS `approval_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) DEFAULT '0' COMMENT '0=Disabled, 1=Enabled',
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `approval_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_settings`
--

LOCK TABLES `approval_settings` WRITE;
/*!40000 ALTER TABLE `approval_settings` DISABLE KEYS */;
INSERT INTO `approval_settings` VALUES (1,'budget_request_approval',0,NULL,'2026-01-15 21:54:45');
/*!40000 ALTER TABLE `approval_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approvers`
--

DROP TABLE IF EXISTS `approvers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approvers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `org_id` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_approver` (`user_id`,`org_id`),
  KEY `org_id` (`org_id`),
  CONSTRAINT `approvers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approvers_ibfk_2` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approvers`
--

LOCK TABLES `approvers` WRITE;
/*!40000 ALTER TABLE `approvers` DISABLE KEYS */;
/*!40000 ALTER TABLE `approvers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_allocations`
--

DROP TABLE IF EXISTS `budget_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_allocations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fiscal_year` int NOT NULL COMMENT 'ปีงบประมาณ',
  `plan_id` int NOT NULL COMMENT 'FK: budget_plans',
  `category_id` int DEFAULT NULL COMMENT 'FK: budget_categories (Optional, can derive from item)',
  `item_id` int DEFAULT NULL COMMENT 'FK: budget_category_items',
  `activity_id` int DEFAULT NULL,
  `organization_id` int DEFAULT NULL,
  `allocated_pba` decimal(15,2) DEFAULT '0.00' COMMENT 'งบ พรบ.',
  `allocated_received` decimal(15,2) DEFAULT '0.00' COMMENT 'งบจัดสรร (ได้รับจริง)',
  `transfer_in` decimal(15,2) DEFAULT '0.00' COMMENT 'โอนเข้า',
  `transfer_out` decimal(15,2) DEFAULT '0.00' COMMENT 'โอนออก',
  `net_budget` decimal(15,2) DEFAULT '0.00' COMMENT 'งบสุทธิ (จัดสรร + โอนเข้า - โอนออก)',
  `disbursed` decimal(15,2) DEFAULT '0.00' COMMENT 'เบิกจ่ายจริง',
  `po_commitment` decimal(15,2) DEFAULT '0.00' COMMENT 'ใบสั่งซื้อ/สัญญา (PO)',
  `pending_approval` decimal(15,2) DEFAULT '0.00' COMMENT 'ขออนุมัติหลักการ (จองงบ)',
  `remaining` decimal(15,2) DEFAULT '0.00' COMMENT 'คงเหลือ (Net - Disbursed - PO - Pending)',
  `status` enum('active','closed','frozen') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_item_id` (`item_id`),
  KEY `fk_budget_allocations_category_id` (`category_id`),
  KEY `fk_budget_allocations_activity_id` (`activity_id`),
  KEY `fk_budget_allocations_organization_id` (`organization_id`),
  CONSTRAINT `fk_budget_allocations_activity_id` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_allocations_category_id` FOREIGN KEY (`category_id`) REFERENCES `budget_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_allocations_item_id` FOREIGN KEY (`item_id`) REFERENCES `budget_category_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_allocations_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_allocations_plan_id` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_allocations`
--

LOCK TABLES `budget_allocations` WRITE;
/*!40000 ALTER TABLE `budget_allocations` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_categories`
--

DROP TABLE IF EXISTS `budget_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` int DEFAULT NULL,
  `level` int NOT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_plan` tinyint(1) DEFAULT '0',
  `plan_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_level` (`level`),
  KEY `idx_budget_categories_code_path` (`code`,`parent_id`,`level`),
  CONSTRAINT `budget_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `budget_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_categories`
--

LOCK TABLES `budget_categories` WRITE;
/*!40000 ALTER TABLE `budget_categories` DISABLE KEYS */;
INSERT INTO `budget_categories` VALUES (1,'1','งบบุคลากร','Personnel Budget','ค่าใช้จ่ายเกี่ยวกับบุคลากร เงินเดือน และค่าจ้าง',21,1,1,1,'2025-12-12 14:52:00','2025-12-15 17:11:49',0,NULL),(2,'1.1','เงินเดือน','Salaries','เงินเดือนและค่าตอบแทนประจำ',1,2,1,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(3,'1.2','ค่าจ้างประจำ','Regular Wages','ค่าจ้างประจำและค่าตอบแทนอื่นๆ',1,2,2,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(4,'1.1.1','เงินหรือที่เรียกเป็นอย่างอื่น','Salary Components','อัตราเงินเดือนและค่าจ้าง',2,3,1,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(5,'1.1.2','เงินอื่นที่จ่ายควบกับเงินเดือน','Other Salary Components','เงินเพิ่ม ค่าตอบแทนพิเศษอื่นๆ',2,3,2,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(6,'1.1.1.1','อัตราเดิม','Original Rates','อัตราเงินเดือนเดิม',3,4,1,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(7,'1.1.1.2','อัตราใหม่','New Rates','อัตราเงินเดือนใหม่',3,4,2,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(8,'1.1.2.1','เงินประจำตำแหน่ง รวม','Position Allowances - Total','เงินประจำตำแหน่งทุกประเภท',4,4,1,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(9,'1.1.2.2','ค่าตอบแทนเท่ากับเงินประจำตำแหน่ง','Position Compensation','ค่าตอบแทนตำแหน่งต่างๆ',4,4,2,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(10,'1.1.2.3','เงินช่วยเหลือการครองชีพข้าราชการระดับต้น','Cost of Living Allowance','เงินช่วยเหลือการครองชีพ',4,4,3,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(11,'1.1.2.4','เงิน พ.ต.ก.','Legal Position Allowance','เงินเพิ่มตำแหน่งที่มีเหตุพิเศษของขรก.พลเรือน (ผู้ปฏิบัติงานด้านนิติกร)',4,4,4,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(12,'1.1.2.5','เงิน พ.พ.ด.','Procurement Allowance','เงินเพิ่มพิเศษสำหรับผู้ปฏิบัติงานด้านพัสดุ',4,4,5,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(13,'1.1.2.6','เงิน พ.ส.ร.','Combat Allowance','เงินเพิ่มพิเศษสำหรับการสู้รบ',4,4,6,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(14,'1.1.2.7','เงิน สปพ.','Welfare Allowance','เงินสวัสดิการสำหรับการปฏิบัติงานประจำสำนักงานในพื้นที่พิเศษ',4,4,7,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(15,'2.1.1.2','ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น','Special Full Salary Compensation','ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น',1,3,10,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(16,'2.1.1.3','ค่าตอบแทนพิเศษรายเดือนให้แก่เจ้าหน้าที่ผู้ปฏิบัติงานในพื้นที่จังหวัดชายแดนภาคใต้','Southern Border Province Compensation','ค่าตอบแทนพิเศษสำหรับจังหวัดชายแดนภาคใต้',1,3,11,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(17,'2.1.2','ค่าใช้จ่าย','Expenses','ค่าใช้จ่ายต่างๆ',1,2,20,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(18,'2.1.2.1','เงินสมทบกองทุนประกันสังคม','Social Security Fund Contributions','เงินสมทบกองทุนประกันสังคม',15,3,1,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(19,'2.1.2.2','เงินสมทบกองทุนเงินทดแทน','Compensation Fund Contributions','เงินสมทบกองทุนเงินทดแทน',15,3,2,1,'2025-12-12 14:52:00','2025-12-12 14:52:00',0,NULL),(20,'OPERATIONS','งบดำเนินงาน',NULL,'ค่าตอบแทน ค่าใช้สอย และวัสดุอุปกรณ์',21,1,2,1,'2025-12-14 16:56:41','2025-12-15 17:11:49',0,NULL),(21,'GOVT_PERSONNEL_EXP','รายการค่าใช้จ่ายบุคลากรภาครัฐ','Government Personnel Expenditure','หมวดหมู่หลักสำหรับค่าใช้จ่ายบุคลากรภาครัฐทั้งหมด',NULL,0,0,1,'2025-12-15 17:11:49','2025-12-15 17:11:49',0,NULL);
/*!40000 ALTER TABLE `budget_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_category_items`
--

DROP TABLE IF EXISTS `budget_category_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_category_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(500) DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `level` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'เธงเธฑเธเนเธงเธฅเธฒเธเธตเนเธชเธฃเนเธฒเธ',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'เธงเธฑเธเนเธงเธฅเธฒเธเธตเนเนเธเนเนเธเธฅเนเธฒเธชเธธเธ',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT 'เธฅเธณเธเธฑเธเธเธฒเธฃเนเธชเธเธเธเธฅ',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'เธชเธเธฒเธเธฐเธเธฒเธฃเนเธเนเธเธฒเธ (1=เนเธเนเธเธฒเธ, 0=เธเธดเธ)',
  `description` text COMMENT 'เธเธณเธญเธเธดเธเธฒเธขเนเธเธดเนเธกเนเธเธดเธก',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเนเธงเธฅเธฒเธเธตเนเธฅเธ (soft delete)',
  `created_by` int DEFAULT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธ (FK to users)',
  `updated_by` int DEFAULT NULL COMMENT 'เธเธนเนเนเธเนเนเธเธฅเนเธฒเธชเธธเธ (FK to users)',
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_level` (`level`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_updated_by` (`updated_by`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_budget_category_items_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `budget_category_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_category_items`
--

LOCK TABLES `budget_category_items` WRITE;
/*!40000 ALTER TABLE `budget_category_items` DISABLE KEYS */;
INSERT INTO `budget_category_items` VALUES (1,21,'เงินเดือนและค่าจ้างประจำ','0_เงินเดือนและค่าจ้างประจำ',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(2,21,'เงินเดือน','.1_เงินเดือน',1,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(3,21,'อัตราเดิม','.2_อัตราเดิม',2,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(4,21,'อัตราใหม่','.2_อัตราใหม่',2,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(5,21,'เงินอื่นที่จ่ายควบกับเงินเดือน','.2_เงินอื่นที่จ่ายควบกับเงินเดือน',2,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(6,21,'เงินประจำตำแหน่ง','.3_เงินประจำตำแหน่ง',5,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(7,21,'เงินประจำตำแหน่ง (บริหารและอำนวยการ)','.4_เงินประจำตำแหน่ง_(บริหารและอำนวยการ)',6,4,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(8,21,'เงินประจำตำแหน่ง (วิชาการ)','.4_เงินประจำตำแหน่ง_(วิชาการ)',6,4,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(9,21,'เงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)','.4_เงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ_(วช)_/เชี่ยวชาญเฉพาะ_(ชช.)',6,4,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(10,21,'เงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์','.5_เงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ_ตำแหน่งนักวิชาการคอมพิวเตอร์',9,5,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(11,21,'เงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก','.5_เงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ_ตำแหน่งวิศวกร/สถาปนิก',9,5,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(12,21,'ค่าตอบแทนรายเดือนสำหรับข้าราชการ','.3_ค่าตอบแทนรายเดือนสำหรับข้าราชการ',5,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(13,21,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง','.4_ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง',12,4,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(14,21,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง (บริหารและอำนวยการ)','.5_ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง_(บริหารและอำนวยการ)',13,5,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(15,21,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง (วิชาการ)','.5_ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง_(วิชาการ)',13,5,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(16,21,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)','.5_ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ_(วช)_/เชี่ยวชาญเฉพาะ_(ชช.)',13,5,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(17,21,'เงินค่าตอบแทนรายเดือนสำหรับข้าราชการระดับ 8 และ 8ว','.4_เงินค่าตอบแทนรายเดือนสำหรับข้าราชการระดับ_8_และ_8ว',12,4,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(18,21,'เงินช่วยเหลือการครองชีพข้าราชการระดับต้น','.3_เงินช่วยเหลือการครองชีพข้าราชการระดับต้น',5,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(19,21,'เงิน พ.ต.ก.  (เงินเพิ่มตำแหน่งที่มีเหตุพิเศษของข้าราชการพลเรือนสำหรับผู้ปฏิบัติงานด้านนิติกร)','.3_เงิน_พ.ต.ก._(เงินเพิ่มตำแหน่งที่มีเหตุพิเศษของข้าราชการพลเรือนสำหรับผู้ปฏิบัติงานด้านนิติกร)',5,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(20,21,'เงิน พ.พ.ด. (เงินเพิ่มพิเศษสำหรับผู้ปฏิบัติงานด้านพัสดุ)','.3_เงิน_พ.พ.ด._(เงินเพิ่มพิเศษสำหรับผู้ปฏิบัติงานด้านพัสดุ)',5,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(21,21,'เงิน พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)','.3_เงิน_พ.ส.ร._(เงินเพิ่มพิเศษสำหรับการสู้รบ)',5,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(22,21,'เงิน สปพ. (เงินสวัสดิการสำหรับการปฏิบัติงานประจำสำนักงานในพื้นที่พิเศษ)','.3_เงิน_สปพ._(เงินสวัสดิการสำหรับการปฏิบัติงานประจำสำนักงานในพื้นที่พิเศษ)',5,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(23,21,'ค่าจ้างประจำ','.1_ค่าจ้างประจำ',1,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(24,21,'อัตราเดิม','.2_อัตราเดิม',23,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(25,21,'อัตราใหม่','.2_อัตราใหม่',23,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(26,21,'เงินอื่นที่จ่ายควบกับค่าจ้างประจำ','.2_เงินอื่นที่จ่ายควบกับค่าจ้างประจำ',23,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(27,21,'ค่าตอบแทนรายเดือนลูกจ้างประจำ','.3_ค่าตอบแทนรายเดือนลูกจ้างประจำ',26,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(28,21,'เงินช่วยเหลือค่าครองชีพ','.3_เงินช่วยเหลือค่าครองชีพ',26,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(29,21,'เงิน พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)','.3_เงิน_พ.ส.ร._(เงินเพิ่มพิเศษสำหรับการสู้รบ)',26,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(30,21,'ค่าตอบแทนพนักงานราชการ','0_ค่าตอบแทนพนักงานราชการ',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(31,21,'ค่าตอบแทนพนักงานราชการ','.1_ค่าตอบแทนพนักงานราชการ',30,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(32,21,'อัตราเดิม','.2_อัตราเดิม',31,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(33,21,'อัตราใหม่','.2_อัตราใหม่',31,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(34,21,'เงินอื่นที่จ่ายควบกับค่าตอบแทนพนักงานราชการ','.2_เงินอื่นที่จ่ายควบกับค่าตอบแทนพนักงานราชการ',31,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(35,21,'เงินช่วยเหลือการครองชีพชั่วคราวพนักงานราชการ','.3_เงินช่วยเหลือการครองชีพชั่วคราวพนักงานราชการ',34,3,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(36,21,'ค่าตอบแทนใช้สอยและวัสดุ','0_ค่าตอบแทนใช้สอยและวัสดุ',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(37,21,'ค่าตอบแทน','.1_ค่าตอบแทน',36,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(38,21,'ค่าเช่าบ้าน','.2_ค่าเช่าบ้าน',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(39,21,'ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น','.2_ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(40,21,'ค่าตอบแทนพิเศษค่าจ้างเต็มขั้น','.2_ค่าตอบแทนพิเศษค่าจ้างเต็มขั้น',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(41,21,'ค่าตอบแทนพิเศษรายเดือนให้แก่เจ้าหน้าที่ผู้ปฎิบัติงานในพื้นที่จังหวัดชายแดนภาคใต้','.2_ค่าตอบแทนพิเศษรายเดือนให้แก่เจ้าหน้าที่ผู้ปฎิบัติงานในพื้นที่จังหวัดชายแดนภาคใต้',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(42,21,'ค่าใช้สอย','.1_ค่าใช้สอย',36,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(43,21,'เงินสมทบกองทุนประกันสังคม','.2_เงินสมทบกองทุนประกันสังคม',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(44,21,'ค่าตอบแทนผู้ปฏิบัติงานให้ทางราชการ','.2_ค่าตอบแทนผู้ปฏิบัติงานให้ทางราชการ',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(45,21,'ค่าตอบแทนการปฏิบัติงานนอกเวลาราชการ','.2_ค่าตอบแทนการปฏิบัติงานนอกเวลาราชการ',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(46,21,'ค่าเบี้ยประชุมกรรมการ','.2_ค่าเบี้ยประชุมกรรมการ',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(47,21,'ค่าตอบแทนเหมาจ่ายแทนการจัดหารถประจำตำแหน่ง','.2_ค่าตอบแทนเหมาจ่ายแทนการจัดหารถประจำตำแหน่ง',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(48,21,'ค่าตอบแทนการปฏิบัติงานของคณะกรรมการตรวจสอบและประเมินผลประจำกระทรวงยุติธรรม','.2_ค่าตอบแทนการปฏิบัติงานของคณะกรรมการตรวจสอบและประเมินผลประจำกระทรวงยุติธรรม',37,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(49,21,'ค่าเบี้ยเลี้ยง ค่าเช่าที่พักและค่าพาหนะ','.2_ค่าเบี้ยเลี้ยง_ค่าเช่าที่พักและค่าพาหนะ',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(50,21,'ค่าซ่อมแซมยานพาหนะและขนส่ง','.2_ค่าซ่อมแซมยานพาหนะและขนส่ง',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(51,21,'ค่าซ่อมแซมครุภัณฑ์','.2_ค่าซ่อมแซมครุภัณฑ์',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(52,21,'ค่าเช่าเครื่องถ่ายเอกสารระบบดิจิทัล','.2_ค่าเช่าเครื่องถ่ายเอกสารระบบดิจิทัล',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(53,21,'ค่าเช่ารถยนต์ประจำตำแหน่งปลัดกระทรวงยุติธรรม พร้อมพนักงานขับรถยนต์','.2_ค่าเช่ารถยนต์ประจำตำแหน่งปลัดกระทรวงยุติธรรม_พร้อมพนักงานขับรถยนต์',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(54,21,'ค่าเช่ารถยนต์ประจำตำแหน่งรัฐมนตรีว่าการกระทรวงยุติธรรม พร้อมพนักงานขับรถยนต์','.2_ค่าเช่ารถยนต์ประจำตำแหน่งรัฐมนตรีว่าการกระทรวงยุติธรรม_พร้อมพนักงานขับรถยนต์',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(55,21,'ค่าจ้างเหมาบุคลากรช่วยปฏิบัติงาน','.2_ค่าจ้างเหมาบุคลากรช่วยปฏิบัติงาน',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(56,21,'ค่ารับรองและพิธีการ','.2_ค่ารับรองและพิธีการ',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(57,21,'ค่าธรรมเนียม','.2_ค่าธรรมเนียม',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(58,21,'ค่าใช้จ่ายเพื่อการขับเคลื่อนนโยบายกระทรวงยุติธรรม','.2_ค่าใช้จ่ายเพื่อการขับเคลื่อนนโยบายกระทรวงยุติธรรม',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(59,21,'ค่าธรรมเนียมเก็บขนขยะมูลฝอย','.2_ค่าธรรมเนียมเก็บขนขยะมูลฝอย',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(60,21,'โครงการติดตามนโยบายและตรวจราชการหน่วยงานในสังกัดกระทรวงยุติธรรม ของผู้บริหารกระทรวงยุติธรรม','.2_โครงการติดตามนโยบายและตรวจราชการหน่วยงานในสังกัดกระทรวงยุติธรรม_ของผู้บริหารกระทรวงยุติธรรม',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(61,21,'ค่าใช้จ่ายในการพิธีรับพระราชทานเครื่องราชอิสริยากรณ์ ชั้นสายสะพาย เบื้องหน้าพระบรมฉายาลักษณ์พระบาทสมเด็จพระเจ้าอยู่หัว','.2_ค่าใช้จ่ายในการพิธีรับพระราชทานเครื่องราชอิสริยากรณ์_ชั้นสายสะพาย_เบื้องหน้าพระบรมฉายาลักษณ์พระบาทสมเด็จพระเจ้าอยู่หัว',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(62,21,'โครงการจัดงานวันสถาปนากระทรวงยุติธรรม ครบรอบ 135 ปี','.2_โครงการจัดงานวันสถาปนากระทรวงยุติธรรม_ครบรอบ_135_ปี',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(63,21,'ค่าจ้างเหมาพนักงานขับรถยนต์ (เพิ่มเติม)','.2_ค่าจ้างเหมาพนักงานขับรถยนต์_(เพิ่มเติม)',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(64,21,'โครงการบริหารงานการรักษาความปลอดภัยในอาคารและพื้นที่ี่กระทรวงยุติธรรม','.2_โครงการบริหารงานการรักษาความปลอดภัยในอาคารและพื้นที่ี่กระทรวงยุติธรรม',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(65,21,'การจ้างเหมาบริการเพื่อจัดทำข้อมูลสนับสนุนเพื่อประกอบการกำหนดนโยบายของผู้บริหาร','.2_การจ้างเหมาบริการเพื่อจัดทำข้อมูลสนับสนุนเพื่อประกอบการกำหนดนโยบายของผู้บริหาร',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(66,21,'ค่าธรรมเนียมฝากมาตรวัดน้ำ','.2_ค่าธรรมเนียมฝากมาตรวัดน้ำ',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(67,21,'ค่าจ้างเหมาบริการ','.2_ค่าจ้างเหมาบริการ',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(68,21,'ค่าบำรุงรักษาระบบเทคโนโลยีสารสนเทศ','.2_ค่าบำรุงรักษาระบบเทคโนโลยีสารสนเทศ',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(69,21,'ค่าใช้จ่ายในการจัดหาหรือการต่อลิขสิทธิ์','.2_ค่าใช้จ่ายในการจัดหาหรือการต่อลิขสิทธิ์',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(70,21,'ค่าใช้จ่ายในการบริหารจัดการเชิงกลยุทธ์','.2_ค่าใช้จ่ายในการบริหารจัดการเชิงกลยุทธ์',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(71,21,'ค่าใช้จ่ายในการพัฒนาระบบบริหาร','.2_ค่าใช้จ่ายในการพัฒนาระบบบริหาร',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(72,21,'ค่าใช้จ่ายในการสัมมนาและฝึกอบรม','.2_ค่าใช้จ่ายในการสัมมนาและฝึกอบรม',42,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(73,21,'วัสดุ','.1_วัสดุ',36,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(74,21,'ค่าสาธารณูปโภค','0_ค่าสาธารณูปโภค',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(75,21,'ค่าครุภัณฑ์ ที่ดินและสิ่งก่อสร้าง','0_ค่าครุภัณฑ์_ที่ดินและสิ่งก่อสร้าง',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(76,21,'ค่าครุภัณฑ์','.1_ค่าครุภัณฑ์',75,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(77,21,'ครุภัณฑ์คอมพิวเตอร์','.2_ครุภัณฑ์คอมพิวเตอร์',76,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(78,21,'ที่ดินและสิ่งก่อสร้าง','.1_ที่ดินและสิ่งก่อสร้าง',75,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(79,21,'ครุภัณฑ์สำนักงาน','.2_ครุภัณฑ์สำนักงาน',76,2,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(80,21,'ค่าที่ดินและสิ่งก่อสร้าง','.1_ค่าที่ดินและสิ่งก่อสร้าง',75,1,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(81,21,'ค่าใช้จ่ายในการพัฒนากฎหมาย','0_ค่าใช้จ่ายในการพัฒนากฎหมาย',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(82,21,'ค่าใช้จ่ายในการขับเคลื่อนงานยุติธรรมชุมชน','0_ค่าใช้จ่ายในการขับเคลื่อนงานยุติธรรมชุมชน',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(83,21,'ค่าใช้จ่ายสำหรับโครงการกำลังงใจ','0_ค่าใช้จ่ายสำหรับโครงการกำลังงใจ',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(84,21,'ค่าใช้จ่ายโครงการส่งเสริมความปลอดภัยด้านการท่องเที่ยว','0_ค่าใช้จ่ายโครงการส่งเสริมความปลอดภัยด้านการท่องเที่ยว',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(85,21,'ค่าใช้จ่ายโครงการพัฒนาทักษะดิจิทัลสำหรับบุคลากรภาครัฐเพื่อการขับเคลื่อนรัฐบาลดิจิทัล','0_ค่าใช้จ่ายโครงการพัฒนาทักษะดิจิทัลสำหรับบุคลากรภาครัฐเพื่อการขับเคลื่อนรัฐบาลดิจิทัล',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(86,21,'ค่าใช้จ่ายในการพัฒนาระบบบริหารเพื่อต่อต้านการทุจริตและส่งเสริมคุ้มครองจริยธรรม','0_ค่าใช้จ่ายในการพัฒนาระบบบริหารเพื่อต่อต้านการทุจริตและส่งเสริมคุ้มครองจริยธรรม',NULL,0,'2025-12-29 11:53:46','2026-01-10 04:59:34',0,1,NULL,NULL,NULL,NULL),(87,21,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์',NULL,16,6,'2026-01-05 12:45:08','2026-01-10 04:59:34',1,1,NULL,NULL,NULL,NULL),(88,21,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก',NULL,16,6,'2026-01-05 12:45:09','2026-01-10 04:59:34',2,1,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `budget_category_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_line_items`
--

DROP TABLE IF EXISTS `budget_line_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_line_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fiscal_year` int NOT NULL DEFAULT '2569',
  `budget_type_id` int DEFAULT NULL COMMENT 'FK: budget_types',
  `plan_id` int DEFAULT NULL COMMENT 'FK: plans',
  `project_id` int DEFAULT NULL COMMENT 'FK: projects',
  `activity_id` int DEFAULT NULL COMMENT 'FK: activities',
  `expense_type_id` int DEFAULT NULL COMMENT 'FK: expense_types',
  `expense_group_id` int DEFAULT NULL COMMENT 'FK: expense_groups',
  `expense_item_id` int DEFAULT NULL COMMENT 'FK: expense_items (lowest level)',
  `ministry_id` int DEFAULT NULL COMMENT 'กระทรวง: organizations.id',
  `department_id` int DEFAULT NULL COMMENT 'กรม: organizations.id',
  `division_id` int DEFAULT NULL COMMENT 'กอง: organizations.id',
  `section_id` int DEFAULT NULL COMMENT 'กลุ่มงาน: organizations.id',
  `province_id` int DEFAULT NULL COMMENT 'FK: provinces',
  `province_group_id` int DEFAULT NULL COMMENT 'FK: province_groups',
  `province_zone_id` int DEFAULT NULL COMMENT 'FK: province_zones',
  `inspection_zone_id` int DEFAULT NULL COMMENT 'FK: inspection_zones',
  `allocated_pba` decimal(15,2) DEFAULT '0.00' COMMENT 'งบ พรบ.',
  `allocated_received` decimal(15,2) DEFAULT '0.00' COMMENT 'งบจัดสรร',
  `transfer_in` decimal(15,2) DEFAULT '0.00' COMMENT 'โอนเข้า',
  `transfer_out` decimal(15,2) DEFAULT '0.00' COMMENT 'โอนออก',
  `disbursed` decimal(15,2) DEFAULT '0.00' COMMENT 'เบิกจ่าย',
  `po_commitment` decimal(15,2) DEFAULT '0.00' COMMENT 'PO',
  `remaining` decimal(15,2) DEFAULT '0.00' COMMENT 'คงเหลือ',
  `region_type` enum('central','regional') COLLATE utf8mb4_unicode_ci DEFAULT 'central' COMMENT 'ส่วนกลาง/ภูมิภาค',
  `remarks` text COLLATE utf8mb4_unicode_ci COMMENT 'หมายเหตุ',
  `status` enum('active','closed','frozen') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_plan` (`plan_id`),
  KEY `idx_project` (`project_id`),
  KEY `idx_activity` (`activity_id`),
  KEY `idx_division` (`division_id`),
  KEY `idx_status` (`status`),
  KEY `fk_budget_line_items_budget_type_id` (`budget_type_id`),
  KEY `fk_budget_line_items_expense_type_id` (`expense_type_id`),
  KEY `fk_budget_line_items_expense_group_id` (`expense_group_id`),
  KEY `fk_budget_line_items_expense_item_id` (`expense_item_id`),
  KEY `fk_budget_line_items_province_id` (`province_id`),
  CONSTRAINT `fk_budget_line_items_activity_id` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_line_items_budget_type_id` FOREIGN KEY (`budget_type_id`) REFERENCES `budget_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_line_items_expense_group_id` FOREIGN KEY (`expense_group_id`) REFERENCES `expense_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_line_items_expense_item_id` FOREIGN KEY (`expense_item_id`) REFERENCES `expense_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_line_items_expense_type_id` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_line_items_plan_id` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_line_items_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_line_items_province_id` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=332 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='รายการงบประมาณ (จาก CSV - รวม all dimensions)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_line_items`
--

LOCK TABLES `budget_line_items` WRITE;
/*!40000 ALTER TABLE `budget_line_items` DISABLE KEYS */;
INSERT INTO `budget_line_items` VALUES (219,2569,1,15,21,31,1,1,2,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL,NULL),(220,2569,1,15,21,31,1,1,3,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL,NULL),(221,2569,1,15,21,31,1,1,6,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL,NULL),(222,2569,1,15,21,31,1,1,7,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL,NULL),(225,2569,1,15,21,31,1,1,13,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL,NULL),(226,2569,1,15,21,31,1,1,14,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL,NULL),(229,2569,1,15,21,31,1,1,16,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(230,2569,1,15,21,31,1,1,17,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(231,2569,1,15,21,31,1,1,18,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(232,2569,1,15,21,31,1,1,19,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(233,2569,1,15,21,31,1,1,20,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(234,2569,1,15,21,31,1,1,21,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(235,2569,1,15,21,31,1,1,23,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(236,2569,1,15,21,31,1,1,24,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(237,2569,1,15,21,31,1,1,26,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(238,2569,1,15,21,31,1,1,27,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(239,2569,1,15,21,31,1,1,28,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(240,2569,1,15,21,31,1,2,30,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(241,2569,1,15,21,31,1,2,31,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(242,2569,1,15,21,31,1,2,33,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(243,2569,1,15,21,31,2,3,35,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(244,2569,1,15,21,31,2,3,36,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(245,2569,1,15,21,31,2,3,37,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(246,2569,1,15,21,31,2,3,38,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(247,2569,1,15,21,31,2,3,40,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(248,2569,1,15,21,31,2,3,40,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,NULL),(249,2569,2,16,22,32,2,3,41,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(250,2569,2,16,22,32,2,3,42,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(251,2569,2,16,22,32,2,3,43,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(252,2569,2,16,22,32,2,3,44,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(253,2569,2,16,22,32,2,3,45,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(254,2569,2,16,22,32,2,3,46,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(255,2569,2,16,22,32,2,3,47,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(256,2569,2,16,22,32,2,3,48,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(257,2569,2,16,22,32,2,3,49,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(258,2569,2,16,22,32,2,3,50,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(259,2569,2,16,22,32,2,3,51,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(260,2569,2,16,22,32,2,3,52,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(261,2569,2,16,22,32,2,3,53,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(262,2569,2,16,22,32,2,3,54,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(263,2569,2,16,22,32,2,3,55,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(264,2569,2,16,22,32,2,3,56,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(265,2569,2,16,22,32,2,3,57,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(266,2569,2,16,22,32,2,3,58,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(267,2569,2,16,22,32,2,3,59,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(268,2569,2,16,22,32,2,3,60,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(269,2569,2,16,22,32,2,3,61,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(270,2569,2,16,22,32,2,3,62,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(271,2569,2,16,22,32,2,3,63,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(272,2569,2,16,22,32,2,3,64,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(273,2569,2,16,22,32,2,3,64,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(274,2569,2,16,22,32,2,3,64,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(275,2569,2,16,22,32,2,3,65,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(276,2569,2,16,22,32,2,3,65,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(277,2569,2,16,22,32,2,3,65,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(278,2569,2,16,22,32,2,3,66,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(279,2569,2,16,22,32,2,3,66,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(280,2569,2,16,22,32,2,3,67,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(281,2569,2,16,22,32,2,3,67,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(282,2569,2,16,22,32,2,3,67,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(283,2569,2,16,22,32,2,3,68,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(284,2569,2,16,22,32,2,3,68,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(285,2569,2,16,22,32,2,3,68,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(286,2569,2,16,22,32,2,3,69,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(287,2569,2,16,22,32,2,3,69,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(288,2569,2,16,22,32,2,3,69,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(289,2569,2,16,22,32,2,3,70,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(290,2569,2,16,22,32,2,3,70,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(291,2569,2,16,22,32,2,3,70,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(292,2569,2,16,22,32,2,4,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(293,2569,2,16,22,32,2,4,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(294,2569,2,16,22,32,2,4,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(295,2569,2,16,22,32,3,5,72,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(296,2569,2,16,22,32,3,5,72,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(297,2569,2,16,22,32,3,5,72,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(298,2569,2,16,22,32,3,5,73,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(299,2569,2,16,22,32,3,5,73,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(300,2569,2,16,22,32,3,5,73,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(301,2569,2,16,22,32,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(302,2569,2,16,22,32,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(303,2569,2,16,22,32,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(304,2569,2,16,22,33,5,7,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(305,2569,2,16,22,33,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(306,2569,2,16,22,34,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(307,2569,2,16,22,35,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(308,2569,2,17,23,36,5,7,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(309,2569,2,17,23,36,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(310,2569,2,18,24,37,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(311,2569,2,18,24,38,2,3,34,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(312,2569,2,18,24,38,2,3,39,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(313,2569,2,18,24,38,2,3,70,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(314,2569,2,18,24,38,2,4,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(315,2569,2,18,24,38,3,5,74,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(316,2569,2,18,24,38,3,5,72,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(317,2569,2,18,24,38,3,5,75,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(318,2569,2,18,24,38,3,5,75,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(319,2569,2,18,24,38,5,7,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(320,2569,2,18,24,38,4,6,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(321,2569,2,18,25,39,4,8,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(322,2569,2,18,26,40,4,9,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(323,2569,2,18,26,41,4,10,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(324,2569,2,19,27,42,4,11,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(325,2569,3,20,28,43,4,12,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(326,2569,3,20,29,44,2,3,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(327,2569,3,21,30,45,4,13,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central','','active','2026-01-01 07:47:27','2026-01-03 21:55:48',NULL,NULL,NULL),(328,2569,NULL,15,21,31,2,3,77,NULL,NULL,3,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central',NULL,'active','2026-01-04 14:12:10','2026-01-04 14:12:10',NULL,NULL,NULL),(329,2569,1,15,21,31,1,1,15,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central',NULL,'active','2026-01-05 15:52:29','2026-01-05 15:52:29',NULL,NULL,NULL),(330,2569,1,15,21,31,1,1,9,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central',NULL,'active','2026-01-05 15:52:29','2026-01-05 15:52:29',NULL,NULL,NULL),(331,2569,1,15,21,31,1,1,10,1,2,3,4,1,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'central',NULL,'active','2026-01-05 15:52:29','2026-01-05 15:52:29',NULL,NULL,NULL);
/*!40000 ALTER TABLE `budget_line_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_monthly_snapshots`
--

DROP TABLE IF EXISTS `budget_monthly_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_monthly_snapshots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `allocation_id` int NOT NULL COMMENT 'FK: budget_allocations',
  `fiscal_year` int NOT NULL,
  `snapshot_date` date NOT NULL COMMENT 'เธงเธฑเธเธ?เธตเนเธเธฑเธเธ?เธถเธ (เธชเธดเนเธเน?เธ?เธทเธญเธ)',
  `allocated_received` decimal(15,2) DEFAULT '0.00',
  `disbursed` decimal(15,2) DEFAULT '0.00',
  `po_commitment` decimal(15,2) DEFAULT '0.00',
  `remaining` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_snapshot_date` (`snapshot_date`),
  KEY `idx_allocation_fiscal` (`allocation_id`,`fiscal_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_monthly_snapshots`
--

LOCK TABLES `budget_monthly_snapshots` WRITE;
/*!40000 ALTER TABLE `budget_monthly_snapshots` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_monthly_snapshots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_records`
--

DROP TABLE IF EXISTS `budget_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `budget_id` int NOT NULL,
  `record_date` date NOT NULL COMMENT 'วันที่บันทึก',
  `record_period` enum('beginning','mid','end') COLLATE utf8mb4_unicode_ci DEFAULT 'beginning' COMMENT 'ช่วงเวลา: ต้นเดือน/กลางเดือน/ปลายเดือน',
  `transfer_allocation` decimal(15,2) DEFAULT NULL,
  `spent_amount` decimal(15,2) DEFAULT NULL,
  `request_amount` decimal(15,2) DEFAULT NULL,
  `po_amount` decimal(15,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'หมายเหตุ',
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_budget_date` (`budget_id`,`record_date`),
  KEY `idx_record_date` (`record_date`),
  CONSTRAINT `budget_records_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budget_records_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `budget_records_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='บันทึกข้อมูลงบประมาณรายเดือน (ต้น/กลาง/ปลายเดือน)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_records`
--

LOCK TABLES `budget_records` WRITE;
/*!40000 ALTER TABLE `budget_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_request_approvals`
--

DROP TABLE IF EXISTS `budget_request_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_request_approvals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `budget_request_id` int NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_request_id` (`budget_request_id`),
  CONSTRAINT `budget_request_approvals_ibfk_1` FOREIGN KEY (`budget_request_id`) REFERENCES `budget_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_request_approvals`
--

LOCK TABLES `budget_request_approvals` WRITE;
/*!40000 ALTER TABLE `budget_request_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_request_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_request_items`
--

DROP TABLE IF EXISTS `budget_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_request_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `budget_request_id` int NOT NULL,
  `category_item_id` int DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,2) DEFAULT '0.00',
  `unit_price` decimal(15,2) DEFAULT '0.00',
  `amount` decimal(15,2) DEFAULT '0.00',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_request_id` (`budget_request_id`),
  KEY `idx_category_item` (`category_item_id`),
  CONSTRAINT `budget_request_items_ibfk_1` FOREIGN KEY (`budget_request_id`) REFERENCES `budget_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_request_items`
--

LOCK TABLES `budget_request_items` WRITE;
/*!40000 ALTER TABLE `budget_request_items` DISABLE KEYS */;
INSERT INTO `budget_request_items` VALUES (1,289,9,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(2,289,10,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(3,289,13,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง (บริหารและอำนวยการ)',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(4,289,14,'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง (วิชาการ)',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(5,289,16,'เงินค่าตอบแทนรายเดือนสำหรับข้าราชการระดับ 8 และ 8ว',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(6,289,6,'เงินประจำตำแหน่ง (บริหารและอำนวยการ)',5.00,200.00,1000.00,'','2026-01-11 13:55:02','2026-01-13 12:55:54'),(7,289,7,'เงินประจำตำแหน่ง (วิชาการ)',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(8,289,20,'เงิน พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(9,289,21,'เงิน สปพ. (เงินสวัสดิการสำหรับการปฏิบัติงานประจำสำนักงานในพื้นที่พิเศษ)',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-12 13:43:50'),(10,289,17,'เงินช่วยเหลือการครองชีพข้าราชการระดับต้น',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(11,289,18,'เงิน พ.ต.ก.  (เงินเพิ่มตำแหน่งที่มีเหตุพิเศษของข้าราชการพลเรือนสำหรับผู้ปฏิบัติงานด้านนิติกร)',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(12,289,19,'เงิน พ.พ.ด. (เงินเพิ่มพิเศษสำหรับผู้ปฏิบัติงานด้านพัสดุ)',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(13,289,2,'อัตราเดิม',10.00,0.00,1000000.00,'','2026-01-11 13:55:02','2026-01-12 13:31:12'),(14,289,3,'อัตราใหม่',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(15,289,23,'อัตราเดิม',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(16,289,26,'ค่าตอบแทนรายเดือนลูกจ้างประจำ',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(17,289,27,'เงินช่วยเหลือค่าครองชีพ',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(18,289,28,'เงิน พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(19,289,24,'อัตราใหม่',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(20,289,30,'อัตราเดิม',213.00,0.00,5000000.00,'','2026-01-11 13:55:02','2026-01-12 18:25:04'),(21,289,31,'อัตราใหม่',0.00,0.00,0.00,'','2026-01-11 13:55:02','2026-01-11 13:55:02'),(22,289,33,'เงินช่วยเหลือการครองชีพชั่วคราวพนักงานราชการ',0.00,0.00,0.00,'','2026-01-11 13:55:03','2026-01-11 13:55:03'),(23,289,36,'ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น',2.00,0.00,20.00,'','2026-01-11 13:55:03','2026-01-12 13:35:50'),(24,289,37,'ค่าตอบแทนพิเศษค่าจ้างเต็มขั้น',0.00,0.00,0.00,'','2026-01-11 13:55:03','2026-01-11 13:55:03'),(25,289,38,'ค่าตอบแทนพิเศษรายเดือนให้แก่เจ้าหน้าที่ผู้ปฎิบัติงานในพื้นที่จังหวัดชายแดนภาคใต้',0.00,0.00,0.00,'','2026-01-11 13:55:03','2026-01-11 13:55:03'),(26,289,35,'ค่าเช่าบ้าน',10.00,0.00,5000.00,'','2026-01-11 13:55:03','2026-01-12 15:32:21'),(27,289,40,'เงินสมทบกองทุนประกันสังคม',0.00,0.00,0.00,'','2026-01-11 13:55:03','2026-01-11 13:55:03'),(28,289,77,'เงินสมทบกองทุนเงินทดแทน',0.00,0.00,0.00,'','2026-01-11 13:55:03','2026-01-11 13:55:03');
/*!40000 ALTER TABLE `budget_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_requests`
--

DROP TABLE IF EXISTS `budget_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fiscal_year` int NOT NULL COMMENT 'เธเธตเธเธเธเธฃเธฐเธกเธฒเธ เธ.เธจ.',
  `request_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธเธทเนเธญเธเธณเธเธญเธเธเธเธฃเธฐเธกเธฒเธ',
  `request_status` enum('draft','saved','confirmed','pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `total_amount` decimal(15,2) DEFAULT NULL,
  `created_by` int NOT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธเธเธณเธเธญ',
  `org_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'เธงเธฑเธเธเธตเนเธชเธฃเนเธฒเธ',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'เธงเธฑเธเธเธตเนเธญเธฑเธเนเธเธ',
  `submitted_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเธเธตเนเธชเนเธเธญเธเธธเธกเธฑเธเธด',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเธเธตเนเธญเธเธธเธกเธฑเธเธด',
  `rejected_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเธเธตเนเนเธกเนเธญเธเธธเธกเธฑเธเธด',
  `rejected_reason` text COLLATE utf8mb4_unicode_ci COMMENT 'เนเธซเธเธธเธเธฅเธเธตเนเนเธกเนเธญเธเธธเธกเธฑเธเธด',
  PRIMARY KEY (`id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_request_status` (`request_status`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_dates` (`created_at`,`submitted_at`,`approved_at`),
  KEY `fk_budget_requests_org_id` (`org_id`),
  CONSTRAINT `budget_requests_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_budget_requests_org_id` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=292 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธฒเธฃเธฒเธเธเธณเธเธญเธเธเธเธฃเธฐเธกเธฒเธเธฃเธฒเธขเธเธต';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_requests`
--

LOCK TABLES `budget_requests` WRITE;
/*!40000 ALTER TABLE `budget_requests` DISABLE KEYS */;
INSERT INTO `budget_requests` VALUES (5,2568,'Draft Request - Office Supplies','saved',50000.00,5,NULL,'2025-12-14 10:22:18','2026-01-11 14:30:38',NULL,NULL,NULL,NULL),(289,2569,'คำของบบุคลากร-2569','saved',6006020.00,2,3,'2026-01-10 09:13:36','2026-01-13 12:55:54',NULL,NULL,NULL,NULL),(290,2570,'คำของบบุคลากร-2570','draft',0.00,2,3,'2026-01-11 15:07:08','2026-01-11 15:07:08',NULL,NULL,NULL,NULL),(291,2570,'คำของบบุคลากร-2570','draft',0.00,2,3,'2026-01-11 15:38:15','2026-01-11 15:38:15',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `budget_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_trackings`
--

DROP TABLE IF EXISTS `budget_trackings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_trackings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `disbursement_record_id` int DEFAULT NULL,
  `budget_type_id` int DEFAULT NULL,
  `plan_id` int DEFAULT NULL,
  `project_id` int DEFAULT NULL,
  `activity_id` int DEFAULT NULL,
  `expense_type_id` int DEFAULT NULL,
  `expense_group_id` int DEFAULT NULL,
  `expense_item_id` int DEFAULT NULL,
  `fiscal_year` int NOT NULL,
  `record_month` int DEFAULT NULL,
  `organization_id` int DEFAULT NULL,
  `budget_category_item_id` int DEFAULT NULL,
  `allocated` decimal(15,2) DEFAULT NULL,
  `transfer` decimal(15,2) DEFAULT NULL,
  `disbursed` decimal(15,2) DEFAULT NULL,
  `pending` decimal(15,2) DEFAULT NULL,
  `po` decimal(15,2) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tracking` (`fiscal_year`,`budget_category_item_id`),
  UNIQUE KEY `uidx_record_item` (`disbursement_record_id`,`expense_item_id`),
  KEY `idx_trackings_budget_type` (`budget_type_id`),
  KEY `idx_trackings_plan` (`plan_id`),
  KEY `idx_trackings_project` (`project_id`),
  KEY `idx_trackings_activity` (`activity_id`),
  KEY `idx_trackings_expense_type` (`expense_type_id`),
  KEY `idx_trackings_expense_group` (`expense_group_id`),
  KEY `idx_trackings_expense_item` (`expense_item_id`),
  KEY `idx_disbursement_record` (`disbursement_record_id`),
  KEY `fk_budget_trackings_organization_id` (`organization_id`),
  KEY `fk_budget_trackings_budget_category_item_id` (`budget_category_item_id`),
  KEY `idx_trackings_month` (`record_month`),
  CONSTRAINT `fk_budget_trackings_budget_category_item_id` FOREIGN KEY (`budget_category_item_id`) REFERENCES `budget_category_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_budget_trackings_disbursement_record` FOREIGN KEY (`disbursement_record_id`) REFERENCES `disbursement_records` (`id`),
  CONSTRAINT `fk_budget_trackings_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_trackings_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trackings_budget_type` FOREIGN KEY (`budget_type_id`) REFERENCES `budget_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trackings_expense_group` FOREIGN KEY (`expense_group_id`) REFERENCES `expense_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trackings_expense_item` FOREIGN KEY (`expense_item_id`) REFERENCES `expense_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trackings_expense_type` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trackings_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trackings_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_trackings`
--

LOCK TABLES `budget_trackings` WRITE;
/*!40000 ALTER TABLE `budget_trackings` DISABLE KEYS */;
INSERT INTO `budget_trackings` VALUES (57,1,NULL,NULL,NULL,31,1,1,15,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-04 18:35:42'),(93,1,NULL,NULL,NULL,31,1,1,9,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(94,1,NULL,NULL,NULL,31,1,1,10,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(95,1,NULL,NULL,NULL,31,1,1,13,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(96,1,NULL,NULL,NULL,31,1,1,14,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(97,1,NULL,NULL,NULL,31,1,1,16,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(98,1,NULL,NULL,NULL,31,1,1,6,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(99,1,NULL,NULL,NULL,31,1,1,7,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(100,1,NULL,NULL,NULL,31,1,1,20,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(101,1,NULL,NULL,NULL,31,1,1,21,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(102,1,NULL,NULL,NULL,31,1,1,17,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(103,1,NULL,NULL,NULL,31,1,1,18,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(104,1,NULL,NULL,NULL,31,1,1,19,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(105,1,NULL,NULL,NULL,31,1,1,2,2569,10,3,NULL,100.00,0.00,80.00,0.00,0.00,'2026-01-06 11:45:41'),(106,1,NULL,NULL,NULL,31,1,1,3,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(107,1,NULL,NULL,NULL,31,1,1,23,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(108,1,NULL,NULL,NULL,31,1,1,26,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(109,1,NULL,NULL,NULL,31,1,1,27,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(110,1,NULL,NULL,NULL,31,1,1,28,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(111,1,NULL,NULL,NULL,31,1,1,24,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(112,1,NULL,NULL,NULL,31,1,2,30,2569,10,3,NULL,5000000.00,0.00,0.00,0.00,0.00,'2026-01-13 12:12:15'),(113,1,NULL,NULL,NULL,31,1,2,31,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41'),(114,1,NULL,NULL,NULL,31,1,2,33,2569,10,3,NULL,0.00,0.00,0.00,0.00,0.00,'2026-01-06 11:45:41');
/*!40000 ALTER TABLE `budget_trackings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_transactions`
--

DROP TABLE IF EXISTS `budget_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `budget_id` int NOT NULL,
  `transaction_type` enum('allocation','expenditure','transfer_in','transfer_out','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_budget_id` (`budget_id`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_budget_transactions_budget_type` (`budget_id`,`transaction_type`),
  CONSTRAINT `budget_transactions_ibfk_1` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budget_transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_transactions`
--

LOCK TABLES `budget_transactions` WRITE;
/*!40000 ALTER TABLE `budget_transactions` DISABLE KEYS */;
INSERT INTO `budget_transactions` VALUES (1,1,'expenditure',1000.00,'ทดสอบ',NULL,1,'2025-12-13 04:18:05');
/*!40000 ALTER TABLE `budget_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_types`
--

DROP TABLE IF EXISTS `budget_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธ',
  `updated_by` int DEFAULT NULL COMMENT 'เธเธนเนเนเธเนเนเธเธฅเนเธฒเธชเธธเธ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_budget_types_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธฃเธฐเนเธ�เธเธเธเธเธฃเธฐเธกเธฒเธ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_types`
--

LOCK TABLES `budget_types` WRITE;
/*!40000 ALTER TABLE `budget_types` DISABLE KEYS */;
INSERT INTO `budget_types` VALUES (1,'BT-a1e23f','งบประมาณรายจ่ายบุคลากร',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(2,'BT-50999c','งบประมาณรายจ่ายของหน่วยรับงบประมาณ',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(3,'BT-baf098','งบประมาณรายจ่ายบูรณาการ',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL);
/*!40000 ALTER TABLE `budget_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budgets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `budget_type_id` int DEFAULT NULL COMMENT 'เธเธฃเธฐเนเธ�เธเธเธเธเธฃเธฐเธกเธฒเธ',
  `plan_id` int DEFAULT NULL COMMENT 'เนเธเธเธเธฒเธ',
  `project_id` int DEFAULT NULL COMMENT 'เธเธฅเธเธฅเธดเธ/เนเธเธฃเธเธเธฒเธฃ',
  `activity_id` int DEFAULT NULL COMMENT 'เธเธดเธเธเธฃเธฃเธก',
  `expense_type_id` int DEFAULT NULL COMMENT 'เธเธฃเธฐเนเธ�เธเธฃเธฒเธขเธเนเธฒเธข',
  `expense_group_id` int DEFAULT NULL COMMENT 'เธเธฅเธธเนเธกเธฃเธฒเธขเธเนเธฒเธข',
  `expense_item_id` int DEFAULT NULL COMMENT 'เธฃเธฒเธขเธเธฒเธฃเธฃเธฒเธขเธเนเธฒเธข',
  `category_id` int NOT NULL,
  `fiscal_year` int NOT NULL DEFAULT '2568',
  `allocated_amount` decimal(15,2) DEFAULT NULL,
  `spent_amount` decimal(15,2) DEFAULT NULL,
  `target_amount` decimal(15,2) DEFAULT NULL,
  `transfer_in` decimal(15,2) DEFAULT NULL,
  `transfer_out` decimal(15,2) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_fiscal_year` (`category_id`,`fiscal_year`),
  KEY `created_by` (`created_by`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_status` (`status`),
  KEY `idx_budgets_category_year` (`category_id`,`fiscal_year`),
  KEY `idx_budgets_type` (`budget_type_id`),
  KEY `idx_budgets_plan` (`plan_id`),
  KEY `idx_budgets_project` (`project_id`),
  KEY `idx_budgets_activity` (`activity_id`),
  KEY `idx_budgets_expense_type` (`expense_type_id`),
  KEY `idx_budgets_expense_group` (`expense_group_id`),
  KEY `idx_budgets_expense_item` (`expense_item_id`),
  CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `budget_categories` (`id`),
  CONSTRAINT `budgets_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `budgets_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_budgets_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budgets_expense_group` FOREIGN KEY (`expense_group_id`) REFERENCES `expense_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budgets_expense_item` FOREIGN KEY (`expense_item_id`) REFERENCES `expense_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budgets_expense_type` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budgets_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budgets_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budgets_type` FOREIGN KEY (`budget_type_id`) REFERENCES `budget_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budgets`
--

LOCK TABLES `budgets` WRITE;
/*!40000 ALTER TABLE `budgets` DISABLE KEYS */;
INSERT INTO `budgets` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,2568,5000000.00,3201000.00,4800000.00,0.00,0.00,1,NULL,'approved','งบประมาณเงินเดือนคณาละดับที่ 1 และ 2','2025-12-12 16:16:48','2025-12-13 04:18:05',NULL),(2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,2568,2000000.00,1500000.00,1900000.00,0.00,0.00,1,NULL,'approved','ค่าจ้างประจำพนักงานชั่วคราว','2025-12-12 16:16:48','2025-12-12 16:16:48',NULL),(3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,6,2568,3000000.00,2800000.00,2900000.00,0.00,0.00,1,NULL,'approved','อัตราเงินเดือนตามโครงเดิม','2025-12-12 16:16:48','2025-12-12 16:16:48',NULL),(4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,7,2568,3500000.00,1200000.00,3300000.00,0.00,0.00,1,NULL,'approved','อัตราเงินเดือนตามโครงใหม่','2025-12-12 16:16:48','2025-12-12 16:16:48',NULL),(5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,8,2568,1500000.00,800000.00,1400000.00,0.00,0.00,1,NULL,'approved','เงินประจำตำแหน่งรวมทุกระดับ','2025-12-12 16:16:48','2025-12-12 16:16:48',NULL),(6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,2568,500000.00,0.00,0.00,0.00,0.00,1,NULL,'draft',NULL,'2025-12-13 04:18:06','2025-12-13 04:18:06',NULL);
/*!40000 ALTER TABLE `budgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disbursement_records`
--

DROP TABLE IF EXISTS `disbursement_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disbursement_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `activity_id` int NOT NULL,
  `status` enum('draft','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_activity` (`session_id`,`activity_id`),
  KEY `activity_id` (`activity_id`),
  CONSTRAINT `disbursement_records_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `disbursement_sessions` (`id`),
  CONSTRAINT `disbursement_records_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disbursement_records`
--

LOCK TABLES `disbursement_records` WRITE;
/*!40000 ALTER TABLE `disbursement_records` DISABLE KEYS */;
INSERT INTO `disbursement_records` VALUES (1,14,31,'completed','2026-01-03 22:32:30','2026-01-13 12:12:15');
/*!40000 ALTER TABLE `disbursement_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disbursement_sessions`
--

DROP TABLE IF EXISTS `disbursement_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disbursement_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organization_id` int NOT NULL,
  `fiscal_year` int NOT NULL,
  `record_month` tinyint NOT NULL COMMENT '1-12',
  `record_date` date NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `org_year_month` (`organization_id`,`fiscal_year`,`record_month`),
  CONSTRAINT `disbursement_sessions_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disbursement_sessions`
--

LOCK TABLES `disbursement_sessions` WRITE;
/*!40000 ALTER TABLE `disbursement_sessions` DISABLE KEYS */;
INSERT INTO `disbursement_sessions` VALUES (4,1,2568,12,'2025-12-30',2,'2025-12-30 15:37:08','2025-12-30 15:37:08'),(5,12,2568,12,'2025-12-30',2,'2025-12-30 15:45:18','2025-12-30 15:45:18'),(12,1,2568,1,'2026-01-01',2,'2026-01-01 10:09:37','2026-01-01 10:09:37'),(13,3,2568,10,'2026-01-01',2,'2026-01-01 10:11:23','2026-01-01 10:11:23'),(14,3,2569,10,'2026-01-01',2,'2026-01-01 10:27:26','2026-01-01 10:27:26');
/*!40000 ALTER TABLE `disbursement_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_groups`
--

DROP TABLE IF EXISTS `expense_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `expense_type_id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธ',
  `updated_by` int DEFAULT NULL COMMENT 'เธเธนเนเนเธเนเนเธเธฅเนเธฒเธชเธธเธ',
  PRIMARY KEY (`id`),
  KEY `idx_expense_type` (`expense_type_id`),
  KEY `idx_expense_groups_deleted` (`deleted_at`),
  CONSTRAINT `expense_groups_ibfk_1` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธฅเธธเนเธกเธฃเธฒเธขเธเนเธฒเธข';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_groups`
--

LOCK TABLES `expense_groups` WRITE;
/*!40000 ALTER TABLE `expense_groups` DISABLE KEYS */;
INSERT INTO `expense_groups` VALUES (1,1,'EG-7bd4','เงินเดือนและค่าจ้างประจำ',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(2,1,'EG-0077','ค่าตอบแทนพนักงานราชการ',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(3,2,'EG-0c90','ค่าตอบแทนใช้สอยและวัสดุ',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(4,2,'EG-8695','ค่าสาธารณูปโภค',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(5,3,'EG-42ba','ค่าครุภัณฑ์ ที่ดินและสิ่งก่อสร้าง',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(6,4,'EG-1f8f','รายการย่อย ...',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(7,5,'EG-1f8f','รายการย่อย ...',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(8,4,'EG-b169','ค่าใช้จ่ายในการพัฒนากฎหมาย',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(9,4,'EG-4fc9','ค่าใช้จ่ายในการขับเคลื่อนงานยุติธรรมชุมชน',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(10,4,'EG-c22b','ค่าใช้จ่ายสำหรับโครงการกำลังงใจ',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(11,4,'EG-919e','ค่าใช้จ่ายโครงการส่งเสริมความปลอดภัยด้านการท่องเที่ยว',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(12,4,'EG-02cf','ค่าใช้จ่ายโครงการพัฒนาทักษะดิจิทัลสำหรับบุคลากรภาครัฐเพื่อการขับเคลื่อนรัฐบาลดิจิทัล',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL),(13,4,'EG-c2ad','ค่าใช้จ่ายในการพัฒนาระบบบริหารเพื่อต่อต้านการทุจริตและส่งเสริมคุ้มครองจริยธรรม',NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL);
/*!40000 ALTER TABLE `expense_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_items`
--

DROP TABLE IF EXISTS `expense_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `expense_group_id` int DEFAULT NULL,
  `expense_type_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `level` int DEFAULT '0' COMMENT 'เธฃเธฐเธเธฑเธ 0-5 เธเธฒเธก CSV เธฃเธฒเธขเธเธฒเธฃ 0-5',
  `is_header` tinyint(1) DEFAULT '0' COMMENT 'เนเธเนเธเธซเธฑเธงเธเนเธญเธซเธฅเธฑเธเธซเธฃเธทเธญเนเธกเน',
  `requires_quantity` tinyint(1) DEFAULT '1' COMMENT 'เธเนเธญเธเธฃเธฐเธเธธเธเธณเธเธงเธเธซเธฃเธทเธญเนเธกเน',
  `default_unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'เธเธ' COMMENT 'เธซเธเนเธงเธขเธเธฑเธเนเธฃเธดเนเธกเธเนเธ',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expense_group` (`expense_group_id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_level` (`level`),
  KEY `idx_is_active` (`is_active`),
  KEY `fk_items_expense_type` (`expense_type_id`),
  CONSTRAINT `expense_items_ibfk_1` FOREIGN KEY (`expense_group_id`) REFERENCES `expense_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expense_items_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `expense_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_items_expense_type` FOREIGN KEY (`expense_type_id`) REFERENCES `expense_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธฃเธฒเธขเธเธฒเธฃเธฃเธฒเธขเธเนเธฒเธข (Hierarchical 6 levels)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_items`
--

LOCK TABLES `expense_items` WRITE;
/*!40000 ALTER TABLE `expense_items` DISABLE KEYS */;
INSERT INTO `expense_items` VALUES (1,1,1,NULL,'EI-b9a2','เงินเดือน',NULL,NULL,0,1,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-05 15:52:42',NULL,NULL),(2,1,1,1,'EI-6a9e','อัตราเดิม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(3,1,1,1,'EI-f7d4','อัตราใหม่',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(4,1,1,1,'EI-3879','เงินอื่นที่จ่ายควบกับเงินเดือน',NULL,NULL,1,1,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-05 15:52:42',NULL,NULL),(5,1,1,4,'EI-5df0','เงินประจำตำแหน่ง',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(6,1,1,5,'EI-0a26','เงินประจำตำแหน่ง (บริหารและอำนวยการ)',NULL,NULL,4,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(7,1,1,5,'EI-175b','เงินประจำตำแหน่ง (วิชาการ)',NULL,NULL,4,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(8,1,1,5,'EI-e8a1','เงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)',NULL,NULL,4,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(9,1,1,15,'EI-f5ac','ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์',NULL,NULL,5,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-05 15:49:07',NULL,NULL),(10,1,1,15,'EI-425b','ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก',NULL,NULL,5,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-05 15:49:08',NULL,NULL),(11,1,1,4,'EI-2708','ค่าตอบแทนรายเดือนสำหรับข้าราชการ',NULL,NULL,2,1,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-05 15:52:42',NULL,NULL),(12,1,1,11,'EI-8db4','ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง',NULL,NULL,3,1,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-05 15:52:42',NULL,NULL),(13,1,1,12,'EI-d4d2','ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง (บริหารและอำนวยการ)',NULL,NULL,5,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(14,1,1,12,'EI-e73e','ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่ง (วิชาการ)',NULL,NULL,5,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(15,1,1,12,'EI-18f6','ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)',NULL,NULL,4,1,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-05 15:52:29',NULL,NULL),(16,1,1,11,'EI-0aeb','เงินค่าตอบแทนรายเดือนสำหรับข้าราชการระดับ 8 และ 8ว',NULL,NULL,4,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(17,1,1,4,'EI-e0a6','เงินช่วยเหลือการครองชีพข้าราชการระดับต้น',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(18,1,1,4,'EI-245f','เงิน พ.ต.ก.  (เงินเพิ่มตำแหน่งที่มีเหตุพิเศษของข้าราชการพลเรือนสำหรับผู้ปฏิบัติงานด้านนิติกร)',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(19,1,1,4,'EI-9472','เงิน พ.พ.ด. (เงินเพิ่มพิเศษสำหรับผู้ปฏิบัติงานด้านพัสดุ)',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(20,1,1,4,'EI-dd61','เงิน พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(21,1,1,4,'EI-cbb6','เงิน สปพ. (เงินสวัสดิการสำหรับการปฏิบัติงานประจำสำนักงานในพื้นที่พิเศษ)',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(22,1,1,NULL,'EI-c2dc','ค่าจ้างประจำ',NULL,NULL,1,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(23,1,1,22,'EI-6a9e','อัตราเดิม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(24,1,1,22,'EI-f7d4','อัตราใหม่',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(25,1,1,22,'EI-4e21','เงินอื่นที่จ่ายควบกับค่าจ้างประจำ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(26,1,1,25,'EI-3e85','ค่าตอบแทนรายเดือนลูกจ้างประจำ',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(27,1,1,25,'EI-f475','เงินช่วยเหลือค่าครองชีพ',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(28,1,1,25,'EI-dd61','เงิน พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(29,2,1,NULL,'EI-6a00','ค่าตอบแทนพนักงานราชการ',NULL,NULL,1,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(30,2,1,29,'EI-6a9e','อัตราเดิม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(31,2,1,29,'EI-f7d4','อัตราใหม่',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(32,2,1,29,'EI-c12f','เงินอื่นที่จ่ายควบกับค่าตอบแทนพนักงานราชการ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(33,2,1,32,'EI-a894','เงินช่วยเหลือการครองชีพชั่วคราวพนักงานราชการ',NULL,NULL,3,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(34,3,2,NULL,'EI-6657','ค่าตอบแทน',NULL,NULL,1,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(35,3,2,34,'EI-1449','ค่าเช่าบ้าน',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(36,3,2,34,'EI-8d18','ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(37,3,2,34,'EI-2c25','ค่าตอบแทนพิเศษค่าจ้างเต็มขั้น',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(38,3,2,34,'EI-439f','ค่าตอบแทนพิเศษรายเดือนให้แก่เจ้าหน้าที่ผู้ปฎิบัติงานในพื้นที่จังหวัดชายแดนภาคใต้',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(39,3,2,NULL,'EI-ab85','ค่าใช้สอย',NULL,NULL,1,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(40,3,2,39,'EI-be93','เงินสมทบกองทุนประกันสังคม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(41,3,2,34,'EI-7d96','ค่าตอบแทนผู้ปฏิบัติงานให้ทางราชการ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(42,3,2,34,'EI-e442','ค่าตอบแทนการปฏิบัติงานนอกเวลาราชการ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(43,3,2,34,'EI-6c1c','ค่าเบี้ยประชุมกรรมการ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(44,3,2,34,'EI-7528','ค่าตอบแทนเหมาจ่ายแทนการจัดหารถประจำตำแหน่ง',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(45,3,2,34,'EI-33c0','ค่าตอบแทนการปฏิบัติงานของคณะกรรมการตรวจสอบและประเมินผลประจำกระทรวงยุติธรรม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(46,3,2,39,'EI-5e20','ค่าเบี้ยเลี้ยง ค่าเช่าที่พักและค่าพาหนะ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(47,3,2,39,'EI-c63b','ค่าซ่อมแซมยานพาหนะและขนส่ง',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(48,3,2,39,'EI-c995','ค่าซ่อมแซมครุภัณฑ์',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(49,3,2,39,'EI-7353','ค่าเช่าเครื่องถ่ายเอกสารระบบดิจิทัล',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(50,3,2,39,'EI-bd72','ค่าเช่ารถยนต์ประจำตำแหน่งปลัดกระทรวงยุติธรรม พร้อมพนักงานขับรถยนต์',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(51,3,2,39,'EI-af74','ค่าเช่ารถยนต์ประจำตำแหน่งรัฐมนตรีว่าการกระทรวงยุติธรรม พร้อมพนักงานขับรถยนต์',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(52,3,2,39,'EI-b911','ค่าจ้างเหมาบุคลากรช่วยปฏิบัติงาน',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(53,3,2,39,'EI-c95e','ค่ารับรองและพิธีการ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(54,3,2,39,'EI-dd7b','ค่าธรรมเนียม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(55,3,2,39,'EI-c860','ค่าใช้จ่ายเพื่อการขับเคลื่อนนโยบายกระทรวงยุติธรรม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(56,3,2,39,'EI-373b','ค่าธรรมเนียมเก็บขนขยะมูลฝอย',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(57,3,2,39,'EI-6382','โครงการติดตามนโยบายและตรวจราชการหน่วยงานในสังกัดกระทรวงยุติธรรม ของผู้บริหารกระทรวงยุติธรรม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(58,3,2,39,'EI-193b','ค่าใช้จ่ายในการพิธีรับพระราชทานเครื่องราชอิสริยากรณ์ ชั้นสายสะพาย เบื้องหน้าพระบรมฉายาลักษณ์พระบาทสมเด็จพระเจ้าอยู่หัว',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(59,3,2,39,'EI-50c2','โครงการจัดงานวันสถาปนากระทรวงยุติธรรม ครบรอบ 135 ปี',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(60,3,2,39,'EI-ae08','ค่าจ้างเหมาพนักงานขับรถยนต์ (เพิ่มเติม)',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(61,3,2,39,'EI-cad3','โครงการบริหารงานการรักษาความปลอดภัยในอาคารและพื้นที่ี่กระทรวงยุติธรรม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(62,3,2,39,'EI-e31a','การจ้างเหมาบริการเพื่อจัดทำข้อมูลสนับสนุนเพื่อประกอบการกำหนดนโยบายของผู้บริหาร',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(63,3,2,39,'EI-ae50','ค่าธรรมเนียมฝากมาตรวัดน้ำ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(64,3,2,39,'EI-66dd','ค่าจ้างเหมาบริการ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(65,3,2,39,'EI-69cf','ค่าบำรุงรักษาระบบเทคโนโลยีสารสนเทศ',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(66,3,2,39,'EI-b1ae','ค่าใช้จ่ายในการจัดหาหรือการต่อลิขสิทธิ์',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(67,3,2,39,'EI-da24','ค่าใช้จ่ายในการบริหารจัดการเชิงกลยุทธ์',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(68,3,2,39,'EI-54b6','ค่าใช้จ่ายในการพัฒนาระบบบริหาร',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(69,3,2,39,'EI-914f','ค่าใช้จ่ายในการสัมมนาและฝึกอบรม',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(70,3,2,NULL,'EI-7e41','วัสดุ',NULL,NULL,1,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(71,5,3,NULL,'EI-4bbc','ค่าครุภัณฑ์',NULL,NULL,1,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(72,5,3,71,'EI-a3fc','ครุภัณฑ์คอมพิวเตอร์',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(73,5,3,NULL,'EI-3cf9','ที่ดินและสิ่งก่อสร้าง',NULL,NULL,1,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(74,5,3,71,'EI-d4b3','ครุภัณฑ์สำนักงาน',NULL,NULL,2,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(75,5,3,NULL,'EI-a940','ค่าที่ดินและสิ่งก่อสร้าง',NULL,NULL,1,0,1,'เธเธ',0,1,NULL,'2026-01-01 07:42:31','2026-01-04 17:18:49',NULL,NULL),(77,3,2,39,NULL,'เงินสมทบกองทุนเงินทดแทน',NULL,NULL,2,0,1,'เธเธ',1,1,NULL,'2026-01-04 14:12:10','2026-01-04 17:18:49',NULL,NULL);
/*!40000 ALTER TABLE `expense_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_types`
--

DROP TABLE IF EXISTS `expense_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_types`
--

LOCK TABLES `expense_types` WRITE;
/*!40000 ALTER TABLE `expense_types` DISABLE KEYS */;
INSERT INTO `expense_types` VALUES (1,'ET-dda6','งบบุคลากร',0,1,'2026-01-01 07:42:31',NULL),(2,'ET-57d1','งบดำเนินงาน',0,1,'2026-01-01 07:42:31',NULL),(3,'ET-914f','งบลงทุน',0,1,'2026-01-01 07:42:31',NULL),(4,'ET-dc9b','งบรายจ่ายอื่น',0,1,'2026-01-01 07:42:31',NULL),(5,'ET-8d6b','งบเงินอุดหนุน',0,1,'2026-01-01 07:42:31',NULL);
/*!40000 ALTER TABLE `expense_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `folder_id` int NOT NULL,
  `organization_id` int DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'pdf, xlsx, png, etc.',
  `file_size` int NOT NULL COMMENT 'Size in bytes',
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `uploaded_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_folder` (`folder_id`),
  KEY `idx_files_org` (`organization_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_files_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fiscal_years`
--

DROP TABLE IF EXISTS `fiscal_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_years` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL COMMENT 'เธเธต เธ.เธจ.',
  `start_date` date NOT NULL COMMENT 'เธงเธฑเธเนเธฃเธดเนเธกเธเนเธเธเธตเธเธเธเธฃเธฐเธกเธฒเธ',
  `end_date` date NOT NULL COMMENT 'เธงเธฑเธเธชเธดเนเธเธชเธธเธเธเธตเธเธเธเธฃเธฐเธกเธฒเธ',
  `is_current` tinyint(1) DEFAULT '0' COMMENT 'เธเธตเธเธเธเธฃเธฐเธกเธฒเธเธเธฑเธเธเธธเธเธฑเธ',
  `is_closed` tinyint(1) DEFAULT '0' COMMENT 'เธเธดเธเธเธตเธเธเธเธฃเธฐเธกเธฒเธเนเธฅเนเธง',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`),
  KEY `idx_fiscal_years_current` (`is_current`),
  KEY `idx_fiscal_years_year` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fiscal_years`
--

LOCK TABLES `fiscal_years` WRITE;
/*!40000 ALTER TABLE `fiscal_years` DISABLE KEYS */;
INSERT INTO `fiscal_years` VALUES (1,2566,'2022-10-01','2023-09-30',0,1,'2025-12-14 04:26:01','2025-12-14 04:26:01'),(2,2567,'2023-10-01','2024-09-30',0,1,'2025-12-14 04:26:01','2025-12-14 04:26:01'),(3,2568,'2024-10-01','2025-09-30',0,0,'2025-12-14 04:26:01','2026-01-10 08:13:37'),(4,2569,'2025-10-01','2026-09-30',1,0,'2025-12-14 04:26:01','2026-01-10 08:13:37'),(5,2570,'2026-10-01','2027-09-30',0,0,'2025-12-14 04:29:13','2026-01-15 15:01:59');
/*!40000 ALTER TABLE `fiscal_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `folders`
--

DROP TABLE IF EXISTS `folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `folders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fiscal_year` int DEFAULT NULL COMMENT 'ปีงบประมาณ (2568, 2569, ...)',
  `organization_id` int DEFAULT NULL,
  `budget_category_id` int DEFAULT NULL COMMENT 'เชื่อมกับหมวดหมู่งบประมาณ (ถ้ามี)',
  `parent_id` int DEFAULT NULL COMMENT 'โฟลเดอร์แม่ (สำหรับโฟลเดอร์ที่สร้างเอง)',
  `folder_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เส้นทางเต็มของโฟลเดอร์',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) DEFAULT '0' COMMENT '1 = สร้างจากระบบ, 0 = สร้างเอง',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_category` (`budget_category_id`),
  KEY `idx_folders_org` (`organization_id`,`fiscal_year`),
  CONSTRAINT `fk_folders_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `folders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `folders`
--

LOCK TABLES `folders` WRITE;
/*!40000 ALTER TABLE `folders` DISABLE KEYS */;
INSERT INTO `folders` VALUES (1,'งบบุคลากร',2568,NULL,1,NULL,'2568/งบบุคลากร',NULL,1,2,'2025-12-17 13:16:15','2025-12-17 13:16:15'),(2,'งบดำเนินงาน',2568,NULL,20,NULL,'2568/งบดำเนินงาน',NULL,1,2,'2025-12-17 13:16:15','2025-12-17 13:16:15'),(3,'ส่วนกลาง',2569,NULL,NULL,NULL,'2569/ส่วนกลาง','โฟลเดอร์ส่วนกลาง สำหรับเอกสารที่ทุกหน่วยงานเข้าถึงได้',1,2,'2026-01-13 18:33:21','2026-01-13 18:33:21'),(4,'กองบริหารทรัพยากรบุคคล',2569,3,NULL,NULL,'2569/กองบริหารทรัพยากรบุคคล','',0,2,'2026-01-14 11:24:20','2026-01-14 11:24:20');
/*!40000 ALTER TABLE `folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_actuals`
--

DROP TABLE IF EXISTS `kpi_actuals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kpi_actuals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kpi_target_id` int NOT NULL COMMENT 'FK: kpi_targets.id',
  `actual_value` decimal(15,2) NOT NULL COMMENT 'เธเนเธฒเธเธฃเธดเธเธเธตเนเธงเธฑเธเนเธเน',
  `recorded_date` date NOT NULL COMMENT 'เธงเธฑเธเธเธตเนเธเธฑเธเธเธถเธเธเธฅ',
  `achievement_rate` decimal(5,2) DEFAULT NULL COMMENT 'เธญเธฑเธเธฃเธฒเธเธงเธฒเธกเธชเธณเนเธฃเนเธ (%)',
  `variance` decimal(15,2) DEFAULT NULL COMMENT 'เธเธฅเธเนเธฒเธเธเธฒเธเนเธเนเธฒ',
  `status` enum('achieved','warning','critical','pending','exceeded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'เธชเธเธฒเธเธฐเธเธฅเธฅเธฑเธเธเน',
  `supporting_data` json DEFAULT NULL COMMENT 'เธเนเธญเธกเธนเธฅเนเธเธดเนเธกเนเธเธดเธก (JSON format)',
  `source_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เธญเนเธฒเธเธญเธดเธเนเธซเธฅเนเธเธเนเธญเธกเธนเธฅ',
  `remarks` text COLLATE utf8mb4_unicode_ci COMMENT 'เธซเธกเธฒเธขเนเธซเธเธธ',
  `verified_by` int DEFAULT NULL COMMENT 'เธเธนเนเธเธฃเธงเธเธชเธญเธ (FK: users)',
  `verified_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเธเธตเนเธเธฃเธงเธเธชเธญเธ',
  `verification_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'เธเธฑเธเธเธถเธเธเธฒเธฃเธเธฃเธงเธเธชเธญเธ',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_target` (`kpi_target_id`),
  KEY `idx_recorded_date` (`recorded_date`),
  KEY `idx_status` (`status`),
  KEY `idx_verified` (`verified_by`,`verified_at`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `kpi_actuals_ibfk_1` FOREIGN KEY (`kpi_target_id`) REFERENCES `kpi_targets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธฅเธเธฒเธฃเธเธณเนเธเธดเธเธเธฒเธเธเธฃเธดเธ KPI';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_actuals`
--

LOCK TABLES `kpi_actuals` WRITE;
/*!40000 ALTER TABLE `kpi_actuals` DISABLE KEYS */;
INSERT INTO `kpi_actuals` VALUES (1,1,20.00,'2024-12-31',80.00,-5.00,'warning',NULL,NULL,NULL,NULL,NULL,NULL,'2026-01-01 08:09:57','2026-01-01 08:09:57',NULL,NULL);
/*!40000 ALTER TABLE `kpi_actuals` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = tis620 */ ;
/*!50003 SET character_set_results = tis620 */ ;
/*!50003 SET collation_connection  = tis620_thai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_kpi_actuals_before_insert` BEFORE INSERT ON `kpi_actuals` FOR EACH ROW BEGIN
    DECLARE v_target_value DECIMAL(15,2);
    DECLARE v_threshold_warning DECIMAL(15,2);
    DECLARE v_threshold_critical DECIMAL(15,2);
    
    
    SELECT 
        target_value, 
        threshold_warning, 
        threshold_critical
    INTO 
        v_target_value,
        v_threshold_warning,
        v_threshold_critical
    FROM kpi_targets 
    WHERE id = NEW.kpi_target_id;
    
    
    SET NEW.achievement_rate = calculate_achievement_rate(NEW.actual_value, v_target_value);
    
    
    SET NEW.variance = NEW.actual_value - v_target_value;
    
    
    SET NEW.status = determine_kpi_status(
        NEW.achievement_rate,
        COALESCE(v_threshold_warning, v_target_value * 0.9),
        COALESCE(v_threshold_critical, v_target_value * 0.7),
        v_target_value
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = tis620 */ ;
/*!50003 SET character_set_results = tis620 */ ;
/*!50003 SET collation_connection  = tis620_thai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_kpi_actuals_before_update` BEFORE UPDATE ON `kpi_actuals` FOR EACH ROW BEGIN
    DECLARE v_target_value DECIMAL(15,2);
    DECLARE v_threshold_warning DECIMAL(15,2);
    DECLARE v_threshold_critical DECIMAL(15,2);
    
    
    IF NEW.actual_value != OLD.actual_value THEN
        
        SELECT 
            target_value, 
            threshold_warning, 
            threshold_critical
        INTO 
            v_target_value,
            v_threshold_warning,
            v_threshold_critical
        FROM kpi_targets 
        WHERE id = NEW.kpi_target_id;
        
        
        SET NEW.achievement_rate = calculate_achievement_rate(NEW.actual_value, v_target_value);
        
        
        SET NEW.variance = NEW.actual_value - v_target_value;
        
        
        SET NEW.status = determine_kpi_status(
            NEW.achievement_rate,
            COALESCE(v_threshold_warning, v_target_value * 0.9),
            COALESCE(v_threshold_critical, v_target_value * 0.7),
            v_target_value
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kpi_definitions`
--

DROP TABLE IF EXISTS `kpi_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kpi_definitions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kpi_source_id` int NOT NULL COMMENT 'FK: kpi_sources.id',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธฃเธซเธฑเธช KPI',
  `name_th` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธเธทเนเธญ KPI',
  `name_en` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'English name',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'เธเธณเธญเธเธดเธเธฒเธข KPI',
  `metric_type` enum('disbursement_pct','approval_count','processing_time','project_count','activity_completed','percentage','amount','count','days','ratio','custom') COLLATE utf8mb4_unicode_ci DEFAULT 'percentage' COMMENT 'เธเธฃเธฐเนเธ�เธ metric',
  `calculation_method` text COLLATE utf8mb4_unicode_ci COMMENT 'เธงเธดเธเธตเธเธฒเธฃเธเธณเธเธงเธ (SQL เธซเธฃเธทเธญ formula)',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '%' COMMENT 'เธซเธเนเธงเธขเธงเธฑเธ (%, เธเธฒเธ, เธเธฃเธฑเนเธ, เธงเธฑเธ, เนเธเธฃเธเธเธฒเธฃ, เธเธดเธเธเธฃเธฃเธก)',
  `has_target` tinyint(1) DEFAULT '1' COMMENT 'เธกเธต target เธซเธฃเธทเธญเนเธกเน',
  `target_type` enum('fixed','cumulative','average','minimum','maximum') COLLATE utf8mb4_unicode_ci DEFAULT 'fixed' COMMENT 'เธเธฃเธฐเนเธ�เธ target',
  `display_format` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0.00' COMMENT 'เธฃเธนเธเนเธเธเธเธฒเธฃเนเธชเธเธเธเธฅ',
  `color_good` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#22c55e' COMMENT 'เธชเธตเนเธเธตเธขเธง (เธเธฅเธเธต)',
  `color_warning` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#f59e0b' COMMENT 'เธชเธตเนเธซเธฅเธทเธญเธ (เนเธเธทเธญเธ)',
  `color_bad` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#ef4444' COMMENT 'เธชเธตเนเธเธ (เธเธฅเนเธกเนเธเธต)',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เนเธญเธเธญเธ (e.g. chart-line, clock, folder)',
  `fiscal_year` int DEFAULT '2569',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_code_year` (`kpi_source_id`,`code`,`fiscal_year`),
  KEY `idx_source` (`kpi_source_id`),
  KEY `idx_metric_type` (`metric_type`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_code` (`code`),
  CONSTRAINT `kpi_definitions_ibfk_1` FOREIGN KEY (`kpi_source_id`) REFERENCES `kpi_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธดเธขเธฒเธก KPI';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_definitions`
--

LOCK TABLES `kpi_definitions` WRITE;
/*!40000 ALTER TABLE `kpi_definitions` DISABLE KEYS */;
INSERT INTO `kpi_definitions` VALUES (1,1,'DISB_PCT','เนเธเธญเธฃเนเนเธเนเธเธเนเธเธฒเธฃเนเธเธดเธเธเนเธฒเธข',NULL,NULL,'disbursement_pct',NULL,'%',1,'cumulative','0.00','#22c55e','#f59e0b','#ef4444',NULL,2569,1,1,NULL,'2026-01-01 08:04:17','2026-01-01 08:04:17',NULL,NULL),(2,1,'APPROVAL_CNT','เธเธณเธเธงเธเธเธฒเธฃเธญเธเธธเธกเธฑเธเธด',NULL,NULL,'approval_count',NULL,'เธฃเธฒเธขเธเธฒเธฃ',1,'cumulative','0.00','#22c55e','#f59e0b','#ef4444',NULL,2569,2,1,NULL,'2026-01-01 08:04:17','2026-01-01 08:04:17',NULL,NULL),(3,2,'PROC_TIME','เธฃเธฐเธขเธฐเนเธงเธฅเธฒเธเธณเนเธเธดเธเธเธฒเธฃ',NULL,NULL,'processing_time',NULL,'เธงเธฑเธ',1,'average','0.00','#22c55e','#f59e0b','#ef4444',NULL,2569,3,1,NULL,'2026-01-01 08:04:17','2026-01-01 08:04:17',NULL,NULL),(4,3,'PROJECT_CNT','เธเธณเธเธงเธเนเธเธฃเธเธเธฒเธฃเธเธตเนเธเธณเนเธเธดเธเธเธฒเธฃ',NULL,NULL,'project_count',NULL,'เนเธเธฃเธเธเธฒเธฃ',1,'cumulative','0.00','#22c55e','#f59e0b','#ef4444',NULL,2569,4,1,NULL,'2026-01-01 08:04:17','2026-01-01 08:04:17',NULL,NULL),(5,3,'ACT_COMPLETE','เธเธณเธเธงเธเธเธดเธเธเธฃเธฃเธกเธเธตเนเธเธณเนเธเธดเธเธเธฒเธฃเนเธฅเนเธงเนเธชเธฃเนเธ',NULL,NULL,'activity_completed',NULL,'เธเธดเธเธเธฃเธฃเธก',1,'cumulative','0.00','#22c55e','#f59e0b','#ef4444',NULL,2569,5,1,NULL,'2026-01-01 08:04:17','2026-01-01 08:04:17',NULL,NULL);
/*!40000 ALTER TABLE `kpi_definitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_sources`
--

DROP TABLE IF EXISTS `kpi_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kpi_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธฃเธซเธฑเธชเนเธซเธฅเนเธ',
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธเธทเนเธญเนเธซเธฅเนเธเธเนเธญเธกเธนเธฅ',
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'English name',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'เธฃเธฒเธขเธฅเธฐเนเธญเธตเธขเธ',
  `is_system` tinyint(1) DEFAULT '0' COMMENT 'เธฃเธฐเธเธเธเธณเธซเธเธ (เนเธกเนเธชเธฒเธกเธฒเธฃเธเธฅเธเนเธเน)',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เนเธซเธฅเนเธเธเนเธญเธกเธนเธฅ KPI';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_sources`
--

LOCK TABLES `kpi_sources` WRITE;
/*!40000 ALTER TABLE `kpi_sources` DISABLE KEYS */;
INSERT INTO `kpi_sources` VALUES (1,'ACT_FY','เธเธฃเธ เธฃเธฒเธขเธเนเธฒเธขเธเธเธเธฃเธฐเธกเธฒเธเธเธฃเธฐเธเธณเธเธต','Annual Appropriation Act',NULL,1,1,1,'2026-01-01 08:04:17','2026-01-01 08:04:17'),(2,'CGD','เธเธฃเธกเธเธฑเธเธเธตเธเธฅเธฒเธ','Comptroller General Department',NULL,1,2,1,'2026-01-01 08:04:17','2026-01-01 08:04:17'),(3,'MIN_PLAN','เนเธเธเธเธฃเธฐเธเธฃเธงเธเธขเธธเธเธดเธเธฃเธฃเธก','Ministry of Justice Plan',NULL,1,3,1,'2026-01-01 08:04:17','2026-01-01 08:04:17'),(4,'OPS_PLAN','เนเธเธเธชเธณเธเธฑเธเธเธฒเธเธเธฅเธฑเธเธเธฃเธฐเธเธฃเธงเธเธขเธธเธเธดเธเธฃเธฃเธก','Permanent Secretary Office Plan',NULL,1,4,1,'2026-01-01 08:04:17','2026-01-01 08:04:17'),(5,'POLICY','เธเนเธขเธเธฒเธข/เธเนเธญเธชเธฑเนเธเธเธฒเธฃ','Policy/Directive',NULL,1,5,1,'2026-01-01 08:04:17','2026-01-01 08:04:17'),(6,'CUSTOM','เธเธณเธซเธเธเนเธญเธ','Custom',NULL,0,99,1,'2026-01-01 08:04:17','2026-01-01 08:04:17');
/*!40000 ALTER TABLE `kpi_sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_targets`
--

DROP TABLE IF EXISTS `kpi_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kpi_targets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kpi_definition_id` int NOT NULL COMMENT 'FK: kpi_definitions.id',
  `budget_line_item_id` int DEFAULT NULL COMMENT 'FK: budget_line_items.id (เธเนเธฒเนเธเธเธฒเธฐเนเธเธฒเธฐเธเธ)',
  `budget_type_id` int DEFAULT NULL COMMENT 'FK: budget_types.id (เธเนเธฒเธฃเธฐเธเธฑเธ type)',
  `plan_id` int DEFAULT NULL COMMENT 'FK: plans.id (เธเนเธฒเธฃเธฐเธเธฑเธ plan)',
  `project_id` int DEFAULT NULL COMMENT 'FK: projects.id (เธเนเธฒเธฃเธฐเธเธฑเธ project)',
  `activity_id` int DEFAULT NULL COMMENT 'FK: activities.id (เธเนเธฒเธฃเธฐเธเธฑเธ activity)',
  `organization_id` int DEFAULT NULL COMMENT 'FK: organizations.id (เธเนเธฒเธฃเธฐเธเธฑเธ org)',
  `fiscal_year` int NOT NULL DEFAULT '2569',
  `period_type` enum('yearly','quarterly','monthly','weekly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yearly' COMMENT 'เธเธฃเธฐเนเธ�เธเธเนเธงเธเนเธงเธฅเธฒ',
  `period_value` int DEFAULT NULL COMMENT 'เธเนเธฒเธเนเธงเธเนเธงเธฅเธฒ: Q1-4 (1-4), Month (1-12), Week (1-52), NULL=yearly',
  `period_start_date` date DEFAULT NULL COMMENT 'เธงเธฑเธเนเธฃเธดเนเธกเธเนเธเธเนเธงเธ (เธชเธณเธซเธฃเธฑเธ weekly)',
  `period_end_date` date DEFAULT NULL COMMENT 'เธงเธฑเธเธชเธดเนเธเธชเธธเธเธเนเธงเธ (เธชเธณเธซเธฃเธฑเธ weekly)',
  `target_value` decimal(15,2) NOT NULL COMMENT 'เธเนเธฒเนเธเนเธฒเธซเธกเธฒเธข',
  `threshold_warning` decimal(15,2) DEFAULT NULL COMMENT 'เธเธตเธเนเธเธทเธญเธ (เนเธซเธฅเธทเธญเธ) - เธเนเธณเธเธงเนเธฒเธเธตเนเนเธฃเธดเนเธกเนเธเธทเธญเธ',
  `threshold_critical` decimal(15,2) DEFAULT NULL COMMENT 'เธเธตเธเธงเธดเธเธคเธ (เนเธเธ) - เธเนเธณเธเธงเนเธฒเธเธตเนเธงเธดเธเธคเธ',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'เธซเธกเธฒเธขเนเธซเธเธธ',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kpi_def` (`kpi_definition_id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_period` (`period_type`,`period_value`),
  KEY `idx_period_dates` (`period_start_date`,`period_end_date`),
  KEY `idx_budget_line` (`budget_line_item_id`),
  KEY `idx_project` (`project_id`),
  KEY `idx_activity` (`activity_id`),
  KEY `budget_type_id` (`budget_type_id`),
  KEY `plan_id` (`plan_id`),
  KEY `organization_id` (`organization_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `kpi_targets_ibfk_1` FOREIGN KEY (`kpi_definition_id`) REFERENCES `kpi_definitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kpi_targets_ibfk_2` FOREIGN KEY (`budget_line_item_id`) REFERENCES `budget_line_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kpi_targets_ibfk_3` FOREIGN KEY (`budget_type_id`) REFERENCES `budget_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kpi_targets_ibfk_4` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kpi_targets_ibfk_5` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kpi_targets_ibfk_6` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kpi_targets_ibfk_7` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_period_value` CHECK ((((`period_type` = _utf8mb4'yearly') and (`period_value` is null)) or ((`period_type` = _utf8mb4'quarterly') and (`period_value` between 1 and 4)) or ((`period_type` = _utf8mb4'monthly') and (`period_value` between 1 and 12)) or ((`period_type` = _utf8mb4'weekly') and (`period_value` between 1 and 52)))),
  CONSTRAINT `chk_weekly_dates` CHECK (((`period_type` <> _utf8mb4'weekly') or ((`period_start_date` is not null) and (`period_end_date` is not null))))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เนเธเนเธฒเธซเธกเธฒเธข KPI เธเธฒเธกเธเนเธงเธเนเธงเธฅเธฒ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_targets`
--

LOCK TABLES `kpi_targets` WRITE;
/*!40000 ALTER TABLE `kpi_targets` DISABLE KEYS */;
INSERT INTO `kpi_targets` VALUES (1,1,NULL,NULL,NULL,NULL,NULL,NULL,2569,'quarterly',1,NULL,NULL,25.00,22.50,17.50,NULL,1,'2026-01-01 08:09:57','2026-01-01 08:09:57',NULL,NULL);
/*!40000 ALTER TABLE `kpi_targets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. approval_request, approved, rejected',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abbreviation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `budget_allocated` decimal(15,2) DEFAULT '0.00',
  `level` int NOT NULL DEFAULT '0' COMMENT 'ระดับ: 0=กระทรวง, 1=กรม, 2=กอง/สำนัก, 3=กลุ่มงาน, 4=จังหวัด/ส่วนราชการ',
  `org_type` enum('ministry','department','division','section','province','office') COLLATE utf8mb4_unicode_ci DEFAULT 'division' COMMENT 'ประเภทหน่วยงาน: กระทรวง/กรม/กอง/กลุ่มงาน/จังหวัด/ส่วนราชการ',
  `province_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'รหัสจังหวัด (สำหรับหน่วยงานส่วนภูมิภาค)',
  `region` enum('central','regional','provincial','central_in_region') COLLATE utf8mb4_unicode_ci DEFAULT 'central',
  `contact_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
  `contact_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'อีเมล',
  `address` text COLLATE utf8mb4_unicode_ci COMMENT 'ที่อยู่',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_org_type` (`org_type`),
  KEY `idx_org_region` (`region`),
  KEY `idx_org_province` (`province_code`),
  KEY `fk_organizations_parent_id` (`parent_id`),
  CONSTRAINT `fk_organizations_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES (1,NULL,'MN-2044','กระทรวงยุติธรรม',NULL,0.00,0,'ministry',NULL,'central',NULL,NULL,NULL,0,1,'2026-01-01 07:42:31','2026-01-01 07:42:31'),(2,1,'DP-0869','สำนักงานปลัดกระทรวงยุติธรรม',NULL,0.00,1,'department',NULL,'central',NULL,NULL,NULL,0,1,'2026-01-01 07:42:31','2026-01-01 07:42:31'),(3,2,'DV-5bc1','กองบริหารทรัพยากรบุคคล',NULL,0.00,2,'division',NULL,'central',NULL,NULL,NULL,0,1,'2026-01-01 07:42:31','2026-01-01 07:42:31'),(4,3,'SC-ff2b','กลุ่มงานระบบข้อมูลบุคคล ค่าตอบแทนและบำเหน็จความชอบ',NULL,0.00,3,'section',NULL,'central',NULL,NULL,NULL,0,1,'2026-01-01 07:42:31','2026-01-01 07:42:31');
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `budget_type_id` int DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `fiscal_year` int DEFAULT '2568',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธ',
  `updated_by` int DEFAULT NULL COMMENT 'เธเธนเนเนเธเนเนเธเธฅเนเธฒเธชเธธเธ',
  PRIMARY KEY (`id`),
  KEY `idx_budget_type` (`budget_type_id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_plans_deleted` (`deleted_at`),
  CONSTRAINT `plans_ibfk_1` FOREIGN KEY (`budget_type_id`) REFERENCES `budget_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เนเธเธเธเธฒเธ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (15,1,'PL-e0bded','แผนงานบุคลากรภาครัฐ',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL),(16,2,'PL-c7fc87','แผนงานพื้นฐานด้านการปรับสมดุลและพัฒนาระบบการบริหารจัดการรัฐ',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL),(17,2,'PL-8297c2','แผนงานยุทธศาสตร์ป้องกันและแก้ไขปัญหาที่มีผลกระทบต่อความมั่นคง',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL),(18,2,'PL-65200c','แผนงานยุทธศาสตร์พัฒนากฎหมายและกระบวนการยุติธรรม',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL),(19,2,'PL-d5812d','แผนงานบูรณาการสร้างรายได้จากการท่องเที่ยว',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL),(20,3,'PL-2cdb9b','แผนงานบูรณารัฐบาลดิจิทัล',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL),(21,3,'PL-efdc14','แผนงานบูรณาการต่อต้านการทุจริตและประพฤติมิชอบ',NULL,NULL,2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL);
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL COMMENT 'FK: projects.id (Parent Project)',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `project_type` enum('output','project') COLLATE utf8mb4_unicode_ci DEFAULT 'output' COMMENT 'เธเธฃเธฐเนเธ�เธ: เธเธฅเธเธฅเธดเธ เธซเธฃเธทเธญ เนเธเธฃเธเธเธฒเธฃ',
  `fiscal_year` int DEFAULT '2568',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธ',
  `updated_by` int DEFAULT NULL COMMENT 'เธเธนเนเนเธเนเนเธเธฅเนเธฒเธชเธธเธ',
  `level` int DEFAULT '0' COMMENT 'Level: 0=Root, 1=Sub, 2=Sub-Sub',
  PRIMARY KEY (`id`),
  KEY `idx_plan` (`plan_id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_projects_deleted` (`deleted_at`),
  KEY `idx_projects_parent` (`parent_id`),
  KEY `idx_projects_level` (`level`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_ibfk_parent` FOREIGN KEY (`parent_id`) REFERENCES `projects` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธฅเธเธฅเธดเธ/เนเธเธฃเธเธเธฒเธฃ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (21,15,NULL,'PJ-c7c4d0','รายการค่าใช้จ่ายบุคลากรภาครัฐ',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:26','2026-01-01 07:47:26',NULL,NULL,0),(22,16,NULL,'PJ-7e69d3','สนับสนุนการบริหารจัดการหน่วยงานในสังกัดและให้บริการแก่ประชาชนในด้านงานยุติธรรม',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(23,17,NULL,'PJ-24fb9f','โครงการอำนวยความยุติธรรรมของกระทรวงยุติธรรมที่สอดคล้องกับวิถีชีวิตของประชาชนในพื้นที่จังหวัดชายแดนภาคใต้',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(24,18,NULL,'PJ-e0440f','โครงการพัฒนาและส่งเสริมให้ประชาชนเข้าถึงความเป็นธรรม',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(25,18,NULL,'PJ-8c4ae6','โครงการพัฒนากฎหมายกระทรวงยุติธรรม',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(26,18,NULL,'PJ-33a6b7','โครงการส่งเสริม สนับสนุน และบูรณาการประสานความร่วมมือกับภาคีเครือข่ายเพื่อขับเคลื่อนงานยุติธรรม',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(27,19,NULL,'PJ-563171','โครงการส่งเสริมความปลอดภัยด้านการท่องเที่ยว',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(28,20,NULL,'PJ-ac1c5a','โครงการการยกระดับทักษะบุคลากรภาครัฐเพื่อตอบโจทย์ความต้องการของประเทศ',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(29,20,NULL,'PJ-0d2b0d','โครงการสนับสุนการดำเนินงานตามนโยบายการใช้คลาวด์เป็นหลัก (Cloud First Policy)',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(30,21,NULL,'PJ-78c14f','โครงการพัฒนาระบบบริหารเพื่อต่อต่อต้านการธระสแสเสร็มค้มครองครองรธรรม',NULL,NULL,'output',2569,0,1,NULL,'2026-01-01 07:47:27','2026-01-01 07:47:27',NULL,NULL,0),(31,NULL,NULL,NULL,'Root Project',NULL,NULL,'output',2568,0,1,NULL,'2026-01-01 08:27:02','2026-01-01 08:27:02',NULL,NULL,0),(32,NULL,31,NULL,'Child Project',NULL,NULL,'output',2568,0,1,NULL,'2026-01-01 08:27:02','2026-01-01 08:27:02',NULL,NULL,0);
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = tis620 */ ;
/*!50003 SET character_set_results = tis620 */ ;
/*!50003 SET collation_connection  = tis620_thai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_projects_check_circular_insert` BEFORE INSERT ON `projects` FOR EACH ROW BEGIN
    IF NEW.parent_id IS NOT NULL AND NEW.parent_id = 0 THEN 
         SET NEW.parent_id = NULL;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = tis620 */ ;
/*!50003 SET character_set_results = tis620 */ ;
/*!50003 SET collation_connection  = tis620_thai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_projects_check_circular_update` BEFORE UPDATE ON `projects` FOR EACH ROW BEGIN
    DECLARE current_parent INT;
    
    IF NEW.parent_id IS NOT NULL AND (OLD.parent_id IS NULL OR NEW.parent_id != OLD.parent_id) THEN
        IF NEW.parent_id = NEW.id THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Project cannot be its own parent.';
        END IF;
        
        SET current_parent = NEW.parent_id;
        
        
        WHILE current_parent IS NOT NULL DO
            IF current_parent = NEW.id THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Circular reference detected in project hierarchy.';
            END IF;
            
            SELECT parent_id INTO current_parent FROM projects WHERE id = current_parent;
            IF current_parent = 0 THEN SET current_parent = NULL; END IF;
        END WHILE;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `provinces`
--

DROP TABLE IF EXISTS `provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provinces` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธฃเธซเธฑเธชเธเธฑเธเธซเธงเธฑเธ',
  `name_th` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'เธเธณเธญเธเธดเธเธฒเธขเนเธเธดเนเธกเนเธเธดเธก',
  `region` enum('central','north','northeast','east','west','south') COLLATE utf8mb4_unicode_ci DEFAULT 'central' COMMENT 'เธ�เธฒเธ',
  `province_group_id` int DEFAULT NULL COMMENT 'FK: province_groups.id',
  `province_zone_id` int DEFAULT NULL COMMENT 'FK: province_zones.id',
  `inspection_zone_id` int DEFAULT NULL COMMENT 'FK: inspection_zones.id',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธ',
  `updated_by` int DEFAULT NULL COMMENT 'เธเธนเนเนเธเนเนเธเธฅเนเธฒเธชเธธเธ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_provinces_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธฑเธเธซเธงเธฑเธ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provinces`
--

LOCK TABLES `provinces` WRITE;
/*!40000 ALTER TABLE `provinces` DISABLE KEYS */;
INSERT INTO `provinces` VALUES (1,'PR-f9f6','กรุงเทพมหานคร',NULL,NULL,'central',NULL,NULL,NULL,0,1,NULL,'2026-01-01 07:42:31','2026-01-01 07:42:31',NULL,NULL);
/*!40000 ALTER TABLE `provinces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `source_of_truth_mappings`
--

DROP TABLE IF EXISTS `source_of_truth_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `source_of_truth_mappings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fiscal_year` int NOT NULL,
  `organization_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `project_id` int NOT NULL,
  `activity_id` int NOT NULL,
  `is_official` tinyint(1) DEFAULT '1',
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'python_etl',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_org_year` (`organization_id`,`fiscal_year`),
  KEY `idx_activity` (`activity_id`),
  KEY `fk_source_of_truth_mappings_plan_id` (`plan_id`),
  KEY `fk_source_of_truth_mappings_project_id` (`project_id`),
  CONSTRAINT `fk_source_of_truth_mappings_activity_id` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_source_of_truth_mappings_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_source_of_truth_mappings_plan_id` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_source_of_truth_mappings_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source_of_truth_mappings`
--

LOCK TABLES `source_of_truth_mappings` WRITE;
/*!40000 ALTER TABLE `source_of_truth_mappings` DISABLE KEYS */;
INSERT INTO `source_of_truth_mappings` VALUES (1,2569,3,15,21,31,1,'python_etl','2026-01-03 21:55:57','2026-01-03 21:55:57');
/*!40000 ALTER TABLE `source_of_truth_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','editor','viewer') COLLATE utf8mb4_unicode_ci DEFAULT 'viewer',
  `is_active` tinyint(1) DEFAULT '1',
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@hrbudget.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator',NULL,'admin',1,'IT','2025-12-12 14:52:00','2025-12-12 14:52:00',NULL),(2,'admin@moj.go.th','$2y$10$EOT5yECB0sAZXYb9M7ez1ep8ZLZC4s/5ma/UCDySeV1fx7.zD/MeS','Admin User',NULL,'admin',1,'IT','2025-12-13 04:13:56','2026-01-17 03:12:10','2026-01-17 03:12:10'),(3,'viewer@moj.go.th','$2y$10$AUOll9s5YC2eeRKCsn.sa.aaoI2j0hzwvB9TbDtSQ0lpoHqrXfxhi','Viewer User',NULL,'viewer',1,'Finance','2025-12-13 04:13:57','2025-12-14 03:59:16',NULL),(5,'editor@moj.go.th','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Editor User',NULL,'editor',1,'เธเธฃเธกเธเธธเธกเธเธฃเธฐเธเธคเธเธด','2025-12-14 03:59:16','2025-12-14 03:59:16',NULL),(189,'thaid.user@moj.go.th','$2y$10$hT6j7Hu6gre5QZb.PO1Nk.GAKWjwDwdWSIEYNwgPWBkFn3.AwJlzS','ผู้ใช้ ThaID (Mock)',NULL,'viewer',1,'กระทรวงยุติธรรม','2025-12-18 11:30:51','2025-12-18 11:30:51',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `v_kpi_dashboard`
--

DROP TABLE IF EXISTS `v_kpi_dashboard`;
/*!50001 DROP VIEW IF EXISTS `v_kpi_dashboard`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_kpi_dashboard` AS SELECT 
 1 AS `source_code`,
 1 AS `source_name`,
 1 AS `kpi_code`,
 1 AS `kpi_name`,
 1 AS `metric_type`,
 1 AS `unit`,
 1 AS `fiscal_year`,
 1 AS `period_type`,
 1 AS `period_value`,
 1 AS `target_value`,
 1 AS `threshold_warning`,
 1 AS `threshold_critical`,
 1 AS `actual_value`,
 1 AS `achievement_rate`,
 1 AS `status`,
 1 AS `recorded_date`,
 1 AS `color_good`,
 1 AS `color_warning`,
 1 AS `color_bad`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_organizations_hierarchy`
--

DROP TABLE IF EXISTS `v_organizations_hierarchy`;
/*!50001 DROP VIEW IF EXISTS `v_organizations_hierarchy`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_organizations_hierarchy` AS SELECT 
 1 AS `id`,
 1 AS `parent_id`,
 1 AS `code`,
 1 AS `name_th`,
 1 AS `abbreviation`,
 1 AS `budget_allocated`,
 1 AS `level`,
 1 AS `org_type`,
 1 AS `province_code`,
 1 AS `region`,
 1 AS `contact_phone`,
 1 AS `contact_email`,
 1 AS `address`,
 1 AS `sort_order`,
 1 AS `is_active`,
 1 AS `created_at`,
 1 AS `updated_at`,
 1 AS `parent_name`,
 1 AS `parent_code`,
 1 AS `org_type_label`,
 1 AS `region_label`*/;
SET character_set_client = @saved_cs_client;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!50606 SET GLOBAL INNODB_STATS_AUTO_RECALC=@OLD_INNODB_STATS_AUTO_RECALC */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-09 20:21:37
