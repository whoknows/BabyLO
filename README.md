# BabyLO

BabyLO est une application web permettant de réaliser des statistiques sur des parties de baby-foot.
L'application est basée sur Symfony2.
Les librairies utilisées sont :
- Bootstrap 3
- HighCharts
- jQuery
- Chosen
- Pickadate.js

## Procédure d'installation

La procédure a été testé et fonctionne sur un Ubuntu 13.10 avec une installation standard de LAMP.

```bash
sudo apt-get install apache2 php5 mysql-server libapache2-mod-php5 php5-mysql
```

Récupération du code
```bash
git clone https://github.com/whoknows/BabyLO.git
```

Configurer vos paramètres de base de données dans le fichier app/config/parameters.yml

```bash
parameters:
    database_driver: pdo_mysql
    database_host: 127.0.0.1
    database_port: '3306'
    database_name: baby
    database_user: root
    database_password: secret
```

Installation du projet
```bash
cd BabyLO/
mkdir app/cache/ && chmod 777 -R app/cache/
mkdir app/logs/ && chmod 777 -R app/logs/
composer update
php app/console doctrine:database:create
php app/console doctrine:schema:update --force
php app/console doctrine:fixtures:load
```

### Accès à l'application

http://localhost/BabyLO/web/app.php

### Identifiants admin

- login : admin
- password : secret

### Mode public / privé

Par défaut l'application est en mode privé, c'est à dire qu'il faut obligatoirement être authentifié pour y accéder.
Il est possible de la rendre publique (hors parties admin) en commentant la ligne 40 du fichier app/config/security.yml :

```bash
- { path: ^/.*, roles: [IS_AUTHENTICATED_FULLY, IS_AUTHENTICATED_REMEMBERED] }
```