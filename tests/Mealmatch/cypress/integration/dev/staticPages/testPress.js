describe('Test if static pages are there', function () {
    it('Test presse page', function () {
        cy.visit('/presse')
        cy.contains('Presse')
    })
})

