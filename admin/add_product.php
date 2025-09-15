<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = '';

// Configuration pour les uploads
$MAX_IMAGES = 5;
$MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
$ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
$UPLOAD_DIR = '../uploads/';

// Créer le dossier d'upload si nécessaire
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

// Charger catégories, tailles et couleurs
$categories = [];
$sizes = [];
$colors = [];
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    // Catégories optionnelles
}
try {
    $sizes = $pdo->query("SELECT id, name, category FROM sizes ORDER BY sort_order ASC, name ASC")->fetchAll();
} catch (Exception $e) {
    // Tailles optionnelles
}
try {
    $colors = $pdo->query("SELECT id, name, hex_code FROM colors ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    // Couleurs optionnelles
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF invalide.';
    } else {
        $name = Security::sanitizeInput($_POST['name'] ?? '');
        $description = Security::sanitizeInput($_POST['description'] ?? '');
        $price = Security::sanitizeInput($_POST['price'] ?? '', 'float');
        $salePrice = !empty($_POST['sale_price']) ? Security::sanitizeInput($_POST['sale_price'], 'float') : null;
        $stock = Security::sanitizeInput($_POST['stock'] ?? 0, 'int');
        $categoryId = !empty($_POST['category_id']) ? Security::sanitizeInput($_POST['category_id'], 'int') : null;
        $brand = Security::sanitizeInput($_POST['brand'] ?? '');
        $material = Security::sanitizeInput($_POST['material'] ?? '');
        $gender = Security::sanitizeInput($_POST['gender'] ?? 'unisexe');
        $season = Security::sanitizeInput($_POST['season'] ?? 'toute_saison');
        $selectedSizes = isset($_POST['sizes']) && is_array($_POST['sizes']) ? array_map('intval', $_POST['sizes']) : [];
        $selectedColors = isset($_POST['colors']) && is_array($_POST['colors']) ? array_map('intval', $_POST['colors']) : [];
        $featured = isset($_POST['featured']) ? 1 : 0;
        $sku = Security::sanitizeInput($_POST['sku'] ?? '');
        // Stocks par taille
        $sizeStocks = [];
        if (!empty($_POST['size_stock']) && is_array($_POST['size_stock'])) {
            foreach ($_POST['size_stock'] as $sid => $val) {
                $sid = (int)$sid;
                $qty = (int)$val;
                if ($qty < 0) { $qty = 0; }
                $sizeStocks[$sid] = $qty;
            }
        }

        // Validation renforcée
        if (empty($name)) {
            $errors[] = 'Le nom du produit est requis.';
        } elseif (strlen($name) < 2) {
            $errors[] = 'Le nom du produit doit contenir au moins 2 caractères.';
        } elseif (strlen($name) > 255) {
            $errors[] = 'Le nom du produit ne peut pas dépasser 255 caractères.';
        }

        if (empty($description)) {
            $errors[] = 'La description est requise.';
        } elseif (strlen($description) < 10) {
            $errors[] = 'La description doit contenir au moins 10 caractères.';
        }

        if (empty($price) || !is_numeric($price) || $price <= 0) {
            $errors[] = 'Le prix doit être un nombre positif.';
        } elseif ($price > 999999.99) {
            $errors[] = 'Le prix ne peut pas dépasser 999,999.99 €.';
        }

        if ($salePrice !== null && (!is_numeric($salePrice) || $salePrice < 0)) {
            $errors[] = 'Le prix de vente doit être un nombre positif.';
        }

        if ($salePrice !== null && $salePrice >= $price) {
            $errors[] = 'Le prix de vente doit être inférieur au prix normal.';
        }

        if ($stock < 0) {
            $errors[] = 'Le stock ne peut pas être négatif.';
        }

        if (!empty($sku)) {
            // Vérifier l'unicité du SKU
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
            $stmt->execute([$sku]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Ce SKU existe déjà.';
            }
        }

        if (!in_array($gender, ['homme', 'femme', 'unisexe'])) {
            $errors[] = 'Genre non valide.';
        }

        if (!in_array($season, ['printemps', 'été', 'automne', 'hiver', 'toute_saison'])) {
            $errors[] = 'Saison non valide.';
        }

        // Gestion avancée des uploads d'images
        $imageUrl = null; // image principale (première)
        $galleryImages = [];
        $uploadedFiles = [];

        // Normaliser les inputs fichiers pour images multiples
        $files = [];
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $files[] = [
                        'name' => $_FILES['images']['name'][$i],
                        'type' => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error' => $_FILES['images']['error'][$i],
                        'size' => $_FILES['images']['size'][$i]
                    ];
                }
            }
        }

        // Validation du nombre d'images
        if (count($files) > $MAX_IMAGES) {
            $errors[] = "Trop d'images sélectionnées. Maximum : {$MAX_IMAGES}.";
        }

        // Traitement des images si pas d'erreurs
        if (empty($errors) && !empty($files)) {
            foreach ($files as $index => $file) {
                // Vérification des erreurs d'upload
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = "Erreur d'upload pour l'image " . ($index + 1) . " (code {$file['error']}).";
                    continue;
                }

                // Validation sécurisée de l'image
                $validation = Security::validateImageUpload($file);
                if (!$validation['valid']) {
                    $errors[] = "Image " . ($index + 1) . " : " . $validation['error'];
                    continue;
                }

                // Générer un nom de fichier sécurisé
                $fileName = Security::generateSecureFileName($file['name']);
                $uploadPath = $UPLOAD_DIR . $fileName;

                // Déplacer le fichier
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // Redimensionner l'image pour optimiser
                    if (function_exists('resizeImage')) {
                        resizeImage($uploadPath, $uploadPath, 1200, 1200);
                    }
                    
                    $url = 'uploads/' . $fileName;
                    if ($imageUrl === null) {
                        $imageUrl = $url; // première comme principale
                    }
                    $galleryImages[] = $url;
                    $uploadedFiles[] = $uploadPath; // Pour nettoyage en cas d'erreur
                } else {
                    $errors[] = "Impossible d'enregistrer l'image " . ($index + 1) . ".";
                }
            }
        }

        // Si pas d'erreurs, ajouter le produit
        if (empty($errors)) {
            try {
                // Démarrer une transaction si non démarrée
                if (!$pdo->inTransaction()) {
                    $pdo->beginTransaction();
                }
                // Préparer les données JSON pour les champs compatibles
                $galleryJson = !empty($galleryImages) ? json_encode($galleryImages) : null;
                $sizesJson = !empty($selectedSizes) ? json_encode($selectedSizes) : null;
                
                // Générer un SKU automatique si vide
                if (empty($sku)) {
                    $sku = 'PRD' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                }
                
                // Insérer le produit avec les colonnes existantes
                $sql = "INSERT INTO products (name, description, price, stock, image_url, status, created_at";
                $values = "VALUES (:name, :description, :price, :stock, :image_url, 'active', NOW()";
                $params = [
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $price,
                    ':stock' => $stock,
                    ':image_url' => $imageUrl
                ];
                
                // Ajouter les colonnes optionnelles si elles existent
                $columns = $pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);

                // Générer et ajouter le slug si la colonne existe
                if (in_array('slug', $columns)) {
                    $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', @iconv('UTF-8', 'ASCII//TRANSLIT', $name)), '-'));
                    if ($baseSlug === '' || $baseSlug === '-') { $baseSlug = 'produit'; }
                    $candidate = $baseSlug;
                    $i = 1;
                    $slugCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
                    while (true) {
                        $slugCheckStmt->execute([$candidate]);
                        if ((int)$slugCheckStmt->fetchColumn() === 0) { break; }
                        $candidate = $baseSlug . '-' . (++$i);
                    }
                    $sql .= ", slug";
                    $values .= ", :slug";
                    $params[':slug'] = $candidate;
                }
                
                if (in_array('sale_price', $columns)) {
                    $sql .= ", sale_price";
                    $values .= ", :sale_price";
                    $params[':sale_price'] = $salePrice;
                }
                
                if (in_array('sku', $columns)) {
                    $sql .= ", sku";
                    $values .= ", :sku";
                    $params[':sku'] = $sku;
                }
                
                if (in_array('category_id', $columns)) {
                    $sql .= ", category_id";
                    $values .= ", :category_id";
                    $params[':category_id'] = $categoryId;
                }
                
                if (in_array('brand', $columns)) {
                    $sql .= ", brand";
                    $values .= ", :brand";
                    $params[':brand'] = $brand ?: null;
                }
                
                if (in_array('material', $columns)) {
                    $sql .= ", material";
                    $values .= ", :material";
                    $params[':material'] = $material ?: null;
                }
                
                if (in_array('gender', $columns)) {
                    $sql .= ", gender";
                    $values .= ", :gender";
                    $params[':gender'] = $gender;
                }
                
                if (in_array('season', $columns)) {
                    $sql .= ", season";
                    $values .= ", :season";
                    $params[':season'] = $season;
                }
                
                if (in_array('gallery', $columns)) {
                    $sql .= ", gallery";
                    $values .= ", :gallery";
                    $params[':gallery'] = $galleryJson;
                }
                
                if (in_array('featured', $columns)) {
                    $sql .= ", featured";
                    $values .= ", :featured";
                    $params[':featured'] = $featured;
                }
                
                $sql .= ") " . $values . ")";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                $newProductId = $pdo->lastInsertId();
                
                // Enregistrer les tailles si les tables existent
                if (!empty($selectedSizes)) {
                    try {
                        $sizeStmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)");
                        foreach ($selectedSizes as $sizeId) {
                            $sizeStock = isset($sizeStocks[$sizeId]) ? (int)$sizeStocks[$sizeId] : 0;
                            $sizeStmt->execute([$newProductId, (int)$sizeId, $sizeStock]);
                        }
                    } catch (PDOException $e) {
                        // Table n'existe pas, ignorer
                    }
                }
                
                // Enregistrer les images supplémentaires si la table existe
                if (!empty($galleryImages) && count($galleryImages) > 1) {
                    try {
                        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)");
                        foreach ($galleryImages as $idx => $url) {
                            if ($idx > 0) { // Ignorer la première (déjà dans image_url)
                                $imgStmt->execute([$newProductId, $url, $idx]);
                            }
                        }
                    } catch (PDOException $e) {
                        // Table n'existe pas, ignorer
                    }
                }
                
                // Enregistrer les couleurs sélectionnées si la table product_colors existe
                if (!empty($selectedColors)) {
                    try {
                        $colorStmt = $pdo->prepare("INSERT INTO product_colors (product_id, color_id) VALUES (:pid, :cid)");
                        foreach ($selectedColors as $colorId) {
                            $colorStmt->execute([':pid' => (int)$newProductId, ':cid' => (int)$colorId]);
                        }
                    } catch (PDOException $e) {
                        // Table product_colors non disponible, on ignore
                    }
                }

                $pdo->commit();

                // Logger l'action
                Security::logAction('product_created', [
                    'product_id' => $newProductId,
                    'name' => $name,
                    'price' => $price,
                    'images_count' => count($galleryImages)
                ]);

                $_SESSION['message'] = 'Produit ajouté avec succès ! ' . count($galleryImages) . ' image(s) uploadée(s).';
                $_SESSION['message_type'] = 'success';
                header('Location: products.php');
                exit();

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                
                // Nettoyer les fichiers uploadés en cas d'erreur
                foreach ($uploadedFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                
                $errors[] = 'Erreur lors de l\'ajout du produit. Veuillez réessayer.';
                error_log('Error adding product: ' . $e->getMessage());
            }
        } else {
            // Nettoyer les fichiers en cas d'erreur de validation
            foreach ($uploadedFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
        }
    }

