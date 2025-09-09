# Déploiement & Rollback

## Prérequis
- PHP 8.0+
- MySQL 8 / MariaDB 10.4+
- Extensions: PDO MySQL

## Variables d'environnement
Copier `.env.example` vers `.env` et compléter les valeurs (ne pas committer les secrets).

## Migrations
```bash
mysql -u $DB_USER -p$DB_PASS -h $DB_HOST < migrations/001_init.sql
```

## Seeds
```bash
mysql -u $DB_USER -p$DB_PASS -h $DB_HOST < seeds/initial_data.sql
```

## Vérification
- Ouvrir `/admin/verify_real_data.php` pour vérifier la présence de données
- Ouvrir `/` et `/products.php` pour valider l'affichage

## Rollback (basique)
- Restaurer un dump SQL précédent
- Ou supprimer les données insérées par les seeds si nécessaire

## Sécurité
- `DEBUG=false` en prod
- HTTPS activé
- Restreindre l'accès à `/admin/`
- Permissions minimales sur `uploads/`

## Indices de performance
- Index sur `products(category_id, gender, featured, status)`
- Utiliser le cache applicatif (fichiers) déjà intégré