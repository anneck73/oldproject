describe('Test if static pages are there', function () {
    it('Test restaurant terms page', function () {
        cy.visit('/mangopay/terms')
        cy.contains('Allgemeine Geschäftsbedingungen zur Nutzung des Services MANGOPAY')
    })
})

