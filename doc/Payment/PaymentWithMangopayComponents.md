# Payment with Mangopay
###### Author: wizard@mealmatch.de | STATUS: WiP | VERSION: 0.2.x

Status, quick brain dump.
This documentation is a start for developers and everyone working with the payment components.

----

## Overview 
The Payment process has 3 "phases":
- Pre-Payment
- Payment
- Post-Payment

**Pre-Payment:** Covers everything that a guest or host of a meal needs to do with the WebApp in order for a his WebApp-User to "qualify" for the actual payment. 
For example: Mangopay requires a CountryOfOrigin value, and we take that Country value from the User-Profile. The
user is required to set a Country value in his User-Profile before he can start using the next phase: "Payment".   

**Payment:** The actual payment execution logic. This involves mostly the guest of a meal and re-directs the WebApp-User 
to 3rdParty websites or Mangopay.

**Post-Payment:** Anything that happens after a payment as been processed. Disputes of any kind. 


### Pre-Payment

Pre-Payment components cover the requirements that MUST be met before any user is able to use the payment.
In other words: Before anyone can pay for a meal and become a guest, these components cover all required
data entry forms and logical components nessesarry to support the qualification of the user for the payment. 

#### User payment profile data entities 

Each user has a "PaymentProfile" with the following data:
- MangopayID
- MangopayWalletID
- PaymentOption
- PayPalEmail
- IBAN
- BIC
- Address to be used for payment.

#### User payment profile UI

The "PaymentProfile" has a link in the user dropdown from where it can be reached, from anywhere in
the webapp.

The payment profile UI support the data entry for all required data entities.

#### User payment profile Text/Internationalization
The text for internationalization is taken from mealmatch.{lang}.yml

#### User payment profile data validation
TBA ?!? this has to be done but how? And why and what?

These are the minimum requirements: 
 
        $userNatural->Email = $user->getEmail();
        $userNatural->FirstName = $user->getUsername();
        $userNatural->LastName = $user->getUsername();
        $userNatural->PersonType = 'NATURAL';
        $userNatural->Birthday = $user->getProfile()->getBirthday()->getTimestamp();
        $userNatural->Nationality = $user->getProfile()->getCountry();
        $userNatural->CountryOfResidence = $user->getProfile()->getCountry(); 


#### Mangopay user creation
In order to execute mangopay "PayIN/OUT" transfers every user required a MangopayID. 
Atm we create the user "on-the-fly" during the payment execution.

#### Mangopay user-wallet creation
We require a Wallet for every user, atm we create that wallet "on-the-fly" during payment.


### Payment
In order for PayIN+PayOUT to work HOST & GUEST require useable Mangopay "Accounts". 
A Guest requires a MangopayID & MangopayWalletID
A Host additionaly requires a BankAccount!

ATM we require that the HOST is correctly setup by mealmatch sales before he can receive a PayOUT.


#### PaymentOption: CreditCard
Status: User can pay, but the credit card does not get credited? 
#### PaymentOption: GIROPAY
Status: Payin Works
#### PaymentOption: SOFORT
Status: Payin Works 
#### PaymentOption: PayPal
Needs to be done, not started yet.

### Post-Payment
ATM we have no post-payment! Everyting required needs to be done via the mangopay dashboard.