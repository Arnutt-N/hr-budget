-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: hr_budget
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Table structure for table `approval_levels`
--

DROP TABLE IF EXISTS `approval_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_levels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `level` int NOT NULL COMMENT 'ลำดับขั้น 1..n',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'บทบาทที่อนุมัติขั้นนี้ (อ้าง roles.code)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_approval_level` (`level`),
  UNIQUE KEY `uq_approval_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='สายอนุมัติหลายขั้น';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_levels`
--

LOCK TABLES `approval_levels` WRITE;
/*!40000 ALTER TABLE `approval_levels` DISABLE KEYS */;
INSERT INTO `approval_levels` VALUES (1,1,'division','อนุมัติระดับกอง','approver_division',1,'2026-06-17 17:32:41'),(2,2,'department','อนุมัติระดับกรม','approver_department',1,'2026-06-17 17:32:41'),(3,3,'ministry','อนุมัติระดับกระทรวง','approver_ministry',1,'2026-06-17 17:32:41');
/*!40000 ALTER TABLE `approval_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_settings`
--

DROP TABLE IF EXISTS `approval_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `status` enum('active','closed','frozen') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
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
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `parent_id` int DEFAULT NULL,
  `level` int NOT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_plan` tinyint(1) DEFAULT '0',
  `plan_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `region_type` enum('central','regional') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'central' COMMENT 'ส่วนกลาง/ภูมิภาค',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'หมายเหตุ',
  `status` enum('active','closed','frozen') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
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
  `allocation_id` int DEFAULT NULL,
  `organization_id` int DEFAULT NULL COMMENT 'org-level snapshot',
  `fiscal_year` int NOT NULL,
  `allocated_pba` decimal(15,2) DEFAULT NULL COMMENT 'งบตาม พรบ.',
  `snapshot_date` date NOT NULL COMMENT 'เธงเธฑเธเธ?เธตเนเธเธฑเธเธ?เธถเธ (เธชเธดเนเธเน?เธ?เธทเธญเธ)',
  `allocated_received` decimal(15,2) DEFAULT '0.00',
  `transfer` decimal(15,2) DEFAULT NULL COMMENT 'โอนจัดสรร/โอนเปลี่ยนแปลง',
  `disbursed` decimal(15,2) DEFAULT '0.00',
  `pending` decimal(15,2) DEFAULT NULL COMMENT 'ขออนุมัติวงเงิน',
  `po_commitment` decimal(15,2) DEFAULT '0.00',
  `remaining` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เช่น pdf_import',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bms_org_date` (`organization_id`,`fiscal_year`,`snapshot_date`),
  KEY `idx_snapshot_date` (`snapshot_date`),
  KEY `idx_allocation_fiscal` (`allocation_id`,`fiscal_year`),
  KEY `idx_bms_org` (`organization_id`,`fiscal_year`,`snapshot_date`),
  CONSTRAINT `fk_bms_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=661 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_monthly_snapshots`
--

LOCK TABLES `budget_monthly_snapshots` WRITE;
/*!40000 ALTER TABLE `budget_monthly_snapshots` DISABLE KEYS */;
INSERT INTO `budget_monthly_snapshots` VALUES (1,NULL,7,2568,155647500.00,'2024-10-28',78071400.00,0.00,76660200.00,0.00,0.00,1411200.00,'2026-06-17 17:22:56','pdf_import'),(2,NULL,7,2568,155647500.00,'2024-10-31',78071400.00,0.00,76660200.00,0.00,0.00,1411200.00,'2026-06-17 17:22:56','pdf_import'),(3,NULL,7,2568,155647500.00,'2024-11-29',78071400.00,0.00,78071400.00,0.00,0.00,0.00,'2026-06-17 17:22:56','pdf_import'),(4,NULL,7,2568,155647500.00,'2024-12-23',78071400.00,0.00,78071400.00,0.00,0.00,0.00,'2026-06-17 17:22:56','pdf_import'),(5,NULL,7,2568,155647500.00,'2025-01-13',78071400.00,0.00,78071400.00,0.00,0.00,0.00,'2026-06-17 17:22:57','pdf_import'),(6,NULL,7,2568,155647500.00,'2025-02-10',78071400.00,0.00,78071400.00,0.00,0.00,0.00,'2026-06-17 17:22:57','pdf_import'),(7,NULL,7,2568,155647500.00,'2025-02-24',78071400.00,0.00,78071400.00,0.00,0.00,0.00,'2026-06-17 17:22:57','pdf_import'),(8,NULL,7,2568,155647500.00,'2025-05-30',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:58','pdf_import'),(9,NULL,7,2568,155647500.00,'2025-07-14',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:58','pdf_import'),(10,NULL,7,2568,155647500.00,'2025-07-21',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:58','pdf_import'),(11,NULL,7,2568,155647500.00,'2025-07-31',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:58','pdf_import'),(12,NULL,7,2568,155647500.00,'2025-08-13',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:58','pdf_import'),(13,NULL,7,2568,155647500.00,'2025-08-18',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:58','pdf_import'),(14,NULL,7,2568,155647500.00,'2025-08-25',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:58','pdf_import'),(15,NULL,7,2568,155647500.00,'2025-08-29',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:59','pdf_import'),(16,NULL,7,2568,155647500.00,'2025-09-15',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:59','pdf_import'),(17,NULL,7,2568,155647500.00,'2025-09-22',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:59','pdf_import'),(18,NULL,7,2568,155647500.00,'2025-09-30',155647500.00,0.00,155647500.00,0.00,0.00,0.00,'2026-06-17 17:22:59','pdf_import'),(19,NULL,7,2569,155647500.00,'2025-10-31',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:22:59','pdf_import'),(20,NULL,7,2569,155647500.00,'2025-11-10',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:22:59','pdf_import'),(21,NULL,7,2569,155647500.00,'2025-11-17',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:22:59','pdf_import'),(22,NULL,7,2569,155647500.00,'2025-11-24',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:23:00','pdf_import'),(23,NULL,7,2569,155647500.00,'2025-11-28',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:23:00','pdf_import'),(24,NULL,7,2569,155647500.00,'2025-12-22',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:23:00','pdf_import'),(25,NULL,7,2569,155647500.00,'2025-12-30',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:23:00','pdf_import'),(26,NULL,7,2569,155647500.00,'2026-01-12',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:23:00','pdf_import'),(27,NULL,7,2569,155647500.00,'2026-01-26',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:23:00','pdf_import'),(28,NULL,7,2569,155647500.00,'2026-02-23',77810700.00,0.00,77810700.00,0.00,0.00,0.00,'2026-06-17 17:23:00','pdf_import'),(29,NULL,7,2569,155647500.00,'2026-05-25',116735600.00,0.00,116735600.00,0.00,0.00,0.00,'2026-06-17 17:23:01','pdf_import'),(30,NULL,7,2569,155647500.00,'2026-05-29',116735600.00,0.00,116735600.00,0.00,0.00,0.00,'2026-06-17 17:23:01','pdf_import'),(31,NULL,24,2568,1022300.00,'2024-10-28',766600.00,0.00,1200.00,0.00,0.00,765400.00,'2026-06-17 17:23:01','pdf_import'),(32,NULL,24,2568,1022300.00,'2024-10-31',766600.00,0.00,1200.00,0.00,0.00,765400.00,'2026-06-17 17:23:01','pdf_import'),(33,NULL,24,2568,1022300.00,'2024-11-29',766600.00,0.00,21145.00,150000.00,0.00,595455.00,'2026-06-17 17:23:01','pdf_import'),(34,NULL,24,2568,1022300.00,'2024-12-23',766600.00,0.00,24520.00,0.00,150000.00,592080.00,'2026-06-17 17:23:02','pdf_import'),(35,NULL,24,2568,1022300.00,'2025-01-13',766600.00,0.00,174345.00,0.00,0.00,592255.00,'2026-06-17 17:23:02','pdf_import'),(36,NULL,24,2568,1022300.00,'2025-02-10',766600.00,0.00,182280.00,0.00,0.00,584320.00,'2026-06-17 17:23:02','pdf_import'),(37,NULL,24,2568,1022300.00,'2025-02-24',766600.00,0.00,208680.00,19600.00,0.00,538320.00,'2026-06-17 17:23:02','pdf_import'),(38,NULL,24,2568,1022300.00,'2025-05-30',1022300.00,0.00,758865.00,0.00,0.00,263435.00,'2026-06-17 17:23:02','pdf_import'),(39,NULL,24,2568,1022300.00,'2025-07-14',1022300.00,0.00,740212.00,0.00,0.00,282088.00,'2026-06-17 17:23:02','pdf_import'),(40,NULL,24,2568,1022300.00,'2025-07-21',1022300.00,0.00,743812.00,0.00,0.00,278488.00,'2026-06-17 17:23:03','pdf_import'),(41,NULL,24,2568,1022300.00,'2025-07-31',1022300.00,0.00,749087.00,0.00,0.00,273213.00,'2026-06-17 17:23:03','pdf_import'),(42,NULL,24,2568,1022300.00,'2025-08-13',1022300.00,0.00,785787.00,0.00,0.00,236513.00,'2026-06-17 17:23:03','pdf_import'),(43,NULL,24,2568,1022300.00,'2025-08-18',1022300.00,0.00,785787.00,0.00,0.00,236513.00,'2026-06-17 17:23:03','pdf_import'),(44,NULL,24,2568,1022300.00,'2025-08-25',1022300.00,0.00,788467.00,0.00,0.00,233833.00,'2026-06-17 17:23:03','pdf_import'),(45,NULL,24,2568,1022300.00,'2025-08-29',1022300.00,0.00,788467.00,115065.00,0.00,118768.00,'2026-06-17 17:23:03','pdf_import'),(46,NULL,24,2568,1022300.00,'2025-09-15',1022300.00,0.00,803452.00,112157.50,106572.00,118.50,'2026-06-17 17:23:04','pdf_import'),(47,NULL,24,2568,1022300.00,'2025-09-22',1022300.00,0.00,815297.00,92287.50,114597.00,118.50,'2026-06-17 17:23:04','pdf_import'),(48,NULL,24,2568,1022300.00,'2025-09-30',1022300.00,0.00,915609.50,0.00,106572.00,118.50,'2026-06-17 17:23:04','pdf_import'),(49,NULL,24,2569,879500.00,'2025-10-31',439700.00,0.00,665.00,0.00,0.00,439035.00,'2026-06-17 17:23:04','pdf_import'),(50,NULL,24,2569,879500.00,'2025-11-10',439700.00,0.00,665.00,0.00,0.00,439035.00,'2026-06-17 17:23:04','pdf_import'),(51,NULL,24,2569,879500.00,'2025-11-17',439700.00,0.00,665.00,196345.00,0.00,242690.00,'2026-06-17 17:23:04','pdf_import'),(52,NULL,24,2569,879500.00,'2025-11-24',439700.00,0.00,665.00,700.00,196345.00,241990.00,'2026-06-17 17:23:04','pdf_import'),(53,NULL,24,2569,879500.00,'2025-11-28',439700.00,0.00,1365.00,16810.00,196345.00,225180.00,'2026-06-17 17:23:05','pdf_import'),(54,NULL,24,2569,879500.00,'2025-12-22',439700.00,0.00,39825.00,0.00,196345.00,203530.00,'2026-06-17 17:23:05','pdf_import'),(55,NULL,24,2569,879500.00,'2025-12-30',439700.00,0.00,43725.00,175.00,196345.00,199455.00,'2026-06-17 17:23:05','pdf_import'),(56,NULL,24,2569,879500.00,'2026-01-12',439700.00,0.00,248645.00,0.00,0.00,191055.00,'2026-06-17 17:23:05','pdf_import'),(57,NULL,24,2569,879500.00,'2026-01-26',439700.00,0.00,248575.00,0.00,0.00,191125.00,'2026-06-17 17:23:05','pdf_import'),(58,NULL,24,2569,879500.00,'2026-02-23',439700.00,0.00,317175.00,0.00,0.00,122525.00,'2026-06-17 17:23:05','pdf_import'),(59,NULL,24,2569,879500.00,'2026-05-25',659700.00,0.00,435665.00,50050.00,0.00,173985.00,'2026-06-17 17:23:05','pdf_import'),(60,NULL,24,2569,879500.00,'2026-05-29',659700.00,0.00,485715.00,0.00,0.00,173985.00,'2026-06-17 17:23:06','pdf_import'),(61,NULL,17,2568,613000.00,'2024-10-28',613000.00,0.00,0.00,0.00,0.00,613000.00,'2026-06-17 17:23:06','pdf_import'),(62,NULL,17,2568,613000.00,'2024-10-31',613000.00,0.00,0.00,0.00,0.00,613000.00,'2026-06-17 17:23:06','pdf_import'),(63,NULL,17,2568,613000.00,'2024-11-29',613000.00,0.00,66000.00,0.00,0.00,547000.00,'2026-06-17 17:23:06','pdf_import'),(64,NULL,17,2568,613000.00,'2024-12-23',613000.00,0.00,132000.00,0.00,0.00,481000.00,'2026-06-17 17:23:06','pdf_import'),(65,NULL,17,2568,613000.00,'2025-01-13',613000.00,0.00,132000.00,0.00,0.00,481000.00,'2026-06-17 17:23:06','pdf_import'),(66,NULL,17,2568,613000.00,'2025-02-10',613000.00,0.00,184640.00,0.00,0.00,428360.00,'2026-06-17 17:23:06','pdf_import'),(67,NULL,17,2568,613000.00,'2025-02-24',613000.00,0.00,246468.00,0.00,0.00,366532.00,'2026-06-17 17:23:07','pdf_import'),(68,NULL,17,2568,613000.00,'2025-05-30',613000.00,37300.00,444817.00,0.00,0.00,205483.00,'2026-06-17 17:23:07','pdf_import'),(69,NULL,17,2568,613000.00,'2025-07-14',613000.00,37300.00,449571.00,0.00,0.00,200729.00,'2026-06-17 17:23:07','pdf_import'),(70,NULL,17,2568,613000.00,'2025-07-21',613000.00,37300.00,449571.00,0.00,0.00,200729.00,'2026-06-17 17:23:07','pdf_import'),(71,NULL,17,2568,613000.00,'2025-07-31',613000.00,37300.00,449571.00,0.00,0.00,200729.00,'2026-06-17 17:23:07','pdf_import'),(72,NULL,17,2568,613000.00,'2025-08-13',613000.00,37300.00,515571.00,0.00,0.00,134729.00,'2026-06-17 17:23:07','pdf_import'),(73,NULL,17,2568,613000.00,'2025-08-18',613000.00,37300.00,542051.00,0.00,0.00,108249.00,'2026-06-17 17:23:07','pdf_import'),(74,NULL,17,2568,613000.00,'2025-08-25',613000.00,-1949.00,542051.00,0.00,0.00,69000.00,'2026-06-17 17:23:08','pdf_import'),(75,NULL,17,2568,613000.00,'2025-08-29',613000.00,-1949.00,542051.00,0.00,0.00,69000.00,'2026-06-17 17:23:08','pdf_import'),(76,NULL,17,2568,613000.00,'2025-09-15',613000.00,-2504.00,609556.00,0.00,0.00,940.00,'2026-06-17 17:23:08','pdf_import'),(77,NULL,17,2568,613000.00,'2025-09-22',613000.00,-2504.00,609556.00,0.00,0.00,940.00,'2026-06-17 17:23:08','pdf_import'),(78,NULL,17,2568,613000.00,'2025-09-30',613000.00,-2534.00,610466.00,0.00,0.00,0.00,'2026-06-17 17:23:08','pdf_import'),(79,NULL,17,2569,582000.00,'2025-10-31',346000.00,0.00,0.00,0.00,0.00,346000.00,'2026-06-17 17:23:08','pdf_import'),(80,NULL,17,2569,582000.00,'2025-11-10',346000.00,0.00,66700.00,0.00,0.00,279300.00,'2026-06-17 17:23:09','pdf_import'),(81,NULL,17,2569,582000.00,'2025-11-17',346000.00,0.00,66700.00,0.00,0.00,279300.00,'2026-06-17 17:23:09','pdf_import'),(82,NULL,17,2569,582000.00,'2025-11-24',346000.00,0.00,66700.00,0.00,0.00,279300.00,'2026-06-17 17:23:09','pdf_import'),(83,NULL,17,2569,582000.00,'2025-11-28',346000.00,0.00,66525.00,0.00,0.00,279475.00,'2026-06-17 17:23:09','pdf_import'),(84,NULL,17,2569,582000.00,'2025-12-22',346000.00,0.00,133400.00,0.00,0.00,212600.00,'2026-06-17 17:23:09','pdf_import'),(85,NULL,17,2569,582000.00,'2025-12-30',346000.00,0.00,133400.00,0.00,0.00,212600.00,'2026-06-17 17:23:09','pdf_import'),(86,NULL,17,2569,582000.00,'2026-01-12',346000.00,0.00,133400.00,0.00,0.00,212600.00,'2026-06-17 17:23:10','pdf_import'),(87,NULL,17,2569,582000.00,'2026-01-26',346000.00,0.00,160440.00,0.00,0.00,185560.00,'2026-06-17 17:23:10','pdf_import'),(88,NULL,17,2569,582000.00,'2026-02-23',346000.00,0.00,181360.00,0.00,0.00,164640.00,'2026-06-17 17:23:10','pdf_import'),(89,NULL,17,2569,582000.00,'2026-05-25',478000.00,120215.00,400241.87,0.00,0.00,197973.13,'2026-06-17 17:23:10','pdf_import'),(90,NULL,17,2569,582000.00,'2026-05-29',478000.00,120215.00,400241.87,0.00,0.00,197973.13,'2026-06-17 17:23:10','pdf_import'),(91,NULL,15,2568,1858800.00,'2024-10-28',1858800.00,0.00,14200.00,2000.00,0.00,1842600.00,'2026-06-17 17:23:10','pdf_import'),(92,NULL,15,2568,1858800.00,'2024-10-31',1858800.00,0.00,16200.00,0.00,371076.00,1471524.00,'2026-06-17 17:23:11','pdf_import'),(93,NULL,15,2568,1858800.00,'2024-11-29',1858800.00,0.00,64498.00,0.00,340153.00,1454149.00,'2026-06-17 17:23:11','pdf_import'),(94,NULL,15,2568,1858800.00,'2024-12-23',1858800.00,0.00,122031.00,0.00,309230.00,1427539.00,'2026-06-17 17:23:11','pdf_import'),(95,NULL,15,2568,1858800.00,'2025-01-13',1858800.00,0.00,134593.00,31750.00,309230.00,1383227.00,'2026-06-17 17:23:11','pdf_import'),(96,NULL,15,2568,1858800.00,'2025-02-10',1858800.00,-20204.00,193880.00,0.00,290307.00,1354409.00,'2026-06-17 17:23:11','pdf_import'),(97,NULL,15,2568,1858800.00,'2025-02-24',1858800.00,-20204.00,237338.00,0.00,247384.00,1353874.00,'2026-06-17 17:23:12','pdf_import'),(98,NULL,15,2568,1858800.00,'2025-05-30',1858800.00,476023.50,947319.50,29180.00,162319.00,1196005.00,'2026-06-17 17:23:12','pdf_import'),(99,NULL,15,2568,1858800.00,'2025-07-14',1858800.00,226023.50,1048491.50,1600.00,631396.00,403336.00,'2026-06-17 17:23:12','pdf_import'),(100,NULL,15,2568,1858800.00,'2025-07-21',1858800.00,226023.50,1080339.50,3600.00,600473.00,400411.00,'2026-06-17 17:23:12','pdf_import'),(101,NULL,15,2568,1858800.00,'2025-07-31',1858800.00,226023.50,1085189.50,8500.00,626153.00,364981.00,'2026-06-17 17:23:12','pdf_import'),(102,NULL,15,2568,1858800.00,'2025-08-13',1858800.00,226023.50,1188965.50,8500.00,526153.00,361205.00,'2026-06-17 17:23:13','pdf_import'),(103,NULL,15,2568,1858800.00,'2025-08-18',1858800.00,226023.50,1221888.50,7090.00,495230.00,360615.00,'2026-06-17 17:23:13','pdf_import'),(104,NULL,15,2568,1858800.00,'2025-08-25',1858800.00,198381.50,1295241.50,1600.00,469550.00,290790.00,'2026-06-17 17:23:13','pdf_import'),(105,NULL,15,2568,1858800.00,'2025-08-29',1858800.00,-13352.50,1310026.50,19022.00,469550.00,46849.00,'2026-06-17 17:23:13','pdf_import'),(106,NULL,15,2568,1858800.00,'2025-09-15',1858800.00,664854.50,1381418.50,4000.00,430923.00,707313.00,'2026-06-17 17:23:13','pdf_import'),(107,NULL,15,2568,1858800.00,'2025-09-22',1858800.00,664854.50,1781418.50,703000.00,30923.00,8313.00,'2026-06-17 17:23:13','pdf_import'),(108,NULL,15,2568,1858800.00,'2025-09-30',1858800.00,664854.50,1787698.50,0.00,729813.00,6143.00,'2026-06-17 17:23:14','pdf_import'),(109,NULL,15,2569,2247000.00,'2025-10-31',1935000.00,0.00,8218.00,501676.00,428856.00,996250.00,'2026-06-17 17:23:14','pdf_import'),(110,NULL,15,2569,2247000.00,'2025-11-10',1935000.00,265000.00,9894.00,500000.00,428856.00,1261250.00,'2026-06-17 17:23:14','pdf_import'),(111,NULL,15,2569,2247000.00,'2025-11-17',1935000.00,265000.00,25631.00,8000.00,928856.00,1237513.00,'2026-06-17 17:23:14','pdf_import'),(112,NULL,15,2569,2247000.00,'2025-11-24',1935000.00,265000.00,93304.00,8000.00,893118.00,1205578.00,'2026-06-17 17:23:14','pdf_import'),(113,NULL,15,2569,2247000.00,'2025-11-28',1935000.00,265000.00,106694.00,10000.00,893118.00,1190188.00,'2026-06-17 17:23:15','pdf_import'),(114,NULL,15,2569,2247000.00,'2025-12-22',1935000.00,265000.00,253836.00,0.00,907318.00,1038846.00,'2026-06-17 17:23:15','pdf_import'),(115,NULL,15,2569,2247000.00,'2025-12-30',1935000.00,265000.00,363950.00,4120.00,807380.00,1024550.00,'2026-06-17 17:23:15','pdf_import'),(116,NULL,15,2569,2247000.00,'2026-01-12',1935000.00,265000.00,371336.00,2000.00,807380.00,1019284.00,'2026-06-17 17:23:15','pdf_import'),(117,NULL,15,2569,2247000.00,'2026-01-26',1935000.00,265000.00,458504.00,101938.00,721642.00,917916.00,'2026-06-17 17:23:15','pdf_import'),(118,NULL,15,2569,2247000.00,'2026-02-23',1935000.00,265000.00,647560.00,0.00,635904.00,916536.00,'2026-06-17 17:23:15','pdf_import'),(119,NULL,15,2569,2247000.00,'2026-05-25',2247000.00,478286.00,1509607.60,4899.00,434040.00,776739.40,'2026-06-17 17:23:16','pdf_import'),(120,NULL,15,2569,2247000.00,'2026-05-29',2247000.00,478286.00,1562706.60,8750.00,416540.00,737289.40,'2026-06-17 17:23:16','pdf_import'),(121,NULL,20,2568,157682800.00,'2024-10-28',157682800.00,0.00,840.00,11980.00,90434000.00,67235980.00,'2026-06-17 17:23:16','pdf_import'),(122,NULL,20,2568,157682800.00,'2024-10-31',157682800.00,0.00,12820.00,0.00,90434000.00,67235980.00,'2026-06-17 17:23:16','pdf_import'),(123,NULL,20,2568,157682800.00,'2024-11-29',157682800.00,-72000.00,2885056.15,0.00,87590841.85,67134902.00,'2026-06-17 17:23:16','pdf_import'),(124,NULL,20,2568,157682800.00,'2024-12-23',157682800.00,-72000.00,8169550.12,11400.00,82350763.88,67079086.00,'2026-06-17 17:23:16','pdf_import'),(125,NULL,20,2568,157682800.00,'2025-01-13',157682800.00,-72000.00,8180950.12,0.00,82350763.88,67079086.00,'2026-06-17 17:23:17','pdf_import'),(126,NULL,20,2568,157682800.00,'2025-02-10',157682800.00,-72000.00,19156113.46,53240.00,71409116.54,66992330.00,'2026-06-17 17:23:17','pdf_import'),(127,NULL,20,2568,157682800.00,'2025-02-24',157682800.00,-115880.00,19182553.46,43880.00,71409116.54,66931370.00,'2026-06-17 17:23:17','pdf_import'),(128,NULL,20,2568,157682800.00,'2025-05-30',157682800.00,-88100.00,35015498.95,0.00,55683913.05,66895288.00,'2026-06-17 17:23:17','pdf_import'),(129,NULL,20,2568,157682800.00,'2025-07-14',157682800.00,-88100.00,40172463.00,0.00,50655600.00,66766637.00,'2026-06-17 17:23:17','pdf_import'),(130,NULL,20,2568,157682800.00,'2025-07-21',157682800.00,-88100.00,40159992.00,0.00,50655600.00,66779108.00,'2026-06-17 17:23:17','pdf_import'),(131,NULL,20,2568,157682800.00,'2025-07-31',157682800.00,-88100.00,40275070.00,0.00,50565200.00,66754430.00,'2026-06-17 17:23:17','pdf_import'),(132,NULL,20,2568,157682800.00,'2025-08-13',157682800.00,-88100.00,40275070.00,0.00,50565200.00,66754430.00,'2026-06-17 17:23:17','pdf_import'),(133,NULL,20,2568,157682800.00,'2025-08-18',157682800.00,-88100.00,40281582.00,0.00,50565200.00,66747918.00,'2026-06-17 17:23:18','pdf_import'),(134,NULL,20,2568,157682800.00,'2025-08-25',157682800.00,-88100.00,40288182.00,0.00,50565200.00,66741318.00,'2026-06-17 17:23:18','pdf_import'),(135,NULL,20,2568,157682800.00,'2025-08-29',157682800.00,-119300.00,40288182.00,300.00,71862100.00,45412918.00,'2026-06-17 17:23:18','pdf_import'),(136,NULL,20,2568,157682800.00,'2025-09-15',157682800.00,-105200.00,48291782.00,0.00,63869600.00,45416218.00,'2026-06-17 17:23:18','pdf_import'),(137,NULL,20,2568,157682800.00,'2025-09-22',157682800.00,-105200.00,48299032.00,0.00,28733300.00,80545268.00,'2026-06-17 17:23:18','pdf_import'),(138,NULL,20,2568,157682800.00,'2025-09-30',157682800.00,-106830.00,62418894.90,0.00,95153637.10,3438.00,'2026-06-17 17:23:18','pdf_import'),(139,NULL,20,2569,232419700.00,'2025-10-31',204312200.00,0.00,3256.00,300.00,0.00,204308644.00,'2026-06-17 17:23:18','pdf_import'),(140,NULL,20,2569,232419700.00,'2025-11-10',204312200.00,0.00,11160889.63,0.00,76680088.33,116471222.04,'2026-06-17 17:23:18','pdf_import'),(141,NULL,20,2569,232419700.00,'2025-11-17',204312200.00,0.00,11160889.63,0.00,76680088.33,116471222.04,'2026-06-17 17:23:19','pdf_import'),(142,NULL,20,2569,232419700.00,'2025-11-24',204312200.00,0.00,19992287.66,0.00,67848690.30,116471222.04,'2026-06-17 17:23:19','pdf_import'),(143,NULL,20,2569,232419700.00,'2025-11-28',204312200.00,0.00,20299187.66,0.00,67541790.30,116471222.04,'2026-06-17 17:23:19','pdf_import'),(144,NULL,20,2569,232419700.00,'2025-12-22',204312200.00,0.00,20299787.66,0.00,81616653.20,102395759.14,'2026-06-17 17:23:19','pdf_import'),(145,NULL,20,2569,232419700.00,'2025-12-30',204312200.00,0.00,25693295.44,12600.00,76295739.42,102310565.14,'2026-06-17 17:23:19','pdf_import'),(146,NULL,20,2569,232419700.00,'2026-01-12',204312200.00,0.00,25705895.44,0.00,76295739.42,102310565.14,'2026-06-17 17:23:19','pdf_import'),(147,NULL,20,2569,232419700.00,'2026-01-26',204312200.00,0.00,25904195.44,0.00,76097739.42,102310265.14,'2026-06-17 17:23:19','pdf_import'),(148,NULL,20,2569,232419700.00,'2026-02-23',204312200.00,0.00,34374235.44,0.00,67652889.42,102285075.14,'2026-06-17 17:23:20','pdf_import'),(149,NULL,20,2569,232419700.00,'2026-05-25',204312200.00,50000.00,34435602.44,0.00,69647889.42,100278708.14,'2026-06-17 17:23:20','pdf_import'),(150,NULL,20,2569,232419700.00,'2026-05-29',204312200.00,50000.00,34446982.44,0.00,69647889.42,100267328.14,'2026-06-17 17:23:20','pdf_import'),(151,NULL,22,2568,296300.00,'2024-10-28',296300.00,0.00,1275.00,0.00,0.00,295025.00,'2026-06-17 17:23:20','pdf_import'),(152,NULL,22,2568,296300.00,'2024-10-31',296300.00,0.00,1275.00,0.00,0.00,295025.00,'2026-06-17 17:23:20','pdf_import'),(153,NULL,22,2568,296300.00,'2024-11-29',296300.00,0.00,38712.00,0.00,0.00,257588.00,'2026-06-17 17:23:20','pdf_import'),(154,NULL,22,2568,296300.00,'2024-12-23',296300.00,0.00,47972.00,8250.00,0.00,240078.00,'2026-06-17 17:23:21','pdf_import'),(155,NULL,22,2568,296300.00,'2025-01-13',296300.00,0.00,67407.00,0.00,0.00,228893.00,'2026-06-17 17:23:21','pdf_import'),(156,NULL,22,2568,296300.00,'2025-02-10',296300.00,0.00,109070.00,9600.00,0.00,177630.00,'2026-06-17 17:23:21','pdf_import'),(157,NULL,22,2568,296300.00,'2025-02-24',296300.00,0.00,118670.00,0.00,0.00,177630.00,'2026-06-17 17:23:21','pdf_import'),(158,NULL,22,2568,296300.00,'2025-05-30',296300.00,164900.00,279420.00,8070.00,0.00,173710.00,'2026-06-17 17:23:21','pdf_import'),(159,NULL,22,2568,296300.00,'2025-07-14',296300.00,164900.00,322734.00,16500.00,0.00,121966.00,'2026-06-17 17:23:22','pdf_import'),(160,NULL,22,2568,296300.00,'2025-07-21',296300.00,164900.00,322606.30,16500.00,0.00,122093.70,'2026-06-17 17:23:22','pdf_import'),(161,NULL,22,2568,296300.00,'2025-07-31',296300.00,164900.00,351331.30,3200.00,0.00,106668.70,'2026-06-17 17:23:22','pdf_import'),(162,NULL,22,2568,296300.00,'2025-08-13',296300.00,164900.00,354531.30,0.00,0.00,106668.70,'2026-06-17 17:23:22','pdf_import'),(163,NULL,22,2568,296300.00,'2025-08-18',296300.00,164900.00,354531.30,0.00,0.00,106668.70,'2026-06-17 17:23:22','pdf_import'),(164,NULL,22,2568,296300.00,'2025-08-25',296300.00,164900.00,368151.30,0.00,0.00,93048.70,'2026-06-17 17:23:22','pdf_import'),(165,NULL,22,2568,296300.00,'2025-08-29',296300.00,210477.00,368151.30,6350.00,0.00,132275.70,'2026-06-17 17:23:23','pdf_import'),(166,NULL,22,2568,296300.00,'2025-09-15',296300.00,192115.30,418935.30,9740.00,0.00,59740.00,'2026-06-17 17:23:23','pdf_import'),(167,NULL,22,2568,296300.00,'2025-09-22',296300.00,192115.30,456575.30,0.00,0.00,31840.00,'2026-06-17 17:23:23','pdf_import'),(168,NULL,22,2568,296300.00,'2025-09-30',296300.00,236245.30,532545.30,0.00,0.00,0.00,'2026-06-17 17:23:23','pdf_import'),(169,NULL,22,2569,376000.00,'2025-10-31',326000.00,0.00,9196.00,39500.00,0.00,277304.00,'2026-06-17 17:23:23','pdf_import'),(170,NULL,22,2569,376000.00,'2025-11-10',326000.00,0.00,9196.00,39500.00,0.00,277304.00,'2026-06-17 17:23:23','pdf_import'),(171,NULL,22,2569,376000.00,'2025-11-17',326000.00,0.00,27156.00,61340.00,0.00,237504.00,'2026-06-17 17:23:23','pdf_import'),(172,NULL,22,2569,376000.00,'2025-11-24',326000.00,0.00,51216.00,39500.00,0.00,235284.00,'2026-06-17 17:23:24','pdf_import'),(173,NULL,22,2569,376000.00,'2025-11-28',326000.00,0.00,51152.00,39500.00,0.00,235348.00,'2026-06-17 17:23:24','pdf_import'),(174,NULL,22,2569,376000.00,'2025-12-22',326000.00,0.00,79032.00,39500.00,0.00,207468.00,'2026-06-17 17:23:24','pdf_import'),(175,NULL,22,2569,376000.00,'2025-12-30',326000.00,0.00,97422.00,0.00,0.00,228578.00,'2026-06-17 17:23:24','pdf_import'),(176,NULL,22,2569,376000.00,'2026-01-12',326000.00,0.00,96221.00,0.00,0.00,229779.00,'2026-06-17 17:23:24','pdf_import'),(177,NULL,22,2569,376000.00,'2026-01-26',326000.00,0.00,104201.00,0.00,0.00,221799.00,'2026-06-17 17:23:24','pdf_import'),(178,NULL,22,2569,376000.00,'2026-02-23',326000.00,0.00,121641.00,0.00,0.00,204359.00,'2026-06-17 17:23:25','pdf_import'),(179,NULL,22,2569,376000.00,'2026-05-25',376000.00,40000.00,206131.00,13500.00,0.00,196369.00,'2026-06-17 17:23:25','pdf_import'),(180,NULL,22,2569,376000.00,'2026-05-29',376000.00,40000.00,208916.00,10640.00,0.00,196444.00,'2026-06-17 17:23:25','pdf_import'),(181,NULL,41,2568,91764111.00,'2024-10-28',73232716.00,-2745280.00,2198855.77,19833425.25,206953.76,48248201.22,'2026-06-17 17:23:25','pdf_import'),(182,NULL,41,2568,91764111.00,'2024-10-31',73232716.00,-15191887.00,2375753.85,10800425.25,9239953.76,35624696.14,'2026-06-17 17:23:25','pdf_import'),(183,NULL,41,2568,91764111.00,'2024-11-29',73232716.00,-15191887.00,4112724.63,1771135.32,26849525.76,25307443.29,'2026-06-17 17:23:25','pdf_import'),(184,NULL,41,2568,91764111.00,'2024-12-23',73232716.00,-15740887.00,9133364.67,692098.90,24625416.94,23040948.49,'2026-06-17 17:23:26','pdf_import'),(185,NULL,41,2568,91764111.00,'2025-01-13',73232716.00,-15740887.00,13107567.60,348857.08,23699552.85,20335851.47,'2026-06-17 17:23:26','pdf_import'),(186,NULL,41,2568,91764111.00,'2025-02-10',73232716.00,-15681114.58,18557769.13,308860.00,20909795.76,17775176.53,'2026-06-17 17:23:26','pdf_import'),(187,NULL,41,2568,91764111.00,'2025-02-24',73232716.00,-15681114.58,20493285.33,2837832.60,19351038.76,14869444.73,'2026-06-17 17:23:26','pdf_import'),(188,NULL,41,2568,91764111.00,'2025-05-30',91764111.00,-24509013.47,37637109.17,1669339.13,17373770.52,10574878.71,'2026-06-17 17:23:26','pdf_import'),(189,NULL,41,2568,91764111.00,'2025-07-14',91764111.00,-24108860.82,45864796.82,1022190.15,13682411.03,7085852.18,'2026-06-17 17:23:26','pdf_import'),(190,NULL,41,2568,91764111.00,'2025-07-21',91764111.00,-24240560.82,47701671.97,1191280.23,11931766.20,6698831.78,'2026-06-17 17:23:26','pdf_import'),(191,NULL,41,2568,91764111.00,'2025-07-31',91764111.00,-24240560.82,49885716.62,1556816.23,11838560.61,4242456.72,'2026-06-17 17:23:26','pdf_import'),(192,NULL,41,2568,91764111.00,'2025-08-13',91764111.00,-24240560.82,50398073.42,1174582.83,12098035.61,3852858.32,'2026-06-17 17:23:26','pdf_import'),(193,NULL,41,2568,91764111.00,'2025-08-18',91764111.00,-24240560.82,51213776.42,1138526.60,11690460.04,3480787.12,'2026-06-17 17:23:27','pdf_import'),(194,NULL,41,2568,91764111.00,'2025-08-25',91764111.00,-24240560.82,55388863.75,1091752.85,9328693.18,1714240.40,'2026-06-17 17:23:27','pdf_import'),(195,NULL,41,2568,91764111.00,'2025-08-29',91764111.00,-24148787.40,55905215.75,1119346.01,8841950.18,1748811.66,'2026-06-17 17:23:27','pdf_import'),(196,NULL,41,2568,91764111.00,'2025-09-15',91764111.00,-23121504.90,57709340.24,2224382.71,7886867.05,822016.10,'2026-06-17 17:23:27','pdf_import'),(197,NULL,41,2568,91764111.00,'2025-09-22',91764111.00,-23121504.90,61179576.25,727878.40,6195598.75,539552.70,'2026-06-17 17:23:27','pdf_import'),(198,NULL,41,2568,91764111.00,'2025-09-30',91764111.00,-23068959.94,62001184.10,0.00,6681402.80,12564.16,'2026-06-17 17:23:28','pdf_import'),(199,NULL,41,2569,94037696.00,'2025-10-31',60851505.00,-13442880.00,2213052.72,323183.92,7151930.04,37720458.32,'2026-06-17 17:23:28','pdf_import'),(200,NULL,41,2569,94037696.00,'2025-11-10',60851505.00,-13442880.00,2579585.22,893274.90,20657174.95,23278589.93,'2026-06-17 17:23:28','pdf_import'),(201,NULL,41,2569,94037696.00,'2025-11-17',60851505.00,-13617461.85,4445383.75,1544660.01,29262898.65,11981100.74,'2026-06-17 17:23:28','pdf_import'),(202,NULL,41,2569,94037696.00,'2025-11-24',60851505.00,-13871861.85,5368677.84,1244896.23,28695748.34,11670320.74,'2026-06-17 17:23:28','pdf_import'),(203,NULL,41,2569,94037696.00,'2025-11-28',60851505.00,-13951680.00,6465121.59,1057091.60,27956953.48,11420658.33,'2026-06-17 17:23:29','pdf_import'),(204,NULL,41,2569,94037696.00,'2025-12-22',60851505.00,-13873680.00,9830572.09,1372538.24,26950064.85,8824649.82,'2026-06-17 17:23:29','pdf_import'),(205,NULL,41,2569,94037696.00,'2025-12-30',60851505.00,-14153416.54,13237854.22,937477.38,24076100.02,8446656.84,'2026-06-17 17:23:29','pdf_import'),(206,NULL,41,2569,94037696.00,'2026-01-12',60851505.00,-14153416.54,13861723.30,813397.70,24096801.52,7926165.94,'2026-06-17 17:23:29','pdf_import'),(207,NULL,41,2569,94037696.00,'2026-01-26',60851505.00,-14153416.54,16208970.67,850286.67,22201098.00,7437733.12,'2026-06-17 17:23:29','pdf_import'),(208,NULL,41,2569,94037696.00,'2026-02-23',60851505.00,-14153416.54,22878760.82,450626.40,19438851.45,3929849.79,'2026-06-17 17:23:30','pdf_import'),(209,NULL,41,2569,94037696.00,'2026-05-25',78260496.00,-22949149.39,36159452.20,615972.60,14069034.10,4466887.71,'2026-06-17 17:23:30','pdf_import'),(210,NULL,41,2569,94037696.00,'2026-05-29',78260496.00,-23745865.00,37270821.14,352435.61,12996479.00,3894895.25,'2026-06-17 17:23:30','pdf_import'),(211,NULL,19,2568,247206050.00,'2024-10-28',123864350.00,0.00,19752361.18,18000.00,0.00,104093988.82,'2026-06-17 17:23:30','pdf_import'),(212,NULL,19,2568,247206050.00,'2024-10-31',123864350.00,0.00,19713247.79,18000.00,0.00,104133102.21,'2026-06-17 17:23:30','pdf_import'),(213,NULL,19,2568,247206050.00,'2024-11-29',123864350.00,0.00,39487157.94,12630.00,0.00,84364562.06,'2026-06-17 17:23:30','pdf_import'),(214,NULL,19,2568,247206050.00,'2024-12-23',123864350.00,0.00,60672683.44,0.00,0.00,63191666.56,'2026-06-17 17:23:31','pdf_import'),(215,NULL,19,2568,247206050.00,'2025-01-13',123864350.00,0.00,76747924.08,2320.00,0.00,47114105.92,'2026-06-17 17:23:31','pdf_import'),(216,NULL,19,2568,247206050.00,'2025-02-10',123864350.00,-5750.00,97352778.45,17820.00,0.00,26488001.55,'2026-06-17 17:23:31','pdf_import'),(217,NULL,19,2568,247206050.00,'2025-02-24',123864350.00,-5750.00,102300032.73,0.00,0.00,21558567.27,'2026-06-17 17:23:31','pdf_import'),(218,NULL,19,2568,247206050.00,'2025-05-30',247206050.00,116760.00,163863991.68,0.00,0.00,83458818.32,'2026-06-17 17:23:31','pdf_import'),(219,NULL,19,2568,247206050.00,'2025-07-14',247206050.00,116760.00,206082984.77,0.00,0.00,41239825.23,'2026-06-17 17:23:32','pdf_import'),(220,NULL,19,2568,247206050.00,'2025-07-21',247206050.00,116760.00,206086331.71,0.00,0.00,41236478.29,'2026-06-17 17:23:32','pdf_import'),(221,NULL,19,2568,247206050.00,'2025-07-31',247206050.00,116760.00,206290351.71,0.00,0.00,41032458.29,'2026-06-17 17:23:32','pdf_import'),(222,NULL,19,2568,247206050.00,'2025-08-13',247206050.00,116760.00,222185348.22,0.00,0.00,25137461.78,'2026-06-17 17:23:32','pdf_import'),(223,NULL,19,2568,247206050.00,'2025-08-18',247206050.00,116760.00,227075195.56,360.00,0.00,20247254.44,'2026-06-17 17:23:32','pdf_import'),(224,NULL,19,2568,247206050.00,'2025-08-25',247206050.00,133820.00,227100904.91,0.00,0.00,20238965.09,'2026-06-17 17:23:33','pdf_import'),(225,NULL,19,2568,247206050.00,'2025-08-29',247206050.00,-41765.00,227377499.80,0.00,0.00,19786785.20,'2026-06-17 17:23:33','pdf_import'),(226,NULL,19,2568,247206050.00,'2025-09-15',247206050.00,-55248.60,250791729.02,38860.00,0.00,284390.00,'2026-06-17 17:23:33','pdf_import'),(227,NULL,19,2568,247206050.00,'2025-09-22',247206050.00,-55248.60,250792629.02,38860.00,0.00,283490.00,'2026-06-17 17:23:33','pdf_import'),(228,NULL,19,2568,247206050.00,'2025-09-30',247206050.00,-55248.60,251124352.42,0.00,0.00,18910.00,'2026-06-17 17:23:33','pdf_import'),(229,NULL,19,2569,246230285.00,'2025-10-31',123325485.00,0.00,22893772.15,0.00,0.00,100431712.85,'2026-06-17 17:23:33','pdf_import'),(230,NULL,19,2569,246230285.00,'2025-11-10',123325485.00,0.00,38743215.98,0.00,0.00,84582269.02,'2026-06-17 17:23:33','pdf_import'),(231,NULL,19,2569,246230285.00,'2025-11-17',123325485.00,0.00,43962485.34,1000.00,0.00,79361999.66,'2026-06-17 17:23:33','pdf_import'),(232,NULL,19,2569,246230285.00,'2025-11-24',123325485.00,0.00,43939334.69,0.00,0.00,79386150.31,'2026-06-17 17:23:34','pdf_import'),(233,NULL,19,2569,246230285.00,'2025-11-28',123325485.00,0.00,43939619.69,1120.00,0.00,79384745.31,'2026-06-17 17:23:34','pdf_import'),(234,NULL,19,2569,246230285.00,'2025-12-22',123325485.00,0.00,66643177.36,0.00,0.00,56682307.64,'2026-06-17 17:23:34','pdf_import'),(235,NULL,19,2569,246230285.00,'2025-12-30',123325485.00,0.00,67334697.36,0.00,0.00,55990787.64,'2026-06-17 17:23:34','pdf_import'),(236,NULL,19,2569,246230285.00,'2026-01-12',123325485.00,0.00,83330086.76,0.00,0.00,39995398.24,'2026-06-17 17:23:34','pdf_import'),(237,NULL,19,2569,246230285.00,'2026-01-26',123325485.00,0.00,88521506.63,0.00,0.00,34803978.37,'2026-06-17 17:23:34','pdf_import'),(238,NULL,19,2569,246230285.00,'2026-02-23',123325485.00,0.00,109957104.65,0.00,0.00,13368380.35,'2026-06-17 17:23:34','pdf_import'),(239,NULL,19,2569,246230285.00,'2026-05-25',246230285.00,211260.00,174146585.07,12905.00,0.00,72282054.93,'2026-06-17 17:23:35','pdf_import'),(240,NULL,19,2569,246230285.00,'2026-05-29',246230285.00,211260.00,174162990.07,9735.00,0.00,72268819.93,'2026-06-17 17:23:35','pdf_import'),(241,NULL,13,2568,2615400.00,'2024-10-28',2365400.00,0.00,575.00,0.00,0.00,2364825.00,'2026-06-17 17:23:36','pdf_import'),(242,NULL,13,2568,2615400.00,'2024-10-31',2365400.00,3200.00,2500.00,0.00,0.00,2366100.00,'2026-06-17 17:23:36','pdf_import'),(243,NULL,13,2568,2615400.00,'2024-11-29',2365400.00,3200.00,173920.00,8220.00,0.00,2186460.00,'2026-06-17 17:23:37','pdf_import'),(244,NULL,13,2568,2615400.00,'2024-12-23',2365400.00,58000.00,186525.00,31500.00,0.00,2205375.00,'2026-06-17 17:23:37','pdf_import'),(245,NULL,13,2568,2615400.00,'2025-01-13',2365400.00,58000.00,1120175.00,31500.00,0.00,1271725.00,'2026-06-17 17:23:37','pdf_import'),(246,NULL,13,2568,2615400.00,'2025-02-10',2365400.00,58000.00,1514376.72,0.00,0.00,909023.28,'2026-06-17 17:23:37','pdf_import'),(247,NULL,13,2568,2615400.00,'2025-02-24',2365400.00,58000.00,1512836.72,33235.00,0.00,877328.28,'2026-06-17 17:23:37','pdf_import'),(248,NULL,13,2568,2615400.00,'2025-05-30',2615400.00,809500.00,2198831.72,3600.00,0.00,1222468.28,'2026-06-17 17:23:37','pdf_import'),(249,NULL,13,2568,2615400.00,'2025-07-14',2615400.00,808990.00,2405431.72,120.00,19000.00,999838.28,'2026-06-17 17:23:38','pdf_import'),(250,NULL,13,2568,2615400.00,'2025-07-21',2615400.00,808990.00,2406201.72,120.00,19000.00,999068.28,'2026-06-17 17:23:38','pdf_import'),(251,NULL,13,2568,2615400.00,'2025-07-31',2615400.00,808990.00,2771966.72,120.00,19000.00,633303.28,'2026-06-17 17:23:38','pdf_import'),(252,NULL,13,2568,2615400.00,'2025-08-13',2615400.00,808990.00,2804166.72,416118.02,0.00,204105.26,'2026-06-17 17:23:38','pdf_import'),(253,NULL,13,2568,2615400.00,'2025-08-18',2615400.00,808990.00,2804166.72,120.00,415998.02,204105.26,'2026-06-17 17:23:38','pdf_import'),(254,NULL,13,2568,2615400.00,'2025-08-25',2615400.00,808990.00,2813141.72,120.00,415998.02,195130.26,'2026-06-17 17:23:38','pdf_import'),(255,NULL,13,2568,2615400.00,'2025-08-29',2615400.00,812150.00,2969596.72,995.00,415998.02,40960.26,'2026-06-17 17:23:39','pdf_import'),(256,NULL,13,2568,2615400.00,'2025-09-15',2615400.00,784738.02,3396069.74,120.00,0.00,3948.28,'2026-06-17 17:23:39','pdf_import'),(257,NULL,13,2568,2615400.00,'2025-09-22',2615400.00,784738.02,3396069.74,0.00,0.00,4068.28,'2026-06-17 17:23:39','pdf_import'),(258,NULL,13,2568,2615400.00,'2025-09-30',2615400.00,784598.02,3398839.74,0.00,0.00,1158.28,'2026-06-17 17:23:39','pdf_import'),(259,NULL,13,2569,1908400.00,'2025-10-31',1079730.00,0.00,2340.00,120.00,0.00,1077270.00,'2026-06-17 17:23:39','pdf_import'),(260,NULL,13,2569,1908400.00,'2025-11-10',1079730.00,0.00,5960.00,0.00,0.00,1073770.00,'2026-06-17 17:23:39','pdf_import'),(261,NULL,13,2569,1908400.00,'2025-11-17',1079730.00,0.00,11040.00,245.00,0.00,1068445.00,'2026-06-17 17:23:40','pdf_import'),(262,NULL,13,2569,1908400.00,'2025-11-24',1079730.00,77000.00,13935.00,0.00,0.00,1142795.00,'2026-06-17 17:23:40','pdf_import'),(263,NULL,13,2569,1908400.00,'2025-11-28',1079730.00,77000.00,56940.00,0.00,0.00,1099790.00,'2026-06-17 17:23:40','pdf_import'),(264,NULL,13,2569,1908400.00,'2025-12-22',1079730.00,82065.00,81260.00,111500.00,0.00,969035.00,'2026-06-17 17:23:40','pdf_import'),(265,NULL,13,2569,1908400.00,'2025-12-30',1079730.00,86565.00,192880.00,0.00,0.00,973415.00,'2026-06-17 17:23:40','pdf_import'),(266,NULL,13,2569,1908400.00,'2026-01-12',1079730.00,86565.00,192880.00,39000.00,0.00,934415.00,'2026-06-17 17:23:40','pdf_import'),(267,NULL,13,2569,1908400.00,'2026-01-26',1079730.00,86565.00,310000.00,30660.00,24000.00,801635.00,'2026-06-17 17:23:40','pdf_import'),(268,NULL,13,2569,1908400.00,'2026-02-23',1079730.00,86565.00,547135.00,1600.00,24000.00,593560.00,'2026-06-17 17:23:41','pdf_import'),(269,NULL,13,2569,1908400.00,'2026-05-25',1619900.00,-72640.00,1231989.00,0.00,0.00,315271.00,'2026-06-17 17:23:41','pdf_import'),(270,NULL,13,2569,1908400.00,'2026-05-29',1619900.00,-72640.00,1231989.00,22010.00,0.00,293261.00,'2026-06-17 17:23:41','pdf_import'),(271,NULL,21,2568,132708300.00,'2024-10-28',97943600.00,0.00,44250.00,90450607.20,0.00,7448742.80,'2026-06-17 17:23:41','pdf_import'),(272,NULL,21,2568,132708300.00,'2024-10-31',97943600.00,0.00,44310.00,88468700.00,1981907.20,7448682.80,'2026-06-17 17:23:41','pdf_import'),(273,NULL,21,2568,132708300.00,'2024-11-29',97943600.00,0.00,395506.00,76128525.00,13984191.01,7435377.99,'2026-06-17 17:23:42','pdf_import'),(274,NULL,21,2568,132708300.00,'2024-12-23',97943600.00,0.00,3069386.20,76127300.00,11343535.81,7403377.99,'2026-06-17 17:23:42','pdf_import'),(275,NULL,21,2568,132708300.00,'2025-01-13',97943600.00,1782.00,3506795.20,80868400.00,10910926.81,2659259.99,'2026-06-17 17:23:42','pdf_import'),(276,NULL,21,2568,132708300.00,'2025-02-10',97943600.00,-30610.80,6805776.20,80867300.00,7692735.81,2547177.19,'2026-06-17 17:23:42','pdf_import'),(277,NULL,21,2568,132708300.00,'2025-02-24',97943600.00,-30610.80,7819527.20,80867300.00,7769044.81,1457117.19,'2026-06-17 17:23:42','pdf_import'),(278,NULL,21,2568,132708300.00,'2025-05-30',132708300.00,-595287.99,14098068.20,0.00,110384708.81,7630235.00,'2026-06-17 17:23:42','pdf_import'),(279,NULL,21,2568,132708300.00,'2025-07-14',132708300.00,-838987.99,58513860.20,1225.00,72572726.81,781500.00,'2026-06-17 17:23:43','pdf_import'),(280,NULL,21,2568,132708300.00,'2025-07-21',132708300.00,-838987.99,83132062.20,1225.00,47973614.81,762410.00,'2026-06-17 17:23:43','pdf_import'),(281,NULL,21,2568,132708300.00,'2025-07-31',132708300.00,-838987.99,93827846.20,0.00,37301205.81,740260.00,'2026-06-17 17:23:43','pdf_import'),(282,NULL,21,2568,132708300.00,'2025-08-13',132708300.00,-838987.99,93958346.20,0.00,37203205.81,707760.00,'2026-06-17 17:23:43','pdf_import'),(283,NULL,21,2568,132708300.00,'2025-08-18',132708300.00,-838987.99,93958346.20,1225.00,37203205.81,706535.00,'2026-06-17 17:23:43','pdf_import'),(284,NULL,21,2568,132708300.00,'2025-08-25',132708300.00,-817537.99,94952262.20,0.00,36210514.81,727985.00,'2026-06-17 17:23:44','pdf_import'),(285,NULL,21,2568,132708300.00,'2025-08-29',132708300.00,-822262.99,105307812.20,43180.00,25860324.81,674720.00,'2026-06-17 17:23:44','pdf_import'),(286,NULL,21,2568,132708300.00,'2025-09-15',132708300.00,-805762.99,112306262.20,0.00,18938144.81,658130.00,'2026-06-17 17:23:44','pdf_import'),(287,NULL,21,2568,132708300.00,'2025-09-22',132708300.00,-805762.99,112333012.20,0.00,18911394.81,658130.00,'2026-06-17 17:23:44','pdf_import'),(288,NULL,21,2568,132708300.00,'2025-09-30',132708300.00,-808952.99,114213918.20,0.00,17677758.81,7670.00,'2026-06-17 17:23:44','pdf_import'),(289,NULL,21,2569,120847800.00,'2025-10-31',74272200.00,0.00,14180.00,0.00,0.00,74258020.00,'2026-06-17 17:23:45','pdf_import'),(290,NULL,21,2569,120847800.00,'2025-11-10',74272200.00,165000.00,14180.00,13188880.00,3360000.00,57874140.00,'2026-06-17 17:23:45','pdf_import'),(291,NULL,21,2569,120847800.00,'2025-11-17',74272200.00,165000.00,38660.00,165000.00,21677680.00,52555860.00,'2026-06-17 17:23:45','pdf_import'),(292,NULL,21,2569,120847800.00,'2025-11-24',74272200.00,165000.00,54820.00,165100.00,21677680.00,52539600.00,'2026-06-17 17:23:45','pdf_import'),(293,NULL,21,2569,120847800.00,'2025-11-28',74272200.00,165000.00,620995.00,199000.00,21117680.00,52499525.00,'2026-06-17 17:23:45','pdf_import'),(294,NULL,21,2569,120847800.00,'2025-12-22',74272200.00,223050.00,813370.00,165000.00,30220056.00,43296824.00,'2026-06-17 17:23:45','pdf_import'),(295,NULL,21,2569,120847800.00,'2025-12-30',74272200.00,-862800.00,32175306.00,1910850.00,35698650.00,31249556.00,'2026-06-17 17:23:46','pdf_import'),(296,NULL,21,2569,120847800.00,'2026-01-12',74272200.00,-7086594.00,3419188.00,165000.00,63153368.00,448050.00,'2026-06-17 17:23:46','pdf_import'),(297,NULL,21,2569,120847800.00,'2026-01-26',74272200.00,-7086594.00,5561637.90,0.00,61249708.10,374260.00,'2026-06-17 17:23:46','pdf_import'),(298,NULL,21,2569,120847800.00,'2026-02-23',74272200.00,-7086594.00,9674812.90,0.00,57162308.10,348485.00,'2026-06-17 17:23:46','pdf_import'),(299,NULL,21,2569,120847800.00,'2026-05-25',105576500.00,-7087259.00,32144770.50,3200.00,65957989.90,383280.60,'2026-06-17 17:23:46','pdf_import'),(300,NULL,21,2569,120847800.00,'2026-05-29',105576500.00,-7087259.00,34016786.10,0.00,69877874.30,0.00,'2026-06-17 17:23:46','pdf_import'),(301,NULL,25,2568,17860800.00,'2024-10-28',9280400.00,0.00,56860.00,2142100.00,2910.40,7078529.60,'2026-06-17 17:23:47','pdf_import'),(302,NULL,25,2568,17860800.00,'2024-10-31',9280400.00,0.00,85790.00,2195940.00,2910.40,6995759.60,'2026-06-17 17:23:47','pdf_import'),(303,NULL,25,2568,17860800.00,'2024-11-29',9280400.00,4650.00,1066753.00,1290003.20,1088410.40,5839883.40,'2026-06-17 17:23:47','pdf_import'),(304,NULL,25,2568,17860800.00,'2024-12-23',9280400.00,4650.00,1475402.20,1115070.00,1707760.40,4986817.40,'2026-06-17 17:23:47','pdf_import'),(305,NULL,25,2568,17860800.00,'2025-01-13',9280400.00,-95765.00,1926615.20,1507570.00,1432410.40,4318039.40,'2026-06-17 17:23:47','pdf_import'),(306,NULL,25,2568,17860800.00,'2025-02-10',9280400.00,-95765.00,3015482.40,1234675.00,1370896.60,3563581.00,'2026-06-17 17:23:48','pdf_import'),(307,NULL,25,2568,17860800.00,'2025-02-24',9280400.00,-95765.00,3152499.40,741750.00,1854681.60,3435704.00,'2026-06-17 17:23:48','pdf_import'),(308,NULL,25,2568,17860800.00,'2025-05-30',17860800.00,54235.00,6447598.18,943980.00,3515649.18,7007807.64,'2026-06-17 17:23:48','pdf_import'),(309,NULL,25,2568,17860800.00,'2025-07-14',17860800.00,-32945.00,7740508.50,1182511.00,3606297.86,5298537.64,'2026-06-17 17:23:48','pdf_import'),(310,NULL,25,2568,17860800.00,'2025-07-21',17860800.00,-32945.00,9001358.50,1109776.00,2527797.86,5188922.64,'2026-06-17 17:23:48','pdf_import'),(311,NULL,25,2568,17860800.00,'2025-07-31',17860800.00,-32945.00,9981920.30,2535542.56,2104434.06,3205958.08,'2026-06-17 17:23:49','pdf_import'),(312,NULL,25,2568,17860800.00,'2025-08-13',17860800.00,-32945.00,10767065.90,2483064.00,2386674.06,2191051.04,'2026-06-17 17:23:49','pdf_import'),(313,NULL,25,2568,17860800.00,'2025-08-18',17860800.00,-32945.00,10925509.40,3049052.00,2502648.56,1350645.04,'2026-06-17 17:23:49','pdf_import'),(314,NULL,25,2568,17860800.00,'2025-08-25',17860800.00,-62869.44,11315142.54,1751514.00,3477039.42,1254234.60,'2026-06-17 17:23:49','pdf_import'),(315,NULL,25,2568,17860800.00,'2025-08-29',17860800.00,-62869.44,11339680.54,2083652.00,3477039.42,897558.60,'2026-06-17 17:23:49','pdf_import'),(316,NULL,25,2568,17860800.00,'2025-09-15',17860800.00,-144181.44,13235550.54,1667304.00,2085879.42,727884.60,'2026-06-17 17:23:49','pdf_import'),(317,NULL,25,2568,17860800.00,'2025-09-22',17860800.00,-144181.44,14380019.96,1420000.00,1352052.00,564546.60,'2026-06-17 17:23:50','pdf_import'),(318,NULL,25,2568,17860800.00,'2025-09-30',17860800.00,-188082.44,15427363.38,0.00,1706324.58,539029.60,'2026-06-17 17:23:50','pdf_import'),(319,NULL,25,2569,17260800.00,'2025-10-31',8680400.00,0.00,27966.00,871210.28,478500.00,7302723.72,'2026-06-17 17:23:50','pdf_import'),(320,NULL,25,2569,17260800.00,'2025-11-10',8680400.00,0.00,51446.00,1267300.00,1131210.28,6230443.72,'2026-06-17 17:23:50','pdf_import'),(321,NULL,25,2569,17260800.00,'2025-11-17',8680400.00,0.00,151446.00,737108.00,1579710.28,6212135.72,'2026-06-17 17:23:50','pdf_import'),(322,NULL,25,2569,17260800.00,'2025-11-24',8680400.00,0.00,691207.28,296941.00,1581200.00,6111051.72,'2026-06-17 17:23:50','pdf_import'),(323,NULL,25,2569,17260800.00,'2025-11-28',8680400.00,0.00,800206.28,350442.00,1581200.00,5948551.72,'2026-06-17 17:23:51','pdf_import'),(324,NULL,25,2569,17260800.00,'2025-12-22',8680400.00,0.00,907837.28,729120.00,1876200.00,5167242.72,'2026-06-17 17:23:51','pdf_import'),(325,NULL,25,2569,17260800.00,'2025-12-30',8680400.00,0.00,1402357.28,575040.00,1592700.00,5110302.72,'2026-06-17 17:23:51','pdf_import'),(326,NULL,25,2569,17260800.00,'2026-01-12',8680400.00,0.00,1681777.28,311820.00,1593000.00,5093802.72,'2026-06-17 17:23:51','pdf_import'),(327,NULL,25,2569,17260800.00,'2026-01-26',8680400.00,0.00,2242154.28,828440.00,1243000.00,4366805.72,'2026-06-17 17:23:51','pdf_import'),(328,NULL,25,2569,17260800.00,'2026-02-23',8680400.00,0.00,3273511.28,504300.00,1015890.00,3886698.72,'2026-06-17 17:23:51','pdf_import'),(329,NULL,25,2569,17260800.00,'2026-05-25',12970600.00,700000.00,6460035.28,145480.00,3009273.00,4055811.72,'2026-06-17 17:23:51','pdf_import'),(330,NULL,25,2569,17260800.00,'2026-05-29',12970600.00,700000.00,6498255.28,1028330.00,3009273.00,3134741.72,'2026-06-17 17:23:52','pdf_import'),(331,NULL,18,2568,407400.00,'2024-10-28',407400.00,0.00,0.00,0.00,0.00,407400.00,'2026-06-17 17:23:52','pdf_import'),(332,NULL,18,2568,407400.00,'2024-10-31',407400.00,0.00,0.00,0.00,0.00,407400.00,'2026-06-17 17:23:52','pdf_import'),(333,NULL,18,2568,407400.00,'2024-11-29',407400.00,0.00,73370.00,0.00,0.00,334030.00,'2026-06-17 17:23:52','pdf_import'),(334,NULL,18,2568,407400.00,'2024-12-23',407400.00,0.00,130719.03,0.00,0.00,276680.97,'2026-06-17 17:23:52','pdf_import'),(335,NULL,18,2568,407400.00,'2025-01-13',407400.00,0.00,214669.03,750.00,0.00,191980.97,'2026-06-17 17:23:52','pdf_import'),(336,NULL,18,2568,407400.00,'2025-02-10',407400.00,75600.00,326014.13,33681.00,0.00,123304.87,'2026-06-17 17:23:53','pdf_import'),(337,NULL,18,2568,407400.00,'2025-02-24',407400.00,119480.00,422985.07,32140.00,0.00,71754.93,'2026-06-17 17:23:53','pdf_import'),(338,NULL,18,2568,407400.00,'2025-05-30',407400.00,267080.00,473733.07,0.00,0.00,200746.93,'2026-06-17 17:23:53','pdf_import'),(339,NULL,18,2568,407400.00,'2025-07-14',407400.00,267080.00,505224.11,0.00,0.00,169255.89,'2026-06-17 17:23:53','pdf_import'),(340,NULL,18,2568,407400.00,'2025-07-21',407400.00,267080.00,549294.11,0.00,0.00,125185.89,'2026-06-17 17:23:53','pdf_import'),(341,NULL,18,2568,407400.00,'2025-07-31',407400.00,267080.00,615393.11,29095.00,0.00,29991.89,'2026-06-17 17:23:53','pdf_import'),(342,NULL,18,2568,407400.00,'2025-08-13',407400.00,267080.00,615393.11,29095.00,0.00,29991.89,'2026-06-17 17:23:54','pdf_import'),(343,NULL,18,2568,407400.00,'2025-08-18',407400.00,267080.00,615393.11,29095.00,0.00,29991.89,'2026-06-17 17:23:54','pdf_import'),(344,NULL,18,2568,407400.00,'2025-08-25',407400.00,264745.00,629802.40,29095.00,0.00,13247.60,'2026-06-17 17:23:54','pdf_import'),(345,NULL,18,2568,407400.00,'2025-08-29',407400.00,264745.00,645822.90,0.00,0.00,26322.10,'2026-06-17 17:23:54','pdf_import'),(346,NULL,18,2568,407400.00,'2025-09-15',407400.00,264745.00,656227.90,0.00,0.00,15917.10,'2026-06-17 17:23:54','pdf_import'),(347,NULL,18,2568,407400.00,'2025-09-22',407400.00,264745.00,652967.90,0.00,0.00,19177.10,'2026-06-17 17:23:54','pdf_import'),(348,NULL,18,2568,407400.00,'2025-09-30',407400.00,254594.60,652967.90,0.00,0.00,9026.70,'2026-06-17 17:23:55','pdf_import'),(349,NULL,18,2569,609000.00,'2025-10-31',409000.00,0.00,0.00,0.00,0.00,409000.00,'2026-06-17 17:23:55','pdf_import'),(350,NULL,18,2569,609000.00,'2025-11-10',409000.00,0.00,0.00,0.00,0.00,409000.00,'2026-06-17 17:23:55','pdf_import'),(351,NULL,18,2569,609000.00,'2025-11-17',409000.00,0.00,0.00,1605.00,0.00,407395.00,'2026-06-17 17:23:55','pdf_import'),(352,NULL,18,2569,609000.00,'2025-11-24',409000.00,0.00,11045.00,0.00,0.00,397955.00,'2026-06-17 17:23:55','pdf_import'),(353,NULL,18,2569,609000.00,'2025-11-28',409000.00,0.00,51475.01,18160.00,0.00,339364.99,'2026-06-17 17:23:55','pdf_import'),(354,NULL,18,2569,609000.00,'2025-12-22',409000.00,0.00,69635.01,0.00,0.00,339364.99,'2026-06-17 17:23:56','pdf_import'),(355,NULL,18,2569,609000.00,'2025-12-30',409000.00,0.00,79975.01,0.00,0.00,329024.99,'2026-06-17 17:23:56','pdf_import'),(356,NULL,18,2569,609000.00,'2026-01-12',409000.00,0.00,79975.01,0.00,0.00,329024.99,'2026-06-17 17:23:56','pdf_import'),(357,NULL,18,2569,609000.00,'2026-01-26',409000.00,0.00,152083.01,0.00,0.00,256916.99,'2026-06-17 17:23:56','pdf_import'),(358,NULL,18,2569,609000.00,'2026-02-23',409000.00,0.00,213478.01,0.00,0.00,195521.99,'2026-06-17 17:23:56','pdf_import'),(359,NULL,18,2569,609000.00,'2026-05-25',409000.00,295700.00,223264.01,0.00,0.00,481435.99,'2026-06-17 17:23:56','pdf_import'),(360,NULL,18,2569,609000.00,'2026-05-29',409000.00,295700.00,223264.01,0.00,0.00,481435.99,'2026-06-17 17:23:57','pdf_import'),(361,NULL,12,2568,2347300.00,'2024-10-28',922300.00,136030.00,63025.00,0.00,0.00,995305.00,'2026-06-17 17:23:57','pdf_import'),(362,NULL,12,2568,2347300.00,'2024-10-31',922300.00,136030.00,215878.67,0.00,0.00,842451.33,'2026-06-17 17:23:57','pdf_import'),(363,NULL,12,2568,2347300.00,'2024-11-29',922300.00,219730.00,335573.69,0.00,0.00,806456.31,'2026-06-17 17:23:57','pdf_import'),(364,NULL,12,2568,2347300.00,'2024-12-23',922300.00,219730.00,640352.61,0.00,0.00,501677.39,'2026-06-17 17:23:57','pdf_import'),(365,NULL,12,2568,2347300.00,'2025-01-13',922300.00,219730.00,641012.61,0.00,0.00,501017.39,'2026-06-17 17:23:57','pdf_import'),(366,NULL,12,2568,2347300.00,'2025-02-10',922300.00,197506.13,695922.58,2500.00,0.00,421383.55,'2026-06-17 17:23:58','pdf_import'),(367,NULL,12,2568,2347300.00,'2025-02-24',922300.00,197506.13,878822.58,0.00,0.00,240983.55,'2026-06-17 17:23:58','pdf_import'),(368,NULL,12,2568,2347300.00,'2025-05-30',2347300.00,296506.13,902017.98,79000.00,0.00,1662788.15,'2026-06-17 17:23:58','pdf_import'),(369,NULL,12,2568,2347300.00,'2025-07-14',2347300.00,196506.13,1417294.69,0.00,0.00,1126511.44,'2026-06-17 17:23:58','pdf_import'),(370,NULL,12,2568,2347300.00,'2025-07-21',2347300.00,196506.13,1417414.69,0.00,0.00,1126391.44,'2026-06-17 17:23:58','pdf_import'),(371,NULL,12,2568,2347300.00,'2025-07-31',2347300.00,196506.13,1449244.69,0.00,0.00,1094561.44,'2026-06-17 17:23:58','pdf_import'),(372,NULL,12,2568,2347300.00,'2025-08-13',2347300.00,196506.13,1505944.69,0.00,0.00,1037861.44,'2026-06-17 17:23:59','pdf_import'),(373,NULL,12,2568,2347300.00,'2025-08-18',2347300.00,196506.13,1543437.30,450.00,0.00,999918.83,'2026-06-17 17:23:59','pdf_import'),(374,NULL,12,2568,2347300.00,'2025-08-25',2347300.00,188871.13,1543887.30,0.00,0.00,992283.83,'2026-06-17 17:23:59','pdf_import'),(375,NULL,12,2568,2347300.00,'2025-08-29',2347300.00,187671.13,1510457.30,0.00,0.00,1024513.83,'2026-06-17 17:23:59','pdf_import'),(376,NULL,12,2568,2347300.00,'2025-09-15',2347300.00,148123.70,2463887.70,0.00,0.00,31536.00,'2026-06-17 17:23:59','pdf_import'),(377,NULL,12,2568,2347300.00,'2025-09-22',2347300.00,148123.70,2408011.20,0.00,0.00,87412.50,'2026-06-17 17:24:00','pdf_import'),(378,NULL,12,2568,2347300.00,'2025-09-30',2347300.00,74408.20,2409796.20,0.00,0.00,11912.00,'2026-06-17 17:24:00','pdf_import'),(379,NULL,12,2569,4084500.00,'2025-10-31',1119100.00,0.00,760.00,322100.00,0.00,796240.00,'2026-06-17 17:24:00','pdf_import'),(380,NULL,12,2569,4084500.00,'2025-11-10',1119100.00,0.00,322860.00,0.00,0.00,796240.00,'2026-06-17 17:24:00','pdf_import'),(381,NULL,12,2569,4084500.00,'2025-11-17',1119100.00,0.00,324390.00,0.00,0.00,794710.00,'2026-06-17 17:24:00','pdf_import'),(382,NULL,12,2569,4084500.00,'2025-11-24',1119100.00,15000.00,325230.00,0.00,0.00,808870.00,'2026-06-17 17:24:00','pdf_import'),(383,NULL,12,2569,4084500.00,'2025-11-28',1119100.00,15000.00,325230.00,9500.00,0.00,799370.00,'2026-06-17 17:24:01','pdf_import'),(384,NULL,12,2569,4084500.00,'2025-12-22',1119100.00,15000.00,274227.37,2250.00,0.00,857622.63,'2026-06-17 17:24:01','pdf_import'),(385,NULL,12,2569,4084500.00,'2025-12-30',1119100.00,13830.00,276477.37,0.00,0.00,856452.63,'2026-06-17 17:24:01','pdf_import'),(386,NULL,12,2569,4084500.00,'2026-01-12',1119100.00,697022.70,803027.37,0.00,0.00,1013095.33,'2026-06-17 17:24:01','pdf_import'),(387,NULL,12,2569,4084500.00,'2026-01-26',1119100.00,697022.70,959960.07,0.00,0.00,856162.63,'2026-06-17 17:24:01','pdf_import'),(388,NULL,12,2569,4084500.00,'2026-02-23',1119100.00,697022.70,968170.07,0.00,0.00,847952.63,'2026-06-17 17:24:02','pdf_import'),(389,NULL,12,2569,4084500.00,'2026-05-25',3252000.00,713922.70,1341202.71,215600.00,0.00,2409119.99,'2026-06-17 17:24:02','pdf_import'),(390,NULL,12,2569,4084500.00,'2026-05-29',3252000.00,713922.70,1557327.71,0.00,0.00,2408594.99,'2026-06-17 17:24:02','pdf_import'),(391,NULL,14,2568,250100.00,'2024-10-28',206700.00,0.00,0.00,0.00,0.00,206700.00,'2026-06-17 17:24:02','pdf_import'),(392,NULL,14,2568,250100.00,'2024-10-31',206700.00,0.00,240.00,0.00,0.00,206460.00,'2026-06-17 17:24:02','pdf_import'),(393,NULL,14,2568,250100.00,'2024-11-29',206700.00,0.00,1740.00,0.00,0.00,204960.00,'2026-06-17 17:24:02','pdf_import'),(394,NULL,14,2568,250100.00,'2024-12-23',206700.00,0.00,5180.00,0.00,0.00,201520.00,'2026-06-17 17:24:03','pdf_import'),(395,NULL,14,2568,250100.00,'2025-01-13',206700.00,0.00,6160.00,0.00,0.00,200540.00,'2026-06-17 17:24:03','pdf_import'),(396,NULL,14,2568,250100.00,'2025-02-10',206700.00,0.00,6303.00,0.00,0.00,200397.00,'2026-06-17 17:24:03','pdf_import'),(397,NULL,14,2568,250100.00,'2025-02-24',206700.00,0.00,21243.00,0.00,0.00,185457.00,'2026-06-17 17:24:03','pdf_import'),(398,NULL,14,2568,250100.00,'2025-05-30',250100.00,-8700.00,134924.00,0.00,0.00,106476.00,'2026-06-17 17:24:03','pdf_import'),(399,NULL,14,2568,250100.00,'2025-07-14',250100.00,7900.00,147664.00,0.00,0.00,110336.00,'2026-06-17 17:24:03','pdf_import'),(400,NULL,14,2568,250100.00,'2025-07-21',250100.00,7900.00,147664.00,0.00,0.00,110336.00,'2026-06-17 17:24:04','pdf_import'),(401,NULL,14,2568,250100.00,'2025-07-31',250100.00,7900.00,147904.00,0.00,0.00,110096.00,'2026-06-17 17:24:04','pdf_import'),(402,NULL,14,2568,250100.00,'2025-08-13',250100.00,7900.00,189954.00,0.00,0.00,68046.00,'2026-06-17 17:24:04','pdf_import'),(403,NULL,14,2568,250100.00,'2025-08-18',250100.00,7900.00,190314.00,245.00,0.00,67441.00,'2026-06-17 17:24:04','pdf_import'),(404,NULL,14,2568,250100.00,'2025-08-25',250100.00,16175.00,190559.00,0.00,0.00,75716.00,'2026-06-17 17:24:04','pdf_import'),(405,NULL,14,2568,250100.00,'2025-08-29',250100.00,34755.00,221799.00,380.00,0.00,62676.00,'2026-06-17 17:24:04','pdf_import'),(406,NULL,14,2568,250100.00,'2025-09-15',250100.00,39855.00,255179.00,71.00,0.00,34705.00,'2026-06-17 17:24:05','pdf_import'),(407,NULL,14,2568,250100.00,'2025-09-22',250100.00,39855.00,260350.00,26112.50,0.00,3492.50,'2026-06-17 17:24:05','pdf_import'),(408,NULL,14,2568,250100.00,'2025-09-30',250100.00,39615.00,286495.50,0.00,0.00,3219.50,'2026-06-17 17:24:05','pdf_import'),(409,NULL,14,2569,739500.00,'2025-10-31',639500.00,0.00,720.00,0.00,0.00,316380.00,'2026-06-17 17:24:05','pdf_import'),(410,NULL,14,2569,739500.00,'2025-11-10',639500.00,0.00,720.00,0.00,62100.00,254280.00,'2026-06-17 17:24:05','pdf_import'),(411,NULL,14,2569,739500.00,'2025-11-17',639500.00,0.00,1260.00,0.00,162100.00,153740.00,'2026-06-17 17:24:05','pdf_import'),(412,NULL,14,2569,739500.00,'2025-11-24',639500.00,0.00,10260.00,0.00,162100.00,144740.00,'2026-06-17 17:24:06','pdf_import'),(413,NULL,14,2569,739500.00,'2025-11-28',639500.00,0.00,10500.00,0.00,162100.00,144500.00,'2026-06-17 17:24:06','pdf_import'),(414,NULL,14,2569,739500.00,'2025-12-22',639500.00,0.00,10500.00,0.00,162100.00,144500.00,'2026-06-17 17:24:06','pdf_import'),(415,NULL,14,2569,739500.00,'2025-12-30',639500.00,0.00,71880.00,0.00,112420.00,132800.00,'2026-06-17 17:24:06','pdf_import'),(416,NULL,14,2569,739500.00,'2026-01-12',639500.00,0.00,71880.00,0.00,112420.00,132800.00,'2026-06-17 17:24:06','pdf_import'),(417,NULL,14,2569,739500.00,'2026-01-26',639500.00,0.00,72300.00,0.00,112420.00,132380.00,'2026-06-17 17:24:06','pdf_import'),(418,NULL,14,2569,739500.00,'2026-02-23',639500.00,0.00,72780.00,0.00,112420.00,131900.00,'2026-06-17 17:24:07','pdf_import'),(419,NULL,14,2569,739500.00,'2026-05-25',739500.00,53130.00,240405.00,0.00,50000.00,502225.00,'2026-06-17 17:24:07','pdf_import'),(420,NULL,14,2569,739500.00,'2026-05-29',739500.00,53130.00,240405.00,37950.00,50000.00,464275.00,'2026-06-17 17:24:07','pdf_import'),(421,NULL,16,2568,650000.00,'2024-10-28',650000.00,0.00,1460.00,0.00,0.00,648540.00,'2026-06-17 17:24:07','pdf_import'),(422,NULL,16,2568,650000.00,'2024-10-31',650000.00,0.00,3015.00,0.00,0.00,646985.00,'2026-06-17 17:24:07','pdf_import'),(423,NULL,16,2568,650000.00,'2024-11-29',650000.00,0.00,354003.00,0.00,0.00,295997.00,'2026-06-17 17:24:07','pdf_import'),(424,NULL,16,2568,650000.00,'2024-12-23',650000.00,0.00,378728.00,28370.00,0.00,242902.00,'2026-06-17 17:24:07','pdf_import'),(425,NULL,16,2568,650000.00,'2025-01-13',650000.00,0.00,197604.30,0.00,0.00,452395.70,'2026-06-17 17:24:08','pdf_import'),(426,NULL,16,2568,650000.00,'2025-02-10',650000.00,0.00,294242.57,1887.00,0.00,353870.43,'2026-06-17 17:24:08','pdf_import'),(427,NULL,16,2568,650000.00,'2025-02-24',650000.00,0.00,526099.54,0.00,0.00,123900.46,'2026-06-17 17:24:08','pdf_import'),(428,NULL,16,2568,650000.00,'2025-05-30',650000.00,425500.00,908662.10,1655.00,0.00,165182.90,'2026-06-17 17:24:08','pdf_import'),(429,NULL,16,2568,650000.00,'2025-07-14',650000.00,629500.00,977732.36,0.00,0.00,301767.64,'2026-06-17 17:24:08','pdf_import'),(430,NULL,16,2568,650000.00,'2025-07-21',650000.00,629500.00,986472.36,0.00,0.00,293027.64,'2026-06-17 17:24:09','pdf_import'),(431,NULL,16,2568,650000.00,'2025-07-31',650000.00,629500.00,1092497.86,0.00,0.00,187002.14,'2026-06-17 17:24:09','pdf_import'),(432,NULL,16,2568,650000.00,'2025-08-13',650000.00,629500.00,1119203.86,0.00,0.00,160296.14,'2026-06-17 17:24:09','pdf_import'),(433,NULL,16,2568,650000.00,'2025-08-18',650000.00,629500.00,1119203.86,18760.00,0.00,141536.14,'2026-06-17 17:24:09','pdf_import'),(434,NULL,16,2568,650000.00,'2025-08-25',650000.00,821433.44,1141893.86,0.00,0.00,329539.58,'2026-06-17 17:24:09','pdf_import'),(435,NULL,16,2568,650000.00,'2025-08-29',650000.00,824463.44,1173793.86,20044.00,0.00,280625.58,'2026-06-17 17:24:09','pdf_import'),(436,NULL,16,2568,650000.00,'2025-09-15',650000.00,972843.44,1467591.86,0.00,0.00,155251.58,'2026-06-17 17:24:10','pdf_import'),(437,NULL,16,2568,650000.00,'2025-09-22',650000.00,972843.44,1470421.86,0.00,0.00,152421.58,'2026-06-17 17:24:10','pdf_import'),(438,NULL,16,2568,650000.00,'2025-09-30',650000.00,1154344.89,1647687.71,0.00,148380.00,8277.18,'2026-06-17 17:24:10','pdf_import'),(439,NULL,16,2569,635500.00,'2025-10-31',635500.00,0.00,19324.00,0.00,0.00,616176.00,'2026-06-17 17:24:10','pdf_import'),(440,NULL,16,2569,635500.00,'2025-11-10',635500.00,0.00,19324.00,0.00,0.00,616176.00,'2026-06-17 17:24:10','pdf_import'),(441,NULL,16,2569,635500.00,'2025-11-17',635500.00,0.00,20094.00,0.00,0.00,615406.00,'2026-06-17 17:24:10','pdf_import'),(442,NULL,16,2569,635500.00,'2025-11-24',635500.00,0.00,22164.00,8680.00,0.00,604656.00,'2026-06-17 17:24:11','pdf_import'),(443,NULL,16,2569,635500.00,'2025-11-28',635500.00,0.00,32252.50,0.00,0.00,603247.50,'2026-06-17 17:24:11','pdf_import'),(444,NULL,16,2569,635500.00,'2025-12-22',635500.00,0.00,147649.50,26034.00,0.00,461816.50,'2026-06-17 17:24:11','pdf_import'),(445,NULL,16,2569,635500.00,'2025-12-30',635500.00,0.00,173683.50,106666.19,0.00,355150.31,'2026-06-17 17:24:11','pdf_import'),(446,NULL,16,2569,635500.00,'2026-01-12',635500.00,0.00,280349.69,23001.60,0.00,332148.71,'2026-06-17 17:24:11','pdf_import'),(447,NULL,16,2569,635500.00,'2026-01-26',635500.00,0.00,401841.29,0.00,0.00,233658.71,'2026-06-17 17:24:11','pdf_import'),(448,NULL,16,2569,635500.00,'2026-02-23',635500.00,450000.00,468882.29,0.00,0.00,616617.71,'2026-06-17 17:24:12','pdf_import'),(449,NULL,16,2569,635500.00,'2026-05-25',635500.00,450000.00,570446.59,25308.00,0.00,489745.41,'2026-06-17 17:24:12','pdf_import'),(450,NULL,16,2569,635500.00,'2026-05-29',635500.00,450000.00,595754.59,0.00,0.00,489745.41,'2026-06-17 17:24:12','pdf_import'),(451,NULL,9,2568,40700000.00,'2024-10-28',20350500.00,-10104000.00,0.00,0.00,8367.40,10238132.60,'2026-06-17 17:24:12','pdf_import'),(452,NULL,9,2568,40700000.00,'2024-10-31',20350500.00,-17322542.55,0.00,0.00,8367.40,3019590.05,'2026-06-17 17:24:12','pdf_import'),(453,NULL,9,2568,40700000.00,'2024-11-29',20350500.00,-19061078.00,147950.00,0.00,8367.40,1133104.60,'2026-06-17 17:24:13','pdf_import'),(454,NULL,9,2568,40700000.00,'2024-12-23',20350500.00,-18482428.00,293975.00,0.00,8367.40,1565729.60,'2026-06-17 17:24:13','pdf_import'),(455,NULL,9,2568,40700000.00,'2025-01-13',20350500.00,-19384578.00,439575.00,0.00,8367.40,517979.60,'2026-06-17 17:24:14','pdf_import'),(456,NULL,9,2568,40700000.00,'2025-02-10',20350500.00,-19384578.00,586602.60,0.00,7639.80,371679.60,'2026-06-17 17:24:14','pdf_import'),(457,NULL,9,2568,40700000.00,'2025-02-24',20350500.00,-19384578.00,586602.60,0.00,7639.80,371679.60,'2026-06-17 17:24:14','pdf_import'),(458,NULL,9,2568,40700000.00,'2025-05-30',40700000.00,-38613629.79,1037000.42,0.00,6220.98,1043148.81,'2026-06-17 17:24:14','pdf_import'),(459,NULL,9,2568,40700000.00,'2025-07-14',40700000.00,-38560976.56,1331961.98,0.00,5784.42,801277.04,'2026-06-17 17:24:14','pdf_import'),(460,NULL,9,2568,40700000.00,'2025-07-21',40700000.00,-38548398.56,1331961.98,0.00,5784.42,813855.04,'2026-06-17 17:24:14','pdf_import'),(461,NULL,9,2568,40700000.00,'2025-07-31',40700000.00,-38599032.76,1332616.82,0.00,5129.58,763220.84,'2026-06-17 17:24:15','pdf_import'),(462,NULL,9,2568,40700000.00,'2025-08-13',40700000.00,-38599032.76,1476991.82,0.00,5129.58,618845.84,'2026-06-17 17:24:15','pdf_import'),(463,NULL,9,2568,40700000.00,'2025-08-18',40700000.00,-38576832.76,1476991.82,0.00,5129.58,641045.84,'2026-06-17 17:24:15','pdf_import'),(464,NULL,9,2568,40700000.00,'2025-08-25',40700000.00,-38576832.76,1477573.90,0.00,4547.50,641045.84,'2026-06-17 17:24:15','pdf_import'),(465,NULL,9,2568,40700000.00,'2025-08-29',40700000.00,-38189194.73,1477573.90,0.00,4547.50,1028683.87,'2026-06-17 17:24:15','pdf_import'),(466,NULL,9,2568,40700000.00,'2025-09-15',40700000.00,-37891562.79,1621123.90,0.00,4547.50,1182765.81,'2026-06-17 17:24:16','pdf_import'),(467,NULL,9,2568,40700000.00,'2025-09-22',40700000.00,-37891562.79,1621524.08,0.00,4147.32,1182765.81,'2026-06-17 17:24:16','pdf_import'),(468,NULL,9,2568,40700000.00,'2025-09-30',40700000.00,-38918896.68,1763383.40,0.00,3638.00,14081.92,'2026-06-17 17:24:16','pdf_import'),(469,NULL,9,2569,80374700.00,'2025-10-31',59177400.00,-19416495.00,0.00,0.00,0.00,39760905.00,'2026-06-17 17:24:16','pdf_import'),(470,NULL,9,2569,80374700.00,'2025-11-10',59177400.00,-19416495.00,0.00,0.00,0.00,39760905.00,'2026-06-17 17:24:16','pdf_import'),(471,NULL,9,2569,80374700.00,'2025-11-17',59177400.00,-19416495.00,0.00,0.00,0.00,39760905.00,'2026-06-17 17:24:16','pdf_import'),(472,NULL,9,2569,80374700.00,'2025-11-24',59177400.00,-19416495.00,0.00,0.00,0.00,39760905.00,'2026-06-17 17:24:17','pdf_import'),(473,NULL,9,2569,80374700.00,'2025-11-28',59177400.00,-52611995.00,1050.00,0.00,0.00,6564355.00,'2026-06-17 17:24:17','pdf_import'),(474,NULL,9,2569,80374700.00,'2025-12-22',59177400.00,-52611995.00,1050.00,0.00,0.00,6564355.00,'2026-06-17 17:24:17','pdf_import'),(475,NULL,9,2569,80374700.00,'2025-12-30',59177400.00,-52130916.45,1050.00,2261600.00,0.00,4783833.55,'2026-06-17 17:24:17','pdf_import'),(476,NULL,9,2569,80374700.00,'2026-01-12',59177400.00,-52130916.45,1050.00,2261600.00,0.00,4783833.55,'2026-06-17 17:24:17','pdf_import'),(477,NULL,9,2569,80374700.00,'2026-01-26',59177400.00,-52130916.45,60200.00,2261600.00,0.00,4724683.55,'2026-06-17 17:24:18','pdf_import'),(478,NULL,9,2569,80374700.00,'2026-02-23',59177400.00,-54416572.52,79700.00,2261600.00,0.00,2419527.48,'2026-06-17 17:24:18','pdf_import'),(479,NULL,9,2569,79655500.00,'2026-05-25',76329100.00,-66740214.42,3980.00,2261600.00,0.00,7323305.58,'2026-06-17 17:24:18','pdf_import'),(480,NULL,9,2569,79655500.00,'2026-05-29',76329100.00,-68939056.42,3980.00,2261600.00,0.00,5124463.58,'2026-06-17 17:24:18','pdf_import'),(481,NULL,23,2568,260000.00,'2024-10-28',260000.00,0.00,0.00,0.00,0.00,260000.00,'2026-06-17 17:24:18','pdf_import'),(482,NULL,23,2568,260000.00,'2024-10-31',260000.00,0.00,0.00,0.00,0.00,260000.00,'2026-06-17 17:24:19','pdf_import'),(483,NULL,23,2568,260000.00,'2024-11-29',260000.00,0.00,0.00,0.00,0.00,260000.00,'2026-06-17 17:24:19','pdf_import'),(484,NULL,23,2568,260000.00,'2024-12-23',260000.00,0.00,61400.00,0.00,0.00,198600.00,'2026-06-17 17:24:19','pdf_import'),(485,NULL,23,2568,260000.00,'2025-01-13',260000.00,0.00,61400.00,0.00,0.00,198600.00,'2026-06-17 17:24:19','pdf_import'),(486,NULL,23,2568,260000.00,'2025-02-10',260000.00,0.00,121855.00,0.00,0.00,138145.00,'2026-06-17 17:24:19','pdf_import'),(487,NULL,23,2568,260000.00,'2025-02-24',260000.00,0.00,157750.00,0.00,0.00,102250.00,'2026-06-17 17:24:20','pdf_import'),(488,NULL,23,2568,260000.00,'2025-05-30',260000.00,-22600.00,220750.00,0.00,0.00,16650.00,'2026-06-17 17:24:20','pdf_import'),(489,NULL,23,2568,260000.00,'2025-07-14',260000.00,-22600.00,224350.00,0.00,0.00,13050.00,'2026-06-17 17:24:20','pdf_import'),(490,NULL,23,2568,260000.00,'2025-07-21',260000.00,47400.00,224350.00,0.00,0.00,83050.00,'2026-06-17 17:24:20','pdf_import'),(491,NULL,23,2568,260000.00,'2025-07-31',260000.00,47400.00,224350.00,69973.50,0.00,13076.50,'2026-06-17 17:24:20','pdf_import'),(492,NULL,23,2568,260000.00,'2025-08-13',260000.00,47400.00,224350.00,69973.50,0.00,13076.50,'2026-06-17 17:24:20','pdf_import'),(493,NULL,23,2568,260000.00,'2025-08-18',260000.00,47400.00,224350.00,10000.00,59973.50,13076.50,'2026-06-17 17:24:21','pdf_import'),(494,NULL,23,2568,260000.00,'2025-08-25',260000.00,47400.00,284323.50,10000.00,0.00,13076.50,'2026-06-17 17:24:21','pdf_import'),(495,NULL,23,2568,260000.00,'2025-08-29',260000.00,47400.00,289398.50,8990.00,0.00,9011.50,'2026-06-17 17:24:21','pdf_import'),(496,NULL,23,2568,260000.00,'2025-09-15',260000.00,46363.50,299088.50,0.00,0.00,7275.00,'2026-06-17 17:24:21','pdf_import'),(497,NULL,23,2568,260000.00,'2025-09-22',260000.00,46363.50,302520.50,0.00,0.00,3843.00,'2026-06-17 17:24:21','pdf_import'),(498,NULL,23,2568,260000.00,'2025-09-30',260000.00,44363.50,302520.50,0.00,0.00,1843.00,'2026-06-17 17:24:22','pdf_import'),(499,NULL,23,2569,264000.00,'2025-10-31',139000.00,0.00,0.00,0.00,0.00,139000.00,'2026-06-17 17:24:22','pdf_import'),(500,NULL,23,2569,264000.00,'2025-11-10',139000.00,0.00,0.00,0.00,0.00,139000.00,'2026-06-17 17:24:22','pdf_import'),(501,NULL,23,2569,264000.00,'2025-11-17',139000.00,0.00,74400.00,0.00,0.00,64600.00,'2026-06-17 17:24:22','pdf_import'),(502,NULL,23,2569,264000.00,'2025-11-24',139000.00,0.00,74400.00,0.00,0.00,64600.00,'2026-06-17 17:24:22','pdf_import'),(503,NULL,23,2569,264000.00,'2025-11-28',139000.00,0.00,74400.00,0.00,0.00,64600.00,'2026-06-17 17:24:22','pdf_import'),(504,NULL,23,2569,264000.00,'2025-12-22',139000.00,0.00,76340.00,0.00,0.00,62660.00,'2026-06-17 17:24:23','pdf_import'),(505,NULL,23,2569,264000.00,'2025-12-30',139000.00,0.00,76340.00,0.00,0.00,62660.00,'2026-06-17 17:24:23','pdf_import'),(506,NULL,23,2569,264000.00,'2026-01-12',139000.00,0.00,76340.00,0.00,0.00,62660.00,'2026-06-17 17:24:23','pdf_import'),(507,NULL,23,2569,264000.00,'2026-01-26',139000.00,0.00,76340.00,0.00,0.00,62660.00,'2026-06-17 17:24:23','pdf_import'),(508,NULL,23,2569,264000.00,'2026-02-23',139000.00,3600.00,76340.00,0.00,0.00,66260.00,'2026-06-17 17:24:23','pdf_import'),(509,NULL,23,2569,264000.00,'2026-05-25',264000.00,89150.00,133090.00,0.00,0.00,220060.00,'2026-06-17 17:24:23','pdf_import'),(510,NULL,23,2569,264000.00,'2026-05-29',264000.00,89150.00,193090.00,39600.00,0.00,120460.00,'2026-06-17 17:24:24','pdf_import'),(511,NULL,26,2568,5000.00,'2024-10-28',5000.00,0.00,0.00,0.00,0.00,5000.00,'2026-06-17 17:24:24','pdf_import'),(512,NULL,26,2568,5000.00,'2024-10-31',5000.00,0.00,0.00,0.00,0.00,5000.00,'2026-06-17 17:24:24','pdf_import'),(513,NULL,26,2568,5000.00,'2024-11-29',5000.00,0.00,0.00,0.00,0.00,5000.00,'2026-06-17 17:24:24','pdf_import'),(514,NULL,26,2568,5000.00,'2024-12-23',5000.00,0.00,0.00,0.00,0.00,5000.00,'2026-06-17 17:24:24','pdf_import'),(515,NULL,26,2568,5000.00,'2025-01-13',5000.00,0.00,0.00,0.00,0.00,5000.00,'2026-06-17 17:24:24','pdf_import'),(516,NULL,26,2568,5000.00,'2025-02-10',5000.00,0.00,1610.00,0.00,0.00,3390.00,'2026-06-17 17:24:25','pdf_import'),(517,NULL,26,2568,5000.00,'2025-02-24',5000.00,0.00,3010.00,0.00,0.00,1990.00,'2026-06-17 17:24:25','pdf_import'),(518,NULL,26,2568,5000.00,'2025-05-30',5000.00,0.00,3010.00,0.00,0.00,1990.00,'2026-06-17 17:24:25','pdf_import'),(519,NULL,26,2568,5000.00,'2025-07-14',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:25','pdf_import'),(520,NULL,26,2568,5000.00,'2025-07-21',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:25','pdf_import'),(521,NULL,26,2568,5000.00,'2025-07-31',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:25','pdf_import'),(522,NULL,26,2568,5000.00,'2025-08-13',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:25','pdf_import'),(523,NULL,26,2568,5000.00,'2025-08-18',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:26','pdf_import'),(524,NULL,26,2568,5000.00,'2025-08-25',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:26','pdf_import'),(525,NULL,26,2568,5000.00,'2025-08-29',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:26','pdf_import'),(526,NULL,26,2568,5000.00,'2025-09-15',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:26','pdf_import'),(527,NULL,26,2568,5000.00,'2025-09-22',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:26','pdf_import'),(528,NULL,26,2568,5000.00,'2025-09-30',5000.00,0.00,4585.00,0.00,0.00,415.00,'2026-06-17 17:24:26','pdf_import'),(529,NULL,26,2569,15000.00,'2025-10-31',15000.00,0.00,3170.00,0.00,0.00,11830.00,'2026-06-17 17:24:27','pdf_import'),(530,NULL,26,2569,15000.00,'2025-11-10',15000.00,0.00,3170.00,0.00,0.00,11830.00,'2026-06-17 17:24:27','pdf_import'),(531,NULL,26,2569,15000.00,'2025-11-17',15000.00,0.00,3170.00,1280.00,0.00,10550.00,'2026-06-17 17:24:27','pdf_import'),(532,NULL,26,2569,15000.00,'2025-11-24',15000.00,0.00,4450.00,0.00,0.00,10550.00,'2026-06-17 17:24:27','pdf_import'),(533,NULL,26,2569,15000.00,'2025-11-28',15000.00,0.00,4450.00,0.00,0.00,10550.00,'2026-06-17 17:24:27','pdf_import'),(534,NULL,26,2569,15000.00,'2025-12-22',15000.00,0.00,6610.00,0.00,0.00,8390.00,'2026-06-17 17:24:27','pdf_import'),(535,NULL,26,2569,15000.00,'2025-12-30',15000.00,0.00,6610.00,0.00,0.00,8390.00,'2026-06-17 17:24:28','pdf_import'),(536,NULL,26,2569,15000.00,'2026-01-12',15000.00,0.00,6610.00,0.00,0.00,8390.00,'2026-06-17 17:24:28','pdf_import'),(537,NULL,26,2569,15000.00,'2026-01-26',15000.00,0.00,6610.00,0.00,0.00,8390.00,'2026-06-17 17:24:28','pdf_import'),(538,NULL,26,2569,15000.00,'2026-02-23',15000.00,0.00,6610.00,0.00,0.00,8390.00,'2026-06-17 17:24:28','pdf_import'),(539,NULL,26,2569,15000.00,'2026-05-25',15000.00,0.00,7040.00,0.00,0.00,7960.00,'2026-06-17 17:24:28','pdf_import'),(540,NULL,26,2569,15000.00,'2026-05-29',15000.00,0.00,7040.00,0.00,0.00,7960.00,'2026-06-17 17:24:29','pdf_import'),(541,NULL,11,2568,3377000.00,'2024-10-28',3312000.00,0.00,0.00,808920.00,34069.92,2469010.08,'2026-06-17 17:24:29','pdf_import'),(542,NULL,11,2568,3377000.00,'2024-10-31',3312000.00,0.00,0.00,808920.00,34069.92,2469010.08,'2026-06-17 17:24:29','pdf_import'),(543,NULL,11,2568,3377000.00,'2024-11-29',3312000.00,0.00,132860.00,360.00,775579.92,2403200.08,'2026-06-17 17:24:29','pdf_import'),(544,NULL,11,2568,3377000.00,'2024-12-23',3312000.00,0.00,270745.00,2360.00,703144.92,2335750.08,'2026-06-17 17:24:29','pdf_import'),(545,NULL,11,2568,3377000.00,'2025-01-13',3312000.00,0.00,405046.60,360.00,635734.92,2270858.48,'2026-06-17 17:24:29','pdf_import'),(546,NULL,11,2568,3377000.00,'2025-02-10',3312000.00,0.00,474354.40,498760.00,631877.12,1707008.48,'2026-06-17 17:24:29','pdf_import'),(547,NULL,11,2568,3377000.00,'2025-02-24',3312000.00,0.00,543439.40,360.00,1060017.12,1708183.48,'2026-06-17 17:24:30','pdf_import'),(548,NULL,11,2568,3377000.00,'2025-05-30',3377000.00,-210055.00,1419763.76,0.00,354592.76,1392588.48,'2026-06-17 17:24:30','pdf_import'),(549,NULL,11,2568,3377000.00,'2025-07-14',3377000.00,-210055.00,1881729.64,56000.00,215476.88,1013738.48,'2026-06-17 17:24:30','pdf_import'),(550,NULL,11,2568,3377000.00,'2025-07-21',3377000.00,-210055.00,1934729.64,0.00,215476.88,1016738.48,'2026-06-17 17:24:30','pdf_import'),(551,NULL,11,2568,3377000.00,'2025-07-31',3377000.00,-210055.00,1936030.88,0.00,212055.64,1018858.48,'2026-06-17 17:24:30','pdf_import'),(552,NULL,11,2568,3377000.00,'2025-08-13',3377000.00,-210055.00,2155840.88,0.00,144645.64,866458.48,'2026-06-17 17:24:31','pdf_import'),(553,NULL,11,2568,3377000.00,'2025-08-18',3377000.00,-210055.00,2155840.88,0.00,144645.64,866458.48,'2026-06-17 17:24:31','pdf_import'),(554,NULL,11,2568,3377000.00,'2025-08-25',3377000.00,-210055.00,2158752.80,0.00,141733.72,866458.48,'2026-06-17 17:24:31','pdf_import'),(555,NULL,11,2568,3377000.00,'2025-08-29',3377000.00,-210055.00,2158892.80,14966.00,141733.72,851352.48,'2026-06-17 17:24:31','pdf_import'),(556,NULL,11,2568,3377000.00,'2025-09-15',3377000.00,-805061.20,2303718.80,29671.10,89289.72,149259.18,'2026-06-17 17:24:31','pdf_import'),(557,NULL,11,2568,3377000.00,'2025-09-22',3377000.00,-805061.20,2402013.92,0.00,71593.70,98331.18,'2026-06-17 17:24:31','pdf_import'),(558,NULL,11,2568,3377000.00,'2025-09-30',3377000.00,-761160.20,2524397.94,0.00,70538.68,20903.18,'2026-06-17 17:24:32','pdf_import'),(559,NULL,11,2569,2129000.00,'2025-10-31',2129000.00,0.00,0.00,0.00,808920.00,1320080.00,'2026-06-17 17:24:32','pdf_import'),(560,NULL,11,2569,2129000.00,'2025-11-10',2129000.00,0.00,54450.00,0.00,808920.00,1265630.00,'2026-06-17 17:24:32','pdf_import'),(561,NULL,11,2569,2129000.00,'2025-11-17',2129000.00,0.00,121860.00,0.00,741510.00,1265630.00,'2026-06-17 17:24:32','pdf_import'),(562,NULL,11,2569,2129000.00,'2025-11-24',2129000.00,0.00,121860.00,0.00,741510.00,1265630.00,'2026-06-17 17:24:33','pdf_import'),(563,NULL,11,2569,2129000.00,'2025-11-28',2129000.00,0.00,121860.00,0.00,741510.00,1265630.00,'2026-06-17 17:24:33','pdf_import'),(564,NULL,11,2569,2129000.00,'2025-12-22',2129000.00,0.00,252190.00,0.00,674100.00,1202710.00,'2026-06-17 17:24:33','pdf_import'),(565,NULL,11,2569,2129000.00,'2025-12-30',2129000.00,0.00,252190.00,0.00,674100.00,1202710.00,'2026-06-17 17:24:33','pdf_import'),(566,NULL,11,2569,2129000.00,'2026-01-12',2129000.00,0.00,415795.00,0.00,606690.00,1106515.00,'2026-06-17 17:24:34','pdf_import'),(567,NULL,11,2569,2129000.00,'2026-01-26',2129000.00,0.00,416495.00,0.00,606690.00,1105815.00,'2026-06-17 17:24:34','pdf_import'),(568,NULL,11,2569,2129000.00,'2026-02-23',2129000.00,12840.00,556505.00,0.00,621670.00,963665.00,'2026-06-17 17:24:34','pdf_import'),(569,NULL,11,2569,2129000.00,'2026-05-25',2129000.00,-4372.00,1109879.50,0.00,337050.00,677698.50,'2026-06-17 17:24:34','pdf_import'),(570,NULL,11,2569,2129000.00,'2026-05-29',2129000.00,-4372.00,1109879.50,0.00,337050.00,677698.50,'2026-06-17 17:24:34','pdf_import'),(571,NULL,8,2568,1112300.00,'2024-10-28',1112300.00,0.00,2500.00,0.00,0.00,1109800.00,'2026-06-17 17:24:35','pdf_import'),(572,NULL,8,2568,1112300.00,'2024-10-31',1112300.00,0.00,2500.00,0.00,0.00,1109800.00,'2026-06-17 17:24:35','pdf_import'),(573,NULL,8,2568,1112300.00,'2024-11-29',1112300.00,0.00,54090.00,143146.40,0.00,915063.60,'2026-06-17 17:24:35','pdf_import'),(574,NULL,8,2568,1112300.00,'2024-12-23',1112300.00,0.00,42144.00,0.00,136125.40,934030.60,'2026-06-17 17:24:35','pdf_import'),(575,NULL,8,2568,1112300.00,'2025-01-13',1112300.00,0.00,48144.00,200000.00,136125.40,728030.60,'2026-06-17 17:24:35','pdf_import'),(576,NULL,8,2568,1112300.00,'2025-02-10',1112300.00,0.00,257471.40,0.00,200000.00,654828.60,'2026-06-17 17:24:36','pdf_import'),(577,NULL,8,2568,1112300.00,'2025-02-24',1112300.00,0.00,252436.40,10500.00,200000.00,649363.60,'2026-06-17 17:24:36','pdf_import'),(578,NULL,8,2568,1112300.00,'2025-05-30',1112300.00,43847.00,774961.45,0.00,0.00,381185.55,'2026-06-17 17:24:36','pdf_import'),(579,NULL,8,2568,1112300.00,'2025-07-14',1112300.00,98851.00,781571.45,0.00,0.00,429579.55,'2026-06-17 17:24:36','pdf_import'),(580,NULL,8,2568,1112300.00,'2025-07-21',1112300.00,41481.00,781571.45,0.00,0.00,372209.55,'2026-06-17 17:24:36','pdf_import'),(581,NULL,8,2568,1112300.00,'2025-07-31',1112300.00,41481.00,802871.45,0.00,0.00,350909.55,'2026-06-17 17:24:36','pdf_import'),(582,NULL,8,2568,1112300.00,'2025-08-13',1112300.00,41481.00,820160.45,0.00,0.00,333620.55,'2026-06-17 17:24:37','pdf_import'),(583,NULL,8,2568,1112300.00,'2025-08-18',1112300.00,41481.00,820160.45,135.00,0.00,333485.55,'2026-06-17 17:24:37','pdf_import'),(584,NULL,8,2568,1112300.00,'2025-08-25',1112300.00,31181.00,831695.45,0.00,0.00,311785.55,'2026-06-17 17:24:37','pdf_import'),(585,NULL,8,2568,1112300.00,'2025-08-29',1112300.00,-63819.00,922362.45,6350.00,0.00,119768.55,'2026-06-17 17:24:37','pdf_import'),(586,NULL,8,2568,1112300.00,'2025-09-15',1112300.00,-75995.55,953345.45,0.00,0.00,82959.00,'2026-06-17 17:24:37','pdf_import'),(587,NULL,8,2568,1112300.00,'2025-09-22',1112300.00,-62745.55,953345.45,0.00,0.00,96209.00,'2026-06-17 17:24:37','pdf_import'),(588,NULL,8,2568,1112300.00,'2025-09-30',1112300.00,-65255.55,1027025.45,0.00,0.00,20019.00,'2026-06-17 17:24:38','pdf_import'),(589,NULL,8,2569,1198500.00,'2025-10-31',916700.00,0.00,3000.00,31443.00,0.00,882257.00,'2026-06-17 17:24:38','pdf_import'),(590,NULL,8,2569,1198500.00,'2025-11-10',916700.00,0.00,3000.00,31443.00,0.00,882257.00,'2026-06-17 17:24:38','pdf_import'),(591,NULL,8,2569,1198500.00,'2025-11-17',916700.00,0.00,8925.00,203700.90,31443.00,672631.10,'2026-06-17 17:24:38','pdf_import'),(592,NULL,8,2569,1198500.00,'2025-11-24',916700.00,0.00,44118.00,0.00,199950.90,672631.10,'2026-06-17 17:24:38','pdf_import'),(593,NULL,8,2569,1198500.00,'2025-11-28',916700.00,0.00,46218.00,0.00,199950.90,670531.10,'2026-06-17 17:24:39','pdf_import'),(594,NULL,8,2569,1198500.00,'2025-12-22',916700.00,0.00,55793.00,0.00,199950.90,660956.10,'2026-06-17 17:24:39','pdf_import'),(595,NULL,8,2569,1198500.00,'2025-12-30',916700.00,-9057.00,55793.00,0.00,199950.90,651899.10,'2026-06-17 17:24:39','pdf_import'),(596,NULL,8,2569,1198500.00,'2026-01-12',916700.00,-9057.00,256873.90,0.00,0.00,650769.10,'2026-06-17 17:24:39','pdf_import'),(597,NULL,8,2569,1198500.00,'2026-01-26',916700.00,-9057.00,269623.90,0.00,200000.00,438019.10,'2026-06-17 17:24:40','pdf_import'),(598,NULL,8,2569,1198500.00,'2026-02-23',916700.00,-9057.00,287383.90,0.00,200000.00,420259.10,'2026-06-17 17:24:40','pdf_import'),(599,NULL,8,2569,1198500.00,'2026-05-25',1198500.00,5893.90,713359.71,0.00,0.00,491034.19,'2026-06-17 17:24:40','pdf_import'),(600,NULL,8,2569,1198500.00,'2026-05-29',1198500.00,5893.90,713359.71,0.00,0.00,491034.19,'2026-06-17 17:24:40','pdf_import'),(601,NULL,39,2568,4430039.00,'2024-10-28',2052334.00,-136030.00,0.00,0.00,0.00,1916304.00,'2026-06-17 17:24:40','pdf_import'),(602,NULL,39,2568,4430039.00,'2024-10-31',2052334.00,-1417005.45,0.00,0.00,0.00,635328.55,'2026-06-17 17:24:41','pdf_import'),(603,NULL,39,2568,4430039.00,'2024-11-29',2052334.00,-1505355.45,0.00,0.00,0.00,546978.55,'2026-06-17 17:24:41','pdf_import'),(604,NULL,39,2568,4430039.00,'2024-12-23',2052334.00,-1848305.45,0.00,0.00,0.00,204028.55,'2026-06-17 17:24:41','pdf_import'),(605,NULL,39,2568,4430039.00,'2025-01-13',2052334.00,-1850087.45,0.00,0.00,0.00,202246.55,'2026-06-17 17:24:41','pdf_import'),(606,NULL,39,2568,4430039.00,'2025-02-10',2052334.00,-1904889.20,0.00,0.00,0.00,147444.80,'2026-06-17 17:24:41','pdf_import'),(607,NULL,39,2568,4430039.00,'2025-02-24',2052334.00,-1904889.20,0.00,0.00,0.00,147444.80,'2026-06-17 17:24:41','pdf_import'),(608,NULL,39,2568,4430039.00,'2025-05-30',4430039.00,-3984636.55,0.00,0.00,0.00,445402.45,'2026-06-17 17:24:42','pdf_import'),(609,NULL,39,2568,4430039.00,'2025-07-14',4430039.00,-4064803.20,0.00,0.00,0.00,365235.80,'2026-06-17 17:24:42','pdf_import'),(610,NULL,39,2568,4430039.00,'2025-07-21',4430039.00,-3958311.20,0.00,0.00,0.00,471727.80,'2026-06-17 17:24:42','pdf_import'),(611,NULL,39,2568,4430039.00,'2025-07-31',4430039.00,-3958311.20,0.00,0.00,0.00,471727.80,'2026-06-17 17:24:42','pdf_import'),(612,NULL,39,2568,4430039.00,'2025-08-13',4430039.00,-3958311.20,0.00,0.00,0.00,471727.80,'2026-06-17 17:24:42','pdf_import'),(613,NULL,39,2568,4430039.00,'2025-08-18',4430039.00,-3958311.20,0.00,0.00,0.00,471727.80,'2026-06-17 17:24:43','pdf_import'),(614,NULL,39,2568,4430039.00,'2025-08-25',4430039.00,-4079944.20,0.00,0.00,0.00,350094.80,'2026-06-17 17:24:43','pdf_import'),(615,NULL,39,2568,4430039.00,'2025-08-29',4430039.00,-3694365.62,0.00,0.00,0.00,735673.38,'2026-06-17 17:24:43','pdf_import'),(616,NULL,39,2568,4430039.00,'2025-09-15',4430039.00,-4413728.95,0.00,0.00,0.00,16310.05,'2026-06-17 17:24:43','pdf_import'),(617,NULL,39,2568,4430039.00,'2025-09-22',4430039.00,-4426978.95,0.00,0.00,0.00,3060.05,'2026-06-17 17:24:44','pdf_import'),(618,NULL,39,2568,4430039.00,'2025-09-30',4430039.00,-4430038.01,0.00,0.00,0.00,0.99,'2026-06-17 17:24:44','pdf_import'),(619,NULL,39,2569,1194519.00,'2025-10-31',1021980.00,0.00,0.00,0.00,0.00,1021980.00,'2026-06-17 17:24:44','pdf_import'),(620,NULL,39,2569,1194519.00,'2025-11-10',1021980.00,-430000.00,0.00,332500.00,0.00,259480.00,'2026-06-17 17:24:44','pdf_import'),(621,NULL,39,2569,1194519.00,'2025-11-17',1021980.00,-255418.15,0.00,332500.00,0.00,434061.85,'2026-06-17 17:24:44','pdf_import'),(622,NULL,39,2569,1194519.00,'2025-11-24',1021980.00,-93018.15,0.00,332500.00,0.00,596461.85,'2026-06-17 17:24:44','pdf_import'),(623,NULL,39,2569,1194519.00,'2025-11-28',1021980.00,-93018.15,0.00,332500.00,0.00,596461.85,'2026-06-17 17:24:45','pdf_import'),(624,NULL,39,2569,1194519.00,'2025-12-22',1021980.00,-234133.15,0.00,332500.00,0.00,455346.85,'2026-06-17 17:24:45','pdf_import'),(625,NULL,39,2569,1194519.00,'2025-12-30',1021980.00,5969247.85,0.00,332500.00,0.00,6658727.85,'2026-06-17 17:24:45','pdf_import'),(626,NULL,39,2569,1194519.00,'2026-01-12',1021980.00,5760755.15,0.00,0.00,0.00,6782735.15,'2026-06-17 17:24:45','pdf_import'),(627,NULL,39,2569,1194519.00,'2026-01-26',1021980.00,5760755.15,0.00,0.00,0.00,6782735.15,'2026-06-17 17:24:45','pdf_import'),(628,NULL,39,2569,1194519.00,'2026-02-23',1021980.00,5307155.15,0.00,0.00,0.00,6329135.15,'2026-06-17 17:24:46','pdf_import'),(629,NULL,39,2569,1194519.00,'2026-05-25',1194519.00,1473640.24,0.00,0.00,0.00,2668159.24,'2026-06-17 17:24:46','pdf_import'),(630,NULL,39,2569,1194519.00,'2026-05-29',1194519.00,1473640.24,0.00,0.00,0.00,2668159.24,'2026-06-17 17:24:46','pdf_import'),(631,NULL,10,2568,8199000.00,'2024-10-28',3855700.00,0.00,0.00,0.00,3492.48,3852207.52,'2026-06-17 17:24:46','pdf_import'),(632,NULL,10,2568,8199000.00,'2024-10-31',3855700.00,0.00,0.00,0.00,3492.48,3852207.52,'2026-06-17 17:24:46','pdf_import'),(633,NULL,10,2568,8199000.00,'2024-11-29',3855700.00,0.00,272428.33,50.00,3492.48,3579729.19,'2026-06-17 17:24:46','pdf_import'),(634,NULL,10,2568,8199000.00,'2024-12-23',3855700.00,0.00,539406.66,0.00,3492.48,3312800.86,'2026-06-17 17:24:47','pdf_import'),(635,NULL,10,2568,8199000.00,'2025-01-13',3855700.00,0.00,820084.99,0.00,3492.48,3032122.53,'2026-06-17 17:24:47','pdf_import'),(636,NULL,10,2568,8199000.00,'2025-02-10',3855700.00,0.00,1083230.63,50235.43,2728.50,2719505.44,'2026-06-17 17:24:47','pdf_import'),(637,NULL,10,2568,8199000.00,'2025-02-24',3855700.00,0.00,1083230.63,50235.43,2728.50,2719505.44,'2026-06-17 17:24:47','pdf_import'),(638,NULL,10,2568,8199000.00,'2025-05-30',8199000.00,0.00,1859987.79,2044500.00,2396580.76,1897931.45,'2026-06-17 17:24:47','pdf_import'),(639,NULL,10,2568,8199000.00,'2025-07-14',8199000.00,0.00,2808064.45,2044500.00,1922654.10,1423781.45,'2026-06-17 17:24:47','pdf_import'),(640,NULL,10,2568,8199000.00,'2025-07-21',8199000.00,0.00,2808064.45,2044500.00,1922654.10,1423781.45,'2026-06-17 17:24:47','pdf_import'),(641,NULL,10,2568,8199000.00,'2025-07-31',8199000.00,0.00,3058573.77,2044500.00,1672144.78,1423781.45,'2026-06-17 17:24:48','pdf_import'),(642,NULL,10,2568,8199000.00,'2025-08-13',8199000.00,0.00,3293173.77,2044500.00,1672144.78,1189181.45,'2026-06-17 17:24:48','pdf_import'),(643,NULL,10,2568,8199000.00,'2025-08-18',8199000.00,0.00,3293173.77,2044500.00,1672144.78,1189181.45,'2026-06-17 17:24:48','pdf_import'),(644,NULL,10,2568,8199000.00,'2025-08-25',8199000.00,0.00,3293464.81,2044500.00,1671853.74,1189181.45,'2026-06-17 17:24:48','pdf_import'),(645,NULL,10,2568,8199000.00,'2025-08-29',8199000.00,0.00,3293464.81,2044500.00,1671853.74,1189181.45,'2026-06-17 17:24:48','pdf_import'),(646,NULL,10,2568,8199000.00,'2025-09-15',8199000.00,-847380.00,3510604.81,0.00,3451853.74,389161.45,'2026-06-17 17:24:49','pdf_import'),(647,NULL,10,2568,8199000.00,'2025-09-22',8199000.00,-847380.00,3510859.47,0.00,3451599.08,389161.45,'2026-06-17 17:24:49','pdf_import'),(648,NULL,10,2568,8199000.00,'2025-09-30',8199000.00,-1028891.45,4473467.46,0.00,2693469.42,3171.67,'2026-06-17 17:24:49','pdf_import'),(649,NULL,10,2569,8199000.00,'2025-10-31',4099500.00,0.00,0.00,0.00,0.00,4099500.00,'2026-06-17 17:24:49','pdf_import'),(650,NULL,10,2569,8199000.00,'2025-11-10',4099500.00,0.00,189743.00,0.00,0.00,3909757.00,'2026-06-17 17:24:49','pdf_import'),(651,NULL,10,2569,8199000.00,'2025-11-17',4099500.00,0.00,189743.00,499590.00,0.00,3410167.00,'2026-06-17 17:24:49','pdf_import'),(652,NULL,10,2569,8199000.00,'2025-11-24',4099500.00,0.00,189743.00,499590.00,0.00,3410167.00,'2026-06-17 17:24:49','pdf_import'),(653,NULL,10,2569,8199000.00,'2025-11-28',4099500.00,0.00,189743.00,0.00,499590.00,3410167.00,'2026-06-17 17:24:50','pdf_import'),(654,NULL,10,2569,8199000.00,'2025-12-22',4099500.00,0.00,383489.00,2426980.00,499590.00,789441.00,'2026-06-17 17:24:50','pdf_import'),(655,NULL,10,2569,8199000.00,'2025-12-30',4099500.00,0.00,383489.00,2426980.00,499590.00,789441.00,'2026-06-17 17:24:50','pdf_import'),(656,NULL,10,2569,8199000.00,'2026-01-12',4099500.00,0.00,574069.00,2426980.00,499590.00,598861.00,'2026-06-17 17:24:50','pdf_import'),(657,NULL,10,2569,8199000.00,'2026-01-26',4099500.00,0.00,574069.00,2426980.00,499590.00,598861.00,'2026-06-17 17:24:50','pdf_import'),(658,NULL,10,2569,8199000.00,'2026-02-23',4099500.00,0.00,766373.00,0.00,2923590.00,409537.00,'2026-06-17 17:24:51','pdf_import'),(659,NULL,10,2569,8199000.00,'2026-05-25',6285800.00,0.00,3299947.50,0.00,969600.00,2016252.50,'2026-06-17 17:24:51','pdf_import'),(660,NULL,10,2569,8199000.00,'2026-05-29',6285800.00,0.00,3299947.50,0.00,969600.00,2016252.50,'2026-06-17 17:24:51','pdf_import');
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
  `record_period` enum('beginning','mid','end') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'beginning' COMMENT 'ช่วงเวลา: ต้นเดือน/กลางเดือน/ปลายเดือน',
  `transfer_allocation` decimal(15,2) DEFAULT NULL,
  `spent_amount` decimal(15,2) DEFAULT NULL,
  `request_amount` decimal(15,2) DEFAULT NULL,
  `po_amount` decimal(15,2) DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'หมายเหตุ',
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
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int DEFAULT NULL COMMENT 'ขั้นที่ดำเนินการ',
  `user_id` int NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,2) DEFAULT '0.00',
  `unit_price` decimal(15,2) DEFAULT '0.00',
  `amount` decimal(15,2) DEFAULT '0.00',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_request_id` (`budget_request_id`),
  KEY `idx_category_item` (`category_item_id`),
  CONSTRAINT `budget_request_items_ibfk_1` FOREIGN KEY (`budget_request_id`) REFERENCES `budget_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `request_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธเธทเนเธญเธเธณเธเธญเธเธเธเธฃเธฐเธกเธฒเธ',
  `request_status` enum('draft','saved','confirmed','pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `current_level` int DEFAULT NULL COMMENT 'ขั้นอนุมัติที่รออยู่ (NULL=ยังไม่เข้า chain)',
  `total_amount` decimal(15,2) DEFAULT NULL,
  `created_by` int NOT NULL COMMENT 'เธเธนเนเธชเธฃเนเธฒเธเธเธณเธเธญ',
  `org_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'เธงเธฑเธเธเธตเนเธชเธฃเนเธฒเธ',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'เธงเธฑเธเธเธตเนเธญเธฑเธเนเธเธ',
  `submitted_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเธเธตเนเธชเนเธเธญเธเธธเธกเธฑเธเธด',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเธเธตเนเธญเธเธธเธกเธฑเธเธด',
  `rejected_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเธเธตเนเนเธกเนเธญเธเธธเธกเธฑเธเธด',
  `rejected_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'เนเธซเธเธธเธเธฅเธเธตเนเนเธกเนเธญเธเธธเธกเธฑเธเธด',
  PRIMARY KEY (`id`),
  KEY `idx_fiscal_year` (`fiscal_year`),
  KEY `idx_request_status` (`request_status`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_dates` (`created_at`,`submitted_at`,`approved_at`),
  KEY `fk_budget_requests_org_id` (`org_id`),
  CONSTRAINT `budget_requests_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_budget_requests_org_id` FOREIGN KEY (`org_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=504 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธฒเธฃเธฒเธเธเธณเธเธญเธเธเธเธฃเธฐเธกเธฒเธเธฃเธฒเธขเธเธต';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_requests`
--

LOCK TABLES `budget_requests` WRITE;
/*!40000 ALTER TABLE `budget_requests` DISABLE KEYS */;
INSERT INTO `budget_requests` VALUES (5,2568,'Draft Request - Office Supplies','saved',NULL,50000.00,5,NULL,'2025-12-14 10:22:18','2026-01-11 14:30:38',NULL,NULL,NULL,NULL),(289,2569,'คำของบบุคลากร-2569','saved',NULL,6006020.00,2,3,'2026-01-10 09:13:36','2026-01-13 12:55:54',NULL,NULL,NULL,NULL),(290,2570,'คำของบบุคลากร-2570','draft',NULL,0.00,2,3,'2026-01-11 15:07:08','2026-01-11 15:07:08',NULL,NULL,NULL,NULL),(291,2570,'คำของบบุคลากร-2570','draft',NULL,0.00,2,3,'2026-01-11 15:38:15','2026-01-11 15:38:15',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `budget_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_targets`
--

DROP TABLE IF EXISTS `budget_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_targets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `target_type_id` int NOT NULL,
  `fiscal_year` int NOT NULL,
  `quarter` int DEFAULT NULL COMMENT 'NULL=เป้าหมายรายปี, 1-4=ไตรมาส',
  `organization_id` int DEFAULT NULL COMMENT 'NULL=ทุกหน่วยงาน',
  `category_id` int DEFAULT NULL COMMENT 'NULL=ทุกหมวดหมู่',
  `target_percent` decimal(5,2) DEFAULT NULL COMMENT 'เป้าหมาย %',
  `target_amount` decimal(15,2) DEFAULT NULL COMMENT 'เป้าหมายจำนวนเงิน',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_budget_targets` (`target_type_id`,`fiscal_year`,`quarter`,`organization_id`,`category_id`),
  KEY `idx_budget_targets_year` (`fiscal_year`),
  KEY `fk_budget_targets_org` (`organization_id`),
  KEY `fk_budget_targets_category` (`category_id`),
  KEY `fk_budget_targets_user` (`created_by`),
  CONSTRAINT `fk_budget_targets_category` FOREIGN KEY (`category_id`) REFERENCES `budget_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budget_targets_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budget_targets_type` FOREIGN KEY (`target_type_id`) REFERENCES `target_types` (`id`),
  CONSTRAINT `fk_budget_targets_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เป้าหมายงบประมาณ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_targets`
--

LOCK TABLES `budget_targets` WRITE;
/*!40000 ALTER TABLE `budget_targets` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_targets` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `transaction_type` enum('allocation','expenditure','transfer_in','transfer_out','adjustment') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reference_document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_transactions`
--

LOCK TABLES `budget_transactions` WRITE;
/*!40000 ALTER TABLE `budget_transactions` DISABLE KEYS */;
INSERT INTO `budget_transactions` VALUES (1,1,'expenditure',1000.00,'ทดสอบ',NULL,1,'2025-12-13 04:18:05'),(2,1,'expenditure',5000.00,'ทดสอบ ปีงบ 2569',NULL,1,'2025-10-15 04:18:05');
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
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `status` enum('draft','submitted','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `status` enum('draft','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
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
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `districts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `province_id` int NOT NULL COMMENT 'FK: provinces.id',
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'รหัสอำเภอมาตรฐาน 4 หลัก (2 หลักแรก = รหัสจังหวัด)',
  `name_th` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0' COMMENT 'ลำดับตามรหัส 2 หลักท้าย (เมือง = 1)',
  `is_active` tinyint(1) DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'ผู้สร้าง',
  `updated_by` int DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_districts_code` (`code`),
  KEY `idx_districts_province` (`province_id`),
  KEY `idx_districts_deleted` (`deleted_at`),
  CONSTRAINT `fk_districts_province` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1024 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='อำเภอ/เขต';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--

LOCK TABLES `districts` WRITE;
/*!40000 ALTER TABLE `districts` DISABLE KEYS */;
INSERT INTO `districts` VALUES (1,1,'1001','พระนคร','Phra Nakhon',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(2,1,'1002','ดุสิต','Dusit',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(3,1,'1003','หนองจอก','Nong Chok',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(4,1,'1004','บางรัก','Bang Rak',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(5,1,'1005','บางเขน','Bang Khen',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(6,1,'1006','บางกะปิ','Bang Kapi',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(7,1,'1007','ปทุมวัน','Pathum Wan',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(8,1,'1008','ป้อมปราบศัตรูพ่าย','Pom Prap Sattru Phai',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(9,1,'1009','พระโขนง','Phra Khanong',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(10,1,'1010','มีนบุรี','Min Buri',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(11,1,'1011','ลาดกระบัง','Lat Krabang',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(12,1,'1012','ยานนาวา','Yan Nawa',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(13,1,'1013','สัมพันธวงศ์','Samphanthawong',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(14,1,'1014','พญาไท','Phaya Thai',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(15,1,'1015','ธนบุรี','Thon Buri',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(16,1,'1016','บางกอกใหญ่','Bangkok Yai',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(17,1,'1017','ห้วยขวาง','Huai Khwang',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(18,1,'1018','คลองสาน','Khlong San',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(19,1,'1019','ตลิ่งชัน','Taling Chan',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(20,1,'1020','บางกอกน้อย','Bangkok Noi',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(21,1,'1021','บางขุนเทียน','Bang Khun Thian',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(22,1,'1022','ภาษีเจริญ','Phasi Charoen',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(23,1,'1023','หนองแขม','Nong Khaem',23,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(24,1,'1024','ราษฎร์บูรณะ','Rat Burana',24,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(25,1,'1025','บางพลัด','Bang Phlat',25,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(26,1,'1026','ดินแดง','Din Daeng',26,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(27,1,'1027','บึงกุ่ม','Bueng Kum',27,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(28,1,'1028','สาทร','Sathon',28,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(29,1,'1029','บางซื่อ','Bang Sue',29,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(30,1,'1030','จตุจักร','Chatuchak',30,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(31,1,'1031','บางคอแหลม','Bang Kho Laem',31,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(32,1,'1032','ประเวศ','Prawet',32,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(33,1,'1033','คลองเตย','Khlong Toei',33,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(34,1,'1034','สวนหลวง','Suan Luang',34,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(35,1,'1035','จอมทอง','Chom Thong',35,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(36,1,'1036','ดอนเมือง','Don Mueang',36,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(37,1,'1037','ราชเทวี','Ratchathewi',37,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(38,1,'1038','ลาดพร้าว','Lat Phrao',38,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(39,1,'1039','วัฒนา','Vadhana',39,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(40,1,'1040','บางแค','Bang Khae',40,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(41,1,'1041','หลักสี่','Lak Si',41,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(42,1,'1042','สายไหม','Sai Mai',42,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(43,1,'1043','คันนายาว','Khan Na Yao',43,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(44,1,'1044','สะพานสูง','Saphan Sung',44,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(45,1,'1045','วังทองหลาง','Wang Thonglang',45,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(46,1,'1046','คลองสามวา','Khlong Sam Wa',46,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(47,1,'1047','บางนา','Bang Na',47,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(48,1,'1048','ทวีวัฒนา','Thawi Watthana',48,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(49,1,'1049','ทุ่งครุ','Thung Khru',49,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(50,1,'1050','บางบอน','Bang Bon',50,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(51,154,'1101','เมืองสมุทรปราการ','Mueang Samut Prakan',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(52,154,'1102','บางบ่อ','Bang Bo',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(53,154,'1103','บางพลี','Bang Phli',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(54,154,'1104','พระประแดง','Phra Pradaeng',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(55,154,'1105','พระสมุทรเจดีย์','Phra Samut Chedi',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(56,154,'1106','บางเสาธง','Bang Sao Thong',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(57,155,'1201','เมืองนนทบุรี','Mueang Nonthaburi',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(58,155,'1202','บางกรวย','Bang Kruai',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(59,155,'1203','บางใหญ่','Bang Yai',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(60,155,'1204','บางบัวทอง','Bang Bua Thong',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(61,155,'1205','ไทรน้อย','Sai Noi',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(62,155,'1206','ปากเกร็ด','Pak Kret',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(63,156,'1301','เมืองปทุมธานี','Mueang Pathum Thani',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(64,156,'1302','คลองหลวง','Khlong Luang',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(65,156,'1303','ธัญบุรี','Thanyaburi',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(66,156,'1304','หนองเสือ','Nong Suea',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(67,156,'1305','ลาดหลุมแก้ว','Lat Lum Kaeo',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(68,156,'1306','ลำลูกกา','Lam Luk Ka',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(69,156,'1307','สามโคก','Sam Khok',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(70,157,'1401','พระนครศรีอยุธยา','Phra Nakhon Si Ayutthaya',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(71,157,'1402','ท่าเรือ','Tha Ruea',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(72,157,'1403','นครหลวง','Nakhon Luang',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(73,157,'1404','บางไทร','Bang Sai',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(74,157,'1405','บางบาล','Bang Ban',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(75,157,'1406','บางปะอิน','Bang Pa-in',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(76,157,'1407','บางปะหัน','Bang Pahan',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(77,157,'1408','ผักไห่','Phak Hai',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(78,157,'1409','ภาชี','Phachi',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(79,157,'1410','ลาดบัวหลวง','Lat Bua Luang',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(80,157,'1411','วังน้อย','Wang Noi',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(81,157,'1412','เสนา','Sena',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(82,157,'1413','บางซ้าย','Bang Sai',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(83,157,'1414','อุทัย','Uthai',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(84,157,'1415','มหาราช','Maha Rat',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(85,157,'1416','บ้านแพรก','Ban Phraek',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(86,158,'1501','เมืองอ่างทอง','Mueang Ang Thong',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(87,158,'1502','ไชโย','Chaiyo',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(88,158,'1503','ป่าโมก','Pa Mok',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(89,158,'1504','โพธิ์ทอง','Pho Thong',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(90,158,'1505','แสวงหา','Sawaeng Ha',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(91,158,'1506','วิเศษชัยชาญ','Wiset Chai Chan',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(92,158,'1507','สามโก้','Samko',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(93,159,'1601','เมืองลพบุรี','Mueang Lop Buri',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(94,159,'1602','พัฒนานิคม','Phatthana Nikhom',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(95,159,'1603','โคกสำโรง','Khok Samrong',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(96,159,'1604','ชัยบาดาล','Chai Badan',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(97,159,'1605','ท่าวุ้ง','Tha Wung',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(98,159,'1606','บ้านหมี่','Ban Mi',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(99,159,'1607','ท่าหลวง','Tha Luang',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(100,159,'1608','สระโบสถ์','Sa Bot',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(101,159,'1609','โคกเจริญ','Khok Charoen',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(102,159,'1610','ลำสนธิ','Lam Sonthi',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(103,159,'1611','หนองม่วง','Nong Muang',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(104,160,'1701','เมืองสิงห์บุรี','Mueang Sing Buri',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(105,160,'1702','บางระจัน','Bang Rachan',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(106,160,'1703','ค่ายบางระจัน','Khai Bang Rachan',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(107,160,'1704','พรหมบุรี','Phrom Buri',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(108,160,'1705','ท่าช้าง','Tha Chang',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(109,160,'1706','อินทร์บุรี','In Buri',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(110,161,'1801','เมืองชัยนาท','Mueang Chai Nat',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(111,161,'1802','มโนรมย์','Manorom',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(112,161,'1803','วัดสิงห์','Wat Sing',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(113,161,'1804','สรรพยา','Sapphaya',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(114,161,'1805','สรรคบุรี','Sankhaburi',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(115,161,'1806','หันคา','Hankha',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(116,161,'1807','หนองมะโมง','Nong Mamong',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(117,161,'1808','เนินขาม','Noen Kham',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(118,162,'1901','เมืองสระบุรี','Mueang Saraburi',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(119,162,'1902','แก่งคอย','Kaeng Khoi',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(120,162,'1903','หนองแค','Nong Khae',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(121,162,'1904','วิหารแดง','Wihan Daeng',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(122,162,'1905','หนองแซง','Nong Saeng',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(123,162,'1906','บ้านหมอ','Ban Mo',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(124,162,'1907','ดอนพุด','Don Phut',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(125,162,'1908','หนองโดน','Nong Don',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(126,162,'1909','พระพุทธบาท','Phra Phutthabat',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(127,162,'1910','เสาไห้','Sao Hai',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(128,162,'1911','มวกเหล็ก','Muak Lek',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(129,162,'1912','วังม่วง','Wang Muang',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(130,162,'1913','เฉลิมพระเกียรติ','Chaloem Phra Kiat',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(131,163,'2001','เมืองชลบุรี','Mueang Chon Buri',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(132,163,'2002','บ้านบึง','Ban Bueng',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(133,163,'2003','หนองใหญ่','Nong Yai',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(134,163,'2004','บางละมุง','Bang Lamung',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(135,163,'2005','พานทอง','Phan Thong',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(136,163,'2006','พนัสนิคม','Phanat Nikhom',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(137,163,'2007','ศรีราชา','Si Racha',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(138,163,'2008','เกาะสีชัง','Ko Sichang',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(139,163,'2009','สัตหีบ','Sattahip',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(140,163,'2010','บ่อทอง','Bo Thong',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(141,163,'2011','เกาะจันทร์','Ko Chan',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(142,164,'2101','เมืองระยอง','Mueang Rayong',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(143,164,'2102','บ้านฉาง','Ban Chang',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(144,164,'2103','แกลง','Klaeng',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(145,164,'2104','วังจันทร์','Wang Chan',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(146,164,'2105','บ้านค่าย','Ban Khai',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(147,164,'2106','ปลวกแดง','Pluak Daeng',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(148,164,'2107','เขาชะเมา','Khao Chamao',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(149,164,'2108','นิคมพัฒนา','Nikhom Phatthana',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(150,165,'2201','เมืองจันทบุรี','Mueang Chanthaburi',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(151,165,'2202','ขลุง','Khlung',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(152,165,'2203','ท่าใหม่','Tha Mai',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(153,165,'2204','โป่งน้ำร้อน','Pong Nam Ron',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(154,165,'2205','มะขาม','Makham',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(155,165,'2206','แหลมสิงห์','Laem Sing',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(156,165,'2207','สอยดาว','Soi Dao',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(157,165,'2208','แก่งหางแมว','Kaeng Hang Maeo',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(158,165,'2209','นายายอาม','Na Yai Am',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(159,165,'2210','เขาคิชฌกูฏ','Khao Khitchakut',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(160,166,'2301','เมืองตราด','Mueang Trat',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(161,166,'2302','คลองใหญ่','Khlong Yai',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(162,166,'2303','เขาสมิง','Khao Saming',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(163,166,'2304','บ่อไร่','Bo Rai',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(164,166,'2305','แหลมงอบ','Laem Ngop',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(165,166,'2306','เกาะกูด','Ko Kut',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(166,166,'2307','เกาะช้าง','Ko Chang',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(167,167,'2401','เมืองฉะเชิงเทรา','Mueang Chachoengsao',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(168,167,'2402','บางคล้า','Bang Khla',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(169,167,'2403','บางน้ำเปรี้ยว','Bang Nam Priao',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(170,167,'2404','บางปะกง','Bang Pakong',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(171,167,'2405','บ้านโพธิ์','Ban Pho',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(172,167,'2406','พนมสารคาม','Phanom Sarakham',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(173,167,'2407','ราชสาส์น','Ratchasan',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(174,167,'2408','สนามชัยเขต','Sanam Chai Khet',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(175,167,'2409','แปลงยาว','Plaeng Yao',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(176,167,'2410','ท่าตะเกียบ','Tha Takiap',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(177,167,'2411','คลองเขื่อน','Khlong Khuean',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(178,168,'2501','เมืองปราจีนบุรี','Mueang Prachin Buri',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(179,168,'2502','กบินทร์บุรี','Kabin Buri',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(180,168,'2503','นาดี','Na Di',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(181,168,'2506','บ้านสร้าง','Ban Sang',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(182,168,'2507','ประจันตคาม','Prachantakham',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(183,168,'2508','ศรีมหาโพธิ','Si Maha Phot',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(184,168,'2509','ศรีมโหสถ','Si Mahosot',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(185,169,'2601','เมืองนครนายก','Mueang Nakhon Nayok',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(186,169,'2602','ปากพลี','Pak Phli',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(187,169,'2603','บ้านนา','Ban Na',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(188,169,'2604','องครักษ์','Ongkharak',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(189,170,'2701','เมืองสระแก้ว','Mueang Sa Kaeo',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(190,170,'2702','คลองหาด','Khlong Hat',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(191,170,'2703','ตาพระยา','Ta Phraya',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(192,170,'2704','วังน้ำเย็น','Wang Nam Yen',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(193,170,'2705','วัฒนานคร','Watthana Nakhon',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(194,170,'2706','อรัญประเทศ','Aranyaprathet',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(195,170,'2707','เขาฉกรรจ์','Khao Chakan',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(196,170,'2708','โคกสูง','Khok Sung',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(197,170,'2709','วังสมบูรณ์','Wang Sombun',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(198,171,'3001','เมืองนครราชสีมา','Mueang Nakhon Ratchasima',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(199,171,'3002','ครบุรี','Khon Buri',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(200,171,'3003','เสิงสาง','Soeng Sang',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(201,171,'3004','คง','Khong',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(202,171,'3005','บ้านเหลื่อม','Ban Lueam',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(203,171,'3006','จักราช','Chakkarat',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(204,171,'3007','โชคชัย','Chok Chai',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(205,171,'3008','ด่านขุนทด','Dan Khun Thot',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(206,171,'3009','โนนไทย','Non Thai',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(207,171,'3010','โนนสูง','Non Sung',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(208,171,'3011','ขามสะแกแสง','Kham Sakaesaeng',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(209,171,'3012','บัวใหญ่','Bua Yai',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(210,171,'3013','ประทาย','Prathai',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(211,171,'3014','ปักธงชัย','Pak Thong Chai',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(212,171,'3015','พิมาย','Phimai',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(213,171,'3016','ห้วยแถลง','Huai Thalaeng',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(214,171,'3017','ชุมพวง','Chum Phuang',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(215,171,'3018','สูงเนิน','Sung Noen',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(216,171,'3019','ขามทะเลสอ','Kham Thale So',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(217,171,'3020','สีคิ้ว','Sikhio',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(218,171,'3021','ปากช่อง','Pak Chong',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(219,171,'3022','หนองบุญมาก','Nong Bunmak',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(220,171,'3023','แก้งสนามนาง','Kaeng Sanam Nang',23,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(221,171,'3024','โนนแดง','Non Daeng',24,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(222,171,'3025','วังน้ำเขียว','Wang Nam Khiao',25,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(223,171,'3026','เทพารักษ์','Thepharak',26,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(224,171,'3027','เมืองยาง','Mueang Yang',27,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(225,171,'3028','พระทองคำ','Phra Thong Kham',28,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(226,171,'3029','ลำทะเมนชัย','Lam Thamenchai',29,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(227,171,'3030','บัวลาย','Bua Lai',30,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(228,171,'3031','สีดา','Sida',31,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(229,171,'3032','เฉลิมพระเกียรติ','Chaloem Phra Kiat',32,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(230,172,'3101','เมืองบุรีรัมย์','Mueang Buri Ram',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(231,172,'3102','คูเมือง','Khu Mueang',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(232,172,'3103','กระสัง','Krasang',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(233,172,'3104','นางรอง','Nang Rong',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(234,172,'3105','หนองกี่','Nong Ki',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(235,172,'3106','ละหานทราย','Lahan Sai',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(236,172,'3107','ประโคนชัย','Prakhon Chai',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(237,172,'3108','บ้านกรวด','Ban Kruat',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(238,172,'3109','พุทไธสง','Phutthaisong',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(239,172,'3110','ลำปลายมาศ','Lam Plai Mat',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(240,172,'3111','สตึก','Satuek',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(241,172,'3112','ปะคำ','Pakham',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(242,172,'3113','นาโพธิ์','Na Pho',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(243,172,'3114','หนองหงส์','Nong Hong',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(244,172,'3115','พลับพลาชัย','Phlapphla Chai',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(245,172,'3116','ห้วยราช','Huai Rat',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(246,172,'3117','โนนสุวรรณ','Non Suwan',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(247,172,'3118','ชำนิ','Chamni',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(248,172,'3119','บ้านใหม่ไชยพจน์','Ban Mai Chaiyaphot',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(249,172,'3120','โนนดินแดง','Non Din Daeng',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(250,172,'3121','บ้านด่าน','Ban Dan',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(251,172,'3122','แคนดง','Khaen Dong',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(252,172,'3123','เฉลิมพระเกียรติ','Chaloem Phra Kiat',23,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(253,173,'3201','เมืองสุรินทร์','Mueang Surin',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(254,173,'3202','ชุมพลบุรี','Chumphon Buri',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(255,173,'3203','ท่าตูม','Tha Tum',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(256,173,'3204','จอมพระ','Chom Phra',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(257,173,'3205','ปราสาท','Prasat',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(258,173,'3206','กาบเชิง','Kap Choeng',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(259,173,'3207','รัตนบุรี','Rattanaburi',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(260,173,'3208','สนม','Sanom',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(261,173,'3209','ศีขรภูมิ','Sikhoraphum',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(262,173,'3210','สังขะ','Sangkha',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(263,173,'3211','ลำดวน','Lamduan',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(264,173,'3212','สำโรงทาบ','Samrong Thap',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(265,173,'3213','บัวเชด','Buachet',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(266,173,'3214','พนมดงรัก','Phanom Dong Rak',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(267,173,'3215','ศรีณรงค์','Si Narong',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(268,173,'3216','เขวาสินรินทร์','Khwao Sinrin',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(269,173,'3217','โนนนารายณ์','Non Narai',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(270,174,'3301','เมืองศรีสะเกษ','Mueang Si Sa Ket',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(271,174,'3302','ยางชุมน้อย','Yang Chum Noi',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(272,174,'3303','กันทรารมย์','Kanthararom',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(273,174,'3304','กันทรลักษ์','Kantharalak',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(274,174,'3305','ขุขันธ์','Khukhan',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(275,174,'3306','ไพรบึง','Phrai Bueng',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(276,174,'3307','ปรางค์กู่','Prang Ku',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(277,174,'3308','ขุนหาญ','Khun Han',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(278,174,'3309','ราษีไศล','Rasi Salai',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(279,174,'3310','อุทุมพรพิสัย','Uthumphon Phisai',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(280,174,'3311','บึงบูรพ์','Bueng Bun',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(281,174,'3312','ห้วยทับทัน','Huai Thap Than',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(282,174,'3313','โนนคูณ','Non Khun',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(283,174,'3314','ศรีรัตนะ','Si Rattana',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(284,174,'3315','น้ำเกลี้ยง','Nam Kliang',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(285,174,'3316','วังหิน','Wang Hin',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(286,174,'3317','ภูสิงห์','Phu Sing',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(287,174,'3318','เมืองจันทร์','Mueang Chan',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(288,174,'3319','เบญจลักษ์','Benchalak',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(289,174,'3320','พยุห์','Phayu',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(290,174,'3321','โพธิ์ศรีสุวรรณ','Pho Si Suwan',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(291,174,'3322','ศิลาลาด','Sila Lat',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(292,175,'3401','เมืองอุบลราชธานี','Mueang Ubon Ratchathani',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(293,175,'3402','ศรีเมืองใหม่','Si Mueang Mai',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(294,175,'3403','โขงเจียม','Khong Chiam',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(295,175,'3404','เขื่องใน','Khueang Nai',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(296,175,'3405','เขมราฐ','Khemarat',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(297,175,'3407','เดชอุดม','Det Udom',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(298,175,'3408','นาจะหลวย','Na Chaluai',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(299,175,'3409','น้ำยืน','Nam Yuen',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(300,175,'3410','บุณฑริก','Buntharik',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(301,175,'3411','ตระการพืชผล','Trakan Phuet Phon',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(302,175,'3412','กุดข้าวปุ้น','Kut Khaopun',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(303,175,'3414','ม่วงสามสิบ','Muang Sam Sip',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(304,175,'3415','วารินชำราบ','Warin Chamrap',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(305,175,'3419','พิบูลมังสาหาร','Phibun Mangsahan',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(306,175,'3420','ตาลสุม','Tan Sum',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(307,175,'3421','โพธิ์ไทร','Pho Sai',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(308,175,'3422','สำโรง','Samrong',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(309,175,'3424','ดอนมดแดง','Don Mot Daeng',24,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(310,175,'3425','สิรินธร','Sirindhorn',25,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(311,175,'3426','ทุ่งศรีอุดม','Thung Si Udom',26,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(312,175,'3429','นาเยีย','Na Yia',29,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(313,175,'3430','นาตาล','Na Tan',30,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(314,175,'3431','เหล่าเสือโก้ก','Lao Suea Kok',31,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(315,175,'3432','สว่างวีระวงศ์','Sawang Wirawong',32,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(316,175,'3433','น้ำขุ่น','Nam Khun',33,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(317,176,'3501','เมืองยโสธร','Mueang Yasothon',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(318,176,'3502','ทรายมูล','Sai Mun',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(319,176,'3503','กุดชุม','Kut Chum',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(320,176,'3504','คำเขื่อนแก้ว','Kham Khuean Kaeo',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(321,176,'3505','ป่าติ้ว','Pa Tio',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(322,176,'3506','มหาชนะชัย','Maha Chana Chai',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(323,176,'3507','ค้อวัง','Kho Wang',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(324,176,'3508','เลิงนกทา','Loeng Nok Tha',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(325,176,'3509','ไทยเจริญ','Thai Charoen',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(326,177,'3601','เมืองชัยภูมิ','Mueang Chaiyaphum',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(327,177,'3602','บ้านเขว้า','Ban Khwao',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(328,177,'3603','คอนสวรรค์','Khon Sawan',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(329,177,'3604','เกษตรสมบูรณ์','Kaset Sombun',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(330,177,'3605','หนองบัวแดง','Nong Bua Daeng',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(331,177,'3606','จัตุรัส','Chatturat',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(332,177,'3607','บำเหน็จณรงค์','Bamnet Narong',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(333,177,'3608','หนองบัวระเหว','Nong Bua Rawe',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(334,177,'3609','เทพสถิต','Thep Sathit',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(335,177,'3610','ภูเขียว','Phu Khiao',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(336,177,'3611','บ้านแท่น','Ban Thaen',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(337,177,'3612','แก้งคร้อ','Kaeng Khro',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(338,177,'3613','คอนสาร','Khon San',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(339,177,'3614','ภักดีชุมพล','Phakdi Chumphon',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(340,177,'3615','เนินสง่า','Noen Sa-nga',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(341,177,'3616','ซับใหญ่','Sap Yai',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(342,178,'3701','เมืองอำนาจเจริญ','Mueang Amnat Charoen',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(343,178,'3702','ชานุมาน','Chanuman',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(344,178,'3703','ปทุมราชวงศา','Pathum Ratchawongsa',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(345,178,'3704','พนา','Phana',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(346,178,'3705','เสนางคนิคม','Senangkhanikhom',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(347,178,'3706','หัวตะพาน','Hua Taphan',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(348,178,'3707','ลืออำนาจ','Lue Amnat',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(349,179,'3801','เมืองบึงกาฬ','Mueang Bueng Kan',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(350,179,'3802','พรเจริญ','Phon Charoen',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(351,179,'3803','โซ่พิสัย','So Phisai',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(352,179,'3804','เซกา','Seka',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(353,179,'3805','ปากคาด','Pak Khat',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(354,179,'3806','บึงโขงหลง','Bueng Khong Long',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(355,179,'3807','ศรีวิไล','Si Wilai',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(356,179,'3808','บุ่งคล้า','Bung Khla',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(357,180,'3901','เมืองหนองบัวลำภู','Mueang Nong Bua Lam Phu',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(358,180,'3902','นากลาง','Na Klang',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(359,180,'3903','โนนสัง','Non Sang',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(360,180,'3904','ศรีบุญเรือง','Si Bun Rueang',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(361,180,'3905','สุวรรณคูหา','Suwannakhuha',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(362,180,'3906','นาวัง','Na Wang',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(363,181,'4001','เมืองขอนแก่น','Mueang Khon Kaen',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(364,181,'4002','บ้านฝาง','Ban Fang',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(365,181,'4003','พระยืน','Phra Yuen',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(366,181,'4004','หนองเรือ','Nong Ruea',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(367,181,'4005','ชุมแพ','Chum Phae',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(368,181,'4006','สีชมพู','Si Chomphu',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(369,181,'4007','น้ำพอง','Nam Phong',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(370,181,'4008','อุบลรัตน์','Ubolratana',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(371,181,'4009','กระนวน','Kranuan',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(372,181,'4010','บ้านไผ่','Ban Phai',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(373,181,'4011','เปือยน้อย','Pueai Noi',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(374,181,'4012','พล','Phon',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(375,181,'4013','แวงใหญ่','Waeng Yai',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(376,181,'4014','แวงน้อย','Waeng Noi',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(377,181,'4015','หนองสองห้อง','Nong Song Hong',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(378,181,'4016','ภูเวียง','Phu Wiang',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(379,181,'4017','มัญจาคีรี','Mancha Khiri',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(380,181,'4018','ชนบท','Chonnabot',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(381,181,'4019','เขาสวนกวาง','Khao Suan Kwang',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(382,181,'4020','ภูผาม่าน','Phu Pha Man',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(383,181,'4021','ซำสูง','Sam Sung',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(384,181,'4022','โคกโพธิ์ไชย','Khok Pho Chai',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(385,181,'4023','หนองนาคำ','Nong Na Kham',23,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(386,181,'4024','บ้านแฮด','Ban Haet',24,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(387,181,'4025','โนนศิลา','Non Sila',25,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(388,181,'4029','เวียงเก่า','Wiang Kao',29,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(389,182,'4101','เมืองอุดรธานี','Mueang Udon Thani',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(390,182,'4102','กุดจับ','Kut Chap',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(391,182,'4103','หนองวัวซอ','Nong Wua So',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(392,182,'4104','กุมภวาปี','Kumphawapi',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(393,182,'4105','โนนสะอาด','Non Sa-at',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(394,182,'4106','หนองหาน','Nong Han',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(395,182,'4107','ทุ่งฝน','Thung Fon',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(396,182,'4108','ไชยวาน','Chai Wan',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(397,182,'4109','ศรีธาตุ','Si That',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(398,182,'4110','วังสามหมอ','Wang Sam Mo',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(399,182,'4111','บ้านดุง','Ban Dung',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(400,182,'4117','บ้านผือ','Ban Phue',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(401,182,'4118','น้ำโสม','Nam Som',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(402,182,'4119','เพ็ญ','Phen',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(403,182,'4120','สร้างคอม','Sang Khom',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(404,182,'4121','หนองแสง','Nong Saeng',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(405,182,'4122','นายูง','Na Yung',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(406,182,'4123','พิบูลย์รักษ์','Phibun Rak',23,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(407,182,'4124','กู่แก้ว','Ku Kaeo',24,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(408,182,'4125','ประจักษ์ศิลปาคม','Prachak Sinlapakhom',25,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(409,183,'4201','เมืองเลย','Mueang Loei',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(410,183,'4202','นาด้วง','Na Duang',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(411,183,'4203','เชียงคาน','Chiang Khan',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(412,183,'4204','ปากชม','Pak Chom',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(413,183,'4205','ด่านซ้าย','Dan Sai',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(414,183,'4206','นาแห้ว','Na Haeo',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(415,183,'4207','ภูเรือ','Phu Ruea',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(416,183,'4208','ท่าลี่','Tha Li',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(417,183,'4209','วังสะพุง','Wang Saphung',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(418,183,'4210','ภูกระดึง','Phu Kradueng',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(419,183,'4211','ภูหลวง','Phu Luang',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(420,183,'4212','ผาขาว','Pha Khao',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(421,183,'4213','เอราวัณ','Erawan',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(422,183,'4214','หนองหิน','Nong Hin',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(423,184,'4301','เมืองหนองคาย','Mueang Nong Khai',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(424,184,'4302','ท่าบ่อ','Tha Bo',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(425,184,'4305','โพนพิสัย','Phon Phisai',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(426,184,'4307','ศรีเชียงใหม่','Si Chiang Mai',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(427,184,'4308','สังคม','Sangkhom',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(428,184,'4314','สระใคร','Sakhrai',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(429,184,'4315','เฝ้าไร่','Fao Rai',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(430,184,'4316','รัตนวาปี','Rattanawapi',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(431,184,'4317','โพธิ์ตาก','Pho Tak',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(432,185,'4401','เมืองมหาสารคาม','Mueang Maha Sarakham',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(433,185,'4402','แกดำ','Kae Dam',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(434,185,'4403','โกสุมพิสัย','Kosum Phisai',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(435,185,'4404','กันทรวิชัย','Kantharawichai',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(436,185,'4405','เชียงยืน','Chiang Yuen',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(437,185,'4406','บรบือ','Borabue',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(438,185,'4407','นาเชือก','Na Chueak',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(439,185,'4408','พยัคฆภูมิพิสัย','Phayakkhaphum Phisai',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(440,185,'4409','วาปีปทุม','Wapi Pathum',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(441,185,'4410','นาดูน','Na Dun',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(442,185,'4411','ยางสีสุราช','Yang Si Surat',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(443,185,'4412','กุดรัง','Kut Rang',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(444,185,'4413','ชื่นชม','Chuen Chom',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(445,186,'4501','เมืองร้อยเอ็ด','Mueang Roi Et',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(446,186,'4502','เกษตรวิสัย','Kaset Wisai',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(447,186,'4503','ปทุมรัตต์','Pathum Rat',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(448,186,'4504','จตุรพักตรพิมาน','Chaturaphak Phiman',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(449,186,'4505','ธวัชบุรี','Thawat Buri',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(450,186,'4506','พนมไพร','Phanom Phrai',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(451,186,'4507','โพนทอง','Phon Thong',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(452,186,'4508','โพธิ์ชัย','Pho Chai',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(453,186,'4509','หนองพอก','Nong Phok',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(454,186,'4510','เสลภูมิ','Selaphum',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(455,186,'4511','สุวรรณภูมิ','Suwannaphum',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(456,186,'4512','เมืองสรวง','Mueang Suang',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(457,186,'4513','โพนทราย','Phon Sai',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(458,186,'4514','อาจสามารถ','At Samat',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(459,186,'4515','เมยวดี','Moei Wadi',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(460,186,'4516','ศรีสมเด็จ','Si Somdet',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(461,186,'4517','จังหาร','Changhan',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(462,186,'4518','เชียงขวัญ','Chiang Khwan',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(463,186,'4519','หนองฮี','Nong Hi',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(464,186,'4520','ทุ่งเขาหลวง','Thung Khao Luang',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(465,187,'4601','เมืองกาฬสินธุ์','Mueang Kalasin',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(466,187,'4602','นามน','Na Mon',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(467,187,'4603','กมลาไสย','Kamalasai',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(468,187,'4604','ร่องคำ','Rong Kham',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(469,187,'4605','กุฉินารายณ์','Kuchinarai',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(470,187,'4606','เขาวง','Khao Wong',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(471,187,'4607','ยางตลาด','Yang Talat',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(472,187,'4608','ห้วยเม็ก','Huai Mek',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(473,187,'4609','สหัสขันธ์','Sahatsakhan',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(474,187,'4610','คำม่วง','Kham Muang',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(475,187,'4611','ท่าคันโท','Tha Khantho',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(476,187,'4612','หนองกุงศรี','Nong Kung Si',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(477,187,'4613','สมเด็จ','Somdet',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(478,187,'4614','ห้วยผึ้ง','Huai Phueng',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(479,187,'4615','สามชัย','Sam Chai',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(480,187,'4616','นาคู','Na Khu',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(481,187,'4617','ดอนจาน','Don Chan',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(482,187,'4618','ฆ้องชัย','Khong Chai',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(483,188,'4701','เมืองสกลนคร','Mueang Sakon Nakhon',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(484,188,'4702','กุสุมาลย์','Kusuman',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(485,188,'4703','กุดบาก','Kut Bak',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(486,188,'4704','พรรณานิคม','Phanna Nikhom',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(487,188,'4705','พังโคน','Phang Khon',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(488,188,'4706','วาริชภูมิ','Waritchaphum',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(489,188,'4707','นิคมน้ำอูน','Nikhom Nam Un',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(490,188,'4708','วานรนิวาส','Wanon Niwat',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(491,188,'4709','คำตากล้า','Kham Ta Kla',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(492,188,'4710','บ้านม่วง','Ban Muang',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(493,188,'4711','อากาศอำนวย','Akat Amnuai',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(494,188,'4712','สว่างแดนดิน','Sawang Daen Din',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(495,188,'4713','ส่องดาว','Song Dao',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(496,188,'4714','เต่างอย','Tao Ngoi',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(497,188,'4715','โคกศรีสุพรรณ','Khok Si Suphan',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(498,188,'4716','เจริญศิลป์','Charoen Sin',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(499,188,'4717','โพนนาแก้ว','Phon Na Kaeo',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(500,188,'4718','ภูพาน','Phu Phan',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(501,189,'4801','เมืองนครพนม','Mueang Nakhon Phanom',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(502,189,'4802','ปลาปาก','Pla Pak',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(503,189,'4803','ท่าอุเทน','Tha Uthen',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(504,189,'4804','บ้านแพง','Ban Phaeng',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(505,189,'4805','ธาตุพนม','That Phanom',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(506,189,'4806','เรณูนคร','Renu Nakhon',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(507,189,'4807','นาแก','Na Kae',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(508,189,'4808','ศรีสงคราม','Si Songkhram',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(509,189,'4809','นาหว้า','Na Wa',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(510,189,'4810','โพนสวรรค์','Phon Sawan',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(511,189,'4811','นาทม','Na Thom',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(512,189,'4812','วังยาง','Wang Yang',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(513,190,'4901','เมืองมุกดาหาร','Mueang Mukdahan',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(514,190,'4902','นิคมคำสร้อย','Nikhom Kham Soi',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(515,190,'4903','ดอนตาล','Don Tan',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(516,190,'4904','ดงหลวง','Dong Luang',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(517,190,'4905','คำชะอี','Khamcha-i',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(518,190,'4906','หว้านใหญ่','Wan Yai',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(519,190,'4907','หนองสูง','Nong Sung',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(520,191,'5001','เมืองเชียงใหม่','Mueang Chiang Mai',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(521,191,'5002','จอมทอง','Chom Thong',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(522,191,'5003','แม่แจ่ม','Mae Chaem',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(523,191,'5004','เชียงดาว','Chiang Dao',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(524,191,'5005','ดอยสะเก็ด','Doi Saket',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(525,191,'5006','แม่แตง','Mae Taeng',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(526,191,'5007','แม่ริม','Mae Rim',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(527,191,'5008','สะเมิง','Samoeng',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(528,191,'5009','ฝาง','Fang',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(529,191,'5010','แม่อาย','Mae Ai',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(530,191,'5011','พร้าว','Phrao',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(531,191,'5012','สันป่าตอง','San Pa Tong',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(532,191,'5013','สันกำแพง','San Kamphaeng',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(533,191,'5014','สันทราย','San Sai',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(534,191,'5015','หางดง','Hang Dong',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(535,191,'5016','ฮอด','Hot',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(536,191,'5017','ดอยเต่า','Doi Tao',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(537,191,'5018','อมก๋อย','Omkoi',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(538,191,'5019','สารภี','Saraphi',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(539,191,'5020','เวียงแหง','Wiang Haeng',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(540,191,'5021','ไชยปราการ','Chai Prakan',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(541,191,'5022','แม่วาง','Mae Wang',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(542,191,'5023','แม่ออน','Mae On',23,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(543,191,'5024','ดอยหล่อ','Doi Lo',24,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(544,191,'5025','กัลยาณิวัฒนา','Galayani Vadhana',25,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(545,192,'5101','เมืองลำพูน','Mueang Lamphun',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(546,192,'5102','แม่ทา','Mae Tha',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(547,192,'5103','บ้านโฮ่ง','Ban Hong',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(548,192,'5104','ลี้','Li',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(549,192,'5105','ทุ่งหัวช้าง','Thung Hua Chang',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(550,192,'5106','ป่าซาง','Pa Sang',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(551,192,'5107','บ้านธิ','Ban Thi',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(552,192,'5108','เวียงหนองล่อง','Wiang Nong Long',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(553,193,'5201','เมืองลำปาง','Mueang Lampang',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(554,193,'5202','แม่เมาะ','Mae Mo',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(555,193,'5203','เกาะคา','Ko Kha',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(556,193,'5204','เสริมงาม','Soem Ngam',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(557,193,'5205','งาว','Ngao',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(558,193,'5206','แจ้ห่ม','Chae Hom',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(559,193,'5207','วังเหนือ','Wang Nuea',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(560,193,'5208','เถิน','Thoen',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(561,193,'5209','แม่พริก','Mae Phrik',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(562,193,'5210','แม่ทะ','Mae Tha',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(563,193,'5211','สบปราบ','Sop Prap',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(564,193,'5212','ห้างฉัตร','Hang Chat',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(565,193,'5213','เมืองปาน','Mueang Pan',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(566,194,'5301','เมืองอุตรดิตถ์','Mueang Uttaradit',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(567,194,'5302','ตรอน','Tron',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(568,194,'5303','ท่าปลา','Tha Pla',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(569,194,'5304','น้ำปาด','Nam Pat',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(570,194,'5305','ฟากท่า','Fak Tha',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(571,194,'5306','บ้านโคก','Ban Khok',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(572,194,'5307','พิชัย','Phichai',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(573,194,'5308','ลับแล','Laplae',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(574,194,'5309','ทองแสนขัน','Thong Saen Khan',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(575,195,'5401','เมืองแพร่','Mueang Phrae',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(576,195,'5402','ร้องกวาง','Rong Kwang',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(577,195,'5403','ลอง','Long',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(578,195,'5404','สูงเม่น','Sung Men',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(579,195,'5405','เด่นชัย','Den Chai',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(580,195,'5406','สอง','Song',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(581,195,'5407','วังชิ้น','Wang Chin',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(582,195,'5408','หนองม่วงไข่','Nong Muang Khai',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(583,196,'5501','เมืองน่าน','Mueang Nan',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(584,196,'5502','แม่จริม','Mae Charim',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(585,196,'5503','บ้านหลวง','Ban Luang',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(586,196,'5504','นาน้อย','Na Noi',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(587,196,'5505','ปัว','Pua',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(588,196,'5506','ท่าวังผา','Tha Wang Pha',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(589,196,'5507','เวียงสา','Wiang Sa',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(590,196,'5508','ทุ่งช้าง','Thung Chang',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(591,196,'5509','เชียงกลาง','Chiang Klang',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(592,196,'5510','นาหมื่น','Na Muen',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(593,196,'5511','สันติสุข','Santi Suk',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(594,196,'5512','บ่อเกลือ','Bo Kluea',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(595,196,'5513','สองแคว','Song Khwae',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(596,196,'5514','ภูเพียง','Phu Phiang',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(597,196,'5515','เฉลิมพระเกียรติ','Chaloem Phra Kiat',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(598,197,'5601','เมืองพะเยา','Mueang Phayao',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(599,197,'5602','จุน','Chun',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(600,197,'5603','เชียงคำ','Chiang Kham',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(601,197,'5604','เชียงม่วน','Chiang Muan',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(602,197,'5605','ดอกคำใต้','Dok Khamtai',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(603,197,'5606','ปง','Pong',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(604,197,'5607','แม่ใจ','Mae Chai',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(605,197,'5608','ภูซาง','Phu Sang',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(606,197,'5609','ภูกามยาว','Phu Kamyao',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(607,198,'5701','เมืองเชียงราย','Mueang Chiang Rai',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(608,198,'5702','เวียงชัย','Wiang Chai',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(609,198,'5703','เชียงของ','Chiang Khong',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(610,198,'5704','เทิง','Thoeng',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(611,198,'5705','พาน','Phan',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(612,198,'5706','ป่าแดด','Pa Daet',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(613,198,'5707','แม่จัน','Mae Chan',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(614,198,'5708','เชียงแสน','Chiang Saen',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(615,198,'5709','แม่สาย','Mae Sai',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(616,198,'5710','แม่สรวย','Mae Suai',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(617,198,'5711','เวียงป่าเป้า','Wiang Pa Pao',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(618,198,'5712','พญาเม็งราย','Phaya Mengrai',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(619,198,'5713','เวียงแก่น','Wiang Kaen',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(620,198,'5714','ขุนตาล','Khun Tan',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(621,198,'5715','แม่ฟ้าหลวง','Mae Fa Luang',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(622,198,'5716','แม่ลาว','Mae Lao',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(623,198,'5717','เวียงเชียงรุ้ง','Wiang Chiang Rung',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(624,198,'5718','ดอยหลวง','Doi Luang',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(625,199,'5801','เมืองแม่ฮ่องสอน','Mueang Mae Hong Son',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(626,199,'5802','ขุนยวม','Khun Yuam',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(627,199,'5803','ปาย','Pai',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(628,199,'5804','แม่สะเรียง','Mae Sariang',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(629,199,'5805','แม่ลาน้อย','Mae La Noi',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(630,199,'5806','สบเมย','Sop Moei',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(631,199,'5807','ปางมะผ้า','Pang Mapha',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(632,200,'6001','เมืองนครสวรรค์','Mueang Nakhon Sawan',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(633,200,'6002','โกรกพระ','Krok Phra',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(634,200,'6003','ชุมแสง','Chum Saeng',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(635,200,'6004','หนองบัว','Nong Bua',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(636,200,'6005','บรรพตพิสัย','Banphot Phisai',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(637,200,'6006','เก้าเลี้ยว','Kao Liao',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(638,200,'6007','ตาคลี','Takhli',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(639,200,'6008','ท่าตะโก','Tha Tako',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(640,200,'6009','ไพศาลี','Phaisali',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(641,200,'6010','พยุหะคีรี','Phayuha Khiri',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(642,200,'6011','ลาดยาว','Lat Yao',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(643,200,'6012','ตากฟ้า','Tak Fa',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(644,200,'6013','แม่วงก์','Mae Wong',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(645,200,'6014','แม่เปิน','Mae Poen',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(646,200,'6015','ชุมตาบง','Chum Ta Bong',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(647,201,'6101','เมืองอุทัยธานี','Mueang Uthai Thani',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(648,201,'6102','ทัพทัน','Thap Than',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(649,201,'6103','สว่างอารมณ์','Sawang Arom',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(650,201,'6104','หนองฉาง','Nong Chang',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(651,201,'6105','หนองขาหย่าง','Nong Khayang',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(652,201,'6106','บ้านไร่','Ban Rai',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(653,201,'6107','ลานสัก','Lan Sak',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(654,201,'6108','ห้วยคต','Huai Khot',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(655,202,'6201','เมืองกำแพงเพชร','Mueang Kamphaeng Phet',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(656,202,'6202','ไทรงาม','Sai Ngam',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(657,202,'6203','คลองลาน','Khlong Lan',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(658,202,'6204','ขาณุวรลักษบุรี','Khanu Woralaksaburi',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(659,202,'6205','คลองขลุง','Khlong Khlung',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(660,202,'6206','พรานกระต่าย','Phran Kratai',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(661,202,'6207','ลานกระบือ','Lan Krabue',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(662,202,'6208','ทรายทองวัฒนา','Sai Thong Watthana',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(663,202,'6209','ปางศิลาทอง','Pang Sila Thong',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(664,202,'6210','บึงสามัคคี','Bueng Samakkhi',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(665,202,'6211','โกสัมพีนคร','Kosamphi Nakhon',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(666,203,'6301','เมืองตาก','Mueang Tak',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(667,203,'6302','บ้านตาก','Ban Tak',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(668,203,'6303','สามเงา','Sam Ngao',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(669,203,'6304','แม่ระมาด','Mae Ramat',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(670,203,'6305','ท่าสองยาง','Tha Song Yang',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(671,203,'6306','แม่สอด','Mae Sot',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(672,203,'6307','พบพระ','Phop Phra',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(673,203,'6308','อุ้มผาง','Umphang',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(674,203,'6309','วังเจ้า','Wang Chao',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(675,204,'6401','เมืองสุโขทัย','Mueang Sukhothai',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(676,204,'6402','บ้านด่านลานหอย','Ban Dan Lan Hoi',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(677,204,'6403','คีรีมาศ','Khiri Mat',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(678,204,'6404','กงไกรลาศ','Kong Krailat',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(679,204,'6405','ศรีสัชนาลัย','Si Satchanalai',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(680,204,'6406','ศรีสำโรง','Si Samrong',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(681,204,'6407','สวรรคโลก','Sawankhalok',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(682,204,'6408','ศรีนคร','Si Nakhon',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(683,204,'6409','ทุ่งเสลี่ยม','Thung Saliam',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(684,205,'6501','เมืองพิษณุโลก','Mueang Phitsanulok',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(685,205,'6502','นครไทย','Nakhon Thai',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(686,205,'6503','ชาติตระการ','Chat Trakan',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(687,205,'6504','บางระกำ','Bang Rakam',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(688,205,'6505','บางกระทุ่ม','Bang Krathum',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(689,205,'6506','พรหมพิราม','Phrom Phiram',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(690,205,'6507','วัดโบสถ์','Wat Bot',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(691,205,'6508','วังทอง','Wang Thong',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(692,205,'6509','เนินมะปราง','Noen Maprang',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(693,206,'6601','เมืองพิจิตร','Mueang Phichit',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(694,206,'6602','วังทรายพูน','Wang Sai Phun',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(695,206,'6603','โพธิ์ประทับช้าง','Pho Prathap Chang',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(696,206,'6604','ตะพานหิน','Taphan Hin',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(697,206,'6605','บางมูลนาก','Bang Mun Nak',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(698,206,'6606','โพทะเล','Pho Thale',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(699,206,'6607','สามง่าม','Sam Ngam',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(700,206,'6608','ทับคล้อ','Thap Khlo',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(701,206,'6609','สากเหล็ก','Sak Lek',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(702,206,'6610','บึงนาราง','Bueng Na Rang',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(703,206,'6611','ดงเจริญ','Dong Charoen',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(704,206,'6612','วชิรบารมี','Wachirabarami',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(705,207,'6701','เมืองเพชรบูรณ์','Mueang Phetchabun',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(706,207,'6702','ชนแดน','Chon Daen',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(707,207,'6703','หล่มสัก','Lom Sak',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(708,207,'6704','หล่มเก่า','Lom Kao',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(709,207,'6705','วิเชียรบุรี','Wichian Buri',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(710,207,'6706','ศรีเทพ','Si Thep',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(711,207,'6707','หนองไผ่','Nong Phai',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(712,207,'6708','บึงสามพัน','Bueng Sam Phan',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(713,207,'6709','น้ำหนาว','Nam Nao',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(714,207,'6710','วังโป่ง','Wang Pong',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(715,207,'6711','เขาค้อ','Khao Kho',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(716,208,'7001','เมืองราชบุรี','Mueang Ratchaburi',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(717,208,'7002','จอมบึง','Chom Bueng',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(718,208,'7003','สวนผึ้ง','Suan Phueng',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(719,208,'7004','ดำเนินสะดวก','Damnoen Saduak',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(720,208,'7005','บ้านโป่ง','Ban Pong',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(721,208,'7006','บางแพ','Bang Phae',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(722,208,'7007','โพธาราม','Photharam',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(723,208,'7008','ปากท่อ','Pak Tho',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(724,208,'7009','วัดเพลง','Wat Phleng',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(725,208,'7010','บ้านคา','Ban Kha',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(726,209,'7101','เมืองกาญจนบุรี','Mueang Kanchanaburi',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(727,209,'7102','ไทรโยค','Sai Yok',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(728,209,'7103','บ่อพลอย','Bo Phloi',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(729,209,'7104','ศรีสวัสดิ์','Si Sawat',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(730,209,'7105','ท่ามะกา','Tha Maka',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(731,209,'7106','ท่าม่วง','Tha Muang',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(732,209,'7107','ทองผาภูมิ','Thong Pha Phum',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(733,209,'7108','สังขละบุรี','Sangkhla Buri',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(734,209,'7109','พนมทวน','Phanom Thuan',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(735,209,'7110','เลาขวัญ','Lao Khwan',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(736,209,'7111','ด่านมะขามเตี้ย','Dan Makham Tia',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(737,209,'7112','หนองปรือ','Nong Prue',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(738,209,'7113','ห้วยกระเจา','Huai Krachao',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(739,210,'7201','เมืองสุพรรณบุรี','Mueang Suphan Buri',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(740,210,'7202','เดิมบางนางบวช','Doem Bang Nang Buat',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(741,210,'7203','ด่านช้าง','Dan Chang',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(742,210,'7204','บางปลาม้า','Bang Pla Ma',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(743,210,'7205','ศรีประจันต์','Si Prachan',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(744,210,'7206','ดอนเจดีย์','Don Chedi',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(745,210,'7207','สองพี่น้อง','Song Phi Nong',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(746,210,'7208','สามชุก','Sam Chuk',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(747,210,'7209','อู่ทอง','U Thong',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(748,210,'7210','หนองหญ้าไซ','Nong Ya Sai',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(749,211,'7301','เมืองนครปฐม','Mueang Nakhon Pathom',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(750,211,'7302','กำแพงแสน','Kamphaeng Saen',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(751,211,'7303','นครชัยศรี','Nakhon Chai Si',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(752,211,'7304','ดอนตูม','Don Tum',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(753,211,'7305','บางเลน','Bang Len',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(754,211,'7306','สามพราน','Sam Phran',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(755,211,'7307','พุทธมณฑล','Phutthamonthon',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(756,212,'7401','เมืองสมุทรสาคร','Mueang Samut Sakhon',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(757,212,'7402','กระทุ่มแบน','Krathum Baen',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(758,212,'7403','บ้านแพ้ว','Ban Phaeo',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(759,213,'7501','เมืองสมุทรสงคราม','Mueang Samut Songkhram',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(760,213,'7502','บางคนที','Bang Khonthi',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(761,213,'7503','อัมพวา','Amphawa',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(762,214,'7601','เมืองเพชรบุรี','Mueang Phetchaburi',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(763,214,'7602','เขาย้อย','Khao Yoi',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(764,214,'7603','หนองหญ้าปล้อง','Nong Ya Plong',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(765,214,'7604','ชะอำ','Cha-am',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(766,214,'7605','ท่ายาง','Tha Yang',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(767,214,'7606','บ้านลาด','Ban Lat',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(768,214,'7607','บ้านแหลม','Ban Laem',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(769,214,'7608','แก่งกระจาน','Kaeng Krachan',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(770,215,'7701','เมืองประจวบคีรีขันธ์','Mueang Prachuap Khiri Khan',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(771,215,'7702','กุยบุรี','Kui Buri',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(772,215,'7703','ทับสะแก','Thap Sakae',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(773,215,'7704','บางสะพาน','Bang Saphan',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(774,215,'7705','บางสะพานน้อย','Bang Saphan Noi',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(775,215,'7706','ปราณบุรี','Pran Buri',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(776,215,'7707','หัวหิน','Hua Hin',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(777,215,'7708','สามร้อยยอด','Sam Roi Yot',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(778,216,'8001','เมืองนครศรีธรรมราช','Mueang Nakhon Si Thammarat',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(779,216,'8002','พรหมคีรี','Phrom Khiri',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(780,216,'8003','ลานสกา','Lan Saka',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(781,216,'8004','ฉวาง','Chawang',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(782,216,'8005','พิปูน','Phipun',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(783,216,'8006','เชียรใหญ่','Chian Yai',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(784,216,'8007','ชะอวด','Cha-uat',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(785,216,'8008','ท่าศาลา','Tha Sala',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(786,216,'8009','ทุ่งสง','Thung Song',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(787,216,'8010','นาบอน','Na Bon',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(788,216,'8011','ทุ่งใหญ่','Thung Yai',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(789,216,'8012','ปากพนัง','Pak Phanang',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(790,216,'8013','ร่อนพิบูลย์','Ron Phibun',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(791,216,'8014','สิชล','Sichon',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(792,216,'8015','ขนอม','Khanom',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(793,216,'8016','หัวไทร','Hua Sai',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(794,216,'8017','บางขัน','Bang Khan',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(795,216,'8018','ถ้ำพรรณรา','Tham Phannara',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(796,216,'8019','จุฬาภรณ์','Chulabhorn',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(797,216,'8020','พระพรหม','Phra Phrom',20,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(798,216,'8021','นบพิตำ','Nopphitam',21,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(799,216,'8022','ช้างกลาง','Chang Klang',22,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(800,216,'8023','เฉลิมพระเกียรติ','Chaloem Phra Kiat',23,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(801,217,'8101','เมืองกระบี่','Mueang Krabi',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(802,217,'8102','เขาพนม','Khao Phanom',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(803,217,'8103','เกาะลันตา','Ko Lanta',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(804,217,'8104','คลองท่อม','Khlong Thom',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(805,217,'8105','อ่าวลึก','Ao Luek',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(806,217,'8106','ปลายพระยา','Plai Phraya',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(807,217,'8107','ลำทับ','Lam Thap',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(808,217,'8108','เหนือคลอง','Nuea Khlong',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(809,218,'8201','เมืองพังงา','Mueang Phang-nga',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(810,218,'8202','เกาะยาว','Ko Yao',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(811,218,'8203','กะปง','Kapong',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(812,218,'8204','ตะกั่วทุ่ง','Takua Thung',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(813,218,'8205','ตะกั่วป่า','Takua Pa',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(814,218,'8206','คุระบุรี','Khura Buri',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(815,218,'8207','ทับปุด','Thap Put',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(816,218,'8208','ท้ายเหมือง','Thai Mueang',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(817,219,'8301','เมืองภูเก็ต','Mueang Phuket',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(818,219,'8302','กะทู้','Kathu',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(819,219,'8303','ถลาง','Thalang',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(820,220,'8401','เมืองสุราษฎร์ธานี','Mueang Surat Thani',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(821,220,'8402','กาญจนดิษฐ์','Kanchanadit',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(822,220,'8403','ดอนสัก','Don Sak',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(823,220,'8404','เกาะสมุย','Ko Samui',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(824,220,'8405','เกาะพะงัน','Ko Pha-ngan',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(825,220,'8406','ไชยา','Chaiya',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(826,220,'8407','ท่าชนะ','Tha Chana',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(827,220,'8408','คีรีรัฐนิคม','Khiri Rat Nikhom',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(828,220,'8409','บ้านตาขุน','Ban Ta Khun',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(829,220,'8410','พนม','Phanom',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(830,220,'8411','ท่าฉาง','Tha Chang',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(831,220,'8412','บ้านนาสาร','Ban Na San',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(832,220,'8413','บ้านนาเดิม','Ban Na Doem',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(833,220,'8414','เคียนซา','Khian Sa',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(834,220,'8415','เวียงสระ','Wiang Sa',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(835,220,'8416','พระแสง','Phrasaeng',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(836,220,'8417','พุนพิน','Phunphin',17,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(837,220,'8418','ชัยบุรี','Chai Buri',18,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(838,220,'8419','วิภาวดี','Vibhavadi',19,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(839,221,'8501','เมืองระนอง','Mueang Ranong',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(840,221,'8502','ละอุ่น','La-un',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(841,221,'8503','กะเปอร์','Kapoe',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(842,221,'8504','กระบุรี','Kra Buri',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(843,221,'8505','สุขสำราญ','Suk Samran',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(844,222,'8601','เมืองชุมพร','Mueang Chumphon',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(845,222,'8602','ท่าแซะ','Tha Sae',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(846,222,'8603','ปะทิว','Pathio',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(847,222,'8604','หลังสวน','Lang Suan',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(848,222,'8605','ละแม','Lamae',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(849,222,'8606','พะโต๊ะ','Phato',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(850,222,'8607','สวี','Sawi',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(851,222,'8608','ทุ่งตะโก','Thung Tako',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(852,223,'9001','เมืองสงขลา','Mueang Songkhla',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(853,223,'9002','สทิงพระ','Sathing Phra',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(854,223,'9003','จะนะ','Chana',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(855,223,'9004','นาทวี','Na Thawi',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(856,223,'9005','เทพา','Thepha',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(857,223,'9006','สะบ้าย้อย','Saba Yoi',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(858,223,'9007','ระโนด','Ranot',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(859,223,'9008','กระแสสินธุ์','Krasae Sin',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(860,223,'9009','รัตภูมิ','Rattaphum',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(861,223,'9010','สะเดา','Sadao',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(862,223,'9011','หาดใหญ่','Hat Yai',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(863,223,'9012','นาหม่อม','Na Mom',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(864,223,'9013','ควนเนียง','Khuan Niang',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(865,223,'9014','บางกล่ำ','Bang Klam',14,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(866,223,'9015','สิงหนคร','Singhanakhon',15,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(867,223,'9016','คลองหอยโข่ง','Khlong Hoi Khong',16,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(868,224,'9101','เมืองสตูล','Mueang Satun',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(869,224,'9102','ควนโดน','Khuan Don',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(870,224,'9103','ควนกาหลง','Khuan Kalong',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(871,224,'9104','ท่าแพ','Tha Phae',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(872,224,'9105','ละงู','La-ngu',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(873,224,'9106','ทุ่งหว้า','Thung Wa',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(874,224,'9107','มะนัง','Manang',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(875,225,'9201','เมืองตรัง','Mueang Trang',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(876,225,'9202','กันตัง','Kantang',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(877,225,'9203','ย่านตาขาว','Yan Ta Khao',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(878,225,'9204','ปะเหลียน','Palian',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(879,225,'9205','สิเกา','Sikao',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(880,225,'9206','ห้วยยอด','Huai Yot',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(881,225,'9207','วังวิเศษ','Wang Wiset',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(882,225,'9208','นาโยง','Na Yong',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(883,225,'9209','รัษฎา','Ratsada',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(884,225,'9210','หาดสำราญ','Hat Samran',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(885,226,'9301','เมืองพัทลุง','Mueang Phatthalung',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(886,226,'9302','กงหรา','Kong Ra',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(887,226,'9303','เขาชัยสน','Khao Chaison',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(888,226,'9304','ตะโหมด','Tamot',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(889,226,'9305','ควนขนุน','Khuan Khanun',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(890,226,'9306','ปากพะยูน','Pak Phayun',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(891,226,'9307','ศรีบรรพต','Si Banphot',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(892,226,'9308','ป่าบอน','Pa Bon',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(893,226,'9309','บางแก้ว','Bang Kaeo',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(894,226,'9310','ป่าพะยอม','Pa Phayom',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(895,226,'9311','ศรีนครินทร์','Srinagarindra',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(896,227,'9401','เมืองปัตตานี','Mueang Pattani',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(897,227,'9402','โคกโพธิ์','Khok Pho',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(898,227,'9403','หนองจิก','Nong Chik',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(899,227,'9404','ปะนาเระ','Panare',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(900,227,'9405','มายอ','Mayo',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(901,227,'9406','ทุ่งยางแดง','Thung Yang Daeng',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(902,227,'9407','สายบุรี','Sai Buri',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(903,227,'9408','ไม้แก่น','Mai Kaen',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(904,227,'9409','ยะหริ่ง','Yaring',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(905,227,'9410','ยะรัง','Yarang',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(906,227,'9411','กะพ้อ','Kapho',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(907,227,'9412','แม่ลาน','Mae Lan',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(908,228,'9501','เมืองยะลา','Mueang Yala',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(909,228,'9502','เบตง','Betong',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(910,228,'9503','บันนังสตา','Bannang Sata',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(911,228,'9504','ธารโต','Than To',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(912,228,'9505','ยะหา','Yaha',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(913,228,'9506','รามัน','Raman',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(914,228,'9507','กาบัง','Kabang',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(915,228,'9508','กรงปินัง','Krong Pinang',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(916,229,'9601','เมืองนราธิวาส','Mueang Narathiwat',1,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(917,229,'9602','ตากใบ','Tak Bai',2,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(918,229,'9603','บาเจาะ','Bacho',3,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(919,229,'9604','ยี่งอ','Yi-ngo',4,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(920,229,'9605','ระแงะ','Ra-ngae',5,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(921,229,'9606','รือเสาะ','Rueso',6,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(922,229,'9607','ศรีสาคร','Si Sakhon',7,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(923,229,'9608','แว้ง','Waeng',8,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(924,229,'9609','สุคิริน','Sukhirin',9,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(925,229,'9610','สุไหงโก-ลก','Su-ngai Kolok',10,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(926,229,'9611','สุไหงปาดี','Su-ngai Padi',11,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(927,229,'9612','จะแนะ','Chanae',12,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL),(928,229,'9613','เจาะไอร้อง','Cho-airong',13,1,NULL,'2026-08-09 09:00:53','2026-08-09 09:00:53',NULL,NULL);
/*!40000 ALTER TABLE `districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `divisions`
--

DROP TABLE IF EXISTS `divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `divisions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'รหัสหน่วยงาน',
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ชื่อหน่วยงาน (ไทย)',
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ชื่อหน่วยงาน (อังกฤษ)',
  `short_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ชื่อย่อ',
  `parent_id` int DEFAULT NULL COMMENT 'หน่วยงานแม่ (ถ้ามี)',
  `type` enum('central','regional','provincial') COLLATE utf8mb4_unicode_ci DEFAULT 'central' COMMENT 'ประเภท: ส่วนกลาง/ภูมิภาค/จังหวัด',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_divisions_code` (`code`),
  KEY `idx_divisions_parent` (`parent_id`),
  CONSTRAINT `fk_divisions_parent` FOREIGN KEY (`parent_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='หน่วยงาน/กอง/สำนัก';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `divisions`
--

LOCK TABLES `divisions` WRITE;
/*!40000 ALTER TABLE `divisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `divisions` ENABLE KEYS */;
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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `level` int DEFAULT '0' COMMENT 'เธฃเธฐเธเธฑเธ 0-5 เธเธฒเธก CSV เธฃเธฒเธขเธเธฒเธฃ 0-5',
  `is_header` tinyint(1) DEFAULT '0' COMMENT 'เนเธเนเธเธซเธฑเธงเธเนเธญเธซเธฅเธฑเธเธซเธฃเธทเธญเนเธกเน',
  `requires_quantity` tinyint(1) DEFAULT '1' COMMENT 'เธเนเธญเธเธฃเธฐเธเธธเธเธณเธเธงเธเธซเธฃเธทเธญเนเธกเน',
  `default_unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'เธเธ' COMMENT 'เธซเธเนเธงเธขเธเธฑเธเนเธฃเธดเนเธกเธเนเธ',
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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'pdf, xlsx, png, etc.',
  `file_size` int NOT NULL COMMENT 'Size in bytes',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fiscal_year` int DEFAULT NULL COMMENT 'ปีงบประมาณ (2568, 2569, ...)',
  `organization_id` int DEFAULT NULL,
  `budget_category_id` int DEFAULT NULL COMMENT 'เชื่อมกับหมวดหมู่งบประมาณ (ถ้ามี)',
  `parent_id` int DEFAULT NULL COMMENT 'โฟลเดอร์แม่ (สำหรับโฟลเดอร์ที่สร้างเอง)',
  `folder_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เส้นทางเต็มของโฟลเดอร์',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `status` enum('achieved','warning','critical','pending','exceeded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'เธชเธเธฒเธเธฐเธเธฅเธฅเธฑเธเธเน',
  `supporting_data` json DEFAULT NULL COMMENT 'เธเนเธญเธกเธนเธฅเนเธเธดเนเธกเนเธเธดเธก (JSON format)',
  `source_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เธญเนเธฒเธเธญเธดเธเนเธซเธฅเนเธเธเนเธญเธกเธนเธฅ',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'เธซเธกเธฒเธขเนเธซเธเธธ',
  `verified_by` int DEFAULT NULL COMMENT 'เธเธนเนเธเธฃเธงเธเธชเธญเธ (FK: users)',
  `verified_at` timestamp NULL DEFAULT NULL COMMENT 'เธงเธฑเธเธเธตเนเธเธฃเธงเธเธชเธญเธ',
  `verification_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'เธเธฑเธเธเธถเธเธเธฒเธฃเธเธฃเธงเธเธชเธญเธ',
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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธฃเธซเธฑเธช KPI',
  `name_th` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธเธทเนเธญ KPI',
  `name_en` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'English name',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'เธเธณเธญเธเธดเธเธฒเธข KPI',
  `metric_type` enum('disbursement_pct','approval_count','processing_time','project_count','activity_completed','percentage','amount','count','days','ratio','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'percentage' COMMENT 'เธเธฃเธฐเนเธ�เธ metric',
  `calculation_method` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'เธงเธดเธเธตเธเธฒเธฃเธเธณเธเธงเธ (SQL เธซเธฃเธทเธญ formula)',
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '%' COMMENT 'เธซเธเนเธงเธขเธงเธฑเธ (%, เธเธฒเธ, เธเธฃเธฑเนเธ, เธงเธฑเธ, เนเธเธฃเธเธเธฒเธฃ, เธเธดเธเธเธฃเธฃเธก)',
  `has_target` tinyint(1) DEFAULT '1' COMMENT 'เธกเธต target เธซเธฃเธทเธญเนเธกเน',
  `target_type` enum('fixed','cumulative','average','minimum','maximum') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fixed' COMMENT 'เธเธฃเธฐเนเธ�เธ target',
  `display_format` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0.00' COMMENT 'เธฃเธนเธเนเธเธเธเธฒเธฃเนเธชเธเธเธเธฅ',
  `color_good` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#22c55e' COMMENT 'เธชเธตเนเธเธตเธขเธง (เธเธฅเธเธต)',
  `color_warning` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#f59e0b' COMMENT 'เธชเธตเนเธซเธฅเธทเธญเธ (เนเธเธทเธญเธ)',
  `color_bad` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#ef4444' COMMENT 'เธชเธตเนเธเธ (เธเธฅเนเธกเนเธเธต)',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เนเธญเธเธญเธ (e.g. chart-line, clock, folder)',
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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธฃเธซเธฑเธชเนเธซเธฅเนเธ',
  `name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธเธทเนเธญเนเธซเธฅเนเธเธเนเธญเธกเธนเธฅ',
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'English name',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'เธฃเธฒเธขเธฅเธฐเนเธญเธตเธขเธ',
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
  `period_type` enum('yearly','quarterly','monthly','weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yearly' COMMENT 'เธเธฃเธฐเนเธ�เธเธเนเธงเธเนเธงเธฅเธฒ',
  `period_value` int DEFAULT NULL COMMENT 'เธเนเธฒเธเนเธงเธเนเธงเธฅเธฒ: Q1-4 (1-4), Month (1-12), Week (1-52), NULL=yearly',
  `period_start_date` date DEFAULT NULL COMMENT 'เธงเธฑเธเนเธฃเธดเนเธกเธเนเธเธเนเธงเธ (เธชเธณเธซเธฃเธฑเธ weekly)',
  `period_end_date` date DEFAULT NULL COMMENT 'เธงเธฑเธเธชเธดเนเธเธชเธธเธเธเนเธงเธ (เธชเธณเธซเธฃเธฑเธ weekly)',
  `target_value` decimal(15,2) NOT NULL COMMENT 'เธเนเธฒเนเธเนเธฒเธซเธกเธฒเธข',
  `threshold_warning` decimal(15,2) DEFAULT NULL COMMENT 'เธเธตเธเนเธเธทเธญเธ (เนเธซเธฅเธทเธญเธ) - เธเนเธณเธเธงเนเธฒเธเธตเนเนเธฃเธดเนเธกเนเธเธทเธญเธ',
  `threshold_critical` decimal(15,2) DEFAULT NULL COMMENT 'เธเธตเธเธงเธดเธเธคเธ (เนเธเธ) - เธเนเธณเธเธงเนเธฒเธเธตเนเธงเธดเธเธคเธ',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'เธซเธกเธฒเธขเนเธซเธเธธ',
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
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. approval_request, approved, rejected',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,2,'approved','คำขอได้รับการอนุมัติ','งบประมาณของคุณได้รับการอนุมัติแล้ว','/requests',0,'2026-06-14 09:00:00');
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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abbreviation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `budget_allocated` decimal(15,2) DEFAULT '0.00',
  `level` int NOT NULL DEFAULT '0' COMMENT 'ระดับ: 0=กระทรวง, 1=กรม, 2=กอง/สำนัก, 3=กลุ่มงาน, 4=จังหวัด/ส่วนราชการ',
  `org_type` enum('ministry','department','division','section','province','office') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'division' COMMENT 'ประเภทหน่วยงาน: กระทรวง/กรม/กอง/กลุ่มงาน/จังหวัด/ส่วนราชการ',
  `province_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'รหัสจังหวัด (สำหรับหน่วยงานส่วนภูมิภาค)',
  `district_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'รหัสอำเภอ 4 หลัก (เฉพาะสำนักงานสาขา L5)',
  `region` enum('central','regional','provincial','central_in_region') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'central',
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เบอร์โทรศัพท์',
  `contact_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'อีเมล',
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'ที่อยู่',
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
  KEY `idx_org_district` (`district_code`),
  CONSTRAINT `fk_organizations_district` FOREIGN KEY (`district_code`) REFERENCES `districts` (`code`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_organizations_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES (1,NULL,'MN-2044','กระทรวงยุติธรรม',NULL,0.00,0,'ministry',NULL,NULL,'central',NULL,NULL,NULL,0,0,'2026-01-01 07:42:31','2026-06-17 16:14:33'),(2,1,'DP-0869','สำนักงานปลัดกระทรวงยุติธรรม',NULL,0.00,1,'department',NULL,NULL,'central',NULL,NULL,NULL,0,0,'2026-01-01 07:42:31','2026-06-17 16:14:33'),(3,2,'DV-5bc1','กองบริหารทรัพยากรบุคคล',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,0,0,'2026-01-01 07:42:31','2026-06-17 16:14:33'),(4,3,'SC-ff2b','กลุ่มงานระบบข้อมูลบุคคล ค่าตอบแทนและบำเหน็จความชอบ',NULL,0.00,3,'section',NULL,NULL,'central',NULL,NULL,NULL,0,0,'2026-01-01 07:42:31','2026-06-17 16:14:33'),(5,NULL,'MOJ','กระทรวงยุติธรรม','ยธ.',0.00,0,'ministry',NULL,NULL,'central',NULL,NULL,NULL,1,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(6,5,'MOJ-OPS','สำนักงานปลัดกระทรวงยุติธรรม','สป.ยธ.',0.00,1,'department',NULL,NULL,'central',NULL,NULL,NULL,1,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(7,5,'MOJ-EXT-LAW','เนติบัณฑิตยสภา/สภาทนายความ',NULL,0.00,1,'office',NULL,NULL,'central',NULL,NULL,NULL,99,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(8,6,'OPS-STRAT','กองยุทธศาสตร์และแผนงาน',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,1,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(9,6,'OPS-PROV','กองประสานราชการยุติธรรมจังหวัด',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,2,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(10,6,'OPS-VOC','สำนักงานส่งเสริมสัมมาชีพและผลิตภัณฑ์เพื่อการพัฒนาพฤตินิสัย',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,3,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(11,6,'OPS-SC','ศูนย์บริการร่วม กระทรวงยุติธรรม',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,4,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(12,6,'OPS-INTL','กองการต่างประเทศ',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,5,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(13,6,'OPS-HRD','สถาบันพัฒนาบุคลากรกระทรวงยุติธรรม',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,6,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(14,6,'OPS-LAW','กองกฎหมาย',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,7,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(15,6,'OPS-CENTRAL','กองกลาง',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,8,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(16,6,'OPS-MIN','สำนักงานรัฐมนตรี',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,9,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(17,6,'OPS-AUDIT','กลุ่มตรวจสอบภายใน',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,10,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(18,6,'OPS-INSP','สำนักผู้ตรวจราชการกระทรวงยุติธรรม',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,11,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(19,6,'OPS-HR','กองบริหารทรัพยากรบุคคล',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,12,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(20,6,'OPS-CONS','กองออกแบบและก่อสร้าง',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,13,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(21,6,'OPS-ICT','ศูนย์เทคโนโลยีสารสนเทศและการสื่อสาร',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,14,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(22,6,'OPS-FIN','กองบริหารการคลัง',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,15,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(23,6,'OPS-PSDG','กลุ่มพัฒนาระบบบริหาร กระทรวงยุติธรรม',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,16,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(24,6,'OPS-ACT','ศูนย์ปฏิบัติการต่อต้านการทุจริต กระทรวงยุติธรรม',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,17,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(25,6,'OPS-INNO','กองพัฒนานวัตกรรมการยุติธรรม',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,18,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(26,6,'OPS-REHAB','กลุ่มภารกิจพัฒนาพฤตินิสัย',NULL,0.00,2,'division',NULL,NULL,'central',NULL,NULL,NULL,19,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(39,8,'OPS-STRAT-CENTRAL','กองยุทธศาสตร์และแผนงาน (บริหารส่วนกลาง)',NULL,0.00,3,'section',NULL,NULL,'central',NULL,NULL,NULL,1,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(40,8,'OPS-STRAT-SBPAC','กองยุทธศาสตร์และแผนงาน ส่วนนโยบายและยุทธศาสตร์จังหวัดชายแดนภาคใต้',NULL,0.00,3,'section',NULL,NULL,'central',NULL,NULL,NULL,2,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(41,22,'OPS-FIN-CENTRAL','กองบริหารการคลัง (ค่าใช้จ่ายส่วนกลาง)',NULL,0.00,3,'section',NULL,NULL,'central',NULL,NULL,NULL,1,1,'2026-06-17 16:14:33','2026-06-17 16:14:33'),(42,9,'PROV-RGN-N','สำนักงานยุติธรรมจังหวัด ภาคเหนือ',NULL,0.00,3,'division',NULL,NULL,'provincial',NULL,NULL,NULL,1,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(43,9,'PROV-RGN-NE','สำนักงานยุติธรรมจังหวัด ภาคตะวันออกเฉียงเหนือ',NULL,0.00,3,'division',NULL,NULL,'provincial',NULL,NULL,NULL,2,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(44,9,'PROV-RGN-C','สำนักงานยุติธรรมจังหวัด ภาคกลาง',NULL,0.00,3,'division',NULL,NULL,'provincial',NULL,NULL,NULL,3,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(45,9,'PROV-RGN-E','สำนักงานยุติธรรมจังหวัด ภาคตะวันออก',NULL,0.00,3,'division',NULL,NULL,'provincial',NULL,NULL,NULL,4,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(46,9,'PROV-RGN-W','สำนักงานยุติธรรมจังหวัด ภาคตะวันตก',NULL,0.00,3,'division',NULL,NULL,'provincial',NULL,NULL,NULL,5,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(47,9,'PROV-RGN-S','สำนักงานยุติธรรมจังหวัด ภาคใต้',NULL,0.00,3,'division',NULL,NULL,'provincial',NULL,NULL,NULL,6,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(49,42,'JP-50','สำนักงานยุติธรรมจังหวัดเชียงใหม่',NULL,0.00,4,'province','50',NULL,'provincial',NULL,NULL,NULL,1,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(50,42,'JP-57','สำนักงานยุติธรรมจังหวัดเชียงราย',NULL,0.00,4,'province','57',NULL,'provincial',NULL,NULL,NULL,2,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(51,42,'JP-55','สำนักงานยุติธรรมจังหวัดน่าน',NULL,0.00,4,'province','55',NULL,'provincial',NULL,NULL,NULL,3,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(52,42,'JP-56','สำนักงานยุติธรรมจังหวัดพะเยา',NULL,0.00,4,'province','56',NULL,'provincial',NULL,NULL,NULL,4,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(53,42,'JP-54','สำนักงานยุติธรรมจังหวัดแพร่',NULL,0.00,4,'province','54',NULL,'provincial',NULL,NULL,NULL,5,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(54,42,'JP-58','สำนักงานยุติธรรมจังหวัดแม่ฮ่องสอน',NULL,0.00,4,'province','58',NULL,'provincial',NULL,NULL,NULL,6,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(55,42,'JP-52','สำนักงานยุติธรรมจังหวัดลำปาง',NULL,0.00,4,'province','52',NULL,'provincial',NULL,NULL,NULL,7,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(56,42,'JP-51','สำนักงานยุติธรรมจังหวัดลำพูน',NULL,0.00,4,'province','51',NULL,'provincial',NULL,NULL,NULL,8,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(57,42,'JP-53','สำนักงานยุติธรรมจังหวัดอุตรดิตถ์',NULL,0.00,4,'province','53',NULL,'provincial',NULL,NULL,NULL,9,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(64,43,'JP-46','สำนักงานยุติธรรมจังหวัดกาฬสินธุ์',NULL,0.00,4,'province','46',NULL,'provincial',NULL,NULL,NULL,1,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(65,43,'JP-40','สำนักงานยุติธรรมจังหวัดขอนแก่น',NULL,0.00,4,'province','40',NULL,'provincial',NULL,NULL,NULL,2,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(66,43,'JP-36','สำนักงานยุติธรรมจังหวัดชัยภูมิ',NULL,0.00,4,'province','36',NULL,'provincial',NULL,NULL,NULL,3,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(67,43,'JP-48','สำนักงานยุติธรรมจังหวัดนครพนม',NULL,0.00,4,'province','48',NULL,'provincial',NULL,NULL,NULL,4,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(68,43,'JP-30','สำนักงานยุติธรรมจังหวัดนครราชสีมา',NULL,0.00,4,'province','30',NULL,'provincial',NULL,NULL,NULL,5,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(69,43,'JP-38','สำนักงานยุติธรรมจังหวัดบึงกาฬ',NULL,0.00,4,'province','38',NULL,'provincial',NULL,NULL,NULL,6,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(70,43,'JP-31','สำนักงานยุติธรรมจังหวัดบุรีรัมย์',NULL,0.00,4,'province','31',NULL,'provincial',NULL,NULL,NULL,7,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(71,43,'JP-44','สำนักงานยุติธรรมจังหวัดมหาสารคาม',NULL,0.00,4,'province','44',NULL,'provincial',NULL,NULL,NULL,8,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(72,43,'JP-49','สำนักงานยุติธรรมจังหวัดมุกดาหาร',NULL,0.00,4,'province','49',NULL,'provincial',NULL,NULL,NULL,9,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(73,43,'JP-35','สำนักงานยุติธรรมจังหวัดยโสธร',NULL,0.00,4,'province','35',NULL,'provincial',NULL,NULL,NULL,10,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(74,43,'JP-45','สำนักงานยุติธรรมจังหวัดร้อยเอ็ด',NULL,0.00,4,'province','45',NULL,'provincial',NULL,NULL,NULL,11,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(75,43,'JP-42','สำนักงานยุติธรรมจังหวัดเลย',NULL,0.00,4,'province','42',NULL,'provincial',NULL,NULL,NULL,12,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(76,43,'JP-33','สำนักงานยุติธรรมจังหวัดศรีสะเกษ',NULL,0.00,4,'province','33',NULL,'provincial',NULL,NULL,NULL,13,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(77,43,'JP-47','สำนักงานยุติธรรมจังหวัดสกลนคร',NULL,0.00,4,'province','47',NULL,'provincial',NULL,NULL,NULL,14,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(78,43,'JP-32','สำนักงานยุติธรรมจังหวัดสุรินทร์',NULL,0.00,4,'province','32',NULL,'provincial',NULL,NULL,NULL,15,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(79,43,'JP-43','สำนักงานยุติธรรมจังหวัดหนองคาย',NULL,0.00,4,'province','43',NULL,'provincial',NULL,NULL,NULL,16,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(80,43,'JP-39','สำนักงานยุติธรรมจังหวัดหนองบัวลำภู',NULL,0.00,4,'province','39',NULL,'provincial',NULL,NULL,NULL,17,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(81,43,'JP-37','สำนักงานยุติธรรมจังหวัดอำนาจเจริญ',NULL,0.00,4,'province','37',NULL,'provincial',NULL,NULL,NULL,18,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(82,43,'JP-41','สำนักงานยุติธรรมจังหวัดอุดรธานี',NULL,0.00,4,'province','41',NULL,'provincial',NULL,NULL,NULL,19,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(83,43,'JP-34','สำนักงานยุติธรรมจังหวัดอุบลราชธานี',NULL,0.00,4,'province','34',NULL,'provincial',NULL,NULL,NULL,20,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(95,44,'JP-62','สำนักงานยุติธรรมจังหวัดกำแพงเพชร',NULL,0.00,4,'province','62',NULL,'provincial',NULL,NULL,NULL,1,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(96,44,'JP-18','สำนักงานยุติธรรมจังหวัดชัยนาท',NULL,0.00,4,'province','18',NULL,'provincial',NULL,NULL,NULL,2,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(97,44,'JP-26','สำนักงานยุติธรรมจังหวัดนครนายก',NULL,0.00,4,'province','26',NULL,'provincial',NULL,NULL,NULL,3,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(98,44,'JP-73','สำนักงานยุติธรรมจังหวัดนครปฐม',NULL,0.00,4,'province','73',NULL,'provincial',NULL,NULL,NULL,4,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(99,44,'JP-60','สำนักงานยุติธรรมจังหวัดนครสวรรค์',NULL,0.00,4,'province','60',NULL,'provincial',NULL,NULL,NULL,5,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(100,44,'JP-12','สำนักงานยุติธรรมจังหวัดนนทบุรี',NULL,0.00,4,'province','12',NULL,'provincial',NULL,NULL,NULL,6,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(101,44,'JP-13','สำนักงานยุติธรรมจังหวัดปทุมธานี',NULL,0.00,4,'province','13',NULL,'provincial',NULL,NULL,NULL,7,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(102,44,'JP-14','สำนักงานยุติธรรมจังหวัดพระนครศรีอยุธยา',NULL,0.00,4,'province','14',NULL,'provincial',NULL,NULL,NULL,8,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(103,44,'JP-66','สำนักงานยุติธรรมจังหวัดพิจิตร',NULL,0.00,4,'province','66',NULL,'provincial',NULL,NULL,NULL,9,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(104,44,'JP-65','สำนักงานยุติธรรมจังหวัดพิษณุโลก',NULL,0.00,4,'province','65',NULL,'provincial',NULL,NULL,NULL,10,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(105,44,'JP-67','สำนักงานยุติธรรมจังหวัดเพชรบูรณ์',NULL,0.00,4,'province','67',NULL,'provincial',NULL,NULL,NULL,11,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(106,44,'JP-16','สำนักงานยุติธรรมจังหวัดลพบุรี',NULL,0.00,4,'province','16',NULL,'provincial',NULL,NULL,NULL,12,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(107,44,'JP-11','สำนักงานยุติธรรมจังหวัดสมุทรปราการ',NULL,0.00,4,'province','11',NULL,'provincial',NULL,NULL,NULL,13,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(108,44,'JP-75','สำนักงานยุติธรรมจังหวัดสมุทรสงคราม',NULL,0.00,4,'province','75',NULL,'provincial',NULL,NULL,NULL,14,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(109,44,'JP-74','สำนักงานยุติธรรมจังหวัดสมุทรสาคร',NULL,0.00,4,'province','74',NULL,'provincial',NULL,NULL,NULL,15,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(110,44,'JP-19','สำนักงานยุติธรรมจังหวัดสระบุรี',NULL,0.00,4,'province','19',NULL,'provincial',NULL,NULL,NULL,16,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(111,44,'JP-17','สำนักงานยุติธรรมจังหวัดสิงห์บุรี',NULL,0.00,4,'province','17',NULL,'provincial',NULL,NULL,NULL,17,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(112,44,'JP-64','สำนักงานยุติธรรมจังหวัดสุโขทัย',NULL,0.00,4,'province','64',NULL,'provincial',NULL,NULL,NULL,18,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(113,44,'JP-72','สำนักงานยุติธรรมจังหวัดสุพรรณบุรี',NULL,0.00,4,'province','72',NULL,'provincial',NULL,NULL,NULL,19,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(114,44,'JP-15','สำนักงานยุติธรรมจังหวัดอ่างทอง',NULL,0.00,4,'province','15',NULL,'provincial',NULL,NULL,NULL,20,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(115,44,'JP-61','สำนักงานยุติธรรมจังหวัดอุทัยธานี',NULL,0.00,4,'province','61',NULL,'provincial',NULL,NULL,NULL,21,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(126,45,'JP-22','สำนักงานยุติธรรมจังหวัดจันทบุรี',NULL,0.00,4,'province','22',NULL,'provincial',NULL,NULL,NULL,1,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(127,45,'JP-24','สำนักงานยุติธรรมจังหวัดฉะเชิงเทรา',NULL,0.00,4,'province','24',NULL,'provincial',NULL,NULL,NULL,2,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(128,45,'JP-20','สำนักงานยุติธรรมจังหวัดชลบุรี',NULL,0.00,4,'province','20',NULL,'provincial',NULL,NULL,NULL,3,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(129,45,'JP-23','สำนักงานยุติธรรมจังหวัดตราด',NULL,0.00,4,'province','23',NULL,'provincial',NULL,NULL,NULL,4,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(130,45,'JP-25','สำนักงานยุติธรรมจังหวัดปราจีนบุรี',NULL,0.00,4,'province','25',NULL,'provincial',NULL,NULL,NULL,5,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(131,45,'JP-21','สำนักงานยุติธรรมจังหวัดระยอง',NULL,0.00,4,'province','21',NULL,'provincial',NULL,NULL,NULL,6,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(132,45,'JP-27','สำนักงานยุติธรรมจังหวัดสระแก้ว',NULL,0.00,4,'province','27',NULL,'provincial',NULL,NULL,NULL,7,1,'2026-06-17 21:36:08','2026-06-17 21:36:08'),(133,46,'JP-71','สำนักงานยุติธรรมจังหวัดกาญจนบุรี',NULL,0.00,4,'province','71',NULL,'provincial',NULL,NULL,NULL,1,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(134,46,'JP-63','สำนักงานยุติธรรมจังหวัดตาก',NULL,0.00,4,'province','63',NULL,'provincial',NULL,NULL,NULL,2,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(135,46,'JP-77','สำนักงานยุติธรรมจังหวัดประจวบคีรีขันธ์',NULL,0.00,4,'province','77',NULL,'provincial',NULL,NULL,NULL,3,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(136,46,'JP-76','สำนักงานยุติธรรมจังหวัดเพชรบุรี',NULL,0.00,4,'province','76',NULL,'provincial',NULL,NULL,NULL,4,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(137,46,'JP-70','สำนักงานยุติธรรมจังหวัดราชบุรี',NULL,0.00,4,'province','70',NULL,'provincial',NULL,NULL,NULL,5,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(140,47,'JP-81','สำนักงานยุติธรรมจังหวัดกระบี่',NULL,0.00,4,'province','81',NULL,'provincial',NULL,NULL,NULL,1,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(141,47,'JP-86','สำนักงานยุติธรรมจังหวัดชุมพร',NULL,0.00,4,'province','86',NULL,'provincial',NULL,NULL,NULL,2,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(142,47,'JP-92','สำนักงานยุติธรรมจังหวัดตรัง',NULL,0.00,4,'province','92',NULL,'provincial',NULL,NULL,NULL,3,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(143,47,'JP-80','สำนักงานยุติธรรมจังหวัดนครศรีธรรมราช',NULL,0.00,4,'province','80',NULL,'provincial',NULL,NULL,NULL,4,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(144,47,'JP-96','สำนักงานยุติธรรมจังหวัดนราธิวาส',NULL,0.00,4,'province','96',NULL,'provincial',NULL,NULL,NULL,5,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(145,47,'JP-94','สำนักงานยุติธรรมจังหวัดปัตตานี',NULL,0.00,4,'province','94',NULL,'provincial',NULL,NULL,NULL,6,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(146,47,'JP-82','สำนักงานยุติธรรมจังหวัดพังงา',NULL,0.00,4,'province','82',NULL,'provincial',NULL,NULL,NULL,7,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(147,47,'JP-93','สำนักงานยุติธรรมจังหวัดพัทลุง',NULL,0.00,4,'province','93',NULL,'provincial',NULL,NULL,NULL,8,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(148,47,'JP-83','สำนักงานยุติธรรมจังหวัดภูเก็ต',NULL,0.00,4,'province','83',NULL,'provincial',NULL,NULL,NULL,9,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(149,47,'JP-95','สำนักงานยุติธรรมจังหวัดยะลา',NULL,0.00,4,'province','95',NULL,'provincial',NULL,NULL,NULL,10,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(150,47,'JP-85','สำนักงานยุติธรรมจังหวัดระนอง',NULL,0.00,4,'province','85',NULL,'provincial',NULL,NULL,NULL,11,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(151,47,'JP-90','สำนักงานยุติธรรมจังหวัดสงขลา',NULL,0.00,4,'province','90',NULL,'provincial',NULL,NULL,NULL,12,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(152,47,'JP-91','สำนักงานยุติธรรมจังหวัดสตูล',NULL,0.00,4,'province','91',NULL,'provincial',NULL,NULL,NULL,13,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(153,47,'JP-84','สำนักงานยุติธรรมจังหวัดสุราษฎร์ธานี',NULL,0.00,4,'province','84',NULL,'provincial',NULL,NULL,NULL,14,1,'2026-06-17 21:36:09','2026-06-17 21:36:09'),(155,70,'JP-3104','สำนักงานยุติธรรมจังหวัดบุรีรัมย์ สาขานางรอง',NULL,0.00,5,'office','31','3104','provincial',NULL,NULL,NULL,1,1,'2026-06-17 21:36:09','2026-08-09 09:00:59'),(156,50,'JP-5704','สำนักงานยุติธรรมจังหวัดเชียงราย สาขาเทิง',NULL,0.00,5,'office','57','5704','provincial',NULL,NULL,NULL,2,1,'2026-06-17 21:36:09','2026-08-09 09:00:59'),(157,149,'JP-9502','สำนักงานยุติธรรมจังหวัดยะลา สาขาเบตง',NULL,0.00,5,'office','95','9502','provincial',NULL,NULL,NULL,3,1,'2026-06-17 21:36:09','2026-08-09 09:00:59'),(158,105,'JP-6703','สำนักงานยุติธรรมจังหวัดเพชรบูรณ์ สาขาหล่มสัก',NULL,0.00,5,'office','67','6703','provincial',NULL,NULL,NULL,4,1,'2026-06-17 21:36:09','2026-08-09 09:00:59'),(159,133,'JP-7107','สำนักงานยุติธรรมจังหวัดกาญจนบุรี สาขาทองผาภูมิ',NULL,0.00,5,'office','71','7107','provincial',NULL,NULL,NULL,5,1,'2026-06-17 21:36:09','2026-08-09 09:00:59');
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เช่น budget.edit, request.approve',
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'กลุ่ม: budget/request/disbursement/org/user/role/report',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_code` (`code`),
  KEY `idx_permissions_resource` (`resource`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='สิทธิ์รายข้อ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'budget.view','ดูข้อมูลงบประมาณ','budget','2026-06-17 15:04:18'),(2,'budget.create','สร้างข้อมูลงบประมาณ','budget','2026-06-17 15:04:18'),(3,'budget.edit','แก้ไขข้อมูลงบประมาณ','budget','2026-06-17 15:04:18'),(4,'budget.delete','ลบข้อมูลงบประมาณ','budget','2026-06-17 15:04:18'),(5,'request.view','ดูคำขอ','request','2026-06-17 15:04:18'),(6,'request.create','สร้างคำขอ','request','2026-06-17 15:04:18'),(7,'request.submit','ส่งคำขอ','request','2026-06-17 15:04:18'),(8,'request.approve','อนุมัติคำขอ','request','2026-06-17 15:04:18'),(9,'request.reject','ไม่อนุมัติคำขอ','request','2026-06-17 15:04:18'),(10,'disbursement.view','ดูข้อมูลการเบิกจ่าย','disbursement','2026-06-17 15:04:18'),(11,'disbursement.record','บันทึกการเบิกจ่าย','disbursement','2026-06-17 15:04:18'),(12,'org.view','ดูข้อมูลหน่วยงาน','org','2026-06-17 15:04:18'),(13,'org.manage','จัดการหน่วยงาน','org','2026-06-17 15:04:18'),(14,'user.manage','จัดการผู้ใช้และการมอบบทบาท','user','2026-06-17 15:04:18'),(15,'role.manage','จัดการบทบาทและสิทธิ์','role','2026-06-17 15:04:18'),(16,'masterdata.manage','จัดการข้อมูลหลัก','masterdata','2026-06-17 15:04:18'),(17,'report.view','ดูรายงาน','report','2026-06-17 15:04:18');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_th` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `project_type` enum('output','project') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'output' COMMENT 'เธเธฃเธฐเนเธ�เธ: เธเธฅเธเธฅเธดเธ เธซเธฃเธทเธญ เนเธเธฃเธเธเธฒเธฃ',
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
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'เธฃเธซเธฑเธชเธเธฑเธเธซเธงเธฑเธ',
  `name_th` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'เธเธณเธญเธเธดเธเธฒเธขเนเธเธดเนเธกเนเธเธดเธก',
  `region` enum('central','north','northeast','east','west','south') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'central' COMMENT 'เธ�เธฒเธ',
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
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เธเธฑเธเธซเธงเธฑเธ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provinces`
--

LOCK TABLES `provinces` WRITE;
/*!40000 ALTER TABLE `provinces` DISABLE KEYS */;
INSERT INTO `provinces` VALUES (1,'10','กรุงเทพมหานคร','Bangkok',NULL,'central',NULL,NULL,NULL,10,1,NULL,'2026-01-01 07:42:31','2026-06-17 23:17:44',NULL,NULL),(154,'11','สมุทรปราการ','Samut Prakan',NULL,'central',NULL,NULL,NULL,11,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(155,'12','นนทบุรี','Nonthaburi',NULL,'central',NULL,NULL,NULL,12,1,NULL,'2026-06-17 23:34:56','2026-08-09 09:00:51',NULL,NULL),(156,'13','ปทุมธานี','Pathum Thani',NULL,'central',NULL,NULL,NULL,13,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(157,'14','พระนครศรีอยุธยา','Phra Nakhon Si Ayutthaya',NULL,'central',NULL,NULL,NULL,14,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(158,'15','อ่างทอง','Ang Thong',NULL,'central',NULL,NULL,NULL,15,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(159,'16','ลพบุรี','Lop Buri',NULL,'central',NULL,NULL,NULL,16,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(160,'17','สิงห์บุรี','Sing Buri',NULL,'central',NULL,NULL,NULL,17,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(161,'18','ชัยนาท','Chai Nat',NULL,'central',NULL,NULL,NULL,18,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(162,'19','สระบุรี','Saraburi',NULL,'central',NULL,NULL,NULL,19,1,NULL,'2026-06-17 23:34:56','2026-08-09 09:00:51',NULL,NULL),(163,'20','ชลบุรี','Chon Buri',NULL,'east',NULL,NULL,NULL,20,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(164,'21','ระยอง','Rayong',NULL,'east',NULL,NULL,NULL,21,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(165,'22','จันทบุรี','Chanthaburi',NULL,'east',NULL,NULL,NULL,22,1,NULL,'2026-06-17 23:34:56','2026-08-09 09:00:51',NULL,NULL),(166,'23','ตราด','Trat',NULL,'east',NULL,NULL,NULL,23,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(167,'24','ฉะเชิงเทรา','Chachoengsao',NULL,'east',NULL,NULL,NULL,24,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(168,'25','ปราจีนบุรี','Prachin Buri',NULL,'east',NULL,NULL,NULL,25,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(169,'26','นครนายก','Nakhon Nayok',NULL,'central',NULL,NULL,NULL,26,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(170,'27','สระแก้ว','Sa Kaeo',NULL,'east',NULL,NULL,NULL,27,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(171,'30','นครราชสีมา','Nakhon Ratchasima',NULL,'northeast',NULL,NULL,NULL,30,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(172,'31','บุรีรัมย์','Buri Ram',NULL,'northeast',NULL,NULL,NULL,31,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(173,'32','สุรินทร์','Surin',NULL,'northeast',NULL,NULL,NULL,32,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(174,'33','ศรีสะเกษ','Si Sa Ket',NULL,'northeast',NULL,NULL,NULL,33,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(175,'34','อุบลราชธานี','Ubon Ratchathani',NULL,'northeast',NULL,NULL,NULL,34,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(176,'35','ยโสธร','Yasothon',NULL,'northeast',NULL,NULL,NULL,35,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(177,'36','ชัยภูมิ','Chaiyaphum',NULL,'northeast',NULL,NULL,NULL,36,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(178,'37','อำนาจเจริญ','Amnat Charoen',NULL,'northeast',NULL,NULL,NULL,37,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(179,'38','บึงกาฬ','Bueng Kan',NULL,'northeast',NULL,NULL,NULL,38,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(180,'39','หนองบัวลำภู','Nong Bua Lam Phu',NULL,'northeast',NULL,NULL,NULL,39,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(181,'40','ขอนแก่น','Khon Kaen',NULL,'northeast',NULL,NULL,NULL,40,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(182,'41','อุดรธานี','Udon Thani',NULL,'northeast',NULL,NULL,NULL,41,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(183,'42','เลย','Loei',NULL,'northeast',NULL,NULL,NULL,42,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(184,'43','หนองคาย','Nong Khai',NULL,'northeast',NULL,NULL,NULL,43,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(185,'44','มหาสารคาม','Maha Sarakham',NULL,'northeast',NULL,NULL,NULL,44,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(186,'45','ร้อยเอ็ด','Roi Et',NULL,'northeast',NULL,NULL,NULL,45,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(187,'46','กาฬสินธุ์','Kalasin',NULL,'northeast',NULL,NULL,NULL,46,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(188,'47','สกลนคร','Sakon Nakhon',NULL,'northeast',NULL,NULL,NULL,47,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(189,'48','นครพนม','Nakhon Phanom',NULL,'northeast',NULL,NULL,NULL,48,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(190,'49','มุกดาหาร','Mukdahan',NULL,'northeast',NULL,NULL,NULL,49,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(191,'50','เชียงใหม่','Chiang Mai',NULL,'north',NULL,NULL,NULL,50,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(192,'51','ลำพูน','Lamphun',NULL,'north',NULL,NULL,NULL,51,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(193,'52','ลำปาง','Lampang',NULL,'north',NULL,NULL,NULL,52,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(194,'53','อุตรดิตถ์','Uttaradit',NULL,'north',NULL,NULL,NULL,53,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(195,'54','แพร่','Phrae',NULL,'north',NULL,NULL,NULL,54,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(196,'55','น่าน','Nan',NULL,'north',NULL,NULL,NULL,55,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(197,'56','พะเยา','Phayao',NULL,'north',NULL,NULL,NULL,56,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(198,'57','เชียงราย','Chiang Rai',NULL,'north',NULL,NULL,NULL,57,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(199,'58','แม่ฮ่องสอน','Mae Hong Son',NULL,'north',NULL,NULL,NULL,58,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(200,'60','นครสวรรค์','Nakhon Sawan',NULL,'central',NULL,NULL,NULL,60,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(201,'61','อุทัยธานี','Uthai Thani',NULL,'central',NULL,NULL,NULL,61,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(202,'62','กำแพงเพชร','Kamphaeng Phet',NULL,'central',NULL,NULL,NULL,62,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(203,'63','ตาก','Tak',NULL,'west',NULL,NULL,NULL,63,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(204,'64','สุโขทัย','Sukhothai',NULL,'central',NULL,NULL,NULL,64,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(205,'65','พิษณุโลก','Phitsanulok',NULL,'central',NULL,NULL,NULL,65,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(206,'66','พิจิตร','Phichit',NULL,'central',NULL,NULL,NULL,66,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(207,'67','เพชรบูรณ์','Phetchabun',NULL,'central',NULL,NULL,NULL,67,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(208,'70','ราชบุรี','Ratchaburi',NULL,'west',NULL,NULL,NULL,70,1,NULL,'2026-06-17 23:34:56','2026-08-09 09:00:51',NULL,NULL),(209,'71','กาญจนบุรี','Kanchanaburi',NULL,'west',NULL,NULL,NULL,71,1,NULL,'2026-06-17 23:34:56','2026-08-09 09:00:51',NULL,NULL),(210,'72','สุพรรณบุรี','Suphan Buri',NULL,'central',NULL,NULL,NULL,72,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(211,'73','นครปฐม','Nakhon Pathom',NULL,'central',NULL,NULL,NULL,73,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(212,'74','สมุทรสาคร','Samut Sakhon',NULL,'central',NULL,NULL,NULL,74,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(213,'75','สมุทรสงคราม','Samut Songkhram',NULL,'central',NULL,NULL,NULL,75,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(214,'76','เพชรบุรี','Phetchaburi',NULL,'west',NULL,NULL,NULL,76,1,NULL,'2026-06-17 23:34:56','2026-08-09 09:00:51',NULL,NULL),(215,'77','ประจวบคีรีขันธ์','Prachuap Khiri Khan',NULL,'west',NULL,NULL,NULL,77,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(216,'80','นครศรีธรรมราช','Nakhon Si Thammarat',NULL,'south',NULL,NULL,NULL,80,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(217,'81','กระบี่','Krabi',NULL,'south',NULL,NULL,NULL,81,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(218,'82','พังงา','Phang-nga',NULL,'south',NULL,NULL,NULL,82,1,NULL,'2026-06-17 23:34:56','2026-08-09 09:00:51',NULL,NULL),(219,'83','ภูเก็ต','Phuket',NULL,'south',NULL,NULL,NULL,83,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(220,'84','สุราษฎร์ธานี','Surat Thani',NULL,'south',NULL,NULL,NULL,84,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(221,'85','ระนอง','Ranong',NULL,'south',NULL,NULL,NULL,85,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(222,'86','ชุมพร','Chumphon',NULL,'south',NULL,NULL,NULL,86,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(223,'90','สงขลา','Songkhla',NULL,'south',NULL,NULL,NULL,90,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(224,'91','สตูล','Satun',NULL,'south',NULL,NULL,NULL,91,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(225,'92','ตรัง','Trang',NULL,'south',NULL,NULL,NULL,92,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(226,'93','พัทลุง','Phatthalung',NULL,'south',NULL,NULL,NULL,93,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(227,'94','ปัตตานี','Pattani',NULL,'south',NULL,NULL,NULL,94,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(228,'95','ยะลา','Yala',NULL,'south',NULL,NULL,NULL,95,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL),(229,'96','นราธิวาส','Narathiwat',NULL,'south',NULL,NULL,NULL,96,1,NULL,'2026-06-17 23:34:56','2026-06-17 23:34:56',NULL,NULL);
/*!40000 ALTER TABLE `provinces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `idx_rp_permission` (`permission_id`),
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='แมปบทบาท-สิทธิ์';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(1,2),(2,2),(3,2),(4,2),(5,2),(1,3),(2,3),(3,3),(4,3),(5,3),(1,4),(2,4),(1,5),(2,5),(3,5),(4,5),(5,5),(6,5),(7,5),(8,5),(9,5),(10,5),(11,5),(1,6),(2,6),(3,6),(4,6),(1,7),(2,7),(3,7),(4,7),(1,8),(2,8),(6,8),(7,8),(8,8),(1,9),(2,9),(6,9),(7,9),(8,9),(1,10),(2,10),(3,10),(4,10),(5,10),(6,10),(7,10),(8,10),(9,10),(10,10),(11,10),(1,11),(2,11),(4,11),(5,11),(1,12),(2,12),(3,12),(4,12),(5,12),(6,12),(7,12),(8,12),(9,12),(10,12),(11,12),(1,13),(1,14),(2,14),(1,15),(1,16),(1,17),(2,17),(3,17),(4,17),(5,17),(6,17),(7,17),(8,17),(9,17),(10,17),(11,17);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'บทบาทระบบ ลบ/แก้ code ไม่ได้',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'เปิด/ปิดการใช้งานบทบาท',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='บทบาทผู้ใช้ (RBAC)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','ผู้ดูแลระบบสูงสุด','Super Administrator',NULL,1,1,1,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(2,'org_admin','ผู้ดูแลหน่วยงาน','Organization Admin',NULL,0,1,2,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(3,'planner','เจ้าหน้าที่แผน/จัดทำคำขอ','Planner',NULL,0,1,3,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(4,'budget_editor','เจ้าหน้าที่งบประมาณ','Budget Editor',NULL,0,1,4,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(5,'finance_officer','เจ้าหน้าที่การเงิน/เบิกจ่าย','Finance Officer',NULL,0,1,5,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(6,'approver_division','ผู้อนุมัติระดับกอง','Division Approver',NULL,0,1,6,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(7,'approver_department','ผู้อนุมัติระดับกรม','Department Approver',NULL,0,1,7,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(8,'approver_ministry','ผู้อนุมัติระดับกระทรวง','Ministry Approver',NULL,0,1,8,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(9,'auditor','ผู้ตรวจสอบ','Auditor',NULL,0,1,9,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(10,'executive','ผู้บริหาร (ดูภาพรวม)','Executive',NULL,0,1,10,'2026-06-17 15:04:18','2026-06-17 15:04:18'),(11,'viewer','ผู้ดูข้อมูลทั่วไป','Viewer',NULL,0,1,11,'2026-06-17 15:04:18','2026-06-17 15:04:18');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
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
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'python_etl',
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
-- Table structure for table `target_types`
--

DROP TABLE IF EXISTS `target_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `target_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'รหัสประเภทเป้าหมาย',
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ชื่อประเภทเป้าหมาย',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_target_types_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ประเภทเป้าหมาย';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `target_types`
--

LOCK TABLES `target_types` WRITE;
/*!40000 ALTER TABLE `target_types` DISABLE KEYS */;
INSERT INTO `target_types` VALUES (3,'TGX-d8cmxx','ประเภทเป้าหมายสำหรับเทสต์ d8cmxx','',1,0,'2026-06-14 03:34:12','2026-06-14 03:34:12'),(5,'TGX-d8gh8n','ประเภทเป้าหมายสำหรับเทสต์ d8gh8n','',1,0,'2026-06-14 03:37:15','2026-06-14 03:37:15'),(6,'TGX-d8ne6x','ประเภทเป้าหมายสำหรับเทสต์ d8ne6x','',1,0,'2026-06-14 03:42:38','2026-06-14 03:42:38'),(8,'TGX-d9u4cm','ประเภทเป้าหมายสำหรับเทสต์ d9u4cm','',1,0,'2026-06-14 04:15:46','2026-06-14 04:15:46');
/*!40000 ALTER TABLE `target_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_access_grants`
--

DROP TABLE IF EXISTS `user_access_grants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_access_grants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `scope_type` enum('organization','all','category','region') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'organization',
  `scope_ref_id` int DEFAULT NULL COMMENT 'NULL เมื่อ scope=all',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_grant` (`user_id`,`role_id`,`scope_type`,`scope_ref_id`),
  KEY `idx_grant_user` (`user_id`),
  KEY `idx_grant_scope` (`scope_type`,`scope_ref_id`),
  KEY `fk_grant_role` (`role_id`),
  CONSTRAINT `fk_grant_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_grant_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='การมอบบทบาท+ขอบเขตให้ผู้ใช้';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_access_grants`
--

LOCK TABLES `user_access_grants` WRITE;
/*!40000 ALTER TABLE `user_access_grants` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_access_grants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','editor','viewer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'viewer',
  `is_active` tinyint(1) DEFAULT '1',
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `thaid_sub` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `uniq_thaid_sub` (`thaid_sub`)
) ENGINE=InnoDB AUTO_INCREMENT=313 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@hrbudget.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator',NULL,'admin',1,'IT','2025-12-12 14:52:00','2025-12-12 14:52:00',NULL,NULL),(2,'admin@moj.go.th','$2y$10$EOT5yECB0sAZXYb9M7ez1ep8ZLZC4s/5ma/UCDySeV1fx7.zD/MeS','Admin User',NULL,'admin',1,'IT','2025-12-13 04:13:56','2026-01-17 03:12:10','2026-01-17 03:12:10',NULL),(3,'viewer@moj.go.th','$2y$10$AUOll9s5YC2eeRKCsn.sa.aaoI2j0hzwvB9TbDtSQ0lpoHqrXfxhi','Viewer User',NULL,'viewer',1,'Finance','2025-12-13 04:13:57','2025-12-14 03:59:16',NULL,NULL),(5,'editor@moj.go.th','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Editor User',NULL,'editor',1,'เธเธฃเธกเธเธธเธกเธเธฃเธฐเธเธคเธเธด','2025-12-14 03:59:16','2025-12-14 03:59:16',NULL,NULL),(189,'thaid.user@moj.go.th','$2y$10$hT6j7Hu6gre5QZb.PO1Nk.GAKWjwDwdWSIEYNwgPWBkFn3.AwJlzS','ผู้ใช้ ThaID (Mock)',NULL,'viewer',1,'กระทรวงยุติธรรม','2025-12-18 11:30:51','2025-12-18 11:30:51',NULL,NULL);
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

--
-- Dumping routines for database 'hr_budget'
--

--
-- Final view structure for view `v_kpi_dashboard`
--

/*!50001 DROP VIEW IF EXISTS `v_kpi_dashboard`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_kpi_dashboard` AS select 1 AS `source_code`,1 AS `source_name`,1 AS `kpi_code`,1 AS `kpi_name`,1 AS `metric_type`,1 AS `unit`,1 AS `fiscal_year`,1 AS `period_type`,1 AS `period_value`,1 AS `target_value`,1 AS `threshold_warning`,1 AS `threshold_critical`,1 AS `actual_value`,1 AS `achievement_rate`,1 AS `status`,1 AS `recorded_date`,1 AS `color_good`,1 AS `color_warning`,1 AS `color_bad` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_organizations_hierarchy`
--

/*!50001 DROP VIEW IF EXISTS `v_organizations_hierarchy`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_organizations_hierarchy` AS select 1 AS `id`,1 AS `parent_id`,1 AS `code`,1 AS `name_th`,1 AS `abbreviation`,1 AS `budget_allocated`,1 AS `level`,1 AS `org_type`,1 AS `province_code`,1 AS `region`,1 AS `contact_phone`,1 AS `contact_email`,1 AS `address`,1 AS `sort_order`,1 AS `is_active`,1 AS `created_at`,1 AS `updated_at`,1 AS `parent_name`,1 AS `parent_code`,1 AS `org_type_label`,1 AS `region_label` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-09 20:15:36
