describe('Test if static pages are there', function () {
    it('Test "How it works" page', function () {
        cy.visit('/how_it_works')
        cy.contains('So funktioniert es')
    })
})

