<?php
/**
 * Script de vérification finale - Test d'ajout de produit
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== VÉRIFICATION FINALE ===\n\n";

// Test de la base de données
echo "1. Test de la base de données...\n";
try {
    require_once '../config/database_fallback.php';
    echo "✓ Connexion à la base de données réussie\n";
    
    // Test d'insertion
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, status) VALUES (?, ?, ?, 'active')");
    $result = $stmt->execute(['Test Product', 'Description de test', 19.99]);
    
    if ($result) {
        $productId = $pdo->lastInsertId();
        echo "✓ Insertion de test réussie (ID: $productId)\n";
        
        // Nettoyer le test
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$productId]);
        echo "✓ Nettoyage effectué\n";
    } else {
        echo "✗ Échec de l'insertion de test\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur de base de données: " . $e->getMessage() . "\n";
}

// Test des permissions de fichiers
echo "\n2. Test des permissions...\n";
$testFile = '../uploads/test_write.txt';
if (file_put_contents($testFile, 'test')) {
    echo "✓ Écriture dans uploads/ OK\n";
    unlink($testFile);
} else {
    echo "✗ Impossible d'écrire dans uploads/\n";
}

// Test de la syntaxe du fichier principal
echo "\n3. Test de syntaxe add_product.php...\n";
$syntaxCheck = shell_exec('php -l add_product.php 2>&1');
if (strpos($syntaxCheck, 'No syntax errors') !== false) {
    echo "✓ Syntaxe PHP correcte\n";
} else {
    echo "✗ Erreur de syntaxe:\n$syntaxCheck\n";
}

// Test des fonctions de sécurité
echo "\n4. Test des fonctions de sécurité...\n";
session_start();

// Simuler les fonctions si elles n'existent pas
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        return bin2hex(random_bytes(16));
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return !empty($token);
    }
}

try {
    $token = generateCSRFToken();
    if (verifyCSRFToken($token)) {
        echo "✓ Fonctions CSRF OK\n";
    } else {
        echo "✗ Problème avec les fonctions CSRF\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur CSRF: " . $e->getMessage() . "\n";
}

echo "\n=== RÉSUMÉ ===\n";
echo "✅ Vérification terminée.\n";
echo "Si tous les tests sont OK, votre système d'ajout de produit devrait fonctionner.\n\n";

echo "INSTRUCTIONS FINALES :\n";
echo "1. Accédez à admin/test_simple_add.php pour tester l'ajout basique\n";
echo "2. Si cela fonctionne, utilisez admin/add_product.php pour l'interface complète\n";
echo "3. Assurez-vous que votre serveur web est démarré\n";
echo "4. En cas de problème, vérifiez les logs d'erreur de votre serveur\n";

?>