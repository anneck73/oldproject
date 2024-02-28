// test command: mocha test/api-test.js --config=/path/to/config.json --reporter spec
let path = require('path');
let fs = require('fs');
let assert = require('assert');
let argv = require('optimist').demand('mmConfig').argv;
let configFilePath = argv.mmConfig;
assert.ok(fs.existsSync(configFilePath), 'config file not found at path: ' + configFilePath);
let config = require('nconf').env().argv().file({file: configFilePath});
let targetHost = config.get('targetHost');
let mmHostConfigs = config.get('host-config');
let mmHostConfig = mmHostConfigs[targetHost];
let hostConfigBaseUrl = mmHostConfig.baseURL;
let hostConfigHeadless = mmHostConfig.headless;

console.log('Mealmatch Configuration:');
console.log('BaseUrl: ' + hostConfigBaseUrl);
console.log('Headless: ' + hostConfigHeadless);

before(async () => {
    global.puppeteer = require('puppeteer');
    global.devices = require('puppeteer/DeviceDescriptors'); // Click while holding STRG on DeviceDescriptors to see devices
//const ipadLandscape = devices['iPad landscape']

    global.chai = require('chai');
    global.assert = chai.assert;
    global.expect = chai.expect;
    chai.should();
    process.setMaxListeners(Infinity);

    // Set headless to false if you wan't to see the browser
    global.browser = await puppeteer.launch({headless: hostConfigHeadless, args: ['--no-sandbox', '--disable-setuid-sandbox', '--start-maximized', '--window-size=1920,1080']});
    global.page = await browser.newPage();
    global.baseURL = hostConfigBaseUrl;
    global.navigationPromise = page.waitForNavigation();


    global.viewport = await page.setViewport({
        width: 1920,
        height: 1080
    });

    // await page.emulate(ipadLandscape)
});

after(async () => {
    await page.close();
    await browser.close();
});