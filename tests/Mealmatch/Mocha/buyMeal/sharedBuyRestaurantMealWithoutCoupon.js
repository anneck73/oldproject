exports.buyRestaurantMealWithoutCoupon = function () {
    it('Buying a 10 EUR Restaurantmeal WITHOUT coupon', async () => {
        // @todo To keep things more simple we use the direct URL to create a ticket. Maybe we should do it via UI later.
        await page.goto(baseURL + '/api/mealticket/' + mealId + '/' + offerId + '/createTicket');
        await page.waitForSelector('a:nth-child(12) > .btn-lg');

        // Get the price of the Meal
        const mealPrice = await page.evaluate(() => document.querySelector('tr:nth-child(1) > .valign-top').innerText);
        const mwst = await page.evaluate(() => document.querySelector('tr:nth-child(2) > .col-xs-2').innerText);
        const endPrice = await page.evaluate(() => document.querySelector('.text-center:nth-child(2)').innerText);
        // console.log('Meal Preis: ' + mealPrice);
        // console.log('MwSt: ' + mwst);
        // console.log('Endprice ' + endPrice);

        await page.focus('a:nth-child(12) > .btn-lg');
        await page.click('a:nth-child(12) > .btn-lg'); // Klick Bezahlung vorbereiten
        navigationPromise;

        await page.waitForSelector('.btn-lg');
        await page.click('.btn-lg'); // Persönliche Date
        navigationPromise;

        await page.waitForSelector('.btn-lg');
        await page.click('.btn-lg'); // Benutzerdaten absenden
        navigationPromise;

        await page.waitForSelector('a:nth-child(12) > .btn-lg');
        await page.click('a:nth-child(12) > .btn-lg'); // Bezahlen
        navigationPromise;

        await page.waitForSelector('.heading'); // Mangopay: Amount to pay

        await page.click('#number');
        await page.type('#number', '4706750000000033'); //4706750000000009
        await page.click('#expirationDate_month');
        await page.waitFor(500);
        await page.select('#expirationDate_month', '06');
        await page.click('#expirationDate_year');
        await page.waitFor(500);
        await page.select('#expirationDate_year', '28');
        await page.click('#cvv');
        await page.type('#cvv', '123');
        await page.click('#submitButton'); // Validate
        navigationPromise;

        await page.waitForSelector('.text-center:nth-child(2)');
        await page.waitFor(5000); // Wait 1sec just to be sure we got signal from mangopay
        await page.reload();
        navigationPromise;

        let searchString = 'Deine bereits bezahlte Reservierung';
        expect(await page.evaluate(() => document.querySelector('h2').innerText)).to.contain(searchString, 'Meal ist nicht bezahlt. SearchString: ' + searchString);

    });
};