describe('Test if static pages are there', function () {
    it('Test events page', function () {
        cy.visit('/events')
        cy.contains('Aktionen')
    })
})

