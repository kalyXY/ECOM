<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = [];
$warnings = [];

// Vérifier et créer les tables manquantes
$tables = [
    'product_images' => "
        CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_product (product_id),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    
    'sizes' => "
        CREATE TABLE IF NOT EXISTS sizes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(20) NOT NULL,
            category VARCHAR(50) DEFAULT 'general',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    
    'product_sizes' => "
        CREATE TABLE IF NOT EXISTS product_sizes (
            product_id INT NOT NULL,
            size_id INT NOT NULL,
            stock INT DEFAULT NULL,
            PRIMARY KEY (product_id, size_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE CASCADE,
            INDEX idx_stock (stock)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    
    'colors' => "
        CREATE TABLE IF NOT EXISTS colors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            hex_code VARCHAR(7) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    
    'product_colors' => "
        CREATE TABLE IF NOT EXISTS product_colors (
            product_id INT NOT NULL,
            color_id INT NOT NULL,
            PRIMARY KEY (product_id, color_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    "
];

// Vérifier et ajouter les colonnes manquantes dans la table products
$productColumns = [
    'sale_price' => 'ALTER TABLE products ADD COLUMN sale_price DECIMAL(10,2) DEFAULT NULL AFTER price',
    'sku' => 'ALTER TABLE products ADD COLUMN sku VARCHAR(100) UNIQUE AFTER sale_price',
    'brand' => 'ALTER TABLE products ADD COLUMN brand VARCHAR(100) AFTER category_id',
    'material' => 'ALTER TABLE products ADD COLUMN material VARCHAR(100) AFTER brand',
    'featured' => 'ALTER TABLE products ADD COLUMN featured BOOLEAN DEFAULT FALSE AFTER material'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        $pdo->beginTransaction();
        
        // Créer les tables
        foreach ($tables as $tableName => $sql) {
            try {
                $pdo->exec($sql);
                $success[] = "Table '$tableName' créée ou vérifiée avec succès.";
            } catch (PDOException $e) {
                $warnings[] = "Table '$tableName' : " . $e->getMessage();
            }
        }
        
        // Ajouter les colonnes manquantes
        foreach ($productColumns as $columnName => $sql) {
            try {
                // Vérifier si la colonne existe déjà
                $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE '$columnName'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec($sql);
                    $success[] = "Colonne '$columnName' ajoutée à la table products.";
                } else {
                    $warnings[] = "Colonne '$columnName' existe déjà dans la table products.";
                }
            } catch (PDOException $e) {
                $warnings[] = "Colonne '$columnName' : " . $e->getMessage();
            }
        }
        
        // Insérer les données de base pour les tailles
        $defaultSizes = [
            // Vêtements
            ['name' => 'XS', 'category' => 'vetements', 'sort_order' => 1],
            ['name' => 'S', 'category' => 'vetements', 'sort_order' => 2],
            ['name' => 'M', 'category' => 'vetements', 'sort_order' => 3],
            ['name' => 'L', 'category' => 'vetements', 'sort_order' => 4],
            ['name' => 'XL', 'category' => 'vetements', 'sort_order' => 5],
            ['name' => 'XXL', 'category' => 'vetements', 'sort_order' => 6],
            
            // Tailles numériques
            ['name' => '34', 'category' => 'vetements', 'sort_order' => 10],
            ['name' => '36', 'category' => 'vetements', 'sort_order' => 11],
            ['name' => '38', 'category' => 'vetements', 'sort_order' => 12],
            ['name' => '40', 'category' => 'vetements', 'sort_order' => 13],
            ['name' => '42', 'category' => 'vetements', 'sort_order' => 14],
            ['name' => '44', 'category' => 'vetements', 'sort_order' => 15],
            
            // Chaussures
            ['name' => '35', 'category' => 'chaussures', 'sort_order' => 20],
            ['name' => '36', 'category' => 'chaussures', 'sort_order' => 21],
            ['name' => '37', 'category' => 'chaussures', 'sort_order' => 22],
            ['name' => '38', 'category' => 'chaussures', 'sort_order' => 23],
            ['name' => '39', 'category' => 'chaussures', 'sort_order' => 24],
            ['name' => '40', 'category' => 'chaussures', 'sort_order' => 25],
            ['name' => '41', 'category' => 'chaussures', 'sort_order' => 26],
            ['name' => '42', 'category' => 'chaussures', 'sort_order' => 27],
            ['name' => '43', 'category' => 'chaussures', 'sort_order' => 28],
            ['name' => '44', 'category' => 'chaussures', 'sort_order' => 29],
            ['name' => '45', 'category' => 'chaussures', 'sort_order' => 30]
        ];
        
        try {
            // Vérifier si des tailles existent déjà
            $stmt = $pdo->query("SELECT COUNT(*) FROM sizes");
            $existingSizes = $stmt->fetchColumn();
            
            if ($existingSizes == 0) {
                $stmt = $pdo->prepare("INSERT INTO sizes (name, category, sort_order) VALUES (?, ?, ?)");
                foreach ($defaultSizes as $size) {
                    $stmt->execute([$size['name'], $size['category'], $size['sort_order']]);
                }
                $success[] = "Tailles par défaut insérées (" . count($defaultSizes) . " tailles).";
            } else {
                $warnings[] = "Des tailles existent déjà ($existingSizes tailles trouvées).";
            }
        } catch (PDOException $e) {
            $warnings[] = "Erreur lors de l'insertion des tailles : " . $e->getMessage();
        }
        
        // Insérer les couleurs de base
        $defaultColors = [
            ['name' => 'Noir', 'hex_code' => '#000000'],
            ['name' => 'Blanc', 'hex_code' => '#FFFFFF'],
            ['name' => 'Gris', 'hex_code' => '#808080'],
            ['name' => 'Rouge', 'hex_code' => '#FF0000'],
            ['name' => 'Bleu marine', 'hex_code' => '#000080'],
            ['name' => 'Bleu ciel', 'hex_code' => '#87CEEB'],
            ['name' => 'Vert', 'hex_code' => '#008000'],
            ['name' => 'Jaune', 'hex_code' => '#FFFF00'],
            ['name' => 'Rose', 'hex_code' => '#FFC0CB'],
            ['name' => 'Violet', 'hex_code' => '#800080'],
            ['name' => 'Orange', 'hex_code' => '#FFA500'],
            ['name' => 'Marron', 'hex_code' => '#A52A2A'],
            ['name' => 'Beige', 'hex_code' => '#F5F5DC'],
            ['name' => 'Bordeaux', 'hex_code' => '#800020']
        ];
        
        try {
            // Vérifier si des couleurs existent déjà
            $stmt = $pdo->query("SELECT COUNT(*) FROM colors");
            $existingColors = $stmt->fetchColumn();
            
            if ($existingColors == 0) {
                $stmt = $pdo->prepare("INSERT INTO colors (name, hex_code) VALUES (?, ?)");
                foreach ($defaultColors as $color) {
                    $stmt->execute([$color['name'], $color['hex_code']]);
                }
                $success[] = "Couleurs par défaut insérées (" . count($defaultColors) . " couleurs).";
            } else {
                $warnings[] = "Des couleurs existent déjà ($existingColors couleurs trouvées).";
            }
        } catch (PDOException $e) {
            $warnings[] = "Erreur lors de l'insertion des couleurs : " . $e->getMessage();
        }
        
        $pdo->commit();
        $success[] = "Configuration terminée avec succès !";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = "Erreur lors de la configuration : " . $e->getMessage();
    }
}

// Vérifier l'état actuel
$currentState = [];
foreach (array_keys($tables) as $tableName) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tableName");
        $count = $stmt->fetchColumn();
        $currentState[$tableName] = $count;
    } catch (PDOException $e) {
        $currentState[$tableName] = 'N/A';
    }
}

