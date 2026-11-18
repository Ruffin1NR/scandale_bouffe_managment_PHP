# Scandale Bouffe - Management

Application de gestion complète pour une activité de vente de bouffe, développée en PHP pur avec Tailwind CSS.

## Fonctionnalités

- **Authentification** : inscription, connexion, gestion de session avec rôles (administrateur / vendeur)
- **Tableau de bord administrateur** : vue d'ensemble des ventes, vendeurs, catégories et inventaire
- **Gestion des vendeurs** : création, consultation, détail et historique par vendeur
- **Point de vente (POS)** : interface de création de ventes pour les vendeurs
- **Inventaire** : CRUD complet des produits (création, modification, affichage, suppression) avec suivi de stock configurable par catégorie
- **Gestion des catégories** : organisation des produits par catégorie et activation du suivi de stock
- **Profil utilisateur** : édition du profil personnel

## Stack technique

| Technologie | Rôle |
|---|---|
| PHP 8+ | Langage principal (pas de framework) |
| MySQL | Base de données |
| Tailwind CSS 3 | Design & responsive |
| PDO | Accès à la base de données |

## Prérequis

- [XAMPP](https://www.apachefriends.org/) (ou tout stack LAMP/WAMP) avec PHP 8+ et MySQL
- [Node.js](https://nodejs.org/) (pour compiler Tailwind CSS)
- [Git](https://git-scm.com/)

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/Ruffin1NR/scandale_bouffe_managment_PHP.git
cd scandale_bouffe_managment_PHP
```

### 2. Configurer la base de données

1. Ouvrez phpMyAdmin (`http://localhost/phpmyadmin`)
2. Créez une base de données nommée `scandale_bouffe`
3. Importez le fichier SQL fourni dans le projet
4. Pour une base existante sans colonne `track_stock`, exécutez `database/migrate_track_stock.sql`.
5. Vérifiez les paramètres de connexion dans `config/db.php` :

```php
$dbHost = 'localhost';
$dbName = 'scandale_bouffe';
$dbUser = 'root';
$dbPass = '';
```

### 3. Compiler Tailwind CSS

```bash
npm install
npm run build
```

Pour le développement avec recompilation automatique :

```bash
npm run build:watch
```

### 4. Lancer l'application

Placez le projet dans le dossier `htdocs` de XAMPP, puis accédez à :

```
http://localhost/scandale_bouffe_management
```

## Structure du projet

```
scandale_bouffe_management/
├── auth/                    # Authentification (login, check)
├── config/
│   └── db.php               # Configuration BDD + helpers PDO
├── css/
│   ├── app.css              # Source Tailwind
│   ├── styles.css           # Styles custom
│   └── tailwind.css         # CSS compilé (généré)
├── pages/
│   ├── admin/               # Espace administrateur
│   │   ├── categories.php
│   │   ├── category_form.php
│   │   ├── sale_create.php
│   │   ├── sale_edit.php
│   │   ├── sales.php
│   │   ├── vendor_create.php
│   │   ├── vendor_detail.php
│   │   └── vendors.php
│   ├── inventory/           # Gestion de l'inventaire
│   │   ├── create.php
│   │   ├── edit.php
│   │   ├── index.php
│   │   └── show.php
│   ├── partials/            # Composants réutilisables
│   │   ├── auth_modal.php
│   │   ├── confirm_modal.php
│   │   └── toast.php
│   ├── profile/             # Profil utilisateur
│   │   └── edit.php
│   ├── vendor/              # Espace vendeur
│   │   ├── dashboard.php
│   │   ├── history.php
│   │   └── pos.php
│   ├── dashboard.php
│   ├── home.php
│   ├── register.php
│   ├── layout.php
│   ├── layout_footer.php
│   ├── 403.php
│   └── 404.php
├── public/
│   └── index.php            # Front controller
├── uploads/                 # Fichiers uploadés (non versionné)
├── .htaccess
├── .gitignore
├── index.php                # Point d'entrée
├── package.json
└── tailwind.config.js
```

## Licence

Projet privé - Tous droits réservés.
