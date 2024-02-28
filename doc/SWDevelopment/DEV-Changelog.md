# Development Changelog
###### Author: wizard@mealmatch.de | STATUS: in development | VERSION: 0.2.17-dev

This changelog is for the development team, e.g. the developers who continuously update the software. It should give any
developer a crisp and clear overview about the most important last/breaking changes and planned future development. 
(WiP) 

## andre/0.2.17-RC1-LogicUpdate (from 0.2.17-RC1)

### MealTicketActions: payTicket update

Now a new logic checks the guest payment profile ->isComplete() method
to only check for mangopayID and mangopayWalletID. Both are required
for the guest to be able to pay a ticket.

As before, if the paymentProfile is not complete we try to auto-create
a NaturalUser and a Wallet. "on the fly".

### New methods in service RestaurantService

#### isPaymentProfilePayoutValid
Checks payment profile for valid mangopay ids

#### isRestaurantProfileValid
Checks restaurant profile for completion (data)

#### isUserProfileValid
Checks user profile for completion (data)

#### getOrCreateMangopayIDs(MMUser user):bool
Creates MangopayLegalUser, MangopayWallet and MangopayBankAccount.



### Updates to RestaurantProfileManagerController

The RestaurantProfileManagerController uses the api.restaurant.service (RestaurantService) to validate UserProfile, 
RestaurantProfile, PaymentProfile AND to create MangopayID's for the Restaurant.

### Role Based validation of profiles in UserProfileManagerController

The UserProfileManagerController now validates the UserProfile regarding all fields required for a specific ROLE.
In order to qualify the role: ROLE_RESTAURANT_USER the condition:

            null === $userProfile->getFirstName() or
            null === $userProfile->getLastName() or
            null === $userProfile->getCountry() or
            null === $userProfile->getAreaCode() or
            null === $userProfile->getCity() or
            null === $userProfile->getNationality() or
            null === $userProfile->getAddressLine1() or
            null === $userProfile->getBirthday() or
            null === $userProfile->getGender() 
has to be met.

In order to qualify for the role: ROLE_HOME_USER the condition:

            null === $userProfile->getFirstName() or
            null === $userProfile->getLastName() or
            null === $userProfile->getNationality() or
            null === $userProfile->getBirthday() or
            null === $userProfile->getGender()

has to be met.

If the validation fails, a toaster message is displayed to the current user.


## feature/WEBAPP-263-TestWriting-0.2.17-dev (from 0.2.17-dev)
Changed default variant to b in parameters_pipeline.yml. Found out, that the response from the geocoder has changed, 
look at Webapp-286. Because of that the sublocality is now changed to Rodenkirchen in the SearchserviceTest.

Switched security channel to http because https gaves us strange redirect messages. ;)

There was many more things that i can't remember now. Look at the commit messages :-P

----
## andre/0.2.17-Encore-Updates (from andre/0.2.16-Coupons)

#### createdDEV.sh

The helper is no longer calling "composer install"!

If an update from GIT changes the PHP classpath/classmap you have to run composer install once manually!

After that createDEV.sh can be called consecutively again. 


#### Webpack + Encore + Fortrabbit

