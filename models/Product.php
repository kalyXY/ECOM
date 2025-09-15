<?php
/**
 * Modèle Product avec gestion optimisée des stocks
 * StyleHub E-Commerce Platform
 */

class Product {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtenir tous les produits avec cache et filtres
     */
    public function getAll($filters = [], $page = 1, $limit = 12, $useCache = true) {
        $cacheKey = 'products_' . md5(serialize($filters) . "_page_{$page}_limit_{$limit}");
        
        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        $where = ["p.status = 'active'"];
        $params = [];
        
        // Filtres
        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search OR p.brand LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['gender'])) {
            $where[] = "p.gender = :gender";
            $params[':gender'] = $filters['gender'];
        }
        
        if (!empty($filters['min_price'])) {
            $where[] = "COALESCE(p.sale_price, p.price) >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $where[] = "COALESCE(p.sale_price, p.price) <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['brand'])) {
            $where[] = "p.brand = :brand";
            $params[':brand'] = $filters['brand'];
        }
        
        if (!empty($filters['color'])) {
            $where[] = "p.color = :color";
            $params[':color'] = $filters['color'];
        }
        
        if (!empty($filters['size'])) {
            $where[] = "p.size = :size";
            $params[':size'] = $filters['size'];
        }
        
        if (isset($filters['featured'])) {
            $where[] = "p.featured = :featured";
            $params[':featured'] = $filters['featured'];
        }
        
        // Exclure les produits en rupture de stock
        if (!isset($filters['include_out_of_stock'])) {
            $where[] = "p.stock > 0";
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Compter le total
        $countSql = "SELECT COUNT(*) FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Tri
        $orderBy = "p.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderBy = "COALESCE(p.sale_price, p.price) ASC";
                    break;
                case 'price_desc':
                    $orderBy = "COALESCE(p.sale_price, p.price) DESC";
                    break;
                case 'name_asc':
                    $orderBy = "p.name ASC";
                    break;
                case 'popularity':
                    $orderBy = "p.view_count DESC";
                    break;
                case 'rating':
                    $orderBy = "p.rating DESC";
                    break;
            }
        }
        
        // Pagination
        $offset = ($page - 1) * $limit;
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        // Requête principale
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        $result = [
            'products' => $products,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page,
            'per_page' => $limit
        ];
        
        if ($useCache) {
            Cache::set($cacheKey, $result, 900); // 15 minutes
        }
        
        return $result;
    }
    
    /**
     * Obtenir un produit par ID avec incrémentation des vues
     */
    public function getById($id, $incrementViews = false) {
        $cacheKey = "product_{$id}";
        $product = Cache::get($cacheKey);
        
        if ($product === null) {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $product = $stmt->fetch();

            if ($product) {
                // Récupérer toutes les images
                $imgStmt = $this->pdo->prepare("SELECT image_url, sort_order FROM product_images WHERE product_id = :pid ORDER BY sort_order ASC, id ASC");
                $imgStmt->execute([':pid' => $id]);
                $images = $imgStmt->fetchAll();
                $product['images'] = array_column($images, 'image_url');
                
                // Si pas d'images dans product_images mais image_url existe, l'ajouter
                if (empty($product['images']) && !empty($product['image_url'])) {
                    $product['images'] = [$product['image_url']];
                }

                // Récupérer les tailles disponibles avec stock
                $sizeStmt = $this->pdo->prepare("
                    SELECT s.id, s.name, s.category as size_category, ps.stock as size_stock 
                    FROM product_sizes ps 
                    JOIN sizes s ON ps.size_id = s.id 
                    WHERE ps.product_id = :pid 
                    ORDER BY s.sort_order ASC, s.name ASC
                ");
                $sizeStmt->execute([':pid' => $id]);
                $product['sizes'] = $sizeStmt->fetchAll();
                
                // Récupérer les couleurs disponibles (si table existe)
                try {
                    $colorStmt = $this->pdo->prepare("
                        SELECT c.id, c.name, c.hex_code 
                        FROM product_colors pc 
                        JOIN colors c ON pc.color_id = c.id 
                        WHERE pc.product_id = :pid 
                        ORDER BY c.name ASC
                    ");
                    $colorStmt->execute([':pid' => $id]);
                    $product['colors'] = $colorStmt->fetchAll();
                } catch (PDOException $e) {
                    // Table product_colors n'existe pas encore
                    $product['colors'] = [];
                }
                
                // Calculer le stock disponible par taille
                $product['available_sizes'] = array_filter($product['sizes'], function($size) {
                    return $size['size_stock'] === null || $size['size_stock'] > 0;
                });
                
                // Calculer le prix effectif
                $product['effective_price'] = !empty($product['sale_price']) ? $product['sale_price'] : $product['price'];
                $product['has_discount'] = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
                if ($product['has_discount']) {
                    $product['discount_percentage'] = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                }
            }
            
            if ($product) {
                Cache::set($cacheKey, $product, 1800); // 30 minutes
            }
        }
        
        if ($product && $incrementViews) {
            $this->incrementViews($id);
        }
        
        return $product;
    }
    
    /**
     * Incrémenter le compteur de vues
     */
    public function incrementViews($id) {
        $sql = "UPDATE products SET view_count = COALESCE(view_count, 0) + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        // Invalider le cache
        Cache::delete("product_{$id}");
    }
    
    /**
     * Créer un nouveau produit avec images et tailles
     */
    public function create($data) {
        try {
            $this->pdo->beginTransaction();

            // Découvrir les colonnes existantes de la table products
            try {
                $columns = $this->pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {
                $columns = [];
            }

            // Construction dynamique de la requête d'insertion pour s'adapter au schéma réel
            $sql = "INSERT INTO products (name, description, price, stock, image_url, status, created_at";
            $values = "VALUES (:name, :description, :price, :stock, :image_url, 'active', NOW()";
            $params = [
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':stock' => $data['stock'] ?? 0,
                ':image_url' => $data['image_url'] ?? null,
            ];

            // Slug unique si la colonne existe
            if (in_array('slug', $columns, true)) {
                $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', @iconv('UTF-8', 'ASCII//TRANSLIT', $data['name'])), '-'));
                if ($baseSlug === '' || $baseSlug === '-') { $baseSlug = 'produit'; }
                $candidate = $baseSlug;
                $i = 1;
                $slugCheckStmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
                while (true) {
                    $slugCheckStmt->execute([$candidate]);
                    if ((int)$slugCheckStmt->fetchColumn() === 0) { break; }
                    $candidate = $baseSlug . '-' . (++$i);
                }
                $sql .= ", slug";
                $values .= ", :slug";
                $params[':slug'] = $candidate;
            }

            if (in_array('sale_price', $columns, true)) {
                $sql .= ", sale_price";
                $values .= ", :sale_price";
                $params[':sale_price'] = $data['sale_price'] ?? null;
            }

            if (in_array('sku', $columns, true)) {
                $sql .= ", sku";
                $values .= ", :sku";
                $params[':sku'] = $data['sku'] ?? null;
            }

            if (in_array('category_id', $columns, true)) {
                $sql .= ", category_id";
                $values .= ", :category_id";
                $params[':category_id'] = $data['category_id'] ?? null;
            }

            if (in_array('brand', $columns, true)) {
                $sql .= ", brand";
                $values .= ", :brand";
                $params[':brand'] = $data['brand'] ?? null;
            }

            if (in_array('material', $columns, true)) {
                $sql .= ", material";
                $values .= ", :material";
                $params[':material'] = $data['material'] ?? null;
            }

            if (in_array('gender', $columns, true)) {
                $sql .= ", gender";
                $values .= ", :gender";
                $params[':gender'] = $data['gender'] ?? 'unisexe';
            }

            if (in_array('season', $columns, true)) {
                $sql .= ", season";
                $values .= ", :season";
                $params[':season'] = $data['season'] ?? 'toute_saison';
            }

            if (in_array('gallery', $columns, true)) {
                $sql .= ", gallery";
                $values .= ", :gallery";
                $galleryValue = null;
                if (!empty($data['gallery'])) {
                    $galleryValue = is_array($data['gallery']) ? json_encode($data['gallery']) : $data['gallery'];
                }
                $params[':gallery'] = $galleryValue;
            }

            if (in_array('featured', $columns, true)) {
                $sql .= ", featured";
                $values .= ", :featured";
                $params[':featured'] = $data['featured'] ?? 0;
            }

            $sql .= ") " . $values . ")";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $productId = $this->pdo->lastInsertId();

                // Ajouter les images
                if (!empty($data['images'])) {
                    $this->addProductImages($productId, $data['images']);
                }

                // Ajouter les tailles
                if (!empty($data['sizes'])) {
                    $this->addProductSizes($productId, $data['sizes']);
                }

                // Ajouter les couleurs
                if (!empty($data['colors'])) {
                    $this->addProductColors($productId, $data['colors']);
                }

                // Log de l'action
                Security::logAction('product_created', [
                    'product_id' => $productId,
                    'name' => $data['name'],
                    'images_count' => count($data['images'] ?? []),
                    'sizes_count' => count($data['sizes'] ?? [])
                ]);

                $this->pdo->commit();

                // Invalider les caches
                $this->clearProductCache();

                return ['success' => true, 'id' => $productId];
            }

            $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Erreur lors de la création'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error creating product: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur système'];
        }
    }
    
    /**
     * Mettre à jour un produit
     */
    public function update($id, $data) {
        try {
            $this->pdo->beginTransaction();
            
            $sql = "UPDATE products SET 
                name = :name, description = :description, price = :price, 
                sale_price = :sale_price, sku = :sku, stock = :stock, 
                category_id = :category_id, brand = :brand, color = :color, 
                size = :size, material = :material, gender = :gender, 
                season = :season, image_url = :image_url, gallery = :gallery, 
                tags = :tags, featured = :featured, status = :status,
                updated_at = NOW()
                WHERE id = :id";
            
            $data['id'] = $id;
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($data);
            
            if ($result) {
                // Log de l'action
                Security::logAction('product_updated', ['product_id' => $id, 'name' => $data['name']]);
                
                $this->pdo->commit();
                
                // Invalider les caches
                $this->clearProductCache();
                Cache::delete("product_{$id}");
                
                return ['success' => true];
            }
            
            $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error updating product: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur système'];
        }
    }
    
    /**
     * Supprimer un produit
     */
    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            
            // Obtenir les infos du produit pour le log
            $product = $this->getById($id);
            
            $sql = "DELETE FROM products WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([':id' => $id]);
            
            if ($result) {
                // Log de l'action
                Security::logAction('product_deleted', [
                    'product_id' => $id, 
                    'name' => $product['name'] ?? 'Unknown'
                ]);
                
                $this->pdo->commit();
                
                // Invalider les caches
                $this->clearProductCache();
                Cache::delete("product_{$id}");
                
                return ['success' => true];
            }
            
            $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Erreur lors de la suppression'];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error deleting product: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur système'];
        }
    }
    
    /**
     * Mettre à jour le stock d'un produit
     */
    public function updateStock($id, $quantity, $operation = 'set') {
        try {
            $this->pdo->beginTransaction();
            
            if ($operation === 'increment') {
                $sql = "UPDATE products SET stock = stock + :quantity WHERE id = :id";
            } elseif ($operation === 'decrement') {
                $sql = "UPDATE products SET stock = GREATEST(0, stock - :quantity) WHERE id = :id";
            } else {
                $sql = "UPDATE products SET stock = :quantity WHERE id = :id";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([':id' => $id, ':quantity' => $quantity]);
            
            if ($result) {
                // Vérifier si le produit est en rupture
                $newStock = $this->getStock($id);
                if ($newStock <= 0) {
                    $this->updateStatus($id, 'out_of_stock');
                } elseif ($newStock > 0) {
                    $this->updateStatus($id, 'active');
                }
                
                $this->pdo->commit();
                
                // Invalider les caches
                Cache::delete("product_{$id}");
                $this->clearProductCache();
                
                return ['success' => true, 'new_stock' => $newStock];
            }
            
            $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour du stock'];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error updating stock: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur système'];
        }
    }
    
    /**
     * Obtenir le stock actuel d'un produit
     */
    public function getStock($id) {
        $sql = "SELECT stock FROM products WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Mettre à jour le statut d'un produit
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE products SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([':id' => $id, ':status' => $status]);
        
        if ($result) {
            Cache::delete("product_{$id}");
            $this->clearProductCache();
        }
        
        return $result;
    }
    
    /**
     * Obtenir les produits en rupture de stock
     */
    public function getOutOfStock() {
        $sql = "SELECT * FROM products WHERE stock <= 0 OR status = 'out_of_stock' ORDER BY updated_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les produits populaires
     */
    public function getPopular($limit = 8) {
        $cacheKey = "popular_products_{$limit}";
        $products = Cache::get($cacheKey);
        
        if ($products === null) {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.status = 'active' AND p.stock > 0
                    ORDER BY p.view_count DESC, p.created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':limit' => $limit]);
            $products = $stmt->fetchAll();
            
            Cache::set($cacheKey, $products, 1800); // 30 minutes
        }
        
        return $products;
    }
    
    /**
     * Recherche de produits avec suggestions
     */
    public function search($query, $limit = 10) {
        $cacheKey = "search_" . md5($query) . "_{$limit}";
        $results = Cache::get($cacheKey);
        
        if ($results === null) {
            $sql = "SELECT id, name, price, sale_price, image_url, brand
                    FROM products 
                    WHERE status = 'active' AND stock > 0
                    AND (name LIKE :query OR description LIKE :query OR brand LIKE :query)
                    ORDER BY 
                        CASE WHEN name LIKE :exact_query THEN 1 ELSE 2 END,
                        view_count DESC
                    LIMIT :limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':query' => '%' . $query . '%',
                ':exact_query' => $query . '%',
                ':limit' => $limit
            ]);
            $results = $stmt->fetchAll();
            
            Cache::set($cacheKey, $results, 300); // 5 minutes
        }
        
        return $results;
    }
    
    /**
     * Ajouter des images à un produit
     */
    private function addProductImages($productId, $images) {
        $stmt = $this->pdo->prepare("INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)");
        foreach ($images as $index => $imageUrl) {
            $stmt->execute([$productId, $imageUrl, $index]);
        }
    }
    
    /**
     * Ajouter des tailles à un produit
     */
    private function addProductSizes($productId, $sizes) {
        $stmt = $this->pdo->prepare("INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)");
        foreach ($sizes as $sizeData) {
            $sizeId = is_array($sizeData) ? $sizeData['id'] : $sizeData;
            $stock = is_array($sizeData) ? ($sizeData['stock'] ?? null) : null;
            $stmt->execute([$productId, $sizeId, $stock]);
        }
    }
    
    /**
     * Ajouter des couleurs à un produit
     */
    private function addProductColors($productId, $colors) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO product_colors (product_id, color_id) VALUES (?, ?)");
            foreach ($colors as $colorId) {
                $stmt->execute([$productId, $colorId]);
            }
        } catch (PDOException $e) {
            // Table product_colors n'existe pas encore, on ignore
        }
    }
    
    /**
     * Supprimer les images d'un produit
     */
    public function deleteProductImages($productId, $imageUrls = null) {
        if ($imageUrls === null) {
            // Supprimer toutes les images
            $stmt = $this->pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);
        } else {
            // Supprimer des images spécifiques
            $placeholders = str_repeat('?,', count($imageUrls) - 1) . '?';
            $stmt = $this->pdo->prepare("DELETE FROM product_images WHERE product_id = ? AND image_url IN ($placeholders)");
            $stmt->execute(array_merge([$productId], $imageUrls));
        }
    }
    
    /**
     * Mettre à jour les tailles d'un produit
     */
    public function updateProductSizes($productId, $sizes) {
        // Supprimer les anciennes tailles
        $stmt = $this->pdo->prepare("DELETE FROM product_sizes WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // Ajouter les nouvelles tailles
        if (!empty($sizes)) {
            $this->addProductSizes($productId, $sizes);
        }
    }
    
    /**
     * Obtenir le stock disponible pour une taille spécifique
     */
    public function getSizeStock($productId, $sizeId) {
        $stmt = $this->pdo->prepare("SELECT stock FROM product_sizes WHERE product_id = ? AND size_id = ?");
        $stmt->execute([$productId, $sizeId]);
        $result = $stmt->fetchColumn();
        
        // Si pas de stock spécifique, utiliser le stock global
        if ($result === null || $result === false) {
            $stmt = $this->pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            return (int) $stmt->fetchColumn();
        }
        
        return (int) $result;
    }
    
    /**
     * Mettre à jour le stock d'une taille spécifique
     */
    public function updateSizeStock($productId, $sizeId, $quantity, $operation = 'set') {
        try {
            $this->pdo->beginTransaction();
            
            if ($operation === 'increment') {
                $sql = "UPDATE product_sizes SET stock = COALESCE(stock, 0) + ? WHERE product_id = ? AND size_id = ?";
            } elseif ($operation === 'decrement') {
                $sql = "UPDATE product_sizes SET stock = GREATEST(0, COALESCE(stock, 0) - ?) WHERE product_id = ? AND size_id = ?";
            } else {
                $sql = "UPDATE product_sizes SET stock = ? WHERE product_id = ? AND size_id = ?";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$quantity, $productId, $sizeId]);
            
            if ($result) {
                $this->pdo->commit();
                
                // Invalider le cache
                Cache::delete("product_{$productId}");
                
                return ['success' => true, 'new_stock' => $this->getSizeStock($productId, $sizeId)];
            }
            
            $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour du stock'];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error updating size stock: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur système'];
        }
    }
    
    /**
     * Vider le cache des produits
     */
    private function clearProductCache() {
        // Vider les caches liés aux produits
        $patterns = ['products_', 'popular_products_', 'featured_products_', 'search_'];
        
        foreach ($patterns as $pattern) {
            $files = glob(__DIR__ . '/../cache/' . md5($pattern) . '*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
}
?>