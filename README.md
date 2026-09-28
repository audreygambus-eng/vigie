# Vigie

Application de veille technique : on enregistre des ressources (titre, URL, catégorie), on les consulte, et l'application donne des statistiques de consultation.

Projet réalisé dans le cadre du titre professionnel Développeur Web et Web Mobile.

**Démo en ligne :** https://audrey.alwaysdata.net/vigie/demo.html

## Pile technique

- Symfony 7.4 (PHP 8.3)
- MySQL 8 avec Doctrine ORM : ressources et catégories
- MongoDB 8 avec Doctrine ODM : consultations et statistiques
- Docker Compose : application, MySQL et MongoDB
- PHPUnit : tests unitaires, d'intégration et fonctionnels

## Prérequis

- Docker Desktop
- Git

## Installation

1. Cloner le dépôt et démarrer les conteneurs :

```bash
   git clone https://github.com/audreygambus-eng/vigie.git
   cd vigie
   docker compose up -d --build
   docker compose exec app composer install
```

2. Créer un fichier `.env.local` à la racine, avec deux valeurs :

   - un secret applicatif, généré par :
```bash
     docker compose exec app php -r "echo bin2hex(random_bytes(16));"
```
   - l'empreinte du mot de passe administrateur, générée par :
```bash
     docker compose exec app php bin/console security:hash-password
```

```dotenv
   APP_SECRET=valeur_generee
   ADMIN_PASSWORD_HASH='empreinte_generee'
```

   Les guillemets simples autour de l'empreinte sont obligatoires car elle contient des `$`.

3. Créer le schéma des bases et charger les données de démonstration :

```bash
   docker compose exec app php bin/console doctrine:migrations:migrate
   docker compose exec app php bin/console doctrine:fixtures:load
   docker compose exec app php bin/console doctrine:mongodb:schema:update
```

## Utilisation

- Page de démonstration : http://localhost:8000/demo.html
- Administrateur : identifiant `admin`, avec le mot de passe choisi à l'installation

| Méthode | Route | Rôle | Accès |
|---|---|---|---|
| GET | `/api/ressources` | lister (filtre `?categorie=`) | public |
| GET | `/api/ressources/{id}` | afficher une ressource | public |
| POST | `/api/ressources` | créer | administrateur |
| PUT | `/api/ressources/{id}` | modifier | administrateur |
| DELETE | `/api/ressources/{id}` | supprimer | administrateur |
| GET | `/api/categories` | lister les catégories | public |
| GET | `/ressources/{id}/consulter` | enregistrer une consultation et rediriger | public |
| GET | `/api/statistiques/top` | top par période (`?jours=`, `?limite=`) | public |

Le fichier `requests.http` contient des requêtes prêtes à l'emploi pour l'extension VS Code REST Client. Le mot de passe administrateur se définit dans les paramètres de VS Code, sous le nom `motdepasseAdmin` :

```json
"rest-client.environmentVariables": {
    "$shared": { "motdepasseAdmin": "..." }
}
```

## Tests

```bash
docker compose exec app php bin/phpunit
```

Les tests d'intégration utilisent une base MongoDB séparée, `vigie_test`.

## Déploiement

L'application est déployée sur **AlwaysData** (PHP 8.4, MariaDB 11.4), avec **MongoDB Atlas** pour les statistiques, AlwaysData ne proposant plus de base MongoDB.

### Extension MongoDB

L'extension est compilée à la main sur le serveur, dans la même version qu'en développement : la bibliothèque `mongodb/mongodb` du projet exige une extension de la série 1.21.

```bash
wget https://pecl.php.net/get/mongodb-1.21.5.tgz
tar xzf mongodb-1.21.5.tgz && cd mongodb-1.21.5
phpize && ./configure && make
```

Le fichier `modules/mongodb.so` est copié hors du dossier de compilation, puis déclaré dans le php.ini du compte (`extension=...`). Il doit être recompilé à chaque changement de version de PHP.

### Installation

```bash
git clone https://github.com/audreygambus-eng/vigie.git
cd vigie
```

Créer un fichier `.env.local`, exclu du dépôt et lisible par son seul propriétaire (`chmod 600`), avec :

- `APP_ENV=prod` et `APP_DEBUG=0`
- `APP_SECRET`
- `DATABASE_URL` (avec `serverVersion=11.4.13-MariaDB`)
- `MONGODB_URI` et `MONGODB_DB`
- `ADMIN_PASSWORD_HASH`, entre guillemets simples

Puis :

```bash
composer install --no-dev --optimize-autoloader
php bin/console security:hash-password
php bin/console doctrine:migrations:migrate
php bin/console doctrine:mongodb:schema:update
```

Les fixtures n'étant pas disponibles en production, les catégories sont insérées en SQL.

### Configuration du site

- **Racine du site : le dossier `public/`**, pour que `.env.local`, `vendor/` et `.git/` ne soient jamais accessibles depuis le web.
- **HTTPS forcé**, indispensable avec HTTP Basic.
- Le fichier `public/.htaccess` envoie les requêtes à Symfony, calcule le préfixe d'installation (`/vigie`), et transmet l'en-tête `Authorization` à PHP. Cette transmission n'a lieu que si l'en-tête contient une valeur : une variable vide serait lue en priorité par Symfony et bloquerait toute authentification.

### Mise à jour

```bash
git pull
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate
php bin/console cache:clear
```

## Choix techniques

- Les consultations sont stockées dans MongoDB avec le titre et la catégorie dupliqués : les statistiques restent lisibles même si une ressource est renommée ou supprimée.
- Les données reçues par l'API passent par un DTO, qui protège contre l'affectation de masse.
- L'écriture est réservée à un administrateur authentifié en HTTP Basic, sans session. HTTPS serait obligatoire en production.
- Les ports des bases de données ne sont exposés que sur la machine locale.

## Limites et évolutions envisagées

- Base MySQL de test, pour couvrir le CRUD par des tests fonctionnels
- Les robots qui suivraient les liens de consultation fausseraient les statistiques (limité par `rel="nofollow"`)