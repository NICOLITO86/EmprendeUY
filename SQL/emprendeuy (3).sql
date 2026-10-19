-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/10/2026 às 15:36
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `emprendeuy`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administrador`
--

CREATE TABLE `administrador` (
  `Cedula` int(11) NOT NULL,
  `Nombre` varchar(60) NOT NULL,
  `Apellido` varchar(60) NOT NULL,
  `Correo` varchar(50) NOT NULL,
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `bloqueo_temporal`
--

CREATE TABLE `bloqueo_temporal` (
  `idBloqueo` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `IP` varchar(45) NOT NULL,
  `BloqueadoHasta` datetime NOT NULL,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `bloqueo_temporal`
--

INSERT INTO `bloqueo_temporal` (`idBloqueo`, `Cedula`, `IP`, `BloqueadoHasta`, `FechaCreacion`) VALUES
(1, 5, '::1', '2026-09-02 20:38:17', '2026-09-02 20:28:17'),
(2, 6, '::1', '2026-09-03 08:16:21', '2026-09-03 08:06:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `ci` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `ci`, `id`, `cantidad`, `fecha_agregado`) VALUES
(3, 2, 17, 1, '2026-08-27 12:39:20'),
(4, 2, 20, 1, '2026-08-30 00:36:36'),
(5, 23456789, 22, 1, '2026-10-13 19:44:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

CREATE TABLE `cliente` (
  `Nombre` varchar(60) NOT NULL,
  `Apellido` varchar(60) NOT NULL,
  `Correo` varchar(50) NOT NULL,
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL,
  `Cedula` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cliente`
--

INSERT INTO `cliente` (`Nombre`, `Apellido`, `Correo`, `Genero`, `Cedula`) VALUES
('2', '2@2', '2', 'Femenino', 2),
('asd', 'asd', 'asd@asd.com', 'Femenino', 56),
('asd1', 'asd1', 'nicocalixto26@gmail.com', 'Masculino', 23456789);

-- --------------------------------------------------------

--
-- Estrutura para tabela `codigo_verificacion`
--

