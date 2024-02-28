Mealmatch WebApp - MMWebFront Bundle Dokumentation
Status: in Arbeit
Version: 0.2.x

#MMWebFrontBundle

Das WebFront Bundle ist für die grafische Darstellung im Browser verwantwortlich. Es verbindet die Daten aus der Mealmatch Business Logik (ApiBundle) mit Web-Resourcen (HTML,CSS,JS,GFX) zur verwendung im Browsern.

## Verzeichnisstruktur Übersicht
src/MMWebFrontBundle/Resources/
├── config 
├── public
│   ├── css
│   ├── fonts
│   ├── images
│   ├── js
│   ├── sass
│   ├── scss
├── translations
└── views

## **config/**
Hier befinden sich die Symfony spezifischen konfigurationen.

## **public/**
Alle Verzeichnisse und Dateien in diesem Verzeichnis werden verarbeitet und in das web/ Verzeichnis exportiert. 

*"Views"* verbinden diese "Resourcen" miteinander (css,js,etc).

### Views

In diesem Verzeichnis befinden sich die TWIG Dateien zum "zusammenbauen" einzelner Teile bzw. der gesamten WebApp.

#### Verzeichnise und Context
└── views
    ├── City
    ├── Contact
    ├── Default
    ├── Demo
    ├── footer.html.twig
    ├── Form
    ├── HomeMeal
    ├── JoinRequest
    ├── Layout
    ├── lp_navbar.html.twig
    ├── MealEvent
    ├── MealOffer
    ├── Meals
    ├── MealTicket
    ├── Modals
    ├── navbar.html.twig
    ├── NavBars
    ├── offline.html.twig
    ├── Partials
    ├── PDF
    ├── ProMeal
    ├── Restaurant
    ├── RestaurantProfile
    ├── Search
    ├── sitemap.xml.twig
    ├── TableTopic
    ├── twbs3-base.html.twig
    ├── UserProfile
    ├── Variations
    └── Widgets
