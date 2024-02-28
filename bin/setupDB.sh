#!/usr/bin/env bash
# Mealmatch build helper for the local developer workplace
# Author: wizard@mealmatch.de
# =====================================================================================================================
version=$(cat VERSION)
printf "/*! =========================================================
 * MealMatch Setup lokal Database ${version^^}
 * ================================================================== */
 "
# using symfony console to create databases ...

# Symfony DEV (PROD is using the same database)
bin/console doctrine:database:drop --force
bin/console doctrine:database:create
bin/console doctrine:schema:create
bin/console doctrine:migrations:migrate -n
bin/console doctrine:fixtures:load -n

# Symfony TEST used by PHPUnit(!)
bin/console doctrine:database:drop --force --env=test
bin/console doctrine:database:create --env=test
bin/console doctrine:schema:create --env=test
bin/console doctrine:migrations:migrate -n --env=test
bin/console doctrine:fixtures:load -n --env=test

# Exit nice ...
exit 0