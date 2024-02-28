#!/usr/bin/env bash
# turn "fail on error" OFF
set +e
# Prepare files/directories expected to exists
mkdir -p build/test-reports/phpunit/
mkdir -p build/phpmetrics/
mkdir -p build/phpmd/
mkdir -p build/pdepend/
mkdir -p build/phpcs/