$pageTitle = 'Ajouter un Produit';
$active = 'add_product';
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
                        <h1 class="page-title">Ajouter un Produit</h1>
                        <p class="page-subtitle">Créez un nouveau produit pour votre catalogue</p>
                    </div>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Messages d'erreur -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Erreurs détectées :</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informations du produit</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate data-auto-save="add_product">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Informations de base -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Informations de base
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="name" class="form-label">
                                                <i class="fas fa-tag me-1"></i>Nom du produit *
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="name" 
                                                   name="name" 
                                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                                   required
                                                   maxlength="255"
                                                   placeholder="Ex: Robe midi élégante">
                                            <div class="invalid-feedback">
                                                Veuillez saisir un nom de produit (2-255 caractères).
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="description" class="form-label">
                                                <i class="fas fa-align-left me-1"></i>Description *
                                            </label>
                                            <textarea class="form-control" 
                                                      id="description" 
                                                      name="description" 
                                                      rows="5" 
                                                      required
                                                      placeholder="Décrivez votre produit en détail..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                            <div class="invalid-feedback">
                                                Veuillez saisir une description (minimum 10 caractères).
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Une description détaillée améliore les ventes
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="sku" class="form-label">
                                                        <i class="fas fa-barcode me-1"></i>SKU (Code produit)
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="sku" 
                                                           name="sku" 
                                                           value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>"
                                                           placeholder="Ex: ROBE001">
                                                    <div class="form-text">
                                                        Code unique pour identifier le produit
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="brand" class="form-label">
                                                        <i class="fas fa-certificate me-1"></i>Marque
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="brand" 
                                                           name="brand" 
                                                           value="<?php echo htmlspecialchars($_POST['brand'] ?? ''); ?>"
                                                           placeholder="Ex: StyleHub">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Prix et stock -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-euro-sign me-2"></i>Prix et Stock
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="price" class="form-label">
                                                        <i class="fas fa-euro-sign me-1"></i>Prix normal (€) *
                                                    </label>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           id="price" 
                                                           name="price" 
                                                           step="0.01" 
                                                           min="0" 
                                                           max="999999.99"
                                                           value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                                           required
                                                           placeholder="0.00">
                                                    <div class="invalid-feedback">
                                                        Veuillez saisir un prix valide (max 999,999.99 €).
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="sale_price" class="form-label">
                                                        <i class="fas fa-percentage me-1"></i>Prix promotionnel (€)
                                                    </label>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           id="sale_price" 
                                                           name="sale_price" 
                                                           step="0.01" 
                                                           min="0"
                                                           value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>"
                                                           placeholder="0.00">
                                                    <div class="form-text">
                                                        Prix de vente (doit être < prix normal)
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="stock" class="form-label">
                                                        <i class="fas fa-boxes me-1"></i>Stock global
                                                    </label>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           id="stock" 
                                                           name="stock" 
                                                           min="0" 
                                                           value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>"
                                                           placeholder="0">
                                                    <div class="form-text">
                                                        Quantité totale disponible
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Catégorisation et attributs -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-layer-group me-2"></i>Catégorisation
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="category_id" class="form-label">
                                                        <i class="fas fa-layer-group me-1"></i>Catégorie
                                                    </label>
                                                    <select class="form-select" id="category_id" name="category_id">
                                                        <option value="">-- Sélectionner une catégorie --</option>
                                                        <?php foreach ($categories as $cat): ?>
                                                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)($_POST['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($cat['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="gender" class="form-label">
                                                        <i class="fas fa-venus-mars me-1"></i>Genre
                                                    </label>
                                                    <select class="form-select" id="gender" name="gender">
                                                        <option value="unisexe" <?php echo ($_POST['gender'] ?? 'unisexe') === 'unisexe' ? 'selected' : ''; ?>>Unisexe</option>
                                                        <option value="homme" <?php echo ($_POST['gender'] ?? '') === 'homme' ? 'selected' : ''; ?>>Homme</option>
                                                        <option value="femme" <?php echo ($_POST['gender'] ?? '') === 'femme' ? 'selected' : ''; ?>>Femme</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="season" class="form-label">
                                                        <i class="fas fa-calendar-alt me-1"></i>Saison
                                                    </label>
                                                    <select class="form-select" id="season" name="season">
                                                        <option value="toute_saison" <?php echo ($_POST['season'] ?? 'toute_saison') === 'toute_saison' ? 'selected' : ''; ?>>Toute saison</option>
                                                        <option value="printemps" <?php echo ($_POST['season'] ?? '') === 'printemps' ? 'selected' : ''; ?>>Printemps</option>
                                                        <option value="été" <?php echo ($_POST['season'] ?? '') === 'été' ? 'selected' : ''; ?>>Été</option>
                                                        <option value="automne" <?php echo ($_POST['season'] ?? '') === 'automne' ? 'selected' : ''; ?>>Automne</option>
                                                        <option value="hiver" <?php echo ($_POST['season'] ?? '') === 'hiver' ? 'selected' : ''; ?>>Hiver</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="material" class="form-label">
                                                        <i class="fas fa-tshirt me-1"></i>Matière
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="material" 
                                                           name="material" 
                                                           value="<?php echo htmlspecialchars($_POST['material'] ?? ''); ?>"
                                                           placeholder="Ex: Coton, Polyester, Soie...">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" id="featured" name="featured" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="featured">
                                                <i class="fas fa-star text-warning me-1"></i>
                                                Produit mis en avant
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tailles disponibles -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-ruler-combined me-2"></i>Tailles et stock par taille
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php 
                                            $sizesByCategory = [];
                                            foreach ($sizes as $size) {
                                                $sizesByCategory[$size['category']][] = $size;
                                            }
                                            ?>
                                            <?php foreach ($sizesByCategory as $category => $categorySizes): ?>
                                                <div class="col-md-6 mb-3">
                                                    <h6 class="text-muted text-uppercase mb-2"><?php echo ucfirst($category); ?></h6>
                                                    <?php foreach ($categorySizes as $size): ?>
                                                        <div class="d-flex align-items-center gap-2 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" 
                                                                       type="checkbox" 
                                                                       id="size_<?php echo $size['id']; ?>" 
                                                                       name="sizes[]" 
                                                                       value="<?php echo (int)$size['id']; ?>" 
                                                                       <?php echo in_array((int)$size['id'], $selectedSizes ?? []) ? 'checked' : ''; ?>>
                                                                <label class="form-check-label fw-medium" for="size_<?php echo $size['id']; ?>">
                                                                    <?php echo htmlspecialchars($size['name']); ?>
                                                                </label>
                                                            </div>
                                                            <input type="number" 
                                                                   class="form-control form-control-sm" 
                                                                   name="size_stock[<?php echo (int)$size['id']; ?>]" 
                                                                   min="0" 
                                                                   placeholder="Stock"
                                                                   value="<?php echo htmlspecialchars($_POST['size_stock'][$size['id']] ?? ''); ?>"
                                                                   style="width: 80px;">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Sélectionnez les tailles disponibles et définissez un stock spécifique (optionnel)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <!-- Upload d'images -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-images me-2"></i>Photos du produit
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="upload-zone" id="uploadZone">
                                                <div class="upload-zone-content text-center p-4">
                                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                                    <h6>Glissez-déposez vos images ici</h6>
                                                    <p class="text-muted small mb-3">ou cliquez pour sélectionner</p>
                                                    <input type="file" 
                                                           class="form-control d-none" 
                                                           id="images" 
                                                           name="images[]" 
                                                           accept="image/jpeg,image/png,image/jpg,image/webp"
                                                           multiple>
                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('images').click()">
                                                        <i class="fas fa-folder-open me-1"></i>Choisir les fichiers
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Formats : JPG, PNG, WEBP • Max <?php echo $MAX_IMAGES; ?> images • 2MB max par image
                                            </div>
                                        </div>

                                        <!-- Aperçus des images -->
                                        <div id="imagesPreview" class="mt-3"></div>
                                        
                                        <!-- Compteur d'images -->
                                        <div id="imageCounter" class="text-center mt-2 small text-muted" style="display: none;">
                                            <span id="currentCount">0</span> / <?php echo $MAX_IMAGES; ?> images
                                        </div>
                                    </div>
                                </div>

                                <!-- Conseils et informations -->
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-lightbulb text-warning me-1"></i>
                                            Conseils pour les photos
                                        </h6>
                                        <ul class="small mb-3">
                                            <li>Utilisez des images de haute qualité</li>
                                            <li>Privilégiez un fond neutre et uniforme</li>
                                            <li>Montrez le produit sous plusieurs angles</li>
                                            <li>La première image sera l'image principale</li>
                                            <li>Évitez les images floues ou sombres</li>
                                        </ul>
                                        
                                        <div class="alert alert-info alert-sm mb-0">
                                            <i class="fas fa-magic me-1"></i>
                                            <small>Les images seront automatiquement redimensionnées et optimisées</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Actions -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        <small>Toutes les données sont sécurisées et validées</small>
                                    </div>
                                    <div class="btn-group">
                                        <a href="products.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Retour
                                        </a>
                                        <button type="reset" class="btn btn-outline-warning">
                                            <i class="fas fa-undo me-2"></i>Réinitialiser
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-plus me-2"></i>Créer le produit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('images');
    const previewContainer = document.getElementById('imagesPreview');
    const imageCounter = document.getElementById('imageCounter');
    const currentCount = document.getElementById('currentCount');
    const maxImages = <?php echo $MAX_IMAGES; ?>;
    let selectedFiles = [];

    // Style pour la zone de drop
    const uploadZoneStyle = `
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        transition: all 0.3s ease;
        cursor: pointer;
    `;
    uploadZone.style.cssText = uploadZoneStyle;

    // Drag & Drop handlers
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadZone.style.borderColor = '#0d6efd';
        uploadZone.style.backgroundColor = '#f8f9ff';
    });

    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadZone.style.borderColor = '#dee2e6';
        uploadZone.style.backgroundColor = 'transparent';
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadZone.style.borderColor = '#dee2e6';
        uploadZone.style.backgroundColor = 'transparent';
        
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });

    // Click handler pour la zone de drop
    uploadZone.addEventListener('click', function() {
        fileInput.click();
    });

    // File input change handler
    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        handleFiles(files);
    });

    function handleFiles(files) {
        // Filtrer les fichiers image
        const imageFiles = files.filter(file => file.type.startsWith('image/'));
        
        if (imageFiles.length === 0) {
            showAlert('Veuillez sélectionner uniquement des fichiers image.', 'warning');
            return;
        }

        // Vérifier le nombre total d'images
        if (selectedFiles.length + imageFiles.length > maxImages) {
            showAlert(`Vous ne pouvez sélectionner que ${maxImages} images maximum.`, 'warning');
            return;
        }

        // Ajouter les nouveaux fichiers
        imageFiles.forEach(file => {
            if (file.size > 2 * 1024 * 1024) { // 2MB
                showAlert(`L'image "${file.name}" est trop volumineuse (max 2MB).`, 'warning');
                return;
            }
            
            selectedFiles.push(file);
            createPreview(file, selectedFiles.length - 1);
        });

        updateCounter();
        updateFileInput();
    }

    function createPreview(file, index) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'position-relative d-inline-block me-2 mb-2';
            previewDiv.innerHTML = `
                <img src="${e.target.result}" 
                     class="img-thumbnail" 
                     style="width: 100px; height: 100px; object-fit: cover;">
                <button type="button" 
                        class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                        style="transform: translate(50%, -50%); padding: 2px 6px;"
                        onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
                <div class="text-center mt-1">
                    <small class="text-muted">${file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name}</small>
                </div>
            `;
            previewContainer.appendChild(previewDiv);
        };
        reader.readAsDataURL(file);
    }

    window.removeImage = function(index) {
        selectedFiles.splice(index, 1);
        updatePreview();
        updateCounter();
        updateFileInput();
    };

    function updatePreview() {
        previewContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            createPreview(file, index);
        });
    }

    function updateCounter() {
        currentCount.textContent = selectedFiles.length;
        imageCounter.style.display = selectedFiles.length > 0 ? 'block' : 'none';
        
        if (selectedFiles.length >= maxImages) {
            uploadZone.style.opacity = '0.5';
            uploadZone.style.pointerEvents = 'none';
        } else {
            uploadZone.style.opacity = '1';
            uploadZone.style.pointerEvents = 'auto';
        }
    }

    function updateFileInput() {
        // Créer un nouveau DataTransfer pour mettre à jour l'input
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }

    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.page-content');
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Validation du formulaire
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const description = document.getElementById('description').value.trim();

        // Normaliser les nombres (virgules -> points, suppression des espaces)
        const priceEl = document.getElementById('price');
        const salePriceEl = document.getElementById('sale_price');
        const normalize = (v) => {
            if (v === undefined || v === null) return '';
            return String(v).replace(/\s/g, '').replace(',', '.');
        };
        priceEl.value = normalize(priceEl.value);
        salePriceEl.value = normalize(salePriceEl.value);

        const price = parseFloat(priceEl.value);
        const salePrice = salePriceEl.value;
        
        let errors = [];
        
        if (name.length < 2) {
            errors.push('Le nom du produit doit contenir au moins 2 caractères.');
        }
        
        if (description.length < 10) {
            errors.push('La description doit contenir au moins 10 caractères.');
        }
        
        if (isNaN(price) || price <= 0) {
            errors.push('Le prix doit être un nombre positif.');
        }
        
        if (salePrice && (isNaN(parseFloat(salePrice)) || parseFloat(salePrice) >= price)) {
            errors.push('Le prix promotionnel doit être inférieur au prix normal.');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            showAlert('Erreurs détectées :<br>• ' + errors.join('<br>• '), 'danger');
            return false;
        }
        
        // Désactiver le bouton de soumission pour éviter les doublons
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
    });

    // Calcul automatique du pourcentage de réduction
    const priceInput = document.getElementById('price');
    const salePriceInput = document.getElementById('sale_price');
    
    function calculateDiscount() {
        const price = parseFloat(priceInput.value);
        const salePrice = parseFloat(salePriceInput.value);
        
        if (price && salePrice && salePrice < price) {
            const discount = Math.round(((price - salePrice) / price) * 100);
            salePriceInput.title = `Réduction de ${discount}%`;
        } else {
            salePriceInput.title = '';
        }
    }
    
    priceInput.addEventListener('input', calculateDiscount);
    salePriceInput.addEventListener('input', calculateDiscount);
});
</script>

<?php include 'layouts/footer.php'; ?>