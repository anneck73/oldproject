require('../global-before-env-driven');

describe('Test Mealmatch-Blog page', function () {

    it('Mealmatch-Blog should be here', async () => {
        await page.goto(baseURL, {timeout: 0, waitUntil: 'domcontentloaded'});
        // screenshot = await page.screenshot({path: 'puppeteerOutput/mealmatch-local.png'});

        await page.waitForSelector('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div.col-md-5th-1.col-sm-4.col-md-offset-0 > div > ul > li:nth-child(4) > a');
        await page.click('body > div.content > footer > div.primary-footer > div:nth-child(1) > div > div >' +
            ' div.col-md-5th-1.col-sm-4.col-md-offset-0 > div > ul > li:nth-child(4) > a');
        await page.waitForSelector('#masthead > div.custom-header > div.site-branding > div > div > h1 > a'); // Überschrift
        let bodyHTML = await page.evaluate(() => document.body.innerHTML);
        let searchString = 'Social-Dining BLOG | Mealmatch';
        expect(bodyHTML).to.contain(searchString, 'Konnte den gesuchten String "' + searchString + '" nicht ' +
            'auf der Blog-Seite finden');
    });
});
