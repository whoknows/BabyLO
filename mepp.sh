#!/bin/bash

php app/console cache:clear
php app/console assetic:dump
php app/console assets:install