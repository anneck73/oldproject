require('../global-before-env-driven');

describe('Test Cologne page', function () {

    it('Cologne page should be here', async () => {
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(4) > div > ul > li:nth-child(1) > a');
        await page.click('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(4) > div > ul > li:nth-child(1) > a');
        await page.waitForSelector('body > div.content > section > div.header-container.findmeal-banner > div > h3'); // Social-Dining/DE/Köln
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Meals in Köln';
        expect(bodyHTML).to.contain(searchString,'Konnte den gesuchten String "' + searchString + '" nicht ' +
            'auf der Köln-Seite finden');
    });
});
