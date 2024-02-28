describe('Test if the Blog is there', function () {
    it('Test Blog page', function () {
        cy.visit('http://blog.mealmatch.de')
        cy.contains('Social-Dining Plattform')
    })
})

