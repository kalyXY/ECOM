# 🚀 Guide d'Installation - StyleHub E-Commerce

## Prérequis

- **PHP 8.0+** avec extensions :
  - PDO MySQL
  - GD ou ImageMagick
  - OpenSSL
  - cURL
  - JSON
  - MBString
- **MySQL 8.0+** ou MariaDB 10.4+
- **Apache 2.4+** avec mod_rewrite
- **Composer** (optionnel mais recommandé)

## Installation Rapide

### 1. Téléchargement et Configuration

```bash
# Cloner ou télécharger les fichiers dans votre répertoire web
cd /var/www/html

# Donner les permissions appropriées
chmod 755 -R stylehub/
chmod 777 stylehub/uploads/
chmod 777 stylehub/cache/
```

### 2. Base de Données

```sql
-- Créer la base de données
CREATE DATABASE stylehub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Importer la structure
mysql -u root -p stylehub_db < database.sql
```

### 3. Configuration

Créer un fichier `.env` à la racine :

```env
# Base de données
DB_HOST=localhost
DB_NAME=stylehub_db
DB_USER=root
DB_PASS=

# Application
APP_URL=http://localhost
DEBUG=false

# Email (optionnel)
SMTP_HOST=
SMTP_USER=
SMTP_PASS=
```

### 4. Migration des Fonctionnalités Mode

Ouvrir dans le navigateur :
```
http://localhost/migrate_clothing_features.php
```

### 5. Accès Admin

- **URL :** `http://localhost/admin/`
- **Identifiants par défaut :**
  - Utilisateur : `admin`
  - Mot de passe : `admin123`

## Configuration Apache

### .htaccess Principal (déjà inclus)

Le fichier `.htaccess` est configuré avec :
- Compression GZIP
- Cache des ressources statiques
- URLs propres
- Protection contre les injections
- Headers de sécurité

### Virtual Host (Recommandé)

```apache
<VirtualHost *:80>
    ServerName stylehub.local
    DocumentRoot /var/www/html/stylehub
    
    <Directory /var/www/html/stylehub>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/stylehub_error.log
    CustomLog ${APACHE_LOG_DIR}/stylehub_access.log combined
</VirtualHost>
```

## Configuration de Production

### 1. Sécurité

```php
// Dans config/app.php
define('DEBUG_MODE', false);

// Dans .htaccess, décommenter :
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 2. Performance

```apache
# Dans .htaccess ou virtual host
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>
```

### 3. Monitoring

```bash
# Logs d'erreurs
tail -f /var/log/apache2/stylehub_error.log

# Logs d'accès
tail -f /var/log/apache2/stylehub_access.log
```

## Personnalisation

### 1. Thème et Couleurs

Modifier dans `assets/css/style.css` :

```css
:root {
    --primary-color: #ff6900;    /* Couleur principale */
    --accent-color: #ff4d4f;     /* Couleur d'accent */
    --success-color: #52c41a;    /* Couleur de succès */
}
```

### 2. Logo et Branding

- Remplacer `assets/images/logo.png`
- Modifier le nom dans `includes/config.php`
- Personnaliser les métadonnées dans `includes/header.php`

### 3. Données d'Exemple

```php
// Exécuter pour créer des données d'exemple
http://localhost/admin/create_sample_data.php
```

## Maintenance

### Cache

```bash
# Vider le cache
rm -rf cache/*.cache

# Ou via l'interface admin
http://localhost/admin/settings.php
```

### Sauvegarde

```bash
#!/bin/bash
# Script de sauvegarde automatique

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/stylehub"

# Base de données
mysqldump -u root -p stylehub_db > $BACKUP_DIR/db_$DATE.sql

# Fichiers
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/stylehub/uploads/
```

### Monitoring des Stocks

Le système vérifie automatiquement :
- Stocks faibles (≤ 5 unités)
- Produits en rupture
- Notifications en temps réel pour les admins

## Dépannage

### Erreur de Connexion Base de Données

```bash
# Vérifier la configuration
grep -r "DB_" config/

# Tester la connexion
mysql -u root -p -e "USE stylehub_db; SHOW TABLES;"
```

### Erreur 500

```bash
# Activer les erreurs PHP temporairement
echo "error_reporting(E_ALL); ini_set('display_errors', 1);" > debug.php
```

### Problème d'Upload

```bash
# Vérifier les permissions
ls -la uploads/
chmod 777 uploads/

# Vérifier la configuration PHP
php -i | grep upload
```

### Performance Lente

1. Vérifier le cache :
   ```bash
   ls -la cache/
   ```

2. Optimiser la base de données :
   ```sql
   OPTIMIZE TABLE products, categories, orders;
   ```

3. Vérifier les logs :
   ```bash
   tail -f /var/log/apache2/error.log
   ```

## Support

- **Documentation :** Voir `README.md`
- **Logs :** Consultez les fichiers de logs Apache et PHP
- **Debug :** Activez `DEBUG_MODE` en développement uniquement

## Sécurité

### Checklist de Sécurité

- [ ] Changer le mot de passe admin par défaut
- [ ] Configurer HTTPS en production
- [ ] Sauvegarder régulièrement
- [ ] Mettre à jour PHP et MySQL
- [ ] Surveiller les logs de sécurité
- [ ] Configurer un pare-feu
- [ ] Limiter l'accès au dossier `/admin/`

### Configuration Firewall (UFW)

```bash
# Autoriser HTTP/HTTPS
ufw allow 80
ufw allow 443

# Limiter SSH
ufw limit ssh

# Activer
ufw enable
```

---

**✅ Installation terminée !** Votre plateforme e-commerce StyleHub est prête à l'emploi.