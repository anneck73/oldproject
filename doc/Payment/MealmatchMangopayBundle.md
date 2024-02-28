# Mealmatch Mangopay Bundle
###### Author: wizard@mealmatch.de | STATUS: WiP | VERSION: 0.2.19-dev

The mangopay bundle is a layer between Mealmatch business logik and the mangopay
payment API. It is repsonsible for 
* the communication with the remote mangopay API
* the processing of Mealmatch data to create mangoapy API objects

## Workflow / Usage

Every Service class interface exposes 3 kinds of functionality to the implementing developer.

* **Create** a MangopayApi **object**
* **Call** a MangopayApi **method** with the **created** MangopayApi **object**
* **Call** a MangopayApi **method** with a ResourceID to return a MangopayApi **object** or a result msg. 

The services follow these naming conventions:
* createStuff():MangopayObj
* doSomethingWith(MangopayObj):MangopayObj
* get/check/setStuff(int resourceID):[result=>'', errorMsg=>'', resultClass=>'']. 

### Step 1: Create mangopay API object

The implementing class should first create the mangopay API object, for the next step to 'consume'. These methods are names with the prefix 'create'


### Step 2: Call remote mangopay API with created object

The implementing class takes the result of the create method from step#1 and uses it
to call the 2nd method on the service named with do***.

## Payment Data Layer (createStuff)

The methods with the prefix 'create' are repsonsible for consuming Mealmatch data objects (for example MMUser) and 
create mangopay APO object like _MangoPay\UserNatural_.
  
            
## Communication Layer (doSomethingWithStuff)

The methods with the prefix 'do' are responsible for 

----

## Validation

The services do not validate any data!
All service methods expect the data to be valid!
Data validation of Mealmatch business object is handled 

## Section Title
### Sub-Section
#### Sub-Sub-Section

use inside lists for more "sub-sections."

----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
