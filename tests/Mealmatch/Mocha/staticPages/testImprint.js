require('../global-before-env-driven');

describe('Test imprint page', function () {

    it('Imprint page should be here', async () => {
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(3) > div > ul > li:nth-child(3) > a');
        await page.click('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(3) > div > ul > li:nth-child(3) > a');
        await page.waitForSelector('body > div.content > section > div.header-container.findmeal-banner > div > h3'); // Überschrift
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Impressum';
        expect(bodyHTML).to.contain(searchString,'Konnte den gesuchten String "' + searchString + '" nicht ' +
            'auf der Impressums-Seite finden');
    });
});
