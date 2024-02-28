#!/usr/bin/env bash
set +e
# get phpmetrics version 2.4.1 (specifically)
curl -L https://github.com/phpmetrics/PhpMetrics/releases/download/v2.4.1/phpmetrics.phar --output phpmetrics.phar
# get phpmetrics EXTENSIONs that fit above version :)
curl -L https://raw.githubusercontent.com/phpmetrics/ComposerExtension/master/composer-extension.phar --output composer-extension.phar
php phpmetrics.phar --plugins=symfony-extension.phar,composer-extension.phar --report-html=build/phpmetrics/index.html ./
php vendor/bin/phpmd src/ html cleancode,codesize,controversial,design,naming,unusedcode --reportfile build/phpmd/index.html || true
php vendor/bin/pdepend --summary-xml=build/pdepend/summary.xml ./src/Mealmatch || true
php vendor/bin/phpcs -d memory_limit=1G -p --report=checkstyle --report-file=build/phpcs/checkstyle.xml ./src/Mealmatch || true
exit 0