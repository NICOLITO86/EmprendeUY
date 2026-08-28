-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-08-2026 a las 05:20:06
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
  `Genero` enum('Masculino','Femenino','Otro') NOT NULL,
  `Emprendimiento` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, 1, 13, '2026-08-25 17:42:05');

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
('a', 's@s', '3', 'Masculino', 0);

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
(0, 'as', 'a@d', '0', 'Femenino');

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
('emprendimiento1', 'de algo', '?PNG\r\n\Z\n\0\0\0\rIHDR\0\0?\0\0\0,\0\0\0???\0\0\0sRGB\0???\0\0\0gAMA\0\0???a\0\0\0	pHYs\0\0?\0\0??o?d\0\0O?IDATx^??yXU??ǿ?yE@D?pJDS?5?*?e?)?m4?LM?Բ?1Ss6sR??)EgAD@d?????Z??9?M?w???????^k???\Zߵ???GG?????????????ӰP?????????????c??	??O???7>??%??A???ג0??1X?q?:??۷o??', 2, 1);

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
(12, 0, 'Activa', 'prueba', 'prueba', 122, '2026-08-25 17:19:25', '?PNG\r\n\Z\n\0\0\0\rIHDR\0\0/\0\0\0?\0\0\0?(ۡ\0\0\0sRGB\0???\0\0\0gAMA\0\0???a\0\0\0	pHYs\0\0?\0\0??o?d\0\0?IDATx^??wXTG?7?/M??DPD?\"J????kl??E?F?M???ޱ7TE??(?P)\"E??tv?~????????<???.??3????9eΙ9?DD??8??8????r????8??8????F???q?q?q\\??/?q?q?????q?q?q\\??/?q?', 'Ropa'),
(13, 0, 'Activa', 'prueba', 'prueba', 122, '2026-08-25 17:19:27', '?PNG\r\n\Z\n\0\0\0\rIHDR\0\0/\0\0\0?\0\0\0?(ۡ\0\0\0sRGB\0???\0\0\0gAMA\0\0???a\0\0\0	pHYs\0\0?\0\0??o?d\0\0?IDATx^??wXTG?7?/M??DPD?\"J????kl??E?F?M???ޱ7TE??(?P)\"E??tv?~????????<???.??3????9eΙ9?DD??8??8????r????8??8????F???q?q?q\\??/?q?q?????q?q?q\\??/?q?', 'Ropa');

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
(0, 'as', 'as', '2026-07-16', 0, 'a@d', '$2y$10$ykO1870g0dGJbSeP7GN9Y.Zxr58c8RHhu7odEtTkfqIAZId..FYHW', '82', '', '', '', '', 'Femenino', 'emprendedor'),
(1, 'alvaro', 'Gonzalez', '2026-06-19', 0, 'a@a', '$2y$10$enZjMoiMyZRexJXW0arWEODGZe6ORIMinDyk8Q4MTtEXEQk1Xtv2W', '1', '', '', '', '', 'Femenino', 'emprendedor'),
(2, 'asa', 'asa', '2026-07-09', 0, 'asa@as', '$2y$10$47Hlat7pStP21khbzqL7qeOE2qwpMgxrZLl515zwXCwtMstGIIuUS', '2', '', '', '', '', 'Masculino', 'cliente'),
(3, 'pruebacliente', 'a', '2026-07-31', 0, 's@s', '$2y$10$ATva/Civj/4x8fO578gHYeuTAXOac5CMojFtc/WGM.Ud.NTFAZHWa', '3', '', '', '', '', 'Masculino', 'cliente'),
(6, 'as', 'as', '2026-07-16', 0, 'a@i', '$2y$10$UHFHGfpuy0Bqy4w3oA7xCOvpwChpT1Or8qtBTkZvOp66WzrlKZIUW', '8', '', '', '', '', 'Femenino', 'administrador'),
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
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `emprendimiento`
--
ALTER TABLE `emprendimiento`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
