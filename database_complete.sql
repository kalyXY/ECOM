-- ============================================================================
-- BASE DE DONNÉES COMPLÈTE - STYLEHUB E-COMMERCE PLATFORM
-- Version: 2.0
-- Description: Base de données moderne et complète pour un site e-commerce
-- ============================================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS stylehub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stylehub_db;

-- Configuration des variables pour optimiser les performances
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ============================================================================
-- 1. TABLES UTILISATEURS ET AUTHENTIFICATION
-- ============================================================================

-- Table des utilisateurs administrateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255),
    phone VARCHAR(20),
    role ENUM('admin', 'manager', 'editor') DEFAULT 'admin',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des sessions utilisateurs
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT,
    last_activity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABLES PRODUITS ET CATALOGUE
-- ============================================================================

-- Table des catégories de produits
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    parent_id INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    meta_title VARCHAR(255),
    meta_description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent_id (parent_id),
    INDEX idx_status (status),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des marques
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    logo_url VARCHAR(255),
    website_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des tailles
CREATE TABLE IF NOT EXISTS sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    category VARCHAR(50) DEFAULT 'general',
    size_type ENUM('clothing', 'shoes', 'accessories') DEFAULT 'clothing',
    sort_order INT DEFAULT 0,
    measurements JSON, -- Stockage des mesures (poitrine, taille, hanches, etc.)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_size_type (size_type),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des couleurs
