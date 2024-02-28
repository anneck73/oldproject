## Mealmatch Widgets
VERSION: 0.2.x
AUTHOR(s): wizard@mealmatch.de
Last Update: Donnerstag, 29. März 2018 10:32 

### Synopsis
Widgets sind View-Komponenten die über zusätzliche, domain spezifische (Mealmatch) logik verfügen. Beispiel, die JSON-LD Structur
für ein Meal ausgeben. 

### TWIG Macros als Widgets
Alle Mealmatch spezifischen TWIG macros befinden sich in der Datei views/Widgets/mealmatch.macros.html.twig

Sollten sich noch weitere, domain spezifische oder allgemeine (Util) Marko Sammlungen finden, so sollten diese in einer domain spezifischen macro datei gesammelt werden. Beispiel: responsivness.macros.html.twig

#### Einbinden / Verwenden von Widgets

In den TWIG templates in denen ein Widget verwendet werden muss die entsprechende macros Datei über ein IMPORT eingebunden werden:

	{# Importing Macros/Widgets Collection #}
	{% import '@MMWebFront/Widgets/mealmatch.macros.html.twig' as widgets %}
	
Danach können alle Macros/Widgets im template verwendet werden, z.B. so:

    {# Using widget from imported collection #}
    {{ widgets.proMealEventJSONLD(proMeal) }}


## Widgets

### Widget: proMealEventJSONLD

#### Synopsis
Erzeugt ein vollständiges JSONLD "Event" HTML Element (Structured Data).

    {# Verarbeitet den 1 parameter als eine ProMeal Entity #}
    {{ widgets.proMealEventJSONLD(proMeal) }}

Es findet keinerlei "Überprüfung" statt ob es sich denn auch um ein ProMeal handelt.

Entity ist Mealmatch\ApiBundle\Entity\Meal\ProMeal.

***

### Widget: breadcrumb

#### Synopsis
Tuts so noch nicht, ist in Arbeit.

***

###### Mealmatch GMBH - 2018; Autor: wizard@mealmatch.de