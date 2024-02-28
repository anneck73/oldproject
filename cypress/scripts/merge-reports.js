const cypress = require('cypress')
const marge = require('mochawesome-report-generator')
const { merge } = require('mochawesome-merge')

cypress.run().then(

    () => {
        generateReport()
    },
    error => {
        generateReport()
        console.error(error)
        process.exit(1)
    }
)

function generateReport(options) {
    return merge(options).then(report => marge.create(report, options))
}



// const cypress = require('cypress')
// const fse = require('fs-extra')
// const { merge } = require('mochawesome-merge')
// const generator = require('mochawesome-report-generator')
//
//
// async function runTests() {
//     await fse.remove('build/mocha/html/') // remove the report folder
//     const { totalFailed } = await cypress.run({
//         reporter: 'mocha-multi-reporters',
//         reporterOptions: {
//             reporterEnabled: 'list,JSON,mochawesome,mocha-junit-reporter',
//             mochaJunitReporterReporterOptions: {
//                 mochaFile: 'build/test-reports/mocha/xml/results.xml'
//             },
//             overwrite: false,
//             html: false,
//             json: true,
//             showPassed: true,
//             reportDir: 'build/mocha/html/',
//             reportFilename: 'result.html',
//         },
//         browser: 'chrome',
//         config: {
//             projectId: 'mmwebapp',
//             baseUrl: 'http://mealmatch-dev.frb.io',
//             fixturesFolder: 'tests/Mealmatch/cypress/fixtures/',
//             integrationFolder: 'tests/Mealmatch/cypress/integration/',
//             viewportWidth: 1855,
//             viewportHeight: 1060,
//             defaultCommandTimeout: 10000,
//             chromeWebSecurity: false,
//         },
//         env: {
//             foo: 'bar',
//             baz: 'quux',
//         }
//     }) // get the number of failed tests
//     const jsonReport = await merge() // generate JSON report
//     await generator.create(jsonReport)
//
//     process.exit(totalFailed) // exit with the number of failed tests
// }
//
// runTests()