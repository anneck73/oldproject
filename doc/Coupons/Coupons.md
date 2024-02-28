# Prototype "Coupons" Mangopay Implementation
###### Author: wizard@mealmatch.de | STATUS: WiP | VERSION: 0.2.15-dev

Status, quick brain dump.
This documentation is about ... 

----
## Mangopay Requirements (de)

Kurzgefasst, das Geld was den Gutscheinen entspricht muss irgendwo herkommen.

    Erstellt ein Wallet im Dashboard mit Daten von MealMatch und schickt mit die Wallet ID, ich lasse diese sofort validieren.
    Auf dieses Wallet könnt Ihr dann Geld überweisen, i.H.v. der Summe aller Gutscheine.
    Von Eurer Seite erstellt Ihr die Gutscheine mit den einzelnen Nummern/Codes oder wie ihr direkt die Gutscheine unterscheiden wollt.
    Dann zu unterscheiden, welche Gutscheine schon benutzt wurden oder gültig sind, diese Logik liegt bei Euch. Wir bieten dazu keine direkte Funktion.
    Wenn ein Gutschein benutzt wird:
        Prüft, dass im Gutschein-Wallet genügend Geld liegt.
        Warenkorb - Gutschein -->  neuer Warenkorb
        Payin mit der neuen Warenkorbsumme zum Empfängerwallet.
        Ergänzent einen TRANSFER vom Gutscheinwallet zum selben Emfpängerwallet machen.


Bitte sicherstellen, dass im Gutscheinwallet genügend Geld leigt um die Gutscheine zu decken.

**!!Wichtig!!** Die Gelder der Gutscheine dürfen gesetzlich nicht direkt in Geld ausbezahlt werden, und die dürfen auch auf 
keiner Weise ausserhalb MealMatch gültig sein. **Dies ist gesetzlich verboten.** 

## Coupons features

### Coupon Wallets (Mangopay)

In order to enable coupons. A mangopay wallet has to be pre-filled with money.

#### Mealmatch Coupon Wallet
A static configure single wallet, for mealmatch use (?)

CouponWallet(ID):
|-Name(string:120)
|-Description(text)
|-MangopayWalletID(int)
|-Status(string:120)

#### Event Coupon Wallet (?)

Can we have mangopay authorize wallets


#### Restaurant Coupon Wallet (?)


### Coupon Types

#### BaseCoupon 
BaseCoupon:
|-title
|-description
|-RRULE(string:120)
|-ValidFrom(datetime)
|-ValidUntil(datetime)
|-Relation zu CouponWallet

### UniqueCoupon

UniqueCoupon(ID) extends BaseCoupon:
|-Relation zu CouponCode(ID) 1-n
|-Value(5€|50€)

CouponCode(ID)
|-CodeString(???)
|-QRCodeString(???)
|-Relation zu Coupon(ID)
|-Relation zu User(ID) (nur vorhanden wenn verwendet|null)
|-Used(boolean)
|-CreatedDate
|-UsedDate

#### UseCase: Gutscheine für "Weihnachtsgurscheine für Markus freunde", 
10 stück a 20€ gültig bis 31.01.2019.
 
Wir erzeugen 10 Gutscheincodes 1 pro User, String(7).

### EventCoupon

EventCoupon(ID) extends BaseCoupon:
|-EventName(string:120)
|-Value(5€|50€)
|-Relation zu USER wie bei Gästen für "eingelöst durch user"?
|-Gutscheincode(CouponCode):"Halloweenspecial2018"
|-AvailableAmount (10)

#### UseCase: Gutscheine für AktionA, 100 stück a 5 €.

10 x 1 Coupon "Halloweenspecial2018" für AktionA gültig vom 29-31 Okt 2018. 


## Coupon Logik Neu
###  Coupon
|-ID, CouponCodeString, AvailableAmount (default=1,unique), Value, Currency, UsedAmount, Active
###  CouponCode
|-ID, @Coupon, @MM_USER(Guest), isClaimed, isRedeemed, isApplied
### MealTicket
|-ID, addCoupon(@CouponCode)

### Redeem
When a Coupon is redeemed the AvailableAmount is reduced by 1 (-1).
If a Coupon:AvailableAmount is 0, it can not be redeemed anymore.
The MealTicket get a new Coupon "added". The MealTicket is repsonsible for
handling a Coupon.


#### Sub-Sub-Section


## Implement available Mangopay functions
### Sub-Section
#### Sub-Sub-Section

use inside lists for more "sub-sections."

----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
