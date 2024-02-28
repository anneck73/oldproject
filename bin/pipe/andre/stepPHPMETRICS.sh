#!/usr/bin/env bash
set +e
# get phpmetrics version 2.4.1 (specifically)
curl -L https://github.com/phpmetrics/PhpMetrics/releases/download/v2.4.1/phpmetrics.phar --output phpmetrics.phar
# get phpmetrics EXTENSIONs that fit above version :)
curl -L https://raw.githubusercontent.com/phpmetrics/ComposerExtension/master/composer-extension.phar --output composer-extension.phar
php phpmetrics.phar --plugins=symfony-extension.phar,composer-extension.phar --report-html=build/phpmetrics/index.html ./