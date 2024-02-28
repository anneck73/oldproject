require('../global-before-env-driven');

describe('Test register as a home user', function () {
    it('Home user registration page should be here', async () => {
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});

        await page.waitForSelector('#mm-navbar-collapse > ul > li:nth-child(2) > a');
        await page.click('#mm-navbar-collapse > ul > li:nth-child(2) > a');

        await navigationPromise;

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Registrierung';
        expect(bodyHTML).to.contain(searchString, 'Could not ' +
            'find the String "' + searchString + '" on the HomeUser-Registration site');

    });
    it('Email hint for registered home user should be here', async () => {
        await page.waitForSelector('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_email');
        await page.click('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_email');

        await page.type('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_email', 'puppeteer_homeuser@mealmatch.de');

        await page.type('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_username', 'puppeteer_homeuser');

        await page.type('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_plainPassword_first', '123');
        await page.type('form > .form-bg > .for-padding > .form-group > #fos_user_registration_form_plainPassword_second', '123');

        await page.waitForSelector('.link-div > .form-group:nth-child(1) > .form-group > .checkbox > .required');
        await page.click('.link-div > .form-group:nth-child(1) > .form-group > .checkbox > .required');

        await page.waitForSelector('form > .form-bg > .for-padding > .button-div > #\_submit');
        await page.click('form > .form-bg > .for-padding > .button-div > #\_submit');

        await page.waitForSelector('body > div.content > section > div.body-content > div > div > div > div > div');

        await navigationPromise;

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'um Ihr Benutzerkonto zu bestätigen';
        expect(bodyHTML).to.contain(searchString, 'Could not ' +
            'find the String "' + searchString + '" on the Email reminder site');

    });

    it('Home user should be visible on the backend now', async () => {
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
        let searchString = 'puppeteer_homeuser';
        expect(bodyHTML).to.contain(searchString, 'Could not ' +
            'find the String "' + searchString + '" in the backend user list');

        await navigationPromise;
    });
    it('Delete the home user', async () => {
        await page.waitForSelector('.table > tbody > tr:nth-child(1) > .actions > .text-primary');
        await page.click('.table > tbody > tr:nth-child(1) > .actions > .text-primary');

        await navigationPromise;

        await page.waitForSelector('.row > .col-xs-12 > .form-group > #form-actions-row > .btn-default');
        await page.click('.row > .col-xs-12 > .form-group > #form-actions-row > .btn-default');

        await navigationPromise;

        await page.click('#form-actions-row > a.btn.btn-default.action-delete');
        await page.waitFor(5000);
        await page.click('#modal-delete-button');

        await navigationPromise;

        await page.waitForSelector('#easyadmin-list-User > div > div > section.content-header > div > div.col-sm-5 > h1');

        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'puppeteer_homeuser';
        expect(bodyHTML).to.not.contain(searchString, 'User with username "'
            + searchString + '" is not deleted');
    });
});
