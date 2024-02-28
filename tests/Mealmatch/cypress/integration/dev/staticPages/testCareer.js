describe('Test if static pages are there', function () {
    it('Test career page', function () {
        cy.visit('/career')
        cy.contains('Karriere')
    })
})

