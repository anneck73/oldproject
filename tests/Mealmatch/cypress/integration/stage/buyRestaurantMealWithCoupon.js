describe('Buing Restaurant meal completely with Coupon', () => {
    let timestampMeal
    let getOffer
    let mealId

    // This before block should iterate over the page in meal search results... But there are some quirks atm.

    // before("Preparation", function () {
    //     const findMeal = (page) => {
    //         cy.request({
    //             url: `http://mealmatch.local:8000/search/do?searchLocation=Essen&_locale=de&page=${page}`,
    //             method: "GET",
    //             timeout: 70000,
    //             failOnStatusCode: false
    //         }).then(response => {
    //             const found = response.body.response.find(p => {
    //                 if (cy.get('#meal-search-results').contains(timestampMeal)) {
    //                     cy.wrap(p).as('foundMeal')
    //                     return true
    //                 }
    //                 return false
    //             })
    //             if (!found && page < 49) {
    //                 findMeal(page + 1)
    //             }
    //         })
    //     }
    //     cy.wrap(findMeal).as("findMeal")
    //
    // })

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
            mealId = $url.toString().replace(/[^0-9]/g, '');
        })
        timestampMeal = Date.now()

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

        })

        // Figuring out the offerId

        // cy.get('#collapse2 > div > div > table > tbody > tr:nth-child(1) > td.action-td > div > button').should('have.attr', 'href').then(($href) => {
        //     // console.log(href.toString())
        //
        //    let getOffer = $href
        //
        // })
        // let offerId = getOffer.toString().replace(/[^0-9]/g, '');
        // offerId++;
        //
        // console.log(getOffer)

        // Date and Time
        cy.get('.date-title').click()
        cy.get('#mealmatch_apibundle_meal_mealevent_startDateTime').clear().type(today + ' 23:59')
        cy.get('.date-save').click()

        // cy.url().then(($url) => {
        //    let mealId = $url.toString().match(/(?<=api\/promeal\/manager\/).*?(?=\/edit)/g) //Will work when Cypress has upgraded to 4.0 Because lookbehind regex ist not supported in electron 59
        //     // parseInt(mealId)
        //
        // // Erstellen
        // cy.visit('/api/workflow/doTransition/Meal/' + mealId + '/create_meals')
        // mealId++
        //
        // // Veröffentlichen
        // cy.visit('/api/workflow/doTransition/Meal/' + mealId + '/start_meal')
        // })

        cy.get('a > .main-btn').click()

        cy.contains('tr', timestampMeal).contains('Veröffentlichen').click()
        cy.get('body > div.content > section > div.body-content > div > div > div.mm-layout.mm-layout-main-content > div > div > table > tbody')
            .should('contain', timestampMeal)
        cy.visit('/logout')
    })

    it('Buy complete meal with coupon', function () {
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


        // Call the function on the before block to find the meal ////////////////////////

        // cy.get('@findMeal').then(meal => {
        //     cy.log(meal)
        //     cy.contains('#meal-search-results', timestampMeal).contains('View').click()
        // })

        mealId++
        getOffer++
        getOffer++

        cy.visit('/api/mealticket/' + mealId + '/' + getOffer + '/createTicket')
        cy.get('.main_header_title').contains('Bezahlvorgang')
        cy.get('#mealmatch_apibundle_coupon_redemm_request_codeString').clear().type('eleven')
        cy.get('form > a > .btn-lg').click() // Coupon einlösen

        cy.get(':nth-child(4) > tbody > :nth-child(1) > .col-xs-2').contains('11.00')
        cy.get(':nth-child(2) > .col-xs-2').contains('- 11.00')
        cy.get(':nth-child(3) > .col-xs-2').contains('2.09')
        cy.get('.payment-sum > .col-xs-2').contains('0.00')

        cy.get('.btn-lg').contains('Bezahlvorgang vorbereiten').click()

        cy.get('.heading-text').contains('Deine bereits bezahlte Reservierung')

        // cy.get('.btn-lg').contains('Drucken als PDF').click() // For later

        // cy.contains('#meal-search-results', timestampMeal).contains('View').click()
        cy.visit('/logout')

    })

    it('Check if MMTestGuest is in Meal', function () {
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
        cy.visit('/api/promeal/manager/' + mealId + '/show')
        cy.get('.modal-guest').should('have.attr', 'href').then(($href) => {
            expect($href.toString()).contain('MMTestGuest')
        })

        cy.visit('/logout')

    })

})