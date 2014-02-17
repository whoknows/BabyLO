# BabyLO

## How to install :

```bash
git clone https://github.com/whoknows/BabyLO.git .
chmod 777 -R app/cache/
chmod 777 -R app/logs/
composer update
php app/console doctrine:schema:update --force
php app/console doctrine:fixtures:load
```

### Admin credentials :

login : admin
password : secret
