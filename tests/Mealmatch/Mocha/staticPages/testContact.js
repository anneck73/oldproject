require('../global-before-env-driven');

describe('Test contact page', function () {

    it('Contact page should be here', async () => {
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('body > div.content > footer > div.primary-footer > div:nth-child(3) > div > div >' +
            ' div:nth-child(1) > div > a');
        await page.click('body > div.content > footer > div.primary-footer > div:nth-child(3) > div > div >' +
            ' div:nth-child(1) > div > a');
        await page.waitForSelector('body > div.content > section > div.header-container.findmeal-banner > div > h3'); // Überschrift
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Kontaktformular';
        expect(bodyHTML).to.contain(searchString,'Konnte den gesuchten String "' + searchString + '" nicht ' +
            'auf der Kontakt-Seite finden');
    });
});
