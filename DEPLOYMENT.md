# Documentation du projet Blog Laravel

Ce document explique le fonctionnement du projet, les modifications nécessaires au déploiement et la procédure complète utilisée avec Render, Neon et Upstash.

Les clés, mots de passe et URLs contenant des identifiants ne doivent jamais être copiés dans ce fichier ni commités dans Git.

## 1. Vue d'ensemble

L'application est un blog Laravel composé de :

- Laravel 13 pour le backend PHP et les routes web/API.
- Blade pour les vues HTML.
- Eloquent et PostgreSQL pour les données.
- Vite et Tailwind CSS pour les assets frontend.
- Neon comme base PostgreSQL distante.
- Upstash Redis comme cache.
- Render comme hébergeur du conteneur Docker.

Le dépôt contient notamment :

- `app/` : logique applicative Laravel.
- `database/migrations/` : structure de la base de données.
- `database/seeders/` : données initiales et comptes de démonstration.
- `resources/views/` : templates Blade.
- `resources/css/` et `resources/js/` : source frontend.
- `public/build/` : assets générés par Vite.
- `Dockerfile` : construction et démarrage de l'image de production.
- `render.yaml` : définition du service Render et de ses variables.
- `composer.json` : dépendances PHP et scripts de déploiement.

## 2. Fonctionnement local

Installation des dépendances PHP :

```bash
composer install
```

Installation des dépendances JavaScript :

```bash
npm install
```

Compilation des assets :

```bash
npm run build
```

Création de la clé Laravel, si nécessaire :

```bash
php artisan key:generate
```

Création de la base locale et des données de démonstration :

```bash
php artisan migrate --seed
```

Démarrage du serveur Laravel :

```bash
php artisan serve
```

Le script global suivant exécute les étapes de préparation du projet :

```bash
composer run setup
```

## 3. Serveur MCP Laravel Boost

Le projet utilise Laravel Boost. La configuration `boost.json` active le MCP avec :

```json
{
    "mcp": true
}
```

Pour démarrer manuellement le serveur MCP depuis la racine du projet :

```powershell
cd E:\blog
php artisan boost:mcp
```

Le terminal doit rester ouvert pendant l'utilisation du serveur. `Ctrl+C` l'arrête.

Dans un autre projet Laravel, Boost peut être installé avec :

```bash
composer require laravel/boost --dev
php artisan boost:install
```

## 4. Configuration des variables d'environnement

En production, les valeurs doivent être saisies dans Render, dans **Environment**. Elles ne doivent pas être écrites dans le dépôt.

Variables attendues :

| Variable | Valeur attendue | Rôle |
| --- | --- | --- |
| `APP_ENV` | `production` | Active le contexte de production. |
| `APP_DEBUG` | `false` | Évite d'afficher les détails d'erreur aux visiteurs. |
| `APP_KEY` | Clé générée par Laravel | Chiffrement des données Laravel. |
| `APP_URL` | URL publique Render | Génération des URLs de l'application. |
| `DB_CONNECTION` | `pgsql` | Utilise PostgreSQL. |
| `DB_URL` | URL complète Neon | Connexion à la base distante. |
| `DB_SSLMODE` | `require` | Chiffrement de la connexion PostgreSQL. |
| `REDIS_CLIENT` | `predis` | Client Redis utilisé par Laravel. |
| `REDIS_URL` | URL Redis `rediss://...` | Connexion à Upstash Redis. |
| `REDIS_CACHE_DB` | `0` | Base logique Redis utilisée par le cache Upstash. |
| `CACHE_STORE` | `redis` | Stockage du cache dans Redis. |
| `SESSION_DRIVER` | `database` | Stockage des sessions dans PostgreSQL. |
| `QUEUE_CONNECTION` | `database` | Stockage des jobs dans PostgreSQL. |
| `LOG_CHANNEL` | `stderr` | Envoie les logs vers Render. |
| `APP_LOCALE` | `fr` | Langue principale. |
| `APP_FALLBACK_LOCALE` | `fr` | Langue de secours. |

### Important pour `DB_URL`

Dans Render, la valeur de `DB_URL` doit être uniquement l'URL, par exemple :

```text
postgresql://utilisateur:mot-de-passe@hote.neon.tech/base?sslmode=require
```

Il ne faut pas saisir :

```text
DB_URL="postgresql://..."
```

Il ne faut pas non plus mettre cette URL dans `DB_DATABASE`. Laravel lit directement `DB_URL` grâce à la configuration PostgreSQL de `config/database.php`.

### Important pour `REDIS_URL`

