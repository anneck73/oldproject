# KYC Procedure
###### Author: dinesh@softsuave.com | STATUS: WiP | VERSION: WEBAPP-240-0-2-15

Status, quick brain dump.

This documentation is about how to use the kyc features based on the
type of user.
 

----

## Types of users
 * Guest
 * Host  
 * Admin 
####Guest
Documents a guest need to upload are only 'Identity proof'.
Documents to be used as identity proof based on country are,

* SEPA area : Id card (Front AND Back (Valid)) or driving license or passport.
* UK, USA and Canada: Driving licence or Passeport. 
* Other Nationalities : Passport is required.

####Host
Documents a host need to upload are (as legalPersonType we are following is 'Business', 
the host has to upload the following documents)  

* Identity Proof : Similar like identity proof of guest
* Registration Proof : Extract from the Company Register issued within the last three months.
* Articles of Association : Certified articles of association mentioned
  with business name, activity, registered address, shareholding…
* Shareholder Declaration : Information referring to the shareholder declaration,
  like the pdf provided in the link.

####Admin
Admin can able to view the kyc status of each user.
At the overall user kyc status, 3 status' are available
* No Document Submitted
* Pending : All the kyc documents for a particular user not get successfully validated
  (ie. May be in 'VALIDATION_ASKED' state or some documents are not yet uploaded).
* Approved : All the kyc documents for a particular user get successfully validated.

Where, each user has a link to display 'status' of each 'kyc document' uploaded by the user. 

### KYC document upload UI

The "KYC" has a link in the user profile dropdown from where it can be reached from anywhere in
the webapp. This link will be disabled for kyc-validated user. 


### KYC document upload Text/Internationalization
The text for internationalization is taken from mealmatch.{lang}.yml

###KYC document creation and kyc page upload
Inorder to upload a kyc document, we need to create a 'kyc document object'. 
For that object creation, we need document type and 
mangopay userId (assumed that mangopay userId is already generated while registration itself).
After creation, the document is in 'CREATED' state.

An user can upload more than one page for a kyc document object. 
After uploading the kyc page(s), the kyc document status should be 
updated to 'VALIDATION_ASKED'.

After validated by 'mangopay', the response may be 'VALIDATED' or 'REFUSED'
based on the deocument submitted. 

For handling the response, we are using hooks.
* KYC_SUCCEEDED : Update database about the response and notify the respective user 
  through system message.
* KYC_FAILED : Notify the respective user about the response through 'system message',
  then delete the database entry for the respective kyc document.
