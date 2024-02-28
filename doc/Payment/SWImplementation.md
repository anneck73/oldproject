
### Details about the technical implementation 

#### MealTicket

- PayIN:status
- PayOUT:status
- RedirectURL
- TransactionID's

#### Tasks

##### Create the PayOUT

- Add a property to the BaseMealTicket "payoutStatus"
- Add fluent getter/setter methods.
- Update the database schema (doctrine)
- Create a new Workflow transition for the Mealticket "payout_ticket", starting from places: "payed" and "used" and target a new place "payedout". 
- Add a logic into the payment flow to mark the mealticket as "payed out" when 
the mangopay "payout" has succeeded. Best using the Workflowcomponent.

##### Add PayPAl to PaymentOptions
- Add PayPal to the payment options
- If required update the payment profile or payment logic to cover special paypal payment cases.


