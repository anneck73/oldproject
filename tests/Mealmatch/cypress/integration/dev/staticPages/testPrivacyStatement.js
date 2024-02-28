describe('Test if static pages are there', function () {
    it('Test privacy statement page', function () {
        cy.visit('/privacystatement')
        cy.contains('Datenschutzerklärung')
    })
})

