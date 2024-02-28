describe('Test if static pages are there', function () {
    it('Test trust and security page', function () {
        cy.visit('/TrustAndSecurity')
        cy.contains('Vertrauen braucht Sicherheit')
    })
})

