#!/usr/bin/env bash
# Mealmatch build helper
version=$(php bin/console app:version:status --no-debug)
printf "/*! =========================================================
 *
 * MealMatch Code Quality Checks
 * ================================================================== */
 ${version^^}
 "
echo ""

mkdir -p build/reports
mkdir -p build/phpmetrics
mkdir -p build/phpcs
mkdir -p build/phpmd
mkdir -p build/pdepend
touch build/pdepend/summary.xml
mkdir -p build/phpunit/logs

# We require phpmetrics ...
if [ ! -f bin/phpmetrics ]; then
    echo "PHPMetrics (downloading files) ... "
    cd bin
    curl -L https://github.com/phpmetrics/PhpMetrics/releases/download/v2.4.1/phpmetrics.phar --output phpmetrics.phar
    curl -L https://raw.githubusercontent.com/phpmetrics/SymfonyExtension/master/symfony-extension.phar --output symfony-extension.phar
    curl -L https://raw.githubusercontent.com/phpmetrics/ComposerExtension/master/composer-extension.phar --output composer-extension.phar
    cd ..
fi
# PHPMetrics
echo "PHPMetrics ... "
php bin/phpmetrics.phar --plugins=bin/symfony-extension.phar,bin/composer-extension.phar --report-html=web/build/phpmetrics/index.html ./

# PHPCheckstyle
# Verify that Symfony3 standard is registered ...
check=$(vendor/bin/phpcs -i | grep Symfony3 | wc -l)
if (( $check < 1 )) ; then
vendor/bin/phpcs --config-set installed_paths ../../endouble/symfony3-custom-coding-standard
fi

echo "PHPCS Checkstyle ..."
vendor/bin/phpcs -p --report=checkstyle --standard=Symfony3Custom --report-file=./build/phpcs/checkstyle.xml src/

echo "PHPCS JUnit ..."
# vendor/bin/phpcs -p --report=junit --standard=Symfony3Custom --report-file=build/phpunit/logs/phpcs-junit.xml src/

#PHPMD
echo "PHPMD ..."
vendor/bin/phpmd src html cleancode,codesize,controversial,design,naming,unusedcode --reportfile build/phpmd/index.html

# PHP SPEC
echo "PHP Spec ..."
vendor/bin/phpspec run

# pDepend
echo "pDepend ..."
vendor/bin/pdepend --summary-xml=build/pdepend/summary.xml src/

# PHP Analyzer
#echo "Analyzer ..."
#vendor/bin/analyze analyze src/
#vendor/bin/analyze bundle build/analyzer/

# PHP CS FIXER
vendor/bin/php-cs-fixer fix src/ --config=.php_cs.php

# Reporting CodeQuality with sonar...
# You need a running sonar instance!!!
# bin/sonar-scanner
# sh '/opt/sonar-scanner-3.0/bin/sonar-scanner'
echo "Finished!"
# Always exit somehow ...
exit 0