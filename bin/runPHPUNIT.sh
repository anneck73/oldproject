#!/usr/bin/env bash
# Mealmatch phpunit helper for local development on linux
if [ ! -f phpunit.xml.local ]; then
  cp phpunit.xml.dist phpunit.xml.local
  echo "Created default local phpunit configuration in phpunit.xml.local!"
fi
export SYMFONY_DEPRECATIONS_HELPER=disabled
mkdir -p build/phpunit/html/
mkdir -p build/phpunit/logs/
mkdir -p build/phpunit/coverage/
# vendor/bin/phpunit -dxdebug.coverage_enable=1 -dxdebug.profiler_enable=0 -c phpunit.xml.local --coverage-clover build/phpunit/clover/MMWebApp.coverage --coverage-html build/phpunit/html/ --coverage-xml build/phpunit/coverage/ --log-junit build/phpunit/logs/phpunit_integration.xml
vendor/bin/phpunit -c phpunit.xml.local