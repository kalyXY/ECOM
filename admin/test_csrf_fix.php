<?php
/**
 * Test de la correction CSRF
 */

// Démarrer la session proprement
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure le système CSRF corrigé
class FixedCSRF {
    public static function generateToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
        return $_SESSION['csrf_token'];
    }
    
    public static function verifyToken($token) {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Vérifier l'expiration (1 heure)
        if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > 3600)) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function getToken() {
        return $_SESSION['csrf_token'] ?? self::generateToken();
    }
}

function generateCSRFTokenFixed() {
    return FixedCSRF::generateToken();
}

function verifyCSRFTokenFixed($token) {
    return FixedCSRF::verifyToken($token);
}

// Variables pour les messages
$message = '';
$messageType = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFTokenFixed($_POST['csrf_token'] ?? '')) {
        $message = '❌ Token CSRF invalide !';
        $messageType = 'error';
    } else {
        $message = '✅ Token CSRF valide ! Le formulaire fonctionne correctement.';
        $messageType = 'success';
        
        // Afficher les données reçues
        $message .= '<br><br><strong>Données reçues :</strong><br>';
        $message .= 'Nom: ' . htmlspecialchars($_POST['test_name'] ?? '') . '<br>';
        $message .= 'Description: ' . htmlspecialchars($_POST['test_desc'] ?? '') . '<br>';
        $message .= 'Prix: ' . htmlspecialchars($_POST['test_price'] ?? '') . '<br>';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Correction CSRF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        button {
            background-color: #007cba;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #005a87;
        }
        
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .debug {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test de la Correction CSRF</h1>
        
        <div class="info">
            <strong>ℹ️ Information :</strong><br>
            Ce formulaire teste si la correction du système CSRF fonctionne correctement.
            Si vous voyez "✅ Token CSRF valide", le problème est résolu !
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFTokenFixed(); ?>">
            
            <div class="form-group">
                <label for="test_name">Nom du produit de test :</label>
                <input type="text" id="test_name" name="test_name" value="Produit Test CSRF" required>
            </div>
            
            <div class="form-group">
                <label for="test_desc">Description :</label>
                <textarea id="test_desc" name="test_desc" required>Description de test pour vérifier que le système CSRF fonctionne correctement après la correction.</textarea>
            </div>
            
            <div class="form-group">
                <label for="test_price">Prix :</label>
                <input type="number" id="test_price" name="test_price" step="0.01" value="29.99" required>
            </div>
            
            <button type="submit">🧪 Tester le CSRF</button>
        </form>
        
        <div class="debug">
            <strong>Debug Info :</strong><br>
            Session ID: <?php echo session_id(); ?><br>
            Token généré: <?php echo substr(FixedCSRF::getToken(), 0, 20) . '...'; ?><br>
            Timestamp: <?php echo $_SESSION['csrf_token_time'] ?? 'Non défini'; ?><br>
            Status session: <?php echo session_status(); ?><br>
        </div>
        
        <div class="info">
            <strong>📋 Instructions :</strong><br>
            1. Cliquez sur "Tester le CSRF"<br>
            2. Si vous voyez "✅ Token CSRF valide", la correction fonctionne !<br>
            3. Vous pouvez maintenant utiliser admin/add_product.php normalement<br>
            4. Si le problème persiste, vérifiez que votre serveur supporte les sessions PHP
        </div>
    </div>
</body>
</html>