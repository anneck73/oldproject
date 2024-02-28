require('../global-before-env-driven');
let sharedLogin = require('./sharedLogin');

describe('Test MMTestRestaurant login', function () {
    sharedLogin.loginMMTestRestaurant();
});