$pageTitle = 'Configuration des fonctionnalités avancées';
$active = 'settings';
include 'layouts/header.php';
?>

<div class="admin-wrapper">
    <?php include 'layouts/sidebar.php'; ?>
    
    <main class="admin-content">
        <?php include 'layouts/topbar.php'; ?>
        
        <div class="page-content">
            <!-- En-tête de page -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Configuration avancée</h1>
                        <p class="page-subtitle">Mise en place des fonctionnalités images multiples et variantes</p>
                    </div>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour aux produits
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Erreurs :</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>Succès :</h6>
                    <ul class="mb-0">
                        <?php foreach ($success as $msg): ?>
                            <li><?php echo htmlspecialchars($msg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($warnings)): ?>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-info-circle me-2"></i>Informations :</h6>
                    <ul class="mb-0">
                        <?php foreach ($warnings as $warning): ?>
                            <li><?php echo htmlspecialchars($warning); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- État actuel -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-database me-2"></i>État actuel de la base de données
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Table</th>
                                            <th>Enregistrements</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($currentState as $tableName => $count): ?>
                                            <tr>
                                                <td><code><?php echo $tableName; ?></code></td>
                                                <td>
                                                    <?php if ($count === 'N/A'): ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info"><?php echo $count; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($count === 'N/A'): ?>
                                                        <span class="badge bg-danger">Manquante</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Présente</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Configuration -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>Lancer la configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>Cette opération va :</p>
                            <ul>
                                <li>Créer les tables manquantes pour les images multiples</li>
                                <li>Créer les tables pour les tailles et couleurs</li>
                                <li>Ajouter les colonnes manquantes à la table products</li>
                                <li>Insérer les données de base (tailles et couleurs standard)</li>
                            </ul>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note :</strong> Cette opération est sécurisée et ne supprimera aucune donnée existante.
                            </div>
                            
                            <form method="POST">
                                <button type="submit" name="setup" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-rocket me-2"></i>Lancer la configuration
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Fonctionnalités -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-star me-2"></i>Nouvelles fonctionnalités
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-images text-primary me-2"></i>
                                    <strong>Images multiples</strong>
                                    <br><small class="text-muted">Jusqu'à 5 images par produit avec drag & drop</small>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-ruler-combined text-success me-2"></i>
                                    <strong>Gestion des tailles</strong>
                                    <br><small class="text-muted">Tailles multiples avec stock individuel</small>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-palette text-warning me-2"></i>
                                    <strong>Gestion des couleurs</strong>
                                    <br><small class="text-muted">Couleurs avec aperçu visuel</small>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-percentage text-info me-2"></i>
                                    <strong>Prix promotionnels</strong>
                                    <br><small class="text-muted">Gestion des réductions</small>
                                </li>
                                <li>
                                    <i class="fas fa-shield-alt text-danger me-2"></i>
                                    <strong>Sécurité renforcée</strong>
                                    <br><small class="text-muted">Validation avancée des uploads</small>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'layouts/footer.php'; ?>