describe('Test if static pages are there', function () {
    it('Test terms page', function () {
        cy.visit('/terms')
        cy.contains('Nutzungsbedingungen')
    })
})

