require('../global-before-env-driven');
let sharedLogin = require('./sharedLogin');

describe('Test SYSTEM Login', function () {
    sharedLogin.loginSYSTEM();
});
