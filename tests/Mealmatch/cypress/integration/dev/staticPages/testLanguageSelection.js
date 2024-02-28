describe('Test if static pages are there', function () {
    it('Test language selection', function () {
        cy.visit('/en')
        cy.contains('Search for interesting Meals in your city')
        cy.visit('/')
        cy.contains('Suche interessante Meals in deiner Stadt')
    })
})

