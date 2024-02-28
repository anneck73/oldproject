npm install @symfony/webpack-encore --save-dev

### MMWebFrontBundle

The main "webfront" for the mealmatch.de domain.

The view is generated using TWIG templates in MMWebFrontBundle/views/.

All "layout" views (exceptions are named) use the twbs3-base.html.twig as their root TWIG template and inherit the basic
definitions about JavaScript and Stylesheets.

### TWBS3-Based layouts 

TWBS3-Base "layout" twig file defines the following TWIG Blocks to be overriden/used.

#### Block:flashbag
Div container for flashbag/toaster messages on all pages.

#### Block:navbar
Div container for navigation on all pages. (includes login)

#### Block:body
Div container for the main content (override/used by layout-templates with design)

#### Block:footer
Div container for the footer on all pages.

#### 3rd Party Stylesheets

The TWIG template twbs3-base.html.twig integrates the following libraries:

bootstrap.min.css
bundles/mmwebfront/css/bootstrap-theme.min.css
bootstrap-datetimepicker.css
bootstrap-tagsinput-typeahead.css
bootstrap-tagsinput.css
bootstrap-dialog.css
datepicker.css
fullcalendar.min.css
material-icons.css
font-awesome.css
simple-line-icons.scss




### Templates using twbs3-base
### Layout:Empty
File: views/Layout/empty.html.twig

The "empty" Layout overwrites the Block:Body, no specific design, just header and footer.
### Layoutlandingpage
#### main
#### search
#### simple
#### startpage

