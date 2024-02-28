#!/usr/bin/env bash
# Helper to execute all doctrine related updates
php bin/console doctrine:schema:update --dump-sql > updateSQL.sql
php bin/console doctrine:schema:update --force
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
