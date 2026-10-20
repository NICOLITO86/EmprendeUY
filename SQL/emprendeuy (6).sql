-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-10-2026 a las 02:33:20
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
-- Base de datos: `emprendeuy`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
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
-- Estructura de tabla para la tabla `bloqueo_temporal`
--

CREATE TABLE `bloqueo_temporal` (
  `idBloqueo` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `IP` varchar(45) NOT NULL,
  `BloqueadoHasta` datetime NOT NULL,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bloqueo_temporal`
--

INSERT INTO `bloqueo_temporal` (`idBloqueo`, `Cedula`, `IP`, `BloqueadoHasta`, `FechaCreacion`) VALUES
(1, 5, '::1', '2026-09-02 20:38:17', '2026-09-02 20:28:17'),
(2, 6, '::1', '2026-09-03 08:16:21', '2026-09-03 08:06:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `ci` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `ci`, `id`, `cantidad`, `fecha_agregado`) VALUES
(3, 2, 17, 1, '2026-08-27 12:39:20'),
(4, 2, 20, 1, '2026-08-30 00:36:36'),
(10, 23456789, 18, 1, '2026-10-16 08:44:16'),
(11, 23456789, 19, 1, '2026-10-16 08:44:22'),
(12, 23456789, 20, 1, '2026-10-16 08:44:23'),
(13, 23456789, 22, 1, '2026-10-16 08:44:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `Nombre` varchar(60) NOT NULL,
  `Apellido` varchar(60) NOT NULL,
  `Correo` varchar(50) NOT NULL,
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL,
  `Cedula` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`Nombre`, `Apellido`, `Correo`, `Genero`, `Cedula`) VALUES
('asd1', 'asd1', 'nicocalixto26@gmail.com', 'Femenino', 23456789);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigo_verificacion`
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
-- Volcado de datos para la tabla `codigo_verificacion`
--

INSERT INTO `codigo_verificacion` (`idCodigo`, `Cedula`, `Tipo`, `CodigoHash`, `Intentos`, `Usado`, `Expira`, `IP`, `FechaCreacion`) VALUES
(5, 23456789, 'verificacion_correo', '$2y$10$HL.OUUc5n3W6lw7O1bCI1exGD6Z7P6Tcfu9K4ujUnfdLpmdiVLl3a', 0, 1, '2026-10-14 10:43:24', '::1', '2026-10-14 10:33:24'),
(6, 23456789, 'recuperacion_contrasena', '$2y$10$QF85TRBsLS40W92iY6Y5LOVlzy4uCORzc2iN7b3WubrLw2LvERUjq', 0, 1, '2026-10-16 08:17:56', '::1', '2026-10-16 08:07:56'),
(8, 23456789, 'recuperacion_contrasena', '$2y$10$UukDr1iYWrouzhCmuVVjC.S6.at/.z54DtUUz7lCrMkEiLnKqXC4u', 0, 1, '2026-10-16 10:42:25', '::1', '2026-10-16 10:32:25'),
(9, 23456789, 'recuperacion_contrasena', '$2y$10$DmPZTOX5PxtbzqcElLoB9.ZNbQ7QruuhBvOvzvjfTE7BAbbDg7WKy', 0, 1, '2026-10-19 08:09:13', '::1', '2026-10-19 07:59:13'),
(11, 87654321, 'verificacion_correo', '$2y$10$MnWizswsvfQVkiziuayRsOVCmJ9N41vMj3M0oVzW8thMEC9ygscb6', 0, 1, '2026-10-19 21:14:07', '::1', '2026-10-19 21:04:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra`
--

CREATE TABLE `compra` (
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `Fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `Alias` varchar(60) NOT NULL,
  `Calle` varchar(100) NOT NULL,
  `Numero_Puerta` varchar(10) NOT NULL,
  `Apartamento` varchar(20) DEFAULT NULL,
  `Esquina` varchar(100) DEFAULT NULL,
  `Barrio` varchar(80) NOT NULL,
  `Ciudad` varchar(80) NOT NULL,
  `Departamento` varchar(30) NOT NULL,
  `Codigo_Postal` varchar(5) DEFAULT NULL,
  `Indicaciones` varchar(200) DEFAULT NULL,
  `Es_Principal` tinyint(1) NOT NULL DEFAULT 0,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emprendedor`
--

CREATE TABLE `emprendedor` (
  `Cedula` int(11) NOT NULL,
  `Nombre` varchar(60) NOT NULL,
  `Apellido` varchar(60) NOT NULL,
  `Correo` varchar(50) NOT NULL,
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `emprendedor`
--

INSERT INTO `emprendedor` (`Cedula`, `Nombre`, `Apellido`, `Correo`, `Genero`) VALUES
(87654321, 'Emprendedor', 'a', 'emprendeuy2026@gmail.com', 'Femenino');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emprendimiento`
--

CREATE TABLE `emprendimiento` (
  `Nombre` varchar(20) NOT NULL,
  `Descripcion` text NOT NULL,
  `Foto` varchar(255) DEFAULT NULL,
  `ID` int(11) NOT NULL,
  `cedula` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `emprendimiento`
--

INSERT INTO `emprendimiento` (`Nombre`, `Descripcion`, `Foto`, `ID`, `cedula`) VALUES
('emprendimiento1', 'de algo', '3.png', 3, 1),
('emprendimiento1', 'de algo', '4.png', 4, 6),
('87', '87', '5.png', 5, 565),
('76', '76', '6.png', 6, 5655),
('emprendimiento1', 'erd', '7.png', 7, 87654321);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intento_login`
--

CREATE TABLE `intento_login` (
  `idIntento` int(11) NOT NULL,
  `Cedula` int(11) NOT NULL,
  `IP` varchar(45) NOT NULL,
  `Exitoso` tinyint(1) NOT NULL,
  `FechaIntento` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `intento_login`
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
(43, 23456789, '::1', 1, '2026-10-13 19:44:42'),
(44, 23456789, '::1', 1, '2026-10-14 08:26:30'),
(45, 23456789, '::1', 1, '2026-10-14 08:27:22'),
(46, 23456789, '::1', 1, '2026-10-14 09:27:07'),
(47, 23456789, '::1', 1, '2026-10-16 08:08:59'),
(48, 23456789, '::1', 1, '2026-10-16 08:45:15'),
(49, 23456789, '::1', 0, '2026-10-19 07:57:35'),
(50, 23456789, '::1', 0, '2026-10-19 07:57:39'),
(51, 23456789, '::1', 0, '2026-10-19 07:58:41'),
(52, 23456789, '::1', 1, '2026-10-19 08:00:06'),
(53, 23456789, '::1', 1, '2026-10-19 09:15:16'),
(54, 23456789, '::1', 1, '2026-10-19 09:34:07'),
(55, 34567890, '::1', 0, '2026-10-19 12:46:37'),
(56, 87654321, '::1', 0, '2026-10-19 20:58:50'),
(57, 87654321, '::1', 0, '2026-10-19 20:58:56'),
(58, 34567890, '::1', 0, '2026-10-19 20:59:41'),
(59, 34567890, '::1', 0, '2026-10-19 20:59:51'),
(60, 34567890, '::1', 0, '2026-10-19 21:02:31'),
(61, 34567890, '::1', 0, '2026-10-19 21:02:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_acceso_admin`
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
-- Volcado de datos para la tabla `log_acceso_admin`
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
(19, 87654321, '/EmprendeUY/PHP/crear_Emprendimiento.php', 'permitido', '::1', '2026-10-19 21:04:59'),
(20, 87654321, '/EmprendeUY/PHP/crear_publicaciones.php', 'permitido', '::1', '2026-10-19 21:05:17'),
(21, 87654321, '/EmprendeUY/PHP/editar_publicacion.php', 'permitido', '::1', '2026-10-19 21:05:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
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
-- Estructura de tabla para la tabla `publicaciones`
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
  `categoria` enum('Indumentaria','Higiene','Higiene personal','Hogar','Higiene para el hogar','Cocina','Alimentos','Tecnologia','Deportes','Botánica','Carpinteria','Herreria') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id`, `Id_emprendimiento`, `status`, `titulo`, `descripcion`, `precio`, `fecha_publicacion`, `foto`, `categoria`) VALUES
(17, 3, 'Pausada', '1', '1', 1, '2026-08-26 00:37:01', '17.png', 'Indumentaria'),
(18, 3, 'Activa', '1', '1', 1, '2026-08-29 18:30:04', '18.jpg', 'Tecnologia'),
(19, 3, 'Activa', 'asd', '1', 1, '2026-08-29 18:30:26', '19.jpg', 'Tecnologia'),
(20, 3, 'Activa', 'asd', '1', 1, '2026-08-29 18:30:27', '20.jpg', 'Tecnologia'),
(21, 3, 'Pausada', 'asd', 'asd', 21, '2026-08-29 18:31:13', '21.jpg', 'Tecnologia'),
(22, 3, 'Activa', 'prueba2', '1', 111, '2026-09-03 08:13:00', '22.png', ''),
(23, 3, 'Pausada', 'we', 'we', 32, '2026-09-03 08:39:22', '23.png', 'Hogar'),
(24, 7, 'Pausada', 'we', 'we', 87, '2026-10-19 21:05:17', '24.png', 'Alimentos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
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
  `Sobre_Mi` varchar(300) DEFAULT NULL,
  `Foto_Perfil` varchar(255) DEFAULT NULL,
  `Domicilio` varchar(60) NOT NULL,
  `Calle` varchar(100) NOT NULL,
  `Manzana` varchar(60) NOT NULL,
  `Solar` varchar(60) NOT NULL,
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL,
  `Rol` enum('administrador','cliente','emprendedor','usuario') NOT NULL DEFAULT 'usuario',
  `Activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`Cedula`, `Nombre`, `Apellido`, `Fecha_Nacimiento`, `Edad`, `Correo`, `CorreoVerificado`, `Contraseña`, `Num_Telefono`, `Sobre_Mi`, `Foto_Perfil`, `Domicilio`, `Calle`, `Manzana`, `Solar`, `Genero`, `Rol`, `Activo`) VALUES
(1, '1', '1', '2026-07-27', 0, '1@1', 1, '$2y$10$KqhnlryC.4IL6m29L2cKgOAe4m0e41l6i..AWOZOg8lKQ3iEVbwaK', '1', NULL, NULL, '', '', '', '', 'Masculino', 'emprendedor', 1),
(2, '2', '2', '2026-07-29', 0, '2@2', 1, '$2y$10$AB0mezcFWvCda20Gwrmp9.fxzOYOsoysZvRvNo/YEayG6WVwhHtCO', '2', NULL, NULL, '', '', '', '', 'Femenino', 'cliente', 1),
(56, 'asd', 'asd', '2026-09-18', 0, 'asd@asd.com', 1, '$2y$10$N2ejuJKjMEvS4MwvoFJGO.i6SMUlkvWLh5EuBxB4GExOilOUuh6hK', '67', NULL, NULL, '', '', '', '', 'Femenino', 'cliente', 1),
(565, 'asd5', 'asd5', '2026-09-18', 0, 'asd@asd5.com', 1, '$2y$10$u47fhbhIxmdxCxUsHv7XpuStgFocbsBAP0j6PrOZ17FQuy4kwLbxq', '675', NULL, NULL, '', '', '', '', 'Femenino', 'emprendedor', 1),
(5655, 'asd56', 'asd56', '2026-09-18', 0, 'asd@asd56.com', 1, '$2y$10$uPFJ.z5HvAhEoP8kGKW4tesK0AYJ9D.vUbYqYk28E/m1Za6Wvkxm6', '6756', NULL, NULL, '', '', '', '', 'Femenino', 'emprendedor', 1),
(23456789, 'asd1', 'asd1', '1997-06-05', 29, 'nicocalixto26@gmail.com', 1, '$2y$10$8nPxTymN5bHBo2HQwuQJN.oUMFY/ibd64tpOENyUUz1PPTIsIy3qi', '123456789', NULL, NULL, '', '', '', '', 'Femenino', 'cliente', 1),
(57683215, 'nicolas', 'calixto', '2026-06-19', 0, 'nicolito3215@gmail.com', 1, '$2y$10$7B/yrGYGEYmGOW/ABrClbuiqXMhPOh.KxFwBCTRhbJCUNIt7SoqNC', '094197336', NULL, NULL, '', '', '', '', 'Masculino', 'administrador', 1),
(87654321, 'Emprendedor', 'a', '1995-06-07', 31, 'emprendeuy2026@gmail.com', 1, '$2y$10$yK9VNo.cUAH2Oc0U7bgPj.lYey8LlB/4rz4cauRaaMeZtvO/Nzrr6', '123456778', NULL, NULL, '', '', '', '', 'Femenino', 'emprendedor', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Indices de la tabla `bloqueo_temporal`
--
ALTER TABLE `bloqueo_temporal`
  ADD PRIMARY KEY (`idBloqueo`),
  ADD KEY `idx_cedula_hasta` (`Cedula`,`BloqueadoHasta`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD UNIQUE KEY `uniq_carrito_usuario_producto` (`ci`,`id`),
  ADD KEY `carrito_ibfk_1` (`id`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Indices de la tabla `codigo_verificacion`
--
ALTER TABLE `codigo_verificacion`
  ADD PRIMARY KEY (`idCodigo`),
  ADD KEY `idx_codigo_cedula_tipo` (`Cedula`,`Tipo`,`Usado`);

--
-- Indices de la tabla `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `Cedula` (`Cedula`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `idx_direccion_cedula` (`Cedula`);

--
-- Indices de la tabla `emprendedor`
--
ALTER TABLE `emprendedor`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Indices de la tabla `emprendimiento`
--
ALTER TABLE `emprendimiento`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `intento_login`
--
ALTER TABLE `intento_login`
  ADD PRIMARY KEY (`idIntento`),
  ADD KEY `idx_cedula_fecha` (`Cedula`,`FechaIntento`);

--
-- Indices de la tabla `log_acceso_admin`
--
ALTER TABLE `log_acceso_admin`
  ADD PRIMARY KEY (`idLog`),
  ADD KEY `idx_cedula_fecha` (`Cedula`,`FechaAcceso`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bloqueo_temporal`
--
ALTER TABLE `bloqueo_temporal`
  MODIFY `idBloqueo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `codigo_verificacion`
--
ALTER TABLE `codigo_verificacion`
  MODIFY `idCodigo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `emprendimiento`
--
ALTER TABLE `emprendimiento`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `intento_login`
--
ALTER TABLE `intento_login`
  MODIFY `idIntento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `log_acceso_admin`
--
ALTER TABLE `log_acceso_admin`
  MODIFY `idLog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `administrador_ibfk_1` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `FK_Cliente_Usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

--
-- Filtros para la tabla `codigo_verificacion`
--
ALTER TABLE `codigo_verificacion`
  ADD CONSTRAINT `fk_codigo_usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `fk_direccion_usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`) ON DELETE CASCADE;

--
-- Filtros para la tabla `emprendedor`
--
ALTER TABLE `emprendedor`
  ADD CONSTRAINT `FK_Emprendedor_Usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

--
-- Filtros para la tabla `log_acceso_admin`
--
ALTER TABLE `log_acceso_admin`
  ADD CONSTRAINT `fk_log_usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
