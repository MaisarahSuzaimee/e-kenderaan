-- MySQL dump 10.13  Distrib 8.4.2, for Win64 (x86_64)
--
-- Host: localhost    Database: db_ekenderaan
-- ------------------------------------------------------
-- Server version	8.4.2

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
-- Table structure for table `kenderaan_jabatan`
--

DROP TABLE IF EXISTS `kenderaan_jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kenderaan_jabatan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_plat` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ptj` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pengeluar` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `model` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `keadaan_semasa` enum('Baik','Sederhana','Rosak') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Baik',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_plat` (`no_plat`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kenderaan_jabatan`
--

LOCK TABLES `kenderaan_jabatan` WRITE;
/*!40000 ALTER TABLE `kenderaan_jabatan` DISABLE KEYS */;
INSERT INTO `kenderaan_jabatan` VALUES (3,'kcw1010','BAHAGIAN KESIHATAN AWAM','sedan','nissan','ev','Baik','2025-05-07 10:31:48','2026-02-05 12:28:57');
/*!40000 ALTER TABLE `kenderaan_jabatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kenderaan_rasmi`
--

DROP TABLE IF EXISTS `kenderaan_rasmi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kenderaan_rasmi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_plat` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `model` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ptj` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_pegawai` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jawatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gred` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_plat` (`no_plat`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kenderaan_rasmi`
--

LOCK TABLES `kenderaan_rasmi` WRITE;
/*!40000 ALTER TABLE `kenderaan_rasmi` DISABLE KEYS */;
INSERT INTO `kenderaan_rasmi` VALUES (1,'KCW 5312','mybi','HOSPITAL YAN','NAJMI','PM11','DG42','2025-05-05 21:42:58','2026-02-25 11:52:58');
/*!40000 ALTER TABLE `kenderaan_rasmi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `tempahan_kenderaan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (12,71,'Tempahan baru dari HANTU KAK LIMAH untuk perjalanan ke LANGKAWI pada 2025-05-22',1,'2025-05-19 23:07:19'),(13,71,'Status tempahan telah dikemaskini kepada TIDAK LULUS',1,'2025-05-20 01:39:40'),(14,71,'Status tempahan telah dikemaskini kepada TIDAK LULUS',1,'2025-05-20 01:39:47'),(20,80,'Tempahan baru dari NURMAISARAH BINTI SUZAIMEE untuk perjalanan ke Baling pada 2026-02-23',1,'2026-02-15 12:09:08'),(21,81,'Tempahan baru dari NURMAISARAH BINTI SUZAIMEE untuk perjalanan ke Baling pada 2026-02-23',1,'2026-02-15 12:18:20'),(22,82,'Tempahan baru dari NURMAISARAH BINTI SUZAIMEE untuk perjalanan ke Baling pada 2026-02-24',1,'2026-02-15 12:28:13'),(24,84,'Tempahan baru dari NURMAISARAH BINTI SUZAIMEE untuk perjalanan ke BAling pada 2026-02-23',1,'2026-02-15 12:37:35'),(26,86,'Tempahan baru dari administrator untuk perjalanan ke ALOR SETAR pada 2026-03-05',0,'2026-03-01 12:32:53'),(27,87,'Tempahan baru dari administrator untuk perjalanan ke Perlis pada 2026-03-03',1,'2026-03-01 12:41:18'),(28,88,'Tempahan baru dari NURMAISARAH BINTI SUZAIMEE untuk perjalanan ke Kuala Lumpur pada 2026-03-03',1,'2026-03-02 09:31:24'),(37,97,'Tempahan baru dari administrator untuk perjalanan ke test2 pada 2026-03-12',0,'2026-03-11 00:15:53'),(38,98,'Tempahan baru dari administrator untuk perjalanan ke test5 pada 2026-03-13',0,'2026-03-11 00:16:52');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pekeliling_kenderaan`
--

DROP TABLE IF EXISTS `pekeliling_kenderaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pekeliling_kenderaan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tajuk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tarikh_pekeliling` date NOT NULL,
  `fail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pekeliling_kenderaan`
--

LOCK TABLES `pekeliling_kenderaan` WRITE;
/*!40000 ALTER TABLE `pekeliling_kenderaan` DISABLE KEYS */;
INSERT INTO `pekeliling_kenderaan` VALUES (2,'PENGURUSAN KENDERAAN JABATAN BIL 5 TAHUN 2030','2025-05-28','1746525142_Khutbah ISTIQAMAH DALAM KETAATAN Tahun 2025 Rumi.pdf','maklumat tentang pekililing terhadap jkn kedah','2025-05-06 17:52:22','2026-02-08 08:30:31'),(5,'PENGURUSAN TEMPAHAN','2025-05-23','1770188322_JABATAN KESIHATAN NEGERI KEDAH 05-11-2025 (MOHD IZZAT).pdf','TAHUN 2090','2025-05-17 14:33:52','2026-02-04 14:58:42');
/*!40000 ALTER TABLE `pekeliling_kenderaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penggunajkn`
--

DROP TABLE IF EXISTS `penggunajkn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penggunajkn` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nokp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `idptj` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bahagian` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jawatan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gred` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nohp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'AKTIF',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penggunajkn`
--

LOCK TABLES `penggunajkn` WRITE;
/*!40000 ALTER TABLE `penggunajkn` DISABLE KEYS */;
INSERT INTO `penggunajkn` VALUES (1,'010503020855','muhd najmi','1','809','farmasi','JURURAWAT','B22','019292929221','123','staff','','AKTIF'),(2,'010503020844','fattah hatti','1','809','hospital','JURURAWAT','C27','0191919191','123','staff','','AKTIF'),(4,'011118090757','FIKRI','18','816','54','PEGAWAI PERUBATAN','B22','01958458454','123','staff','fikri@gmail.com','AKTIF'),(5,'admin','administrator','admin','admin','admin','admin','admin','01123123123','123456','admin','admin@gmail.com','AKTIF'),(6,'030806080575','ali aliff','1','809','Unit Pentadbiran','JURURAWAT','C41','01121525865','123','user','wanrxz7871@gmail.com','AKTIF'),(7,'superadmin','admin','18',NULL,NULL,NULL,NULL,'01121525865','123123','superadmin',NULL,'AKTIF'),(8,'030806080757','MUHAMMAD HARITH HAKIM BIN ZAMANI','1','809','39','PEGAWAI KHIDMAT PELANGGAN','B22','01121525865','123','staff','harithhakimgt@gmail.com','AKTIF'),(9,'010101010101','MUHAMMAD DANIAL','1','809','Unit Pentadbiran','PEGAWAI REKOD PERUBATAN','F41','01121525865','123','staff','rythgaming03@gmail.com','AKTIF'),(10,'031203090184','JANNATUL LAILA','18','816','Pengurusan Farmasi','JURURAWAT PERGIGIAN','C48','0199683088','123123','staff','k01ssk19f033@gmail.com','Tidak Aktif'),(11,'03120309044','puan sharimah','1','809','39','PEGAWAI TEKNOLOGI MAKLUMAT','F38','0192668244','123','staff','sharimah_yahya@moh.gov.my','AKTIF'),(12,'132131232312','EN RAM','1','809','39','JURUTERA (ELEKTRIK)','FT17','012391239129','123','staff','sornram_21@yahoo.com','AKTIF'),(13,'010503020822','MUHAMMAD NAJMI','1',NULL,NULL,NULL,NULL,'0196383812','123','admin',NULL,'AKTIF'),(14,'010503020855','ADMIN','1','','39','ADMIN','F41','0196383815','admin123','staff','najmipisol01@gmail.com','AKTIF'),(15,'010503020855','ADMIN','1','','39','ADMIN','F41','0196383815','admin123','staff','najmipisol01@gmail.com','AKTIF'),(16,'010503020811','ADMIN','1','','39','ADMIN','F41','0196383815','admin123','staff','najmipisol01@gmail.com','AKTIF'),(18,'010503020877','MUHAMMAD NAJMI','1','','39','PRAKTIKAL','F41','0124838924','123456','staff','k01ssk19f033@gmail.com','AKTIF'),(19,'810708025970','YUSFARIZAN BT. MOHAMMAD YUSOF','1','','39','PAMBANTU TADBIR','N1','01121109575','123456','staff','yusfarizan@moh.gov.my','AKTIF'),(20,'860527025790','ATIQAH','1','','39','PENGAWAI TEKNOLOGI MAKLUMAT','F10','0194558347','123456','staff','noor.atiqah@moh.gov.my','AKTIF'),(26,'010503020833','MUHAMMAD NAJEMII','1','','39','JURUTEKNIK KOMPUTER','F41','0196383815','123123','staff','mnajemie12@gmail.com','AKTIF'),(32,'000000000000','HANTU KAK LIMAH','1','809','Unit Khidmat/Pengurusan','JURUAUDIO VISUAL','C90','0196383815','12341234','user','mnajemie12@gmail.com','AKTIF'),(33,'020822030278','NURMAISARAH BINTI SUZAIMEE','1','809','39','PEGAWAI TEKNOLOGI MAKLUMAT','10','01161404157','123123','staff','nmaisarah2208@gmail.com','AKTIF'),(34,'720719025149','MAISARAHS','42','811','49','MA','69','0168945321','123456','staff','msrhszm@gmail.com','Tidak Aktif'),(35,'781216025269','MAISARAH2','43','815','63','TEST','10','012369584','123123','staff','abcdefg@gmail.com','AKTIF'),(36,'951220025431','MAISARAH','8','130','0','TEST','14','0121111123','123123','staff','nmaisarah2208@gmail.com','AKTIF'),(38,'1112131415170','MAISARAH3','13',NULL,NULL,'TEST','69','0135599874','123456','staff','nmaisarah2208@gmail.com','AKTIF');
/*!40000 ALTER TABLE `penggunajkn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penyelenggara_kenderaan`
--

DROP TABLE IF EXISTS `penyelenggara_kenderaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penyelenggara_kenderaan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_plat` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tarikh_penyelenggaraan` date NOT NULL,
  `butir_penyelenggaraan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kos_penyelenggaraan` decimal(10,2) NOT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `ptj_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ptj_id` (`ptj_id`),
  CONSTRAINT `penyelenggara_kenderaan_ibfk_1` FOREIGN KEY (`ptj_id`) REFERENCES `tptj` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penyelenggara_kenderaan`
--

LOCK TABLES `penyelenggara_kenderaan` WRITE;
/*!40000 ALTER TABLE `penyelenggara_kenderaan` DISABLE KEYS */;
INSERT INTO `penyelenggara_kenderaan` VALUES (3,'KCW 5312','2025-05-07','nak tukar engine jadi 4.0 laju kit nak bawa pengarah pastu nak letak sunroof nak style kit2',200000.00,40000.06,15,'2025-05-06 19:28:31','2026-03-03 11:55:47'),(13,'RF4377','2026-03-05','Baru3',1000.00,10000.00,5,'2026-03-03 11:57:32','2026-03-03 11:59:18');
/*!40000 ALTER TABLE `penyelenggara_kenderaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `level` enum('info','warning','error') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'info',
  `user_id` int DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_system_logs_level` (`level`),
  KEY `idx_system_logs_user_id` (`user_id`),
  KEY `idx_system_logs_created_at` (`created_at`),
  CONSTRAINT `fk_system_logs_user_id` FOREIGN KEY (`user_id`) REFERENCES `penggunajkn` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_logs`
--

LOCK TABLES `system_logs` WRITE;
/*!40000 ALTER TABLE `system_logs` DISABLE KEYS */;
INSERT INTO `system_logs` VALUES (1,'info',7,'System logs exported to CSV','::1','2026-03-11 06:59:34'),(2,'info',7,'Deleted backup file: backup_2026-03-11_07-59-03.sql','::1','2026-03-11 06:59:55'),(3,'info',7,'System logs exported to CSV','::1','2026-03-11 07:00:13');
/*!40000 ALTER TABLE `system_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting` varchar(100) DEFAULT NULL,
  `value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting` (`setting`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=armscii8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'last_backup','2026-03-11 14:59:03','2026-03-11 06:59:03');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbahagian`
--

DROP TABLE IF EXISTS `tbahagian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbahagian` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bahagian` varchar(100) DEFAULT NULL,
  `idptj` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=829 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbahagian`
--

LOCK TABLES `tbahagian` WRITE;
/*!40000 ALTER TABLE `tbahagian` DISABLE KEYS */;
INSERT INTO `tbahagian` VALUES (1,'Unit Kejuruteraan',4),(2,'Asrama Jururawat',4),(3,'Carakerja',4),(4,'Farmasi Agihan',4),(5,'Hemodialisis',4),(6,'JPL',4),(7,'Klinik Kesihatan Kupang',4),(8,'Klinik Lawatan Pakar',4),(9,'Orthopedik',4),(10,'OT/CSSD',4),(11,'Patologi',4),(12,'Pengurusan',4),(13,'Radiologi',4),(14,'Stor Perubatan',4),(15,'Unit A & E',4),(16,'Unit Carakerja',4),(17,'Unit Farmasi Agihan',4),(18,'Unit Fisioterapi',4),(19,'Unit Hasil',4),(20,'Unit Hemodialisis',4),(21,'Unit ICN',4),(22,'Unit Kewangan',4),(23,'Unit Kualiti',4),(24,'Unit Operator',4),(25,'Unit Patologi',4),(26,'Unit Pemandu',4),(27,'Unit Pengurusan',4),(28,'Unit Pusat Sumber',4),(29,'Unit Radiologi',4),(30,'Unit Rekod Perubatan',4),(31,'Unit Sajian',4),(32,'Unit Stor Integrasi',4),(33,'Wad 1 ',4),(34,'Wad 2 ',4),(35,'Wad 3',4),(36,'Wad 4 & 5',4),(37,'Wad 6 & 7',4),(38,'-',5),(39,'CSSD',5),(40,'HDU',5),(41,'JPL',5),(42,'Klinik Pakar',5),(43,'Pusat Sumber',5),(44,'Stor Farmasi',5),(45,'Unit Asrama',5),(46,'Unit Farmasi',5),(47,'Unit Fisioterapi',5),(48,'Unit Kecemasan',5),(49,'Unit Kewangan',5),(50,'Unit Kualiti',5),(51,'Unit Patologi',5),(52,'Unit Pengimejan & Diagnostik',5),(53,'Unit Pentadbiran',5),(54,'Unit Penyediaan Makanan',5),(55,'Unit Rekod',5),(56,'Unit Rekod Perubatan',5),(57,'Unit Sajian',5),(58,'Unit Stor Farmasi',5),(59,'Wad Bersalin',5),(60,'Wad KanaKlinik Kesihatan anak',5),(61,'Wad Lelaki',5),(62,'Wad Perempuan',5),(63,' Pentadbiran',6),(64,'A&E/Wad',6),(65,'Bilik Kualiti',6),(66,'Bilik Operator',6),(67,'CSSD',6),(68,'Dapur',6),(69,'Farmasi',6),(70,'Hasil',6),(71,'Hemodialisis',6),(72,'JPL',6),(73,'Kaunter Pkp',6),(74,'Kaw. Jangkitan',6),(75,'Kecemasan',6),(76,'Kewangan',6),(77,'Kinik Dada',6),(78,'Klinik Pakar',6),(79,'Klnik Dada',6),(80,'Makmal',6),(81,'Pej. Rekod',6),(82,'Pengurusan',6),(83,'Penyeliaan',6),(84,'Pusat Sumber',6),(85,'Setor Intergrasi',6),(86,'Sumber Manusia',6),(87,'Wad',6),(88,'Wad Bersalin',6),(89,'Wad Kanak2',6),(90,'Wad Lelaki',6),(91,'Wad Perempuan',6),(92,'Wad/A&E/JPL',6),(93,'X-Ray',6),(94,'Bius',7),(95,'Dietetik & Sajian',7),(96,'Farmasi',7),(97,'Kebajikan Sosial',7),(98,'Kecemasan',7),(99,'Klinik Kesihatan Padang Serai',7),(100,'O&G',7),(101,'Oftalmologi',7),(102,'Orthopedik',7),(103,'Patologi',7),(104,'Pediatrik',7),(105,'Pembedahan',7),(106,'Pendidikan Kesihatan',7),(107,'Pengurusan',7),(108,'Perubatan',7),(109,'Psikiatrik',7),(110,'Rehabilitasi',7),(111,'Unit Bekalan Steril',7),(112,'X-Ray',7),(113,' Pentadbiran',8),(114,'A&E',8),(115,'Anaestesiologi',8),(116,'Bersandar Hosp Sultanah B',8),(117,'Bilik Bersalin',8),(118,'CSSU',8),(119,'Dewan Bedah',8),(120,'Hemodialisis',8),(121,'ICU',8),(122,'Jabatan Kecemasan',8),(123,'Kejuruteraan',8),(124,'Klinik Pakar',8),(125,'Medical',8),(126,'Obstetrik & Ginekologi',8),(127,'Orthopidik',8),(128,'PAED',8),(129,'Pengurusan',8),(130,'SCN',8),(131,'Surgical',8),(132,'Unit Dapur',8),(133,'Unit Farmasi',8),(134,'Unit Fisioterapi',8),(135,'Unit Hasil',8),(136,'Unit Kawalan Infeksi',8),(137,'Unit Kecemasan',8),(138,'Unit Kualiti',8),(139,'Unit Optometri',8),(140,'Unit Ortopedik',8),(141,'Unit Patologi',8),(142,'Unit Perpustakaan',8),(143,'Unit Rekod',8),(144,'Unit Stor Perubatan',8),(145,'Unit Teknologi Maklumat',8),(146,'Unit X-Ray',8),(147,'Wad Bersalin',8),(148,'Wad Kanak2',8),(149,'Wad Kelas 1 & 2',8),(150,'Wad Lelaki',8),(151,'Wad Perempuan',8),(152,'Wad SCNN',8),(153,' Pentadbiran',9),(154,' Unit Pesakit Luar',9),(155,'CSSD',9),(156,'Farmasi',9),(157,'Haemodialisis',9),(158,'Kaw. Infeksi',9),(159,'Kecemasan',9),(160,'Klinik Dada',9),(161,'Klinik Pakar',9),(162,'Makmal',9),(163,'O&G',9),(164,'Pengurusan',9),(165,'Penyeliaan',9),(166,'Pesakit Dalam',9),(167,'Pesakit Luar',9),(168,'Rehabilitasi',9),(169,'Rekod Perubatan',9),(170,'Sajian',9),(171,'Stor Perubatan',9),(172,'Unit Kecemasan',9),(173,'Unit Kecemasan & Trauma',9),(174,'Unit Kualiti',9),(175,'Unit Pesakit Luar',9),(176,'X-Ray',9),(177,'Dewan Bedah',10),(178,'Dewan Bersalin',10),(179,'HO',10),(180,'Jabatan Anestesiologi',10),(181,'Jabatan Dietetik & Sajian',10),(182,'Jabatan ENT',10),(183,'Jabatan Farmasi',10),(184,'Jabatan Forensik',10),(185,'Jabatan Kecemasan & Trauma',10),(186,'Jabatan O&G',10),(187,'Jabatan Oftalmologi',10),(188,'Jabatan Orthopedik',10),(189,'Jabatan Patologi',10),(190,'Jabatan Pediatrik',10),(191,'Jabatan Pembedahan',10),(192,'Jabatan Pengimejan & Diagnostik',10),(193,'Jabatan Perubatan Am',10),(194,'Jabatan Psikiatri',10),(195,'Jabatan Rekod Perubatan',10),(196,'Kaunter Pertanyaan',10),(197,'Klinik Pakar',10),(198,'Linen',10),(199,'Pejabat Pakar',10),(200,'Pejabat Penyelia',10);
/*!40000 ALTER TABLE `tbahagian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tempahan_kenderaan`
--

DROP TABLE IF EXISTS `tempahan_kenderaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tempahan_kenderaan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `tarikh_mohon` datetime NOT NULL,
  `bertolak` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `destinasi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `negeri` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_perjalanan` enum('2 hala','1 hala') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tarikh_pergi` date NOT NULL,
  `masa_pergi` time NOT NULL,
  `tarikh_balik` date DEFAULT NULL,
  `masa_balik` time DEFAULT NULL,
  `tujuan_perjalanan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lain_tujuan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `bil_penumpang` int NOT NULL,
  `senarai_penumpang` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `jenis_kenderaan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pemohon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kelulusan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'BARU',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_pemandu` int DEFAULT NULL,
  `model` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_email` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pemandu` (`id_pemandu`),
  KEY `fk_tempahan_user` (`user_id`),
  CONSTRAINT `fk_tempahan_user` FOREIGN KEY (`user_id`) REFERENCES `penggunajkn` (`id`),
  CONSTRAINT `tempahan_kenderaan_ibfk_1` FOREIGN KEY (`id_pemandu`) REFERENCES `tpemandu` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tempahan_kenderaan`
--

LOCK TABLES `tempahan_kenderaan` WRITE;
/*!40000 ALTER TABLE `tempahan_kenderaan` DISABLE KEYS */;
INSERT INTO `tempahan_kenderaan` VALUES (7,1,'2025-02-09 20:47:49',NULL,'kampong','Kedah','2 hala','2025-02-10','20:48:00','2025-02-11','20:48:00','Lain-lain','cuti',1,'najmi','Sedan','2025-02-09 20:48:16','muhd najmi','BARU','2025-04-30 17:51:20',NULL,NULL,NULL,NULL),(8,8,'2025-02-20 15:16:17',NULL,'asdf','Kedah','2 hala','2025-02-13','03:16:00','2025-02-13','15:16:00','Lawatan Kerja','-',0,'asd','MPV 6 Seater','2025-02-20 15:23:49','MUHAMMAD HARITH HAKIM BIN ZAMANI','BARU','2025-04-30 17:51:20',NULL,NULL,NULL,NULL),(9,8,'2025-02-20 15:26:23',NULL,'asdf','Kedah','2 hala','2025-02-13','03:16:00','2025-02-13','15:16:00','Lawatan Kerja','-',0,'asd','MPV 6 Seater','2025-02-20 15:28:49','MUHAMMAD HARITH HAKIM BIN ZAMANI','BARU','2025-04-30 17:51:20',NULL,NULL,NULL,NULL),(11,9,'2025-02-25 12:57:02',NULL,'LANGKAWI','Kedah','2 hala','2025-02-26','12:00:00','2025-02-26','00:00:00','Kursus/Seminar','-',0,'AIMAN DAN ABU','SUV','2025-02-25 12:57:34','MUHAMMAD DANIAL','BARU','2025-04-30 17:51:20',NULL,NULL,NULL,NULL),(14,9,'2025-02-25 15:45:00',NULL,'asdasd','Johor','2 hala','2025-02-19','15:45:00','2025-02-26','15:49:00','Lawatan Kerja','-',0,'asda asda','MPV 6 Seater','2025-02-25 15:46:32','MUHAMMAD DANIAL','LULUS','2025-05-16 16:28:46',19,NULL,NULL,NULL),(17,9,'2025-02-25 15:49:00',NULL,'assd','Johor','2 hala','2025-02-25','03:49:00','2025-02-26','03:49:00','Lawatan Kerja','-',0,'asdf asdf as gf','Lori','2025-02-25 15:49:40','MUHAMMAD DANIAL','TIDAK LULUS','2025-05-16 16:28:29',NULL,NULL,NULL,NULL),(18,9,'2025-03-03 01:48:00',NULL,'asdf','Johor','2 hala','2025-03-12','01:52:00','2025-03-12','01:51:00','Lawatan Kerja','-',0,'asd  asd','Sedan','2025-03-04 01:48:45','MUHAMMAD DANIAL','TIDAK LULUS','2025-05-06 17:29:00',NULL,NULL,NULL,NULL),(23,11,'2025-03-13 12:54:00',NULL,'kk jitra','Kedah','1 hala','2025-03-13','13:56:00','2025-03-13','14:56:00','Lawatan Kerja','lain-lain',0,'puansharimah','Sedan','2025-03-13 12:54:59','puan sharimah','LULUS','2025-05-06 19:52:43',19,NULL,'SENT','2025-05-06 19:52:43'),(25,10,'2025-03-16 10:40:00',NULL,'kk baling','Kedah','1 hala','2025-03-16','02:47:00','2025-03-16','05:41:00','Lawatan Kerja','lain-lain',0,'lailer','Sedan','2025-03-16 10:41:34','JANNATUL LAILA','LULUS','2025-05-04 23:17:37',13,NULL,NULL,NULL),(27,12,'2025-03-26 15:22:00',NULL,'KK PANDANG TERAP','Kedah','1 hala','2025-03-26','16:22:00','2025-03-26','19:22:00','Lain-lain','lain-lain',0,'EN RAM','MPV 6 Seater','2025-03-26 15:23:12','EN RAM','LULUS','2025-04-30 17:51:20',14,NULL,NULL,NULL),(30,10,'2025-04-15 08:56:00',NULL,'KK JITRA','Kedah','1 hala','2025-04-15','10:03:00','2025-04-15','00:00:00','Mesyuarat','lain-lain',0,'JANNATUL','SUV','2025-04-15 08:57:46','JANNATUL LAILA','LULUS','2025-05-04 23:17:21',14,NULL,NULL,NULL),(31,10,'2025-04-15 10:27:00',NULL,'KK PADANG TERAP','Kedah','1 hala','2025-04-15','11:29:00','2025-04-16','01:29:00','Mesyuarat','lain-lain',0,'jannatul','MPV 6 Seater','2025-04-15 10:30:27','JANNATUL LAILA','LULUS','2025-04-30 17:51:20',24,NULL,NULL,NULL),(40,6,'2025-04-30 18:33:00',NULL,'KKBD','Kuala Lumpur','2 hala','2025-04-30','18:33:00','2025-04-30','18:33:00','Lawatan Kerja','dsa',0,'asdasd','MPV 6 Seater','2025-04-30 18:33:30','ali','LULUS','2025-05-02 20:37:54',20,NULL,'SENT','2025-05-02 20:37:54'),(41,10,'2025-05-04 00:26:00',NULL,'KK JITRA','Kedah','1 hala','2025-05-06','02:28:00','2025-05-06','03:28:00','Mesyuarat','tu je',0,'najmi','Lori','2025-05-05 00:28:53','JANNATUL LAILA','LULUS','2025-05-11 08:22:41',9,NULL,'SENT','2025-05-11 08:22:41'),(42,10,'2025-05-06 19:48:00',NULL,'KK BALING','Kedah','1 hala','2025-05-07','22:48:00','2025-05-07','14:48:00','Lawatan Kerja','tu je',0,'najmi','Sedan','2025-05-06 19:48:50','JANNATUL LAILA','LULUS','2025-05-14 11:34:36',14,NULL,'SENT','2025-05-14 11:34:36'),(44,19,'2025-05-07 08:39:00',NULL,'HOSPITAL SIK','Kedah','1 hala','2025-05-18','10:02:00','2025-05-18','05:40:00','Lawatan Kerja','lain-lain',0,'en hilmi puan wana','Sedan','2025-05-07 08:41:21','YUSFARIZAN BT. MOHAMMAD YUSOF','LULUS','2025-05-20 01:56:01',22,NULL,'SENT','2025-05-20 01:56:01'),(45,11,'2025-05-07 08:58:00',NULL,'HOSPITAL JITRA','Kedah','1 hala','2025-05-22','10:00:00','2025-05-22','00:59:00','Lawatan Kerja','lain-lain',0,'PUAN SHARIMAH','Sedan','2025-05-07 09:00:00','puan sharimah','LULUS','2025-05-20 02:10:17',51,NULL,'SENT','2025-05-20 02:10:17'),(46,20,'2025-05-07 09:11:00',NULL,'PUTRAJAYA  - JKN','Putrajaya','1 hala','2025-05-07','10:20:00','2025-05-07','04:00:00','Kursus/Seminar','',0,'PUAN ATIQAH','Sedan','2025-05-07 09:39:15','ATIQAH','LULUS','2025-05-20 02:09:14',19,NULL,'SENT','2025-05-20 02:09:14'),(47,26,'2025-05-16 17:16:00',NULL,'KK JITRA','Kedah','1 hala','2025-05-17','22:17:00','2025-05-17','13:18:00','Mesyuarat','tiada',0,'NAJMI','Sedan','2025-05-16 17:18:26','MUHAMMAD NAJEMII','LULUS','2025-05-16 22:42:30',19,NULL,'SENT','2025-05-16 17:26:29'),(71,32,'2025-05-19 23:06:00','JKN','LANGKAWI','Kedah','1 hala','2025-05-22','10:30:00','0000-00-00','00:00:00','Kursus / Seminar / Bengkel','tiada',1,'EN USOP','Lori','2025-05-19 23:07:19','HANTU KAK LIMAH','LULUS','2025-05-20 01:55:26',19,NULL,'SENT','2025-05-20 01:55:26'),(80,33,'2026-02-15 12:07:14','JKN','Baling','Kedah','1 hala','2026-02-23','12:00:00',NULL,NULL,'Lawatan Kerja','',1,'Maisarah','Sedan','2026-02-15 12:09:08','NURMAISARAH BINTI SUZAIMEE','BARU','2026-02-15 12:09:08',NULL,NULL,NULL,NULL),(81,33,'2026-02-15 12:17:33','JKN','Baling','Kedah','1 hala','2026-02-23','12:00:00',NULL,NULL,'Lawatan Kerja','',1,'Maisarah','Sedan','2026-02-15 12:18:20','NURMAISARAH BINTI SUZAIMEE','BARU','2026-02-15 12:18:20',NULL,NULL,NULL,NULL),(82,33,'2026-02-15 12:27:46','JKN','Baling','Kedah','1 hala','2026-02-24','13:27:00',NULL,NULL,'Lawatan Kerja','',1,'Mai','Sedan','2026-02-15 12:28:13','NURMAISARAH BINTI SUZAIMEE','BARU','2026-02-15 12:28:13',NULL,NULL,NULL,NULL),(84,33,'2026-02-15 12:36:55','JKN','BAling','Kedah','1 hala','2026-02-23','12:37:00',NULL,NULL,'Lawatan Kerja','',1,'Maisarah','Sedan','2026-02-15 12:37:35','NURMAISARAH BINTI SUZAIMEE','BATAL','2026-03-01 09:13:47',NULL,NULL,NULL,NULL),(86,5,'2026-03-01 12:31:45','JKN','ALOR SETAR','Kedah','2 hala','2026-03-05','08:30:00','2026-03-05','12:30:00','Bank','',1,'Maisarah','Sedan','2026-03-01 12:32:53','administrator','BARU','2026-03-01 12:32:53',NULL,NULL,NULL,NULL),(87,5,'2026-03-01 12:40:31','JKN','Perlis','Perlis','2 hala','2026-03-03','14:40:00','2026-03-02','14:40:00','Bank','',1,'.','Sedan','2026-03-01 12:41:18','administrator','BARU','2026-03-01 12:41:18',NULL,NULL,NULL,NULL),(88,33,'2026-03-02 09:30:45','JKN','Kuala Lumpur','Kuala Lumpur','1 hala','2026-03-03','09:30:00',NULL,NULL,'Kursus / Seminar / Bengkel','',1,'Maisarah','Sedan','2026-03-02 09:31:24','NURMAISARAH BINTI SUZAIMEE','TIDAK LULUS','2026-03-02 11:40:32',NULL,NULL,NULL,NULL),(97,5,'2026-03-11 08:15:16','test','test2','Kedah','1 hala','2026-03-12','08:15:00',NULL,NULL,'Kursus / Seminar / Bengkel','',2,'p1, p2','Hino','2026-03-11 00:15:53','administrator','BARU','2026-03-11 00:15:53',NULL,NULL,NULL,NULL),(98,5,'2026-03-11 08:15:55','test4','test5','Kedah','1 hala','2026-03-13','08:15:00',NULL,NULL,'Lain-lain','tujuan',2,'eeee','Sedan','2026-03-11 00:16:52','administrator','BARU','2026-03-11 00:16:52',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `tempahan_kenderaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tgred`
--

DROP TABLE IF EXISTS `tgred`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tgred` (
  `id` int NOT NULL,
  `gred` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tgred`
--

LOCK TABLES `tgred` WRITE;
/*!40000 ALTER TABLE `tgred` DISABLE KEYS */;
INSERT INTO `tgred` VALUES (1,'B22'),(2,'C27'),(3,'C32'),(4,'C41'),(5,'C44'),(6,'C48'),(7,'C52'),(8,'F29'),(9,'F32'),(10,'F41'),(11,'F44'),(12,'FT17'),(13,'J17'),(14,'J29'),(15,'J41'),(16,'J44'),(17,'J48'),(18,'KP17'),(19,'M41'),(20,'M44'),(21,'M48'),(22,'M52'),(23,'N1'),(24,'N17'),(25,'N22'),(26,'N26'),(27,'N27'),(28,'N28'),(29,'N32'),(30,'N36'),(31,'N4'),(32,'N41'),(33,'R1'),(34,'R3'),(35,'R4'),(36,'R6'),(37,'S41'),(38,'S44'),(39,'S48'),(40,'U17'),(41,'U29'),(42,'U32'),(43,'U36'),(44,'U38'),(45,'U41'),(46,'U42'),(47,'U44'),(48,'U48'),(49,'U52'),(50,'U54'),(51,'UD44'),(52,'UD48'),(53,'UD51'),(54,'UD52'),(55,'UD54'),(56,'W17'),(57,'W22'),(58,'W27'),(59,'W36'),(60,'W44'),(62,'M48'),(63,'M54'),(64,'H11'),(65,'N11'),(66,'R11'),(67,'U19'),(68,'W29'),(69,'H14'),(70,'JUSA C'),(71,'JUSA B'),(72,'JUSA A'),(73,'F38'),(74,'W19');
/*!40000 ALTER TABLE `tgred` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tjawatan`
--

DROP TABLE IF EXISTS `tjawatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tjawatan` (
  `id` int NOT NULL,
  `jawatan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tjawatan`
--

LOCK TABLES `tjawatan` WRITE;
/*!40000 ALTER TABLE `tjawatan` DISABLE KEYS */;
INSERT INTO `tjawatan` VALUES (2,'JURUAUDIO VISUAL'),(3,'JURURAWAT'),(4,'JURURAWAT PERGIGIAN'),(5,'JURUTEKNIK'),(6,'JURUTEKNIK KOMPUTER'),(7,'JURUTEKNOLOGI MAKMAL PERUBATAN'),(8,'JURUTERA (AWAM)'),(9,'JURUTERA (ELEKTRIK)'),(10,'JURUTERA (KESIHATAN UMUM)'),(11,'JURUTERA (MEKANIKAL)'),(12,'PEGAWAI FARMASI'),(14,'PEGAWAI KAUNSELOR'),(15,'PEGAWAI KESIHATAN PERSEKITARAN'),(16,'PEGAWAI KHIDMAT PELANGGAN'),(18,'PEGAWAI PERGIGIAN'),(19,'PEGAWAI PERGIGIAN'),(20,'PEGAWAI PERUBATAN'),(22,'PEGAWAI SAINS'),(23,'PEGAWAI SAINS (KIMIA HAYAT)'),(24,'PEGAWAI SAINS (PEGAWAI ZAT MAKANAN)'),(26,'PEGAWAI TADBIR DAN DIPLOMATIK'),(27,'PEGAWAI TEKNOLOGI MAKANAN'),(28,'PEGAWAI TEKNOLOGI MAKLUMAT'),(29,'PEKERJA AWAM'),(30,'PEKERJA RENDAH AWAM'),(31,'PEMANDU KENDERAAN'),(32,'PEMBANTU AM PEJABAT'),(33,'PEMBANTU KESELAMATAN'),(34,'PEMBANTU KESIHATAN AWAM'),(35,'PEMBANTU TADBIR (KESETIAUSAHAAN)'),(36,'PEMBANTU TADBIR (KEWANGAN)'),(37,'PEMBANTU TADBIR (P/O)'),(39,'PEMBANTU TEKNIK'),(40,'PEN. PEG. TEKNOLOGI MAKANAN'),(41,'PENOLONG AKAUNTAN'),(42,'PENOLONG JURUTERA'),(43,'PENOLONG PEGAWAI KESIHATAN PERSEKITARAN'),(44,'PENOLONG PEGAWAI PERUBATAN'),(45,'PENOLONG PEGAWAI SAINS'),(46,'PENOLONG PEGAWAI TADBIR'),(47,'PEN. PEGAWAI TADBIR (REKOD PERUBATAN)'),(48,'PEN. PEGAWAI TEKNOLOGI MAKLUMAT'),(49,'PEREKA'),(50,'SETIAUSAHA PEJABAT'),(52,'TIMB. PENGARAH KESIHATAN NEGERI (PENGURUSAN)'),(53,'PENGARAH KESIHATAN NEGERI'),(54,'PENGARAH HOSPITAL'),(55,'PEGAWAI REKOD PERUBATAN'),(56,'PEMBANTU AWAM');
/*!40000 ALTER TABLE `tjawatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tjenis`
--

DROP TABLE IF EXISTS `tjenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tjenis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jeniskenderaan` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tjenis`
--

LOCK TABLES `tjenis` WRITE;
/*!40000 ALTER TABLE `tjenis` DISABLE KEYS */;
INSERT INTO `tjenis` VALUES (1,'Ambulansp'),(2,'Pacuan 4 Roda 600KG'),(3,'Pacuan 4 Roda 1000KG'),(4,'Van 10 Penumpang'),(5,'Bas Mini 25 Penumpang'),(6,'Bas 44 Penumpang'),(8,'Kereta Jenazah Islam'),(9,'Kereta Mayat'),(10,'Lori 1.5 tan'),(11,'Lori 3.5 tan'),(12,'Lori 5.5 tan'),(13,'Kereta Sedan'),(14,'Motosikal'),(15,'Basikal Roda Tiga'),(17,'MPV Pacuan 4 roda'),(18,'Bot'),(19,'Motokar Pelbagai Utiliti'),(20,'SUV'),(21,'MPV');
/*!40000 ALTER TABLE `tjenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tkenderaan`
--

DROP TABLE IF EXISTS `tkenderaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tkenderaan` (
  `no_plat` varchar(15) NOT NULL,
  `idptj` varchar(100) DEFAULT NULL,
  `idjenis` int DEFAULT NULL,
  `idpengeluar` int DEFAULT NULL,
  `modeltemp` varchar(200) DEFAULT NULL,
  `idmodel` int DEFAULT NULL,
  `tahunpengeluaran` int DEFAULT NULL,
  `statuspemilikan` varchar(20) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `keadaansemasa` varchar(15) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `idpenempatan` int DEFAULT NULL,
  `tarikhlupus` date DEFAULT NULL,
  `tarikhber` date DEFAULT NULL,
  `geran` varchar(100) DEFAULT NULL,
  `idpenempatan_ptj` int DEFAULT NULL,
  `idpenempatan_bahagian` int DEFAULT NULL,
  PRIMARY KEY (`no_plat`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tkenderaan`
--

LOCK TABLES `tkenderaan` WRITE;
/*!40000 ALTER TABLE `tkenderaan` DISABLE KEYS */;
INSERT INTO `tkenderaan` VALUES ('afd34566','5',1,1,NULL,12,2010,'Belian',70000.00,'Baik','',193,NULL,NULL,'imagesCA9HMRQ0.jpg',NULL,NULL),('BFR6408','36',11,19,'TOYOTA HLUX',8,2003,'Belian',0.00,'Baik','',171,NULL,NULL,'KOMPUTER_PNG.png',NULL,NULL),('BHM9058','14',6,14,'NISSAN UD',0,2004,'Belian',0.00,'Baik','NTC, TURNKEY PROJECT',30,NULL,NULL,NULL,NULL,NULL),('BHM9061','14',6,14,'NISSAN UD',0,2004,'Belian',0.00,'Baik','NTC, TURNKEY PROJECT',30,NULL,NULL,NULL,NULL,NULL),('CUTI REHAT','1',1,24,NULL,18,2017,'Hadiah',2.60,'Dibaiki','REMARK CUTI REHAT',NULL,NULL,NULL,NULL,1,809),('E.L','1',1,1,NULL,12,2200,'Lain-Lain',0.05,'Rosak','REMARK TIDAK HADIR/CUTI LAST MINUTE',NULL,NULL,NULL,NULL,1,809),('FARMASI','1',17,19,NULL,22,2018,'Lain-Lain',NULL,'Baik','',NULL,NULL,NULL,NULL,1,809),('JURUTERA','1',3,19,NULL,30,2019,'Belian',0.00,'Baik','',NULL,NULL,NULL,NULL,1,809),('KAV4975','36',3,9,'LAND ROVER DEFENDER 110',0,1999,'Belian',88961.00,'Baik','',170,NULL,NULL,NULL,NULL,NULL),('KAV4976','38',3,9,'LAND ROVER DEFENDER',0,2000,'Belian',82000.00,'Baik','',180,NULL,NULL,NULL,NULL,NULL),('KAV4978','38',3,9,'LAND ROVER DEFENDER',0,1997,'Belian',82000.00,'Baik','',180,NULL,NULL,NULL,NULL,NULL),('KAV4979','29',3,9,'LAND ROVER DEFENDER',0,1997,'Belian',0.00,'Baik','',90,NULL,NULL,NULL,NULL,NULL),('KAV6421','24',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',80976.00,'Baik','',60,NULL,NULL,NULL,NULL,NULL),('KAV6457','14',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',0.00,'Baik','',0,NULL,NULL,NULL,NULL,NULL),('KAV6458','32',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',80976.00,'Baik','',117,NULL,NULL,NULL,NULL,NULL),('KAV6459','29',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',0.00,'Baik','',93,NULL,NULL,NULL,NULL,NULL),('KAV6460','3',3,11,'MITSUBISHI PAJERO V3HV NDRM',0,1999,'Belian',80976.00,'Baik','DIPINJAMKAN KE VEKTOR KUALA MUDA',128,NULL,NULL,NULL,NULL,NULL),('KAV6461','35',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',80976.00,'Baik','',160,NULL,NULL,NULL,NULL,NULL),('KAV6462','34',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',80976.00,'Baik','',151,NULL,NULL,NULL,NULL,NULL),('KAV6463','32',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',80976.00,'Baik','',127,NULL,NULL,NULL,NULL,NULL),('KAV6465','38',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',80000.00,'Baik','',180,NULL,NULL,NULL,NULL,NULL),('KAV6467','14',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',114000.00,'Baik','',32,NULL,NULL,NULL,NULL,NULL),('KAV6468','32',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',80976.00,'Baik','',119,NULL,NULL,NULL,NULL,NULL),('KAW2947','#N/A',3,11,'MITSUBISHI PAJERO',0,1999,'Belian',0.00,'Baik','',56,NULL,NULL,NULL,NULL,NULL),('KAW2948','31',3,11,'PAJERO V34VNDRM',0,1999,'Belian',0.00,'Baik','',100,NULL,NULL,NULL,NULL,NULL),('KAW7400','39',3,9,'LAND ROVER DEFENDER',0,1999,'Belian',0.00,'Baik','',187,NULL,NULL,NULL,NULL,NULL),('KAW7500','37',3,9,'LAND ROVER-DEFENDER 110',0,1999,'Belian',132222.00,'Baik','',174,NULL,NULL,NULL,NULL,NULL),('KAW7600','32',3,9,'LAND ROVER DEFENDER',0,1999,'Belian',132222.00,'Baik','',130,NULL,NULL,NULL,NULL,NULL),('KAW7800','33',3,9,'LAND ROVER DEFENDER',0,1999,'Belian',132222.00,'Baik','DALAM PROSES PELUPUSAN',131,NULL,NULL,NULL,NULL,NULL),('KAW8200','29',3,9,'LAND ROVER DEFENDER',0,1999,'Belian',0.00,'Baik','',85,NULL,NULL,NULL,NULL,NULL),('KAW8300','34',3,9,'LAND ROVER DEFENDER 110',0,1999,'Belian',132222.00,'Baik','',157,NULL,NULL,NULL,NULL,NULL),('KAW8400','30',3,9,'LAND ROVER DEFENDER 110',0,1999,'Belian',132222.00,'Baik','',97,NULL,NULL,NULL,NULL,NULL),('KAW8600','35',3,9,'LAND ROVER',0,1999,'Belian',132223.00,'Baik','',160,NULL,NULL,NULL,NULL,NULL),('KAW8700','33',3,9,'LAND ROVER DEFENDER 110',0,1999,'Belian',132222.00,'Baik','',133,NULL,NULL,NULL,NULL,NULL),('KAW8900','31',3,9,'LAND ROVER DEFENDER 110',0,1999,'Belian',0.00,'Baik','',100,NULL,NULL,NULL,NULL,NULL),('KAW9200','37',3,9,'LAND ROVER DEFENDER 110',0,1999,'Belian',132222.00,'Baik','',174,NULL,NULL,NULL,NULL,NULL),('KAW9400','39',3,9,'LAND ROVER DEFENDER 110',0,1999,'Belian',0.00,'Baik','',185,NULL,NULL,NULL,NULL,NULL),('KAX9107','37',3,9,'LAND ROVER DEFENDER 110',0,2000,'Belian',88961.00,'Baik','',174,NULL,NULL,NULL,NULL,NULL),('KAY2218','11',6,0,'TATA LP01313/55',0,2000,'Belian',0.00,'Baik','TATA LP01313/55',13,NULL,NULL,NULL,NULL,NULL),('KBA6780','33',3,11,'MITSUBISHI PAJERO',0,2000,'Belian',86976.00,'Baik','',137,NULL,NULL,NULL,NULL,NULL),('KBB2020','11',4,14,'NISSAN SERENA',0,2002,'Belian',73000.00,'Baik','BAIK',13,NULL,NULL,NULL,NULL,NULL),('KBB6405','34',3,9,'LAND ROVER DEFENDDER 11O',0,2001,'Belian',132223.00,'Baik','',147,NULL,NULL,NULL,NULL,NULL),('KBB6406','3',3,9,'LAND ROVER DEFENDER 110',0,2001,'Belian',132223.00,'Baik','',188,NULL,NULL,NULL,NULL,NULL),('KBB6407','34',3,9,'LAND ROVER DEFENDER 110',0,2001,'Belian',132223.00,'Baik','',149,NULL,NULL,NULL,NULL,NULL),('KBB6408','35',3,9,'LAND ROVER',0,2001,'Belian',132223.00,'Baik','',165,NULL,NULL,NULL,NULL,NULL),('KBB9382','35',3,11,'MITSUBISHI PAJERO',0,2001,'Belian',86976.00,'Baik','',160,NULL,NULL,NULL,NULL,NULL),('KBB9385','#N/A',3,11,'MITSUBISHI PAJERO',0,2001,'Belian',0.00,'Baik','',56,NULL,NULL,NULL,NULL,NULL),('KBC3915','3',3,9,'LAND ROVER DEFENDER 110',0,2001,'Belian',88961.00,'Baik','DIPINJAMKAN KE VEKTOR KOTA STAR',113,NULL,NULL,NULL,NULL,NULL),('KBC814','35',3,5,'ISUZU TROOPER',0,2001,'Belian',0.00,'Baik','JKN BEKALKAN',160,NULL,NULL,NULL,NULL,NULL),('KBC9901','39',3,9,'LAND ROVER DEFENDER 110',0,2001,'Belian',0.00,'Baik','',183,NULL,NULL,NULL,NULL,NULL),('KBD5132','39',3,11,'MITSUBISHI PAJERO',0,2001,'Belian',173952.00,'Baik','',183,NULL,NULL,NULL,NULL,NULL),('KBD5133','39',3,11,'MITSUBISHI PAJERO',0,2001,'Belian',173952.00,'Baik','',183,NULL,NULL,NULL,NULL,NULL),('KBD5134','35',3,11,'MITSUBISHI PAJERO',0,2001,'Belian',86976.00,'Baik','',163,NULL,NULL,NULL,NULL,NULL),('KBE8655','35',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',86976.00,'Baik','',163,NULL,NULL,NULL,NULL,NULL),('KBF1429','29',3,9,'LAND ROVER DEFENDER',0,2002,'Belian',0.00,'Baik','',93,NULL,NULL,NULL,NULL,NULL),('KBF1431','29',3,9,'LAND ROVER DEFENDER',0,2002,'Belian',0.00,'Baik','',80,NULL,NULL,NULL,NULL,NULL),('KBF1432','33',3,9,'LAND ROVER DEFENDER 110',0,2002,'Belian',148817.00,'Baik','',138,NULL,NULL,NULL,NULL,NULL),('KBF1433','33',3,9,'LAND ROVER DEFENDER 110',0,2002,'Belian',148817.00,'Baik','',131,NULL,NULL,NULL,NULL,NULL),('KBF1435','33',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',86976.00,'Baik','DALAM PROSES PELUPUSAN',135,NULL,NULL,NULL,NULL,NULL),('KBF1439','29',3,9,'LAND ROVER DEFENDER 110',0,2002,'Belian',0.00,'Baik','',81,NULL,NULL,NULL,NULL,NULL),('KBF2957','34',3,5,'ISUZU TROOPER UBS25GUK',0,2002,'Belian',128500.00,'Baik','',154,NULL,NULL,NULL,NULL,NULL),('KBF2958','30',3,5,'ISUZU TROOPER UBS25GUK',0,2002,'Belian',128500.00,'Baik','',95,NULL,NULL,NULL,NULL,NULL),('KBF348','39',3,9,'LAND ROVER DEFENDER 110',0,2002,'Belian',0.00,'Baik','',185,NULL,NULL,NULL,NULL,NULL),('KBF350','39',3,9,'LAND ROVER DEFENDER 110',0,2002,'Belian',177921.00,'Baik','',184,NULL,NULL,NULL,NULL,NULL),('KBF8340','21',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',86976.00,'Baik','',43,NULL,NULL,NULL,NULL,NULL),('KBF8341','26',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',86976.00,'Baik','',71,NULL,NULL,NULL,NULL,NULL),('KBF8344','39',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',434880.00,'Baik','',183,NULL,NULL,NULL,NULL,NULL),('KBF8347','29',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',0.00,'Baik','',87,NULL,NULL,NULL,NULL,NULL),('KBG 6715','1',7,14,NULL,26,2012,'Belian',2.60,'Baik','',NULL,NULL,NULL,NULL,1,809),('KBG6918','19',19,14,'NISSAN SERENA',26,0,'Belian',0.00,'Baik','',38,NULL,NULL,NULL,19,812),('KBG6972','25',4,14,'NISSAN VANETTE',0,2002,'Belian',50542.00,'Baik','',65,NULL,NULL,NULL,NULL,NULL),('KBG7109','36',3,11,'MITSUBISHI PAJERO V34VNDRM',0,2002,'Belian',86976.00,'Baik','',170,NULL,NULL,NULL,NULL,NULL),('KBG7110','29',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',0.00,'Baik','',89,NULL,NULL,NULL,NULL,NULL),('KBG7113','26',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',86976.00,'Baik','',71,NULL,NULL,NULL,NULL,NULL),('KBG7114','29',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',0.00,'Baik','',90,NULL,NULL,NULL,NULL,NULL),('KBG7116','30',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',86976.00,'Baik','',95,NULL,NULL,NULL,NULL,NULL),('KBG7119','33',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',0.00,'Baik','',135,NULL,NULL,NULL,NULL,NULL),('KBG7122','36',3,11,'MITSUBISHI PAJERO V34VNDRM',0,2002,'Belian',86976.00,'Baik','',170,NULL,NULL,NULL,NULL,NULL),('KBG7146','22',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',86976.00,'Baik','',48,NULL,NULL,NULL,NULL,NULL),('KBG7149','33',3,11,'MITSUBISHI PAJERO',0,2002,'Belian',86976.00,'Baik','',141,NULL,NULL,NULL,NULL,NULL),('KBG7354','10',4,14,'NISSAN VANETTE',0,2002,'Belian',48722.00,'Baik','',4,NULL,NULL,NULL,NULL,NULL),('KBG7390','33',4,14,'NISSAN VANETTE',0,2002,'Belian',38658.00,'Baik','',136,NULL,NULL,NULL,NULL,NULL),('KBG7391','33',4,14,'NISSAN VANETTE',0,2002,'Belian',0.00,'Baik','',134,NULL,NULL,NULL,NULL,NULL),('KBG7392','33',4,14,'NISSAN VANETTE',0,2002,'Belian',0.00,'Baik','',140,NULL,NULL,NULL,NULL,NULL),('KBG7633','35',4,14,'NISSAN VANETTE',0,2002,'Belian',0.00,'Baik','JKN BEKALKAN',164,NULL,NULL,NULL,NULL,NULL),('KBG7634','8',4,14,'NISSAN VANETTE',0,2002,'Belian',48722.00,'Baik','',197,NULL,NULL,NULL,NULL,NULL),('KBG7636','24',4,14,'NISSAN VANETTE',0,2002,'Belian',48722.00,'Baik','',58,NULL,NULL,NULL,NULL,NULL),('KBG7637','32',4,14,'NISSAN VANETTE',0,2002,'Belian',48722.00,'Baik','',126,NULL,NULL,NULL,NULL,NULL),('KBG7638','39',4,14,'NISSAN VANETTE',0,2002,'Belian',0.00,'Baik','',184,NULL,NULL,NULL,NULL,NULL),('KBG7639','34',4,14,'NISSAN VANETTE',0,2002,'Belian',48722.00,'Baik','',150,NULL,NULL,NULL,NULL,NULL),('KBG7640','34',4,14,'NISSAN VANETTE',0,2002,'Belian',48722.00,'Baik','',146,NULL,NULL,NULL,NULL,NULL),('KBG7641','29',4,14,'NISSAN VANETTE',0,2002,'Belian',0.00,'Baik','',92,NULL,NULL,NULL,NULL,NULL),('KBG7643','29',4,14,'NISSAN VANETTE',0,2002,'Belian',0.00,'Baik','',88,NULL,NULL,NULL,NULL,NULL),('KBG7644','29',4,14,'NISSAN VANETTE',0,2002,'Belian',0.00,'Baik','',73,NULL,NULL,NULL,NULL,NULL),('KBG7646','22',4,14,'NISSAN VANETTE',0,2002,'Belian',48772.00,'Baik','',48,NULL,NULL,NULL,NULL,NULL),('KBG7671','37',4,14,'NISSAN VANETTE',0,2002,'Belian',48722.00,'Baik','',179,NULL,NULL,NULL,NULL,NULL),('KBJ3610','33',3,9,'LAND ROVER DEFENDER 110',0,2003,'Belian',74370.00,'Baik','',131,NULL,NULL,NULL,NULL,NULL),('KBJ3612','33',3,9,'LAND ROVER DEFENDER 110',0,2003,'Belian',0.00,'Baik','',144,NULL,NULL,NULL,NULL,NULL),('KBK1568','36',3,9,'LAND ROVER DEFENDER 11O',0,2003,'Belian',104556.00,'Baik','',170,NULL,NULL,NULL,NULL,NULL),('KBK1570','25',3,9,'LAND ROVER 110',0,2003,'Belian',104556.00,'Baik','',63,NULL,NULL,NULL,NULL,NULL),('KBK8776','38',14,3,'HONDA',0,2003,'Belian',3951.00,'Baik','',181,NULL,NULL,NULL,NULL,NULL),('KBM5056','29',3,14,'NISSAN X-TRAIL',0,2004,'Belian',0.00,'Baik','',88,NULL,NULL,NULL,NULL,NULL),('KBM5958','34',4,14,'NISSAN VANETTE',0,2004,'Belian',0.00,'Baik','',149,NULL,NULL,NULL,NULL,NULL),('KBM5961','34',4,14,'NISSAN VANETTE',0,2004,'Belian',0.00,'Baik','',148,NULL,NULL,NULL,NULL,NULL),('KBM6351','19',19,14,'PERODUA RUSA',21,0,'Belian',0.00,'Baik','',38,NULL,NULL,NULL,19,812),('KBM8372','3',3,14,'NISSAN X-Trail 2.0 L HWD',0,2004,'Belian',83976.00,'Baik','',189,NULL,NULL,NULL,NULL,NULL),('KBN 8579','1',17,14,NULL,21,2016,'Belian',NULL,'Baik','PEJABAT KESIHATAN PINTU MASUK BUKIT KAYU HITAM',212,NULL,NULL,NULL,20,NULL),('KBN2933','22',4,14,'NISSAN VANETTE',0,2004,'Belian',44000.00,'Baik','',48,NULL,NULL,NULL,NULL,NULL),('KBN2935','31',4,14,'NISSAN VANETTE',0,2004,'Belian',42686.00,'Baik','',100,NULL,NULL,NULL,NULL,NULL),('KBN2940','31',4,14,'NISSAN VANETTE',0,2004,'Belian',42686.00,'Baik','',108,NULL,NULL,NULL,NULL,NULL),('KBN2941','#N/A',4,14,'NISSAN VANETTE',0,2004,'Belian',0.00,'Baik','',53,NULL,NULL,NULL,NULL,NULL),('KBN2952','#N/A',4,14,'NISSAN VANETTE',0,2004,'Belian',0.00,'Baik','',56,NULL,NULL,NULL,NULL,NULL),('KBN2956','#N/A',4,14,'NISSAN VANETTE',0,2004,'Belian',0.00,'Baik','',55,NULL,NULL,NULL,NULL,NULL),('KBN3067','37',3,14,'NISSAN X-TRAIL 2.5 4WD',0,2004,'Belian',86404.00,'Baik','',174,NULL,NULL,NULL,NULL,NULL),('KBN3068','26',4,14,'NISSAN VANETTE',0,2004,'Belian',44000.00,'Baik','',70,NULL,NULL,NULL,NULL,NULL),('KBN5055','32',3,11,'MITSUBISHI K74T JEN HFM',0,2004,'Belian',87100.00,'Baik','',114,NULL,NULL,NULL,NULL,NULL),('KBN6108','36',4,8,'KIA PREGIO',0,2004,'Belian',59892.00,'Baik','',170,NULL,NULL,NULL,NULL,NULL),('KBN6114','36',4,8,'KIA PREGIO',0,2004,'Belian',59892.00,'Baik','',171,NULL,NULL,NULL,NULL,NULL),('KBP3468','24',4,1,'FORD ECONOVAN',0,2004,'Belian',53885.00,'Baik','',57,NULL,NULL,NULL,NULL,NULL),('KBP8055','22',4,8,'KIA PREGIO',0,2004,'Belian',59892.00,'Baik','',47,NULL,NULL,NULL,NULL,NULL),('KBP917','39',4,8,'KIA PREGIO',0,2004,'Belian',59892.00,'Baik','',186,NULL,NULL,NULL,NULL,NULL),('KBP920','39',4,8,'KIA PREGIO',0,2004,'Belian',59892.00,'Baik','',187,NULL,NULL,NULL,NULL,NULL),('KBS5965','32',14,0,'MAHINDRA SCORPIO 2.0(M)',0,2006,'Belian',65171.00,'Baik','MAHINDRA SCORPIO 2.0(M)',121,NULL,NULL,NULL,NULL,NULL),('KBS9953','29',4,14,'NISSAN VANETTE',0,2005,'Belian',0.00,'Baik','',86,NULL,NULL,NULL,NULL,NULL),('KBS9954','29',4,14,'NISSAN VANETTE',0,2005,'Belian',0.00,'Baik','',80,NULL,NULL,NULL,NULL,NULL),('KBS9960','29',4,14,'NISSAN VANETTE',0,2005,'Belian',0.00,'Baik','',82,NULL,NULL,NULL,NULL,NULL),('KBT1295','32',4,8,'KIA PREGIO',0,2005,'Belian',65000.00,'Baik','',124,NULL,NULL,NULL,NULL,NULL),('KBT1302','32',4,8,'KIA PREGIO',0,2005,'Belian',65000.00,'Baik','',119,NULL,NULL,NULL,NULL,NULL),('KBT1303','32',4,8,'KIA PREGIO',0,2005,'Belian',65000.00,'Baik','',115,NULL,NULL,NULL,NULL,NULL),('KBT2202','38',14,3,'HONDA',0,2005,'Belian',3951.00,'Baik','',182,NULL,NULL,NULL,NULL,NULL),('KBT2204','32',3,14,'NISSAN X-TRAIL',0,2005,'Belian',54146.00,'Baik','',122,NULL,NULL,NULL,NULL,NULL),('KBT7947','39',3,14,'NISSAN FRONTIER',0,2005,'Belian',99955.00,'Baik','',183,NULL,NULL,NULL,NULL,NULL),('KBT8028','34',13,16,'PROTON WIRA 1.5 GLM',0,2005,'Belian',0.00,'Baik','',154,NULL,NULL,NULL,NULL,NULL),('KBT860','32',3,14,'NISSAN X-TRAIL',0,2005,'Belian',84146.00,'Baik','',125,NULL,NULL,NULL,NULL,NULL),('KBT8739','31',3,14,'NISSAN FRONTIER',0,2005,'Belian',79825.00,'Baik','',100,NULL,NULL,NULL,NULL,NULL),('KBU1040','38',14,3,'HONDA',0,2005,'Belian',3951.00,'Baik','',181,NULL,NULL,NULL,NULL,NULL),('KBU1819','38',14,3,'HONDA',0,2005,'Belian',5000.00,'Baik','',180,NULL,NULL,NULL,NULL,NULL),('KBU1821','38',14,3,'HONDA',0,2005,'Belian',3951.00,'Baik','',180,NULL,NULL,NULL,NULL,NULL),('KBU1823','38',14,3,'HONDA',0,2005,'Belian',3951.00,'Baik','',181,NULL,NULL,NULL,NULL,NULL),('KBU1824','38',14,3,'HONDA',0,2005,'Belian',3951.00,'Baik','',180,NULL,NULL,NULL,NULL,NULL),('KBU2227','29',4,19,'TOYOTA HIACE',0,2005,'Belian',0.00,'Baik','',74,NULL,NULL,NULL,NULL,NULL),('KBU2357','38',14,3,'HONDA',0,2005,'Belian',3951.00,'Baik','',182,NULL,NULL,NULL,NULL,NULL),('KBU2360','38',14,3,'HONDA',0,2005,'Belian',3951.00,'Baik','',182,NULL,NULL,NULL,NULL,NULL),('KBU2382','36',3,19,'TOYOTA HILUX DOUBLE CAB 2.5 L',0,2005,'Belian',83182.00,'Baik','',170,NULL,NULL,NULL,NULL,NULL),('KBU2436','4',4,1,'FORD ECONOVAN',0,2005,'Belian',45132.00,'Baik','',190,NULL,NULL,NULL,NULL,NULL),('KBU3118','29',3,19,'TOYOTA INNOVA',0,2005,'Belian',0.00,'Baik','',88,NULL,NULL,NULL,NULL,NULL),('KBU5598','39',3,14,'NISSAN X-TRAIL',0,2006,'Belian',99498.00,'Baik','',183,NULL,NULL,NULL,NULL,NULL),('KBU5839','31',4,8,'KIA PREGIO',0,2005,'Belian',0.00,'Baik','',111,NULL,NULL,NULL,NULL,NULL),('KBU5840','31',4,14,'NISSAN VANNETTE',0,2005,'Belian',0.00,'Baik','',107,NULL,NULL,NULL,NULL,NULL),('KBU5842','31',3,14,'NISSAN X-TRAIL',0,2006,'Belian',93246.00,'Baik','',100,NULL,NULL,NULL,NULL,NULL),('KBU6329','25',3,14,'NISSAN X-TRAIL 2.5L (A)',0,2006,'Belian',99848.00,'Baik','',63,NULL,NULL,NULL,NULL,NULL),('KBU691','25',4,8,'KIA PREGIO',0,2005,'Belian',65972.00,'Baik','',64,NULL,NULL,NULL,NULL,NULL),('KBU7053','32',3,14,'NISSAN FRONTIER',0,2005,'Belian',86980.00,'Baik','',118,NULL,NULL,NULL,NULL,NULL),('KBU7304','22',4,1,'FORD ECONOVAN',0,2005,'Belian',69564.00,'Baik','',50,NULL,NULL,NULL,NULL,NULL),('KBU7307','22',4,1,'FORD ECONOVAN',0,2005,'Belian',69564.00,'Baik','',48,NULL,NULL,NULL,NULL,NULL),('KBU7569','30',3,14,'NISSAN X-TRAIL 2.0L(A)',0,2005,'Belian',85196.00,'Baik','',97,NULL,NULL,NULL,NULL,NULL),('KBV2711','35',14,3,'HONDA',0,2005,'Belian',4360.00,'Baik','',161,NULL,NULL,NULL,NULL,NULL),('KBV2712','35',14,3,'HONDA',0,2005,'Belian',4360.00,'Baik','',161,NULL,NULL,NULL,NULL,NULL),('KBV2713','35',14,0,'ELIT SN150F',0,2005,'Belian',5200.00,'Baik','ELIT SN150F',162,NULL,NULL,NULL,NULL,NULL),('KBV9046','30',14,3,'MOTOSIKAL HONDA WAVE NF125',0,2005,'Belian',4995.00,'Baik','',94,NULL,NULL,NULL,NULL,NULL),('KBV9050','30',14,3,'MOTOSIKAL HONDA WAVE NF125',0,2005,'Belian',4995.00,'Baik','',94,NULL,NULL,NULL,NULL,NULL),('KBW4947','38',1,19,'TOYOTA',0,2006,'Belian',83000.00,'Baik','',181,NULL,NULL,NULL,NULL,NULL),('KBW5287','6',3,14,'NISSAN X-TRAIL',0,2006,'Belian',97040.00,'Baik','',194,NULL,NULL,NULL,NULL,NULL),('KBW8994','4',4,13,'NAZA RIA 2.5 L',0,2006,'Belian',87514.00,'Baik','',190,NULL,NULL,NULL,NULL,NULL),('KBX2927','33',3,14,'NISSAN X-TRAIL',0,2006,'Belian',86022.00,'Baik','',143,NULL,NULL,NULL,NULL,NULL),('KBY4296','9',3,19,'TOYOTA FORTUNER 2.5 G',0,2006,'Belian',98843.00,'Baik','',200,NULL,NULL,NULL,NULL,NULL),('KBY4400','37',3,19,'TOYOTA HILUX',0,2006,'Belian',83348.00,'Baik','',174,NULL,NULL,NULL,NULL,NULL),('KBY4970','34',3,19,'TOYOTA HILUX',0,2006,'Belian',99823.00,'Baik','',145,NULL,NULL,NULL,NULL,NULL),('KBY8252','36',14,3,'HONDA AN125MRM 7(MY)',0,2006,'Belian',7000.00,'Baik','',170,NULL,NULL,NULL,NULL,NULL),('KBY8900','37',4,1,'FORD ECONOVAN',0,2006,'Belian',70000.00,'Baik','',174,NULL,NULL,NULL,NULL,NULL),('KCE 646','1',17,14,NULL,21,2016,'Belian',NULL,'Baik','',211,NULL,NULL,NULL,NULL,NULL),('KCH 4777','40',19,19,NULL,28,2008,'Belian',67891.67,'Baik','Keadaan kenderaan baik.',NULL,NULL,NULL,NULL,40,810),('KCH 4777 R','1',17,19,NULL,28,2001,'Belian',2.60,'Baik','UNTUK CATATAN SAHAJA',NULL,NULL,NULL,NULL,1,NULL),('KCH 5270','1',17,14,NULL,27,0,'Belian',0.00,'Baik','',NULL,NULL,NULL,NULL,2,809),('KCH778','18',17,14,NULL,21,0,'Belian',0.00,'Baik','',NULL,NULL,NULL,NULL,18,814),('KCJ9161','19',19,14,'NISSAN X-TRAIL',27,0,'Belian',0.00,'Baik','',38,NULL,NULL,NULL,19,812),('KCM 8040','1',17,19,NULL,28,2009,'Belian',70.00,'Baik','',NULL,NULL,NULL,NULL,1,809),('KCM8040','42',19,19,NULL,28,2009,'Belian',80.00,'Baik','',NULL,NULL,NULL,NULL,1,811),('KCP 646','1',17,19,NULL,22,2016,'Belian',NULL,'Baik','',212,NULL,NULL,NULL,NULL,NULL),('KCP 747','1',19,19,NULL,1,2016,'Belian',NULL,'Baik','PKD LANGKAWI',809,NULL,NULL,NULL,35,NULL),('KCQ 966 R','1',4,19,NULL,8,2010,'Belian',2.60,'Baik','UNTUK CATATAN SAHAJA',NULL,NULL,NULL,NULL,1,809),('KDA 7541','1',17,19,NULL,28,2010,'Belian',80000.00,'Baik','',NULL,NULL,NULL,NULL,43,NULL),('KDK 940','1',17,14,NULL,27,2010,'Belian',NULL,'Baik','',NULL,NULL,NULL,NULL,1,809),('KDN 8586','1',19,16,NULL,24,2016,'Belian',NULL,'Baik','',212,NULL,NULL,NULL,1,NULL),('KEB 4560','40',19,16,NULL,24,2015,'Belian',NULL,'Baik','',NULL,NULL,NULL,NULL,40,810),('KEB 7610','1',17,14,NULL,27,2015,'Belian',60.00,'Baik','',NULL,NULL,NULL,NULL,1,809),('KEB7401','18',17,19,'MITSUBISHI PAJERO',22,0,'Belian',0.00,'Baik','',37,NULL,NULL,NULL,18,814),('KEC644','18',19,14,NULL,26,0,'Belian',0.00,'Baik','',NULL,NULL,NULL,NULL,1,814),('KEG 3680','40',3,14,NULL,21,2016,'Belian',117.93,'Baik','',NULL,NULL,NULL,NULL,40,810),('KEG 7610','1',3,19,NULL,30,2016,'Belian',70.00,'Baik','',NULL,NULL,NULL,NULL,1,809),('KEP 7911','18',10,2,NULL,31,2018,'Belian',125000.00,'Baik','',NULL,NULL,NULL,NULL,18,814),('KV4154C','8',3,14,'NISSAN FRONTIER P/ UP 4WD 2.5L (D) 2AB',0,2006,'Belian',88937.00,'Baik','PICK - UP',197,NULL,NULL,NULL,NULL,NULL),('KV5279A','8',1,19,'TOYOTA HIACE VAN',0,1995,'Belian',24816.00,'Baik','',197,NULL,NULL,NULL,NULL,NULL),('KV5632A','8',4,19,'TOYOTA HIACE VAN',0,1995,'Belian',24816.00,'Baik','',197,NULL,NULL,NULL,NULL,NULL),('KV5633A','8',4,19,'TOYOTA HIACE VAN',0,1995,'Belian',24816.00,'Baik','',197,NULL,NULL,NULL,NULL,NULL),('KV5730A','8',1,19,'TOYOTA HIACE VAN',0,1995,'Belian',24816.00,'Baik','',197,NULL,NULL,NULL,NULL,NULL),('KV5731A','8',1,19,'TOYOTA HIACE VAN',0,1995,'Belian',24816.00,'Baik','',197,NULL,NULL,NULL,NULL,NULL),('KV6077A','8',9,19,'TOYOTA HIACE VAN',0,1995,'Belian',24816.00,'Baik','',197,NULL,NULL,NULL,NULL,NULL),('KV6078A','8',8,19,'TOYOTA HIACE VAN',0,1995,'Belian',24816.00,'Baik','',197,NULL,NULL,NULL,NULL,NULL),('LORI ','1',12,11,NULL,38,2021,'Belian',NULL,'Baik','',NULL,NULL,NULL,NULL,1,809);
/*!40000 ALTER TABLE `tkenderaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tnegeri`
--

DROP TABLE IF EXISTS `tnegeri`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tnegeri` (
  `id` int NOT NULL,
  `negeri` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tnegeri`
--

LOCK TABLES `tnegeri` WRITE;
/*!40000 ALTER TABLE `tnegeri` DISABLE KEYS */;
INSERT INTO `tnegeri` VALUES (1,'Kedah'),(2,'Johor'),(3,'Kelantan'),(4,'Melaka'),(5,'Negeri Sembilan'),(6,'Pahang'),(7,'Perak'),(8,'Perak'),(9,'Perlis'),(10,'Pulau Pinang'),(11,'Sabah'),(12,'Sarawak'),(13,'Selangor'),(14,'Terengganu'),(15,'Labuan'),(16,'Putrajaya'),(17,'Kuala Lumpur');
/*!40000 ALTER TABLE `tnegeri` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpekeliling`
--

DROP TABLE IF EXISTS `tpekeliling`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpekeliling` (
  `id` int DEFAULT NULL,
  `tajuk` varchar(200) DEFAULT NULL,
  `fail` varchar(50) DEFAULT NULL,
  `tarikh_pekeliling` varchar(100) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpekeliling`
--

LOCK TABLES `tpekeliling` WRITE;
/*!40000 ALTER TABLE `tpekeliling` DISABLE KEYS */;
/*!40000 ALTER TABLE `tpekeliling` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpemandu`
--

DROP TABLE IF EXISTS `tpemandu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpemandu` (
  `id` int NOT NULL,
  `nokp` varchar(15) DEFAULT NULL,
  `namapemandu` varchar(100) DEFAULT NULL,
  `idjawatan` int DEFAULT NULL,
  `idgred` int DEFAULT NULL,
  `idptj` int DEFAULT NULL,
  `idbahagian` int DEFAULT NULL,
  `idunit` int DEFAULT NULL,
  `notelefon` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpemandu`
--

LOCK TABLES `tpemandu` WRITE;
/*!40000 ALTER TABLE `tpemandu` DISABLE KEYS */;
INSERT INTO `tpemandu` VALUES (4,'781216025261','MOHAMMAD REDZUAN BIN OTHMAN ',31,64,1,809,38,'017-5542559','Tidak Aktif','test'),(5,'770607026639','NASIRON BIN SHUIB ',31,64,1,809,NULL,'012-5910630','Aktif',''),(6,'751025025309','MAZLAN BIN TALIB ',31,64,1,809,40,'019-5640338','Aktif',''),(8,'840615025127','MOHD RIDWAN BIN MAZINI',31,64,2,210,NULL,NULL,'Aktif',''),(9,'580625025675','ABD HAMID BIN SHAFIE',31,34,2,210,NULL,'04-7741000','Aktif',''),(10,'890905025155','Nizam Abdullah',31,34,5,192,NULL,'0174512255','Aktif',''),(11,'740505125966','HUZAIRI BIN ISMAIL',31,34,8,198,NULL,'0195547414','Aktif',''),(12,'682305026548','Muhammad Shahrul',31,35,12,19,NULL,NULL,'Aktif',''),(13,'870251026584','Azlan shah',31,1,12,14,NULL,NULL,'Aktif',''),(14,'87562512255','Abu Hurairah bin Ali',31,33,12,15,NULL,'0123923134 / 0312121','Aktif','Cuti Belajar 5 Tahun'),(15,'580254025231','Muhammad',31,34,12,17,NULL,NULL,'Berpindah/Bersara',''),(19,'820219025071','ABDUL AZIZ BIN RAHIM',31,64,1,809,NULL,'014-2400001','Aktif',''),(20,'900416025361','MOHAMAD FIKRI BIN OMAR',31,64,1,809,40,'011-11944745','Aktif','017-4137745'),(21,'700507025029','YUNUS BIN SAAD',32,23,1,809,NULL,'017-5985267','Aktif','DIGUNAKAN SEKIRANYA KEADAAN MEMERLUKAN'),(22,'700821025975','AZHAR BIN ABU BAKAR',32,23,1,809,36,'012-5665644','Aktif','DIGUNAKAN BILA KEADAAN MEMERLUKAN'),(23,'720601025701','ROHAIMI BIN ALI @ ROMLI',31,64,19,812,53,'012-2222222','Aktif',''),(24,'740725025285','ASWAD BIN AWANG',31,64,19,812,53,'012-4444444','Aktif',''),(25,'781004025665','MUHAMMAD SAFIZOL BIN MD SAAD',31,64,18,814,67,'0192595518','Aktif',''),(26,'750324026673','KAMAL HIJAZ BIN ABDUL GANI',31,69,18,814,54,'0194235903','Lain-lain','BERTUKAR KE UNIT PENGURUSAN '),(27,'810817025515','MUHAMMAD EZUAN B RAZALI @ GHAZALI',31,64,40,810,43,'017-5891904','Aktif',''),(28,'690518025431','MOHAMAD AZAM BIN HASSAN',2,24,40,810,45,'0194414542','Aktif',''),(29,'630715025399','MUHAMAD BIN ALI',2,25,40,810,45,'0125785649','Aktif',''),(30,'770302-02-6187','MOHD ZALANI BIN ISHAK',31,36,42,811,52,NULL,'Aktif',''),(31,'761204025207','YAHYA BIN MUSTAFA',32,65,1,809,40,'019-5405804','Aktif',''),(32,'840423025309','MUHAMMAD HUSNI BIN GHAZALI',37,24,1,809,38,'012-5997560','Aktif','PEMANDU SAMBILAN UTK UNIT PEMBANGUNAN SAHAJA'),(33,'810817025515','MUHAMMAD EZUAN BIN RAZALI@GHAZALI',31,69,1,809,NULL,'011-55804338','Aktif','011-59624338 no whatsup'),(34,'880808028889','MOHD RIDWAN BIN MAZINI',31,64,1,809,40,'01111221471','Aktif',''),(35,'661212025522','CHANRAN A/L SINNIAH',30,69,1,809,40,'0164547968','Aktif',''),(36,'860723-02-5149','MOHD AZIZI BIN AHMAD KHILMI',31,64,1,809,40,'012-5729506','Aktif',''),(37,'5465','PEMANDU KEJURUTERAAN',31,64,1,809,40,'047741153','Aktif','KAK HUSNI'),(38,'7777','PEMANDU RKPBV',31,64,1,809,40,'047746000','Aktif',''),(40,'671123025513','NOR AZIZI BIN JOHARI',31,64,1,809,40,'013-5316954','Aktif',''),(41,'670612025675','MOHD FADHLI BIN AHMAD',31,64,1,809,40,'010-5665987','Aktif',''),(42,'750324026673','KAMAL HIJAZ BIN ABDUL GANI',31,64,1,809,40,'019-4235903','Aktif',''),(43,'781004025665','MOHAMAD SAFIZOL BIN MD SAAD',31,64,1,809,40,'019-2595518','Aktif',''),(44,'770302026187','MOHD ZALANI BIN ISAHAK',31,64,1,809,40,'0134032503','Aktif',''),(45,'840729-06-5039','SHAH RIFUDIN BIN SHAH RAID ',31,64,1,809,40,'014-5150300','Aktif','PEMANDU (BKKM)'),(46,'721110025277','MUHAMMAD FITRI BIN ABU SEMAN',31,64,1,809,40,'019-4472068','Aktif',''),(47,'TPKNG','PEMANDU TPKNG',31,34,1,809,40,'-','Aktif',''),(48,'900918025193','MUHAMMAD HAZIQ BIN SHAFIE ',31,64,1,809,40,'013-4196989','Aktif',''),(49,'TPKNF','PEMANDU TPKNF',31,1,1,809,40,'-','Aktif',''),(50,'12345678','PEMANDU KENDERAAN',31,64,1,809,40,'0124693019','Aktif',''),(51,'740725025285','ASWAD BIN AWANG',31,64,1,809,NULL,'011-56567128','Aktif',''),(52,'720601025701','ROHAIMI BIN ALi',31,64,1,809,NULL,'013-4469499','Aktif',''),(53,'701103025115','WAN AHMAD ZAKI BIN WAN YAHAYA',46,30,1,809,36,'018-2522771','Aktif',''),(54,'12345','PEMANDU KENDERAAN SAMBILAN',37,24,1,809,40,'-','Aktif',''),(55,'860617-08-5025','MOHD ZAKWAN BIN ABDUL MANAP',32,65,1,809,40,'019-5450049','Aktif',NULL),(56,'860706-56-6111','MOHD HAFFIS BIN ABDUL SAMAD',31,34,1,809,40,'011-35608086','Aktif',NULL),(57,'700821-02-5971','AZHAR BIN ABU BAKAR ',32,65,18,814,54,'012-5665644','Aktif',''),(58,'860823025103','MOHD NIZAM BIN IBRAHIM',56,64,1,809,40,'017-4679443','Tidak Aktif',''),(59,'951220025431','MEGAT PUTERA NASRULLAH BIN ROZIZI',31,1,18,814,54,'011-26900726','Aktif',''),(60,'821030025531','MUHAMMAD SHARUZI B. ZAINOL',32,65,1,809,40,'016-6075539','Aktif',NULL),(61,'020311-05-1654','NURMAISARAH BINTI SUZAMIEE',28,10,1,809,39,'011-61404157','Aktif','permohonan baru2'),(62,'020856245874','TEST PEMANDU',18,11,5,51,NULL,'011-61404157','Tidak Aktif',NULL),(63,'020822-09-0278','TEST FORMAT',16,11,12,369,1,'011-61404157','Aktif','test');
/*!40000 ALTER TABLE `tpemandu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpemandu_backup_20250516_221241`
--

DROP TABLE IF EXISTS `tpemandu_backup_20250516_221241`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpemandu_backup_20250516_221241` (
  `id` int NOT NULL,
  `nokp` varchar(15) DEFAULT NULL,
  `namapemandu` varchar(100) DEFAULT NULL,
  `idjawatan` int DEFAULT NULL,
  `idgred` int DEFAULT NULL,
  `idptj` int DEFAULT NULL,
  `idbahagian` int DEFAULT NULL,
  `idunit` int DEFAULT NULL,
  `notelefon` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpemandu_backup_20250516_221241`
--

LOCK TABLES `tpemandu_backup_20250516_221241` WRITE;
/*!40000 ALTER TABLE `tpemandu_backup_20250516_221241` DISABLE KEYS */;
INSERT INTO `tpemandu_backup_20250516_221241` VALUES (0,'999192019299','NAJMO',2,15,1,809,40,'010403020544','Aktif',NULL),(4,'781216025261','MOHAMMAD REDZUAN BIN OTHMAN ',31,64,1,809,38,'017-5542559','Aktif',''),(5,'770607026639','NASIRON BIN SHUIB ',31,64,1,809,NULL,'012-5910630','Aktif',''),(6,'751025025309','MAZLAN BIN TALIB ',31,64,1,809,40,'019-5640338','Aktif',''),(8,'840615025127','MOHD RIDWAN BIN MAZINI',31,64,2,210,NULL,NULL,'Aktif',''),(9,'580625025675','ABD HAMID BIN SHAFIE',31,34,2,210,NULL,'04-7741000','Aktif',''),(10,'890905025155','Nizam Abdullah',31,34,5,192,NULL,'0174512255','Aktif',''),(11,'740505125966','HUZAIRI BIN ISMAIL',31,34,8,198,NULL,'0195547414','Aktif',''),(12,'682305026548','Muhammad Shahrul',31,35,12,19,NULL,NULL,'Aktif',''),(13,'870251026584','Azlan shah',31,1,12,14,NULL,NULL,'Aktif',''),(14,'87562512255','Abu Hurairah bin Ali',31,33,12,15,NULL,'0123923134 / 0312121','Aktif','Cuti Belajar 5 Tahun'),(15,'580254025231','Muhammad',31,34,12,17,NULL,NULL,'Berpindah/Bersara',''),(19,'820219025071','ABDUL AZIZ BIN RAHIM',31,64,1,809,NULL,'014-2400001','Aktif',''),(20,'900416025361','MOHAMAD FIKRI BIN OMAR',31,64,1,809,40,'011-11944745','Aktif','017-4137745'),(21,'700507025029','YUNUS BIN SAAD',32,23,1,809,NULL,'017-5985267','Aktif','DIGUNAKAN SEKIRANYA KEADAAN MEMERLUKAN'),(22,'700821025975','AZHAR BIN ABU BAKAR',32,23,1,809,36,'012-5665644','Aktif','DIGUNAKAN BILA KEADAAN MEMERLUKAN'),(23,'720601025701','ROHAIMI BIN ALI @ ROMLI',31,64,19,812,53,'012-2222222','Aktif',''),(24,'740725025285','ASWAD BIN AWANG',31,64,19,812,53,'012-4444444','Aktif',''),(25,'781004025665','MUHAMMAD SAFIZOL BIN MD SAAD',31,64,18,814,67,'0192595518','Aktif',''),(26,'750324026673','KAMAL HIJAZ BIN ABDUL GANI',31,69,18,814,54,'0194235903','Lain-lain','BERTUKAR KE UNIT PENGURUSAN '),(27,'810817025515','MUHAMMAD EZUAN B RAZALI @ GHAZALI',31,64,40,810,43,'017-5891904','Aktif',''),(28,'690518025431','MOHAMAD AZAM BIN HASSAN',2,24,40,810,45,'0194414542','Aktif',''),(29,'630715025399','MUHAMAD BIN ALI',2,25,40,810,45,'0125785649','Aktif',''),(30,'770302-02-6187','MOHD ZALANI BIN ISHAK',31,36,42,811,52,NULL,'Aktif',''),(31,'761204025207','YAHYA BIN MUSTAFA',32,65,1,809,40,'019-5405804','Aktif',''),(32,'840423025309','MUHAMMAD HUSNI BIN GHAZALI',37,24,1,809,38,'012-5997560','Aktif','PEMANDU SAMBILAN UTK UNIT PEMBANGUNAN SAHAJA'),(33,'810817025515','MUHAMMAD EZUAN BIN RAZALI@GHAZALI',31,69,1,809,NULL,'011-55804338','Aktif','011-59624338 no whatsup'),(34,'880808028889','MOHD RIDWAN BIN MAZINI',31,64,1,809,40,'01111221471','Aktif',''),(35,'661212025522','CHANRAN A/L SINNIAH',30,69,1,809,40,'0164547968','Aktif',''),(36,'860723-02-5149','MOHD AZIZI BIN AHMAD KHILMI',31,64,1,809,40,'012-5729506','Aktif',''),(37,'5465','PEMANDU KEJURUTERAAN',31,64,1,809,40,'047741153','Aktif','KAK HUSNI'),(38,'7777','PEMANDU RKPBV',31,64,1,809,40,'047746000','Aktif',''),(40,'671123025513','NOR AZIZI BIN JOHARI',31,64,1,809,40,'013-5316954','Aktif',''),(41,'670612025675','MOHD FADHLI BIN AHMAD',31,64,1,809,40,'010-5665987','Aktif',''),(42,'750324026673','KAMAL HIJAZ BIN ABDUL GANI',31,64,1,809,40,'019-4235903','Aktif',''),(43,'781004025665','MOHAMAD SAFIZOL BIN MD SAAD',31,64,1,809,40,'019-2595518','Aktif',''),(44,'770302026187','MOHD ZALANI BIN ISAHAK',31,64,1,809,40,'0134032503','Aktif',''),(45,'840729065039','SHAH RIFUDIN BIN SHAH RAID ',31,64,1,809,40,'0145150300','Aktif','PEMANDU (BKKM)'),(46,'721110025277','MUHAMMAD FITRI BIN ABU SEMAN',31,64,1,809,40,'019-4472068','Aktif',''),(47,'TPKNG','PEMANDU TPKNG',31,34,1,809,40,'-','Aktif',''),(48,'900918025193','MUHAMMAD HAZIQ BIN SHAFIE ',31,64,1,809,40,'013-4196989','Aktif',''),(49,'TPKNF','PEMANDU TPKNF',31,1,1,809,40,'-','Aktif',''),(50,'12345678','PEMANDU KENDERAAN',31,64,1,809,40,'0124693019','Aktif',''),(51,'740725025285','ASWAD BIN AWANG',31,64,1,809,NULL,'011-56567128','Aktif',''),(52,'720601025701','ROHAIMI BIN ALi',31,64,1,809,NULL,'013-4469499','Aktif',''),(53,'701103025115','WAN AHMAD ZAKI BIN WAN YAHAYA',46,30,1,809,36,'018-2522771','Aktif',''),(54,'12345','PEMANDU KENDERAAN SAMBILAN',37,24,1,809,40,'-','Aktif',''),(55,'860617085025','MOHD ZAKWAN BIN ABDUL MANAP',32,65,1,809,40,'019-5450049','Aktif',''),(56,'860706566111','MOHD HAFFIS BIN ABDUL SAMAD',31,34,1,809,40,'01135608086','Aktif',''),(57,'700821-02-5971','AZHAR BIN ABU BAKAR ',32,65,18,814,54,'012-5665644','Aktif',''),(58,'860823025103','MOHD NIZAM BIN IBRAHIM',56,64,1,809,40,'017-4679443','Aktif',''),(59,'951220025431','MEGAT PUTERA NASRULLAH BIN ROZIZI',31,1,18,814,54,'011-26900726','Aktif',''),(60,'821030025531','MUHAMMAD SHARUZI B. ZAINOL',32,65,1,809,40,'0166075539','Aktif','');
/*!40000 ALTER TABLE `tpemandu_backup_20250516_221241` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpemandu_new`
--

DROP TABLE IF EXISTS `tpemandu_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpemandu_new` (
  `id` int NOT NULL,
  `nokp` varchar(15) DEFAULT NULL,
  `namapemandu` varchar(100) DEFAULT NULL,
  `idjawatan` int DEFAULT NULL,
  `idgred` int DEFAULT NULL,
  `idptj` int DEFAULT NULL,
  `idbahagian` int DEFAULT NULL,
  `idunit` int DEFAULT NULL,
  `notelefon` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpemandu_new`
--

LOCK TABLES `tpemandu_new` WRITE;
/*!40000 ALTER TABLE `tpemandu_new` DISABLE KEYS */;
/*!40000 ALTER TABLE `tpemandu_new` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpemeriksaan_berkala`
--

DROP TABLE IF EXISTS `tpemeriksaan_berkala`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpemeriksaan_berkala` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_plat` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pusat_pemeriksaan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tarikh_pemeriksaan` date NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpemeriksaan_berkala`
--

LOCK TABLES `tpemeriksaan_berkala` WRITE;
/*!40000 ALTER TABLE `tpemeriksaan_berkala` DISABLE KEYS */;
INSERT INTO `tpemeriksaan_berkala` VALUES (2,'WCJ5943','Puspakom','2014-02-16','','2025-05-17 03:54:35'),(3,'WCM5562','JKR','2013-02-12','','2025-05-17 03:54:35'),(4,'KV4154C','JKR','2015-02-02','','2025-05-17 03:54:35'),(5,'KBY4296','JKR','2014-12-16','','2025-05-17 03:54:35'),(6,'KBY646','JKR','2015-04-01','Pemeriksaan Keseluruhan Badan Kenderaan termasuk enjin.','2025-05-17 03:54:35'),(7,'WNQ9567','JKR','2016-10-17','','2025-05-17 03:54:35'),(8,'WC 5247 D','JKR','2017-02-19','Pemeriksaan Tahunan 2017','2025-05-17 03:54:35'),(9,'KCP 646','JKR','2017-02-19','Pemeriksaan Tahunan 2017','2025-05-17 03:54:35'),(10,'KDN 8586','JKR','2017-02-19','Pemeriksaan Tahunan 2017','2025-05-17 03:54:35'),(11,'KCE 646','JKR','2017-02-19','Pemeriksaan Tahunan 2017','2025-05-17 03:54:35'),(12,'WC 5246 D','JKR','2017-02-19','Pemeriksaan Tahunan 2017','2025-05-17 03:54:35'),(13,'WC 721 G','JKR','2017-02-19','Pemeriksaan Tahunan 2017','2025-05-17 03:54:35'),(14,'WC 716 G','JKR','2017-02-27','Pemeriksaan Tahunan 2017','2025-05-17 03:54:35'),(15,'KCP 747','JKR','2023-03-27','UBAH KE TARIKH LAIN ','2025-05-17 03:54:35'),(16,'KCE 646','JKR','2023-03-27','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(17,'KCM 8040','JKR','2023-03-28','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(18,'KBN 8579','JKR','2023-03-28','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(19,'KCP 646','JKR','2023-03-29','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(20,'WNQ 9567','JKR','2023-03-29','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(21,'KDN 8586','JKR','2023-04-04','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(22,'WC 5247 D','JKR','2023-04-04','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(23,'WC 5246 D','JKR','2023-04-05','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(24,'WC 716 G','JKR','2023-04-05','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(25,'KCH 4777 R','JKR','2023-04-10','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(26,'VFH 5891','JKR','2023-04-11','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(27,'WC 721 G','JKR','2023-04-12','PEMERIKSAAN TAHUNAN 2023','2025-05-17 03:54:35'),(28,'KEP 7911','JKR','2023-04-11','PEMANDU SAFIZOL','2025-05-17 03:54:35'),(29,'KCH778','JKR','2023-04-03','PEMANDU SAFIZOL','2025-05-17 03:54:35'),(30,'KEC644','JKR','2023-04-03','PEMANDU AZHAR','2025-05-17 03:54:35'),(31,'kcw1010','JKR','2025-04-23','','2025-05-17 03:54:35'),(32,'KCW 5341','JKK','2025-05-06','PEMANDU','2025-05-17 03:54:35'),(66,'RAG 2040','JKR','2026-02-25','test','2026-02-25 15:25:21'),(68,'RF4377','JKR','2026-03-03','test','2026-03-03 12:11:58');
/*!40000 ALTER TABLE `tpemeriksaan_berkala` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpenempatan`
--

DROP TABLE IF EXISTS `tpenempatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpenempatan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idptj` int DEFAULT NULL,
  `penempatan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpenempatan`
--

LOCK TABLES `tpenempatan` WRITE;
/*!40000 ALTER TABLE `tpenempatan` DISABLE KEYS */;
INSERT INTO `tpenempatan` VALUES (1,10,'HOSPITAL BALING (DIPINJAM)'),(2,10,'HOSPITAL SULTAN ABD HALIM, SG PETANI'),(3,10,'HOSPITAL SULTAN ABDUL HALIM'),(4,10,'HOSPITAL SULTAN ABDUL HALIM, SG PETANI'),(5,11,'JABATAN ENT'),(6,11,'JABATAN OFTALMOLOGI'),(7,11,'JABATAN PAEDIATRIK'),(8,11,'JABATAN PATOLOGI'),(9,11,'JABATAN PEMBEDAHAN'),(10,11,'JABATAN PERUBATAN'),(11,11,'JABATAN PERUBATAN DADA'),(12,11,'JABATAN PSIKIATRI'),(13,11,'UNIT KENDERAAN'),(14,12,'HOSPITAL YAN'),(15,12,'PENGARAH KESIHATAN NEGERI'),(17,12,'UNIT KECEMASAN, HOSPITAL YAN'),(19,12,'UNIT LATIHAN'),(20,12,'UNIT PEMBANGUNAN'),(21,12,'UNIT PENGANGKUTAN, HOSPITAL YAN'),(23,12,'UNIT PENTADBIRAN, HOSPITAL YAN'),(24,12,'UNIT RUMAH MAYAT'),(26,13,'HOSPITAL KULIM'),(27,13,'KOLEJ JURURAWAT MASYARAKAT KULIM'),(28,13,'KOLEY JURURAWAT MASYARAKAT KULIM'),(29,14,'UNIT DMU, KOLEJ KEJURURAWATAN ALOR STAR'),(30,14,'UNIT LATIHAN'),(31,14,'UNIT LATIHAN, KOLEJ KEJURURAWATAN ALOR S'),(32,14,'UNIT PENTADBIRAN'),(33,15,'KOLEJ KEJURURAWATAN SG PETANI'),(34,16,'KOLEJ KEJURURAWATAN ALOR STAR'),(35,16,'KOLEJ KESIHATAN AWAM JITRA'),(36,17,'KOLEJ PEMBANTU PERUBATAN ALOR STAR'),(37,18,'KKIK'),(38,19,'PEJABAT TPKN (G)'),(39,20,'JKN'),(40,20,'KKIK'),(41,20,'MAKMAL'),(42,20,'UNIT KAWALAN MUTU MAKANAN'),(43,21,'KLINIK PERGIGIAN BALING'),(44,21,'KLINIK PERGIGIAN BALING (PASUKAN BERGERA'),(45,21,'KLINIK PERGIGIAN KUALA KETIL (PASUKAN BE'),(46,21,'KLINIK PERGIGIAN KUPANG (PASUKAN BERGERA'),(47,22,'KLINIK  PERGIGIAN KODIANG'),(48,22,'KLINIK PERGIGIAN JITRA'),(49,22,'KLINIK PERGIGIAN KODIANG'),(50,22,'KLINIK PERGIGIAN KUALA NERANG'),(51,23,'KLINIK KESIHATAN KOTA SARANG  SEMUT'),(52,23,'KLINIK PERGIGIAN ANIKA JLN PUTRA'),(53,23,'KLINIK PERGIGIAN PENDANG'),(54,23,'KLINIK PERGIGIAN POKOK SENA'),(55,23,'KLINIK PERGIGIAN SIMPANG KUALA'),(56,23,'KLINIK PERGIGIAN TELOK WANJAH'),(57,24,'KKLINIK PERGIGIAN TAMAN SELASIH'),(58,24,'KLINIK PERGIGIAN BANDAR BAHARU'),(59,24,'KLINIK PERGIGIAN KULIM'),(60,24,'KLINIK PERGIGIAN MERBAU PULAS'),(61,24,'KLINIK PERGIGIAN PADANG SERAI'),(62,24,'KLINIK PERGIGIAN SERDANG'),(63,25,'KLINIK PERGIGIAN BESAR KUAH'),(64,25,'KLINIK PERGIGIAN LANGKAWI'),(65,25,'KLINIK PERGIGIAN PADANG MATSIRAT'),(66,26,'KLINIK PERGIGIAN BEDONG'),(67,26,'KLINIK PERGIGIAN GUAR CHEMPEDAK'),(68,26,'KLINIK PERGIGIAN KOTA KUALA MUDA'),(69,26,'KLINIK PERGIGIAN MERBOK'),(70,26,'KLINIK PERGIGIAN SIK'),(71,26,'KLINIK PERGIGIAN SUNGAI PETANI'),(72,29,'CKPBV KUALA KETIL'),(73,29,'KIA BALING'),(74,29,'KLINIK DESA BALING'),(75,29,'KLINIK DESA BANDAR'),(76,29,'KLINIK DESA CARUK BERAS'),(77,29,'KLINIK DESA KUALA MERAH'),(78,29,'KLINIK DESA TANJUNG PARI'),(79,29,'KLINIK DESA WENG'),(80,29,'KLINIK KESIHATAN KG LALANG'),(81,29,'KLINIK KESIHATAN KUALA KETIL'),(82,29,'KLINIK KESIHATAN KUPANG'),(83,29,'KLINIK KESIHATAN KUPANG (BAKAS)'),(84,29,'KLINIK KESIHATAN MALAU'),(85,29,'KLINIK KESIHATAN PARIT PANJANG'),(86,29,'KLINIK KESIHATAN TAWAR'),(87,29,'KMAM'),(88,29,'PEJABAT'),(89,29,'UNIT SEKOLAH'),(90,29,'UPK PEJABAT'),(91,29,'VEKTOR  BALING'),(92,29,'VEKTOR BALING'),(93,29,'VEKTOR KUALA KETIL'),(94,30,'INSPEKTORAT'),(95,30,'KLINIK DESA BANDAR BAHARU'),(96,30,'KLINIK KESIHATAN BANDAR BAHARU'),(97,30,'KLINIK KESIHATAN LUBUK BUNTAR'),(98,30,'KLINIK KESIHATAN SERDANG'),(99,31,'KLINIK DESA  KOTA STAR'),(100,31,'KLINIK DESA KOTA STAR'),(101,31,'KLINIK DESA PADANG HANG'),(102,31,'KLINIK DESA SIMPANG 4 KANGKONG'),(103,31,'KLINIK KESIHATAN ALOR JANGGUS'),(104,31,'KLINIK KESIHATAN JALAN PUTRA'),(105,31,'KLINIK KESIHATAN KOTA SARANG SEMUT'),(106,31,'KLINIK KESIHATAN KUALA KEDAH'),(107,31,'KLINIK KESIHATAN LANGGAR'),(108,31,'KLINIK KESIHATAN POKOK SENA'),(109,31,'KLINIK KESIHATAN SIMPANG EMPAT'),(110,31,'KLINIK KESIHATAN SIMPANG KUALA'),(111,31,'PKD'),(112,31,'POLIKLINIK SIMPANG KUALA'),(113,31,'VEKTOR KOTA STAR'),(114,32,'BAKAS, KLINIK DESA KUALA MUDA'),(115,32,'KKIA TAMAN BANDAR BARU CINTA SAYANG'),(116,32,'KLINIK DESA KUALA MUDA'),(117,32,'KLINIK KESIHATAN BAKAR ARANG'),(118,32,'KLINIK KESIHATAN BEDONG'),(119,32,'KLINIK KESIHATAN BUKIT SELAMBAU'),(120,32,'KLINIK KESIHATAN IBU DAN ANAK SG. PETANI'),(121,32,'KLINIK KESIHATAN KOTA KUALA MUDA'),(122,32,'KLINIK KESIHATAN MERBOK'),(123,32,'KLINIK KESIHATAN TAMAN BANDAR BARU  SG. LALANG'),(124,32,'KLINIK KESIHATAN TAMAN BANDAR BARU SG LA'),(125,32,'PENTADBIRAN, KLINIK DESA KUALA MUDA'),(126,32,'PENTADBIRAN, KLINIK DESA KUALAMUDA'),(127,32,'UNIT PENDIDIKAN KESIHATAN KUALA MUDA'),(128,32,'VEKTOR  KUALA MUDA'),(129,32,'VEKTOR, KLINIK DESA KUALA MUDA'),(130,32,'VEKTOR, PKD'),(131,33,'CKPBV JITA'),(132,33,'KLINIK DESA KUBANG PASU'),(133,33,'KLINIK KESIHATAN AIR HITAM'),(134,33,'KLINIK KESIHATAN BANAI'),(135,33,'KLINIK KESIHATAN CHANGLOON'),(136,33,'KLINIK KESIHATAN JITRA'),(137,33,'KLINIK KESIHATAN KEPALA BATAS'),(138,33,'KLINIK KESIHATAN KODIANG'),(139,33,'KLINIK KESIHATAN LAKA TEMIN'),(140,33,'KLINIK KESIHATAN TUNJANG'),(141,33,'KMM'),(142,33,'PASUKAN SEKOLAH'),(143,33,'PEJABAT'),(144,33,'PROMOSI KESIHATAN'),(145,34,'BAKAS'),(146,34,'KIA'),(147,34,'KLINIK KESIHATAN KARANGAN'),(148,34,'KLINIK KESIHATAN LUNAS'),(149,34,'KLINIK KESIHATAN MAHANG'),(150,34,'KLINIK KESIHATAN MERBAU PULAS'),(151,34,'KLINIK KESIHATAN PADANG SERAI'),(152,34,'KLINIK KESIHATAN TAMAN SELASIH'),(153,34,'OPD'),(154,34,'PEJABAT PENTADBIRAN'),(155,34,'PKD'),(156,34,'UNIT PENDIDIKAN KESIHATAN'),(157,34,'VEKTOR'),(158,35,'BAKAS'),(159,35,'KKIA  KUAH'),(160,35,'KLINIK DESA LANGKAWI'),(161,35,'KLINIK DESA LUBUK CHEMPEDAK'),(162,35,'KLINIK DESA TUBA'),(163,35,'KLINIK KESIHATAN AIR HANGAT'),(164,35,'KLINIK KESIHATAN KUAH'),(165,35,'KLINIK KESIHATAN PADANG MAT SIRAT'),(166,35,'PASUKAN SEKOLAH, KLINIK KESIHATAN KUAH'),(167,35,'PKD'),(168,35,'POLIKLINIK KUAH'),(169,35,'RKPBV'),(170,36,'KLINIK DESA PADANG TERAP'),(171,36,'KLINIK KESIHATAN KUALA NERANG'),(172,36,'KLINIK KESIHATAN LUBUK MERBAU'),(173,36,'KLINIK KESIHATAN NAKA'),(174,37,'KLINIK DESA PENDANG'),(175,37,'KLINIK KESIHATAN KUBUR PANJANG'),(176,37,'KLINIK KESIHATAN PENDANG'),(177,37,'KLINIK KESIHATAN SG. TIANG'),(178,37,'KLINIK KESIHATAN SG.TIANG'),(179,37,'UNIT SEKOLAH, KLINIK DESA PENDANG'),(180,38,'KLINIK DESA SIK'),(181,38,'KLINIK KESIHATAN GULAU'),(182,38,'KLINIK KESIHATAN JENIANG'),(183,39,'KLINIK DESA YAN'),(184,39,'KLINIK KESIHATAN GUAR CHEMPEDAK'),(185,39,'KLINIK KESIHATAN SG. LIMAU DALAM'),(186,39,'KLINIK KESIHATAN SUNGAI LIMAU DALAM'),(187,39,'KLINIK KESIHATAN YAN'),(188,3,'VEKTOR'),(189,3,'VEKTOR NEGERI'),(190,4,'HOSPITAL BALING'),(191,5,'BILIK MAYAT'),(192,5,'PEJABAT PENTADBIRAN'),(193,5,'UNIT KECEMASAN'),(194,6,'HOSPITAL KUALA NERANG'),(195,6,'HOSPITAL LANGKAWI'),(196,7,'HOSPITAL KULIM'),(197,8,'HOSPITAL LANGKAWI'),(198,8,'UNIT PENGANGKUTAN (PENTADBIRAN)'),(199,8,'UNIT PENGANGKUTAN (PENTADBIRAN), HOSP. L'),(200,9,'UNIT KECEMASAN'),(202,4,'UNIT PENTADBIRAN'),(203,4,'UNIT PENTADBIRAN'),(204,4,'KUALA SELANGOR');
/*!40000 ALTER TABLE `tpenempatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpengeluar`
--

DROP TABLE IF EXISTS `tpengeluar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpengeluar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `namapengeluar` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpengeluar`
--

LOCK TABLES `tpengeluar` WRITE;
/*!40000 ALTER TABLE `tpengeluar` DISABLE KEYS */;
INSERT INTO `tpengeluar` VALUES (1,'FORD'),(2,'HINO'),(3,'HONDA'),(4,'INOKOM'),(5,'ISUZU'),(6,'IVECO'),(7,'KAWASAKI'),(8,'KIA'),(9,'LAND ROVER'),(10,'MERCEDES'),(11,'MITSUBISHI'),(12,'MODENAS'),(13,'NAZA'),(14,'NISSAN'),(15,'PERODUA'),(16,'PROTON'),(17,'RENAULT'),(18,'SUZUKI'),(19,'TOYOTA'),(20,'VOLVO'),(21,'TIDAK DINYATAKAN'),(24,'SUBARU'),(26,'WESTSTAR MAXUS V80');
/*!40000 ALTER TABLE `tpengeluar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpenyelenggaraan`
--

DROP TABLE IF EXISTS `tpenyelenggaraan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpenyelenggaraan` (
  `id` int DEFAULT NULL,
  `no_plat` varchar(10) DEFAULT NULL,
  `tarikh_penyelenggaraan` date DEFAULT NULL,
  `butir_penyelenggaraan` varchar(255) DEFAULT NULL,
  `kos_penyelenggaraan` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpenyelenggaraan`
--

LOCK TABLES `tpenyelenggaraan` WRITE;
/*!40000 ALTER TABLE `tpenyelenggaraan` DISABLE KEYS */;
INSERT INTO `tpenyelenggaraan` VALUES (2,'WCM5562','2014-02-04','Eksiden Langgar taik lembu',15.60),(3,'WLD4013','2015-01-01','Kereta tidak mahu start. Gear Automatik rosak',1212.00),(5,'WCM5562','2014-11-11','brek rosak',250.00),(7,'KV4154C','2014-12-15','brek rosak',8000.00),(8,'WCJ5943','2015-02-09','overhaul',7000.00),(9,'KBY646','2015-06-22','Minyak Enjin Jenis Toyota Fully Syntetic\r\nOil filter\r\nService Charges',610.00),(10,'WMT7845','2016-10-16','',2323.00),(11,'WC 5247 D','2017-02-06','Service 40K\r\nTo Lubrication Service\r\nTo Brake Service/Bleeding B/Fluid\r\nTo Eccs Consult Diagnosis\r\nTo Wheel Allignment\r\nTo Wheel Balancing\r\nTo Renew Axle/TCF Axle Oil\r\nPart:\r\nEngine Oil Fully\r\nEngine Oil 1 Litre\r\nOil Filter\r\nOil Pan Washer\r\nWindshield Was',1021.60),(12,'KCP 646','2017-02-08','SPAREPART\r\nFull Synthetic 4L\r\nWindow Washer 30ML\r\nFull Synthetic 1L\r\nOil Filter\r\nGasket\r\n\r\nLABOUR\r\nWheel Balancing 2 Wheels (Alloy)\r\nWheel Alignment 2 Wheels\r\nMaxcheck Advance\r\nAnti Rust Inspection\r\nMAxcheck Add-On\r\n\r\n \r\n\r\n\r\n\r\n',566.17),(13,'KDN 8586','2017-02-08','SPAREPART\r\nAssy Oil Filter\r\nGasket Plug Oil\r\nAuto Trans Oil ATF-9 SPIII\r\nBrake Fluid DOT4\r\nSyntium 3000 SE 5W30 SN 4L\r\nScreen Wash\r\nSensor Front LH\r\nSensor Front RH\r\n\r\nLABOUR\r\nProcare 40,000\r\nService & Clean Blower Filter\r\nSpeed Sensor ABS Front Replaceme',698.95),(14,'WC 721 G','2017-02-23','Service 50000KM\r\n\r\nLabour cost:\r\nLube Service & Car Wash\r\nWheel Alignment\r\nWheel Balancing\r\n\r\nPart:\r\nEngine Oil\r\nOil Filter\r\nOil Pan Washer\r\nEngine Treatment\r\nInjector Cleaner\r\nWindscreen Washer',530.70),(15,'KDN 8586','2017-02-08','Penyelenggaraan cermin by Dr.Cermin',127.20),(16,'WNQ 9567','2017-02-14','MATERIAL:\r\nSYNTIUM 900 SM 5W/30-4L\r\nAUTO TRANX OIL-18L\r\nIDLER TIMING BELT\r\nLEVER ASSY TENSION\r\nTENSIONER AUTO T/B\r\nTENSIONER RR,RH\r\nTENSIONER RR,LH\r\nHOSE VENT PART\r\nHOSE VENT\r\nHOSE ASSY WATER\r\nPRESS PISTON\r\nCYC. LINER SEALANT\r\nGASKET KIT-ENGINE OVERHAUL\r\n',10749.95),(17,'WC 5246 D','2017-02-20','SERVIS 20000KM\r\n\r\nLabour:\r\nLube Service & Car Wash\r\nWheel Alignment\r\nWheel Balancing\r\nECCS Consult\r\nBrake Service\r\n\r\nParts:\r\nEngine Oil\r\nOil Filter\r\nOil Pan Washer\r\nWindscreen Washer\r\nEngine Treatment & Injector Cleaner\r\nAircond Filter\r\nEvaporator Cleaner',842.25),(18,'KEC644','2017-02-13','VALVE CLEAN\r\nFUEL INJECTOR CLEANER \r\nSNOW20 FULLY SYN 4L\r\nOIL PAN WASHER (SMALL)\r\nMOS 2 ENGINE TREATMENT \r\nENGINE FLUSH\r\nOIL FILTER \r\nAIR FILTER - FUEL INJECTION \r\nWHEEL ALIGNMENT\r\nWHEEL BALANCING\r\nBACK DOOR STAY (FOC)',578.85),(19,'KEB7401','2017-02-23','FULLY SYNTHETIC ENGINGE OIL 5L\r\nGASKET\r\nWINDOW WASHER 30 ML\r\nOIL FILTER\r\nPETROL INJECTION CLEANER\r\nAIRCOND SERVICE\r\nWHEEL BALANCING 2 WHEEL\r\nWHEEL ALIGNMENT 2 WHEEL \r\n',818.84),(20,'WC 716 G','2017-03-15','AIR FILTER COND\r\nEVAPORATOR CLEANER\r\nVALVE CLEAN\r\nFUEL INJECTOR CLEANER\r\nMOS 2 ENGINE TREATMENT\r\nENGINE FLUSH\r\nWINDSHIELD WASHER\r\nFILTER ASSY-OIL\r\nSN5W30 FULLY SYN 4L\r\nSN5W30 FULLY SYN 1L\r\nOIL PAN WASHER (SMALL)\r\nPTT SR BRAKE FLUID DOT 4 (0.5L)\r\nBACK DOOR',921.75),(21,'WNQ 9567','2017-03-29','Air Flow Sensor\r\nService Aircond Complete\r\n-Liner 6PC\r\n-Motor Fan\r\n-Thermo Housing\r\n-Belt Timing\r\n-Gasket Set\r\n-Front Mounting LH\r\nService Charge',3061.35),(22,'WC 5247 D','2017-05-04','LABOUR\r\nStandard Lube Service\r\nReplace Oil Filter\r\nChange Engine Oil\r\nReplace Oil Pan Washer\r\nWheel Alignment 4 Wheels\r\nWheel Balancing Alloy/Wheel\r\nReplace Air Filter\r\nMulti Points Inspection\r\nComplimentary Car Wash\r\nPower Engine Treatment\r\n\r\nSPAREPART\r\n',737.40),(23,'WC 721 G','2017-05-05','SERVIS 60000 KM\r\n\r\nSPAREPART\r\nAir Filter Cond\r\nEvaporator Cleaner\r\nValve Clean\r\nFuel Injector Cleaner\r\nMOS 2 Engine Treatment\r\nEngine Flush\r\nWindshield Washer\r\nFilter Assy-Oil\r\nSN5W30 Fully Syn 4L\r\nSN5W30 Fully Syn 1L\r\nOil Pan Washer (Small)\r\nPTT SR Brake',921.75),(24,'KBG 6715','2017-05-09','LABOUR\r\nCheck Internal Car Heavy Petrol Smell\r\nReplace Front Both Bearing\r\nPress Front Both Wheel Bearing\r\nReplace Knock sensor\r\nReplace Hose\r\nReplace Aircond Hose(High Pressure)\r\n\r\nSPAREPART\r\nHose Air Inlet\r\nBearing Assy FR\r\nSensor Knock\r\nAircond Hose',1875.15),(25,'KCP 646','2017-05-29','RUJUK PADA SEBUTHARGA',1582.43),(26,'KEC644','2018-04-21','Penyelenggaran Kenderaan \r\n- Fully Synthetic 4L Snow 20\r\n- Oil Filter \r\n- Oil Pan Washer \r\n- Sr Brake Fluid Dot 3 (0.5L)\r\n- Air Filter  - Fuel Injection \r\n- Air Filter Aircond\r\n- Evaporator Cleaner\r\n- Petrol Engine Refresh\r\n- Wheel Alignment\r\n- Wheel Bala',775.10),(27,'KCH778','2018-04-05','Penukaran Bateri \r\n- 55D 23L ',448.00),(28,'KEB7401','2018-04-18','-Fully Syntthetic Engine Oil \r\n- Oil Filter \r\n- Gasket \r\n- Petrol Inj. Cleaner \r\n- Petrol F/G Flush ADT\r\n- Wheel Balancing\r\n- Wheel Alignment \r\n',794.85),(29,'KEC644','2019-03-19','Penggantian Bateri NS 95 \r\nPenggantian Bateri NS 40',793.00),(30,'KCH778','2019-04-11','Engine oil \r\nOil Treatment\r\nOil Filter\r\nOil Flusing Engine\r\nDrain Plug & Washer\r\nService Throttle Body\r\nPetrol Carbon Addtive Cleaner\r\nKomputer Tuning engine\r\nRadiator Coolant\r\nFront Wheel Bearing RH/LH\r\nOil Seal Bearing RH/LH\r\nWheel Alignment tyres\r\n',200.00),(31,'KEC644','2019-04-14','Engine Oil 5/30 w fully syntectic \r\nOil treatment\r\nOil filter \r\nOil Flusing Engine \r\nPetrol Carbon Additive Cleaner \r\nRadiator Coolant\r\nAuto Trans Fluid \r\nService 4 wheel Brakes\r\nBrake Fluid DOT 4\r\nService Aircond \r\nWheel Alignment & Balancing Tyre\r\nKompu',1263.00),(NULL,'KCW 5341','2025-04-02','exiden langgar babi',2000.00),(NULL,'KCW 5312','2025-04-02','pintu lucut',500.00),(NULL,'KCW 5312','2025-05-14','dd',1000.00);
/*!40000 ALTER TABLE `tpenyelenggaraan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tptj`
--

DROP TABLE IF EXISTS `tptj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tptj` (
  `id` int NOT NULL AUTO_INCREMENT,
  `namaptj` varchar(100) DEFAULT NULL,
  `namapenyelaras` varchar(100) DEFAULT NULL,
  `namaringkas` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tptj`
--

LOCK TABLES `tptj` WRITE;
/*!40000 ALTER TABLE `tptj` DISABLE KEYS */;
INSERT INTO `tptj` VALUES (1,'BAHAGIAN PENGURUSAN',NULL,'U'),(2,'PEJABAT PENGARAH KESIHATAN NEGERI',NULL,NULL),(3,'VEKTOR NEGERI',NULL,NULL),(4,'HOSPITAL BALING',NULL,NULL),(5,'HOSPITAL JITRA',NULL,NULL),(6,'HOSPITAL KUALA NERANG',NULL,NULL),(7,'HOSPITAL KULIM',NULL,NULL),(8,'HOSPITAL LANGKAWI',NULL,NULL),(9,'HOSPITAL SIK',NULL,NULL),(10,'HOSPITAL SULTAN ABDUL HALIM',NULL,NULL),(11,'HOSPITAL SULTANAH BAHIYAH',NULL,NULL),(12,'HOSPITAL YAN',NULL,NULL),(13,'KOLEJ JURURAWAT MASYARAKAT KULIM',NULL,NULL),(14,'KOLEJ KEJURURAWATAN ALOR STAR',NULL,NULL),(15,'KOLEJ KEJURURAWATAN SUNGAI PETANI',NULL,NULL),(16,'KOLEJ KESIHATAN AWAM JITRA',NULL,NULL),(17,'KOLEJ PEMBANTU PERUBATAN ALOR STAR',NULL,NULL),(18,'BAHAGIAN FARMASI',NULL,'F'),(19,'BAHAGIAN PERGIGIAN',NULL,'G'),(20,'PEJABAT KESIHATAN PINTU MASUK BUKIT KAYU HITAM',NULL,NULL),(21,'PEJABAT PERGIGIAN BALING',NULL,NULL),(22,'PEJABAT PERGIGIAN JITRA',NULL,NULL),(23,'PEJABAT PERGIGIAN KOTA SETAR',NULL,'PPKS'),(24,'PEJABAT PERGIGIAN KULIM',NULL,NULL),(25,'PEJABAT PERGIGIAN LANGKAWI',NULL,NULL),(26,'PEJABAT PERGIGIAN SUNGAI PETANI',NULL,NULL),(27,'PEJABAT PERGIGIAN YAN',NULL,NULL),(28,'PEJABAT PERGIGIAN PADANG TERAP',NULL,NULL),(29,'PKD BALING',NULL,NULL),(30,'PKD BANDAR BAHARU',NULL,NULL),(31,'PKD KOTA SETAR',NULL,NULL),(32,'PKD KUALA MUDA',NULL,NULL),(33,'PKD KUBANG PASU',NULL,NULL),(34,'PKD KULIM',NULL,NULL),(35,'PKD LANGKAWI',NULL,NULL),(36,'PKD PADANG TERAP',NULL,NULL),(37,'PKD PENDANG',NULL,NULL),(38,'PKD SIK',NULL,NULL),(39,'PKD YAN',NULL,NULL),(40,'BAHAGIAN KESIHATAN AWAM',NULL,'KA'),(42,'BAHAGIAN PERUBATAN',NULL,'PBTN'),(43,'BAHAGIAN KESELAMATAN DAN KUALITI MAKANAN',NULL,'BKKM');
/*!40000 ALTER TABLE `tptj` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ttempah_jenis`
--

DROP TABLE IF EXISTS `ttempah_jenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ttempah_jenis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jenis_kenderaan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ttempah_jenis`
--

LOCK TABLES `ttempah_jenis` WRITE;
/*!40000 ALTER TABLE `ttempah_jenis` DISABLE KEYS */;
INSERT INTO `ttempah_jenis` VALUES (1,'Sedan'),(2,'MPV 6 Seater'),(3,'Lori'),(4,'Hino');
/*!40000 ALTER TABLE `ttempah_jenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ttempah_kenderaan`
--

DROP TABLE IF EXISTS `ttempah_kenderaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ttempah_kenderaan` (
  `id` int NOT NULL,
  `tarikh_mohon` datetime DEFAULT NULL,
  `idpemohon` varchar(10) DEFAULT NULL,
  `destinasi` varchar(200) DEFAULT NULL,
  `tahun` varchar(4) DEFAULT NULL,
  `jenis_perjalanan` int DEFAULT NULL,
  `tarikh_pergi` date DEFAULT NULL,
  `masa_pergi` time DEFAULT NULL,
  `tarikh_balik` date DEFAULT NULL,
  `masa_balik` time DEFAULT NULL,
  `idtujuan_perjalanan` int DEFAULT NULL,
  `lain_tujuan` varchar(40) DEFAULT NULL,
  `bil_penumpang` smallint DEFAULT NULL,
  `senarai_penumpang` varchar(255) DEFAULT NULL,
  `idjenis_kenderaan` int NOT NULL,
  `kelulusan` int DEFAULT NULL,
  `pegawai_pelulus` varchar(10) DEFAULT NULL,
  `tarikh_lulus` datetime DEFAULT NULL,
  `idpemandu` int DEFAULT NULL,
  `idkenderaan` varchar(15) DEFAULT NULL,
  `ulasan` text,
  `negeri` int DEFAULT NULL,
  `batal_tempahan` varchar(5) DEFAULT NULL,
  `pemohon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ttempah_kenderaan`
--

LOCK TABLES `ttempah_kenderaan` WRITE;
/*!40000 ALTER TABLE `ttempah_kenderaan` DISABLE KEYS */;
INSERT INTO `ttempah_kenderaan` VALUES (1,'2016-12-28 15:51:28','35','KK NAKA,  KK LUBUK MERBAU, KK PEDU',NULL,2,'2017-01-02','08:00:00','2017-01-02','17:00:00',2,NULL,3,'SARIFAH , YUHANIS, ARIF',2,1,'32','2016-12-28 15:53:45',4,'WC 721 G','',1,'TIDAK',''),(2,'2016-12-28 15:54:30','34','HOSPITAL KULIM',NULL,2,'2017-01-03','07:30:00','2017-01-03','13:30:00',2,NULL,3,'HJ ZAMRI BIN HJ ABDUL HAMID\r\nHJ SHAFIE BIN ABDULLAH\r\nEN SALLEH BIN YAHAYA',1,1,'32','2016-12-28 16:00:37',19,'WC 5246 D','',1,'TIDAK',''),(3,'2016-12-28 16:05:17','34','HOSPITAL SULTAN ABDUL HALIM',NULL,2,'2017-01-04','08:00:00','2017-01-04','12:30:00',2,'-',3,'HJ ZAMRI BIN HJ ABDUL HAMID\r\nHJ SHAFIE BIN ABDULLLAH\r\nMOHAMAD SALLEH BIN YAHAYA',1,1,'32','2016-12-28 16:18:11',22,'KCE 646','Ubah Pemandu sebab ada Mesyuarat Pemandu Pengurusan bersama PP & EO (Khidmat Pengurusan)',1,'TIDAK',''),(4,'2016-12-29 09:19:14','33','Hospital Sultan Abdul Halim',NULL,2,'2017-01-12','07:45:00','2017-01-12','15:30:00',5,NULL,4,'Pn. Rohida, Pn. Suriana, Pn. Sharifah salbiah, Pn. Noriah',2,1,'32','2017-01-01 08:32:55',19,'WB 1567 L','',1,'TIDAK',''),(5,'2016-12-29 09:22:47','33','PKD Kuala Muda',NULL,2,'2017-01-17','07:45:00','2017-01-17','17:00:00',1,NULL,4,'Pn. Rohida, Pn. Suriana, Pn. Sharifah salbiah, Pn. Noriah',2,1,'32','2017-01-01 08:25:43',5,'WC 5247 D','',1,'TIDAK',''),(6,'2016-12-29 09:23:52','33','PKD Kuala Muda',NULL,2,'2017-01-18','07:45:00','2017-01-18','17:00:00',1,NULL,4,'Pn. Rohida, Pn. Suriana, Pn. Sharifah salbiah, Pn. Noriah',2,1,'32','2017-01-01 08:26:50',4,'WC 721 G','',1,'TIDAK',''),(7,'2016-12-29 09:24:55','33','PKD Kuala Muda',NULL,2,'2017-01-19','07:45:00','2017-01-19','15:30:00',1,NULL,4,'Pn. Rohida, Pn. Suriana, Pn. Sharifah salbiah, Pn. Noriah',2,1,'32','2017-01-01 08:27:32',4,'WC 721 G','',1,'TIDAK',''),(8,'2017-01-01 08:57:31','42','PUTRAJAYA',NULL,2,'2017-01-17','09:00:00','2017-01-19','17:00:00',1,NULL,3,'DR SHAHIDA BT ISMAIL\r\nSITI NAZEHA BT MD SURI\r\nYUNUS BIN ISMAIL',1,1,'32','2017-01-01 09:07:50',3,'KDN 8586','PERTUKARAN PEMANDU\r\nNASIRON PASSING TRIP\r\n',1,'TIDAK',''),(9,'2017-01-01 12:51:15','48','KK Betong dan KD Jenari, SIK',NULL,2,'2017-01-02','08:30:00','2017-01-02','16:30:00',2,NULL,3,'Matron Muzlifah\r\nMatron Sopiah\r\nSister Fazliwati',1,1,'32','2017-01-01 14:01:06',20,'WXN 3346','Tempahan kurang drp tempoh 3hari',1,'TIDAK',''),(10,'2017-01-01 15:57:10','50','PEJABAT JPJ KEDAH',NULL,2,'2017-01-02','15:00:00','2017-01-02','17:00:00',1,NULL,1,'MOHD FITRI ABDUL',1,1,'32','2017-01-01 16:01:18',19,'WC 5246 D','PERTUKARAN PEMANDU\r\nMAZLAN-M.C\r\nPERTUKARAN KENDERAAN :PREVE SERVIS\r\nWB 1567 L',1,'TIDAK',''),(11,'2017-01-01 16:35:05','43','Auditorium Hospital Sultan Abdul Halim Sungai Petani',NULL,2,'2017-01-12','08:00:00','2017-01-12','15:30:00',5,NULL,2,'Dr Mohd Nazrin B Jamhari , Pn Zattul Iradah Bt Hassan',1,-1,'43','2017-01-11 12:18:14',NULL,NULL,'',1,'BATAL',''),(12,'2017-01-02 09:28:32','51','SG. PETANI',NULL,2,'2017-01-02','09:30:00','2017-01-02','17:00:00',2,NULL,2,'SHAHIDON BIN MD SAAD',1,1,'39','2017-01-02 09:31:33',25,'KEB7401','KEGUNAAN TUGAS RASMI CAWANGAN PENGUATKUASA FARMASI',1,'TIDAK',''),(13,'2017-01-02 09:36:22','52','ALOR SETAR',NULL,2,'2017-01-02','11:00:00','2017-01-02','17:00:00',2,NULL,1,'THANA ',1,1,'39','2017-01-02 09:39:01',26,'KCH778','TUGAS PENGUATKUASAAN',1,'TIDAK',''),(14,'2017-01-02 11:33:03','54','Hospital Sultanah Bahiyah ',NULL,2,'2017-01-05','08:00:00','2017-01-05','14:00:00',2,NULL,3,'Dr Zahariyah bt yaacob\r\nHj Khairol Nizam Bin Ibrahim\r\nMaheran Binti Tajuddin',1,1,'32','2017-01-02 11:45:30',6,'KCP 646','PERTUKARAN PEMANDU\r\nFIKRI REST PUTRAJAYA',1,'TIDAK',''),(15,'2017-01-02 14:58:07','43','HUSM Kubang Kerian, Kota Baharu, Kelantan.',NULL,2,'2017-01-22','08:00:00','2017-02-25','10:00:00',4,'Peperiksaan',3,'Dr. Mohd Nazrin, Dr. Mohd Syazwan, Dr. Nazrul Hafeez',1,-1,'43','2017-01-02 16:21:42',NULL,NULL,NULL,3,'BATAL',''),(16,'2017-01-02 15:20:05','56','JKN KE PULAU PINANG\r\nPULAU PINANG KE JKN',NULL,2,'2017-01-09','15:00:00','2017-01-10','17:00:00',1,NULL,3,'SITI RAHAYU BIN AHMAD RUSLI\r\nMOHD AZMIL BIN ABDULLAH\r\nABDUL HADI BIN AHMAD\r\n',2,1,'32','2017-01-02 16:12:26',4,'WC 721 G','',1,'TIDAK',''),(17,'2017-01-02 16:22:54','43','HUSM Kubang Kerian, Kota Baharu, Kelantan',NULL,2,'2017-01-22','08:00:00','2017-01-25','10:00:00',4,'Peperiksaan',3,'Dr. Mohd Nazrin, Dr. Ahmad Syazwan, Dr.Nasrul Hafeez',1,-1,'43','2017-01-22 08:53:45',NULL,NULL,'PROGRAM BELUM KOMPOM',3,'BATAL',''),(18,'2017-01-02 16:45:06','58','HOSPITAL SULTANAH BAHIYAH',NULL,2,'2017-01-03','09:00:00','2017-01-03','13:00:00',2,NULL,1,'EN. OOLI GUNALAN A/L MANICKAM',1,1,'32','2017-01-02 16:49:14',20,'WXN 3346','',1,'TIDAK',''),(19,'2017-01-03 08:59:43','56','pejabat tanah kota setar\r\npejabat tanah negeri kedah',NULL,2,'2017-01-04','08:00:00','2017-01-04','17:00:00',4,'isu-isu tanah berbangkit daerah a/s',2,'ABDUL HADI BIN AHMAD\r\nMUHAMMAD HUSNI BIN GHAZALI',2,0,'32','2017-01-03 09:28:38',NULL,NULL,'Mesyuarat PPT bersama Pemandu Pengurusan Bil1/2017\r\nUrusan selepas pukul 2 ptg shj yg boleh diluluskan',1,'TIDAK',''),(20,'2017-01-03 10:26:46','60','Damai Laut, Lumut, Perak',NULL,2,'2017-01-18','18:00:00','2017-01-21','11:00:00',5,'Seminar Pelan Tindakan 2017',5,'Barang-barang IT\r\nZayid, Nizam, Shun, Sobrina',2,-1,'60','2017-01-09 10:38:55',NULL,NULL,'',1,'BATAL',''),(21,'2017-01-03 10:33:27','60','Damai Laut Resort, Lumut, Perak',NULL,2,'2017-01-18','18:00:00','2017-01-21','11:00:00',5,'Seminar Pelan Tindakan 2017',4,'Barang IT\r\nZayid, Nizam, Shun, Sobrina',2,-1,'60','2017-01-09 10:38:41',NULL,NULL,'PERTUKARAN PEMANDU AKIBAT PERTEMBUNGAN TARIKH ZAMAN KE HUSM KUBANG KERIAN',1,'BATAL',''),(22,'2017-01-03 10:34:56','60','Damai Laut Resort, Lumut, Perak',NULL,2,'2017-01-19','10:00:00','2017-01-21','11:00:00',5,'Seminar Pelan Tindakan 2017',1,'Mr Ooli Gunalan',1,-1,'60','2017-01-09 10:38:04',NULL,NULL,'',1,'BATAL',''),(23,'2017-01-03 10:37:00','60','Damai Laut Resort, Lumut, Perak',NULL,2,'2017-01-19','10:00:00','2017-01-21','11:00:00',5,'Seminar Pelan Tindakan 2017',1,'4 orang\r\nEn. Mohd Fitri, Pn. Hayati dll.',2,-1,'60','2017-01-09 10:38:20',NULL,NULL,'',1,'BATAL',''),(24,'2017-01-03 10:41:09','60','Damai Laut Resort, Lumut, Perak',NULL,2,'2017-01-19','08:00:00','2017-01-21','11:00:00',5,'Seminar Pelan Tindakan 2017',1,'Team ICT JKN',2,-1,'60','2017-01-09 10:37:45',NULL,NULL,'',1,'BATAL',''),(25,'2017-01-03 11:02:10','60','Dokumen SULIT ke Putrajaya',NULL,2,'2017-01-19','08:00:00','2017-01-20','12:00:00',4,'Dokumen SULIT',1,'Nizam',1,-1,'60','2017-01-03 11:05:38',NULL,NULL,NULL,1,'BATAL',''),(26,'2017-01-03 11:05:48','60','KKM Putrajaya',NULL,2,'2017-01-09','08:00:00','2017-01-10','12:00:00',4,'Dokumen SULIT',1,'NIZAM',1,1,'32','2017-01-03 11:51:55',19,'WB 1567 L','',1,'TIDAK',''),(27,'2017-01-03 11:08:07','60','Damai Laut Resort, Lumut (Lawatan Tapak)',NULL,2,'2017-01-11','07:00:00','2017-01-14','15:00:00',2,'Lawatan Tapak Program',1,'En. Fitri, Shun, Pn Yati, En. Nadmin',2,-1,'60','2017-01-19 12:33:28',NULL,NULL,'TUKAR KERETA SBB X-TRAIL WC 5247 D LIMIT SERVIS',1,'BATAL',''),(28,'2017-01-03 11:10:46','60','Damai Laut Resort Lumut (Lawatan Tapak)',NULL,2,'2017-01-11','07:00:00','2017-01-14','15:00:00',2,'Lawatan Tapak Program',6,'Team ICT dll',2,-1,'60','2017-01-19 12:32:47',NULL,NULL,'',1,'BATAL',''),(29,'2017-01-03 12:04:53','62','PEKAN RABU',NULL,2,'2017-01-03','12:25:00','2017-01-03','14:30:00',4,'BELI HADIAH PERTUKARAN DAN BERSARA',3,'NAIMAH ABD MAJID\r\nNUR SYAZWANI BT SHAHBANY\r\nNORISAH BT HAMID',1,1,'32','2017-01-03 12:11:05',3,'KCE 646','',1,'TIDAK',''),(30,'2017-01-03 12:37:58','35','KLINIK KESIHATAN BAKAR ARANG ',NULL,2,'2017-01-05','08:00:00','2017-01-05','15:30:00',1,NULL,2,'SARIFAH\r\nAZMIL',2,1,'32','2017-01-03 12:42:20',4,'WC 721 G','',1,'TIDAK',''),(31,'2017-01-03 12:41:58','63','PEJABAT KESIHATAN DAERAH KUALA MUDA, SUNGAI PETANI',NULL,2,'2017-01-05','09:00:00','2017-01-05','14:30:00',2,NULL,1,'MOHD SOFIAN BIN SAAD',1,1,'32','2017-01-03 12:45:05',19,'KCE 646','NASIRON GANTI PEMANDU PENGARAH ABD HAMID',1,'TIDAK',''),(32,'2017-01-03 12:45:55','32','BAHAGIAN kHIDMAT pENGURUSAN\r\nARAS 7 & 8 BLOK E7 KOMPLEKS E',NULL,2,'2017-01-05','17:00:00','2017-01-06','17:00:00',4,'AMBIL SAMPEL KAIN KONTRAK PAKAIAN SRAGAM',1,'AZHAR & KOTAK',1,1,'32','2017-01-04 15:30:41',20,'KCE 646','',16,'TIDAK',''),(33,'2017-01-03 12:57:26','56','pejabat tanah kota setar\r\nkd derang\r\npejabat tanah negeri\r\nkangkong\r\ntebengau',NULL,2,'2017-01-05','08:00:00','2017-01-05','15:30:00',2,NULL,2,'abdul hadi bin ahmad\r\nmuhammad husni bin ghazali',2,1,'32','2017-01-03 15:00:30',3,'WC 5246 D','PERTUKARAN PEMANDU\r\nFIKRI REST PUTRAJAYA',1,'TIDAK',''),(34,'2017-01-04 06:57:04','54','Klinik Kesihatan Kota Kuala Muda,PKD Kuala Muda Sungai Petani',NULL,2,'2017-01-08','08:00:00','2017-01-08','17:00:00',2,NULL,5,'Dr zahariyah\r\nHj Khairol Nizam Bin Ibrahim\r\nMaheran Binti Tajuddin\r\nNurazliana Binti Che Lat\r\nRohaya Karim',2,1,'32','2017-01-04 08:20:04',6,'KCP 646','TIADA KENDERAAN MPV BUAT SEMENTARA WAKTU',1,'TIDAK',''),(35,'2017-01-04 07:00:02','54','Hospital Gombak,Selangor',NULL,2,'2017-02-13','08:00:00','2017-02-14','17:00:00',4,'Sambutan Hari Kusta Sedunia Peringkat ',5,'Dr Zahariyah\r\nHj Khairol Nizam Bin Ibrahim\r\nMaheran Binti tajuddin\r\nNurazliana Binti Che Lat\r\nRohaya karim',2,-1,'54','2017-02-08 07:17:04',NULL,NULL,'',1,'BATAL',''),(36,'2017-01-04 07:03:15','54','Hotel Zenith,Kuantan Pahang',NULL,2,'2017-03-10','08:00:00','2017-03-11','17:00:00',4,'Sambutan Hari PPP Peringkat kebangsaan ',3,'Haji Khairol Nizam Bin Ibrahim\r\nMuhammad Bin Salleh\r\nKhalihuddin Bin Ahmad',1,-1,'32','2017-02-28 15:31:43',NULL,NULL,'PERCAKAPAN DGN HJ KHAIRUL CDC.\r\nAKAUN XLEH AKSES,MOHON AKAUN DIBATALKAN',6,'TIDAK',''),(37,'2017-01-04 10:11:09','66','bank',NULL,2,'2017-01-04','14:30:00','2017-01-04','15:30:00',3,NULL,1,'1',1,1,'32','2017-01-04 10:17:01',20,'WC 5246 D','',1,'TIDAK',''),(38,'2017-01-04 12:49:26','35','KLINIK PERGIGIAN ALOR JANGGUS ',NULL,2,'2017-01-08','08:00:00','2017-01-08','15:00:00',2,NULL,1,'SARIFAH\r\nSOTHEESWARAN',2,1,'32','2017-01-04 13:11:10',4,'WC 721 G','',1,'TIDAK',''),(39,'2017-01-04 14:04:27','32','LIMA 2017',NULL,2,'2017-03-16','08:00:00','2017-03-26','17:00:00',4,'PAMERAN',1,'PERUBATAN',1,-1,'32','2017-02-21 11:47:27',NULL,NULL,'',1,'TIDAK',''),(40,'2017-01-04 14:07:22','83','jeti kuala kedah',NULL,1,'2017-01-04','02:30:00','2017-01-05','02:00:00',4,'sebutan kes mahkamah langkawi',1,'farhanis',1,1,'39','2017-01-04 14:11:12',25,'KEB7401','URUSAN RASMI',1,'TIDAK',''),(41,'2017-01-04 14:07:16','32','LIMA 2017',NULL,2,'2017-03-16','08:00:00','2017-03-26','17:00:00',4,'PAMERAN',1,'PERUBATAN',2,-1,'32','2017-02-21 11:47:57',NULL,NULL,'',1,'TIDAK',''),(42,'2017-01-04 14:09:25','32','LIMA 2017',NULL,2,'2017-03-16','08:00:00','2017-03-26','17:00:00',4,'PAMERAN',1,'PERUBATAN',2,-1,'32','2017-02-21 11:48:13',NULL,NULL,'GANTI KENDERAAN DGN SERENA KBG6715',1,'TIDAK',''),(43,'2017-01-04 14:10:56','32','LIMA 2017',NULL,2,'2017-03-16','08:00:00','2017-03-26','17:00:00',4,'PAMERAN',1,'PERUBATAN',2,-1,'32','2017-02-21 11:47:08',NULL,NULL,'',1,'TIDAK',''),(44,'2017-01-04 14:11:57','32','LIMA 2017',NULL,2,'2017-03-16','08:00:00','2017-03-26','17:00:00',4,'PAMERAN',1,'PERUBATAN',2,-1,'32','2017-02-21 11:47:45',NULL,NULL,'',1,'TIDAK',''),(45,'2017-01-04 14:12:57','32','LIMA 2017',NULL,2,'2017-03-16','08:00:00','2017-03-26','17:00:00',4,'PAMERAN',1,'PERUBATAN',2,-1,'32','2017-01-04 14:15:03',NULL,NULL,'',1,'TIDAK',''),(46,'2017-01-04 16:09:49','39','STOR EKSHIBIT JALAN SULTANAH',NULL,2,'2017-01-05','08:30:00','2017-01-05','12:00:00',4,'AMBIL BARANG ',2,'MOHD KHIRI , SHAHIDON ',2,1,'39','2017-01-04 16:12:17',26,'KEC644','URUSAN RASMI JABATAN ',1,'TIDAK',''),(47,'2017-01-05 08:39:12','85','KEMENTERIAN KESIHATAN MALAYSIA',NULL,2,'2017-01-19','10:00:00','2017-01-19','17:00:00',1,NULL,3,'HAJI MOHD NASIR BIN SAID\r\nPUAN HASIMAH BINTI HUSSIN\r\nEN ABD MOHD FAZURI BIN IBRAHIM',1,-1,'85','2017-01-05 08:43:49',NULL,NULL,NULL,1,'BATAL',''),(48,'2017-01-05 08:44:01','85','KEMENTERIAN KESIHATAN MALAYSIA',NULL,2,'2017-01-18','10:00:00','2017-01-19','17:00:00',1,NULL,3,'HAJI MOHD NASIR BIN SAID\r\nPUAN HASIMAH BINTI HUSSIN\r\nEN ABD MOHD FAZURI BIN IBRAHIM',1,1,'32','2017-01-05 10:24:38',20,'WC 5246 D','PERTUKARAN PEMANDU',1,'TIDAK',''),(49,'2017-01-05 11:27:10','87','Mahkamah Kulim dan kawasan sekitar',NULL,2,'2017-01-09','08:00:00','2017-01-09','16:00:00',4,'Daftar kes dan pemeriksaan premis',2,'Amir Hariz Bin Ghazali, Mohd Fazli Bin Kamaruzaman',1,1,'39','2017-01-05 12:16:19',26,'KEB7401','URUSAN RASMI JABATAN',1,'TIDAK',''),(50,'2017-01-05 11:41:28','43','PKD BALING',NULL,2,'2017-01-08','08:00:00','2017-01-17','17:00:00',2,NULL,4,'DR. ROSHIDI ( KKM )  DR. SHARIFAH SAFFINAS PPKPK MOHD FITRIE & PPKP ZATUL IRADAH',2,-1,'43','2017-01-05 12:00:45',NULL,NULL,NULL,1,'BATAL',''),(51,'2017-01-05 11:46:41','89','Mahkamah Gurun ',NULL,2,'2017-01-08','08:00:00','2017-01-08','17:00:00',4,'Mahkamah',6,'Kalaiarsan, Yip, Nurul Ashikin, Fazli Azmi, Sareh, Rashid',2,1,'39','2017-01-05 12:15:34',26,'KEC644','URUSAN RASMI JABATAN ',1,'TIDAK',''),(52,'2017-01-05 12:00:50','43','PKD BALING',NULL,2,'2017-01-08','08:00:00','2017-01-08','17:00:00',2,NULL,4,'DR. ROSHIDI ( KKM ) DR. SHARIFAH SAFFINAS, PPKPK FITRIE & PPKP ZATUL IRADAH',2,1,'32','2017-01-05 12:13:48',3,'KCE 646','',1,'TIDAK',''),(53,'2017-01-05 12:22:57','90','PULAU LANGKAWI',NULL,2,'2017-02-26','08:00:00','2017-03-05','14:20:00',4,'SIREX LANGKAWI',4,'M. HAIRIL\r\nDR NORIDA\r\nDR ZAIDAH\r\nDR FIKRIAH',2,-1,'90','2017-02-16 10:01:05',NULL,NULL,'',1,'BATAL',''),(54,'2017-01-05 12:24:43','90','PULAU LANGKAWI',NULL,2,'2017-03-16','08:00:00','2017-03-26','15:20:00',4,'LIMA\'17 LANGKAWI',6,'M. HAIRIL\r\nDR NORIDA\r\nDR FIKRIAH\r\nDR ZAIDAH',1,-1,'90','2017-01-05 12:43:16',NULL,NULL,NULL,1,'BATAL',''),(55,'2017-01-05 13:34:19','56','PKD KUALA NERANG\r\nPEJABAT TANAH PDG TERAP\r\nKK NAKA',NULL,2,'2017-01-08','08:00:00','2017-01-08','13:00:00',2,NULL,2,'ABDUL HADI BIN AHMAD\r\nMUHAMMAD HUSNI BIN GHAZALI',2,1,'32','2017-01-05 13:47:33',20,'WC 5246 D','',1,'TIDAK',''),(56,'2017-01-05 13:35:19','32','CUTI REHAT',NULL,2,'2017-02-01','08:00:00','2017-02-02','17:00:00',4,'CUTI REHAT',1,'CUTI REHAT',1,1,'32','2017-01-05 13:49:08',3,'KDN 8586','CUTI REHAT',1,'TIDAK',''),(57,'2017-01-05 13:37:35','32','CUTI REHAT',NULL,2,'2017-03-26','08:00:00','2017-03-27','17:00:00',4,'CUTI REHAT',1,'CUTI REHAT',1,1,'32','2017-01-05 13:48:41',3,'KDN 8586','CUTI REHAT',1,'TIDAK',''),(58,'2017-01-05 14:47:09','90','HSB KE GRAND ALORA, \r\nGRAND ALORA KE DARULAMAN GOFT AND COUNTRY RESORT,\r\nGRAND ALORA KE TAMAN JUBLI EMAS\r\n',NULL,2,'2017-03-07','08:00:00','2017-03-12','17:00:00',4,'FAST CAR UNTUK PTIWRBNM',2,'PAKAR JABATAN KECEMASAN DAN TRAUMA',1,-1,'90','2017-01-05 15:04:57',NULL,NULL,NULL,1,'BATAL',''),(59,'2017-01-05 15:05:06','90','menggunakan kenderaan sebagai fast car untuk Kegunaan Pakar Kecemasan semasa lawatan PM ke Negeri Kedah',NULL,2,'2017-02-07','08:00:00','2017-02-12','08:00:00',4,'FAST CAR UNTUK PTIWRBNM',1,'pakar jabatan kecemsan dan trauma, HSB',1,-1,'90','2017-02-06 15:24:27',NULL,NULL,'',1,'BATAL',''),(60,'2017-01-05 15:10:04','92','HOSPITAL KULIM',NULL,2,'2017-01-23','08:00:00','2017-01-23','14:00:00',2,NULL,6,'TPKN (P), DR. NORIDA, DR. AMER, DR. ZAIDAH, DR. NURAH, DR. FIKRIAH',2,-1,'92','2017-01-08 14:29:43',NULL,NULL,'TIADA KENDERAAN.PROGRAM BERTEMBUNG DGN MESY PELAN TINDAKAN PENGURUSAN DI LUMUT',1,'BATAL',''),(61,'2017-01-05 15:13:25','92','HOSPITAL KULIM',NULL,2,'2017-01-23','08:00:00','2017-01-23','14:00:00',2,NULL,6,'MTR. HAJIJAH, MTR. HASHIMI, SR.SHARIFAH, HJ,ZAMRI, HJ. SHAFIE, SR.ASMA',2,-1,'92','2017-01-08 14:30:52',NULL,NULL,'PINJAM INNOVA UKAPS.TELAH INFO PENYELARAS EN.NASRUL & JAI.GANTI DGN PREVE WB 1567 L.PROGRAM BERTEMBUNG DGN MESY PELAN TINDAKAN PENGURUSAN DI LUMUT',1,'BATAL',''),(62,'2017-01-05 15:16:08','92','HOSPITAL SULTAN ABDUL HALIM',NULL,2,'2017-02-05','08:00:00','2017-02-05','16:00:00',5,NULL,4,'GANTI PROGRAM KEJURUTERAAN HOS.(EN.SHAUKI)\r\nDR.NURAH, DR. FIKRIAH, MTR. HAJIJAH, SR.SHARIFAH',1,1,'32','2017-01-05 15:26:24',4,'WC 721 G','GANTI PROGRAM DGN KEJURUTERAAN HOSPITAL.GRAND LIVINA DAN PEMANDU',1,'TIDAK',''),(63,'2017-01-05 15:20:39','92','HOSPITAL SULTAN ABDUL HALIM',NULL,2,'2017-02-06','08:00:00','2017-02-06','16:00:00',5,NULL,4,'GANTI PROGRAM DGN KJURUTERAAN HOS.(EN SHAUKI)\r\nDR.NURAH, DR.FIKRIAH, MTR.HAJIJAH, SR.SHARIFAH',1,1,'32','2017-01-08 14:51:26',5,'KCE 646','GANTI PROGRAM KJURUTERAAN HOS.GRAND LIVINA DAN PEMANDU',1,'TIDAK',''),(64,'2017-01-08 09:42:43','93','Mengambil pelawat dari Airport dan lawatan ke Klinik Kesihatan Bakar Arang dan klinik yang lain.',NULL,2,'2017-01-25','07:30:00','2017-01-26','17:00:00',2,NULL,4,'1- Dr Majdah Mohamad\r\n2-Dr Norizan bt ahmad\r\n3-Matron Suzana\r\n4-Matron Dara A/P Pan',2,1,'59','2017-01-16 10:38:22',27,'KCH 4777','',1,'TIDAK',''),(65,'2017-01-08 11:29:53','96','Jabatan Akauntan Negara Cawangan Negeri Kedah\r\nWisma Persekutuan Anak Bukit',NULL,2,'2017-01-08','11:20:00','2017-01-08','13:00:00',4,'Hantar Dokumen',1,'Roshidi Khalid',1,1,'32','2017-01-08 11:35:07',19,'WB 1567 L','',1,'TIDAK',''),(66,'2017-01-08 11:34:19','97','kkm putrajaya',NULL,2,'2017-01-10','08:00:00','2017-01-11','11:40:00',4,'hantar dokumen',1,'1',2,1,'32','2017-01-08 11:46:17',5,'WC 5247 D','',16,'TIDAK',''),(67,'2017-01-08 11:50:39','67','Dari JKN Kedah ke Lapangan Terbang Antarabangsa Pulau PInang',NULL,1,'2017-01-24','14:00:00','2017-01-24',NULL,4,'Mengunjungi Wilayah Asal',1,'Pn. Lau Sie Ngo',1,-1,'67','2017-01-08 11:59:19',NULL,NULL,NULL,10,'BATAL',''),(68,'2017-01-08 11:54:51','67','Lapangan Terbang Antarabangsa Pulau Pinang ke JKN Kedah',NULL,1,'2017-02-06','12:00:00','2017-02-06',NULL,4,'Balik dari mengunjungi Wilayah asal',1,'Pn. Lau Sie Ngo',1,-1,'67','2017-01-08 11:59:40',NULL,NULL,NULL,1,'BATAL',''),(69,'2017-01-08 11:59:44','67','Lapangan Terbang Antarabangsa Pulau Pinang',NULL,1,'2017-01-24','14:00:00','2017-02-06',NULL,4,'Mengunjungi Wilayah Asal',1,'Pn. Lau Sie Ngo',1,-1,'67','2017-01-08 12:27:10',NULL,NULL,'TIADA PEMANDU.GUNA PEMANDU RM5',10,'BATAL',''),(70,'2017-01-08 12:02:52','67','Lapangan Terbang Antarabangsa Pulau Pinang Ke JKN Kedah',NULL,1,'2017-02-06','12:00:00','2017-02-06',NULL,4,'Balik dari mengunjungi Wilayah asal',1,'Pn. Lau Sie Ngo',1,-1,'67','2017-01-08 12:27:20',NULL,NULL,NULL,10,'BATAL',''),(71,'2017-01-08 12:20:44','32','TESTING',NULL,1,'2017-01-10','08:00:00','2017-01-10',NULL,4,'TESTING',1,'TESTING',1,-1,'32','2017-01-08 12:48:59',NULL,NULL,'',1,'TIDAK',''),(72,'2017-01-08 12:27:24','67','Airport Penang',NULL,1,'2017-01-24','14:00:00','2017-01-24',NULL,4,'Mengunjungi Wilayah Asal',1,'Pn. Lau Sie Ngo',1,-1,'67','2017-01-10 09:16:39',NULL,NULL,'GUNA PEMANDU RM5 KERANA TIADA PEMANDU',10,'BATAL',''),(73,'2017-01-08 12:27:37','99','KEMENTERIAN PERTAHANAN, KUALA LUMPUR',NULL,2,'2017-01-10','02:00:00','2017-01-11','06:00:00',1,NULL,2,'DR. AZLINA BT MUHAMAD RADZI\r\nDR. NORIDA BT MD HANAFIAH',1,-1,'99','2017-01-08 12:45:23',NULL,NULL,'',17,'BATAL',''),(74,'2017-01-08 12:28:55','67','Airport Penang ke JKN Kedah',NULL,1,'2017-02-06','00:00:00','2017-02-06','12:00:00',4,'Balik dari mengunjungi Wilayah asal',1,'Pn. Lau Sie Ngo',1,1,'32','2017-01-08 12:34:32',20,'WXN 3346','',10,'TIDAK',''),(75,'2017-01-08 12:28:01','32','TESTING',NULL,1,'2017-01-11','08:00:00','2017-01-11','12:00:00',4,'TESTING',1,'TESTING',1,-1,'32','2017-01-08 12:48:33',NULL,NULL,'',1,'TIDAK',''),(76,'2017-01-08 12:45:29','99','KEMENTERIAN PERTAHANAN, KUALA LUMPUR',NULL,2,'2017-01-10','14:00:00','2017-01-11','18:00:00',1,NULL,2,'DR. AZLINA BT MUHAMAD RADZI\r\nDR. NORIDA BT MD HANAFIAH',1,-1,'99','2017-01-08 12:53:56',NULL,NULL,'',17,'BATAL',''),(77,'2017-01-08 12:54:00','99','KEMENTERIAN PERTAHANAN, KUALA LUMPUR',NULL,2,'2017-01-11','07:00:00','2017-01-11','18:00:00',1,NULL,2,'DR. AZLINA BT MUHAMAD RADZI\r\nDR. NORIDA BT MD HANAFIAH',1,-1,'99','2017-01-09 09:40:36',NULL,NULL,NULL,1,'BATAL',''),(78,'2017-01-08 12:57:26','32','CUTI SAKIT GOUT',NULL,1,'2017-01-08','08:00:00','2017-01-09','17:00:00',4,'CUTI SAKIT GOUT',1,'NASIRON BIN SHUIB',2,1,'32','2017-01-08 13:00:39',5,'WC 5247 D','CUTI SAKIT GOUT',1,'TIDAK',''),(79,'2017-01-08 14:31:10','92','HOSPITAL KULIM',NULL,2,'2017-01-16','08:00:00','2017-01-16','14:00:00',2,NULL,6,'TPKN(P), DR. NORIDA, DR. ZAIDAH, DR. AMER, DR. NURAH, DR. FIKRIAH',2,-1,'92','2017-01-11 11:29:52',NULL,NULL,'',1,'BATAL',''),(80,'2017-01-08 14:33:27','92','HOSPITAL KULIM',NULL,2,'2017-01-16','08:00:00','2017-01-16','14:00:00',2,NULL,6,'MTR. HAJIJAH, MTR. LAU, MTR HASHIMI, SR. SHARIFAH, HJ. ZAMRI, HJ.SHAFIE',2,-1,'92','2017-01-11 11:29:30',NULL,NULL,'',1,'BATAL',''),(81,'2017-01-08 15:28:53','32','KURSUS DIUNIT KAUNSELING',NULL,1,'2017-01-11','08:00:00','2017-01-11','17:00:00',5,'KURSUS',1,'KURSUS',1,1,'32','2017-01-08 15:30:17',4,'WC 721 G','KURSUS',1,'TIDAK',''),(82,'2017-01-08 15:41:58','32','CUTI REHAT',NULL,1,'2017-01-31','08:00:00','2017-02-02','17:00:00',4,'CUTI REHAT',1,'CUTI REHAT',1,1,'32','2017-01-08 15:43:35',4,'WC 721 G','CUTI REHAT',1,'TIDAK',''),(83,'2017-01-08 15:55:58','32','REST',NULL,1,'2017-01-10','08:00:00','2017-01-10','17:00:00',4,'REST',1,'REST N STANDBY PETANG',1,1,'32','2017-01-08 15:57:24',20,'WXN 3346','REST',1,'TIDAK',''),(84,'2017-01-08 16:50:46','100','KK Merbok',NULL,2,'2017-01-10','09:00:00','2017-01-10','03:45:00',2,'Pemantauan 1GOVNET',2,'UMMI MASTURAH BINTI MOHD MOKHTAR\r\nMOHD SHAFAROL WAHID BIN MAT SAAD',1,-1,'100','2017-01-09 08:24:16',NULL,NULL,NULL,1,'BATAL',''),(85,'2017-01-09 08:11:43','101','TH Hotel, Penang',NULL,2,'2017-02-06','14:00:00','2017-02-08','14:00:00',1,NULL,2,'1. Cik Raudhoh Binti Saari\r\n2. Cik Amirah Binti Ahmad Razif',1,1,'39','2017-01-09 16:57:30',26,'KEB7401','URUSAN RASMI JABATAN',1,'TIDAK',''),(86,'2017-01-09 08:24:29','100','KK Merbok',NULL,2,'2017-01-10','09:00:00','2017-01-10','15:45:00',2,'Pemantauan 1GOVNET',2,'UMMI MASTURAH BINTI MOHD MOKHTAR\r\nMOHD SHAFAROL WAHID BIN MAT SAAD',1,1,'32','2017-01-09 09:08:46',3,'KCE 646','PERTUKARAN DGN MAZLAN',1,'TIDAK',''),(87,'2017-01-09 09:14:38','32','KK HUTAN KAMPONG',NULL,2,'2017-01-09','09:30:00','2017-01-09','12:00:00',4,'BARANG PEMBANGUNAN',1,'CC HADI\r\nCC AZMIL',1,1,'32','2017-01-09 09:17:04',20,'WC 721 G','REDZUAN MENGUNJUNG',1,'TIDAK',''),(88,'2017-01-09 09:11:09','49','Lapangan Terbang Antarabangsa Pulau Pinang',NULL,1,'2017-01-16','02:00:00','2017-01-16',NULL,1,'Masyuarat',1,'Dr Azlina Binti Azlan (017-9460008)',1,-1,'49','2017-01-09 09:21:36',NULL,NULL,NULL,10,'BATAL',''),(89,'2017-01-09 09:21:56','49','Lapangan Terbang Antarabangsa Pulau Pinang ( dari rumah ke Lapangan Terbang Antarabangsa Pulau Pinang)',NULL,1,'2017-01-16','02:00:00','2017-01-16',NULL,1,'Masyuarat',1,'Dr Azlina Binti Azlan (017-9460008)',1,1,'32','2017-01-09 09:30:26',20,'WXN 3346','',10,'TIDAK',''),(90,'2017-01-09 09:40:41','99','AIRPORT PENANG',NULL,2,'2017-01-11','08:00:00','2017-01-11','22:50:00',1,NULL,2,'DR. AZLINA BT MUHAMAD RADZI\r\nDR. NORIDA BT MD HANAFIAH',1,-1,'99','2017-01-09 14:31:57',NULL,NULL,'',10,'BATAL',''),(91,'2017-01-09 09:57:49','43','Hospital Ulu  Kinta Ipoh',NULL,2,'2017-01-23','08:00:00','2017-01-25','14:00:00',1,NULL,2,'Dr Sharifah , En . Mohd Fitrie',1,-1,'43','2017-01-22 16:27:39',NULL,NULL,'',7,'BATAL',''),(92,'2017-01-09 10:15:36','54','Hospital sultanah bahiyah dan Klinik Kesihatan Pokok Sena',NULL,2,'2017-01-09','08:00:00','2017-01-10','13:00:00',4,'siasatan kes kusta(URGENT)',2,'Hj khairol nizam\r\nmaheran tajuddin',1,-1,'54','2017-01-09 10:23:36',NULL,NULL,NULL,1,'BATAL',''),(93,'2017-01-09 10:23:42','54','Hospital Sultanah Bahiyah dan KK Pokok Sena',NULL,2,'2017-01-10','09:00:00','2017-01-10','13:00:00',4,'siasatan kes kusta(URGENT)',2,'Haji khairol Nizam\r\nMaheran tajuddin',1,-1,'54','2017-01-09 16:44:53',NULL,NULL,'',1,'BATAL',''),(94,'2017-01-09 10:39:18','60','Swiss Garden Damai Laut, Lumut, Perak (Pelan Tindakan)',NULL,2,'2017-02-16','10:00:00','2017-02-18','11:00:00',1,NULL,1,'Dr.Zaida\r\nDr.Norida\r\nDr.Amer\r\nAMBIL DR AZLINA DI KTM IPOH 8MLM 16HB',1,1,'32','2017-01-09 11:02:03',5,'WC 5247 D','',1,'TIDAK',''),(95,'2017-01-09 10:41:13','60','Swiss Garden Damai Laut, Lumut (Pelan Tindakan)',NULL,2,'2017-02-16','09:00:00','2017-02-18','11:00:00',1,NULL,4,'En. Fitri, Pn Hayati dll.',2,1,'32','2017-01-09 11:04:40',4,'WC 721 G','',1,'TIDAK',''),(96,'2017-01-09 10:42:48','60','Swiss Garden Damai Laut, Lumut, Perak (Pelan Tindakan)',NULL,2,'2017-02-16','10:00:00','2017-02-18','11:00:00',1,NULL,4,'Team ICT (Pn Rozita dll)',2,1,'32','2017-01-09 11:03:55',3,'KDN 8586','',1,'TIDAK',''),(97,'2017-01-09 10:44:33','60','Swiss Garden Damai Laut, Lumut, Perak.',NULL,2,'2017-02-15','17:00:00','2017-02-18','11:00:00',1,NULL,4,'HANTAR & BALIK COVER PROGRAM PEMBGNN KEMENTERIAN PD 16HB\r\nTeam Teknikal (Peralatan Audio dll)',2,-1,NULL,NULL,NULL,NULL,'PERCAKAPAN DGN CC SHUN',1,'BATAL',''),(98,'2017-01-09 10:46:26','60','Swiss Garden Damai Laut, Lumut, Perak.',NULL,2,'2017-02-15','17:00:00','2017-02-18','11:00:00',1,NULL,4,'GUNA GERMBUSTER FOOD\r\nZayid, Nizam (Peralatan) Shun, Sobrina dll',2,1,'32','2017-01-09 11:00:32',19,'KCE 646','',1,'TIDAK',''),(99,'2017-01-09 10:58:00','102','WISNA NEGERI ',NULL,2,'2017-01-11','09:00:00','2017-01-11','12:00:00',1,NULL,2,'1. DR NORIZAN AHMAD\r\n2. DR AZILAH ABDULLAH',1,1,'59','2017-01-16 10:36:27',27,'KCH 4777','',1,'TIDAK',''),(100,'2017-01-09 15:50:36','90','SEKITAR ALOR SETAR UNTUK DIGUNAKAN SEBAGAI FAST CAR SEMPENA LAWATAN PERDANA MENTERI KE KEDAH, LOKASI BELUM DITETAPKAN KERANA ESOK BARU ADA MEETING DI WISMA DARUL AMAN',NULL,2,'2017-01-16','08:00:00','2017-01-17','17:00:00',1,NULL,2,'PAKAR KECEMASAN',1,-1,'90','2017-01-09 15:53:11',NULL,NULL,NULL,1,'BATAL',''),(101,'2017-01-09 15:53:16','90','	SEKITAR ALOR SETAR UNTUK DIGUNAKAN SEBAGAI FAST CAR SEMPENA LAWATAN PERDANA MENTERI KE KEDAH, LOKASI BELUM DITETAPKAN KERANA ESOK BARU ADA MEETING DI WISMA DARUL AMAN',NULL,2,'2017-01-16','08:00:00','2017-01-17','17:00:00',4,'FAST CAR UNTUK PM',1,'PAKAR KECEMASAN',1,1,'32','2017-01-09 16:11:36',19,'WC 5246 D','',1,'TIDAK',''),(102,'2017-01-09 15:54:45','90','MELAWAT PETUGAS DI \r\nSTADIUM SULTAN ABDUL HALIM, JALAN SUKA MENANTI\r\nTH HOTEL\r\nGRAND ALORA HOTEL\r\n',NULL,2,'2017-02-09','13:00:00','2017-02-09','17:00:00',2,NULL,3,'PPP HAIRIL\r\nDR FIKRIAH\r\nHJ SHAFIE ABDULLAH',1,-1,'32','2017-02-07 11:22:38',NULL,NULL,'BATAL ATAS PERCAKAPAN DGN EN HAIRIL.CUTI PERISTIWA',1,'TIDAK',''),(103,'2017-01-09 16:44:57','54','Makmal Kebangsaan Sungai Buloh Selangor',NULL,2,'2017-01-10','09:00:00','2017-01-11','12:00:00',4,'hantar sample Biopsy Kusta',1,'Haji Khairol Nizam Bin Ibrahim',1,1,'32','2017-01-09 16:56:04',6,'KCP 646','PERTUKARAN DGN ZAMAN',1,'TIDAK',''),(104,'2017-01-10 08:13:46','66','bank',NULL,2,'2017-01-10','14:30:00','2017-01-10','15:30:00',3,NULL,1,'NOR HAFIZA@ZAITUN SHAARI',1,1,'32','2017-01-10 08:45:50',20,'WXN 3346','',1,'TIDAK',''),(105,'2017-01-10 08:16:19','70','MAHKAMAH ALOR SETAR',NULL,2,'2017-01-10','08:30:00','2017-01-10','13:00:00',4,'KES BICARA',2,'KALAIARSAN, SHAHIDON',1,1,'39','2017-01-10 08:18:05',25,'KCH778','URUSAN RASMI ',1,'TIDAK',''),(106,'2017-01-10 09:16:42','67','Airport Penang',NULL,1,'2017-01-25','04:30:00','2017-01-25',NULL,4,'Mengunjungi Wilayah Asal',1,'Puan Lau Sie Ngo',1,-1,'67','2017-01-22 09:25:29',NULL,NULL,'',1,'BATAL',''),(107,'2017-01-10 11:48:38','78','KULIM HI-TECH , PULAU PINANG',NULL,2,'2017-01-18','08:00:00','2017-01-18','17:00:00',2,NULL,2,'SAREH SAFWAN BIN ABU SEMAN ,  AIMI BT ISHAK',1,1,'39','2017-01-10 11:51:33',25,'KEB7401','URUSAN RASMI JABATAN ',1,'TIDAK',''),(108,'2017-01-10 15:05:06','103','KULIM',NULL,2,'2017-02-16','08:00:00','2017-02-16','16:00:00',4,'PERBICARAAN DI MAHKAMAH KULIM',4,'1. DR NORHASMALIZA BT. MUHAMAD NOOR\r\n2. EN . WAN RASHDAN BIN WAN OMAR\r\n3. NASIRON BIN YUSOF\r\n4. ANGGOTA POLIS',2,1,'32','2017-01-24 15:30:02',6,'KCP 646','',1,'TIDAK',''),(109,'2017-01-10 15:43:11','79','Pendang',NULL,2,'2017-01-11','11:00:00','2017-01-11','03:00:00',4,'Tugas Khas',4,'Fazli, Amir, Syazwan, Farhanis',1,1,'39','2017-01-10 15:46:57',26,'KEB7401','URUSAN RASMI',1,'TIDAK',''),(110,'2017-01-10 17:07:28','58','PEJABAT KESIHATAN DAERAH YAN',NULL,2,'2017-01-17','08:30:00','2017-01-17','01:00:00',1,NULL,1,'ENCIK OOLI GUNALAN A/L MANICKAM',1,1,'32','2017-01-11 08:16:07',20,'WXN 3346','GANTI DGN REDZUAN SBB PN SITI PEMBANGUNAN MOHON REDZUAN SBG PEMANDU KE KL\r\n',1,'TIDAK',''),(111,'2017-01-10 17:51:03','70','MAHKAMAH JITRA ',NULL,2,'2017-01-11','08:00:00','2017-01-11','13:00:00',4,'SEBUTAN KES MAHKAMAH',2,'KALAIARSAN , SHAHIDON',2,1,'39','2017-01-10 17:55:31',25,'KEC644','BAWA BARANG KES UNTUK SEBUTAN KES MAHKAMAH',1,'TIDAK',''),(112,'2017-01-11 08:21:30','49','Klinik Kesihatan Bakar Arang & PKD Kulim\r\n(Bertolak dari JKN pukul 9 pagi)',NULL,2,'2017-01-11','09:00:00','2017-01-11','15:00:00',1,NULL,2,'Dr Azlina Azlan\r\nDr Farhuda Zulaikha',1,1,'32','2017-01-11 08:36:53',20,'WC 5246 D','PREVE MASALAH BREK',1,'TIDAK',''),(113,'2017-01-11 10:41:51','48','KK Alor Janggus',NULL,2,'2017-01-11','14:30:00','2017-01-11','16:30:00',2,NULL,2,'mt Muzlifah dan mt Sopiah',1,1,'32','2017-01-11 11:06:27',3,'KCE 646','',1,'TIDAK',''),(114,'2017-01-11 11:58:08','43','Bilik Mesyuarat Utama NCD, Aras 2 , Bl;ok E3 Kompleks E,\r\nKementerian kesihatan Malaysia, Putrajaya.',NULL,2,'2017-01-16','14:30:00','2017-01-17','17:00:00',1,NULL,2,'Pn. Badariah, Pn. Mardiana',1,1,'32','2017-01-11 12:28:17',6,'KCP 646','',16,'TIDAK',''),(115,'2017-01-11 14:00:06','32','PROTON SERVIS',NULL,2,'2017-01-11','13:15:00','2017-01-12','15:30:00',4,'SERVIS BRAKE SYSTEM FAILURE',1,'SERVIS BRAKE SYSTEM FAILURE\r\nWXN 3346',1,1,'32','2017-01-11 14:06:15',3,'WXN 3346','HANTAR SERVIS',1,'TIDAK',''),(116,'2017-01-11 14:02:51','32','PROTON SERVIS',NULL,2,'2017-01-11','13:15:00','2017-01-12','15:30:00',4,'PASANG PART MISPLACE/UNSCREW',1,'PASANG PART MISPLACE/UNSCREW\r\nWB 1567 L',1,1,'32','2017-01-11 14:06:49',19,'WB 1567 L','TUNGGU SIAP',1,'TIDAK',''),(117,'2017-01-11 14:10:47','32','STANDBY N BANK',NULL,2,'2017-01-11','09:00:00','2017-01-11','15:30:00',4,'LAPOR DIRI PADA PPT',1,'PPT(T)\r\nAZIZ',1,-1,'32','2017-01-11 14:18:30',NULL,NULL,'',1,'TIDAK',''),(118,'2017-01-11 14:18:34','32','STANDBY , PUSAT SERVIS PROTON, BANK',NULL,2,'2017-01-12','09:30:00','2017-01-12','15:30:00',4,'LAPOR DIRI PADA PPT(T)',1,'PPT(T)\r\nAZIZ',1,-1,'32','2017-01-19 10:58:57',NULL,NULL,'STANDBY DGN AZIZ',1,'TIDAK',''),(119,'2017-01-11 14:23:28','32','OFF PETANG 4JAM',NULL,2,'2017-01-12','10:30:00','2017-01-12','15:30:00',4,'OFF PETANG',1,'OFF PETANG 1030-1530',1,1,'32','2017-01-11 14:26:36',4,'WC 721 G','INFO KAK NORSIAH N CC SOBRINA',1,'TIDAK',''),(120,'2017-01-11 14:20:48','106','POLITEKNIK TUANKU SULTANAH BAHIYAH KULIM',NULL,2,'2017-01-16','07:00:00','2017-01-16','17:00:00',4,'SESI DIALOG CPF DENGAN PEMEGANG LESEN B ',3,'NORSHAM, SAREH SAFWAN, MOKHTAR',1,-1,'39','2017-01-12 14:23:16',NULL,NULL,'URUSAN RASMI JABATAN',1,'TIDAK',''),(121,'2017-01-11 14:25:28','106','POLITEKNIK TUANKU SULTANAH BAHIYAH KULIM',NULL,2,'2017-01-16','07:30:00','2017-01-16','17:00:00',4,'SESI DIALOG CPF DENGAN PEMEGANG LESEN B',6,'TEH JIA WEI, THANA , FARHANIS , VANESSA, AISHAH, AIMI',2,1,'39','2017-01-11 14:30:35',25,'KEC644','URUSAN RASMI JABATAN',1,'TIDAK',''),(122,'2017-01-11 14:34:33','77','BALING',NULL,2,'2017-01-17','08:30:00','2017-01-17','17:00:00',4,'TUGAS KHAS PENGUATKUASAAN',3,'MOHD FAZLI , NIK NOOR AZAN, MOHD SYAZWAN',1,1,'39','2017-01-11 14:44:39',26,'KEB7401','TUGAS RASMI JABATAN',1,'TIDAK',''),(123,'2017-01-11 14:36:36','77','BALING',NULL,2,'2017-01-17','08:30:00','2017-01-17','17:00:00',4,'TUGAS KHAS PENGUATKUASAAN',5,'AMIR , VINEODH , ADHAM , SYAKIRIN, RASHID',2,1,'39','2017-01-11 14:45:36',25,'KEC644','TUGAS KHAS PENGUATKUASAAN',1,'TIDAK',''),(124,'2017-01-11 14:38:06','77','SUNGAI PETANI',NULL,2,'2017-01-18','08:30:00','2017-01-18','17:00:00',4,'TUGAS KHAS PENGUATKUASAAN',5,'FAZLI , NIK NOOR AZAN, SYAZWAN, AMIR, ADHAM',2,-1,'39','2017-01-17 08:31:46',NULL,NULL,'TUGAS RASMI JABATAN',1,'TIDAK',''),(125,'2017-01-12 08:21:29','49','Ke Jeti Kuala Perlis (bertolak dar JKN pada pukul 7.30 pagi)',NULL,2,'2017-01-22','07:30:00','2017-01-22','16:30:00',1,NULL,2,'Dr Azlina Azlan\r\nPPKP Maznie Hussin',1,-1,'49','2017-01-19 15:12:56',NULL,NULL,'',1,'BATAL',''),(126,'2017-01-12 10:10:09','69','LUMUT , PERAK',NULL,2,'2017-01-22','08:00:00','2017-01-24','17:00:00',1,NULL,4,'NORASYIKIN, BAHARUDDIN, MOKHTAR',2,-1,'39','2017-01-22 09:39:52',NULL,NULL,'',1,'TIDAK',''),(127,'2017-01-12 12:28:56','100','PKD KOTA SETAR',NULL,2,'2017-01-16','10:00:00','2017-01-16','16:30:00',2,'Pemantauan 1GOVNET',3,'ROZITA BT OSMAN\r\nUMMI MASTURAH BT MOHD MOKHTAR\r\nMOHD SHAFAROL WAHID B MAT SAAD',1,-1,'100','2017-01-16 08:04:01',NULL,NULL,'',1,'BATAL',''),(128,'2017-01-12 12:47:35','101','AKSEM , BKH',NULL,2,'2017-01-16','08:30:00','2017-01-16','13:00:00',1,NULL,2,'1. En. Dali Bin Ismail\r\n2. Pn Rohaya Binti Mahmud',1,1,'39','2017-01-12 14:24:37',26,'KEB7401','URUSAN RASMI',1,'TIDAK',''),(129,'2017-01-12 15:35:17','99','AIRPORT PENANG',NULL,2,'2017-01-19','08:00:00','2017-01-19','22:00:00',1,NULL,2,'DR AZLINA BT MUHAMAD RADZI (TPKN PERUBATAN)\r\nDR NORIDA BT MD HANAFIAH (KPPK PERUBATAN)',1,-1,'99','2017-01-18 14:40:13',NULL,NULL,'',10,'BATAL',''),(130,'2017-01-13 09:12:05','107','DEWAN WAWASAN, JITRA',NULL,2,'2017-01-31','07:10:00','2017-01-31','16:00:00',5,NULL,1,'MUHAMMAD FAHMI BIN MAHMUD, \r\nKETUA PENOLONG PENGARAH KANAN (SUMBER MANUSIA)',1,-1,'32','2017-01-30 16:40:26',NULL,NULL,'percakapan wani dgn PA Amalina',1,'TIDAK',''),(131,'2017-01-16 07:03:35','54','HOSPITAL KPJ PENANG,BANDAR BARU PERDA BUKIT MERTAJAM',NULL,2,'2017-01-18','10:00:00','2017-01-18','03:00:00',4,'JEMPUTAN UNTUK MEMBERI TAKLIMAT TIBI',2,'HJ KHAIROL NIZAM BIN IBRAHIM\r\nKJK ROHAYA KARIM',1,-1,'98','2017-01-16 11:40:01',NULL,NULL,'',1,'TIDAK',''),(132,'2017-01-16 09:08:47','48','ke wisma negeri ',NULL,2,'2017-01-16','09:30:00','2017-01-16','10:30:00',4,'pengambilan borang',1,'cik Nurhidayah',1,1,'32','2017-01-16 09:15:10',5,'WB 1567 L','',1,'TIDAK',''),(133,'2017-01-16 09:13:44','109','HOSPITAL KUALA NERANG',NULL,2,'2017-01-16','13:15:00','2017-01-16','17:00:00',1,NULL,4,'DR AZLINA (TPKN P), DR NURAH, MATRON HAJIJAH, EN SYAUKI',1,1,'32','2017-01-16 09:15:43',3,'KDN 8586','',1,'TIDAK',''),(134,'2017-01-16 09:36:58','42','HOSPITAL KULIM',NULL,2,'2017-01-23','08:00:00','2017-01-23','17:00:00',2,NULL,4,'DR SHAHIDA ISMAIL\r\nDR NOREDA BAKRI\r\nSITI NAZEHA MD SURI\r\nYUNUS ISMAIL',2,1,'32','2017-01-16 10:24:43',4,'WC 721 G','',1,'TIDAK',''),(135,'2017-01-16 10:00:30','35','KK MERBOK',NULL,2,'2017-01-18','08:00:00','2017-01-18','02:00:00',2,NULL,2,'SARIFAH\r\nEN MOHD FAUDZI',2,-1,'35','2017-01-16 10:04:02',NULL,NULL,NULL,1,'BATAL',''),(136,'2017-01-16 10:04:11','35','KK MERBOK',NULL,2,'2017-01-18','08:00:00','2017-01-18','15:00:00',2,NULL,2,'SARIFAH\r\nMOHD FAUDZI',2,-1,'35','2017-01-16 11:19:20',NULL,NULL,'',1,'BATAL',''),(137,'2017-01-16 11:19:17','90','GRAND ALORA , TH HOTEL',NULL,2,'2017-01-19','10:00:00','2017-01-19','17:00:00',2,NULL,3,'HJ SYAFIE\r\nDR FIKRIAH\r\nHAIRIL',1,1,'32','2017-01-16 11:43:20',6,'KCP 646','',1,'TIDAK',''),(138,'2017-01-16 11:43:17','54','HOSPITAL KPJ PENANG,BANDAR BARU PERDA BUKIT MERTAJAM',NULL,2,'2017-01-18','08:00:00','2017-01-18','17:00:00',4,'JEMPUTAN UNTUK MEMBERI TAKLIMAT TIBI',1,'HJ KHAIROL NIZAM BIN IBRAHIM\r\nROHAYA BINTI KARIM',1,1,'32','2017-01-16 11:47:20',19,'WB 1567 L','GANTI SBB PN SITI PEMBANGUNAN MOHON REDZUAN KE KL',1,'TIDAK',''),(139,'2017-01-16 12:22:05','86','Pusat Informatik Kesihatan , Bahagian Perancangan dan Pembangunan KKM, Aras 6, Blok E7, Kompleks E, 62590 Putra Jaya',NULL,2,'2017-01-16','08:00:00','2017-01-20','12:00:00',4,'Mengambil bahan penerbitan ',2,'Mohd Yunus / Mohd Nizam ',1,-1,'86','2017-01-16 14:18:22',NULL,NULL,NULL,1,'BATAL',''),(140,'2017-01-16 14:07:41','56','JKN KEDAH KE KUALA LUMPUR\r\nKUALA LUMPUR KE JKN KEDAH',NULL,2,'2017-01-16','15:00:00','2017-01-17','00:00:00',1,NULL,2,'MOHD AZMIL BIN ABDULLAH\r\nABDUL HADI BIN AHMAD',2,1,'32','2017-01-16 14:12:56',4,'WC 721 G','PEMANDU REQUEST BY CC HADI N PN SITI',1,'TIDAK',''),(141,'2017-01-16 14:12:54','58','TERMINAL FERI KUALA KEDAH',NULL,2,'2017-01-23','06:30:00','2017-01-23','03:00:00',1,NULL,1,'EN. OOLI GUNALAN A/L MANICKAM',1,-1,'58','2017-01-17 08:18:37',NULL,NULL,NULL,1,'BATAL',''),(142,'2017-01-16 14:18:30','86','KEMENTERIAN KESIHATAN MALAYSIA BAHAGIAN PERANCANGAN ARAS 3, 6 & 8, BLOK E6, KOMPLEKS E, PRESINT 1, PUTRA JAYA',NULL,2,'2017-01-19','08:00:00','2017-01-20','12:00:00',4,'Mengambil bahan penerbitan ',1,'YUNUS BIN SAAD / MOHD NIZAM',2,1,'32','2017-01-16 15:05:50',5,'KCE 646','GUNA KENDERAAN GERMBUSTER FOOD',1,'TIDAK',''),(143,'2017-01-16 15:59:32','32','RUMAH DR.HAYATI',NULL,1,'2017-01-16','04:00:00','2017-01-16',NULL,4,'HANTAR DOKUMEN',1,'DOKUMEN',1,1,'32','2017-01-16 16:01:47',21,'KCE 646','REQUEST BY HIDAYAH KA',1,'TIDAK',''),(144,'2017-01-17 08:06:01','90','SAREX PULAU LANGKAWI',NULL,2,'2017-02-26','08:00:00','2017-03-04','18:00:00',4,'LATIHAN SAREX\'17 SEMPENA LIMA\'17',5,'DR PAKAR\r\nDR\r\n3 PPP\r\n',2,-1,'90','2017-02-16 10:01:18',NULL,NULL,'',1,'BATAL',''),(145,'2017-01-17 08:19:52','58','TERMINAL FERI KUALA KEDAH',NULL,2,'2017-01-23','06:30:00','2017-01-23','15:00:00',1,NULL,1,'OOLI GUNALAN A/L MANICKAM',1,1,'32','2017-01-17 08:32:48',5,'WC 5247 D','',1,'TIDAK',''),(146,'2017-01-17 08:33:15','71','STESEN KERATAPI ALOR SETAR',NULL,1,'2017-01-18','09:00:00','2017-01-20','03:30:00',1,NULL,1,'NIK NOOR AZAN BIN NIK ISMAIL',1,1,'39','2017-01-17 08:40:43',26,'KCH778','HANTAR PEGAWAI MESYUARAT',1,'TIDAK',''),(147,'2017-01-17 08:35:14','77','ALOR SETAR',NULL,2,'2017-01-19','08:00:00','2017-01-19','03:30:00',4,'TUGAS KHAS PENGUATKUASAAN',2,'MOHD FAZLI KAMARUZAMAN , SYAZWAN BIN MORAT',1,1,'39','2017-01-17 08:41:57',26,'KEB7401','URUSAN RASMI JABATAN',1,'TIDAK',''),(148,'2017-01-17 08:36:45','77','ALOR SETAR',NULL,2,'2017-01-19','08:00:00','2017-01-19','03:30:00',4,'TUGAS KHAS PENGUATKUASAAN',5,'AMIRAH BT AHMAD RASIF , AMIR HARIZ , VINEODH NAIDU, LEE BOON SIN, KUMARESAN',1,1,'39','2017-01-17 08:41:34',25,'KEC644','URUSAN RASMI JABATAN',1,'TIDAK',''),(149,'2017-01-17 09:28:04','86','kkm, Bahagian Perkembangan Perubatan, Aras 2,4 - 7, Blok E1, Presint 1, Pusat Pentadbiran Kerajaan Persekutuan Putra Jaya',NULL,2,'2017-01-25','20:30:00','2017-01-26','14:00:00',1,NULL,3,'Dr. Zaiton Udin(TUMPANG UTK P\'JALANAN BALIK)\r\nDr Azlina bt Muhamad Radzi ,\r\nDr Zaidah M.Ariff , \r\nPn Rosseriyany bt Don',2,1,'32','2017-01-17 10:06:28',4,'WC 721 G','Dr. Zaiton Udin(Tumpang Utk Pjalanan Balik)',1,'TIDAK',''),(150,'2017-01-17 10:05:01','104','PKD KOTA SETAR, KK LANGGAR, KD HUTAN KAMPUNG,KD TELAGA EMAS\r\n\r\nKD LENGKUAS\r\n\r\n\r\n',NULL,2,'2017-02-06','08:00:00','2017-02-06','05:00:00',4,'AUDIT KAWALAN INFEKSI',5,'DR SARMIZA,HJ. HASNIZAM, PPP FAZURI, MATRON HASIMAH,KJK THAIRAH',2,-1,'104','2017-01-17 10:34:28',NULL,NULL,NULL,1,'BATAL',''),(151,'2017-01-17 10:12:43','104','KD DERANG,KD KUBANG JAWI,KD PADANG HANG,KD LEPAI',NULL,2,'2017-02-06','08:00:00','2017-02-06','05:00:00',4,'AUDIT KAWALAN INFEKSI TEAM 2',5,'DR. MAZWIN, HJ. NASIR, PPP FIRDAUS, MATRON ANIS, KJK FAZLIWATI',2,-1,'104','2017-01-17 10:34:15',NULL,NULL,NULL,1,'BATAL',''),(152,'2017-01-17 10:17:56','46','PKD Port Dickson',NULL,2,'2017-01-19','08:00:00','2017-01-20','05:00:00',1,NULL,3,'DR SHAHIDA BT ISMAIL\r\nSITI NAZEHA BT MD SURI\r\nYUNUS B ISMAIL',1,-1,'46','2017-01-17 10:32:11',NULL,NULL,NULL,5,'BATAL',''),(153,'2017-01-17 10:32:19','46','PKD Port Dickson',NULL,2,'2017-01-19','08:00:00','2017-01-20','17:00:00',1,NULL,3,'dr shahida ismail\r\nsiti nazeha bt md suri\r\nyunus b ismail',1,1,'32','2017-01-17 10:38:25',3,'KDN 8586','sambung perjalanan daripada Mesy.Putrajaya',5,'TIDAK',''),(154,'2017-01-17 10:34:45','104','KK LANGGAR,KD HUTAN KAMPUNG,KD LENGKUAS,KD TELAGA EMAS',NULL,2,'2017-02-06','08:00:00','2017-02-06','17:00:00',4,'AUDIT KAWALAN INFEKSI TEAM 1',5,'SILA RUJUK PN AZIZAH KIK DAHULU\r\nDR. SARMIZA, HJ. NASIR, PPP FAZURI, MATRON HASIMAH, MATRON ANIS',2,1,'32','2017-01-23 10:34:03',21,'KDN 8586','TEMPAHAN UNIT KKIK,PRIMER DAN PEMAKANAN PERLU MELALUI PN AZIZAH KIK DAHULU',1,'TIDAK',''),(155,'2017-01-17 10:38:06','104','KD BUKIT PINANG, KD TITI HAJI IDRIS,KD LEPAI,KD PADANG HANG',NULL,2,'2017-02-06','08:00:00','2017-02-06','17:00:00',4,'AUDIT KAWALAN INFEKSI TEAM 2',5,'JIKA XMUAT SILA GUNA INNOVA KCH4777\r\n\r\nDR. MAZWIN, HJ. HASNIZAM, PPP FIRDAUS, KJK FAZLIWATI, KJK THAIRAH',2,1,'32','2017-01-23 10:33:06',6,'KCP 646','SILA RUJUK PN AZIZAH KIK DAHULU',1,'TIDAK',''),(156,'2017-01-17 10:40:30','104','KD KUBANG JAWI,KD DERANG,K1M POKOK SENA',NULL,2,'2017-02-07','08:00:00','2017-02-07','17:00:00',4,'AUDIT KAWALAN INFEKSI TEAM 1',5,'SILA RUJUK PN AZIZAH KIK DAHULU\r\nDR. SARMIZA, HJ. NASIR, PPP FAZURI, MATRON HASIMAH, SN ROZITA',2,1,'59','2017-01-23 10:21:52',27,'KCH 4777','',1,'TIDAK',''),(157,'2017-01-17 10:43:16','104','K1M ALOR MENGKUDU,K1M UTC ALOR SETAR\r\n\r\n',NULL,2,'2017-02-07','08:00:00','2017-02-07','17:00:00',4,'AUDIT KAWALAN INFEKSI TEAM 2',4,'SILA RUJUK PN AZIZAH KIK DAHULU\r\nDR. MAZWIN, PPP FIRDAUS, MATRON ANIS, KJK FAZLIWATI',1,1,'32','2017-01-23 10:35:26',21,'KCE 646','SILA RUJUK PN AZIZAH DAHULU',1,'TIDAK',''),(158,'2017-01-17 14:33:40','112','Pejabat Kesihatan Bukit Kayu Hitam',NULL,2,'2017-01-18','08:30:00','2017-01-18','15:00:00',2,'Networking 1GovNet',1,'Muhammad Zayid Bin Ibeni',1,1,'32','2017-01-17 14:41:46',5,'WC 5247 D','',1,'TIDAK',''),(159,'2017-01-17 14:45:57','32','BANK & JPJ',NULL,2,'2017-01-18','10:00:00','2017-01-18','13:00:00',3,NULL,1,'UNIT KEWANGAN',1,-1,'32','2017-01-19 10:57:37',NULL,NULL,'',1,'TIDAK',''),(160,'2017-01-17 17:25:42','50','Hospital Kuala Nerang',NULL,2,'2017-01-19','13:30:00','2017-01-19','17:00:00',1,NULL,2,'Mohd Fitri Abdul, Dr Nurah',2,1,'32','2017-01-18 08:28:40',22,'WXN 3346','',1,'TIDAK',''),(161,'2017-01-18 08:17:37','80','Mahkamah Jitra',NULL,2,'2017-01-25','08:00:00','2017-01-25','17:00:00',4,'Perbicaraan',1,'Vanessa Teoh ',1,1,'39','2017-01-22 09:39:24',25,'KEB7401','URUSAN RASMI JABATAN',1,'TIDAK',''),(162,'2017-01-18 10:31:48','63','KOMPLEKS E PUSAT PENTADBIRAN KERAJAAN PERSEKUTUAN, PUTRAJAYA',NULL,2,'2017-01-22','12:00:00','2017-01-23','03:00:00',1,NULL,1,'MOHD SOFIAN BIN SAAD',1,-1,'63','2017-01-18 11:00:33',NULL,NULL,NULL,16,'BATAL',''),(163,'2017-01-18 11:01:00','63','KOMPLEKS E KKM PUTRAJAYA',NULL,2,'2017-01-22','10:00:00','2017-01-23','15:00:00',1,NULL,1,'MOHD SOFIAN BIN SAAD',1,1,'32','2017-01-18 11:19:42',20,'WXN 3346','',16,'TIDAK',''),(164,'2017-01-18 12:16:16','56','alor setar',NULL,2,'2017-01-18','12:35:00','2017-01-18','15:50:00',4,'kedai photostat',1,'muhammad husni bin ghazali',2,1,'32','2017-01-18 12:33:54',22,'KCE 646','YAHYA BIN MUSTAFA SEBAGAI PEMANDU',1,'TIDAK',''),(165,'2017-01-18 14:57:50','61','TAMAN SELASIH KULIM',NULL,2,'2017-01-23','08:00:00','2017-01-23','17:00:00',2,NULL,3,'RUJUK PN AZIZAH KIK\r\nPn Rohida ,Pn Suriana ,Pn Sharifah',1,1,'59','2017-01-23 10:23:44',27,'KCH 4777','',1,'TIDAK',''),(166,'2017-01-18 14:57:50','61','KLINIK KESIHATAN  SUNGAI TIANG',NULL,2,'2017-02-15','08:00:00','2017-02-15','17:00:00',2,NULL,3,'SILA RUJUK PN AZIZAH KIK DAHULU\r\nPn Rohida ,Pn Suriana ,Pn Sharifah',1,-1,'59','2017-02-05 10:10:36',NULL,NULL,'TEMPAHAN ONLINE UNIT KIK,PRIMER DAN PEMAKANAN PERLU MELALUI PN AZIZAH KIK DAHULU',1,'TIDAK',''),(167,'2017-01-18 15:26:08','43','BILIK MESYUARAT UTAMA , INSTITUT KANSER NEGARA, PUTRAJAYA',NULL,2,'2017-02-13','12:30:00','2017-02-15','17:00:00',1,NULL,3,'DR SHAHRUL BARIYAH, DR SHARIFAH SHAFFINAS, PN BADARIAH',1,-1,'32','2017-02-07 13:06:07',NULL,NULL,'percakapan dgn sister badariah',16,'TIDAK',''),(168,'2017-01-18 15:59:39','66','bank',NULL,2,'2017-01-19','10:00:00','2017-01-19','11:00:00',3,NULL,1,'PN. HAFIEZA \r\nPN. ZAITON',1,1,'32','2017-01-18 16:20:39',19,'WB 1567 L','',1,'TIDAK',''),(169,'2017-01-18 16:37:53','46','PKD Kota Setar',NULL,2,'2017-01-19','10:30:00','2017-01-19','12:30:00',1,NULL,1,'Dr Noreda Bt Bakri',1,1,'32','2017-01-18 17:03:43',21,'WXN 3346','',1,'TIDAK',''),(170,'2017-01-19 11:10:27','58','JEMPUT DI RUMAH DAN HANTAR KE LAPANGAN TERBANG SULTAN ABDUL HALIM ALOR SETAR',NULL,2,'2017-01-24','08:00:00','2017-01-24','21:00:00',1,'MENGHADIRI MESYUARAT DI PUTRAJAYA',1,'EN. OOLI GUNALAN A/L MANICKAM',1,-1,'58','2017-01-23 14:22:57',NULL,NULL,'',1,'BATAL',''),(171,'2017-01-19 11:34:37','80','Jeti Kuala Kedah',NULL,1,'2017-01-23','07:30:00','2017-01-23','05:30:00',1,NULL,1,'Dr. Zaidah Binti M. Arif',1,-1,'80','2017-01-19 11:42:08',NULL,NULL,NULL,1,'BATAL',''),(172,'2017-01-19 11:46:08','100','MAKMAL BUKIT KAYU HITAM',NULL,2,'2017-01-23','09:30:00','2017-01-23','13:00:00',2,'Pemeriksaan Peralatan ICT',1,'JAMILAH BINTI HARUN',1,1,'32','2017-01-19 12:12:37',6,'KCP 646','',1,'TIDAK',''),(173,'2017-01-19 11:47:32','114','HOSPITAL YAN',NULL,2,'2017-01-23','08:30:00','2017-01-23','12:00:00',2,NULL,3,'CHE NAH BT SHAARI\r\nNOR ADILA BT CHE DAUD\r\nPUAN NURUL NAJWA BT ADBUL JAMIL',1,1,'32','2017-01-19 12:22:07',3,'KDN 8586','',1,'TIDAK',''),(174,'2017-01-19 12:19:41','115','Jeti Kuala Kedah',NULL,2,'2017-01-23','08:00:00','2017-01-23','05:30:00',1,'Mesyuarat',1,'Dr. Zaidah Binti M. Arif',1,0,'32','2017-01-19 12:23:20',NULL,NULL,'TIADA PEMANDU DAN KENDERAAN.JADUAL FULL\r\nMOHON MAAF',1,'TIDAK',''),(175,'2017-01-19 12:27:57','60','Damai Laut, Lumut',NULL,2,'2017-01-26','07:00:00','2017-01-26','12:00:00',2,NULL,4,'En. Fitri, Pn Hayati dll.',2,-1,'32','2017-01-26 22:42:02',NULL,NULL,'Tunda',1,'TIDAK',''),(176,'2017-01-19 12:29:35','60','Damai Laut, Lumut',NULL,2,'2017-01-26','07:00:00','2017-01-26','12:00:00',2,NULL,4,'Team ICT',2,-1,'32','2017-01-26 22:41:25',NULL,NULL,'Tunda',1,'TIDAK',''),(177,'2017-01-19 12:40:30','118','Kulim dan Kuala Kedah',NULL,2,'2017-02-06','08:00:00','2017-02-06','05:00:00',2,NULL,5,'Dr Hajar\r\nDr Izzati\r\nDr Hasmaliza\r\nEn.Wan\r\nSr. Chor',2,-1,'32','2017-02-06 09:19:07',NULL,NULL,'info by Redzuan',1,'TIDAK',''),(178,'2017-01-19 12:55:15','118','Kulim dan Kuala Kedah',NULL,2,'2017-02-07','08:00:00','2017-02-07','05:00:00',2,NULL,5,'Dr Hajar\r\nDr Izzati\r\nDr Hasmaliza\r\nEn. Wan\r\nSr. Chor',2,1,'32','2017-01-24 15:28:48',3,'KDN 8586','',1,'TIDAK',''),(179,'2017-01-19 14:20:13','96','jabatan akauntan negeri kedah',NULL,2,'2017-01-19','11:30:00','2017-01-19','13:00:00',4,'Hantar Dokumen',1,'roshidi khalid',1,1,'32','2017-01-19 14:26:04',19,'WB 1567 L','',1,'TIDAK',''),(180,'2017-01-19 15:22:44','118','PENDANG / YAN / KUALA KEDAH',NULL,2,'2017-01-04','08:00:00','2017-01-04','04:30:00',2,NULL,3,'KPP / PPP WAN / SIS. CHOR',1,1,'118','2017-01-24 15:24:26',30,'KCM8040','',1,'TIDAK',''),(181,'2017-01-19 18:10:25','50','Hospital kuala nerang',NULL,2,'2017-01-22','08:00:00','2017-01-22','01:00:00',2,NULL,3,'Mohd fitri abdul, Dr. Nurah, Matron Hajijah',1,1,'32','2017-01-22 08:18:56',6,'KCP 646','INFO PADA 19 JANUARI PUKUL 18.30',1,'TIDAK',''),(182,'2017-01-22 09:25:34','67','JKN Kedah ke Airport Penang',NULL,1,'2017-01-24','13:30:00','2017-01-24',NULL,4,'Mengunjungi Wilayah Asal',1,'Pn. lau Sie Ngo',1,1,'32','2017-01-22 09:30:48',5,'WC 5247 D','',1,'TIDAK',''),(183,'2017-01-22 10:16:17','50','Hospital Kuala Nerang',NULL,2,'2017-01-24','08:00:00','2017-01-24','13:00:00',2,NULL,4,'Dr. Azlina, Dr. Nurah, Mohd Fitri Abdul, Matron Hajijah',2,1,'32','2017-01-22 10:43:23',3,'KDN 8586','',1,'TIDAK',''),(184,'2017-01-22 10:38:01','53','KOLEJ SAINS KESIHATAN BERSEKUTU, TANJUNG RAMBUTAN, IPOH, PERAK',NULL,2,'2017-02-13','08:00:00','2017-02-15','05:00:00',5,NULL,5,'PN. KHAIRUL BARIAH BT. ISHAK\r\nPN. FAUZIAH BT. MAHMUD\r\nPEN NUR ARIFAH BT. IDRIS\r\nCIK. JAMILAH BT. ABD WAHAB\r\nEN. MOHAMAD SHAFUDEN BIN OTHMAN',2,1,'32','2017-01-22 11:28:15',3,'KDN 8586','',7,'TIDAK',''),(185,'2017-01-22 14:32:25','121','PKD SIK',NULL,2,'2017-01-25','08:00:00','2017-01-25','16:00:00',2,NULL,3,'DR. TAN SEOK HONG, ABDULLAH AHMAD, MUHAMMAD MUSTAQIM, ',1,1,'32','2017-01-22 14:50:56',6,'KCP 646','',1,'TIDAK',''),(186,'2017-01-22 15:10:37','54','Hospital KPJ Bandar Perda,Bukit Mertajam Pulau Pinang dan Hospital Kulim',NULL,2,'2017-01-24','08:00:00','2017-01-24','17:00:00',2,NULL,2,'YUNUS GANTI FIKRI(EL ISTERI SAKIT)\r\nHJ KHAIROL NIZAM BIN IBRAHIM\r\nMAHERAN BINTI TAJUDDIN',1,1,'32','2017-01-22 15:34:50',21,'WXN 3346','FIKRI E.L',1,'TIDAK',''),(187,'2017-01-22 15:34:07','56','JETI KUALA KEDAH',NULL,2,'2017-01-23','07:30:00','2017-01-23','19:00:00',1,NULL,3,'mohd azmil bin abdullah\r\nabdul hadi bin ahmad\r\nmuhammad husni bin ghazali',2,1,'32','2017-01-22 15:45:06',3,'KDN 8586','',1,'TIDAK',''),(188,'2017-01-22 16:24:14','56','PTG Kuala Muda',NULL,2,'2017-01-24','08:00:00','2017-01-24','17:00:00',2,NULL,3,'SITI RAHAYU BINTI AHMAD RUSLI\r\nMOHD AZMIL BIN ABDULLAH\r\nABDUL HADI BIN AHMAD',2,-1,'56','2017-01-24 09:30:52',NULL,NULL,'',1,'BATAL',''),(189,'2017-01-22 16:27:45','43','KSKB Ulu Kinta, Ipoh',NULL,2,'2017-01-23','10:00:00','2017-01-26','12:00:00',1,NULL,2,'Dr Shahrul Bariyah , Dr. Sharifah Saffinas',1,1,'32','2017-01-22 16:32:44',19,'WB 1567 L','',7,'TIDAK',''),(190,'2017-01-22 16:24:00','61','Putrajaya/Kuala Lumpur (tempat tak tetap lagi)',NULL,2,'2017-02-20','08:00:00','2017-02-23','17:00:00',1,NULL,3,'Pn rohida,pn suriana,en Wan Nurussabah',1,0,'59','2017-01-23 10:56:06',NULL,NULL,'Tidak diluluskan kerana tempat mesyuarat tidak di tetapkan lagi.',16,'TIDAK',''),(191,'2017-01-22 16:33:00','61','Kuala Lumpur',NULL,2,'2017-03-18','08:00:00','2017-03-23','17:00:00',4,'Latihan pada pengumpul data NHMS',6,'Pn Rohida,PSP Hasnani dan lain-lain',1,1,'59','2017-01-24 11:44:01',27,'KCH 4777','',17,'TIDAK',''),(192,'2017-01-23 08:45:47','49','Ke Jeti Kuala Perlis (bertolak dar JKN pada pukul 7.30 pagi)',NULL,2,'2017-02-05','07:30:00','2017-02-05','17:00:00',1,NULL,2,'Dr Azlina Azlan\r\nMaznie Hussin',1,1,'32','2017-01-23 09:40:58',19,'KCE 646','tukar kereta Preve kpd Xtrail kerana aircond preve rosak',1,'TIDAK',''),(193,'2017-01-23 08:53:04','90','GRAND ALORA HOTEL, \r\nSTADIUM SUKA MENANTI',NULL,2,'2017-02-07','08:00:00','2017-02-09','20:00:00',4,'FAST CAR UNTUK PASUKAN LIPUTAN PERUBATAN',3,'PAKAR\r\nMA\r\nSN',1,-1,'90','2017-02-07 08:10:23',NULL,NULL,'',1,'BATAL',''),(194,'2017-01-23 09:34:47','61','Kedah Medical Centre .',NULL,2,'2017-01-25','14:00:00','2017-01-25','17:00:00',4,'penghantaran plak ke klinik KMC',1,'Pn Suriana',1,1,'32','2017-01-23 11:09:10',20,'WXN 3346','PERCAKAPAN TELEFON DGN SR NORIAH',1,'TIDAK',''),(195,'2017-01-23 09:54:01','59','Kursus',NULL,2,'2017-02-06','08:00:00','2017-02-06','17:00:00',5,'Taklimat ',1,'Kursus/Seminar',1,1,'59','2017-01-23 09:58:22',27,'KCH 4777','',1,'TIDAK',''),(196,'2017-01-23 11:13:17','32','SPA KEDAH',NULL,2,'2017-02-14','08:00:00','2017-02-15','17:00:00',4,'TEMUDUGA',1,'PEGAWAI SPA',1,-1,'32','2017-01-25 15:49:59',NULL,NULL,'',1,'TIDAK',''),(197,'2017-01-23 11:18:17','32','SPA KEDAH',NULL,2,'2017-02-20','08:00:00','2017-02-23','17:00:00',4,'TEMUDUGA',1,'PEGAWAI SPA',1,-1,'32','2017-01-25 15:51:41',NULL,NULL,'',1,'TIDAK',''),(198,'2017-01-23 11:20:23','32','SPA KEDAH',NULL,2,'2017-02-27','08:00:00','2017-02-28','17:00:00',4,'TEMUDUGA',1,'PEGAWAI SPA',1,-1,'32','2017-01-25 15:52:19',NULL,NULL,'',1,'TIDAK',''),(199,'2017-01-23 11:21:41','32','SPA KEDAH',NULL,2,'2017-03-01','08:00:00','2017-03-01','17:00:00',4,'TEMUDUGA',1,'PEGAWAI SPA',1,-1,'32','2017-01-25 15:52:38',NULL,NULL,'',1,'TIDAK',''),(200,'2017-01-23 14:23:03','58','PEJABAT KESIHATAN DAERAH KUALA MUDA DAN PEJABAT TANAH KUALA MUDA',NULL,2,'2017-01-24','08:30:00','2017-01-24','17:00:00',1,NULL,1,'PN.SITI RAHAYU\r\nEN.ABDUL HADI\r\nEN OOLI GUNALAN A/L MANICKAM(el)',1,1,'32','2017-01-23 14:36:07',4,'WC 721 G','BATAL PROGRAM MAZLAN TANPA INFO.NAIK KENDERAAN EN.OOLI',1,'TIDAK','');
/*!40000 ALTER TABLE `ttempah_kenderaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ttempah_tujuan`
--

DROP TABLE IF EXISTS `ttempah_tujuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ttempah_tujuan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tujuan_perjalanan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ttempah_tujuan`
--

LOCK TABLES `ttempah_tujuan` WRITE;
/*!40000 ALTER TABLE `ttempah_tujuan` DISABLE KEYS */;
INSERT INTO `ttempah_tujuan` VALUES (1,'Mesyuarat'),(2,'Lawatan Kerja'),(3,'Bank'),(4,'Lain-lain'),(5,'Kursus / Seminar / Bengkel');
/*!40000 ALTER TABLE `ttempah_tujuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tunit`
--

DROP TABLE IF EXISTS `tunit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tunit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit` varchar(100) DEFAULT NULL,
  `idbahagian` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tunit`
--

LOCK TABLES `tunit` WRITE;
/*!40000 ALTER TABLE `tunit` DISABLE KEYS */;
INSERT INTO `tunit` VALUES (1,'AIDS/STD','369'),(2,'ALAM SEKITAR','1'),(3,'INSPEKTORAT & PERUNDANGAN','1'),(4,'KEJURUTERAAN','1'),(5,'KESIHATAN AWAM','1'),(6,'KESIHATAN KELUARGA','1'),(7,'KESIHATAN PRIMER','1'),(8,'KPAS','1'),(9,'PEMAKANAN','1'),(10,'PEMBANGUNAN KELUARGA','1'),(11,'PENYAKIT BERJANGKIT','1'),(12,'PENYAKIT TIDAK BERJANGKIT','1'),(13,'PROMOSI KESIHATAN','1'),(14,'TPKN(KA)','1'),(15,'PEJABAT TPKN (KKM)','2'),(16,'IT','3'),(17,'KAUNSELING','3'),(18,'KEJURURAWATAN','3'),(19,'KEWANGAN','3'),(20,'LATIHAN','3'),(21,'PEMBANGUNAN','3'),(22,'PENGARAH','3'),(23,'PENTADBIRAN','3'),(24,'PERJAWATAN','3'),(25,'SM','3'),(26,'TPKN(U)','3'),(27,'PERKHIDMATAN PERUBATAN','4'),(28,'PERUBATAN','4'),(29,'REKOD PERUBATAN','4'),(30,'UKAPS','4'),(31,'AMALAN & PERKEMBANGAN FARMASI','5'),(32,'BAHAGIAN PENGURUSAN FARMASI','5'),(33,'CAWANGAN PENGUATKUASA FARMASI','5'),(34,'TPKN (PERGIGIAN)','6'),(36,'Unit Sumber Manusia','809'),(37,'Unit Kewangan','809'),(38,'Unit Pembangunan','809'),(39,'Unit ICT','809'),(40,'Unit Khidmat Pengurusan','809'),(41,'Unit Kaunseling','809'),(42,'Unit Kesihatan Awam','810'),(43,'Unit Kesihatan Keluarga','810'),(44,'Unit Primer','810'),(45,'Unit Promosi Kesihatan','810'),(46,'Unit Kawalan Penyakit Berjangkit','810'),(47,'Unit Kawalan Penyakit Tidak Berjangkit','810'),(48,'Unit Kejuruteraan','810'),(49,'Unit Pengurusan Perubatan','811'),(50,'Unit Kejuruteraan Hospital','811'),(51,'Unit Kualiti Perubatan','811'),(52,'Unit UKAPS','811'),(53,'Unit Pengurusan','812'),(54,'Amalan & Perkembangan Farmasi','814'),(55,'Amalan & Perkembangan Farmasi','814'),(56,'Unit Kesihatan Pekerjaan','810'),(57,'Unit Kejururawatan','810'),(58,'Unit Kesihatan Keluarga','810'),(59,'Pemakanan','810'),(60,'AIDS/STD','810'),(61,'Inspektorat Dan Perundangan','810'),(62,'Vektor','810'),(63,'BKKM','815'),(64,'Unit Rekod Perubatan','811'),(65,'Unit Kejururawatan','811'),(66,'PEROLEHAN DAN ASET','809'),(67,'Amalan & Perkembangan Farmasi','814'),(68,'daaa','824'),(69,'AIDS/STD','825'),(70,'AIDS/STD','153'),(72,'TEST','779'),(73,'TE','154');
/*!40000 ALTER TABLE `tunit` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-11 15:01:58
