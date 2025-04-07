/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.21-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: inventariofinal
-- ------------------------------------------------------
-- Server version	10.6.21-MariaDB-cll-lve

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
-- Table structure for table `cambios`
--

DROP TABLE IF EXISTS `cambios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cambios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_producto_devuelto` int(11) NOT NULL,
  `cantidad_devuelta` int(11) NOT NULL,
  `id_producto_entregado` int(11) NOT NULL,
  `cantidad_entregada` int(11) NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado','completado') DEFAULT 'pendiente',
  `motivo` text NOT NULL,
  `usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_producto_devuelto` (`id_producto_devuelto`),
  KEY `id_producto_entregado` (`id_producto_entregado`),
  KEY `usuario` (`usuario`),
  CONSTRAINT `cambios_ibfk_1` FOREIGN KEY (`id_producto_devuelto`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cambios_ibfk_2` FOREIGN KEY (`id_producto_entregado`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cambios_ibfk_3` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cambios`
--

LOCK TABLES `cambios` WRITE;
/*!40000 ALTER TABLE `cambios` DISABLE KEYS */;
INSERT INTO `cambios` VALUES (3,'2025-02-16 21:22:55',63,1,62,1,'aprobado','CAMBIO DE TALLA',1),(4,'2025-02-18 02:00:35',60,1,59,1,'aprobado','Cambio de Talla',63),(5,'2025-02-20 23:59:31',60,1,58,1,'aprobado','Cambio Talla',1),(7,'2025-02-24 19:24:15',63,1,61,1,'aprobado','CAMBIO DE TALLA',1),(8,'2025-02-25 18:58:01',60,1,59,1,'aprobado','CAMBIO DE TALLA',1),(10,'2025-03-02 19:20:07',60,1,59,1,'aprobado','Cambio de talla',1),(11,'2025-03-11 20:42:09',60,1,58,1,'rechazado','Cambio de Talla',1),(12,'2025-03-11 20:42:48',50,1,47,1,'pendiente','Cambio de talla',1),(13,'2025-03-11 20:43:18',58,1,53,1,'pendiente','Cambio de color',1),(15,'2025-03-25 21:53:50',63,1,62,1,'aprobado','Cambio de Talla',1),(16,'2025-03-27 16:50:46',63,1,62,1,'aprobado','cambio de talla',1);
/*!40000 ALTER TABLE `cambios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'CARDIGAN','2025-02-24 19:15:53'),(2,'SUETER','2024-10-18 22:24:26'),(3,'CHALINA','2024-10-18 22:24:49'),(4,'GUANTES','2024-10-18 22:24:58'),(5,'MITONES','2024-10-18 22:25:07'),(6,'GORROS','2024-10-18 22:25:20'),(7,'CHULLO','2024-10-18 22:25:25'),(8,'MANTA','2025-02-18 01:50:26'),(15,'medias','2025-02-20 23:48:43');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_venta`
--

DROP TABLE IF EXISTS `detalle_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_venta` (`id_venta`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`),
  CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_venta`
--

LOCK TABLES `detalle_venta` WRITE;
/*!40000 ALTER TABLE `detalle_venta` DISABLE KEYS */;
INSERT INTO `detalle_venta` VALUES (19,33,63,3,150),(20,36,62,1,149),(21,36,61,2,149),(22,37,57,1,239),(23,37,56,3,239),(24,38,18,1,299),(25,38,9,1,249),(26,54,63,1,150),(27,54,62,1,149);
/*!40000 ALTER TABLE `detalle_venta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devolucion`
--

DROP TABLE IF EXISTS `devolucion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `devolucion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado','completado') DEFAULT 'pendiente',
  `motivo` text NOT NULL,
  `usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_producto` (`id_producto`),
  KEY `usuario` (`usuario`),
  CONSTRAINT `devolucion_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `devolucion_ibfk_2` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devolucion`
--

LOCK TABLES `devolucion` WRITE;
/*!40000 ALTER TABLE `devolucion` DISABLE KEYS */;
INSERT INTO `devolucion` VALUES (17,'2025-02-04 23:51:11',3,2,'pendiente','Error de diseño',57),(18,'2025-02-04 23:51:11',5,1,'aprobado','Producto defectuoso',58),(19,'2025-02-04 23:51:11',7,3,'rechazado','No coincide con la descripción',63),(43,'2025-03-12 03:23:50',63,1,'pendiente','Producto defectuoso',63),(45,'2025-03-12 03:24:26',54,2,'aprobado','Error en la talla',1),(47,'2025-02-17 04:21:59',51,1,'aprobado','ERROR',1),(55,'2025-03-11 20:24:55',55,1,'pendiente','Producto defectuoso',1),(56,'2025-03-12 03:26:03',63,1,'pendiente','Error de color',1),(57,'2025-03-11 20:27:18',41,3,'pendiente','Error de tallas',1),(58,'2025-03-18 01:18:25',42,1,'aprobado','Error de talla',1);
/*!40000 ALTER TABLE `devolucion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mermas`
--

DROP TABLE IF EXISTS `mermas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mermas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` text NOT NULL,
  `usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_producto` (`id_producto`),
  KEY `usuario` (`usuario`),
  CONSTRAINT `mermas_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mermas_ibfk_2` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mermas`
