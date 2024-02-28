#!/usr/bin/env bash
# Mealmatch build helper for the local developer workplace
# Synopsis:
# setup.sh initializes the local build environment to properly work with all used build helper tools.
# ... @todo finish synopsis
# Author: wizard@mealmatch.de
# =====================================================================================================================
set -e
now="$(date +'%d.%m.%Y')"
mkdir -p build/
# colors ...
if [ ! -f etc/bash/.colors ]; then
    echo "etc/bash/.colors not found!"
    exit -1
    else
    source etc/bash/.colors
fi
echo -e "${Blue}Mealmatch bin/setup.sh ${Green}Development Environment${Blue} started ... $COff"
echo -e "${Blue}Local PHP install check ${Green}Development Environment${Blue} started, you need to sudo this command $COff"
# MMWebApp requires PHP7.2+
sudo apt-get install php7.2 \
    php7.2-intl \
    php7.2-gd \
    php7.2-mysql \
    php7.2-opcache \
    php7.2-phpdbg \
    php7.2-curl \
    php7.2-bcmath \
    php7.2-xsl \
    php7.2-zip \
    php7.2-xml \
    php7.2-mbstring \
    php-apcu


# We require composer ...
if [ ! -f bin/composer ]; then
    echo -e "$BRed Composer not found! $COF"
    echo -e "$Green ... downloading composer installer $COF"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=./bin --filename=composer
fi
# Configurations parameters will be created if they don't exist yet ...
if [ ! -f app/config/parameters.yml ]; then
    echo -e "$BRed parameters.yml not found, starting install! $COF"
    bin/composer install
fi
echo -e "${Blue}DB: Drop and Create everything from scratch ...$COff"
# Using symfony console to create databases ...
# Symfony environment DEV + PROD (using the same database)
bin/console doctrine:database:drop --force
bin/console doctrine:database:create
bin/console doctrine:schema:create
bin/console doctrine:migrations:migrate -n
bin/console doctrine:fixtures:load -n

# Symfony environment is  TEST used by PHPUnit(!)
bin/console doctrine:database:drop --force --env=test
bin/console doctrine:database:create --env=test
bin/console doctrine:schema:create --env=test
bin/console doctrine:migrations:migrate -n --env=test
bin/console doctrine:fixtures:load -n --env=test

mkdir -p var/cache/dev/
mkdir -p var/cache/test/
mkdir -p var/cache/stage/
mkdir -p var/cache/prod/
mkdir -p var/logs/

touch var/logs/dev.log
touch var/logs/test.log
touch var/logs/prod.log

# Helper to fix file based permission problems when running the web-server with a different user (e.g. Apache)
developer=$(whoami)
webserver=www-data
# fix with chown
sudo chown -R $developer:$webserver var/cache/ var/logs/ web/
# using setfacl to make it permanent (@todo: ? doesnt work as permanent as expected!)
sudo setfacl -dR -m g::rwX var/cache/ var/logs/ web/
# make sure everything in bin is executable
chmod u+x bin/*

# Create the current version
version=$(bin/createVersion.sh)
echo -e "\n$Green Successfully run setup for $COff version: $Red${version^^}$COff\n"
echo -e "Setup $version at $now running on $(hostname -f)." >> build/setup.log
echo -e "Setup $version at $now running on $(hostname -f)."
# just to be sure ...
chmod -R 777 var/
exit 0
