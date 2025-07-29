-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-07-2025 a las 06:31:58
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cajas_v3`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `armado`
--

CREATE TABLE `armado` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `partes` int(1) NOT NULL,
  `referencia` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `armado`
--

INSERT INTO `armado` (`id`, `nombre`, `partes`, `referencia`) VALUES
(1, 'Estandar (Manual sin suaje)', 1, ''),
(2, 'Boxlunch (lonchera con asa)', 1, ''),
(3, 'Tapa autoarmable - reforzada', 2, ''),
(4, 'Tapa autoarmable - tipo rosca', 2, ''),
(5, 'Estandar (Suajada)', 1, ''),
(6, 'Estandar con tapa (Manual sin suaje)', 2, ''),
(7, 'Estandar con tapa (Suajada)', 2, ''),
(8, 'Dona', 1, ''),
(9, 'Mailbox', 1, ''),
(10, 'Pizza', 1, ''),
(11, 'Taza autoarmable', 1, ''),
(12, 'Taza semiarmable', 1, ''),
(13, 'Zapatos', 1, ''),
(14, 'Charola autoarmable reforzada', 1, ''),
(15, 'Charola autoarmable con pestañas', 1, ''),
(16, 'Inserto sencillo', 1, ''),
(17, 'Inserto con pestañas', 1, ''),
(18, 'Lamina de cartón', 1, ''),
(19, 'Caja para palomitas', 1, ''),
(20, 'Bolsa con handhold', 1, ''),
(21, 'Per - Rallado, suajado', 1, ''),
(22, 'Per - Rallado, suajado, pegado', 1, ''),
(23, 'Per - Rallado, ranurado', 1, ''),
(24, 'Per - Rallado, ranurado, pegado', 1, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `armado_procesos`
--

CREATE TABLE `armado_procesos` (
  `id_armado` int(11) NOT NULL,
  `id_proceso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `armado_procesos`
--

