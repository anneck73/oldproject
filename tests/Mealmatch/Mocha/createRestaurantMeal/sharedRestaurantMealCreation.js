exports.createRestaurantMeal = function () {


    it('Create a new restaurantmeal', async () => {
        let today = new Date();
        let dd = String(today.getDate()).padStart(2, '0');
        let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
        let yyyy = today.getFullYear();

        today = dd + '.' + mm + '.' + yyyy;

        // Meal Details
        await page.waitForSelector('.navbar > #mm-navbar-collapse > .nav > li:nth-child(1) > a');
        await page.click('.navbar > #mm-navbar-collapse > .nav > li:nth-child(1) > a');

        await navigationPromise;

        await page.waitForSelector('.col-md-12 > .create-meal > a > .create-btn > span:nth-child(2)');
        await page.click('.col-md-12 > .create-meal > a > .create-btn > span:nth-child(2)');

        await navigationPromise;
        let url = await page.url();
        mealId = url.toString().replace(/[^0-9]/g, '');
        mealId++;

        await page.waitForSelector('#mealmatch_apibundle_meal_promeal_page_tableTopic');
        await page.click('#mealmatch_apibundle_meal_promeal_page_tableTopic', {clickCount: 3});
        await page.type('#mealmatch_apibundle_meal_promeal_page_tableTopic', 'Puppeteer Coupon Meal');  // + date.getTime());

        await page.waitForSelector('#mealmatch_apibundle_meal_promeal_page_maxNumberOfGuest');
        await page.click('#mealmatch_apibundle_meal_promeal_page_maxNumberOfGuest', {clickCount: 3});
        await page.type('#mealmatch_apibundle_meal_promeal_page_maxNumberOfGuest', '10');

        await page.waitForSelector('#cke_1_contents > iframe');
        await page.click('#cke_1_contents > iframe', {clickCount: 3});
        await page.type('html', 'A Puppeteer coupon meal.');

        await page.click('.button-div > .main-btn');
        await navigationPromise;

        // Offer Details
        await page.waitForSelector('.offer-title');
        await page.focus('.offer-title');
        await page.click('.offer-title');
        await page.waitFor(3000);

        // Figuring out the offerId
        let getOffer = await page.evaluate(() => {
            const buttons = document.querySelectorAll('#collapse2 > div > div > table > tbody > tr:nth-child(1) > td.action-td > div > button');
            return [].map.call(buttons, value => value.getAttribute('href'));
        });

        offerId = getOffer.toString().replace(/[^0-9]/g, '');
        offerId++;


        // Date and Time
        await page.waitForSelector('.date-title');
        await page.focus('.date-title');
        await page.click('.date-title');
        await page.waitFor(3000);
        await page.waitForSelector('#mealmatch_apibundle_meal_mealevent_startDateTime');
        await page.click('#mealmatch_apibundle_meal_mealevent_startDateTime', {clickCount: 3});

        await page.type('#mealmatch_apibundle_meal_mealevent_startDateTime', today + ' 23:59');
        await page.click('.date-save');
        await navigationPromise;

        //Erstellen
        await page.waitForSelector('a > .main-btn');
        await page.click('a > .main-btn');
        await navigationPromise;

        //"Meine Meals"
        await page.waitForSelector('.navbar-right > li:nth-child(2) > a');
        await page.click('.navbar-right > li:nth-child(2) > a');
        await navigationPromise;

        // Veröffentlichen
        // todo: The meal should be made public via selecting the right element. But for now just we just request the transition URL
        await page.goto(baseURL + '/api/workflow/doTransition/Meal/' + mealId + '/start_meal', {timeout: 0, waitUntil: 'domcontentloaded'});
        await page.waitForSelector('.search-sec > h3');
        // await page.waitForSelector('a[href="/api/workflow/doTransition/Meal/' + id + '/start_meal"]');


        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = '/api/workflow/doTransition/Meal/' + mealId +'/stop_meal';
        expect(bodyHTML).to.contain(searchString, 'Konnte String ' + searchString + ' nicht finden');

        await page.click('.dropdown-toggle > .img-responsive');
        await page.click('#mm-profile-image-dropdown-menu > li:nth-child(4) > a');
        await page.waitForSelector('li:nth-child(3) > a:nth-child(2)');
    });
};