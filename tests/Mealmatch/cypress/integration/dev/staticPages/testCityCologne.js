describe('Test if static pages are there', function () {
    it('Test "Meals in Köln" page', function () {
        cy.visit('/p/social-dining/DE/K%C3%B6ln')
        cy.contains('Meals in Köln')
    })
})

