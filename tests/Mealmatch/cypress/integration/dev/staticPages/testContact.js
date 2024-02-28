describe('Test if static pages are there', function () {
    it('Test contact page', function () {
        cy.visit('/contact')
        cy.contains('Kontaktformular')
    })
})

