#!/usr/bin/env bash
apt-get update && apt-get install -y git gnupg unzip rsync zip mysql-client libfreetype6-dev libjpeg62-turbo-dev libmemcached-dev zlib1g-dev
docker-php-ext-install pdo pdo_mysql
# pecl install xdebug && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
# turn "fail on error" OFF
set +e
# Prepare Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# First rebuild auto-loader ...
composer dump-autoload
# Composer install for pipeline environment
composer install -v --no-interaction --no-scripts --no-progress
# Enforcing clean database for pipeline db
php bin/console doctrine:database:drop --force --env=pipeline
php bin/console doctrine:database:create --env=pipeline
php bin/console doctrine:schema:create --env=pipeline
# Enforcing migrations and fixtures for pipeline db
php bin/console doctrine:migrations:migrate -n --env=pipeline
php bin/console doctrine:fixtures:load -n --env=pipeline -vvv
# PHPUnit
# -dxdebug.coverage_enable=1 -dxdebug.profiler_enable=0
php vendor/bin/phpunit -d memory_limit=1G -c phpunit.xml.pipeline --log-junit build/test-reports/phpunit/phpunit.xml --testdox-html build/phpunit/phpunit.html