La valeur Render doit être uniquement :

```text
rediss://default:mot-de-passe@hote.upstash.io:6379
```

Il ne faut pas saisir une valeur imbriquée comme :

```text
REDIS_URL="rediss://..."
```

Les anciennes variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD` doivent être supprimées si elles contiennent des valeurs locales ou contradictoires. Avec `DB_URL`, elles ne sont pas nécessaires.

## 5. Générer une clé Laravel

Depuis le projet local :

```bash
php artisan key:generate --show
```

Copier la valeur affichée dans Render, dans la variable `APP_KEY`. Ne jamais la commit dans `.env`, `render.yaml` ou ce fichier.

Si une clé a été exposée publiquement, elle doit être remplacée. Le changement de `APP_KEY` peut rendre illisibles des données chiffrées avec l'ancienne clé.

## 6. Création des services distants

### Neon

1. Créer une base PostgreSQL Neon.
2. Ouvrir **Connect**.
3. Sélectionner la branche `production`, la base et le rôle corrects.
4. Copier la chaîne PostgreSQL.
5. La saisir uniquement dans Render, variable `DB_URL`.
6. Ne jamais placer cette chaîne dans Git.

La connexion poolée Neon a fonctionné pour les migrations de ce projet. Une connexion directe peut aussi être utilisée si Neon la propose, notamment pour les opérations de migration. L'essentiel est que Laravel reçoive une URL PostgreSQL valide dans `DB_URL`.

### Upstash Redis

1. Créer la base Redis.
2. Activer l'éviction automatique pour le plan gratuit.
3. Récupérer l'URL Redis classique commençant par `rediss://`.
4. La saisir uniquement dans Render, variable `REDIS_URL`.

L'éviction est adaptée ici car Redis ne sert qu'au cache. Les sessions et la queue utilisent PostgreSQL. Une entrée de cache supprimée peut être recalculée par Laravel.

Upstash ne prend en charge que la base logique Redis `0`. Laravel utilise souvent la base `1` pour sa connexion de cache par défaut. Il faut donc ajouter cette variable dans Render :

```text
REDIS_CACHE_DB=0
```

Sans cette variable, Laravel peut échouer avec :

```text
ERR Only 0th database is supported! Selected DB: 1
```

## 7. Structure du déploiement Docker

Le `Dockerfile` utilise deux étapes.

### Étape frontend

```dockerfile
FROM node:22-bookworm-slim AS frontend
```

Cette étape :

1. Installe les dépendances JavaScript avec `npm ci`.
2. Copie le code source.
3. Exécute `npm run build`.
4. Produit les fichiers dans `public/build`.

### Étape PHP

```dockerfile
FROM php:8.5-cli-bookworm
```

Cette étape :

1. Installe les extensions système nécessaires.
2. Active `pdo_pgsql` pour PostgreSQL.
3. Active `zip` pour Composer.
4. Copie Composer dans l'image.
5. Copie le code Laravel.
6. Copie les assets construits depuis l'étape frontend.
7. Crée les répertoires runtime Laravel.
8. Installe uniquement les dépendances de production.

La version PHP est importante. `composer.lock` contient Symfony 8, qui exige PHP 8.4 minimum. L'ancienne image PHP 8.3 provoquait cette erreur :

```text
symfony/... requires php >=8.4
```

L'image PHP 8.5 est donc alignée avec les dépendances verrouillées.

### Commande de démarrage

Le conteneur exécute :

```bash
composer run deploy
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
```

Le script `deploy` exécute les opérations suivantes :

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Les migrations et le seeder sont donc exécutés au démarrage du service, avant le serveur HTTP.

## 8. Configuration Blade ajoutée

Le projet ne possédait pas de `config/view.php`. Laravel ne connaissait donc pas le chemin de compilation Blade et `php artisan view:cache` échouait avec :

```text
View path not found.
```

Le fichier `config/view.php` définit maintenant :

```php
return [
    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views')),
];
```

Le Dockerfile crée également les répertoires suivants :

```text
storage/framework/cache
storage/framework/sessions
storage/framework/views
storage/logs
bootstrap/cache
```

Cette création est nécessaire car certains répertoires runtime sont exclus par `.dockerignore` et ne doivent pas dépendre de fichiers vides dans Git.

## 9. HTTPS derrière le proxy Render

Render termine la connexion TLS avant de transmettre la requête au conteneur Laravel. Dans ce contexte, Laravel peut recevoir la requête interne en HTTP et générer des URLs d'assets en `http://`, même lorsque le visiteur utilise HTTPS.

