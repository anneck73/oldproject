require('../global-before-env-driven');

describe('Test register as a restaurant', function () {

    it('Restaurant registration page should be here', async () => {
        await page.goto(baseURL + '/register/pro', {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('body > div.content > section > div.header-container.findmeal-banner > div > h3');

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Restaurant Registrierung';
        expect(bodyHTML).to.contain(searchString, 'Could not ' +
            'find the String "' + searchString + '" on the Restaurant-Registration site');


    });

    it('Register RestaurantUser, e-mail hint should be here', async () => {

        await page.waitForSelector('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_email');
        await page.type('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_email', 'puppeteer_restaurant@mealmatch.de');

        await page.waitForSelector('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_username');
        await page.type('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_username', 'puppeteer_restaurant');

        await page.waitForSelector('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_plainPassword_first');
        await page.type('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_plainPassword_first', '123');

        await page.waitForSelector('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_plainPassword_second');
        await page.type('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_plainPassword_second', '123');

        await page.waitForSelector('.link-div > .form-group:nth-child(1) > .form-group > .checkbox > .required');
        await page.click('.link-div > .form-group:nth-child(1) > .form-group > .checkbox > .required');

        await page.waitForSelector('form > .form-bg > .for-padding > .button-div > #\_submit');
        await page.click('form > .form-bg > .for-padding > .button-div > #\_submit');

        await navigationPromise;

        await page.waitForSelector('body > div.content > section > div.header-container.findmeal-banner > div > h3');
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Benutzerkonto Bestätigen';
        expect(bodyHTML).to.contain(searchString, 'Could not ' +
            'find the String "' + searchString + '" on the Email reminder site');
    });

    it('RestaurantUser should be visible in the backend now', async () => {
        await page.goto(baseURL + '/login', {timeout: 0, waitUntil: 'domcontentloaded'});
        await page.waitForSelector('.form-signin > .row > .col-lg-12 > .form-group > #username_ui');
        await page.click('.form-signin > .row > .col-lg-12 > .form-group > #username_ui');

        await page.type('.form-signin > .row > .col-lg-12 > .form-group > #username_ui', 'SYSTEM');

        await page.click('.form-signin > .row > .col-lg-12 > .form-group > #password_ui');
        await page.type('.form-signin > .row > .col-lg-12 > .form-group > #password_ui', '123');

        await page.waitForSelector('.form-bg > .for-padding > .form-signin > .button-div > #\_submit_ui');
        await page.click('.form-bg > .for-padding > .form-signin > .button-div > #\_submit_ui');

        await page.waitForSelector('body > div.content > section > div.search-container > div > h3');

        await page.goto(baseURL + '/admin', {timeout: 0, waitUntil: 'domcontentloaded'});
        await page.waitForSelector('#easyadmin-list-User > div > div > section.content-header > div > div.col-sm-5 > h1');

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'puppeteer_restaurant';
        expect(bodyHTML).to.contain(searchString, 'Could not ' +
            'find the String "' + searchString + '" in the backend user list');

        await navigationPromise;
    });

    it('Delete the RestaurantUser', async () => {
        await page.waitForSelector('.table > tbody > tr:nth-child(1) > .actions > .text-primary');
        await page.click('.table > tbody > tr:nth-child(1) > .actions > .text-primary');

        await navigationPromise;

        await page.waitForSelector('.row > .col-xs-12 > .form-group > #form-actions-row > .btn-default');
        await page.click('.row > .col-xs-12 > .form-group > #form-actions-row > .btn-default');

        await navigationPromise;

        await page.click('#form-actions-row > a.btn.btn-default.action-delete');
        await page.waitFor(10000);
        await page.click('#modal-delete-button');

        await navigationPromise;

        await page.waitForSelector('#easyadmin-list-User > div > div > section.content-header > div > div.col-sm-5 > h1');

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'puppeteer_restaurant';
        expect(bodyHTML).to.not.contain(searchString, 'RestaurantUser with name "'
            + searchString + '" is not deleted');
    });
});
