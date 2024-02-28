# Nice code snippets that should ne be forgotten

## Find mealId and mealOfferId in JS filled search response
`preg_match('~(mealticket\\\/)([0-9]*)(\\\/)([0-9]*)~', $crawler->text(), $matches);`

`$mealId = $matches[2];`

`$mealOfferId = $matches[4];`

## Cypress Reporting
To generate a single JSON file from multiple JSON files:

` npx mochawesome-merge --reportDir build/mocha/JSON > build/mocha/html/mochawesome.json`

To create a nice HTML with mochawesome:

` npx mochawesome-report-generator --reportDir build/mocha/html/ build/mocha/html/mochawesome.json`
