-- Migración para la página "Mi perfil" (HTML/perfil.html + JS/perfil.js).
-- Ejecutar una sola vez sobre la base `emprendeuy` (phpMyAdmin > SQL).

-- 1) Campos nuevos en el usuario: "Sobre mí" y nombre del archivo de la foto de perfil.
ALTER TABLE `usuario`
  ADD COLUMN `Sobre_Mi` varchar(300) DEFAULT NULL AFTER `Num_Telefono`,
  ADD COLUMN `Foto_Perfil` varchar(255) DEFAULT NULL AFTER `Sobre_Mi`;

-- 2) Direcciones de entrega (un usuario puede tener varias, una sola principal).
CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL AUTO_INCREMENT,
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
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_direccion`),
  KEY `idx_direccion_cedula` (`Cedula`),
  CONSTRAINT `fk_direccion_usuario` FOREIGN KEY (`Cedula`) REFERENCES `usuario` (`Cedula`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