With [https://bitbucket.org/mealmatch/mmwebapp/pull-requests/206](PR#206) merged the following configuration is in 
place.


##### Webpack Encore Outputpath

In webpack.config.js the output path is set to web/static + env, where env is either 'dev' or 'prod'.

##### Webpack Encore Development (dev)

This is automatically called from composer script 'install-dev' during createDEV.sh.

The yarn command ```#> yarn encore dev``` creates the web/static/dev/* assets.


##### Webpack Encore Production (prod)

The yarn command ```#> yarn encore prod``` creates the web/static/prod/* assets.

##### Fortrabbit

Deployment to fortrabbit is configured via ```fortrabbit.yml``` and now includes web/static/ as a **sustained** 
directory. With this chang ```rsync``` to @deploy.eu2.frbit.com works like a charm.

See [Install Symfony 4 / Deploying assets](https://help.fortrabbit.com/install-symfony-4-uni)

Use the new helper scripts bin/deployUIToMealmatch-DEV|STAGE.sh. 

## andre/0.2.16-Webpack-up (from andre/0.2.16-Coupons)

#### EasyAdmin /admin 

Updated MealTicket views
Added RedeemRequest to views

#### Database Updates

Requesting the couponCode async via cron task requires the request to be persistet on the server.
The "redeem request" takes a couponCode and validates it.
A BaseMealTicket now hase 0-"many" redeemRequests.

##### SQL executed

     CREATE TABLE redeem_request (id INT AUTO_INCREMENT NOT NULL, meal_ticket_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, hash VARCHAR(190) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_73AE1223D1B862B8 (hash), INDEX IDX_73AE1223CE6F45D9 (meal_ticket_id), INDEX IDX_73AE1223B03A8386 (created_by_id), INDEX IDX_73AE1223896DBBDE (updated_by_id), INDEX IDX_73AE1223C76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8mb4 COLLATE UTF8mb4_unicode_ci ENGINE = InnoDB;
     ALTER TABLE redeem_request ADD CONSTRAINT FK_73AE1223CE6F45D9 FOREIGN KEY (meal_ticket_id) REFERENCES base_meal_ticket (id);
     ALTER TABLE redeem_request ADD CONSTRAINT FK_73AE1223B03A8386 FOREIGN KEY (created_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
     ALTER TABLE redeem_request ADD CONSTRAINT FK_73AE1223896DBBDE FOREIGN KEY (updated_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;
     ALTER TABLE redeem_request ADD CONSTRAINT FK_73AE1223C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES mm_user (id) ON DELETE SET NULL;



----
## andre/0.2.16-Webpack-up (from andre/0.2.16-Coupons)

Trying / Starting transition to webpack + encore.
See new helper scripts:

bin/setupUI.sh --- to be executed once to get npm/yarn working (installs all dependencies)
bin/build.UI.sh --- replacement for assetic:dump, builds the "ui" inside web/**

This is heavy work in progress.

----
## andre/0.2.16-Coupons (from 0.2.16-dev)

### BC (Breaking Changes)
Database update required.

#### Database Updates
Coupons and CouponCodes are now part of the DB.

#### ClassMap Updates
New Bundle Mealmatch\CouponBundle\

### Additions
#### Coupons
#### Public Coupon Service

##### claim coupon

##### redeem coupon

##### process redeemer command
for cron:create to be run every minute. '* * * * *'

### Libraries/3rd-Party
Removing composer bundles:
    "omnipay/common": "3.*@dev",
    "omnipay/paypal": "3.*@dev",
    "openbuildings/paypal": "^0.4.2",

Composer updates!!!

---

Loading composer repositories with package information                                                                                                                                                             Updating dependencies (including require-dev)         Package operations: 1 install, 46 updates, 1 removal
  - Removing phpfastcache/riak-client (3.4.3)
  - Updating psr/log (1.0.2 => 1.1.0): Loading from cache
  - Updating monolog/monolog (1.23.0 => 1.24.0): Loading from cache
  - Updating symfony/polyfill-mbstring (v1.9.0 => v1.10.0): Loading from cache
  - Updating symfony/polyfill-ctype (v1.9.0 => v1.10.0): Loading from cache
  - Updating symfony/polyfill-php70 (v1.9.0 => v1.10.0): Loading from cache
  - Updating symfony/polyfill-util (v1.9.0 => v1.10.0): Loading from cache
  - Updating symfony/polyfill-php56 (v1.9.0 => v1.10.0): Loading from cache
  - Updating symfony/symfony (v3.4.17 => v3.4.20): Loading from cache
  - Updating symfony/polyfill-intl-icu (v1.9.0 => v1.10.0): Loading from cache
  - Updating symfony/polyfill-apcu (v1.9.0 => v1.10.0): Loading from cache
  - Updating doctrine/persistence (v1.0.1 => v1.1.0): Loading from cache
  - Updating doctrine/common (v2.9.0 => v2.10.0): Loading from cache
  - Updating symfony/monolog-bundle (v3.3.0 => v3.3.1): Loading from cache
  - Updating sensiolabs/security-checker (v4.1.8 => v5.0.2): Loading from cache
  - Updating sensio/distribution-bundle (v5.0.22 => v5.0.23): Loading from cache
  - Updating sensio/framework-extra-bundle (v5.2.1 => v5.2.4): Downloading (100%)         
  - Updating squizlabs/php_codesniffer (2.9.1 => 2.9.2): Loading from cache
  - Updating doctrine/dbal (v2.8.0 => v2.9.0): Loading from cache
  - Updating doctrine/doctrine-cache-bundle (1.3.3 => 1.3.5): Loading from cache
  - Updating doctrine/doctrine-bundle (1.9.1 => 1.10.0): Loading from cache
  - Updating doctrine/doctrine-migrations-bundle (v1.3.1 => v1.3.2): Loading from cache
  - Updating doctrine/doctrine-fixtures-bundle (3.0.2 => 3.0.4): Loading from cache
  - Updating knplabs/doctrine-behaviors (1.5.0 => 1.6.0): Loading from cache
  - Installing ralouphie/getallheaders (2.0.5): Loading from cache
  - Updating guzzlehttp/psr7 (1.4.2 => 1.5.2): Loading from cache
  - Updating aws/aws-sdk-php (3.69.13 => 3.81.3): Downloading (100%)         
  - Updating twig/extensions (v1.5.2 => v1.5.4): Loading from cache
  - Updating doctrine/orm (v2.6.2 => v2.6.3): Loading from cache
  - Updating easycorp/easyadmin-bundle (v1.17.14 => v1.17.18): Loading from cache
  - Updating simshaun/recurr (v3.0.7 => v3.1): Loading from cache
  - Updating jms/metadata (1.6.0 => 1.7.0): Loading from cache
  - Updating vich/uploader-bundle (1.8.3 => 1.8.5): Loading from cache
  - Updating league/flysystem (1.0.48 => 1.0.49): Loading from cache
  - Updating oneup/flysystem-bundle (3.0.2 => 3.0.3): Loading from cache
  - Updating sonata-project/exporter (1.9.1 => 1.10.0): Downloading (100%)         
  - Updating sonata-project/seo-bundle (2.6.1 => 2.6.2): Loading from cache
  - Updating php-http/message (1.7.0 => 1.7.2): Loading from cache
  - Updating php-http/httplug-bundle (1.11.0 => 1.13.1): Loading from cache
  - Updating geocoder-php/google-maps-provider (4.2.0 => 4.3.0): Loading from cache
  - Updating mangopay/php-sdk-v2 (2.9.0 => 2.10.0): Downloading (100%)         
  - Updating symfony/phpunit-bridge (v3.4.17 => v3.4.20): Loading from cache
  - Updating composer/xdebug-handler (1.3.0 => 1.3.1): Loading from cache
  - Updating symfony/polyfill-php72 (v1.9.0 => v1.10.0): Loading from cache
  - Updating friendsofsymfony/http-cache (2.5.2 => 2.5.4): Loading from cache
  - Updating moneyphp/money (v3.1.3 => v3.2.0): Loading from cache
  - Updating omnipay/common dev-master (8b9f038 => 125ee8a):  Checking out 125ee8a12b

----
## 0.2.16-dev (from release/0.2.15)

### BC (Breaking Changes)
Database update required.

#### Database Updates
KYC documents are now part of the db.

#### ClassMap Updates
@todo

### Additions
#### ??? 

### Libraries/3rd-Party
none

---

## 0.2.15-MangopayBundleDevUpdate
### BC (Breaking Changes)
#### Database Updates
    ALTER TABLE mm_user ADD overallKycStatus VARCHAR(255) NOT NULL;

#### ClassMap Updates
Composer is now creating the PHP Classmap using this configuration in **composer.json**:

    "autoload": {
        "psr-4": {
          "MMApiBundle\\": "src/MMApiBundle",
          "MMUserBundle\\": "src/MMUserBundle",
          "MMWebFrontBundle\\": "src/MMWebFrontBundle",
          "Mealmatch\\ApiBundle\\": "src/Mealmatch/ApiBundle",
          "Mealmatch\\CalendarBundle\\": "src/Mealmatch/CalendarBundle",
          "Mealmatch\\GameLogicBundle\\": "src/Mealmatch/GameLogicBundle",
          "Mealmatch\\MangopayBundle\\": "src/Mealmatch/MangopayBundle",
          "Mealmatch\\MemeMemoryBundle\\": "src/Mealmatch/MemeMemoryBundle",
          "Mealmatch\\PayPalBundle\\": "src/Mealmatch/PayPalBundle",
          "Mealmatch\\RestaurantWebFrontBundle\\": "src/Mealmatch/RestaurantWebFrontBundle",
          "Mealmatch\\SearchBundle\\": "src/Mealmatch/SearchBundle",
          "Mealmatch\\ServiceTasksBundle\\": "src/Mealmatch/ServiceTasksBundle",
          "Mealmatch\\WebAdminBundle\\": "src/Mealmatch/WebAdminBundle",
          "Mealmatch\\WorkflowBundle\\": "src/Mealmatch/WorkflowBundle"
        },
        "classmap": [
          "app/AppKernel.php",
          "app/AppCache.php"
        ]
      },

### Additions
#### MealmatchMangopayBundle
This is the "new" home for the mangopay implementation. The prototype features will be replaced with this implementation
once finished.

#### KYC "prototype"
The KYC prototype implementation is contained within [PR #190](https://bitbucket.org/mealmatch/mmwebapp/pull-requests/190/0215-mangopaybundledevupdate/diff)

### Libraries/3rd-Party
Installs: phpfastcache/riak-client:3.4.3
Updates: aws/aws-sdk-php:3.69.13, composer/ca-bundle:1.1.3, friendsofsymfony/http-cache-bundle:2.6.0, league/flysystem:1.0.48, mangopay/php-sdk-v2:2.9.0, friendsofphp/php-cs-fixer:v2.13.1, roave/security-advisories:dev-master 0d96c6c
  - Updating aws/aws-sdk-php (3.69.5 => 3.69.13)
  - Updating composer/ca-bundle (1.1.2 => 1.1.3)
  - Updating friendsofsymfony/http-cache-bundle (2.5.1 => 2.6.0)
  - Updating league/flysystem (1.0.47 => 1.0.48)
  - Updating mangopay/php-sdk-v2 (2.8.1 => 2.9.0)
  - Updating friendsofphp/php-cs-fixer (v2.13.0 => v2.13.1)
  - Installing phpfastcache/riak-client (3.4.3): Loading from cache


----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
