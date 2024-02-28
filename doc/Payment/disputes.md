#Disputes 
###### Author: dinesh@softsuave.com | STATUS: WiP | VERSION: 0.2.16

This documentation is about dispute hooks handling.

----
###Disputes Implementation
For managing disputes, we are using mangopay dashboard.

From code base, we are handling only the disputes hooks.

###Dispute Hooks
Dispute hooks are handled mainly to notify 'Mealmatch admin' about the status change in
each disputes through email, as Mangopay send notification email only if a dispute get created.


#####DisputeCreated hook
This hook event get raised when a dispute get created. Eventhough Mangopay send notification 
email to us(Mealmatch), we also handling that event due to some preventive reasons. 

#####DisputeActionRequired hook
This hook event get raised when a dispute get created with a type 'CONTESTABLE'. 
If a dispute is CONTESTABLE, you can either close or contest the dispute from mangopay dashboard
under 'Disputes' tab.

#####DisputeDocumentSucceeded hook
This hook event get raised when a 'dispute evidence document' submitted by us get succeeded
(ie. Successfully verified by mangopay).

[Disputes evidence document is submitted by us when we want to 'contest' a 'CONTESTABLE' dispute.]

#####DisputeDocumentFailed hook
This hook event get raised when a 'dispute evidence document' submitted by us get failed(Refused by Mangopay).
In this case the Refused Reason Message is also send along with the email.

#####DisputeSentToBank hook
This hook event get raised when a dispute is sent to the respective bank after the 
successful verification of 'dispute evidence document' by  Mangopay.

#####DisputeFurtherActionRequired hook
After 'dispute evidence document' sent to bank, that bank will review those documents. Then 
it may require further evidence documents for reviewing. At that moment 'DisputeFurtherActionRequired'
hook will get raised. 

Then the dispute get reopened by Mangopay, then we have to submit further evidences.
See status message of that dispute for more info.  

#####DisputeClosed hook
This hook event get raised when a dispute is closed. Reason for closing that dispute can be
get from the result message of that dispute.

Possible reasons for closing a dispute, 
* We won/lost the dispute
* That dispute is 'NOT CONTESTABLE'(ie.dispute get lost just after being created and no further action 
  is possible from our side)

####Dispute Hook URL addition/updation on Mangopay dashboard

Addition or updation of dispute hook url on Mangopay dashboard based on the environment 
can be achieved through adding the dispute hook api path in '/mmwebapp/hooksInitialization.php'.
(eg) /u/dispute/disputehooks

#### Links / References

* REFERENCE_HERE: [Mangpay dispute doc](https://docs.mangopay.com/endpoints/v2.01/disputes#e176_the-dispute-object)

