describe('Test if static pages are there', function () {
    it('Test about page', function () {
        cy.visit('/about')
        cy.contains('Über Mealmatch')
    })
})

