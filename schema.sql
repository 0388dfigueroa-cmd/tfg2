-- =====================================================
-- Base de datos: pescaypasca
-- Proyecto: Pasca y Pesca - TFG
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS pescaypesca;
USE pescaypesca;

-- =====================================================
-- TABLA: users
-- Descripción: Almacena información de usuarios registrados
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    aficiones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: posts
-- Descripción: Almacena posts del foro
-- =====================================================
CREATE TABLE IF NOT EXISTS posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content LONGTEXT NOT NULL,
    image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: comments
-- Descripción: Almacena comentarios en los posts
-- =====================================================
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: carrito
-- Descripción: Carrito de compra de usuarios
-- =====================================================
CREATE TABLE IF NOT EXISTS carrito (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, producto_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: productos
-- Descripción: Catálogo de productos de la tienda
-- =====================================================
CREATE TABLE IF NOT EXISTS productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion LONGTEXT,
    precio DECIMAL(10, 2) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    imagen VARCHAR(500),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria),
    INDEX idx_precio (precio),
    FULLTEXT INDEX ft_nombre_descripcion (nombre, descripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pedidos
-- Descripción: Registro de pedidos realizados
-- =====================================================
CREATE TABLE IF NOT EXISTS pedidos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'pendiente',
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_pedido (fecha_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pedido_items
-- Descripción: Detalles de los items en cada pedido
-- =====================================================
CREATE TABLE IF NOT EXISTS pedido_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_pedido_id (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: tecnicas
-- Descripción: Artículos y técnicas de pesca
-- =====================================================
CREATE TABLE IF NOT EXISTS tecnicas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descripcion LONGTEXT NOT NULL,
    contenido LONGTEXT,
    autor_id INT,
    imagen VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES users(id) ON DELETE SET NULL,
    FULLTEXT INDEX ft_titulo_descripcion (titulo, descripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: zonas_calientes
-- Descripción: Información sobre zonas recomendadas para pesca
-- =====================================================
CREATE TABLE IF NOT EXISTS zonas_calientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion LONGTEXT,
    ubicacion VARCHAR(200),
    coordenadas_lat DECIMAL(10, 8),
    coordenadas_long DECIMAL(11, 8),
    especie_principal VARCHAR(100),
    temporada_best VARCHAR(100),
    dificultad VARCHAR(20),
    imagen VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FULLTEXT INDEX ft_nombre_descripcion (nombre, descripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE PRUEBA
-- =====================================================

-- Insertar usuario admin de prueba
-- Contraseña: admin1234
-- Para generar otro hash, ejecuta en PHP: echo password_hash('admin1234', PASSWORD_DEFAULT);
INSERT INTO users (username, email, password_hash, aficiones, created_at) VALUES
('admin', 'admin@pascaypesca.com', '$2y$10$9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KFm', 'Administrador', NOW());

-- Insertar productos de ejemplo
INSERT INTO productos (nombre, descripcion, precio, categoria, imagen, stock) VALUES
('Caña de Pescar Pro X', 'Caña profesional de alta calidad para pesca deportiva', 89.99, 'Cañas', 'img/cañaProX.webp', 15),
('Carrete Shimano 5000', 'Carrete de precisión para pesca en agua dulce', 129.99, 'Carretes', 'img/carreteShimano5000.webp', 10),
('Kit Señuelos Premium', 'Set de 5 señuelos variados de máxima calidad', 39.99, 'Señuelos', 'img/señuelosPremium.webp', 30),
('Caña de Pescar Básica', 'Caña versátil para principiantes y aficionados', 49.99, 'Cañas', 'img/cañaBásica.webp', 20),
('Carrete Daiwa 3000', 'Carrete de alta durabilidad para pesca en mar', 99.99, 'Carretes', 'img/carreteDaiwa3000.webp', 12),
('Señuelo Spinner Pro', 'Señuelo giratorio con acabado cromado', 18.99, 'Señuelos', 'img/señueloSpinnerPro.webp', 50),
('Caña Telescópica', 'Caña extensible ideal para transporte y almacenamiento', 79.99, 'Cañas', 'img/cañaTelescópica.webp', 8),
('Carrete Penn 4000', 'Carrete profesional de agua salada', 149.99, 'Carretes', 'img/carretePenn4000.webp', 5),
('Kit de Aparejos', 'Conjunto completo de aparejos y accesorios', 29.99, 'Accesorios', 'img/kitAparejos.webp', 25),
('Caña Spinning', 'Caña de rotación para spinning y pesca activa', 109.99, 'Cañas', 'img/cañaSpinning.webp', 10),
('Carrete Abu Garcia', 'Carrete de alta precisión de marca prestigiosa', 119.99, 'Carretes', 'carreteAbuGarcia.webp', 7),
('Señuelo Topwater', 'Señuelo de superficie para pesca en aguas tranquilas', 19.99, 'Señuelos', 'img/señueloTopwater.webp', 40),
('Caja de Aparejos', 'Caja organizadora con compartimentos ajustables', 24.99, 'Accesorios', 'img/cajaAparejos.webp', 35),
('Caña de Mosca', 'Caña especializada para pesca con mosca', 139.99, 'Cañas', 'img/cañaDeMosca.webp', 6),
('Carrete Fly Fishing', 'Carrete específico para pesca con mosca', 159.99, 'Carretes', 'img/carreteFlyFishing.webp', 4);


-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
