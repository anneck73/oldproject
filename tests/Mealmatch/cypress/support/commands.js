Cypress.Commands.add('loginAsMMTestRestaurant', (csrf) => {
    cy.request({
        method: "POST",
        url: "/login_check",
        form: true, // we are submitting a regular form body
        body: {
            _username: "MMTestRestaurant",
            _password: "123",
            _csrf_token: csrf // insert this as part of form body
        }
    })
    // .then((resp) => {
    //     window.localStorage.setItem()
    // })
})
Cypress.Commands.add('loginAsMMTestGuest', (csrf) => {
    cy.request({
        method: "POST",
        url: "/login_check",
        form: true, // we are submitting a regular form body
        body: {
            _username: "MMTestGuest",
            _password: "123",
            _csrf_token: csrf // insert this as part of form body
        }
    })
    // .then((resp) => {
    //     window.localStorage.setItem()
    // })
})
Cypress.Commands.add('loginAsSYSTEM', (csrf) => {
    cy.request({
        method: "POST",
        url: "/login_check",
        form: true, // we are submitting a regular form body
        body: {
            _username: "SYSTEM",
            _password: "123",
            _csrf_token: csrf // insert this as part of form body
        }
    })
})
