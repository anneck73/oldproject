// require('./global-before-mmstage');

exports.loginMMTestRestaurant = function () {
    it('We should be logged in as MMTestRestaurant', async () => {
        await page.goto(baseURL + '/logout', {timeout: 0, waitUntil: 'domcontentloaded'});
        await page.waitForSelector('li:nth-child(2) > a:nth-child(2)');

        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});

        await page.waitForSelector('.navbar > #mm-navbar-collapse > .nav > li:nth-child(3) > a');
        await page.click('.navbar > #mm-navbar-collapse > .nav > li:nth-child(3) > a');
        await page.waitFor(500);

        await page.waitForSelector('.modal-dialog > .modal-content > .form-signin > .modal-body > #username');
        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-body > #username');
        await page.type('.modal-dialog > .modal-content > .form-signin > .modal-body > #username', 'MMTestRestaurant');



        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-body > #password');
        await page.type('.modal-dialog > .modal-content > .form-signin > .modal-body > #password', '123');

        await page.waitForSelector('.modal-dialog > .modal-content > .form-signin > .modal-footer > #\_submit');
        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-footer > #\_submit');

        await page.waitForSelector('#mm-navbar-collapse > ul > li:nth-child(2) > a'); // Wait for  Mein Restaurant
        await navigationPromise;

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Mein Restaurant';
        expect(bodyHTML).to.contain(searchString, 'Konnte String ' + searchString + ' nicht finden');

        // assert.strictEqual(myRestaurant, 'Mein Restaurant', 'FEhler: ');
    });

}
exports.loginMMTestGuest = function () {
    it('We should be logged in as MMTestGuest', async () => {
        await page.goto(baseURL + '/logout', {timeout: 0, waitUntil: 'domcontentloaded'});
        await page.waitForSelector('li:nth-child(2) > a:nth-child(2)');

        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});

        await page.waitForSelector('.navbar > #mm-navbar-collapse > .nav > li:nth-child(3) > a');
        await page.click('.navbar > #mm-navbar-collapse > .nav > li:nth-child(3) > a');
        await page.waitFor(500);

        await page.waitForSelector('.modal-dialog > .modal-content > .form-signin > .modal-body > #username');
        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-body > #username');
        await page.type('.modal-dialog > .modal-content > .form-signin > .modal-body > #username', 'MMTestGuest');



        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-body > #password');
        await page.type('.modal-dialog > .modal-content > .form-signin > .modal-body > #password', '123');

        await page.waitForSelector('.modal-dialog > .modal-content > .form-signin > .modal-footer > #\_submit');
        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-footer > #\_submit');

        await page.waitForSelector('#mm-navbar-collapse > ul > li:nth-child(2) > a'); // Wait for  Mein Restaurant
        await navigationPromise;

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Meine Matches';
        expect(bodyHTML).to.contain(searchString, 'Konnte String ' + searchString + ' nicht finden');

        // assert.strictEqual(myRestaurant, 'Mein Restaurant', 'FEhler: ');
    });

}
exports.loginSYSTEM = function () {
    it('We should be logged in as SYSTEM', async () => {
        await page.goto(baseURL + '/logout', {timeout: 0, waitUntil: 'domcontentloaded'});
        await page.waitForSelector('li:nth-child(2) > a:nth-child(2)');

        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});

        await page.waitForSelector('.navbar > #mm-navbar-collapse > .nav > li:nth-child(3) > a');
        await page.click('.navbar > #mm-navbar-collapse > .nav > li:nth-child(3) > a');
        await page.waitFor(500);

        await page.waitForSelector('.modal-dialog > .modal-content > .form-signin > .modal-body > #username');
        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-body > #username');
        await page.type('.modal-dialog > .modal-content > .form-signin > .modal-body > #username', 'SYSTEM');



        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-body > #password');
        await page.type('.modal-dialog > .modal-content > .form-signin > .modal-body > #password', '123');

        await page.waitForSelector('.modal-dialog > .modal-content > .form-signin > .modal-footer > #\_submit');
        await page.click('.modal-dialog > .modal-content > .form-signin > .modal-footer > #\_submit');

        await page.waitForSelector('#mm-navbar-collapse > ul > li:nth-child(2) > a'); // Wait for  Mein Restaurant
        await navigationPromise;

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Meine Restaurant';
        expect(bodyHTML).to.contain(searchString, 'Konnte String ' + searchString + ' nicht finden');

        // assert.strictEqual(myRestaurant, 'Mein Restaurant', 'FEhler: ');
    });

}