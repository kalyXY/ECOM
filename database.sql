-- Base de données : stylehub_db
-- Script SQL pour créer la base de données et les tables pour StyleHub

CREATE DATABASE IF NOT EXISTS stylehub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stylehub_db;

-- Table des utilisateurs administrateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des catégories de mode
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    parent_id INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des produits de mode
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    sku VARCHAR(100) UNIQUE,
    stock INT DEFAULT 0,
    category_id INT,
    brand VARCHAR(100),
    color VARCHAR(50),
    size VARCHAR(20),
    material VARCHAR(100),
    gender ENUM('homme', 'femme', 'unisexe') DEFAULT 'unisexe',
    season ENUM('printemps', 'été', 'automne', 'hiver', 'toute_saison') DEFAULT 'toute_saison',
    image_url VARCHAR(255),
    gallery TEXT, -- JSON array of additional images
    tags TEXT, -- JSON array of tags
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_gender (gender),
    INDEX idx_featured (featured),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des commandes (pour les statistiques)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion d'un utilisateur admin par défaut
-- Mot de passe : admin123
INSERT INTO users (username, password_hash, email, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@stylehub.fr', 'Administrateur StyleHub')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Insertion des catégories de mode
INSERT INTO categories (name, slug, description) VALUES 
('Femme', 'femme', 'Collection féminine complète'),
('Homme', 'homme', 'Collection masculine tendance'),
('Accessoires', 'accessoires', 'Accessoires de mode pour tous'),
('Robes', 'robes', 'Robes élégantes pour toutes occasions'),
('Tops & T-shirts', 'tops-tshirts', 'Hauts et t-shirts tendance'),
('Pantalons & Jeans', 'pantalons-jeans', 'Bas confortables et stylés'),
('Chaussures', 'chaussures', 'Chaussures pour tous les styles'),
('Sacs & Maroquinerie', 'sacs-maroquinerie', 'Sacs et accessoires en cuir'),
('Bijoux', 'bijoux', 'Bijoux fantaisie et précieux')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Insertion de produits de mode d'exemple
INSERT INTO products (name, description, price, sale_price, sku, stock, category_id, brand, color, size, material, gender, season, image_url, featured) VALUES 
('Robe Midi Élégante', 'Robe midi fluide parfaite pour les occasions spéciales. Coupe flatteuse et tissu de qualité premium.', 89.99, 69.99, 'ROBE001', 15, 4, 'StyleHub', 'Noir', 'M', 'Polyester', 'femme', 'toute_saison', 'uploads/robe-midi-noire.jpg', TRUE),
('Jean Slim Homme', 'Jean slim fit confortable avec stretch. Coupe moderne et délavage authentique.', 79.99, NULL, 'JEAN001', 25, 6, 'DenimCo', 'Bleu', '32', 'Coton/Elasthanne', 'homme', 'toute_saison', 'uploads/jean-slim-homme.jpg', TRUE),
('Blazer Femme Chic', 'Blazer structuré pour un look professionnel. Doublure soyeuse et finitions soignées.', 129.99, 99.99, 'BLAZ001', 12, 1, 'ChicStyle', 'Beige', 'L', 'Laine/Polyester', 'femme', 'automne', 'uploads/blazer-femme-beige.jpg', TRUE),
('Sneakers Unisexe', 'Baskets tendance en cuir véritable. Confort optimal pour un usage quotidien.', 119.99, NULL, 'SNEAK001', 30, 7, 'UrbanStep', 'Blanc', '42', 'Cuir', 'unisexe', 'toute_saison', 'uploads/sneakers-blanc.jpg', FALSE),
('Sac à Main Cuir', 'Sac à main en cuir véritable avec compartiments multiples. Élégance et praticité.', 159.99, 139.99, 'SAC001', 8, 8, 'LeatherLux', 'Cognac', 'Unique', 'Cuir véritable', 'femme', 'toute_saison', 'uploads/sac-cuir-cognac.jpg', TRUE),
('T-shirt Basique Homme', 'T-shirt en coton bio, coupe regular. Essentiel du dressing masculin.', 24.99, NULL, 'TSHIRT001', 50, 5, 'BasicWear', 'Blanc', 'L', 'Coton Bio', 'homme', 'toute_saison', 'uploads/tshirt-blanc-homme.jpg', FALSE),
('Collier Doré', 'Collier fantaisie plaqué or avec pendentif délicat. Parfait pour sublimer vos tenues.', 39.99, 29.99, 'COLL001', 20, 9, 'GoldTouch', 'Doré', 'Unique', 'Métal plaqué or', 'femme', 'toute_saison', 'uploads/collier-dore.jpg', FALSE),
('Pantalon Chino Homme', 'Pantalon chino en coton stretch. Coupe droite et couleurs intemporelles.', 59.99, NULL, 'CHINO001', 18, 6, 'ClassicMen', 'Beige', '34', 'Coton/Elasthanne', 'homme', 'printemps', 'uploads/chino-beige.jpg', FALSE)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Insertion de quelques commandes d'exemple
INSERT INTO orders (customer_name, customer_email, total_amount, status) VALUES 
('Emma Dubois', 'emma.dubois@email.com', 159.98, 'delivered'),
('Thomas Martin', 'thomas.martin@email.com', 209.97, 'shipped'),
('Léa Durand', 'lea.durand@email.com', 89.99, 'processing'),
('Sophie Bernard', 'sophie.bernard@email.com', 179.98, 'pending'),
('Lucas Moreau', 'lucas.moreau@email.com', 119.99, 'delivered'),
('Camille Rousseau', 'camille.rousseau@email.com', 249.97, 'delivered'),
('Antoine Leroy', 'antoine.leroy@email.com', 79.99, 'shipped')
ON DUPLICATE KEY UPDATE customer_name = VALUES(customer_name);



