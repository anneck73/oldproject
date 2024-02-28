#!/usr/bin/env bash
find ./ -iname *.yml~ -delete
find ./ -iname *.xlf -delete
console translation:extract --enable-extractor=jms_i18n_routing en -b MMWebFrontBundle
console translation:extract --enable-extractor=jms_i18n_routing en -b MMApiBundle
console translation:extract --enable-extractor=jms_i18n_routing en -b MMPayPalBundle
console translation:extract --enable-extractor=jms_i18n_routing en -b MMUserBundle

console translation:extract --enable-extractor=jms_i18n_routing de -b MMWebFrontBundle
console translation:extract --enable-extractor=jms_i18n_routing de -b MMApiBundle
console translation:extract --enable-extractor=jms_i18n_routing de -b MMPayPalBundle
console translation:extract --enable-extractor=jms_i18n_routing de -b MMUserBundle
