# TWIG Macros
###### Author: YOUR_NAME@mealmatch.de | STATUS: WiP | VERSION: 0.0.1


Status, quick brain dump.
This documentation is about ... 

----

## Global Macros

The file [mealmatch.macros.html.twig](https://bitbucket.org/mealmatch/mmwebapp/src/0.2.15-dev/src/MMWebFrontBundle/Resources/views/Widgets/mealmatch.macros.html.twig") 
contains global macros for use on every page.

To use it 

### Mealcards Horizontal

Displays meals in cards with a teaser text on top.

#### Usage
Wie und wann wird das macro verwendet (derzeit)

    use: mealcardsHorizontal(meals, teaserText)

##### Im WebFront Bundle
src/MMWebFrontBundle/Resources/views/**

* Startpage (b) - Variations/Default/b/index.html.twig
* ...

##### Woanders ? 


#### Example

    var s = "JavaScript syntax highlighting";
    alert(s);
    
###UIProfileBundle macros
The file (src/Mealmatch/UIPublicBundle/Resources/views/Widgets/mealmatch.macros.html.twig)) 
contains macros for mealcard. 

use: **mealcardsHorizontal(meals, teaserText)**

To maintain the grid-row view of meal cards(if size of any meal card get increased
due to the size of offer name), coupled each two mealcards into a div.

  


#### Notes / Important stuff


----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
