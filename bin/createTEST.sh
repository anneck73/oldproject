#!/usr/bin/env bash
# Mealmatch build helper, used from local developer workplace

# Author: wizard@mealmatch.de
# =====================================================================================================================
set -e
now="$(date +'%d.%m.%Y')"
mkdir -p build/
buildLogfile="build/build.log"
if [ -f ${buildLogfile} ]; then
    rm -f ${buildLogfile}
fi
# this enables the parameters.php to use env variable ...
export APP_SECRETS=./etc/secrets.json
# We require some settings ...
if [ ! -f ${APP_SECRETS} ]; then
    echo -e "Missing resource '${APP_SECRECTS}'."
    exit -1
fi
# setting symfomy environment
export SYMFONY_ENV=test
# We require composer ...
if [ ! -f bin/composer ]; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php --install-dir=bin/ --filename=composer
    php -r "unlink('composer-setup.php');"
fi
# First rebuild auto-loader ...
bin/composer dump-autoload
# Configurations parameters will be created if they don't exist yet ...
if [ ! -f app/config/parameters.yml ]; then
    bin/composer install
fi
# Create the current version
version=$(cat VERSION)
rm -rf var/cache/test
rm -rf var/logs/test.log
# Rebuild vendor ...
# bin/composer update --no-scripts --no-interaction
bin/composer install -v --no-scripts --no-interaction
# Validation ...
# bin/composer run-script database-validate -v --no-interaction
echo ${version^^} >> update-dev-SQL.sql
# Clean TEST DB
bin/console doctrine:database:drop --force --env=test
bin/console doctrine:database:create --env=test

# Install TEST
bin/composer run-script install-test

# UnitTest / Coverage ...
#sh bin/runPHPUNIT.sh
#sh bin/runCODEQUALITY.sh
# vendor/bin/phpunit --coverage-html build/phpunit/html/ --coverage-clover build/phpunit/clover.xml --coverage-xml build/phpunit/coverage/ --log-junit build/phpunit/phpunit_integration.xml

# Clear cache, just to be clean ...
bin/console cache:clear
bin/console doctrine:cache:clear-metadata
bin/console doctrine:cache:clear-result
bin/console doctrine:cache:clear-query
# Echo current composer package versions into file to note changes!
echo $version > composer.versions
bin/composer show >> composer.versions
# UnitTest / Coverage ...
#sh bin/runPHPUNIT.sh
#sh bin/runCODEQUALITY.sh
# vendor/bin/phpunit --coverage-html build/phpunit/html/ --coverage-clover build/phpunit/clover.xml --coverage-xml build/phpunit/coverage/ --log-junit build/phpunit/phpunit_integration.xml
echo "Build TEST-$version build at $now running on $(hostname -f)."
chmod -R 777 var/
exit 0