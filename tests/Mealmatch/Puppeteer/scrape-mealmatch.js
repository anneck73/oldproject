const puppeteer = require('puppeteer');

let scrape = async () => {
    const browser = await puppeteer.launch({headless: true});
    const page = await browser.newPage();

    viewport = await page.setViewport({
        width: 1920,
        height: 1080
    });


    await page.goto('https://mealmatch-stage.frb.io/');
    await page.waitFor(1000);
    await page.screenshot({path: 'puppeteerOutput/mealmatch-stage.png'});
    await page.emulateMedia('screen');
    await page.pdf({path: 'puppeteerOutput/medium.pdf', printBackground: true, format: 'A4', scale: 0.3});
    // Gather assets page urls for all the blockchains
    const assetUrls = await page.$$eval('a', assetLinks => assetLinks.map(link => link.href));
    // let i = 0;
    for (let assetsUrl of assetUrls) {
        await page.goto(assetsUrl);
        //
        // await page.screenshot({path: 'puppeteerOutput/mealmatch-stage-i-' + i + '.png'});
        // i++;
        //
        //
        //
        // Now collect all the ICO urls.
        const subURLS = await page.$$eval('a', links => links.map(link => link.href));
        let j = 0
        // Visit each anker one by one and collect the data.
        for (let subURL of subURLS) {
            if (!subURL.includes("mailto:info@mealmatch.de") || !subURL.includes("mailto:info@company.com" || !subURL.includes("mealmatch-stage.frb.io/bundles/mmwebfront/NutzungsbedingungenGmbHRestaurant.pdf"))) {

            await page.goto(subURL);

            await page.$$eval('a', links => links.map(link => link.href));
            await page.screenshot({path: 'puppeteerOutput/' + j + '.png'});
            await page.pdf({
                path: 'puppeteerOutput/medium' + j + '.pdf',
                printBackground: true,
                format: 'A4',
                scale: 0.3,
                displayHeaderFooter: true,
                // headerTemplate: '<b style="font-size:16px;width:100%;">' + subURL + '</b>',
                margin: {top: '50px', right: '10px', bottom: '50px', left: '10px',}
            });
            // TODO: Gather all the needed info like description etc here.
            j++;
            console.log(subURL.toString());

            }
        }
    }

    // await page.evaluate(() => {
    //
    //     document.querySelectorAll("#mm-navbar-collapse > ul > li:nth-child(2) > a").forEach(function (s) {
    //         let pagescreenshot = async () => {
    //             await page.screenshot({path: 'puppeteerOutput/mealmatch-stage ' + s + '.png'});
    //         }
    //     })
    // });

    browser.close();

};


scrape().then((value) => {
    console.log(value); // Success!
});