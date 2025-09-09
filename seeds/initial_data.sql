USE stylehub_db;

-- Sizes
INSERT INTO sizes (name, category, sort_order) VALUES 
('XS', 'vetements', 1),
('S', 'vetements', 2),
('M', 'vetements', 3),
('L', 'vetements', 4),
('XL', 'vetements', 5),
('XXL', 'vetements', 6),
('34', 'vetements', 10),
('36', 'vetements', 11),
('38', 'vetements', 12),
('40', 'vetements', 13),
('42', 'vetements', 14),
('44', 'vetements', 15),
('35', 'chaussures', 20),
('36', 'chaussures', 21),
('37', 'chaussures', 22),
('38', 'chaussures', 23),
('39', 'chaussures', 24),
('40', 'chaussures', 25),
('41', 'chaussures', 26),
('42', 'chaussures', 27),
('43', 'chaussures', 28),
('44', 'chaussures', 29),
('45', 'chaussures', 30)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Colors
INSERT INTO colors (name, hex_code) VALUES 
('Noir', '#000000'),
('Blanc', '#FFFFFF'),
('Gris', '#808080'),
('Rouge', '#FF0000'),
('Bleu marine', '#000080'),
('Bleu ciel', '#87CEEB'),
('Vert', '#008000'),
('Jaune', '#FFFF00'),
('Rose', '#FFC0CB'),
('Violet', '#800080'),
('Orange', '#FFA500'),
('Marron', '#A52A2A'),
('Beige', '#F5F5DC'),
('Bordeaux', '#800020')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Products
INSERT INTO products (name, description, price, sale_price, sku, stock, category_id, brand, color, size, material, gender, season, image_url, featured) VALUES 
('Robe Midi Élégante', 'Robe midi fluide parfaite pour les occasions spéciales. Coupe flatteuse et tissu de qualité premium.', 89.99, 69.99, 'ROBE001', 15, (SELECT id FROM categories WHERE slug='femme'), 'StyleHub', 'Noir', 'M', 'Polyester', 'femme', 'toute_saison', 'uploads/robe-midi-noire.jpg', TRUE),
('Jean Slim Homme', 'Jean slim fit confortable avec stretch. Coupe moderne et délavage authentique.', 79.99, NULL, 'JEAN001', 25, (SELECT id FROM categories WHERE slug='homme'), 'DenimCo', 'Bleu', '32', 'Coton/Elasthanne', 'homme', 'toute_saison', 'uploads/jean-slim-homme.jpg', TRUE),
('Blazer Femme Chic', 'Blazer structuré pour un look professionnel. Doublure soyeuse et finitions soignées.', 129.99, 99.99, 'BLAZ001', 12, (SELECT id FROM categories WHERE slug='femme'), 'ChicStyle', 'Beige', 'L', 'Laine/Polyester', 'femme', 'automne', 'uploads/blazer-femme-beige.jpg', TRUE),
('Sneakers Unisexe', 'Baskets tendance en cuir véritable. Confort optimal pour un usage quotidien.', 119.99, NULL, 'SNEAK001', 30, (SELECT id FROM categories WHERE slug='accessoires'), 'UrbanStep', 'Blanc', '42', 'Cuir', 'unisexe', 'toute_saison', 'uploads/sneakers-blanc.jpg', FALSE),
('Sac à Main Cuir', 'Sac à main en cuir véritable avec compartiments multiples. Élégance et praticité.', 159.99, 139.99, 'SAC001', 8, (SELECT id FROM categories WHERE slug='accessoires'), 'LeatherLux', 'Cognac', 'Unique', 'Cuir véritable', 'femme', 'toute_saison', 'uploads/sac-cuir-cognac.jpg', TRUE),
('T-shirt Basique Homme', 'T-shirt en coton bio, coupe regular. Essentiel du dressing masculin.', 24.99, NULL, 'TSHIRT001', 50, (SELECT id FROM categories WHERE slug='homme'), 'BasicWear', 'Blanc', 'L', 'Coton Bio', 'homme', 'toute_saison', 'uploads/tshirt-blanc-homme.jpg', FALSE)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Example orders for analytics realism
INSERT INTO orders (customer_name, customer_email, total_amount, status, created_at) VALUES 
('Emma Dubois', 'emma.dubois@example.com', 159.98, 'delivered', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('Thomas Martin', 'thomas.martin@example.com', 209.97, 'shipped', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Léa Durand', 'lea.durand@example.com', 89.99, 'processing', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Sophie Bernard', 'sophie.bernard@example.com', 179.98, 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY))
ON DUPLICATE KEY UPDATE customer_name = VALUES(customer_name);

