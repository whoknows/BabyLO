#!/bin/bash

php app/console cache:clear --env=prod
php app/console assetic:dump --env=prod
php app/console assets:install --env=prod

chmod 777 -R app/logs/
chmod 777 -R app/cache/