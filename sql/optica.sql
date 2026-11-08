-- ============================================================
--  optica_db — Script de creación e inserción de datos
--  Plataforma Web para la Gestión de una Óptica
--  Raúl Miguel Almarza Robles — DAW 2025-2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS optica_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE optica_db;

-- ------------------------------------------------------------
--  Tabla: usuarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id             INT          NOT NULL AUTO_INCREMENT,
    nombre         VARCHAR(50)  NOT NULL,
    apellidos      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL,
    password       VARCHAR(255) NOT NULL,
    rol            ENUM('admin','paciente') NOT NULL DEFAULT 'paciente',
    fecha_registro DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
--  Tabla: pacientes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pacientes (
    id               INT          NOT NULL AUTO_INCREMENT,
    usuario_id       INT          NOT NULL,
    dni              CHAR(9)      NOT NULL,  -- Formato: 8 dígitos + 1 letra (ej: 12345678A)
    fecha_nacimiento DATE         NOT NULL,
    telefono         VARCHAR(15)  DEFAULT NULL,
    direccion        VARCHAR(200) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_dni (dni),
    UNIQUE KEY uq_usuario (usuario_id),
    CONSTRAINT fk_pac_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
--  Tabla: revisiones
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS revisiones (
    id            INT           NOT NULL AUTO_INCREMENT,
    paciente_id   INT           NOT NULL,
    fecha         DATE          NOT NULL,
    optometrista  VARCHAR(100)  DEFAULT NULL,
    od_esfera     DECIMAL(4,2)  DEFAULT NULL,
    od_cilindro   DECIMAL(4,2)  DEFAULT NULL,
    od_eje        INT           DEFAULT NULL,
    oi_esfera     DECIMAL(4,2)  DEFAULT NULL,
    oi_cilindro   DECIMAL(4,2)  DEFAULT NULL,
    oi_eje        INT           DEFAULT NULL,
    diagnostico   TEXT          DEFAULT NULL,
    observaciones TEXT          DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_rev_paciente FOREIGN KEY (paciente_id) REFERENCES pacientes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
--  Tabla: citas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS citas (
    id          INT          NOT NULL AUTO_INCREMENT,
    paciente_id INT          NOT NULL,
    fecha_hora  DATETIME     NOT NULL,
    motivo      VARCHAR(200) DEFAULT NULL,
    estado      ENUM('pendiente','confirmada','cancelada','completada') NOT NULL DEFAULT 'pendiente',
    PRIMARY KEY (id),
    CONSTRAINT fk_cita_paciente FOREIGN KEY (paciente_id) REFERENCES pacientes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  Datos de muestra (ficticios, solo fines educativos)
-- ============================================================

-- Contraseñas: admin → Admin1234! | pacientes → Paciente1234!
-- Hashes generados con password_hash($pass, PASSWORD_DEFAULT)

INSERT INTO usuarios (nombre, apellidos, email, password, rol) VALUES
('Eugenio',  'Rodríguez Velasco', 'admin@optica.local',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('María',    'García López',      'maria.garcia@mail.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'paciente'),
('Carlos',   'Martínez Ruiz',     'carlos.martinez@mail.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'paciente'),
('Laura',    'Sánchez Moreno',    'laura.sanchez@mail.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'paciente');

INSERT INTO pacientes (usuario_id, dni, fecha_nacimiento, telefono, direccion) VALUES
(2, '12345678A', '1990-03-15', '600111222', 'Calle Mayor 5, Madrid'),
(3, '23456789B', '1985-07-22', '611333444', 'Av. Castellana 100, Madrid'),
(4, '34567890C', '1995-11-08', '622555666', 'Calle Serrano 20, Madrid');

INSERT INTO revisiones (paciente_id, fecha, optometrista, od_esfera, od_cilindro, od_eje, oi_esfera, oi_cilindro, oi_eje, diagnostico, observaciones) VALUES
(1, '2025-01-10', 'Dra. Fernández', -1.50, -0.50, 180, -1.25, -0.25, 175, 'Miopía leve bilateral', 'Se recomienda uso de gafas para conducción'),
(1, '2025-09-15', 'Dra. Fernández', -1.75, -0.50, 180, -1.50, -0.25, 170, 'Miopía leve bilateral con ligera progresión', 'Control en 6 meses'),
(2, '2025-03-20', 'Dr. López',      +1.00,  0.00,   0, +1.25,  0.00,   0, 'Hipermetropía leve', 'No requiere corrección de momento'),
(3, '2025-06-05', 'Dra. Fernández', -2.50, -1.00,  90, -2.25, -0.75,  85, 'Miopía y astigmatismo', 'Se recomiendan lentes de contacto tóricas');

INSERT INTO citas (paciente_id, fecha_hora, motivo, estado) VALUES
(1, '2026-05-10 10:00:00', 'Revisión anual', 'confirmada'),
(1, '2026-07-20 11:30:00', 'Control progresión', 'pendiente'),
(2, '2026-05-15 09:00:00', 'Primera revisión', 'pendiente'),
(3, '2026-04-28 16:00:00', 'Adaptación lentillas', 'confirmada'),
(3, '2026-03-10 10:00:00', 'Revisión semestral', 'completada');
