#!/usr/bin/env bash
# Yarn/bin into PATH
export PATH=$HOME/.yarn/bin:$PATH
#
apt-get update && apt-get install -y git gnupg unzip zip
# Install actual nodejs LTS
curl -sL https://deb.nodesource.com/setup_10.x | bash -
apt-get install -y nodejs
# Install webpack-encore via yarn
curl -o- -L https://yarnpkg.com/install.sh | bash
# yarn
yarn encore production
# Prepare Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# First rebuild auto-loader ...
composer dump-autoload
# Composer install for pipeline environment
composer install -v --no-interaction --no-scripts --no-progress