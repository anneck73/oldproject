//
//
// before(async () => {
//     global.puppeteer = require('puppeteer');
//     global.devices = require('puppeteer/DeviceDescriptors'); // Click while holding STRG on DeviceDescriptors to see devices
// //const ipadLandscape = devices['iPad landscape']
//
//     global.chai = require('chai');
//     global.assert = chai.assert;
//     global.expect = chai.expect;
//     chai.should();
//     process.setMaxListeners(Infinity);
//
//     // Set headless to false if you wan't to see the browser
//     global.browser = await puppeteer.launch({headless: false, args: ['--no-sandbox', '--disable-setuid-sandbox', '--start-maximized', '--window-size=1855,1060']});
//     global.page = await browser.newPage();
//     global.baseURL = 'https://mealmatch-dev.frb.io';
//     global.navigationPromise = page.waitForNavigation();
//
//
//     global.viewport = await page.setViewport({
//         width: 1855,
//         height: 1060
//     });
//
//     // await page.emulate(ipadLandscape)
// });
//
// after(async  () => {
//     await page.close();
//     await browser.close();
// });