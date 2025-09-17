-- Base de datos para sistema de cupones
-- Alcance: Gestión CRUD cupones + Canje + Panel administrativo

CREATE DATABASE IF NOT EXISTS coupon_system;
USE coupon_system;

-- Tabla de cupones (gestión principal)
CREATE TABLE cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    fecha_expiracion DATE NOT NULL,
    estado ENUM('activo', 'inactivo', 'canjeado') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de roles (súper simple - solo admin vs usuario)
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    es_admin BOOLEAN DEFAULT FALSE
);

-- Tabla de usuarios (admin y usuarios que canjean)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    rol_id INT NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Tabla de canjes (historial para panel administrativo)
CREATE TABLE canjes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cupon_id INT NOT NULL,
    usuario_id INT NULL,
    fecha_canje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cupon_id) REFERENCES cupones(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Insertar roles (súper simple)
INSERT INTO roles (nombre, es_admin) VALUES
('Administrador', TRUE),
('Usuario', FALSE);

-- Insertar usuarios
INSERT INTO usuarios (email, password, nombre, apellido, rol_id) VALUES 
('admin', PASSWORD('admin'), 'Administrador', 'Sistema', 1),
('usuario@test.com', PASSWORD('123456'), 'Usuario', 'Final', 2);

-- Insertar 3 cupones (uno por estado)
INSERT INTO cupones (codigo, descripcion, valor, fecha_expiracion, estado) VALUES
('ACTIVO10', 'Cupon de prueba activo', 10.00, '2025-12-31', 'activo'),
('INACTIVO20', 'Cupon de prueba inactivo', 20.00, '2025-12-31', 'inactivo'),
('CANJEADO30', 'Cupon de prueba canjeado', 30.00, '2025-12-31', 'canjeado');