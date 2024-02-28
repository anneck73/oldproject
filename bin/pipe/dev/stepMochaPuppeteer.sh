#!/usr/bin/env bash
# Yarn/bin into PATH
export PATH=$HOME/.yarn/bin:$PATH
# Install Puppeteer dependencies
apt-get update && apt-get install -y git gnupg unzip zip gconf-service libasound2 libatk1.0-0 libatk-bridge2.0-0 libc6 \
libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 \
libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 \
libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation \
libappindicator1 libnss3 lsb-release xdg-utils wget
# Install actual nodejs LTS
curl -sL https://deb.nodesource.com/setup_10.x | bash -
apt-get install -y nodejs
# Install webpack-encore via yarn
curl -o- -L https://yarnpkg.com/install.sh | bash
# Installing Javascript testing tools as developer dependncies (look in packacge.json)
yarn add mocha chai chai-as-promised puppeteer mocha-junit-reporter mocha-multi-reporters mochawesome --dev
# Start Test-Run
yarn run test-pipeline

# Just a reminder how to run a single test
# ./node_modules/mocha/bin/mocha --recursive --timeout 10000 --reporter mocha-multi-reporters --reporter-options configFile=mocha-config.json tests/Mealmatch/Mocha/registerRestaurant/testRegisterAsRestaurant.js