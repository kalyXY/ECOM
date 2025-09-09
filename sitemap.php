<?php
require_once 'config.php';

// Définir le type de contenu XML
header('Content-Type: application/xml; charset=utf-8');

// URL de base du site
$baseUrl = 'https://www.stylehub.fr'; // À adapter selon votre domaine

// Date de dernière modification
$lastmod = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    <!-- Page d'accueil -->
    <url>
        <loc><?php echo $baseUrl; ?>/</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Pages principales -->
    <url>
        <loc><?php echo $baseUrl; ?>/products.php</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc><?php echo $baseUrl; ?>/about.php</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    
    <url>
        <loc><?php echo $baseUrl; ?>/contact.php</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    
    <url>
        <loc><?php echo $baseUrl; ?>/careers.php</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    
    <url>
        <loc><?php echo $baseUrl; ?>/terms.php</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    
    <url>
        <loc><?php echo $baseUrl; ?>/privacy.php</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    
    <!-- Collections par genre -->
    <url>
        <loc><?php echo $baseUrl; ?>/products.php?gender=femme</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    
    <url>
        <loc><?php echo $baseUrl; ?>/products.php?gender=homme</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    
    <url>
        <loc><?php echo $baseUrl; ?>/products.php?gender=unisexe</loc>
        <lastmod><?php echo $lastmod; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
    
    <?php
    // Ajouter les catégories dynamiquement
    try {
        $stmt = $pdo->query("SELECT slug FROM categories WHERE status = 'active' ORDER BY name");
        while ($category = $stmt->fetch()) {
            echo "    <url>\n";
            echo "        <loc>{$baseUrl}/products.php?category=" . urlencode($category['slug']) . "</loc>\n";
            echo "        <lastmod>{$lastmod}</lastmod>\n";
            echo "        <changefreq>daily</changefreq>\n";
            echo "        <priority>0.7</priority>\n";
            echo "    </url>\n";
        }
    } catch (Exception $e) {
        // Ignorer les erreurs de base de données
    }
    ?>
    
    <?php
    // Ajouter les produits dynamiquement
    try {
        $stmt = $pdo->query("SELECT id, updated_at FROM products WHERE status = 'active' ORDER BY id");
        while ($product = $stmt->fetch()) {
            $productLastmod = date('Y-m-d', strtotime($product['updated_at']));
            echo "    <url>\n";
            echo "        <loc>{$baseUrl}/product.php?id={$product['id']}</loc>\n";
            echo "        <lastmod>{$productLastmod}</lastmod>\n";
            echo "        <changefreq>weekly</changefreq>\n";
            echo "        <priority>0.6</priority>\n";
            echo "    </url>\n";
        }
    } catch (Exception $e) {
        // Ignorer les erreurs de base de données
    }
    ?>
    
</urlset>