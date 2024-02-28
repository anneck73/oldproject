# Mealmatch GEO Address resolution feature
###### Author: wizard@mealmatch.de | STATUS: WiP | VERSION: 0.2.19-dev

Status, quick brain dump.
This documentation is about how the geo address lookup is done using the Mealmatch service classes.

----

### GEO Address Data layer
Every entity holding geo data is required to provide a method which connects the geo coordinates to a location string.
This string is then associated to the geo coordinates determined during the geo data resolution process.

This is the GeoCodeable interface. 
###### The GeoCodeable interface
        interface GeoCodeable extends EntityData
        {
            public function getLocationString(): string;
        }

Every XYZ*Address* entity class implements the GeoCodeable interface.

### MealAddress 
The MealAddress is associated to every **HomeMeal**.

### RestaurantAddress
Todo: The RestaurantAddress is associated to every **ProMeal** (Restaurant Meals)
WiP with v0.2.19-dev

### SameAddress
The SameAddress is a mealmatch utility entity to "cache" the processing of the same location string.

## GEO Address resolution layer

The service class GeoAddressService is exsposing the GeoAddressServiceData to the implementing code in order to access 
the results of the service operation calls. Every public method which is part of the geo address resolution feature uses
and returns GeoAddressServiceData objects.

In order to use the GeoAddressService class you have to create GeoAddressServiceData or use one of the presented public 
methods.

### Main service method 

    geocode(string $locationString, GeoAddressServiceData $serviceData)
   
This method will update the **serviceData** variable with the result of the geo address lookup.

* If the geo address lookup has been successfully the **serviceData->isValid()** will return true.
* If the geo address lookup failed the **serviceData->isValid()** will return false.

The geo lookup error messages are contained in **serviceData->getErrors()**.
 
### Public service usage
The GeoAddress Service is used in the following sequence:

1) Inject Entity
1) Use any Service Method
1) Get Results, Errors, Updated entity data

Use specific methods to inject specific entity data.



#### Sub-Sub-Section

use inside lists for more "sub-sections."

----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