--

LOCK TABLES `mermas` WRITE;
/*!40000 ALTER TABLE `mermas` DISABLE KEYS */;
INSERT INTO `mermas` VALUES (1,'2025-02-16 21:30:10',52,2,'ERROR DE DISEÑO',1),(9,'2025-03-11 20:31:47',55,2,'Error de talla',1),(10,'2025-03-11 20:32:19',50,2,'Error de color del producto',1),(11,'2025-03-11 20:33:09',36,3,'Error de producto defectuoso',58),(12,'2025-03-11 20:33:39',62,1,'Error de color del producto',58),(13,'2025-03-11 20:34:25',10,1,'Error de talla',58),(14,'2025-03-11 20:39:43',36,1,'Error de talla',58),(15,'2025-03-11 20:40:47',58,2,'Error de producto defectuoso',58);
/*!40000 ALTER TABLE `mermas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_categoria` int(11) NOT NULL,
  `codigo` text NOT NULL,
  `descripcion` text NOT NULL,
  `ubicacion` varchar(50) NOT NULL,
  `imagen` text NOT NULL,
  `stock` int(11) NOT NULL,
  `precio_produccion` float NOT NULL,
  `precio_venta` float NOT NULL,
  `ventas` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `talla` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_categoria` (`id_categoria`),
  CONSTRAINT `fk_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (3,1,'101','Cárdigan Verde Bosque de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/101/627.png',3,130,249,1,'2025-02-28 20:31:08','XL'),(5,1,'103','Cárdigan Verde Bosque de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/103/253.png',3,130,249,0,'2025-02-28 20:31:08','S'),(6,1,'104','Cárdigan Verde Bosque de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/104/231.png',0,130,249,0,'2025-02-28 20:31:08','L'),(7,1,'105','Cárdigan Verde Bosque de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/105/671.png',1,130,249,0,'2025-02-28 20:31:08','XXL'),(8,1,'106','Cárdigan Adaya Rojo de alpaca','ALMACEN 1','vistas/img/productos/106/772.png',0,130,249,0,'2025-02-28 20:31:08','S'),(9,1,'107','Cárdigan Adaya Rojo de alpaca','ALMACEN 1','vistas/img/productos/107/866.png',11,130,249,1,'2025-03-28 00:16:22','M'),(10,1,'108','Cárdigan Adaya Rojo de alpaca','ALMACEN 1','vistas/img/productos/108/676.png',19,130,350,10,'2025-03-11 20:34:25','L'),(11,1,'109','Cárdigan Adaya Rojo de alpaca','ALMACEN 1','vistas/img/productos/109/833.png',0,120,249,0,'2025-02-28 20:31:08','XL'),(17,1,'110','Cárdigan Retenido de Alpaca Con Cuello Redondo','ALMACEN 1','vistas/img/productos/110/193.jpg',15,140,249,0,'2025-02-28 20:31:08','S'),(18,1,'111','Cárdigan Retenido de Alpaca Con Cuello Redondo','ALMACEN 1','vistas/img/productos/111/712.jpg',19,150,299,1,'2025-03-28 00:16:22','M'),(19,1,'112','Cárdigan Retenido de Alpaca Con Cuello Redondo','ALMACEN 1','vistas/img/productos/112/945.jpg',20,140,299,0,'2025-02-28 20:31:08','L'),(20,1,'113','Cárdigan Retenido de Alpaca Con Cuello Redondo','ALMACEN 1','vistas/img/productos/113/634.jpg',25,140,299,0,'2025-02-28 20:31:08','XL'),(21,1,'114','Cárdigan Retenido de Alpaca Con Cuello Redondo','ALMACEN 1','vistas/img/productos/114/386.jpg',15,140,299,0,'2025-02-28 20:31:08','XXL'),(22,2,'201','Suéter Retenido de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/201/620.jpg',20,130,239,0,'2025-02-28 20:30:57','S'),(23,2,'202','Suéter Retenido de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/202/223.jpg',23,130,239,0,'2025-02-28 20:30:57','M'),(26,2,'203','Suéter Retenido de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/203/503.jpg',30,130,239,0,'2025-02-28 20:30:57','L'),(27,2,'204','Suéter Retenido de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/204/751.jpg',18,130,239,0,'2025-02-28 20:30:57','XL'),(28,2,'205','Suéter Retenido de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/205/529.jpg',20,130,239,0,'2025-02-28 20:30:57','XXL'),(29,1,'115','Cárdigan Azul de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/115/530.jpg',25,140,249,0,'2025-02-28 20:31:08','S'),(30,1,'116','Cárdigan Azul de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/116/159.jpg',20,140,249,0,'2025-02-28 20:31:08','M'),(31,1,'117','Cárdigan Azul de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/117/816.jpg',33,140,249,0,'2025-02-28 20:31:08','L'),(32,1,'118','Cárdigan Azul de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/118/317.jpg',15,140,249,0,'2025-02-28 20:31:08','XL'),(33,1,'119','Cárdigan Azul de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/119/164.jpg',18,140,249,0,'2025-02-28 20:31:08','XXL'),(34,1,'120','Cárdigan Verde de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/120/497.jpg',19,140,249,3,'2025-02-28 20:31:08','S'),(35,1,'121','Cárdigan Verde de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/121/447.jpg',25,140,249,0,'2025-02-28 20:31:08','M'),(36,1,'122','Cárdigan Verde de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/122/504.jpg',26,140,249,0,'2025-03-11 20:39:43','L'),(37,1,'123','Cárdigan Verde de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/123/943.jpg',15,140,249,0,'2025-02-28 20:31:08','XL'),(38,1,'124','Cárdigan Verde de Alpaca con Botones Andino','ALMACEN 1','vistas/img/productos/124/174.jpg',10,140,249,0,'2025-02-28 20:31:08','XXL'),(41,1,'125','Cárdigan de alpaca con patrón geométrico inca','ALMACEN 1','vistas/img/productos/125/120.jpg',20,140,249,0,'2025-02-28 20:31:08','S'),(42,1,'126','Cárdigan de alpaca con patrón geométrico inca','ALMACEN 1','vistas/img/productos/126/398.jpg',21,140,249,0,'2025-03-17 18:18:25','M'),(43,1,'127','Cárdigan de alpaca con patrón geométrico inca','ALMACEN 1','vistas/img/productos/127/488.jpg',28,140,249,0,'2025-02-28 20:31:08','L'),(44,1,'128','Cárdigan de alpaca con patrón geométrico inca','ALMACEN 1','vistas/img/productos/128/464.jpg',30,140,249,0,'2025-02-28 20:31:08','XL'),(45,1,'129','Cárdigan de alpaca con patrón geométrico inca','ALMACEN 1','vistas/img/productos/129/337.jpg',19,140,249,0,'2025-02-28 20:31:08','XXL'),(46,2,'206','Suéter Andina de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/206/780.jpg',18,150,259,7,'2025-02-28 20:30:57','S'),(47,2,'207','Suéter Andina de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/207/159.jpg',20,150,259,0,'2025-02-28 20:30:57','M'),(48,2,'208','Suéter Andina de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/208/389.jpg',32,150,259,0,'2025-02-28 20:30:57','L'),(49,2,'209','Suéter Andina de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/209/965.jpg',20,150,259,0,'2025-02-28 20:30:57','XL'),(50,2,'210','Suéter Andina de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/210/232.jpg',23,150,259,0,'2025-03-11 20:32:19','XXL'),(51,2,'211','Suéter Marroncito de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/211/766.jpg',26,140,249,0,'2025-02-28 20:30:57','S'),(52,2,'212','Suéter Marroncito de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/212/659.jpg',20,140,249,0,'2025-02-28 20:30:57','M'),(53,2,'213','Suéter Marroncito de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/213/655.jpg',26,140,249,0,'2025-02-28 20:30:57','L'),(54,2,'214','Suéter Marroncito de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/214/431.jpg',18,140,249,6,'2025-03-11 20:24:26','XL'),(55,2,'215','Suéter Marroncito de Alpaca Con Cuello Redondo','ALMACEN 2','vistas/img/productos/215/148.jpg',17,140,249,1,'2025-03-11 20:31:47','XXL'),(56,2,'216','Suéter de alpaca sueño del norte','ALMACEN 2','vistas/img/productos/216/901.jpg',16,130,239,4,'2025-03-28 00:15:53','S'),(57,2,'217','Suéter de alpaca sueño del norte','ALMACEN 2','vistas/img/productos/217/578.jpg',29,130,239,1,'2025-03-28 00:15:53','M'),(58,2,'218','Suéter de alpaca sueño del norte','ALMACEN 2','vistas/img/productos/218/621.jpg',22,130,239,0,'2025-03-11 20:40:47','L'),(59,2,'219','Suéter de alpaca sueño del norte','ALMACEN 2','vistas/img/productos/219/353.jpg',15,130,239,0,'2025-03-02 19:20:23','XL'),(60,2,'220','Suéter de alpaca sueño del norte','ALMACEN 2','vistas/img/productos/220/413.jpg',24,130,239,0,'2025-03-02 19:20:23','XXL'),(61,3,'301','Bufanda Nieve de Alpaca','ALMACEN 3','vistas/img/productos/301/560.jpg',15,60,149,4,'2025-03-28 00:15:43','S'),(62,3,'302','Bufanda Nieve de Alpaca','ALMACEN 3','vistas/img/productos/302/627.jpg',22,60,149,3,'2025-03-28 00:31:25','M'),(63,3,'303','Bufanda Nieve de Alpaca','ALMACEN 3','vistas/img/productos/303/371.jpg',8,60,150,10,'2025-03-28 00:31:25','L');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` text NOT NULL,
  `usuario` text NOT NULL,
  `password` text NOT NULL,
  `correo` varchar(100) NOT NULL,
  `perfil` text NOT NULL,
  `foto` text NOT NULL,
  `estado` int(11) NOT NULL,
  `ultimo_login` datetime NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `token` varchar(255) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador1','admin','$2a$07$asxx54ahjppf45sd87a5aunxs9bkpyGmGE/.vekdjFg83yRec789S','administrador@gmail.com','Administrador','vistas/img/usuarios/admin/258.jpg',1,'2025-03-28 22:19:05','2025-03-29 03:19:05',NULL,NULL),(57,'Juan Fernando Urrego','juan','$2a$07$asxx54ahjppf45sd87a5au.U/M0caGNRi1j0bgxZqVwBDctNLt11O','juanurre@gmail.com','Vendedor','vistas/img/usuarios/juan/622.jpg',1,'2025-03-27 19:15:23','2025-03-28 00:15:23',NULL,NULL),(58,'Julio Gómez','julio','$2a$07$asxx54ahjppf45sd87a5aub5nGP0VwzLwk9mYKNeU59ocPXY/HdwK','gojulio2@gmai.com','Almacenero','vistas/img/usuarios/julio/404.jpg',1,'2025-04-02 22:25:02','2025-04-03 03:25:02',NULL,NULL),(63,'Juan','juannc','$2a$07$asxx54ahjppf45sd87a5auvAHKcjTiKVMaWrkR9n46GQjSbLF6jCG','juandiegonuc@gmail.com','Administrador','vistas/img/usuarios/juannc/573.jpg',1,'2025-03-27 18:38:42','2025-03-27 23:38:42','ff9c8dcc9ba9e361ea0ecfbd7b306428363e096d208ad5927e14d7e58ab3c7d0','2025-02-18 00:51:12'),(80,'Jeremy','jeremy54','$2a$07$asxx54ahjppf45sd87a5auvi5HoW0bD6Ef0ZywTkJtwy7fQyIOPAa','harlockjeremy54@gmail.com','Vendedor','vistas/img/usuarios/jeremy54/330.png',1,'2025-03-27 11:41:32','2025-03-27 16:41:32',NULL,NULL),(90,'prueba','prueba1','$2a$07$asxx54ahjppf45sd87a5auJRR6foEJ7ynpjisKtbiKJbvJsoQ8VPS','prueba1@gmail.com','Administrador','vistas/img/usuarios/prueba1/897.png',1,'0000-00-00 00:00:00','2025-03-25 21:40:55',NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `impuesto` float NOT NULL,
  `neto` float NOT NULL,
  `total` float NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (6,10005,1,125.64,698,823.64,'2025-01-28 03:26:36'),(8,10006,1,53.64,298,351.64,'2025-02-06 00:49:54'),(9,10007,1,179.28,996,1175.28,'2025-02-06 00:59:39'),(10,10008,1,89.64,498,587.64,'2025-02-06 01:24:28'),(11,10009,1,26.82,149,175.82,'2025-02-06 01:44:59'),(12,10010,1,326.34,1813,2139.34,'2025-02-06 01:51:53'),(13,10011,1,134.46,747,881.46,'2025-02-06 01:55:39'),(14,10012,1,44.82,249,293.82,'2025-02-06 02:54:07'),(15,10013,1,43.02,239,282.02,'2025-02-06 03:28:18'),(16,10014,1,54,300,354,'2025-02-14 05:01:41'),(20,10015,63,53.64,298,351.64,'2025-02-18 01:56:12'),(24,10016,1,27,150,177,'2025-02-24 17:41:24'),(29,10017,1,27,150,177,'2025-03-02 19:53:08'),(33,10018,63,127.8,710,837.8,'2025-03-27 23:57:31'),(35,10019,57,43.8,400,443.8,'2023-01-04 02:11:52'),(36,10020,57,80.46,447,527.46,'2025-03-28 00:15:43'),(37,10021,57,172.08,956,1128.08,'2025-03-28 00:15:53'),(38,10022,57,98.64,548,646.64,'2025-03-28 00:16:22'),(39,10023,57,43.8,400,443.8,'2024-03-14 02:22:46'),(40,10024,57,45,400,445,'2023-01-12 07:00:00'),(41,10025,57,27,250,277,'2023-01-20 07:00:00'),(42,10026,57,54,450,504,'2023-01-28 07:00:00'),(43,10027,57,18,150,168,'2023-02-03 07:00:00'),(44,10028,57,63,550,613,'2024-02-10 07:00:00'),(45,10029,57,9,80,89,'2024-02-17 07:00:00'),(46,10030,57,72,650,722,'2024-02-24 07:00:00'),(47,10031,57,45,400,445,'2023-08-12 07:00:00'),(48,10032,57,27,250,277,'2023-08-20 07:00:00'),(49,10033,57,54,450,504,'2023-08-28 07:00:00'),(50,10034,57,18,150,168,'2023-08-03 07:00:00'),(51,10035,57,63,550,613,'2024-08-10 07:00:00'),(52,10036,57,9,80,89,'2024-08-17 07:00:00'),(53,10037,57,72,650,722,'2024-08-24 07:00:00'),(54,10038,1,53.82,299,352.82,'2025-03-28 00:31:25');
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-02 21:20:21
