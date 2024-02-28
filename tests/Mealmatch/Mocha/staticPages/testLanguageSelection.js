require('../global-before-env-driven');

describe('Test language selection', function () {

    it('Switch to English', async () => {
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(5) > div > ul > li:nth-child(2) > a');
        await page.click('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(5) > div > ul > li:nth-child(2) > a');
        await page.waitForSelector('body > div.content > section > div.search-container > div > h3'); // Search sloagen
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Search for interesting Meals in your city';
        expect(bodyHTML).to.contain(searchString,'Konnte den gesuchten String "' + searchString + '" nicht ' +
            'auf der englischen Seite finden');
    });
    it('Switch to German', async () => {
        await page.goto(baseURL + '/en/', {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(5) > div > ul > li:nth-child(1) > a');
        await page.click('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(5) > div > ul > li:nth-child(1) > a');
        await page.waitForSelector('body > div.content > section > div.search-container > div > h3'); // Search slogan
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Suche interessante Meals in deiner Stadt';
        expect(bodyHTML).to.contain(searchString,'Konnte den gesuchten String "' + searchString + '" nicht ' +
            'auf der deutschen Seite finden');
    });
});
