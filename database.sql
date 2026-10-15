CREATE DATABASE IF NOT EXISTS home_helpdesk
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE home_helpdesk;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(254) NOT NULL,
    password VARCHAR(255) NULL,
    rol ENUM('admin', 'familiar') NOT NULL DEFAULT 'familiar',
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users MODIFY password VARCHAR(255) NULL;

INSERT IGNORE INTO users (nombre, email, password, rol) VALUES
    ('Arturo', 'arturo@casa.local', NULL, 'admin'),
    ('Mamá', 'mama@casa.local', NULL, 'familiar'),
    ('Inés', 'ines@casa.local', NULL, 'familiar');

CREATE TABLE IF NOT EXISTS incidencias (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    estado ENUM('sin_hacer', 'en_proceso', 'hecho') NOT NULL DEFAULT 'sin_hacer',
    fecha_entrada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_salida DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_incidencias_user_fecha (user_id, fecha_entrada),
    KEY idx_incidencias_estado (estado),
    CONSTRAINT fk_incidencias_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
