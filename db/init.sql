-- Base de datos
CREATE DATABASE IF NOT EXISTS proovedatos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE proovedatos;

-- Tabla contacto
CREATE TABLE IF NOT EXISTS contacto (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL,
    telefono   VARCHAR(20)   NOT NULL,
    ciudad     VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla Region ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Region (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre_region VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla Provincia ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS Provincia (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    nombre_provincia      VARCHAR(100) NOT NULL,
    capital_provincia     VARCHAR(100) NOT NULL,
    descripcion_provincia TEXT,
    poblacion_provincia   VARCHAR(50),
    superficie_provincia  VARCHAR(50),
    latitud_provincia     VARCHAR(20),
    longitud_provincia    VARCHAR(20),
    id_region             INT,
    FOREIGN KEY (id_region) REFERENCES Region(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla reporte ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reporte (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    formato         VARCHAR(10)  NOT NULL,
    ordenado_por    VARCHAR(50)  NOT NULL DEFAULT 'created_at',
    direccion       VARCHAR(4)   NOT NULL DEFAULT 'asc',
    total_registros INT          NOT NULL DEFAULT 0,
    estado          ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


