CREATE DATABASE `organizacion` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */

-- organizacion.proyecto definition
CREATE TABLE `proyecto` (
  `id_proyecto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `presupuesto` decimal(10,0) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_proyecto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- AGREGAR NUEVA TABLA

-- organizacion.donante definition
CREATE TABLE `donante` (
  `id_donante` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_donante`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- organizacion.donacion definition
CREATE TABLE `donacion` (
  `id_donacion` int(11) NOT NULL AUTO_INCREMENT,
  `monto` decimal(10,0) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `id_proyecto` int(11) DEFAULT NULL,
  `id_donante` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_donacion`),
  KEY `donacion_donante_FK` (`id_donante`),
  KEY `donacion_proyecto_FK` (`id_proyecto`),
  CONSTRAINT `donacion_donante_FK` FOREIGN KEY (`id_donante`) REFERENCES `donante` (`id_donante`),
  CONSTRAINT `donacion_proyecto_FK` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Insertar 3 registros en la tabla proyecto
INSERT INTO organizacion.proyecto (nombre, descripcion, presupuesto, fecha_inicio, fecha_fin) VALUES
('Proyecto Comunitario A', 'Construcción de un centro comunitario en la zona.', 50000, '2025-09-01', '2026-03-31'),
('Iniciativa Educativa B', 'Programa de apoyo escolar para niños de bajos recursos.', 30000, '2025-08-15', '2026-12-31'),
('Campaña de Salud C', 'Realización de jornadas de salud y prevención en diferentes barrios.', 40000, '2025-10-01', '2026-06-30');

-- Insertar 3 registros en la tabla donante
INSERT INTO organizacion.donante (nombre, email, direccion, telefono) VALUES
('Elena Vargas', 'elena.v@gmail.com', 'Calle Los Boldos 123, Santiago', '912345678'),
('Pedro Gómez', 'p.gomez@elive.cl', 'Avenida Principal 456, Valparaíso', '987654321'),
('Sofía López', 's.lopez@salud.cl', 'Pasaje Las Flores 789, Concepción', '911223344');

-- Insertar 3 registros en la tabla donacion
INSERT INTO organizacion.donacion (monto, fecha, id_proyecto, id_donante) VALUES
(15000, '2025-10-05', 1, 1),
(8000, '2025-10-10', 2, 2),
(12000, '2025-10-15', 1, 3);


-- Insertar 10 donaciones en la tabla DONACION
INSERT INTO ORGANIZACION.donacion (monto, fecha, id_proyecto, id_donante) VALUES
(10000, '2025-07-15', 1, 1),
(5000, '2025-07-18', 2, 2),
(7500, '2025-07-20', 1, 3),
(2000, '2025-07-22', 3, 1),
(6000, '2025-07-25', 2, 2),
(12000, '2025-07-28', 1, 3),
(3500, '2025-07-30', 3, 2),
(8000, '2025-08-02', 2, 1),
(9500, '2025-08-05', 1, 2),
(4000, '2025-08-08', 3, 3);