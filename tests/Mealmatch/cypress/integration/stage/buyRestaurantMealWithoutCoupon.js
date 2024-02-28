describe('Buing Restaurant meal without Coupon', function () {
    let timestampMeal
    let getOffer
    let mealId
    let transactionId

    it('Create a new meal', function () {
        cy.visit("/login")
            .its('body')
            .then((body) => {
                // we can use Cypress.$ to parse the string body
                // thus enabling us to query into it easily
                const $html = Cypress.$('body')
                const csrf = $html.find("input[name=\"_csrf_token\"]").val()
                console.log('html: ' + $html)
                console.log('csrftoken: ' + csrf)
                cy.loginAsMMTestRestaurant(csrf)
                    .then((resp) => {
                        expect(resp.status).to.eq(200)
                        expect(resp.body).to.include("Mein Restaurant")
                    })
            })

        let today = new Date();
        let dd = String(today.getDate()).padStart(2, '0');
        let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        let yyyy = today.getFullYear();
        today = dd + '.' + mm + '.' + yyyy;

        // Meal Details
        // cy.get('.navbar > #mm-navbar-collapse > .nav > li:nth-child(1) > a').click()
        cy.visit('/api/meal/')
        cy.get('.col-md-12 > .create-meal > a > .create-btn > span:nth-child(2)').click()

        cy.url().then(($url) => {
            mealId = $url.toString().match(/\/api\/promeal\/manager\s*\/\s*([\d]+)/)[1];
        })
        console.log('MealId: ' + mealId)
        timestampMeal = Date.now()

        // Meal details

        cy.get('#mealmatch_apibundle_meal_promeal_page_tableTopic').clear().type('Coupon Meal ' + timestampMeal)
        cy.get('#mealmatch_apibundle_meal_promeal_page_maxNumberOfGuest').clear().type('10')

        cy.window()
            .then(win => {
                win.CKEDITOR.instances["mealmatch_apibundle_meal_promeal_page_description"].setData("<p>A Cypress coupon meal</p>");
            });
        cy.get('.button-div > .main-btn').click() // Speichern

        cy.get('.offer-title').click() // Offer scrolldown
        cy.get('.add-offer').click() // Add Offer

        cy.get('tr:nth-child(3) .icon-note').click() // Edit new offer


        cy.get('#collapse2 > div > div > table > tbody > tr:nth-child(3) > td.action-td > div > button').should('have.attr', 'href').then(($href) => {
            // console.log(href.toString())

            getOffer = $href.toString().replace(/[^0-9]/g, '');


            let timestampOffer = Date.now()
            cy.get('#offer-' + getOffer + ' #mealmatch_apibundle_meal_mealoffer_name').clear().type('Offer ' + timestampOffer)
            //CKEditor
            cy.window()
                .then(win => {
                    win.CKEDITOR.instances[getOffer].setData("<p>A Cypress meal bought with coupon</p>");
                });
            cy.get('#offer-' + getOffer + ' #mealmatch_apibundle_meal_mealoffer_price').clear().type('11')
            cy.get('#offer-' + getOffer + ' > .no-padding > .mm-tab-section > form > .row > .col-lg-12 > .main-btn').click()

            // Date and Time
            cy.get('.date-title').click()
            cy.get('#mealmatch_apibundle_meal_mealevent_startDateTime').clear().type(today + ' 23:59')
            cy.get('.date-save').click()
            cy.get('a > .main-btn').click()

            cy.contains('tr', timestampMeal).contains('Veröffentlichen').click()
            cy.get('body > div.content > section > div.body-content > div > div > div.mm-layout.mm-layout-main-content > div > div > table > tbody')
                .should('contain', timestampMeal)
            cy.visit('/logout')
        })
    })

    it('Buy a Restaurant meal', function () {
        cy.visit("/login")
            .its('body')
            .then((body) => {
                // we can use Cypress.$ to parse the string body
                // thus enabling us to query into it easily
                const $html = Cypress.$('body')
                const csrf = $html.find("input[name=\"_csrf_token\"]").val()
                console.log('html: ' + $html)
                console.log('csrftoken: ' + csrf)
                cy.loginAsMMTestGuest(csrf)
                    .then((resp) => {
                        expect(resp.status).to.eq(200)
                        expect(resp.body).to.include("Meine Matches")
                    })
            })
        cy.visit('/')
        cy.get('.home-search').type('Essen')
        cy.get('.search-btn').click()


        mealId++
        getOffer++
        getOffer++

        cy.visit('/api/mealticket/' + mealId + '/' + getOffer + '/createTicket')
        cy.contains('Bezahlvorgang')

        cy.get(':nth-child(7) > tbody > :nth-child(1) > .col-xs-2').contains('11.00')

        cy.get(':nth-child(2) > .col-xs-2').contains('2.09')
        cy.get('.payment-sum > .col-xs-2').contains('11.00')

        cy.get('a:nth-child(13) > .btn-lg').contains('Bezahlvorgang vorbereiten').click()

        //Bezahldaten
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_firstName').clear().type('MMGuest')
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_lastName').clear().type('TESTUSER')
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_address').clear().type('Widdersdorfer Str. 207')
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_postalCode').clear().type('50825')
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_city').clear().type('Köln')
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_region').clear().type('NRW')
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_birthday').type('1974-04-04')
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_nationality').select('DE')
        cy.get('#mealmatch_apibundle_meal_basemealticket_payinrequireddata_countryOfResidence').select('DE')
        cy.get('.btn-lg').click() // Speichern
        cy.get('.btn-lg').click() // Absenden

        cy.contains('Bezahlen').click()
        // cy.get('a:nth-child(12) > .btn-lg').click() // Bezahlen


        cy.contains('Amount to pay : 11 EUR')
        cy.get('#number').clear().type('4706750000000009')
        cy.get('#expirationDate_month').select('12')
        cy.get('#expirationDate_year').select('29')
        cy.get('#cvv').clear().type('123')
        cy.get('#submitButton').click()


        cy.url().then(($url) => {
            const url = $url.toString()
            cy.log('URL: ' + url)
            transactionId = url.match(/transactionId\s*=\s*([\S\s]+)/)[1]; // Get transactionId. Means get all after =
            cy.log('TransactionId: ' + transactionId)
        })

        console.log('MealId: ' + mealId) // Is visible here but not in same it() where declaration is. JUST a REMINDER
        // Payment buttons should not exist after succesful payment
        cy.get('form > a > .btn-lg').should('not.exist')
        cy.get('[href="/api/workflow/doTransition/Ticket/46/prepare_ticket"] > .btn-lg').should('not.exist')

        cy.visit('/logout')
    })

    it('Checking MealticketTransaction if payment was successful', function () {

        // Sites that implement old school security measures such as clickjacking or framebusting break Cypress. //
        // We are running into this in the Mangopay Sandbox //
        // Even enabling modifyObstructiveCode doesn't help so we must rely on our backend. //

        cy.wait(10000)
        cy.visit("/login")
            .its('body')
            .then((body) => {
                // we can use Cypress.$ to parse the string body
                // thus enabling us to query into it easily
                const $html = Cypress.$('body')
                const csrf = $html.find("input[name=\"_csrf_token\"]").val()
                console.log('html: ' + $html)
                console.log('csrftoken: ' + csrf)
                cy.loginAsSYSTEM(csrf)
                    .then((resp) => {
                        expect(resp.status).to.eq(200)
                        expect(resp.body).to.include("Mein Restaurant")
                    })
            })
        cy.visit('/admin/?entity=MealTicketTransaction&action=list&menuIndex=2&submenuIndex=3')
        cy.get('.form-control').clear().type(transactionId)
        cy.get('.input-group-btn > .btn').click()
        cy.contains(transactionId).contains('PAYIN_NORMAL_SUCCEEDED').contains('Anzeigen').click()
        cy.get(':nth-child(3) > .col-sm-10 > .form-control').should('have.text', 'PAYIN_NORMAL_SUCCEEDED')
    })
})