CREATE TABLE IF NOT EXISTS colors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    hex_code VARCHAR(7) NOT NULL,
    rgb_code VARCHAR(20),
    color_family VARCHAR(30), -- rouge, bleu, vert, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_color_family (color_family)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table principale des produits
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    sku VARCHAR(100) UNIQUE,
    barcode VARCHAR(50),
    
    -- Prix et stock
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    cost_price DECIMAL(10,2) DEFAULT NULL,
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 0,
    max_stock INT DEFAULT NULL,
    stock_status ENUM('in_stock', 'out_of_stock', 'on_backorder') DEFAULT 'in_stock',
    manage_stock BOOLEAN DEFAULT TRUE,
    
    -- Classification
    category_id INT,
    brand_id INT,
    
    -- Caractéristiques physiques
    weight DECIMAL(8,3), -- en kg
    dimensions JSON, -- longueur, largeur, hauteur
    material VARCHAR(100),
    care_instructions TEXT,
    
    -- Classification mode
    gender ENUM('homme', 'femme', 'enfant', 'unisexe') DEFAULT 'unisexe',
    season ENUM('printemps', 'été', 'automne', 'hiver', 'toute_saison') DEFAULT 'toute_saison',
    age_group ENUM('adult', 'teen', 'child', 'baby') DEFAULT 'adult',
    
    -- Images
    image_url VARCHAR(255),
    gallery JSON, -- Array des URLs des images
    
    -- SEO et métadonnées
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    
    -- Statut et visibilité
    status ENUM('active', 'inactive', 'draft', 'archived') DEFAULT 'active',
    visibility ENUM('visible', 'catalog', 'search', 'hidden') DEFAULT 'visible',
    featured BOOLEAN DEFAULT FALSE,
    
    -- Statistiques
    view_count INT DEFAULT 0,
    purchase_count INT DEFAULT 0,
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    rating_count INT DEFAULT 0,
    
    -- Dates
    available_from TIMESTAMP NULL,
    available_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Contraintes et index
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_sku (sku),
    INDEX idx_category_id (category_id),
    INDEX idx_brand_id (brand_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_gender (gender),
    INDEX idx_price (price),
    INDEX idx_stock_status (stock_status),
    INDEX idx_created_at (created_at),
    FULLTEXT idx_search (name, description, short_description, meta_keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des images produits (relation one-to-many)
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    title VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    image_type ENUM('main', 'gallery', 'variant', 'detail') DEFAULT 'gallery',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_sort_order (sort_order),
    INDEX idx_is_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des tailles par produit (relation many-to-many)
CREATE TABLE IF NOT EXISTS product_sizes (
    product_id INT NOT NULL,
    size_id INT NOT NULL,
    stock INT DEFAULT NULL,
    price_modifier DECIMAL(10,2) DEFAULT 0.00,
    sku_suffix VARCHAR(20),
    
    PRIMARY KEY (product_id, size_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE CASCADE,
    INDEX idx_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des couleurs par produit (relation many-to-many)
CREATE TABLE IF NOT EXISTS product_colors (
    product_id INT NOT NULL,
    color_id INT NOT NULL,
    stock INT DEFAULT NULL,
    price_modifier DECIMAL(10,2) DEFAULT 0.00,
    sku_suffix VARCHAR(20),
    image_url VARCHAR(255), -- Image spécifique à cette couleur
    
    PRIMARY KEY (product_id, color_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE CASCADE,
    INDEX idx_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des variantes de produits (combinaisons taille/couleur)
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size_id INT DEFAULT NULL,
    color_id INT DEFAULT NULL,
    sku VARCHAR(100),
    stock INT DEFAULT 0,
    price DECIMAL(10,2),
    sale_price DECIMAL(10,2) DEFAULT NULL,
    image_url VARCHAR(255),
    weight DECIMAL(8,3),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE SET NULL,
    FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE SET NULL,
    UNIQUE KEY unique_variant (product_id, size_id, color_id),
    INDEX idx_sku (sku),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TABLES CLIENTS ET COMPTES
-- ============================================================================

-- Table des clients
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('M', 'F', 'O'), -- Masculin, Féminin, Autre
    
    -- Préférences
    newsletter_subscribed BOOLEAN DEFAULT FALSE,
    marketing_emails BOOLEAN DEFAULT TRUE,
    preferred_language VARCHAR(5) DEFAULT 'fr',
    preferred_currency VARCHAR(3) DEFAULT 'EUR',
    
    -- Statut
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    
    -- Statistiques
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    last_order_date TIMESTAMP NULL,
    last_login TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_last_name (last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des adresses clients
CREATE TABLE IF NOT EXISTS customer_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    type ENUM('billing', 'shipping', 'both') DEFAULT 'both',
    company VARCHAR(100),
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    state VARCHAR(100),
    country VARCHAR(2) NOT NULL, -- Code ISO 2 lettres
    phone VARCHAR(20),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer_id (customer_id),
    INDEX idx_type (type),
    INDEX idx_country (country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. TABLES COMMANDES ET PAIEMENTS
-- ============================================================================

-- Table des commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    
    -- Informations client (copie pour archivage)
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    
    -- Adresses
    billing_address JSON NOT NULL,
    shipping_address JSON,
    
    -- Montants
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    shipping_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    
    -- Statut et suivi
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
    shipping_status ENUM('not_shipped', 'partially_shipped', 'shipped', 'delivered') DEFAULT 'not_shipped',
    
    -- Informations de livraison
    shipping_method VARCHAR(100),
    tracking_number VARCHAR(100),
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    
    -- Métadonnées
    notes TEXT,
    admin_notes TEXT,
    currency VARCHAR(3) DEFAULT 'EUR',
    exchange_rate DECIMAL(10,4) DEFAULT 1.0000,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des articles de commande
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    variant_id INT,
    
    -- Informations produit (copie pour archivage)
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100),
    variant_info JSON, -- taille, couleur, etc.
    
    -- Prix et quantité
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des paiements
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    transaction_id VARCHAR(255),
    payment_method VARCHAR(50) NOT NULL, -- stripe, paypal, bank_transfer, etc.
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    gateway_response JSON,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. TABLES PANIER ET WISHLIST
-- ============================================================================

-- Table du panier
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    customer_id INT,
    product_id INT NOT NULL,
    variant_id INT,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de la wishlist (favoris)
CREATE TABLE IF NOT EXISTS wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    customer_id INT,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (session_id, customer_id, product_id),
    INDEX idx_session_id (session_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. TABLES PROMOTIONS ET COUPONS
-- ============================================================================

-- Table des coupons de réduction
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    
    -- Type de réduction
    discount_type ENUM('fixed', 'percentage') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    
    -- Conditions d'utilisation
    minimum_amount DECIMAL(10,2) DEFAULT 0.00,
    maximum_discount DECIMAL(10,2),
    usage_limit INT,
    usage_limit_per_customer INT DEFAULT 1,
    used_count INT DEFAULT 0,
    
    -- Restrictions
    product_ids JSON, -- IDs des produits éligibles
    category_ids JSON, -- IDs des catégories éligibles
    customer_ids JSON, -- IDs des clients éligibles
    
    -- Validité
    starts_at TIMESTAMP,
    expires_at TIMESTAMP,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table d'utilisation des coupons
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    order_id INT NOT NULL,
    customer_id INT,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_coupon_id (coupon_id),
    INDEX idx_order_id (order_id),
    INDEX idx_customer_id (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. TABLES AVIS ET ÉVALUATIONS
-- ============================================================================

-- Table des avis produits
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT,
    order_id INT,
    
    -- Contenu de l'avis
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    content TEXT,
    pros TEXT,
    cons TEXT,
    
    -- Métadonnées
    verified_purchase BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected', 'spam') DEFAULT 'pending',
    
    -- Informations client (anonymisées)
    reviewer_name VARCHAR(100),
    reviewer_email VARCHAR(100),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des votes d'utilité des avis
CREATE TABLE IF NOT EXISTS review_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    customer_id INT,
    session_id VARCHAR(255),
    vote_type ENUM('helpful', 'not_helpful') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (review_id) REFERENCES product_reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (review_id, customer_id, session_id),
    INDEX idx_review_id (review_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. TABLES CONTENU ET BLOG
-- ============================================================================

-- Table des articles de blog
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    featured_image VARCHAR(255),
    
    -- SEO
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    
    -- Classification
    category VARCHAR(100),
    tags JSON,
    
    -- Statut
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    
    -- Auteur
    author_id INT,
    
    -- Statistiques
    view_count INT DEFAULT 0,
    comment_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at),
    FULLTEXT idx_content_search (title, excerpt, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des pages statiques
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    
    -- SEO
    meta_title VARCHAR(255),
    meta_description TEXT,
    
    -- Statut
    status ENUM('active', 'inactive') DEFAULT 'active',
    
    -- Ordre d'affichage
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. TABLES SYSTÈME ET CONFIGURATION
-- ============================================================================

-- Table des paramètres système
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json', 'text') DEFAULT 'string',
    description TEXT,
    group_name VARCHAR(50) DEFAULT 'general',
    is_autoload BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_group_name (group_name),
    INDEX idx_is_autoload (is_autoload)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des logs d'activité
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    subject_type VARCHAR(100), -- products, orders, customers, etc.
    subject_id INT,
    properties JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL, -- order_placed, stock_low, etc.
    notifiable_type VARCHAR(100) NOT NULL, -- users, customers
    notifiable_id INT NOT NULL,
    data JSON NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_notifiable (notifiable_type, notifiable_id),
    INDEX idx_type (type),
    INDEX idx_read_at (read_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des sauvegardes de cache
CREATE TABLE IF NOT EXISTS cache (
    cache_key VARCHAR(255) PRIMARY KEY,
    cache_value LONGTEXT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. DONNÉES INITIALES
-- ============================================================================

-- Insertion d'un utilisateur admin par défaut
INSERT INTO users (username, email, password_hash, full_name, role) VALUES 
('admin', 'admin@stylehub.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur StyleHub', 'admin')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Insertion des catégories principales
INSERT INTO categories (name, slug, description, sort_order) VALUES 
('Femme', 'femme', 'Collection féminine complète', 1),
('Homme', 'homme', 'Collection masculine tendance', 2),
('Enfant', 'enfant', 'Mode pour enfants', 3),
('Accessoires', 'accessoires', 'Accessoires de mode pour tous', 4),
('Chaussures', 'chaussures', 'Chaussures pour tous les styles', 5),
('Sacs & Maroquinerie', 'sacs-maroquinerie', 'Sacs et accessoires en cuir', 6),
('Bijoux', 'bijoux', 'Bijoux fantaisie et précieux', 7)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Insertion des marques
INSERT INTO brands (name, slug, description) VALUES 
('StyleHub', 'stylehub', 'Marque maison de qualité'),
('Premium Fashion', 'premium-fashion', 'Mode haut de gamme'),
('Urban Style', 'urban-style', 'Style urbain moderne'),
('Classic Wear', 'classic-wear', 'Mode classique intemporelle')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Insertion des tailles standard
INSERT INTO sizes (name, category, size_type, sort_order) VALUES 
-- Vêtements femme/homme
('XS', 'vetements', 'clothing', 1),
('S', 'vetements', 'clothing', 2),
('M', 'vetements', 'clothing', 3),
('L', 'vetements', 'clothing', 4),
('XL', 'vetements', 'clothing', 5),
('XXL', 'vetements', 'clothing', 6),
('XXXL', 'vetements', 'clothing', 7),

-- Tailles numériques vêtements
('32', 'vetements', 'clothing', 10),
('34', 'vetements', 'clothing', 11),
('36', 'vetements', 'clothing', 12),
('38', 'vetements', 'clothing', 13),
('40', 'vetements', 'clothing', 14),
('42', 'vetements', 'clothing', 15),
('44', 'vetements', 'clothing', 16),
('46', 'vetements', 'clothing', 17),
('48', 'vetements', 'clothing', 18),

-- Chaussures
('35', 'chaussures', 'shoes', 20),
('36', 'chaussures', 'shoes', 21),
('37', 'chaussures', 'shoes', 22),
('38', 'chaussures', 'shoes', 23),
('39', 'chaussures', 'shoes', 24),
('40', 'chaussures', 'shoes', 25),
('41', 'chaussures', 'shoes', 26),
('42', 'chaussures', 'shoes', 27),
('43', 'chaussures', 'shoes', 28),
('44', 'chaussures', 'shoes', 29),
('45', 'chaussures', 'shoes', 30),
('46', 'chaussures', 'shoes', 31),
('47', 'chaussures', 'shoes', 32),

-- Accessoires
('Unique', 'accessoires', 'accessories', 40)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Insertion des couleurs de base
INSERT INTO colors (name, hex_code, rgb_code, color_family) VALUES 
('Noir', '#000000', '0,0,0', 'noir'),
('Blanc', '#FFFFFF', '255,255,255', 'blanc'),
('Gris clair', '#D3D3D3', '211,211,211', 'gris'),
('Gris', '#808080', '128,128,128', 'gris'),
('Gris foncé', '#404040', '64,64,64', 'gris'),
('Rouge', '#FF0000', '255,0,0', 'rouge'),
('Rouge foncé', '#8B0000', '139,0,0', 'rouge'),
('Bordeaux', '#800020', '128,0,32', 'rouge'),
('Rose', '#FFC0CB', '255,192,203', 'rose'),
('Rose fuchsia', '#FF1493', '255,20,147', 'rose'),
('Bleu marine', '#000080', '0,0,128', 'bleu'),
('Bleu royal', '#4169E1', '65,105,225', 'bleu'),
('Bleu ciel', '#87CEEB', '135,206,235', 'bleu'),
('Bleu turquoise', '#40E0D0', '64,224,208', 'bleu'),
('Vert', '#008000', '0,128,0', 'vert'),
('Vert olive', '#808000', '128,128,0', 'vert'),
('Vert émeraude', '#50C878', '80,200,120', 'vert'),
('Jaune', '#FFFF00', '255,255,0', 'jaune'),
('Jaune moutarde', '#FFDB58', '255,219,88', 'jaune'),
('Orange', '#FFA500', '255,165,0', 'orange'),
('Orange brûlé', '#CC5500', '204,85,0', 'orange'),
('Violet', '#800080', '128,0,128', 'violet'),
('Violet clair', '#DDA0DD', '221,160,221', 'violet'),
('Marron', '#A52A2A', '165,42,42', 'marron'),
('Marron clair', '#D2B48C', '210,180,140', 'marron'),
('Beige', '#F5F5DC', '245,245,220', 'beige'),
('Crème', '#FFFDD0', '255,253,208', 'beige'),
('Camel', '#C19A6B', '193,154,107', 'marron'),
('Kaki', '#F0E68C', '240,230,140', 'vert'),
('Argent', '#C0C0C0', '192,192,192', 'gris'),
('Or', '#FFD700', '255,215,0', 'jaune')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Paramètres système par défaut
INSERT INTO settings (setting_key, setting_value, setting_type, description, group_name) VALUES 
('site_name', 'StyleHub', 'string', 'Nom du site', 'general'),
('site_description', 'Votre boutique de mode en ligne', 'string', 'Description du site', 'general'),
('site_email', 'contact@stylehub.fr', 'string', 'Email de contact', 'general'),
('site_phone', '01 23 45 67 89', 'string', 'Téléphone de contact', 'general'),
('currency', 'EUR', 'string', 'Devise par défaut', 'shop'),
('currency_symbol', '€', 'string', 'Symbole de la devise', 'shop'),
('tax_rate', '20.00', 'string', 'Taux de TVA (%)', 'shop'),
('free_shipping_threshold', '50.00', 'string', 'Seuil de livraison gratuite', 'shop'),
('products_per_page', '12', 'integer', 'Produits par page', 'shop'),
('enable_reviews', '1', 'boolean', 'Activer les avis clients', 'features'),
('enable_wishlist', '1', 'boolean', 'Activer la wishlist', 'features'),
('enable_stock_management', '1', 'boolean', 'Gestion du stock', 'features'),
('low_stock_threshold', '5', 'integer', 'Seuil de stock faible', 'inventory'),
('out_of_stock_threshold', '0', 'integer', 'Seuil de rupture de stock', 'inventory')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

-- Pages statiques par défaut
INSERT INTO pages (title, slug, content, status, sort_order) VALUES 
('Conditions générales de vente', 'cgv', 'Contenu des conditions générales de vente...', 'active', 1),
('Politique de confidentialité', 'politique-confidentialite', 'Contenu de la politique de confidentialité...', 'active', 2),
('Mentions légales', 'mentions-legales', 'Contenu des mentions légales...', 'active', 3),
('Livraison et retours', 'livraison-retours', 'Informations sur la livraison et les retours...', 'active', 4),
('À propos', 'a-propos', 'Présentation de l\'entreprise...', 'active', 5)
ON DUPLICATE KEY UPDATE slug = VALUES(slug);

-- ============================================================================
-- 11. TRIGGERS ET FONCTIONS
-- ============================================================================

-- Trigger pour mettre à jour les statistiques produit après un avis
DELIMITER $$
CREATE TRIGGER update_product_rating_after_review 
AFTER INSERT ON product_reviews 
FOR EACH ROW 
BEGIN 
    UPDATE products 
    SET 
        rating_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved'),
        rating_average = (SELECT ROUND(AVG(rating), 2) FROM product_reviews WHERE product_id = NEW.product_id AND status = 'approved')
    WHERE id = NEW.product_id;
END$$

-- Trigger pour mettre à jour le stock après une commande
CREATE TRIGGER update_stock_after_order 
AFTER INSERT ON order_items 
FOR EACH ROW 
BEGIN 
    UPDATE products 
    SET 
        stock = stock - NEW.quantity,
        purchase_count = purchase_count + NEW.quantity
    WHERE id = NEW.product_id;
    
    -- Mettre à jour le statut de stock
    UPDATE products 
    SET stock_status = CASE 
        WHEN stock <= 0 THEN 'out_of_stock'
        WHEN stock <= min_stock THEN 'on_backorder'
        ELSE 'in_stock'
    END
    WHERE id = NEW.product_id;
END$$

-- Trigger pour générer automatiquement le numéro de commande
CREATE TRIGGER generate_order_number 
BEFORE INSERT ON orders 
FOR EACH ROW 
BEGIN 
    IF NEW.order_number IS NULL OR NEW.order_number = '' THEN
        SET NEW.order_number = CONCAT('ORD-', YEAR(NOW()), MONTH(NOW()), '-', LPAD(LAST_INSERT_ID(), 6, '0'));
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- 12. VUES POUR LES RAPPORTS
-- ============================================================================

-- Vue des produits populaires
CREATE OR REPLACE VIEW popular_products AS
SELECT 
    p.*,
    c.name as category_name,
    b.name as brand_name,
    COALESCE(p.sale_price, p.price) as effective_price,
    CASE WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price 
         THEN ROUND(((p.price - p.sale_price) / p.price) * 100) 
         ELSE 0 END as discount_percentage
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN brands b ON p.brand_id = b.id
WHERE p.status = 'active'
ORDER BY p.view_count DESC, p.purchase_count DESC;

-- Vue des commandes avec détails
CREATE OR REPLACE VIEW order_details AS
SELECT 
    o.*,
    c.first_name,
    c.last_name,
    c.email as customer_email_verified,
    COUNT(oi.id) as items_count,
    SUM(oi.quantity) as total_items
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- Vue des statistiques de vente par produit
CREATE OR REPLACE VIEW product_sales_stats AS
SELECT 
    p.id,
    p.name,
    p.sku,
    COALESCE(SUM(oi.quantity), 0) as total_sold,
    COALESCE(SUM(oi.total_price), 0) as total_revenue,
    COALESCE(AVG(oi.unit_price), 0) as avg_selling_price,
    COUNT(DISTINCT oi.order_id) as orders_count
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('processing', 'shipped', 'delivered')
GROUP BY p.id;

-- ============================================================================
-- 13. HARMONISATION DU SCHÉMA AVEC LE CODE
-- ============================================================================

-- Ajouter les colonnes utilisées par le code applicatif si elles n'existent pas
ALTER TABLE products
    ADD COLUMN IF NOT EXISTS brand VARCHAR(100) NULL AFTER brand_id,
    ADD COLUMN IF NOT EXISTS color VARCHAR(50) NULL AFTER brand,
    ADD COLUMN IF NOT EXISTS size VARCHAR(50) NULL AFTER color,
    ADD COLUMN IF NOT EXISTS tags JSON NULL AFTER gallery,
    ADD COLUMN IF NOT EXISTS rating DECIMAL(3,2) NOT NULL DEFAULT 0.00 AFTER rating_count;

-- Index pour les nouvelles colonnes
ALTER TABLE products
    ADD INDEX IF NOT EXISTS idx_brand_text (brand),
    ADD INDEX IF NOT EXISTS idx_color (color),
    ADD INDEX IF NOT EXISTS idx_size (size),
    ADD INDEX IF NOT EXISTS idx_rating (rating);

-- Noter: le modèle utilise rating pour le tri; rating_average reste pour la moyenne calculée

-- ============================================================================
-- FINALISATION
-- ============================================================================

-- Réactiver les vérifications de clés étrangères
SET foreign_key_checks = 1;

-- Optimiser les tables
OPTIMIZE TABLE products, orders, customers, product_images;

-- Message de confirmation
SELECT 'Base de données StyleHub créée avec succès!' as message;
SELECT 'Tables créées:', COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'stylehub_db';