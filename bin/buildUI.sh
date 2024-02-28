#!/usr/bin/env bash
# Mealmatch build helper for the local developer workplace
# Author: wizard@mealmatch.de
# =====================================================================================================================
# set -e
# create namespace ... e.g. the autoloader.
bin/composer dump-autoload
# bin/composer autoformat-src
yarn run encore dev
# choose your style :
# yarn run encore dev --watch
# yarn run encore production

# Make sure its clean
rm -rf var/cache/dev
rm -rf var/logs/dev.log
# Validate Database
# bin/composer database-validate
# Update the UI
#bin/console assets:install | grep ERROR
# Clear cache, just to be clean ...
#bin/console cache:clear
#bin/console doctrine:cache:clear-metadata
#bin/console doctrine:cache:clear-result
#bin/console doctrine:cache:clear-query
# chmod -R 777 var/cache/dev/
exit 0
