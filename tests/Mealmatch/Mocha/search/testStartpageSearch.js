require('../global-before-env-driven');

describe('Test the search function', function () {

    it('There should be Meals in Cologne', async () => {
        // We have to work around the Geolocation Permission from the Browser
        await page.evaluateOnNewDocument(function() {
            navigator.geolocation.getCurrentPosition = function (cb) {
                setTimeout(() => {
                    cb({
                        'coords': {
                            accuracy: 21,
                            altitude: null,
                            altitudeAccuracy: null,
                            heading: null,
                            latitude: 50.941278,
                            longitude: 6.958281,
                            speed: null
                        }
                    })
                }, 1000)
            }
        });
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('.main > .search-container > .search-sec > form > .home-search');
        await page.click('.main > .search-container > .search-sec > form > .home-search');

        await page.type('.main > .search-container > .search-sec > form > .home-search', 'Köln');
        await page.click('.main > .search-container > .search-sec > form > .search-btn');
        await page.waitForSelector('body > div.content > section > div.header-container.findmeal-banner > div > h3'); // Überschrift

        let bodyHTML = await page.waitForSelector('#resultCount').then(() => page.evaluate(() => document
            .getElementById('resultCount').innerText));

        expect(bodyHTML).to.be.oneOf(['Meal in Köln gefunden', 'Meals in Köln gefunden'],
            'Kein Meal in Köln gefunden');
    });
});
