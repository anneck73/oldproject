require('../global-before-env-driven');

describe('Test privacy statement page', function () {

    it('Privacy page should be here', async () => {
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(3) > div > ul > li:nth-child(1) > a');
        await page.click('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div:nth-child(3) > div > ul > li:nth-child(1) > a');
        await page.waitForSelector('body > main > section > iframe'); // PDF
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'DatenschutzerklärungGmbH.pdf';
        expect(bodyHTML).to.contain(searchString,
            'Konnte den gesuchten String "' + searchString + '" nicht auf der Datenschutz-Seite finden');
    });
});
