-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-09-2026 a las 01:47:55
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
(1, 5, '::1', '2026-09-02 20:38:17', '2026-09-02 20:28:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `ci` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `ci`, `id`, `fecha_agregado`) VALUES
(3, 2, 17, '2026-08-27 12:39:20'),
(4, 2, 20, '2026-08-30 00:36:36');

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
('2', '2@2', '2', 'Femenino', 2),
('ma', 'ma', 'ma@ma.com', 'Femenino', 5),
('8', '8', '8@8.com', 'Masculino', 8);

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
(1, '1', '1@1', '1', 'Masculino'),
(6, '6', '6', '6@6.com', 'Femenino');

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
('emprendimiento1', 'de algo', '4.png', 4, 6);

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
(5, 5, '::1', 0, '2026-09-02 20:28:17');

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
  `status` enum('Pausada','Bloqueada','Activa') NOT NULL DEFAULT 'Activa',
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(20,0) NOT NULL,
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL,
  `categoria` enum('Ropa','Hogar','Tecnologia','Carpinteria','Herreria','Higiene','Deportes') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id`, `Id_emprendimiento`, `status`, `titulo`, `descripcion`, `precio`, `fecha_publicacion`, `foto`, `categoria`) VALUES
(17, 3, 'Pausada', '1', '1', 1, '2026-08-26 00:37:01', '17.png', 'Ropa'),
(18, 3, 'Activa', '1', '1', 1, '2026-08-29 18:30:04', '18.jpg', 'Tecnologia'),
(19, 3, 'Activa', 'asd', '1', 1, '2026-08-29 18:30:26', '19.jpg', 'Tecnologia'),
(20, 3, 'Activa', 'asd', '1', 1, '2026-08-29 18:30:27', '20.jpg', 'Tecnologia'),
(21, 3, 'Pausada', 'asd', 'asd', 21, '2026-08-29 18:31:13', '21.jpg', 'Tecnologia');

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
  `Contraseña` varchar(100) NOT NULL,
  `Num_Telefono` varchar(20) NOT NULL,
  `Domicilio` varchar(60) NOT NULL,
  `Calle` varchar(100) NOT NULL,
  `Manzana` varchar(60) NOT NULL,
  `Solar` varchar(60) NOT NULL,
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL,
  `Rol` enum('administrador','cliente','emprendedor','usuario') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`Cedula`, `Nombre`, `Apellido`, `Fecha_Nacimiento`, `Edad`, `Correo`, `Contraseña`, `Num_Telefono`, `Domicilio`, `Calle`, `Manzana`, `Solar`, `Genero`, `Rol`) VALUES
(1, '1', '1', '2026-07-27', 0, '1@1', '$2y$10$KqhnlryC.4IL6m29L2cKgOAe4m0e41l6i..AWOZOg8lKQ3iEVbwaK', '1', '', '', '', '', 'Masculino', 'emprendedor'),
(2, '2', '2', '2026-07-29', 0, '2@2', '$2y$10$AB0mezcFWvCda20Gwrmp9.fxzOYOsoysZvRvNo/YEayG6WVwhHtCO', '2', '', '', '', '', 'Femenino', 'cliente'),
(5, 'ma', 'ma', '2026-08-30', 0, 'ma@ma.com', '$2y$10$NDjjQSTJ564r3mI249fRs..qCurp2DFBQIcg1p9NlnAc2f/qYg4aq', '5', '', '', '', '', 'Femenino', 'cliente'),
(6, '6', '6', '2026-08-27', 0, '6@6.com', '$2y$10$5Q7wWw28KyrYxqv8vSBNT.68mWnJF1OsBGkjdtqumbSHurSK9/YWG', '6', '', '', '', '', 'Femenino', 'emprendedor'),
(7, 'yy', 'y', '2026-08-20', 0, 'y@y.com', '$2y$10$kShz1ERZ8WMsMV8Oj9yNYOamBlVVqPIt4tRPCEjimGZUVEe1KKkSe', '7', '', '', '', '', 'Femenino', 'emprendedor'),
(8, '8', '8', '2026-08-27', 0, '8@8.com', '$2y$10$lUkjjfCN3k2ZNNW6MzG12eiPTAFUX91D6yQPdGA3ueK30qOnnqJ0m', '8', '', '', '', '', 'Masculino', 'cliente'),
(23, 'Emprendedor', '2', '2026-08-22', 0, '2@3', '$2y$10$WtvBea4muTVUBcUZvjMIPeqhkgyh5k0UIprbtytDkCOZe5nv36E9.', '23', '', '', '', '', 'Femenino', 'administrador'),
(57683215, 'nicolas', 'calixto', '2026-06-19', 0, 'nicolito3215@gmail.com', '$2y$10$7B/yrGYGEYmGOW/ABrClbuiqXMhPOh.KxFwBCTRhbJCUNIt7SoqNC', '094197336', '', '', '', '', 'Masculino', 'administrador');

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
  ADD KEY `ci` (`ci`),
  ADD KEY `id` (`id`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`Cedula`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Indices de la tabla `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `Cedula` (`Cedula`);

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
  MODIFY `idBloqueo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `emprendimiento`
--
ALTER TABLE `emprendimiento`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `intento_login`
--
ALTER TABLE `intento_login`
  MODIFY `idIntento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `log_acceso_admin`
--
ALTER TABLE `log_acceso_admin`
  MODIFY `idLog` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
-- Filtros para la tabla `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`);

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
