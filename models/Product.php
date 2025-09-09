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
        
        // Compter le total (pas besoin de joindre categories pour un COUNT)
        $countSql = "SELECT COUNT(*) FROM products p WHERE {$whereClause}";
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
                    WHERE p.id = :id AND p.status = 'active'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $product = $stmt->fetch();
            
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
     * Créer un nouveau produit
     */
    public function create($data) {
        try {
            $this->pdo->beginTransaction();
            
            $sql = "INSERT INTO products (
                name, description, price, sale_price, sku, stock, category_id,
                brand, color, size, material, gender, season, image_url,
                gallery, tags, featured, status, created_at
            ) VALUES (
                :name, :description, :price, :sale_price, :sku, :stock, :category_id,
                :brand, :color, :size, :material, :gender, :season, :image_url,
                :gallery, :tags, :featured, :status, NOW()
            )";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($data);
            
            if ($result) {
                $productId = $this->pdo->lastInsertId();
                
                // Log de l'action
                Security::logAction('product_created', ['product_id' => $productId, 'name' => $data['name']]);
                
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