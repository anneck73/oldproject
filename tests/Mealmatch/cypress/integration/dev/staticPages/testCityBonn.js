describe('Test if static pages are there', function () {
    it('Test "Meals in Bonn" page', function () {
        cy.visit('/p/social-dining/DE/Bonn')
        cy.contains('Meals in Bonn')
    })
})

