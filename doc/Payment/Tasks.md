## (outdated see JIRA!!!)


### Pay-IN:CreditCard (bug)
Problem, the amount does not get credited. 

### Pay-IN:PayPal (new task)
ToDo

#### Hook: Pay-IN: created
The Hook/Event should probably just produce a log message on our side, 
since we already track "created" as part of our mealticket-action logic.
+ Notification to User-System-Inbox

#### Hook: Pay-IN: success
This Hook/Event should verify the mealticket actions logic, check if guest is in meal and
other stuff? 
+ Notification to User-System-Inbox

#### Hook: Pay-IN: failed
This Hook/Event MUST set mealticket payment status to FAILED.
+ Notification to User-System-Inbox

### Pay-OUT: Bankaccount Restaurant (new task)

#### Hook: Pay-OUT: created
+ Notification to Restaurant-User-System-Inbox

#### Hook: Pay-OUT: success
+ Notification to Restaurant-User-System-Inbox

#### Hook: Pay-OUT: failed
+ Notification to Restaurant-User-System-Inbox

### Mealmatch Logic: Pay-OUT:created
### Mealmatch Logic: Pay-OUT:success
### Mealmatch Logic: Pay-OUT:failed

#### Hook:Pay-OUT:created
#### Hook:Pay-OUT:success
#### Hook:Pay-OUT:failed

### PaymentProfile UI Dialog for Mangopay Create 
- User
- Wallet
- Restaurant Bankaccount!

### New Restaurant UI for Mangopay Payout Management
- UI Input for all required mangopay data
- Mealmatch logic to manage Mangopay data (update,delete)
- Payout Button

### Pay-OUT-OnDemand (new Task)

### Disputes (new Tasks)

### Edge Cases

#### If a guest has payed for the meal but the restaurant owner does not have a bankaccount
the money can not be transferred. What if the guest is sitting in the restaurant,
pre-paid for the meal but the restaurant owner did not get the money yet?

- Send the restaurant owner notifications in the system-in-box?!
- Is this our problem? 
- Do we need disputes for that?