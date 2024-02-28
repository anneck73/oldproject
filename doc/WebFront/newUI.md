# NewUI 
###### Author: dinesh@softsuave.com | STATUS: WiP | VERSION: 0.2.19

# Google ReCaptcha
###### Author: wizard@mealmatch.de | STATUS: WiP | VERSION: *DEV

Quick dump of secret keys :) 

----

## Keys für mealmatch.de (v3)
Webseitenschlüssel: 6LfrBrIUAAAAAGzQGYdHdLaTdeC0_U917OfNzjZM
Geheim: 6LfrBrIUAAAAAP2FJdb_zkQxjl-J4LmzX7T-CXA7

## Keys for v2 mealmatch contact
Webseitenschlüssel: 6LcnDLIUAAAAACYawgYInf1_9LE2gkAmp04GgZU_
Geheim: 6LcnDLIUAAAAACYawgYInf1_9LE2gkAmp04GgZU_


----

## About new UI
### Template hierarchy
Template hierarchy of new UI is specified along with their purposes as follow

###newUI-Base.html.twig
 
This is the main base layout for the project. Templates extending this layout are,

a) Layouts/main_layout.html.twig

b) Layouts/mealmanager_main_layout.html.twig

c) Layouts/simple.html.twig

d) Layouts/search-layout.html.twig

e) Layouts/simple_ui_a.html.twig

f) index.html.twig (home page)


####a) Layouts/main_layout.html.twig

This layout is used for the pages with left_icon and the main_content vertically parellel
to the left_icon.

 * all static pages except about.html.twig(StaticPages/)
 * contactForm.html.twig and inviteForm.html.twig

####b) Layouts/mealmanager_main_layout.html.twig

This layout is preferably used for the pages with left_icon, main_header_title and the 
main_header_action button which are at the top of the main_content.

* JoinRequest/index.html.twig
* Restaurant/index.html.twig
* MealTicket/show.html.twig
* HomeMeal/managerEdit.html.twig,managerShow.html.twig, index.html.twig
* ProMeal/managerEdit.html.twig,managerShow.html.twig, index.html.twig
* City/meals.index.html.twig
* Contact/contactFormSend.html.twig, inviteFormSend.html.twig
* Meals/meals.country.index.html.twig

####c) Layouts/simple.html.twig

Pages which requires plain body along with header image are using this layout.
 
* RestaurantProfile/public.html.twig
* UserProfile/public.html.twig
* about.html.twig

####d) Layouts/search-layout.html.twig

Pages which are displaying search result along with map are using this layout.

* Search/search_do.html.twig, search_do_pro.html.twig, search_do_home.html.twig

####e) Layouts/simple_ui_a.html.twig

Pages which are requiring plain body along with header image are using this layout.
Also this layout uses a twig variable 'page', which is used to specify html classes
(restaurant-profile-page, user-profilepage) with different css styling properties.

* RestaurantProfile/manager.html.twig ({{ page }} value => 'restaurant-profile-page')
* UserProfile/manager.html.twig ({{ page }} value => 'user-profilepage')
* register.html.twig ({{ page }} value => 'restaurant-profile-page')

###Carousel

Wherever list of guests are displayed in 'Mealmatch', carousels are used there.

eg.  
At restaurant details page(/RestaurantProfile/public.html.twig), if more than 3 guests are there
for that restaurant, then casousel is automatically get added there.

At '/UIPublicBundle/Resources/public/js/mealManager_js.js', added javascript code to set the 
carousel interval to false, which prevent the carousel from auto sliding.


----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
