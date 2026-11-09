-- Migración: suspensión de usuarios (reemplaza el borrado).
-- Ejecutar una sola vez sobre la base `emprendeuy` ya existente (se puede repetir sin problema).

-- Un emprendimiento puede quedar suspendido junto con su dueño.
ALTER TABLE `emprendimiento`
  ADD COLUMN IF NOT EXISTS `Activo` tinyint(1) NOT NULL DEFAULT 1;

-- Estado que tenía la publicación antes de la suspensión, para devolverla igual al reactivar.
-- (NULL = no fue afectada por una suspensión.)
ALTER TABLE `publicaciones`
  ADD COLUMN IF NOT EXISTS `status_previo` enum('Pausada','Activa') DEFAULT NULL;

-- `usuario`.`Activo` ya existía: 1 = habilitado, 0 = suspendido.
