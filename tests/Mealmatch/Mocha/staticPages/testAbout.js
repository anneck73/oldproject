require('../global-before-env-driven');

describe('Test about page', function () {

    it('About page should be here', async () => {
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('.col-md-offset-0 > .footer-menu > ul > li:nth-child(1) > a');
        await page.click('.col-md-offset-0 > .footer-menu > ul > li:nth-child(1) > a');
        await page.waitForSelector('body > div.content > section > div.header-container.findmeal-banner > div > h3'); // Über Mealmatch
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Über Mealmatch';
        expect(bodyHTML).to.contain(searchString, 'Konnte den ' +
            'gesuchten String "' + searchString + '" nicht auf der About-Seite finden');
    });
});
