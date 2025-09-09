<?php
/**
 * Générateur de sitemap XML automatique
 * StyleHub E-Commerce Platform
 */

require_once 'config/bootstrap.php';
require_once 'models/Product.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = App::url();
$productModel = new Product($pdo);

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    <!-- Page d'accueil -->
    <url>
        <loc><?php echo $baseUrl; ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Pages principales -->
    <url>
        <loc><?php echo App::url('products.php'); ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc><?php echo App::url('contact.php'); ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    
    <!-- Catégories -->
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active'");
        $categories = $stmt->fetchAll();
        
        foreach ($categories as $category):
    ?>
    <url>
        <loc><?php echo App::url('products.php?category=' . $category['id']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($category['updated_at'] ?? $category['created_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; } catch (Exception $e) {} ?>
    
    <!-- Produits -->
    <?php
    try {
        $result = $productModel->getAll(['status' => 'active'], 1, 1000, false);
        $products = $result['products'];
        
        foreach ($products as $product):
    ?>
    <url>
        <loc><?php echo App::url('product.php?id=' . $product['id']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($product['updated_at'] ?? $product['created_at'])); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; } catch (Exception $e) {} ?>
    
</urlset>