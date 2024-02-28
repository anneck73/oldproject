#!/usr/bin/env bash
# Mealmatch build helper for the local developer workplace
# Author: wizard@mealmatch.de
# =====================================================================================================================
# =====================================================================================================================
set -e
now="$(date +'%d/%m/%Y')"
# this enables the parameters.php to use env variable ...
export APP_SECRETS=./etc/secrets.json
# setting symfomy environment
export SYMFONY_ENV=prod
# Create the current version
version=$(cat VERSION)
rm -rf var/cache/prod
rm -rf var/logs/prod.log
# Rebuild vendor ...
# bin/composer update --no-scripts --no-interaction
bin/composer install -v --no-scripts --no-interaction
# Validation ...
bin/composer run-script database-validate -v --no-interaction
# Install DEV
bin/composer run-script install-prod
# Clear cache, just to be clean ...
bin/console cache:clear --env=$SYMFONY_ENV
bin/console doctrine:cache:clear-metadata --env=$SYMFONY_ENV
bin/console doctrine:cache:clear-result --env=$SYMFONY_ENV
bin/console doctrine:cache:clear-query --env=$SYMFONY_ENV
# Echo current composer package versions into file to note changes!
echo $version > composer.versions
bin/composer show >> composer.versions
git diff composer.versions
# just to be sure ...
chmod -R 777 var/
exit 0
