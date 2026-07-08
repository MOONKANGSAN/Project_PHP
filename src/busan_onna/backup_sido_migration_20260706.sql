-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: busan_onna
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `busan_restaurant`
--

DROP TABLE IF EXISTS `busan_restaurant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `busan_restaurant` (
  `idx` int unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `star_point` decimal(3,1) NOT NULL DEFAULT '0.0',
  `info` text COLLATE utf8mb4_unicode_ci,
  `address1` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address2` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_num` tinyint NOT NULL DEFAULT '0',
  `price_range` tinyint(1) NOT NULL DEFAULT '1',
  `thumb_idx` int unsigned DEFAULT NULL,
  `hashtag_idx` text COLLATE utf8mb4_unicode_ci,
  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edit_date` datetime DEFAULT NULL,
  `view_cnt` int unsigned NOT NULL DEFAULT '0',
  `like_cnt` int unsigned NOT NULL DEFAULT '0',
  `sido` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'lat,lng 좌표',
  `longitude` decimal(11,8) DEFAULT NULL,
  `reg_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_time` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parking` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idx`),
  KEY `state` (`state`),
  KEY `category_num` (`category_num`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `busan_restaurant`
--

LOCK TABLES `busan_restaurant` WRITE;
/*!40000 ALTER TABLE `busan_restaurant` DISABLE KEYS */;
INSERT INTO `busan_restaurant` VALUES (1,1,'강산국밥',0.0,'아호오오오홍호','진남로 135','수영로 135 대연롯데캐슬레전드','025-191-1458',3,2,NULL,NULL,'2026-06-29 05:55:23','2026-06-29 05:55:23',4,0,'남구',NULL,NULL,'admin1','06:00~22:30',1),(2,1,'강산국밥',0.0,'아호오오오홍호','진남로 135','수영로 135 대연롯데캐슬레전드','025-191-1458',3,2,NULL,NULL,'2026-06-29 06:00:28','2026-06-29 06:00:28',0,0,'남구',NULL,NULL,'admin1','06:00~22:30',1),(3,1,'베츠노히토노',0.0,'카레시니','진남로 135','수영로 135','010-2561-4258',2,2,NULL,NULL,'2026-06-29 06:02:36','2026-06-29 06:02:36',0,0,'남구',NULL,NULL,'admin1','05:30~21:30',1),(4,1,'아버지여',0.0,'고쳐주소서','부산광역시 테헤란로','우로롱빌딩 101호','051-915-3578',3,2,1,NULL,'2026-06-29 06:11:08','2026-06-29 06:11:08',0,0,'부산',NULL,NULL,'admin1','01:30~11:00',1),(5,1,'원조 할매 돼지국밥',0.0,'50년 전통의 부산 서면 돼지국밥 원조 맛집. 진하고 구수한 국물이 일품입니다.','부산광역시 부산진구 서면로 12',NULL,'051-123-1001',1,1,NULL,NULL,'2026-06-09 06:18:40','2026-06-29 06:18:40',61,41,'부산광역시',NULL,NULL,'admin','06:00~21:00',0),(6,1,'해운대 암소갈비집',0.0,'해운대 해변 인근 프리미엄 한우 암소갈비 전문점. 생갈비와 LA갈비가 유명합니다.','부산광역시 해운대구 해운대해변로 264',NULL,'051-123-1002',1,3,NULL,NULL,'2026-06-10 06:18:40','2026-06-29 06:18:40',348,89,'부산광역시',NULL,NULL,'admin','11:00~22:00',1),(7,1,'광안리 조개구이 바다',0.0,'광안대교 야경을 보며 즐기는 싱싱한 조개구이. 바지락, 소라, 전복 등 다양한 해산물.','부산광역시 수영구 광안해변로 219',NULL,'051-123-1003',1,2,NULL,NULL,'2026-06-11 06:18:40','2026-06-29 06:18:40',467,67,'부산광역시',NULL,NULL,'admin','15:00~23:30',0),(8,1,'남포동 씨앗호떡 본점',0.0,'부산 남포동 BIFF광장 대표 길거리 음식. 고소한 씨앗이 가득한 달콤한 호떡.','부산광역시 중구 BIFF광장로 21',NULL,'051-123-1004',5,1,NULL,NULL,'2026-06-12 06:18:40','2026-06-29 06:18:40',289,2,'부산광역시',NULL,NULL,'admin','10:00~22:00',0),(9,1,'스시 오마카세 하루',0.0,'해운대 프리미엄 일식 오마카세. 매일 공수되는 신선한 제철 생선으로 구성.','부산광역시 해운대구 달맞이길 62번길 14',NULL,'051-123-1005',2,3,NULL,NULL,'2026-06-13 06:18:40','2026-06-29 06:18:40',289,80,'부산광역시',NULL,NULL,'admin','12:00~22:00',1),(10,1,'카페 선셋 해운대',0.0,'해운대 해변이 보이는 오션뷰 카페. 수제 디저트와 스페셜티 커피를 즐길 수 있습니다.','부산광역시 해운대구 해운대해변로 120',NULL,'051-123-1006',6,2,NULL,NULL,'2026-06-14 06:18:40','2026-06-29 06:18:40',106,63,'부산광역시',NULL,NULL,'admin','09:00~22:00',0),(11,1,'기장 대게 직판장',0.0,'기장 앞바다에서 갓 잡은 싱싱한 대게와 킹크랩을 합리적인 가격에 제공합니다.','부산광역시 기장군 기장읍 연화리 12',NULL,'051-123-1007',1,3,NULL,NULL,'2026-06-15 06:18:40','2026-06-29 06:18:40',350,21,'부산광역시',NULL,NULL,'admin','10:00~21:00',1),(12,1,'동래 할매파전',0.0,'동래 파전 골목의 대표 맛집. 바삭하고 쫄깃한 파전에 막걸리 한 잔이 일품.','부산광역시 동래구 온천장로 95',NULL,'051-123-1008',5,1,NULL,NULL,'2026-06-16 06:18:40','2026-06-29 06:18:40',464,81,'부산광역시',NULL,NULL,'admin','11:00~21:00',0),(13,1,'브루어리 버거 해운대',0.0,'수제 패티와 브루어리 맥주를 함께 즐길 수 있는 해운대 핫플. 두툼한 수제버거가 인기.','부산광역시 해운대구 중동1로 56',NULL,'051-123-1009',4,2,NULL,NULL,'2026-06-17 06:18:40','2026-06-29 06:18:40',385,23,'부산광역시',NULL,NULL,'admin','11:30~22:00',0),(14,1,'홍보각 중화요리',0.0,'서면 30년 전통 중화요리 전문점. 짬뽕, 탕수육, 코스 요리 모두 인기 메뉴.','부산광역시 부산진구 서면문화로 20',NULL,'051-123-1010',3,2,NULL,NULL,'2026-06-18 06:18:40','2026-06-29 06:18:40',113,21,'부산광역시',NULL,NULL,'admin','11:00~21:30',1),(15,1,'부산역 원조 밀면',0.0,'부산 대표 향토 음식 밀면의 원조. 쫄깃한 면발과 시원한 육수가 특징.','부산광역시 동구 중앙대로 196',NULL,'051-123-1011',1,1,NULL,NULL,'2026-06-19 06:18:40','2026-06-29 06:18:40',340,60,'부산광역시',NULL,NULL,'admin','10:00~20:00',0),(16,1,'그랜드 뷔페 센텀',0.0,'센텀시티 프리미엄 뷔페. 해산물, 스테이크, 스시, 디저트 등 200여 가지 메뉴.','부산광역시 해운대구 센텀남대로 35',NULL,'051-123-1012',7,3,NULL,NULL,'2026-06-20 06:18:40','2026-06-29 06:18:40',223,90,'부산광역시',NULL,NULL,'admin','11:30~22:00',1),(17,1,'나폴리 피자 광안리',0.0,'나폴리 정통 방식의 화덕 피자 전문점. 모짜렐라 마르게리타와 트러플 피자가 인기.','부산광역시 수영구 광안해변로 57',NULL,'051-123-1013',4,2,NULL,NULL,'2026-06-21 06:18:40','2026-06-29 06:18:40',301,76,'부산광역시',NULL,NULL,'admin','11:00~22:30',0),(18,1,'초량 이바구 분식',0.0,'초량 이바구길 골목 분식 맛집. 떡볶이, 순대, 어묵이 조합된 모듬이 인기.','부산광역시 동구 초량상로 77',NULL,'051-123-1014',5,1,NULL,NULL,'2026-06-22 06:18:40','2026-06-29 06:18:40',276,70,'부산광역시',NULL,NULL,'admin','10:00~19:00',0),(19,1,'남포동 통닭 골목 원조',0.0,'남포동 통닭 골목의 60년 전통 바삭 통닭집. 옛날 방식 그대로의 바삭한 튀김옷.','부산광역시 중구 광복로 51',NULL,'051-123-1015',1,1,NULL,NULL,'2026-06-23 06:18:40','2026-06-29 06:18:40',314,9,'부산광역시',NULL,NULL,'admin','11:00~21:00',0),(20,1,'기장 멸치쌈밥 정식',0.0,'기장 앞바다 멸치로 만든 쌈밥 정식 전문점. 멸치회, 멸치쌈밥, 멸치조림 코스.','부산광역시 기장군 기장읍 차성로 262',NULL,'051-123-1016',1,2,NULL,NULL,'2026-06-24 06:18:40','2026-06-29 06:18:40',313,77,'부산광역시',NULL,NULL,'admin','10:30~20:30',1),(21,1,'비스트로 수영',0.0,'수영구 감성 이탈리안 레스토랑. 파스타, 리조또, 와인 페어링 코스 전문.','부산광역시 수영구 수영로 365',NULL,'051-123-1017',4,3,NULL,NULL,'2026-06-25 06:18:40','2026-06-29 06:18:40',87,98,'부산광역시',NULL,NULL,'admin','11:30~21:30',0),(22,1,'해운대 낙지볶음 명가',0.0,'해운대 현지인 단골 낙지볶음 맛집. 산낙지와 볶음 낙지 모두 당일 직접 공수.','부산광역시 해운대구 구남로 21',NULL,'051-123-1018',1,2,NULL,NULL,'2026-06-26 06:18:40','2026-06-29 06:18:40',196,48,'부산광역시',NULL,NULL,'admin','11:00~22:00',0),(23,1,'라멘 타로 서면',0.0,'일본 정통 돈코츠 라멘 전문점. 12시간 우려낸 진한 돈코츠 국물이 자랑.','부산광역시 부산진구 서면로 46',NULL,'051-123-1019',2,2,NULL,NULL,'2026-06-27 06:18:40','2026-06-29 06:18:40',487,5,'부산광역시',NULL,NULL,'admin','11:00~23:00',0),(24,1,'브루클린 로스터리 광복',0.0,'광복동 감성 스페셜티 커피 로스터리. 직접 로스팅한 원두와 시그니처 라떼 아트.','부산광역시 중구 광복로 68',NULL,'051-123-1020',6,2,NULL,NULL,'2026-06-28 06:18:40','2026-06-29 06:18:40',107,78,'부산광역시',NULL,NULL,'admin','09:00~21:00',0),(25,1,'카이저빌라',0.0,'카이저빌란데용','부산 수영구 수영로666번길 21-3','404호','010-2561-4283',1,2,12,NULL,'2026-07-01 08:21:07','2026-07-01 08:21:07',2,0,'부산','35.1650558',129.11604440,'admin1','18:00~23:30',1);
/*!40000 ALTER TABLE `busan_restaurant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `busan_place`
--

DROP TABLE IF EXISTS `busan_place`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `busan_place` (
  `idx` int unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `star_point` decimal(3,1) NOT NULL DEFAULT '0.0',
  `info` text COLLATE utf8mb4_unicode_ci,
  `address1` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address2` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumb_idx` int unsigned DEFAULT NULL,
  `hashtag_idx` text COLLATE utf8mb4_unicode_ci,
  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edit_date` datetime DEFAULT NULL,
  `view_cnt` int unsigned NOT NULL DEFAULT '0',
  `like_cnt` int unsigned NOT NULL DEFAULT '0',
  `sido` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `reg_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_time` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admission_fee` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parking` tinyint(1) NOT NULL DEFAULT '0',
  `category_num` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`idx`),
  KEY `state` (`state`),
  KEY `category_num` (`category_num`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `busan_place`
--

LOCK TABLES `busan_place` WRITE;
/*!40000 ALTER TABLE `busan_place` DISABLE KEYS */;
INSERT INTO `busan_place` VALUES (1,1,'태종대해수온천',0.0,'태종대해수온천입니다잇~!','영도구 어쩌도','202호',7,NULL,'2026-06-29 06:37:41','2026-06-29 06:37:41',2,0,'영도',NULL,NULL,'admin1','06:00~23:00','3000',1,8);
/*!40000 ALTER TABLE `busan_place` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `busan_event`
--

DROP TABLE IF EXISTS `busan_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `busan_event` (
  `idx` int unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `info` text COLLATE utf8mb4_unicode_ci,
  `star_point` decimal(3,1) NOT NULL DEFAULT '0.0',
  `address1` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address2` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detail_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumb_idx` int unsigned DEFAULT NULL,
  `hashtag_idx` text COLLATE utf8mb4_unicode_ci,
  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edit_date` datetime DEFAULT NULL,
  `view_cnt` int unsigned NOT NULL DEFAULT '0',
  `like_cnt` int unsigned NOT NULL DEFAULT '0',
  `sido` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `reg_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_range` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `category_num` tinyint NOT NULL DEFAULT '0',
  `host` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_free` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idx`),
  KEY `state` (`state`),
  KEY `start_date_end_date` (`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `busan_event`
--

LOCK TABLES `busan_event` WRITE;
/*!40000 ALTER TABLE `busan_event` DISABLE KEYS */;
INSERT INTO `busan_event` VALUES (1,1,'광안리 야간드론쇼','드론쇼입니당~!',0.0,'부산광역시 수영구 광안돋ㅇ','광안리해수욕장','https://worldkang.duckdns.org',10,NULL,'2026-06-29 07:00:47','2026-06-29 07:00:47',1,0,'브산',NULL,NULL,'admin1',1,'2026-01-01','2026-12-31',1,'부산시청 및 수영구청',1);
/*!40000 ALTER TABLE `busan_event` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-06 13:41:50