INSERT INTO `armado_procesos` (`id_armado`, `id_proceso`) VALUES
(1, 2),
(1, 1),
(1, 3),
(1, 4),
(2, 1),
(2, 3),
(2, 5),
(2, 4),
(3, 1),
(3, 5),
(3, 4),
(4, 1),
(4, 5),
(4, 4),
(5, 1),
(5, 3),
(5, 5),
(5, 4),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(7, 1),
(7, 3),
(7, 5),
(7, 4),
(8, 1),
(8, 5),
(8, 4),
(9, 1),
(9, 5),
(9, 4),
(10, 1),
(10, 5),
(10, 4),
(11, 1),
(11, 5),
(11, 4),
(12, 1),
(12, 3),
(12, 5),
(12, 4),
(13, 1),
(13, 5),
(13, 4),
(14, 1),
(14, 5),
(14, 4),
(15, 1),
(15, 5),
(15, 4),
(16, 1),
(16, 5),
(16, 4),
(17, 1),
(17, 5),
(17, 4),
(18, 1),
(18, 4),
(19, 1),
(19, 3),
(19, 5),
(19, 4),
(20, 1),
(20, 3),
(20, 5),
(20, 4),
(21, 1),
(21, 5),
(21, 4),
(22, 1),
(22, 3),
(22, 5),
(22, 4),
(23, 1),
(23, 2),
(23, 4),
(24, 1),
(24, 2),
(24, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogo_productos`
--

CREATE TABLE `catalogo_productos` (
  `Nombre` varchar(255) DEFAULT NULL,
  `SKU` varchar(50) NOT NULL,
  `Color` varchar(20) DEFAULT NULL,
  `Precio_Unit` int(11) DEFAULT NULL,
  `Minimo_para_impresion` int(11) DEFAULT NULL,
  `Grupo` varchar(50) DEFAULT NULL,
  `Largo` decimal(5,2) DEFAULT NULL,
  `Ancho` decimal(5,2) DEFAULT NULL,
  `Alto` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `catalogo_productos`
--

INSERT INTO `catalogo_productos` (`Nombre`, `SKU`, `Color`, `Precio_Unit`, `Minimo_para_impresion`, `Grupo`, `Largo`, `Ancho`, `Alto`) VALUES
('Caja Bolsas de papel 22x8.5x15 cm blanca', 'B1B', 'Blanca', 7, 100, 'Bolsas de papel', 22.00, 8.50, 15.00),
('Caja Bolsas de papel 22x8.5x15 cm kraft', 'B1K', 'Kraft', 7, 100, 'Bolsas de papel', 22.00, 8.50, 15.00),
('Caja Bolsas de papel 15.5x8x22 cm blanca', 'B2B', 'Blanca', 8, 100, 'Bolsas de papel', 15.50, 8.00, 22.00),
('Caja Bolsas de papel 15.5x8x22 cm kraft', 'B2K', 'Kraft', 6, 100, 'Bolsas de papel', 15.50, 8.00, 22.00),
('Caja Bolsas de papel 20x10x29 cm kraft', 'B3K', 'Kraft', 9, 100, 'Bolsas de papel', 20.00, 10.00, 29.00),
('Caja Bolsas de papel 32.5x17.5x37 cm kraft', 'B4K', 'Kraft', 13, 100, 'Bolsas de papel', 32.50, 17.50, 37.00),
('Caja Bolsas de papel 39x15x41 cm kraft', 'B5K', 'Kraft', 14, 100, 'Bolsas de papel', 39.00, 15.00, 41.00),
('Caja Boxlunch 15x15x12.5 cm blanca', 'BL1B', 'Blanca', 14, 25, 'Boxlunch', 15.00, 15.00, 12.50),
('Caja Boxlunch 15x15x12.5 cm kraft', 'BL1K', 'Kraft', 11, 25, 'Boxlunch', 15.00, 15.00, 12.50),
('Caja Boxlunch 20x18x15 cm blanca', 'BL2B', 'Blanca', 18, 25, 'Boxlunch', 20.00, 18.00, 15.00),
('Caja Boxlunch 20x18x15 cm kraft', 'BL2K', 'Kraft', 16, 25, 'Boxlunch', 20.00, 18.00, 15.00),
('Caja Boxlunch 25x18x15 cm blanca', 'BL3B', 'Blanca', 21, 25, 'Boxlunch', 25.00, 18.00, 15.00),
('Caja Boxlunch 25x18x15 cm kraft', 'BL3K', 'Kraft', 17, 25, 'Boxlunch', 25.00, 18.00, 15.00),
('Caja Boxlunch 26x26x15 cm blanca', 'BL4B', 'Blanca', 27, 25, 'Boxlunch', 26.00, 26.00, 15.00),
('Caja Boxlunch 26x26x15 cm kraft', 'BL4K', 'Kraft', 24, 25, 'Boxlunch', 26.00, 26.00, 15.00),
('Caja Boxlunch 31x31x15 cm blanca', 'BL5B', 'Blanca', 29, 25, 'Boxlunch', 31.00, 31.00, 15.00),
('Caja Boxlunch 31x31x15 cm kraft', 'BL5K', 'Kraft', 26, 25, 'Boxlunch', 31.00, 31.00, 15.00),
('Caja Boxlunch 13x8x14.5 cm blanca', 'BL6B', 'Blanca', 8, 25, 'Boxlunch', 13.00, 8.00, 14.50),
('Caja Boxlunch 13x8x14.5 cm kraft', 'BL6K', 'Kraft', 8, 25, 'Boxlunch', 13.00, 8.00, 14.50),
('Caja Caple 5.5x3.5x5.5 cm kraft', 'CC1000K', 'Kraft', 2, 100, 'Caple', 5.50, 3.50, 5.50),
('Caja Caple 2.7x2.7x5.5 cm kraft', 'CC1009K', 'Kraft', 1, 100, 'Caple', 2.70, 2.70, 5.50),
('Caja Caple 3.5x2.5x5.5 cm kraft', 'CC1012K', 'Kraft', 1, 100, 'Caple', 3.50, 2.50, 5.50),
('Caja Caple 12x12x6 cm blanca', 'CC12126B', 'Blanca', 3, 100, 'Caple', 12.00, 12.00, 6.00),
('Caja Caple 12x12x6 cm kraft', 'CC12126K', 'Kraft', 3, 100, 'Caple', 12.00, 12.00, 6.00),
('Caja Caple 13x13x7 cm blanca', 'CC13137B', 'Blanca', 4, 100, 'Caple', 13.00, 13.00, 7.00),
('Caja Caple 13x13x7 cm kraft', 'CC13137K', 'Kraft', 4, 100, 'Caple', 13.00, 13.00, 7.00),
('Caja Caple 17x7x5 cm blanca', 'CC1775B', 'Blanca', 3, 100, 'Caple', 17.00, 7.00, 5.00),
('Caja Caple 18x7x4 cm blanca', 'CC1874B', 'Blanca', 1, 100, 'Caple', 18.00, 7.00, 4.00),
('Caja Caple 24x24x6 cm blanca', 'CC24246B', 'Blanca', 8, 100, 'Caple', 24.00, 24.00, 6.00),
('Caja Caple 24x24x6 cm kraft', 'CC24246K', 'Kraft', 8, 100, 'Caple', 24.00, 24.00, 6.00),
('Caja Caple 25x7x5 cm blanca', 'CC2575B', 'Blanca', 4, 100, 'Caple', 25.00, 7.00, 5.00),
('Caja Caple 32x22x7 cm blanca', 'CC32227B', 'Blanca', 14, 100, 'Caple', 32.00, 22.00, 7.00),
('Caja Caple 32x22x7 cm kraft', 'CC32227K', 'Kraft', 14, 100, 'Caple', 32.00, 22.00, 7.00),
('Caja Caple 32x22x7 cm blanca', 'CC32227VB', 'Blanca', 24, 100, 'Caple', 32.00, 22.00, 7.00),
('Caja Caple 4.5x4.5x10 cm kraft', 'CC4410K', 'Kraft', 3, 100, 'Caple', 4.50, 4.50, 10.00),
('Caja Caple 4.5x4.5x6 cm kraft', 'CC446K', 'Kraft', 2, 100, 'Caple', 4.50, 4.50, 6.00),
('Caja Caple 15.5x10x5.5 cm blanca', 'CC490PB', 'Blanca', 4, 100, 'Caple', 15.50, 10.00, 5.50),
('Caja Caple 17x13x5.5 cm blanca', 'CC720PB', 'Blanca', 6, 100, 'Caple', 17.00, 13.00, 5.50),
('Caja Caple 17.5x14x6 cm blanca', 'CC894PB', 'Blanca', 7, 100, 'Caple', 17.50, 14.00, 6.00),
('Caja Caple 9x7x3.5 cm blanca', 'CC973B', 'Blanca', 1, 100, 'Caple', 9.00, 7.00, 3.50),
('Caja Caple 21x14x7 cm blanca', 'CCF300PB', 'Blanca', 7, 100, 'Caple', 21.00, 14.00, 7.00),
('Caja Caple 6.5x6.5x4.5 cm blanca', 'CCJ30B', 'Blanca', 5, 100, 'Caple', 6.50, 6.50, 4.50),
('Caja Caple 9x8.5x4 cm blanca', 'CCJ34B', 'Blanca', 5, 100, 'Caple', 9.00, 8.50, 4.00),
('Caja Con tapa 20x20x20 cm kraft', 'CCT20K', 'Kraft', 19, 100, 'Con tapa', 20.00, 20.00, 20.00),
('Caja Con tapa 25x25x25 cm kraft', 'CCT25K', 'Kraft', 24, 100, 'Con tapa', 25.00, 25.00, 25.00),
('Caja Con tapa 30x30x30 cm kraft', 'CCT30K', 'Kraft', 30, 100, 'Con tapa', 30.00, 30.00, 30.00),
('Caja Con tapa 40x40x40 cm kraft', 'CCT40K', 'Kraft', 45, 100, 'Con tapa', 40.00, 40.00, 40.00),
('Caja Snacks 23x10x6 cm kraft', 'CD23K', 'Kraft', 5, 100, 'Snacks', 23.00, 10.00, 6.00),
('Caja Envios 10x10x3 cm blanca', 'CE10B', 'Blanca', 5, 100, 'Envios', 10.00, 10.00, 3.00),
('Caja Envios 10x10x3 cm kraft', 'CE10K', 'Kraft', 4, 100, 'Envios', 10.00, 10.00, 3.00),
('Caja Envios 12.5x12.5x5 cm blanca', 'CE12125B', 'Blanca', 10, 100, 'Envios', 12.50, 12.50, 5.00),
('Caja Envios 12.5x12.5x5 cm kraft', 'CE12125K', 'Kraft', 8, 100, 'Envios', 12.50, 12.50, 5.00),
('Caja Envios 12.5x8.5x6.5 cm blanca', 'CE1286B', 'Blanca', 8, 100, 'Envios', 12.50, 8.50, 6.50),
('Caja Envios 12.5x8.5x6.5 cm kraft', 'CE1286K', 'Kraft', 6, 100, 'Envios', 12.50, 8.50, 6.50),
('Caja Envios 15x7.5x5.5 cm blanca', 'CE15B', 'Blanca', 7, 100, 'Envios', 15.00, 7.50, 5.50),
('Caja Envios 15x7.5x5.5 cm kraft', 'CE15K', 'Kraft', 6, 100, 'Envios', 15.00, 7.50, 5.50),
('Caja Envios 16x11x9 cm kraft', 'CE16K', 'Kraft', 6, 25, 'Envios', 16.00, 11.00, 9.00),
('Caja Envios 18.5x9.5x9 cm blanca', 'CE1899B', 'Blanca', 10, 100, 'Envios', 18.50, 9.50, 9.00),
('Caja Envios 18.5x9.5x9 cm kraft', 'CE1899K', 'Kraft', 8, 100, 'Envios', 18.50, 9.50, 9.00),
('Caja Envios 19x18.5x9.5 cm blanca', 'CE19189B', 'Blanca', 13, 100, 'Envios', 19.00, 18.50, 9.50),
('Caja Envios 19x18.5x9.5 cm kraft', 'CE19189K', 'Kraft', 11, 100, 'Envios', 19.00, 18.50, 9.50),
('Caja Envios 19x13x6.5 cm kraft', 'CE19K', 'Kraft', 7, 100, 'Envios', 19.00, 13.00, 6.50),
('Caja Envios 58x29x29 cm kraft', 'CE20118K', 'Kraft', 33, 100, 'Envios', 58.00, 29.00, 29.00),
('Caja Envios 20x20x12 cm blanca', 'CE202012B', 'Blanca', 20, 100, 'Envios', 20.00, 20.00, 12.00),
('Caja Envios 20x20x12 cm kraft', 'CE202012K', 'Kraft', 16, 100, 'Envios', 20.00, 20.00, 12.00),
('Caja Envios 22x12x14 cm kraft', 'CE221214K', 'Kraft', 11, 25, 'Envios', 22.00, 12.00, 14.00),
('Caja Envios 22x16x5.5 cm blanca', 'CE22B', 'Blanca', 8, 25, 'Envios', 22.00, 16.00, 5.50),
('Caja Envios 22x16x5.5 cm kraft', 'CE22K', 'Kraft', 6, 25, 'Envios', 22.00, 16.00, 5.50),
('Caja Envios 25x25x12 cm blanca', 'CE252512B', 'Blanca', 22, 100, 'Envios', 25.00, 25.00, 12.00),
('Caja Envios 25x25x12 cm kraft', 'CE252512K', 'Kraft', 18, 100, 'Envios', 25.00, 25.00, 12.00),
('Caja Envios 26x17x9 cm kraft', 'CE26179K', 'Kraft', 13, 100, 'Envios', 26.00, 17.00, 9.00),
('Caja Envios 34x26x9 cm blanca', 'CE34269B', 'Blanca', 23, 100, 'Envios', 34.00, 26.00, 9.00),
('Caja Envios 34x26x9 cm kraft', 'CE34269K', 'Kraft', 19, 100, 'Envios', 34.00, 26.00, 9.00),
('Caja Envios 45x40x10 cm kraft', 'CE45K', 'Kraft', 28, 25, 'Envios', 45.00, 40.00, 10.00),
('Caja Envios 59x36x9 cm kraft', 'CE59K', 'Kraft', 29, 25, 'Envios', 59.00, 36.00, 9.00),
('Caja Envios 5x5x2 cm blanca', 'CE5B', 'Blanca', 4, 100, 'Envios', 5.00, 5.00, 2.00),
('Caja Envios 5x5x2 cm kraft', 'CE5K', 'Kraft', 3, 100, 'Envios', 5.00, 5.00, 2.00),
('Caja Envios 60x50x16 cm kraft', 'CE60K', 'Kraft', 55, 25, 'Envios', 60.00, 50.00, 16.00),
('Caja Envios 6x5x4 cm kraft', 'CE654K', 'Kraft', 4, 100, 'Envios', 6.00, 5.00, 4.00),
('Caja Envios 6x6x2 cm kraft', 'CE662K', 'Kraft', 4, 100, 'Envios', 6.00, 6.00, 2.00),
('Caja Mica 17x17x16 cm kraft', 'CM171716K', 'Kraft', 25, 100, 'Mica', 17.00, 17.00, 16.00),
('Caja Mica 21x21x18 cm kraft', 'CM212118K', 'Kraft', 33, 100, 'Mica', 21.00, 21.00, 18.00),
('Caja Mica 25x25x23 cm kraft', 'CM252523K', 'Kraft', 38, 100, 'Mica', 25.00, 25.00, 23.00),
('Caja Mica 25x25x33 cm kraft', 'CM252533K', 'Kraft', 45, 100, 'Mica', 25.00, 25.00, 33.00),
('Caja Mica 30x30x22.5 cm kraft', 'CM303022K', 'Kraft', 47, 100, 'Mica', 30.00, 30.00, 22.50),
('Caja Pizza 12.5x12.5x2.5 cm blanca', 'CP12122B', 'Blanca', 7, 100, 'Pizza', 12.50, 12.50, 2.50),
('Caja Pizza 12.5x12.5x2.5 cm kraft', 'CP12122K', 'Kraft', 6, 100, 'Pizza', 12.50, 12.50, 2.50),
('Caja Pizza 18.5x13x3 cm blanca', 'CP18133B', 'Blanca', 7, 100, 'Pizza', 18.50, 13.00, 3.00),
('Caja Pizza 18.5x13x3 cm kraft', 'CP18133K', 'Kraft', 5, 100, 'Pizza', 18.50, 13.00, 3.00),
('Caja Pizza 20.5x14.4x5.5 cm blanca', 'CP20145B', 'Blanca', 8, 100, 'Pizza', 20.50, 14.40, 5.50),
('Caja Pizza 20.5x14.4x5.5 cm kraft', 'CP20145K', 'Kraft', 6, 100, 'Pizza', 20.50, 14.40, 5.50),
('Caja Pizza 24.5x21.5x4.5 cm blanca', 'CP24214B', 'Blanca', 10, 25, 'Pizza', 24.50, 21.50, 4.50),
('Caja Pizza 6x6x2 cm blanca', 'CP662B', 'Blanca', 4, 100, 'Pizza', 6.00, 6.00, 2.00),
('Caja Pizza 6x6x2 cm kraft', 'CP662K', 'Kraft', 3, 100, 'Pizza', 6.00, 6.00, 2.00),
('Caja ReposteriÂ­a 15x15x12 cm blanca', 'CR151512B', 'Blanca', 8, 100, 'Reposteria', 15.00, 15.00, 12.00),
('Caja ReposteriÂ­a 15x15x12 cm kraft', 'CR151512K', 'Kraft', 8, 100, 'Reposteria', 15.00, 15.00, 12.00),
('Caja ReposteriÂ­a 20x20x12 cm blanca', 'CR202012B', 'Blanca', 12, 100, 'Reposteria', 20.00, 20.00, 12.00),
('Caja ReposteriÂ­a 20x20x12 cm kraft', 'CR202012K', 'Kraft', 12, 100, 'Reposteria', 20.00, 20.00, 12.00),
('Caja Taza 10.5x10.5x10.5 cm blanca', 'CT10CB', 'Blanca', 8, 100, 'Taza', 10.50, 10.50, 10.50),
('Caja Taza 10.5x10.5x10.5 cm kraft', 'CT10CK', 'Kraft', 6, 100, 'Taza', 10.50, 10.50, 10.50),
('Caja Taza 10x10x10 cm kraft', 'CT10VAK', 'Kraft', 6, 100, 'Taza', 10.00, 10.00, 10.00),
('Caja Taza 10.5x10.5x10.5 cm blanca', 'CT10VB', 'Blanca', 8, 100, 'Taza', 10.50, 10.50, 10.50),
('Caja Taza 10.5x10.5x10.5 cm kraft', 'CT10VK', 'Kraft', 6, 100, 'Taza', 10.50, 10.50, 10.50),
('Caja Taza 12x10x10 cm kraft', 'CT12VAK', 'Kraft', 7, 100, 'Taza', 12.00, 10.00, 10.00),
('Caja Taza 12x9x16 cm kraft', 'CT17VAK', 'Kraft', 8, 100, 'Taza', 12.00, 9.00, 16.00),
('Caja Vinos 15x15x32 cm kraft', 'CV151532K', 'Kraft', 20, 100, 'Vinos', 15.00, 15.00, 32.00),
('Caja Fajilla para vasos 7x6x0 cm kraft', 'CVKK', 'Kraft', 1, 100, 'Fajilla para vasos', 7.00, 6.00, 0.00),
('Caja Zapatos 18x17.5x5.5 cm blanca', 'CZ18165B', 'Blanca', 10, 100, 'Zapatos', 18.00, 17.50, 5.50),
('Caja Zapatos 18x17.5x5.5 cm kraft', 'CZ18165K', 'Kraft', 9, 100, 'Zapatos', 18.00, 17.50, 5.50),
('Caja Zapatos 30x25x11.5 cm kraft', 'CZ302511K', 'Kraft', 23, 100, 'Zapatos', 30.00, 25.00, 11.50),
('Caja Zapatos 45x41x14 cm kraft', 'CZ454114K', 'Kraft', 36, 100, 'Zapatos', 45.00, 41.00, 14.00),
('Caja Zapatos 46x25x14 cm kraft', 'CZ462514K', 'Kraft', 28, 100, 'Zapatos', 46.00, 25.00, 14.00),
('Caja Mailbox 13x13x5 cm blanca', 'MB13B', 'Blanca', 8, 100, 'Mailbox', 13.00, 13.00, 5.00),
('Caja Mailbox 13x13x5 cm kraft', 'MB13K', 'Kraft', 6, 100, 'Mailbox', 13.00, 13.00, 5.00),
('Caja Mailbox 18x10x2.5 cm blanca', 'MB18102B', 'Blanca', 8, 25, 'Mailbox', 18.00, 10.00, 2.50),
('Caja Mailbox 18x10x2.5 cm kraft', 'MB18102K', 'Kraft', 6, 25, 'Mailbox', 18.00, 10.00, 2.50),
('Caja Mailbox 18x12x4 cm kraft', 'MB18124K', 'Kraft', 7, 25, 'Mailbox', 18.00, 12.00, 4.00),
('Caja Mailbox 18x10x5.5 cm blanca', 'MB18B', 'Blanca', 8, 25, 'Mailbox', 18.00, 10.00, 5.50),
('Caja Mailbox 18x10x5.5 cm kraft', 'MB18K', 'Kraft', 8, 25, 'Mailbox', 18.00, 10.00, 5.50),
('Caja Mailbox 20x11x10 cm blanca', 'MB2011B', 'Blanca', 18, 25, 'Mailbox', 20.00, 11.00, 10.00),
('Caja Mailbox 20x11x10 cm kraft', 'MB2011K', 'Kraft', 16, 25, 'Mailbox', 20.00, 11.00, 10.00),
('Caja Mailbox 20x18x3.5 cm blanca', 'MB2018B', 'Blanca', 12, 25, 'Mailbox', 20.00, 18.00, 3.50),
('Caja Mailbox 20x18x3.5 cm kraft', 'MB2018K', 'Kraft', 10, 25, 'Mailbox', 20.00, 18.00, 3.50),
('Caja Mailbox 20x20x8 cm blanca', 'MB20B', 'Blanca', 15, 25, 'Mailbox', 20.00, 20.00, 8.00),
('Caja Mailbox 20x20x8 cm kraft', 'MB20K', 'Kraft', 13, 25, 'Mailbox', 20.00, 20.00, 8.00),
('Caja Mailbox 21x9x3.5 cm blanca', 'MB21B', 'Blanca', 7, 100, 'Mailbox', 21.00, 9.00, 3.50),
('Caja Mailbox 21x9x3.5 cm kraft', 'MB21K', 'Kraft', 6, 100, 'Mailbox', 21.00, 9.00, 3.50),
('Caja Mailbox 22x15x5 cm blanca', 'MB22B', 'Blanca', 9, 25, 'Mailbox', 22.00, 15.00, 5.00),
('Caja Mailbox 22x15x5 cm kraft', 'MB22K', 'Kraft', 7, 25, 'Mailbox', 22.00, 15.00, 5.00),
('Caja Mailbox 25x20x10 cm blanca', 'MB2510B', 'Blanca', 21, 25, 'Mailbox', 25.00, 20.00, 10.00),
('Caja Mailbox 25x20x10 cm kraft', 'MB2510K', 'Kraft', 17, 25, 'Mailbox', 25.00, 20.00, 10.00),
('Caja Mailbox 25x7x7 cm blanca', 'MB2577B', 'Blanca', 15, 25, 'Mailbox', 25.00, 7.00, 7.00),
('Caja Mailbox 25x20x6 cm blanca', 'MB25B', 'Blanca', 15, 25, 'Mailbox', 25.00, 20.00, 6.00),
('Caja Mailbox 25x20x6 cm kraft', 'MB25K', 'Kraft', 12, 25, 'Mailbox', 25.00, 20.00, 6.00),
('Caja Mailbox 26x9x9 cm blanca', 'MB26B', 'Blanca', 15, 25, 'Mailbox', 26.00, 9.00, 9.00),
('Caja Mailbox 26x9x9 cm kraft', 'MB26K', 'Kraft', 12, 25, 'Mailbox', 26.00, 9.00, 9.00),
('Caja Mailbox 29x16x9 cm blanca', 'MB29B', 'Blanca', 15, 25, 'Mailbox', 29.00, 16.00, 9.00),
('Caja Mailbox 29x16x9 cm kraft', 'MB29K', 'Kraft', 14, 25, 'Mailbox', 29.00, 16.00, 9.00),
('Caja Mailbox 30x20x10 cm blanca', 'MB3020B', 'Blanca', 22, 25, 'Mailbox', 30.00, 20.00, 10.00),
('Caja Mailbox 30x20x10 cm kraft', 'MB3020K', 'Kraft', 20, 25, 'Mailbox', 30.00, 20.00, 10.00),
('Caja Mailbox 30x23x5 cm blanca', 'MB30235B', 'Blanca', 17, 25, 'Mailbox', 30.00, 23.00, 5.00),
('Caja Mailbox 30x23x5 cm kraft', 'MB30235K', 'Kraft', 13, 25, 'Mailbox', 30.00, 23.00, 5.00),
('Caja Mailbox 30x30x10 cm blanca', 'MB30B', 'Blanca', 29, 25, 'Mailbox', 30.00, 30.00, 10.00),
('Caja Mailbox 30x30x10 cm kraft', 'MB30K', 'Kraft', 24, 25, 'Mailbox', 30.00, 30.00, 10.00),
('Caja Mailbox 33x32.5x16 cm kraft', 'MB33K', 'Kraft', 37, 25, 'Mailbox', 33.00, 32.50, 16.00),
('Caja Mailbox 35x25x7 cm blanca', 'MB35B', 'Blanca', 26, 25, 'Mailbox', 35.00, 25.00, 7.00),
('Caja Mailbox 40x30x10 cm blanca', 'MB40B', 'Blanca', 31, 25, 'Mailbox', 40.00, 30.00, 10.00),
('Caja Mailbox 40x30x10 cm kraft', 'MB40K', 'Kraft', 28, 25, 'Mailbox', 40.00, 30.00, 10.00),
('Caja Mailbox 43x29.5x6 cm kraft', 'MB43K', 'Kraft', 18, 25, 'Mailbox', 43.00, 29.50, 6.00),
('Caja Rosca 45x35x8 cm kraft', 'ROSCA45358K', 'Kraft', 23, 25, 'Rosca', 45.00, 35.00, 8.00),
('Caja Rosca 53.5x39.5x8 cm kraft', 'ROSCA53398K', 'Kraft', 26, 25, 'Rosca', 53.50, 39.50, 8.00),
('Caja Rosca 66x49x8 cm kraft', 'ROSCA66498K', 'Kraft', 29, 25, 'Rosca', 66.00, 49.00, 8.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material`
--

CREATE TABLE `material` (
  `id` int(11) NOT NULL,
  `tipo` varchar(10) NOT NULL,
  `clave` varchar(10) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `resistencia` decimal(5,2) NOT NULL,
  `grosor` decimal(5,2) NOT NULL,
  `precio` decimal(7,2) NOT NULL,
  `largo_max` int(3) NOT NULL,
  `ancho_max` int(3) NOT NULL,
  `actualizacion` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `material`
--

INSERT INTO `material` (`id`, `tipo`, `clave`, `descripcion`, `resistencia`, `grosor`, `precio`, `largo_max`, `ancho_max`, `actualizacion`) VALUES
(1, 'metro', 'CSK26', 'Corrugado sencillo C kraft 26 ECT', 26.00, 4.00, 9.70, 290, 240, '2024-10-17'),
(2, 'metro', 'CSK32', 'Corrugado sencillo C kraft 32 ECT', 32.00, 4.00, 10.50, 290, 240, '2024-10-17'),
(3, 'metro', 'DCK42', 'Doble corrugado BC kraft 42 ECT', 42.00, 7.00, 13.50, 290, 240, '2024-10-17'),
(4, 'metro', 'CSB26', 'Corrugado sencillo C blanco-kraft 26 ECT', 26.00, 4.00, 12.50, 290, 240, '2024-10-17'),
(5, 'metro', 'CSB32', 'Corrugado sencillo C blanco-kraft 32 ECT', 32.00, 4.00, 14.50, 290, 240, '2024-10-17'),
(6, 'metro', 'MCK26', 'Micro-corrugado E kraft 26 ECT', 26.00, 2.00, 10.15, 290, 240, '2024-10-17'),
(7, 'metro', 'MCB26', 'Micro-corrugado E blanco-kraft 26 ECT', 26.00, 2.00, 14.50, 290, 240, '2024-10-17'),
(8, 'metro', 'MCN26', 'Micro-corrugado E negro 26 ECT', 26.00, 2.00, 33.50, 290, 240, '2024-10-17'),
(12, 'lamina', 'zeni12', 'Cartulina sulfatada 12 pts', 12.00, 0.22, 6582.00, 71, 125, '2024-10-30'),
(13, 'lamina', 'zeni12', 'Cartulina sulfatada 12 pts', 12.00, 0.22, 8331.00, 90, 125, '2024-10-30'),
(17, 'lamina', 'zeni14', 'Cartulina sulfatada 14 pts', 14.00, 0.22, 7044.00, 71, 125, '2024-10-17'),
(18, 'lamina', 'zeni14', 'Cartulina sulfatada 14 pts', 14.00, 0.22, 8915.00, 90, 125, '2024-10-17'),
(19, 'lamina', 'capi12', 'Caple importado 12.4 pts', 12.40, 0.22, 5769.00, 71, 125, '2024-10-17'),
(20, 'lamina', 'capi12', 'Caple importado 12.4 pts', 12.40, 0.22, 7312.00, 90, 125, '2024-10-17'),
(21, 'lamina', 'capi13', 'Caple importado 13.78 pts', 13.78, 0.22, 6301.00, 71, 125, '2024-10-17'),
(22, 'lamina', 'capi13', 'Caple importado 13.78 pts', 13.78, 0.22, 7897.00, 90, 125, '2024-10-17'),
(23, 'lamina', 'capc12', 'Caple chileno 12 pts', 12.00, 0.22, 5338.00, 71, 125, '2024-10-17'),
(24, 'lamina', 'capc12', 'Caple chileno 12 pts', 12.00, 0.22, 6766.00, 90, 125, '2024-10-17'),
(25, 'lamina', 'capc14', 'Caple chileno 14 pts', 14.00, 0.22, 5934.00, 71, 125, '2024-10-17'),
(26, 'lamina', 'capc14', 'Caple chileno 14 pts', 14.00, 0.22, 7523.00, 90, 125, '2024-10-17'),
(27, 'metro', 'micareg', 'Mica regular', 0.00, 0.22, 50.00, 200, 55, '2024-10-23'),
(31, 'lamina', 'bondb120', 'Papel bond blanco 120 g', 120.00, 0.40, 2852.00, 102, 72, '2024-11-07'),
(32, 'lamina', 'papelk120', 'Papel color kraft 120 g', 120.00, 0.40, 3098.00, 125, 90, '2024-11-07'),
(33, 'lamina', 'zeni16', 'Cartulina sulfatada 16 pts', 16.00, 0.22, 8092.00, 71, 125, '2025-01-30'),
(34, 'lamina', 'zeni16', 'Cartulina sulfatada 16 pts', 16.00, 0.22, 10243.00, 90, 125, '2025-01-30'),
(35, 'lamina', 'zeni18', 'Cartulina sulfatada 18 pts', 18.00, 0.22, 8488.00, 71, 125, '2025-01-30'),
(36, 'lamina', 'zeni18', 'Cartulina sulfatada 18 pts', 18.00, 0.22, 10745.00, 90, 125, '2025-01-30'),
(37, 'lamina', 'zeni20', 'Cartulina sulfatada 20 pts', 20.00, 0.22, 9352.00, 71, 125, '2025-01-30'),
(38, 'lamina', 'zeni20', 'Cartulina sulfatada 20 pts', 20.00, 0.22, 11837.00, 90, 125, '2025-01-30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procesos`
--

CREATE TABLE `procesos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `precio` double(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `procesos`
--

INSERT INTO `procesos` (`id`, `nombre`, `precio`) VALUES
(1, 'Rallado', 400.00),
(2, 'Ranurado', 400.00),
(3, 'pegado', 1000.00),
(4, 'empaquetado', 400.00),
(5, 'suajado', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rangos_suajado`
--

CREATE TABLE `rangos_suajado` (
  `id` int(11) NOT NULL,
  `rango_inf` int(11) NOT NULL,
  `rango_sup` int(11) NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `id_proceso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rangos_suajado`
--

INSERT INTO `rangos_suajado` (`id`, `rango_inf`, `rango_sup`, `precio`, `id_proceso`) VALUES
(1, 0, 0, 0.00, 5),
(2, 1, 500, 250.00, 5),
(3, 501, 1000, 1000.00, 5),
(4, 1001, 2000, 750.00, 5),
(5, 2001, 4000, 1000.00, 5),
(6, 4001, 10000, 2000.00, 5),
(7, 10001, 20000, 5000.00, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valores`
--

CREATE TABLE `valores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `precio` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `valores`
--

INSERT INTO `valores` (`id`, `nombre`, `descripcion`, `precio`) VALUES
(1, 'Utilidad', 'Porcentaje de utilidad', 25.00),
(2, 'Merma', 'Porcentaje de merma', 10.00),
(3, 'iva', 'Porcentaje de IVA', 16.00),
(4, 'Suaje', 'Costo del suaje por cm lineal', 4.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vendedores`
--

CREATE TABLE `vendedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(10) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `correo` varchar(30) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `activo` varchar(2) NOT NULL,
  `admin` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vendedores`
--

INSERT INTO `vendedores` (`id`, `nombre`, `apellido`, `correo`, `password`, `activo`, `admin`) VALUES
(1, 'Pablo', 'Miranda', 'pablomiranda@millop.com', NULL, 's', 's'),
(2, 'Ericka', 'Miranda', 'ventas2@millop.com', NULL, 's', ''),
(3, 'Gerardo', 'Xicotencatl', 'ventas3@millop.com', NULL, 's', ''),
(4, 'Oscar', 'Ortiz', 'ventas4@millop.com', NULL, 's', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT current_timestamp(),
  `monto_venta` decimal(9,2) NOT NULL,
  `monto_envio` decimal(8,2) DEFAULT NULL,
  `monto_suaje` decimal(8,2) DEFAULT NULL,
  `cuenta` varchar(10) NOT NULL,
  `iva` tinyint(1) NOT NULL DEFAULT 1,
  `vendedor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `armado_procesos`
--
ALTER TABLE `armado_procesos`
  ADD KEY `id_armado` (`id_armado`),
  ADD KEY `id_proceso` (`id_proceso`);

--
-- Indices de la tabla `procesos`
--
ALTER TABLE `procesos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rangos_suajado`
--
ALTER TABLE `rangos_suajado`
  ADD KEY `id_proceso` (`id_proceso`);

--
-- Indices de la tabla `valores`
--
ALTER TABLE `valores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD KEY `vendedor` (`vendedor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `procesos`
--
ALTER TABLE `procesos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `valores`
--
ALTER TABLE `valores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
