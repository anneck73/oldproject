Mealmatch WebApp - Api Bundle Dokumentation
Status: in Arbeit
Version: 0.2.x

#ApiBundle

## Verzeichnisse

src/Mealmatch/ApiBundle/
├── ApiBundle.md
├── ApiBundle.php
├── ApiConstants.php
├── Controller
│   ├── ApiController.php
│   ├── Meal
│   └── RestaurantController.php
├── DataFixtures
│   └── ORM
├── Entity
│   ├── AbstractEntity.php
│   ├── EntityData.php
│   ├── GeoCodeable.php
│   ├── LegalFile.php
│   ├── LogEntry.php
│   ├── Meal
│   ├── Messages
│   ├── SameAddress.php
│   └── UploadableFile.php
├── EventSubscriber
│   └── MessagesSubscriber.php
├── Exceptions
│   ├── GeoCodeException.php
│   ├── InvalidArgumentException.php
│   ├── MealCreateFailed.php
│   ├── MealmatchException.php
│   ├── MealTicketException.php
│   ├── MMException.php
│   ├── MMPaymentException.php
│   ├── ServiceDataException.php
│   ├── ServiceDataValidationException.php
│   └── UserNotFoundException.php
├── Form
│   ├── LegalFileType.php
│   └── Meal
├── MealMatch
│   ├── CollectionHelper.php
│   ├── Doctrine
│   ├── EntityHelper.php
│   ├── FlashTypes.php
│   ├── FortrabbitMemcachedSessionHandler.php
│   ├── Meal.php
│   ├── README.md
│   ├── Swift
│   ├── Traits
│   ├── Twig
│   ├── UICalendarEventData.php
│   └── UserManager.php
├── Model
│   ├── AbstractServiceDataManager.php
│   ├── GeoAddressServiceData.php
│   ├── HomeMealServiceData.php
│   ├── LegalFileServiceData.php
│   ├── MealEventServiceData.php
│   ├── MealServiceData.php
│   ├── MetaServiceData.php
│   ├── ProMealServiceData.php
│   └── ServiceDataSpecification.php
├── Repository
│   ├── Meal
│   ├── MealAddressRepository.php
│   └── SameAddressRepository.php
├── Resources
│   ├── config
│   ├── translations
│   └── views
├── Security
│   └── Core
├── Services
│   ├── AbstractFinderService.php
│   ├── AwsUploaderService.php
│   ├── FinderServiceInterface.php
│   ├── GeoAddressService.php
│   ├── HomeMealServiceInterface.php
│   ├── HomeMealService.php
│   ├── JoinRequestService.php
│   ├── LegalFileService.php
│   ├── LogEntryHandlerService.php
│   ├── MealEventService.php
│   ├── MealService.php
│   ├── MealTicketService.php
│   ├── ProMealServiceInterface.php
│   ├── ProMealService.php
│   ├── RestaurantService.php
│   ├── SEOService.php
│   ├── SitemapIteratorService.php
│   └── ViewHelperService.php
└── SymfonyConstants.php


