#!/usr/bin/env bash
# Echo current composer package versions into file to note changes!
version=$(cat VERSION)
echo $version > ./etc/composer.$version
bin/composer show >> ./etc/composer.$version
cat ./etc/composer.$version