describe('Test login as a restaurant',  () => {
    it('Login test', () => {
        cy.visit('/login')
        cy.get('#username_ui').type('MMTestRestaurant')
        cy.get('#password_ui').type('123')
        cy.get('#_submit_ui').click()
    })
})