Le fichier `app/Providers/AppServiceProvider.php` force donc le schéma HTTPS en production :

```php
if ($this->app->isProduction()) {
    URL::forceScheme('https');
}
```

Sans ce réglage, le navigateur bloque les fichiers CSS et JavaScript comme contenu mixte. La variable Render `APP_URL` doit malgré tout rester configurée avec l'URL publique HTTPS :

```text
https://blog-laravel-9q6j.onrender.com
```

## 10. Configuration Render

Le fichier `render.yaml` déclare un service web Docker :

- Nom : `blog-laravel`.
- Plan : `free`.
- Dockerfile : `./Dockerfile`.
- Contexte Docker : `.`.
- Port : fourni par la variable Render `PORT`.
- Health check : `/up`.

Les appels suivants dans les logs sont normaux :

```text
/up ... ~ 0.1ms
```

Render appelle régulièrement `/up` pour vérifier que l'application répond. Ce ne sont pas des requêtes d'image.

## 11. Procédure de déploiement

Après chaque modification de code :

```powershell
git status
git add .
git commit -m "description de la modification"
git push
```

Render détecte ensuite le nouveau commit et lance le build Docker.

Dans Render :

1. Ouvrir le service `blog-laravel`.
2. Vérifier les variables dans **Environment**.
3. Attendre le build Docker.
4. Lire les **Build logs** si le build échoue.
5. Lire les logs de déploiement si le conteneur démarre mais s'arrête.
6. Attendre le statut `Live`.
7. Ouvrir l'URL publique du service.

## 12. Diagnostic des erreurs rencontrées

### Erreur PHP pendant Composer

Symptôme :

```text
symfony/... requires php >=8.4
```

Cause : l'image Docker utilisait PHP 8.3 alors que `composer.lock` avait résolu Symfony 8.

Correction : utiliser `php:8.5-cli-bookworm`.

### Connexion vers 127.0.0.1

Symptôme :

```text
connection to server at "127.0.0.1" port 5432 failed
```

Cause : l'URL Neon était placée dans `DB_DATABASE` ou `DB_URL` n'était pas correctement renseignée.

Correction : mettre l'URL complète uniquement dans `DB_URL` et supprimer les anciennes variables PostgreSQL locales.

### URL Redis imbriquée

Symptôme : Redis ne peut pas se connecter.

Cause : la valeur contenait quelque chose comme `REDIS_URL="rediss://..."` au lieu de l'URL seule.

Correction : saisir uniquement l'URL `rediss://...` dans la valeur Render.

### `View path not found`

Symptôme :

```text
php artisan view:cache
RuntimeException: View path not found.
```

Cause : absence de configuration `config/view.php` et de chemin `view.compiled`.

Correction : ajouter `config/view.php` et créer les répertoires runtime dans le Dockerfile.

### CSS absent malgré un déploiement réussi

Symptôme : la page HTML s'affiche sans style et le navigateur bloque les fichiers `/build/assets/*.css`.

Diagnostic : vérifier le code source HTML. Si les liens commencent par `http://` alors que le site est ouvert en `https://`, il s'agit d'un problème de schéma généré par Laravel derrière le proxy Render.

Correction : conserver `APP_URL` en HTTPS et activer `URL::forceScheme('https')` en production dans `AppServiceProvider`.

### Logs d'application vides

Si Render affiche `No logs in the past 7 days`, le conteneur n'a probablement pas démarré. Dans ce cas, consulter les logs du déploiement ou les Build logs, pas uniquement les Application logs.

## 13. Vérifications locales avant un nouveau déploiement

Vérifier Composer :

```bash
composer validate
composer check-platform-reqs --no-dev
```

Vérifier les assets :

```bash
npm ci
npm run build
```

Vérifier les vues Blade :

```bash
php artisan config:clear
php artisan view:cache
```

Vérifier le style des fichiers PHP modifiés :

```bash
vendor/bin/pint --dirty --format agent
```

Vérifier les migrations sur une base de test avant une base de production :

```bash
php artisan migrate:status
php artisan migrate --force
```

## 14. Sécurité des secrets

Les secrets déjà exposés dans un log, un fichier partagé ou une conversation doivent être considérés comme compromis :

- régénérer le mot de passe Neon ;
- régénérer ou remplacer le secret Redis Upstash ;
- remplacer les valeurs correspondantes dans Render ;
- vérifier que `.env`, les exports et les fichiers de configuration secrets ne sont pas suivis par Git.

Ne jamais envoyer un log contenant une URL complète avec mot de passe. Remplacer les secrets par des placeholders comme `<REDACTED>`.
