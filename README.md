# StyleHub - Boutique de Mode en Ligne

StyleHub est une boutique de mode en ligne moderne et élégante, développée en PHP avec une interface d'administration complète.

## 🌟 Fonctionnalités

### Front-Office
- **Design moderne et responsive** adapté à la mode
- **Catalogue de produits** avec filtres avancés (genre, catégorie, prix, couleur)
- **Système de panier** avec gestion des quantités
- **Pages produits détaillées** avec galerie d'images
- **Système de favoris** (wishlist)
- **Guide des tailles** interactif
- **Newsletter** et contact
- **Animations CSS** et effets visuels

### Back-Office (Admin)
- **Dashboard** avec statistiques
- **Gestion des produits** (CRUD complet)
- **Gestion des catégories** hiérarchiques
- **Gestion des commandes**
- **Gestion des clients**
- **Rapports et analytics**
- **Interface moderne** avec sidebar responsive

## 🛠️ Technologies Utilisées

- **Backend**: PHP 8.0+, MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Framework CSS**: Bootstrap 5.3
- **Icons**: Font Awesome 6.4
- **Fonts**: Google Fonts (Playfair Display, Inter)

## 📦 Installation

1. **Cloner le projet**
   ```bash
   git clone [url-du-repo]
   cd stylehub
   ```

2. **Configuration de la base de données**
   - Créer une base de données MySQL nommée `stylehub_db`
   - Importer le fichier `database.sql`
   - Modifier les paramètres de connexion dans `config.php`

3. **Configuration du serveur web**
   - Pointer le document root vers le dossier du projet
   - S'assurer que PHP et les extensions PDO sont installées
   - Créer le dossier `uploads/` avec les permissions d'écriture

4. **Accès**
   - **Front-office**: `http://votre-domaine/`
   - **Admin**: `http://votre-domaine/admin/`
   - **Identifiants admin**: `admin` / `admin123`

## 🎨 Thème Mode

Le site est spécialement conçu pour une boutique de mode avec :

### Palette de couleurs
- **Primaire**: #2c2c2c (Noir élégant)
- **Secondaire**: #8b7355 (Beige sophistiqué)
- **Accent**: #d4af37 (Or fashion)
- **Arrière-plan**: #faf8f5 (Crème doux)

### Typographie
- **Titres**: Playfair Display (serif élégant)
- **Texte**: Inter (sans-serif moderne)

### Fonctionnalités Mode
- **Filtres par genre** (Homme, Femme, Unisexe)
- **Sélecteur de tailles** interactif
- **Guide des tailles** en modal
- **Système de favoris** avec localStorage
- **Badges produits** (Promo, Nouveau)
- **Métadonnées produits** (marque, couleur, matière, saison)

## 📁 Structure du Projet

```
stylehub/
├── admin/                  # Interface d'administration
│   ├── assets/            # CSS/JS admin
│   ├── layouts/           # Templates admin
│   └── *.php             # Pages admin
├── assets/                # Assets front-office
│   ├── css/              # Styles CSS
│   └── js/               # Scripts JavaScript
├── includes/              # Fichiers inclus
│   ├── config.php        # Configuration front
│   ├── header.php        # En-tête
│   └── footer.php        # Pied de page
├── uploads/               # Images uploadées
├── config.php            # Configuration principale
├── database.sql          # Structure BDD
├── index.php             # Page d'accueil
├── products.php          # Catalogue
├── product.php           # Détail produit
├── cart.php              # Panier
├── contact.php           # Contact
└── README.md             # Documentation
```

## 🔧 Configuration

### Base de données
Modifier les paramètres dans `config.php` :
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