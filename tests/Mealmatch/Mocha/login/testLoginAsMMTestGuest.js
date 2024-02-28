require('../global-before-env-driven');
let sharedLogin = require('./sharedLogin');

describe('Test MMTestGuest login', function () {
    sharedLogin.loginMMTestGuest();
});
