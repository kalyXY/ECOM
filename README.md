# E-Commerce Back-Office

Un système de gestion e-commerce moderne avec interface d'administration professionnelle.

## 🚀 Fonctionnalités

### Front-Office (Public)
- ✅ Affichage des produits
- ✅ Design responsive avec Bootstrap 5
- ✅ Navigation intuitive

### Back-Office (Admin)
- ✅ **Dashboard moderne** avec statistiques en temps réel
- ✅ **Gestion complète des produits** (CRUD)
- ✅ **Upload d'images sécurisé** avec redimensionnement automatique
- ✅ **Authentification sécurisée** avec sessions
- ✅ **Interface responsive** et moderne
- ✅ **Pagination intelligente**
- ✅ **Graphiques interactifs** (Chart.js)
- ✅ **Design professionnel** avec animations

## 📁 Structure du projet (RÉORGANISÉE)

```
/
├── index.php                 # Front-office (site public)
├── config.php               # Configuration principale
├── database.sql             # Structure de la base de données
├── migrate.php              # Script de migration
├── redirect.php             # Redirections pour compatibilité
├── uploads/                 # Images des produits
├── assets/                  # Assets du front-office
│   ├── css/style.css
│   └── js/script.js
└── admin/                   # 🆕 BACK-OFFICE RÉORGANISÉ
    ├── index.php            # Dashboard admin
    ├── login.php            # Connexion admin moderne
    ├── logout.php           # Déconnexion
    ├── products.php         # Gestion des produits
    ├── add_product.php      # Ajouter un produit
    ├── delete_product.php   # Supprimer un produit
    ├── config.php           # Configuration admin
    ├── .htaccess            # Sécurité admin
    ├── assets/              # Assets de l'admin
    │   ├── css/admin.css    # Styles modernes (500+ lignes)
    │   └── js/admin.js      # JavaScript avancé
    └── layouts/             # Templates admin
        ├── header.php
        ├── sidebar.php
        ├── topbar.php
        └── footer.php
```

## 🧹 Nettoyage effectué

### ❌ Fichiers supprimés (obsolètes)
- `login.php` (racine) → `admin/login.php`
- `logout.php` (racine) → `admin/logout.php`
- `admin.php` (racine) → `admin/index.php`
- `products.php` (racine) → `admin/products.php`
- `add_product.php` (racine) → `admin/add_product.php`
- `edit_product.php` (racine) → `admin/edit_product.php`
- `delete_product.php` (racine) → `admin/delete_product.php`
- `profile.php` (racine) → supprimé
- `layouts/` (racine) → `admin/layouts/`
- `migrate_database.sql` → fusionné dans `migrate.php`

### ✅ Structure finale optimisée
- **Séparation claire** : Front-office / Back-office
- **Sécurité renforcée** : Admin isolé dans son dossier
- **URLs propres** : `/admin/` pour l'administration
- **Maintenance facilitée** : Code organisé et modulaire

## 🛠️ Installation

### 1. Base de données
```sql
-- Exécuter le fichier database.sql dans phpMyAdmin
-- Ou utiliser le script de migration
```

### 2. Configuration
```php
// Modifier config.php avec vos paramètres
$host = 'localhost';
$dbname = 'ecommerce_db';
$username = 'root';
$password = '';
```

### 3. Permissions
```bash
# Donner les permissions d'écriture au dossier uploads
chmod 755 uploads/
```

## 🔐 Accès Admin

**URL :** `http://votre-site.com/admin/`

**Identifiants par défaut :**
- Utilisateur : `admin`
- Mot de passe : `admin123`

## 🎨 Design & UX

### Caractéristiques du design
- **Interface moderne** inspirée des meilleurs dashboards
- **Sidebar responsive** avec navigation intuitive
- **Cartes de statistiques** avec animations
- **Graphiques interactifs** pour les données
- **Formulaires élégants** avec validation en temps réel
- **Alerts et notifications** stylées
- **Mode sombre** (optionnel)

### Technologies utilisées
- **PHP 8+** avec PDO
- **MySQL** pour la base de données
- **Bootstrap 5** pour le responsive
- **Font Awesome 6** pour les icônes
- **Chart.js** pour les graphiques
- **Inter Font** pour la typographie moderne

## 🔒 Sécurité

- ✅ **Requêtes préparées** (protection SQL injection)
- ✅ **Validation CSRF** sur tous les formulaires
- ✅ **Hashage des mots de passe** (password_hash)
- ✅ **Upload sécurisé** avec validation des types
- ✅ **Sessions sécurisées**
- ✅ **Headers de sécurité** (.htaccess)
- ✅ **Validation côté serveur**

## 📱 Responsive Design

L'interface s'adapte parfaitement à tous les écrans :
- **Desktop** : Interface complète avec sidebar
- **Tablet** : Sidebar collapsible
- **Mobile** : Navigation optimisée

## 🚀 Fonctionnalités avancées

### Dashboard
- Statistiques en temps réel
- Graphiques des ventes
- Produits récents
- Actions rapides

### Gestion des produits
- Liste avec pagination
- Recherche et filtres
- Upload d'images avec preview
- Validation complète
- Suppression sécurisée

### Interface utilisateur
- Animations fluides
- Auto-save des brouillons
- Notifications toast
- Tooltips informatifs
- Loading states

## 🔧 Personnalisation

### Couleurs
Modifiez les variables CSS dans `admin/assets/css/admin.css` :
```css
:root {
    --primary-color: #4f46e5;
    --primary-dark: #3730a3;
    /* ... autres variables */
}
```

### Logo
Remplacez l'icône dans `admin/layouts/sidebar.php` :
```html
<i class="fas fa-store"></i> <!-- Remplacer par votre logo -->
```

## 📈 Performance

- **CSS et JS minifiés** en production
- **Images optimisées** automatiquement
- **Cache des ressources** statiques
- **Compression GZIP** activée
- **Lazy loading** des images

## 🐛 Dépannage

### Erreur "Column not found"
Exécutez le script de migration :
```
http://votre-site.com/migrate.php
```

### Problème d'upload
Vérifiez les permissions du dossier `uploads/` :
```bash
chmod 755 uploads/
```

### Erreur de connexion
Vérifiez la configuration dans `config.php`

## 📞 Support

Pour toute question ou problème :
1. Vérifiez les logs d'erreur PHP
2. Consultez la documentation
3. Vérifiez les permissions des fichiers

## 🎯 Roadmap

- [ ] Gestion des catégories
- [ ] Système de commandes complet
- [ ] Gestion des clients
- [ ] Rapports avancés
- [ ] API REST
- [ ] Multi-langues
- [ ] Mode sombre complet

---

**Développé avec ❤️ pour une expérience e-commerce moderne**