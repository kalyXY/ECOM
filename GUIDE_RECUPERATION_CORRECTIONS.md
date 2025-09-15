# 📥 GUIDE - Comment récupérer les corrections

## 🎯 Plusieurs méthodes pour récupérer les corrections

### 📋 **Méthode 1 : Copier-Coller manuel (Recommandée)**

#### Étape 1 : Sauvegarder votre version actuelle
```bash
# Créer une sauvegarde de votre fichier actuel
cp admin/add_product.php admin/add_product_backup.php
```

#### Étape 2 : Appliquer les corrections principales

**A. Modifier `admin/add_product.php`**

Ajoutez ce code au début du fichier (après `requireLogin();`) :

```php
// === CORRECTION CSRF ===
// Forcer le démarrage de session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Système CSRF corrigé
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

// Fonctions globales corrigées
function generateCSRFTokenFixed() {
    return FixedCSRF::generateToken();
}

function verifyCSRFTokenFixed($token) {
    return FixedCSRF::verifyToken($token);
}
// === FIN CORRECTION CSRF ===
```

**B. Modifier la vérification CSRF**

Trouvez cette ligne :
```php
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
```

Remplacez par :
```php
if (!verifyCSRFTokenFixed($_POST['csrf_token'] ?? '')) {
```

**C. Modifier la génération du token dans le formulaire**

Trouvez cette ligne :
```php
<input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
```

Remplacez par :
```php
<input type="hidden" name="csrf_token" value="<?php echo generateCSRFTokenFixed(); ?>">
```

#### Étape 3 : Créer le fichier de test

Créez `admin/test_csrf_fix.php` avec ce contenu :

```php
<?php
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
        
        if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > 3600)) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

function generateCSRFTokenFixed() {
    return FixedCSRF::generateToken();
}

function verifyCSRFTokenFixed($token) {
    return FixedCSRF::verifyToken($token);
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFTokenFixed($_POST['csrf_token'] ?? '')) {
        $message = '❌ Token CSRF invalide !';
        $messageType = 'error';
    } else {
        $message = '✅ Token CSRF valide ! Le formulaire fonctionne correctement.';
        $messageType = 'success';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test CSRF Fix</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔧 Test Correction CSRF</h1>
    
    <?php if ($message): ?>
        <div class="<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFTokenFixed(); ?>">
        
        <div class="form-group">
            <label>Test:</label>
            <input type="text" name="test" value="Test CSRF" required>
        </div>
        
        <button type="submit">Tester CSRF</button>
    </form>
    
    <p><strong>Instructions :</strong></p>
    <ol>
        <li>Cliquez sur "Tester CSRF"</li>
        <li>Si vous voyez "✅ Token CSRF valide", c'est corrigé !</li>
        <li>Vous pouvez maintenant utiliser add_product.php</li>
    </ol>
</body>
</html>
```

---

### 🔄 **Méthode 2 : Via Git (si vous utilisez Git)**

```bash
# Si vous avez initialisé un dépôt Git
git init
git add .
git commit -m "Sauvegarde avant corrections"

# Appliquer les modifications manuellement puis
git add .
git commit -m "Correction erreur CSRF"
```

---

### 📁 **Méthode 3 : Téléchargement de fichiers**

Si vous travaillez via un panneau de contrôle (cPanel, etc.) :

1. **Téléchargez** vos fichiers actuels en sauvegarde
2. **Modifiez** `admin/add_product.php` selon les instructions ci-dessus
3. **Créez** le fichier `admin/test_csrf_fix.php`
4. **Uploadez** les fichiers modifiés

---

### 🖥️ **Méthode 4 : Via SSH/FTP**

```bash
# Connexion SSH
ssh votre-utilisateur@votre-serveur

# Naviguer vers votre dossier web
cd /var/www/html  # ou votre dossier web

# Éditer le fichier
nano admin/add_product.php
# Appliquer les modifications mentionnées ci-dessus

# Créer le fichier de test
nano admin/test_csrf_fix.php
# Coller le contenu du fichier de test
```

---

## 🧪 **Étapes de test après application**

### 1. Test rapide
```
http://votre-site.com/admin/test_csrf_fix.php
```

### 2. Test complet
```
http://votre-site.com/admin/add_product.php
```

### 3. Vérification
- Remplissez le formulaire d'ajout de produit
- Soumettez-le
- L'erreur "Token CSRF invalide" ne devrait plus apparaître

---

## 🚨 **En cas de problème**

### Restaurer la sauvegarde
```bash
cp admin/add_product_backup.php admin/add_product.php
```

### Vérifier les logs d'erreur
- Consultez les logs de votre serveur web
- Vérifiez les erreurs PHP

### Support
Si le problème persiste, vérifiez :
1. Les permissions de fichiers
2. La configuration PHP (sessions activées)
3. Les logs d'erreur du serveur

---

## ✅ **Résultat attendu**

Après avoir appliqué ces corrections :
- ✅ L'erreur "Token CSRF invalide" disparaît
- ✅ Le formulaire d'ajout de produit fonctionne
- ✅ La sécurité CSRF est maintenue
- ✅ Un système de test est disponible

**Temps estimé :** 10-15 minutes pour l'application manuelle des corrections.