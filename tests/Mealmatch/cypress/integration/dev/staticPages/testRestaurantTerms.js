describe('Test if static pages are there', function () {
    it('Test restaurant terms page', function () {
        cy.visit('/restaurant/terms')
        cy.get('.mm-exported-html-container > p:nth-child(1) > font:nth-child(1) > font:nth-child(1) > ' +
            'b:nth-child(1)').contains('Nutzungsbedingungen für Restaurants')
        // cy.contains('Nutzungsbedingungen für Restaurants')
    })
})

