// require('./global-before-env-driven');

context('Register Actions', () => {
    describe('Test register as a restaurant',  () => {
        const username = 'cypress_restaurant'
        const password = '123'
        const email = 'cypress_restaurant@mealmatch.de'
        const age = true
        const terms = true

        // Cypress.Commands.add('registerRestaurantWithToken', (fos_user_registration_form__token) => {
        //     cy.request({
        //         method: 'POST',
        //         url: '/register/pro',
        //         failOnStatusCode: false, // dont fail so we can make assertions
        //         form: true, // we are submitting a regular form body
        //         body: {
        //             email,
        //             username,
        //             password,
        //             age,
        //             terms,
        //             registerRestaurantWithToken: fos_user_registration_form__token // insert this as part of form body
        //         }
        //     })
        // })

        // it('Restaurant registration page should be here',  () => {
        //     cy.visit('/register/pro')
        //
        //     // cy.server()
        //
        //     // cy.route('/register/pro','GET').as(getRegistration)
        //     // cy.wait('@getRegistration')
        //
        //     cy.contains('Restaurant Registrierung')
        //
        //
        // });

        it('Register RestaurantUser, e-mail hint should be here',  () => {

            cy.visit('/register/pro')

            cy.contains('Restaurant Registrierung')

            cy.get('#fos_user_registration_form_email')
                .type(email)
                .should('have.value', 'cypress_restaurant@mealmatch.de')

            cy.get('#fos_user_registration_form_username')
                .type(username)
                .should('have.value', 'cypress_restaurant')

            cy.get('#fos_user_registration_form_plainPassword_first')
                .type(password)
                .should('have.value', '123')

            cy.get('#fos_user_registration_form_plainPassword_second')
                .type(password)
                .should('have.value', '123')

            cy.get('#fos_user_registration_form_termsAccepted').check()
                .should('be.checked')

            cy.get('#fos_user_registration_form_over18')
                .should('be.checked')

            cy.get('.button-div > #_submit').click()


            // cy.request('/register/pro')
            //     .its('body')
            //     .then((body) => {
            //         // we can use Cypress.$ to parse the string body
            //         // thus enabling us to query into it easily
            //         const $html = Cypress.$(body)
            //         const csrf  = $html.find("input[name=registerRestaurantWithToken]").val()
            //
            //         cy.registerRestaurantWithToken(csrf)
            //             .then((resp) => {
            //                 expect(resp.status).to.eq(200)
            //                 expect(resp.body).to.include("<h3>Restaurant Registrierung</h3>")
            //             })
            //     })

            cy.get('body > div.content > section > div.body-content > div > div > div > div > div').should('contain',
                'Eine E-Mail wurde an cypress_restaurant@mealmatch.de gesendet')


        })

        it('should be logged in as SYSTEM', function () {
            cy.visit('/admin')
            cy.get('#username_ui').type('SYSTEM')
            cy.get('#password_ui').type('123')
            cy.get('#_submit_ui').click()
            // cy.get('.navbar-right > li:nth-child(2) > a').should('contain','Mein Restaurant')

            cy.get('.logo').should('contain', 'Mealmatch Admin')
            cy.get('#main > div.table-responsive > table > tbody > tr:nth-child(1)')
                .should('contain', 'cypress_restaurant')

            cy.get('tr:nth-child(1) .action-show').click()
            cy.get('.form-group:nth-child(1) .form-control').should(($formControl) => {
                expect($formControl).to.contain('cypress_restaurant')
            })
        .then(($formControl) => {
            cy.get('.action-delete').click()
            cy.get('#modal-delete-button').click()
        })
            cy.get('#main > div.table-responsive > table > tbody').should(($table) => {
                expect($table).to.not.contain('cypress_restaurant')
            })

        });


        //
        // it('RestaurantUser should be visible in the backend now', async () => {
        //     await page.goto(baseURL + '/login', {timeout: 0, waitUntil: 'domcontentloaded'});
        //     await page.waitForSelector('.form-signin > .row > .col-lg-12 > .form-group > #username_ui');
        //     await page.click('.form-signin > .row > .col-lg-12 > .form-group > #username_ui');
        //
        //     await page.type('.form-signin > .row > .col-lg-12 > .form-group > #username_ui', 'SYSTEM');
        //
        //     await page.click('.form-signin > .row > .col-lg-12 > .form-group > #password_ui');
        //     await page.type('.form-signin > .row > .col-lg-12 > .form-group > #password_ui', '123');
        //
        //     await page.waitForSelector('.form-bg > .for-padding > .form-signin > .button-div > #\_submit_ui');
        //     await page.click('.form-bg > .for-padding > .form-signin > .button-div > #\_submit_ui');
        //
        //     await page.waitForSelector('body > div.content > section > div.search-container > div > h3');
        //
        //     await page.goto(baseURL + '/admin', {timeout: 0, waitUntil: 'domcontentloaded'});
        //     await page.waitForSelector('#easyadmin-list-User > div > div > section.content-header > div > div.col-sm-5 > h1');
        //
        //     let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        //     let searchString = 'puppeteer_restaurant';
        //     expect(bodyHTML).to.contain(searchString, 'Could not ' +
        //         'find the String "' + searchString + '" in the backend user list');
        //
        //     await navigationPromise;
        // });
        //
        // it('Delete the RestaurantUser', async () => {
        //     await page.waitForSelector('.table > tbody > tr:nth-child(1) > .actions > .text-primary');
        //     await page.click('.table > tbody > tr:nth-child(1) > .actions > .text-primary');
        //
        //     await navigationPromise;
        //
        //     await page.waitForSelector('.row > .col-xs-12 > .form-group > #form-actions-row > .btn-default');
        //     await page.click('.row > .col-xs-12 > .form-group > #form-actions-row > .btn-default');
        //
        //     await navigationPromise;
        //
        //     await page.waitForSelector('#form-actions-row > a.btn.btn-default.action-delete');
        //     await page.focus('#form-actions-row > a.btn.btn-default.action-delete');
        //     await page.click('#form-actions-row > a.btn.btn-default.action-delete');
        //     await page.waitForSelector('.modal-content', {visible: true});
        //     const button = await page.waitForSelector('#modal-delete-button', {visible: true});
        //     await button.click();
        //     // await page.waitForFunction('.modal-content', { hidden: true });
        //
        //
        //     // await navigationPromise;
        //
        //     await page.goto(baseURL + '/admin', {timeout: 0, waitUntil: 'domcontentloaded'});
        //     // await navigationPromise;
        //
        //     await page.waitForSelector('#main', {visible: true});
        //     let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        //     let searchString = 'puppeteer_restaurant';
        //     expect(bodyHTML).to.not.contain(searchString, 'RestaurantUser with name "'
        //         + searchString + '" is not deleted');
        //     await page.goto(baseURL + '/logout', {timeout: 0, waitUntil: 'domcontentloaded'});
        //
        // });
    })
})