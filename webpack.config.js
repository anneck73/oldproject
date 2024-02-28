// Mealmatch assets management using webpack encore
// Each xyz-AppBundle has its custom configuration
// At the end of this script all configurations are exported

// typescript
// .enableTypeScriptLoader()
// faster typescript compile
// .enableForkedTypeScriptTypesChecking()

// allow legacy applications to use $/jQuery as a global variable
// .autoProvidejQuery()

// show OS notifications when builds finish/fail
// .enableBuildNotifications()

// GLOBAL
const Encore = require('@symfony/webpack-encore');
// Environment isProduction = 'prod', everything else is 'dev
let env = Encore.isProduction() ? 'prod' : 'dev';
// Glob to be used with copy
// unused as of yet...
let glob = require("glob");
const CopyWebpackPlugin = require('copy-webpack-plugin');

Encore
    .setOutputPath('web/static/' + env)
    .setPublicPath('/static/' + env)
    // Manually set this to match current version to cache bust everything
    .setManifestKeyPrefix('v0218')
    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .enableSingleRuntimeChunk()
    .enableBuildNotifications()
    .autoProvidejQuery()
    .createSharedEntry('jQuery', './src/Mealmatch/UIPublicBundle/Resources/public/js/common_js.js')

    .addEntry('UICouponApp', './src/Mealmatch/UICouponBundle/Resources/public/js/UICouponApp.js')

    .addStyleEntry('webapp', './src/Mealmatch/UIPublicBundle/Resources/public/sass/webapp.scss')

    .addStyleEntry('mmWebFrontStyle', [
        './src/Mealmatch/UIPublicBundle/Resources/public/css/bootstrap.min.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/bootstrap-theme.min.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/bootstrap-datetimepicker.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/bootstrap-tagsinput-typeahead.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/bootstrap-tagsinput.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/bootstrap-dialog.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/datepicker.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/material-icons.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/font-awesome.min.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/mealmatch-circle-icons.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/scss/simple-line-icons.scss'
    ])

    .addStyleEntry('webapp_ui', [
        './src/Mealmatch/UIPublicBundle/Resources/public/sass/webapp_ui.scss',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/newUI_style.css',
        './src/Mealmatch/UIPublicBundle/Resources/public/css/font_proxima.css'
    ])
    .addEntry('mmWebFrontJS', [
        './vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js',
        './web/static/' + env + '/js-routing.js',
        './src/Mealmatch/UIPublicBundle/Resources/public/js/bootstrap-tagsinput.min.js',
        './src/Mealmatch/UIPublicBundle/Resources/public/js/bootstrap-datetimepicker.min.js',
        './src/Mealmatch/UIPublicBundle/Resources/public/js/bootstrap-dialog.js',
        './src/Mealmatch/UIPublicBundle/Resources/public/js/bloodhound.js',
        './src/Mealmatch/UIPublicBundle/Resources/public/js/typeahead.js',
        './src/Mealmatch/UIPublicBundle/Resources/public/js/bootstrap3-typeahead.js'
    ])

    .addEntry('mealManager', [
        './src/Mealmatch/UIPublicBundle/Resources/public/js/mealManager_js.js'
    ])

    .addEntry('searchJS', [
        './src/Mealmatch/UIPublicBundle/Resources/public/js/mm-search.js',
        './src/Mealmatch/UIPublicBundle/Resources/public/js/prettify-1.0.min.js'
    ])

;

module.exports = Encore.getWebpackConfig();