CREATE TABLE `codigo_verificacion` (
  `idCodigo` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `Tipo` enum('verificacion_correo','recuperacion_contrasena') NOT NULL,
  `CodigoHash` varchar(255) NOT NULL,
  `Intentos` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `Usado` tinyint(1) NOT NULL DEFAULT 0,
  `Expira` datetime NOT NULL,
  `IP` varchar(45) NOT NULL,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `codigo_verificacion`
--

INSERT INTO `codigo_verificacion` (`idCodigo`, `Cedula`, `Tipo`, `CodigoHash`, `Intentos`, `Usado`, `Expira`, `IP`, `FechaCreacion`) VALUES
(1, 23456789, 'verificacion_correo', '$2y$10$eC86rixjGcb/sTHHk/HFFevsg7JlAvL2rAIbsSg2qxiWHyjhdZ5hG', 0, 1, '2026-10-13 18:41:11', '::1', '2026-10-13 18:31:11'),
(2, 23456789, 'verificacion_correo', '$2y$10$kd8cXEG3Dzijxo8iHBUgAO7wnejP3KqVAOV85Am9I58vQ/hjt2uW6', 0, 1, '2026-10-13 18:43:40', '::1', '2026-10-13 18:33:40'),
(3, 23456789, 'verificacion_correo', '$2y$10$VCymjJbZTDpjs4MEX2cB2OuAfJKuOi5ncsrLH94ym/r5l0S9MmGTO', 0, 1, '2026-10-13 18:48:57', '::1', '2026-10-13 18:38:57'),
(4, 23456789, 'verificacion_correo', '$2y$10$GLDissRE9b3uoNN5TmyL.eyCUWlwqxfgQIb74vHiimhe5FZMMOboy', 0, 1, '2026-10-13 19:12:18', '::1', '2026-10-13 19:02:18'),
(7, 23456789, 'recuperacion_contrasena', '$2y$10$xPLXTO/pHQKsFCaEI9gw..UurRBrQvvAWd0qw3p/WbU3TC2sNLJV2', 0, 1, '2026-10-16 10:48:31', '::1', '2026-10-16 10:38:31'),
(8, 23456789, 'recuperacion_contrasena', '$2y$10$XZdoQA0D4c1r9UBEg2y7Uu9cibDdN81cxaz2ijv4zFB71Lc7XVE/2', 0, 1, '2026-10-16 10:57:50', '::1', '2026-10-16 10:47:50'),
(9, 57683215, 'recuperacion_contrasena', '$2y$10$byZWGOSkz9fTtomckUNGLuJVTnG8HIwIFdE1T/eddDZPLaztI9yzG', 0, 0, '2026-10-16 10:59:43', '::1', '2026-10-16 10:49:43'),
(10, 23456789, 'recuperacion_contrasena', '$2y$10$NLDZu/MPYThUqCFM7KKdKe7R2NHNUxp6/NkttG5BYbsBI.ERGlcBi', 0, 1, '2026-10-16 11:04:02', '::1', '2026-10-16 10:54:02'),
(11, 23456789, 'recuperacion_contrasena', '$2y$10$pxV8gG6kVBds12Zqt4uJP.4ZlNjrkpW56BL8eSWCGp45Z3Il7mRKS', 0, 1, '2026-10-16 11:06:10', '::1', '2026-10-16 10:56:10'),
(12, 23456789, 'recuperacion_contrasena', '$2y$10$eoY7eHJSLD1upPeb94qj0.AMoucO36iJJ7YsXflc1bncmtwBRWx5e', 0, 0, '2026-10-16 11:09:27', '::1', '2026-10-16 10:59:27'),
(13, 58756565, 'verificacion_correo', '$2y$10$JygmOV1n25BCI4LfpRBkzOTtH8SEeoqerGBaY0MgyOY30KjGJ5Zri', 0, 1, '2026-10-16 11:12:14', '::1', '2026-10-16 11:02:14'),
(14, 57252305, 'verificacion_correo', '$2y$10$aC3H/pKD52wzbRzngg0iL.kKH0YQ.6ttyX0lDH0RSsieLOh2Xht3q', 0, 1, '2026-10-19 10:01:15', '::1', '2026-10-19 09:51:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `compra`
--

CREATE TABLE `compra` (
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `Fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprendedor`
--

CREATE TABLE `emprendedor` (
  `Cedula` int(11) NOT NULL,
  `Nombre` varchar(60) NOT NULL,
  `Apellido` varchar(60) NOT NULL,
  `Correo` varchar(50) NOT NULL,
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `emprendedor`
--

INSERT INTO `emprendedor` (`Cedula`, `Nombre`, `Apellido`, `Correo`, `Genero`) VALUES
(1, '1', '1@1', '1', 'Masculino'),
(565, 'asd5', 'asd5', 'asd@asd5.com', 'Femenino'),
(5655, 'asd56', 'asd56', 'asd@asd56.com', 'Femenino'),
(57252305, 'Lucia', 'Camerota', 'cl528991@gmail.com', 'Femenino');

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprendimiento`
--

CREATE TABLE `emprendimiento` (
  `Nombre` varchar(20) NOT NULL,
  `Descripcion` text NOT NULL,
  `Foto` varchar(255) DEFAULT NULL,
  `ID` int(11) NOT NULL,
  `cedula` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `emprendimiento`
--

INSERT INTO `emprendimiento` (`Nombre`, `Descripcion`, `Foto`, `ID`, `cedula`) VALUES
('emprendimiento1', 'de algo', '3.png', 3, 1),
('emprendimiento1', 'de algo', '4.png', 4, 6),
('87', '87', '5.png', 5, 565),
('76', '76', '6.png', 6, 5655),
('Swap', 'Trata de indumetaria especialmente para publicar outfits completos o una prenda que seguramente no encuentres en ningun otro lugar.', '7.jpg', 7, 57252305);

-- --------------------------------------------------------

--
-- Estrutura para tabela `intento_login`
--

CREATE TABLE `intento_login` (
  `idIntento` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `IP` varchar(45) NOT NULL,
  `Exitoso` tinyint(1) NOT NULL,
  `FechaIntento` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `intento_login`
--

INSERT INTO `intento_login` (`idIntento`, `Cedula`, `IP`, `Exitoso`, `FechaIntento`) VALUES
(1, 5, '::1', 0, '2026-09-02 20:28:05'),
(2, 5, '::1', 0, '2026-09-02 20:28:12'),
(3, 5, '::1', 0, '2026-09-02 20:28:14'),
(4, 5, '::1', 0, '2026-09-02 20:28:16'),
(5, 5, '::1', 0, '2026-09-02 20:28:17'),
(6, 6, '::1', 0, '2026-09-03 08:06:06'),
(7, 6, '::1', 0, '2026-09-03 08:06:11'),
(8, 6, '::1', 0, '2026-09-03 08:06:15'),
(9, 6, '::1', 0, '2026-09-03 08:06:18'),
(10, 6, '::1', 0, '2026-09-03 08:06:21'),
(11, 1, '::1', 1, '2026-09-03 08:06:35'),
(12, 2, '::1', 1, '2026-09-03 08:41:44'),
(13, 57683215, '::1', 1, '2026-09-03 08:42:15'),
(14, 1, '::1', 1, '2026-09-03 11:05:47'),
(15, 1, '::1', 1, '2026-09-03 11:39:12'),
(16, 57683215, '::1', 1, '2026-09-03 11:40:51'),
(17, 57683215, '::1', 1, '2026-09-10 12:28:53'),
(18, 57683215, '::1', 1, '2026-09-10 12:33:42'),
(19, 57683215, '::1', 1, '2026-09-10 12:42:51'),
(20, 57683215, '::1', 1, '2026-09-10 12:59:36'),
(21, 57683215, '::1', 1, '2026-09-10 13:01:01'),
(22, 57683215, '::1', 1, '2026-09-10 13:01:41'),
(23, 6, '::1', 1, '2026-09-10 13:08:02'),
(24, 57683215, '::1', 1, '2026-09-10 13:11:04'),
(25, 1, '::1', 1, '2026-09-11 08:34:48'),
(26, 1, '::1', 1, '2026-09-11 08:34:56'),
(27, 2, '::1', 1, '2026-09-11 08:35:06'),
(28, 1, '::1', 1, '2026-09-11 08:55:36'),
(29, 1, '::1', 1, '2026-09-11 09:02:17'),
(30, 57683215, '::1', 1, '2026-09-11 09:02:36'),
(31, 57683215, '::1', 0, '2026-09-11 09:07:01'),
(32, 20, '::1', 1, '2026-09-11 09:10:37'),
(33, 1, '::1', 1, '2026-09-11 09:13:10'),
(34, 1, '::1', 1, '2026-09-11 09:13:22'),
(35, 1, '::1', 1, '2026-09-11 09:13:43'),
(36, 1, '::1', 1, '2026-09-11 19:57:53'),
(37, 2, '::1', 1, '2026-09-11 19:58:31'),
(38, 57683215, '::1', 1, '2026-09-11 19:58:47'),
(39, 87654321, '::1', 0, '2026-10-13 19:41:57'),
(40, 23456789, '::1', 1, '2026-10-13 19:43:09'),
(41, 12345678, '::1', 0, '2026-10-13 19:44:17'),
(42, 12345678, '::1', 0, '2026-10-13 19:44:25'),
(43, 23456789, '::1', 1, '2026-10-13 19:44:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_acceso_admin`
--

CREATE TABLE `log_acceso_admin` (
  `idLog` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `Recurso` varchar(150) NOT NULL,
  `Resultado` enum('permitido','denegado') NOT NULL,
  `IP` varchar(45) NOT NULL,
  `FechaAcceso` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `log_acceso_admin`
--

INSERT INTO `log_acceso_admin` (`idLog`, `Cedula`, `Recurso`, `Resultado`, `IP`, `FechaAcceso`) VALUES
(1, 1, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-09-03 08:13:00'),
(2, 1, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-09-03 08:33:41'),
(3, 1, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-09-03 08:39:22'),
(4, 57683215, '/EmprendeUY/HTML/paneladm.php', 'permitido', '::1', '2026-09-03 08:42:15'),
(5, 1, '/EmprendeUY/HTML/paneladm.php', 'denegado', '::1', '2026-09-03 11:40:27'),
(6, 57683215, '/EmprendeUY/HTML/paneladm.php', 'permitido', '::1', '2026-09-03 11:40:51'),
(7, 1, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-09-11 08:57:05'),
(8, 1, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-09-11 08:57:07'),
(9, 1, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-09-11 08:57:08'),
(10, 1, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-09-11 08:57:08'),
(11, 1, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-09-11 08:57:08'),
(12, 57683215, '/EmprendeUY/PHP/admin.php:buscar_usuario', 'permitido', '::1', '2026-09-11 09:03:51'),
(13, 57683215, '/EmprendeUY/PHP/admin.php:buscar_emprendimiento', 'permitido', '::1', '2026-09-11 09:04:00'),
(14, 57683215, '/EmprendeUY/PHP/admin.php:mostrar_todo', 'permitido', '::1', '2026-09-11 09:04:02'),
(15, 57683215, '/EmprendeUY/PHP/admin.php:buscar_emprendimiento', 'permitido', '::1', '2026-09-11 09:05:31'),
(17, 565, '/EmprendeUY/PHP/crear_Emprendimiento.php', 'permitido', '::1', '2026-09-12 15:24:20'),
(18, 5655, '/EmprendeUY/PHP/crear_Emprendimiento.php', 'permitido', '::1', '2026-09-12 15:36:21'),
(19, 57252305, '/EmprendeUY/PHP/crear_Emprendimiento.php', 'permitido', '::1', '2026-10-19 10:14:32'),
(20, 57252305, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-10-19 10:15:20'),
(21, 57252305, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-10-19 10:16:06'),
(22, 57252305, '/EmprendeUY/PHP/cambiar_estado.php', 'permitido', '::1', '2026-10-19 10:16:13'),
(23, 57252305, '/EmprendeUY/PHP/cambiar_estado.php', 'permitido', '::1', '2026-10-19 10:16:14'),
(24, 57252305, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-10-19 10:22:20'),
(25, 57252305, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-10-19 10:26:27'),
(26, 57252305, '/EmprendeUY/PHP/cambiar_estado.php', 'permitido', '::1', '2026-10-19 10:26:37'),
(27, 57252305, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-10-19 10:32:56'),
(28, 57252305, '/EmprendeUY/PHP/cambiar_estado.php', 'permitido', '::1', '2026-10-19 10:33:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `precio` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id` int(11) NOT NULL,
  `Id_emprendimiento` int(110) NOT NULL,
  `status` enum('Pausada','Bloqueada','Activa') NOT NULL DEFAULT 'Pausada',
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(20,0) NOT NULL,
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL,
  `categoria` enum('Ropa','Hogar','Tecnologia','Carpinteria','Herreria','Higiene','Deportes') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `publicaciones`
--

INSERT INTO `publicaciones` (`id`, `Id_emprendimiento`, `status`, `titulo`, `descripcion`, `precio`, `fecha_publicacion`, `foto`, `categoria`) VALUES
(17, 3, 'Pausada', '1', '1', 1, '2026-08-26 00:37:01', '17.png', 'Ropa'),
(18, 3, 'Activa', '1', '1', 1, '2026-08-29 18:30:04', '18.jpg', 'Tecnologia'),
(19, 3, 'Activa', 'asd', '1', 1, '2026-08-29 18:30:26', '19.jpg', 'Tecnologia'),
(20, 3, 'Activa', 'asd', '1', 1, '2026-08-29 18:30:27', '20.jpg', 'Tecnologia'),
(21, 3, 'Pausada', 'asd', 'asd', 21, '2026-08-29 18:31:13', '21.jpg', 'Tecnologia'),
(22, 3, 'Activa', 'prueba2', '1', 111, '2026-09-03 08:13:00', '22.png', ''),
(23, 3, 'Pausada', 'we', 'we', 32, '2026-09-03 08:39:22', '23.png', 'Hogar'),
(24, 7, 'Activa', 'Outfit completo', 'Top: M en blanco  Pollera short: S-M en marron y negro  Botas: 39-41 en marron,negro y blanco   Bolso: Marron, rojo y negro', 1300, '2026-10-19 10:15:20', '24.jpg', ''),
(25, 7, 'Activa', 'Falda en V', 'Pollera short: S-M-L en gris,negro,blanco y rojo', 450, '2026-10-19 10:16:06', '25.jpg', ''),
(26, 7, 'Pausada', 'Outfit tipo noche de invierno', 'Talle unico y en colores blanco,negro y marron, las medias vienen tipo piel, de color negro y beige', 1400, '2026-10-19 10:22:20', '26.jpg', ''),
(27, 7, 'Activa', 'Outfit Noche de Invierno', 'Talle unico y en colores blanco,negro y marron, las medias vienen tipo piel, de color negro y beige, botas: del 37 al 41', 1400, '2026-10-19 10:26:27', '27.jpg', ''),
(28, 7, 'Activa', 'Outfit Elegante', 'Talle unico dos piezas, botas del 37 al 41, medias polares de color beige, negro y marron claro', 1340, '2026-10-19 10:32:56', '28.jpg', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `Cedula` int(11) NOT NULL,
  `Nombre` varchar(60) NOT NULL,
  `Apellido` varchar(60) NOT NULL,
  `Fecha_Nacimiento` date NOT NULL,
  `Edad` int(2) NOT NULL,
  `Correo` varchar(60) NOT NULL,
  `CorreoVerificado` tinyint(1) NOT NULL DEFAULT 0,
  `Contraseña` varchar(100) NOT NULL,
  `Num_Telefono` varchar(20) NOT NULL,
  `Domicilio` varchar(60) NOT NULL,
  `Calle` varchar(100) NOT NULL,
  `Manzana` varchar(60) NOT NULL,
  `Solar` varchar(60) NOT NULL,
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL,
  `Rol` enum('administrador','cliente','emprendedor','usuario') NOT NULL DEFAULT 'usuario',
  `Activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`Cedula`, `Nombre`, `Apellido`, `Fecha_Nacimiento`, `Edad`, `Correo`, `CorreoVerificado`, `Contraseña`, `Num_Telefono`, `Domicilio`, `Calle`, `Manzana`, `Solar`, `Genero`, `Rol`, `Activo`) VALUES
(1, '1', '1', '2026-07-27', 0, '1@1', 1, '$2y$10$KqhnlryC.4IL6m29L2cKgOAe4m0e41l6i..AWOZOg8lKQ3iEVbwaK', '1', '', '', '', '', 'Masculino', 'emprendedor', 1),
(2, '2', '2', '2026-07-29', 0, '2@2', 1, '$2y$10$AB0mezcFWvCda20Gwrmp9.fxzOYOsoysZvRvNo/YEayG6WVwhHtCO', '2', '', '', '', '', 'Femenino', 'cliente', 1),
(56, 'asd', 'asd', '2026-09-18', 0, 'asd@asd.com', 1, '$2y$10$N2ejuJKjMEvS4MwvoFJGO.i6SMUlkvWLh5EuBxB4GExOilOUuh6hK', '67', '', '', '', '', 'Femenino', 'cliente', 1),
(565, 'asd5', 'asd5', '2026-09-18', 0, 'asd@asd5.com', 1, '$2y$10$u47fhbhIxmdxCxUsHv7XpuStgFocbsBAP0j6PrOZ17FQuy4kwLbxq', '675', '', '', '', '', 'Femenino', 'emprendedor', 1),
(5655, 'asd56', 'asd56', '2026-09-18', 0, 'asd@asd56.com', 1, '$2y$10$uPFJ.z5HvAhEoP8kGKW4tesK0AYJ9D.vUbYqYk28E/m1Za6Wvkxm6', '6756', '', '', '', '', 'Femenino', 'emprendedor', 1),
(23456789, 'asd1', 'asd1', '1999-02-02', 27, 'nicocalixto26@gmail.com', 1, '$2y$10$AOwqqOcOayZSaOCWACsj1OazM8KU21IcsuOjZTqPSqRot992LQKPa', '123456789', '', '', '', '', 'Masculino', 'cliente', 1),
(57252305, 'Lucia', 'Camerota', '2008-04-24', 18, 'cl528991@gmail.com', 1, '$2y$10$u32mBSmq97ihH2APYUjCiOe3LpUa/xAP2dPHyQS5wilppevjAMfL6', '094172302', '', '', '', '', 'Femenino', 'emprendedor', 1),
(57683215, 'nicolas', 'calixto', '2026-06-19', 0, 'nicolito3215@gmail.com', 1, '$2y$10$7B/yrGYGEYmGOW/ABrClbuiqXMhPOh.KxFwBCTRhbJCUNIt7SoqNC', '094197336', '', '', '', '', 'Masculino', 'administrador', 1),
(58756565, 'Sofia', 'Lopez', '1999-11-19', 26, 'lulalopez1977h2r@gmail.com', 1, '$2y$10$EfIICHFJhkGiUNnzzl1WNO/3tNg9rbkyCZfLKKb.p5kiBBtgFQDpu', '098765434', '', '', '', '', 'Femenino', 'emprendedor', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Índices de tabela `bloqueo_temporal`
--
ALTER TABLE `bloqueo_temporal`
  ADD PRIMARY KEY (`idBloqueo`),
  ADD KEY `idx_cedula_hasta` (`Cedula`,`BloqueadoHasta`);

--
-- Índices de tabela `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD UNIQUE KEY `uniq_carrito_usuario_producto` (`ci`,`id`),
  ADD KEY `carrito_ibfk_1` (`id`);

--
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Índices de tabela `codigo_verificacion`
--
ALTER TABLE `codigo_verificacion`
  ADD PRIMARY KEY (`idCodigo`),
  ADD KEY `idx_codigo_cedula_tipo` (`Cedula`,`Tipo`,`Usado`);

--
-- Índices de tabela `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `Cedula` (`Cedula`);

--
-- Índices de tabela `emprendedor`
--
ALTER TABLE `emprendedor`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Índices de tabela `emprendimiento`
--
ALTER TABLE `emprendimiento`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `intento_login`
--
ALTER TABLE `intento_login`
  ADD PRIMARY KEY (`idIntento`),
  ADD KEY `idx_cedula_fecha` (`Cedula`,`FechaIntento`);

--
-- Índices de tabela `log_acceso_admin`
--
ALTER TABLE `log_acceso_admin`
  ADD PRIMARY KEY (`idLog`),
  ADD KEY `idx_cedula_fecha` (`Cedula`,`FechaAcceso`);

--
-- Índices de tabela `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`);

--
-- Índices de tabela `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `bloqueo_temporal`
--
ALTER TABLE `bloqueo_temporal`
  MODIFY `idBloqueo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `codigo_verificacion`
--
ALTER TABLE `codigo_verificacion`
  MODIFY `idCodigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `compra`
--
ALTER TABLE `compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `emprendimiento`
--
ALTER TABLE `emprendimiento`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `intento_login`
--
ALTER TABLE `intento_login`
  MODIFY `idIntento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de tabela `log_acceso_admin`
--
ALTER TABLE `log_acceso_admin`
  MODIFY `idLog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `administrador_ibfk_1` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

--
-- Restrições para tabelas `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `FK_Cliente_Usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

--
-- Restrições para tabelas `codigo_verificacion`
--
ALTER TABLE `codigo_verificacion`
  ADD CONSTRAINT `fk_codigo_usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

--
-- Restrições para tabelas `emprendedor`
--
ALTER TABLE `emprendedor`
  ADD CONSTRAINT `FK_Emprendedor_Usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

--
-- Restrições para tabelas `log_acceso_admin`
--
ALTER TABLE `log_acceso_admin`
  ADD CONSTRAINT `fk_log_usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
