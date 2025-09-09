USE stylehub_db;

-- Settings table for site-wide parameters
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text','number','boolean','email','url') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default settings if not present
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name','StyleHub','text','Nom du site'),
('site_description','Votre destination mode pour un style unique et tendance','text','Description du site'),
('site_email','contact@stylehub.fr','email','Email de contact'),
('site_phone','01 42 86 95 73','text','Téléphone de contact'),
('site_address','25 Avenue des Champs-Élysées, 75008 Paris','text','Adresse'),
('currency','EUR','text','Devise'),
('products_per_page','12','number','Produits par page'),
('maintenance_mode','0','boolean','Mode maintenance'),
('allow_registration','1','boolean','Autoriser inscriptions');

-- Ensure customers has password_hash for client auth
ALTER TABLE customers 
    ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL AFTER email;

