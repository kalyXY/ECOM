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
$host = 'localhost';
$dbname = 'stylehub_db';
$username = 'votre_utilisateur';
$password = 'votre_mot_de_passe';
```

### Paramètres du site
Les paramètres sont configurables dans `includes/config.php` :
- Nom du site
- Description
- Coordonnées de contact
- Réseaux sociaux

## 🚀 Fonctionnalités Avancées

### Système de Panier
- Ajout/suppression d'articles
- Modification des quantités
- Persistance en session
- Compteur temps réel

### Gestion des Images
- Upload sécurisé
- Redimensionnement automatique
- Galerie produit
- Images par défaut

### SEO et Performance
- URLs propres
- Meta tags optimisés
- Images optimisées
- Code minifié

## 🎯 Roadmap

- [ ] Système de comptes clients
- [ ] Processus de commande complet
- [ ] Paiement en ligne
- [ ] Gestion des stocks avancée
- [ ] Système de reviews
- [ ] Programme de fidélité
- [ ] API REST
- [ ] Application mobile

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👥 Contribution

Les contributions sont les bienvenues ! Merci de :
1. Fork le projet
2. Créer une branche feature
3. Commit vos changements
4. Push vers la branche
5. Ouvrir une Pull Request

## 📞 Support

Pour toute question ou support :
- Email: contact@stylehub.fr
- Issues GitHub: [Lien vers les issues]

---

**StyleHub** - Votre destination mode pour un style unique et tendance ✨