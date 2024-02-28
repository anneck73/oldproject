describe('Test if static pages are there', function () {
    it('Test invite a friend page', function () {
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
        cy.visit('/api/invite/afriend')
        cy.contains('Einen Freund einladen')
    })
})

