# Déploiement Render — Dockerfile fonctionnel (Laravel, PHP 8.3 + Apache)

## Le problème
Votre Dockerfile sur GitHub contenait un simple « . » en ligne 1 (40 octets)
-> « dockerfile parse error on line 1: unknown instruction: . »
Ce paquet le remplace par une image complète et prête pour Render.

## Fichiers à mettre À LA RACINE du dépôt GitHub
  Dockerfile               (l'image : PHP 8.3 + Apache + extensions + Composer)
  .dockerignore            (exclut .env, vendor, node_modules, archives…)
  docker/entrypoint.sh     (démarrage : port Render, storage:link, caches, migrations)

Puis :  git add Dockerfile .dockerignore docker/  ->  commit  ->  push
Render relancera le build automatiquement.

## Ce que fait l'image
- PHP 8.3 + Apache, DocumentRoot pointé sur /public (avec mod_rewrite).
- Extensions installées : pdo_mysql, zip (celle qui manquait en local !), gd,
  intl, opcache.
- composer install --no-dev --optimize-autoloader pendant le build.
- Au démarrage : Apache écoute sur le PORT fourni par Render, création du lien
  storage, mise en cache config/routes/vues, et migrations si RUN_MIGRATIONS=true.

## Variables d'environnement à définir dans Render (Environment)
OBLIGATOIRES :
  APP_KEY        -> générez-la en local :  php artisan key:generate --show
                    (copiez la valeur complète, ex. base64:xxxxxxxx...)
  APP_ENV        -> production
  APP_DEBUG      -> false
  APP_URL        -> https://groupesamilcar.onrender.com
BASE DE DONNÉES (voir note ci-dessous) :
  DB_CONNECTION  -> mysql
  DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD
AUTRES (selon vos fonctionnalités) :
  GROQ_API_KEY   -> votre clé IA (examens/rapports)
  MAIL_MAILER    -> log (ou smtp + identifiants pour l'envoi réel)
  QUEUE_CONNECTION -> database
OPTIONNEL :
  RUN_MIGRATIONS -> true   (au 1er déploiement pour créer les tables, puis
                            remettez à false)

## ⚠️ Deux points importants sur Render
1) BASE DE DONNÉES : Render ne propose pas MySQL en service géré (uniquement
   PostgreSQL). Votre app utilise MySQL. Deux options :
   - utiliser un MySQL externe gratuit/hébergé (ex. Aiven, Railway, votre
     hébergeur…) et renseigner les DB_* ci-dessus ;
   - ou migrer vers PostgreSQL (changements dans le code à prévoir : je peux
     vous accompagner si vous choisissez cette voie).
   Sans base accessible, l'app démarrera mais plantera à la 1ère requête SQL.
2) FICHIERS UPLOADÉS : sur l'offre gratuite, le disque est ÉPHÉMÈRE : les
   fichiers uploadés (photos, supports du cahier de texte, bibliothèque) sont
   PERDUS à chaque redéploiement. Pour les conserver : ajoutez un « Persistent
   Disk » Render monté sur /var/www/html/storage/app/public (offre payante),
   ou un stockage externe (S3…).

## Rappel Render (réglages du service)
- Type : Web Service, Runtime : Docker (détecté via le Dockerfile).
- Branch : main. Rien à mettre dans Build/Start command (le Dockerfile gère tout).
