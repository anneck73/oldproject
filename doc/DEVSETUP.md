# Development Setup Short Docu
This is Work in progress ... 

## Requirements ##

- MySQL
- Memcache (optional)
- 
### Initial Setup

1. Checkout the sources from BitBucket.
1. Configure a mysql root user with all privileges.
1. Put the credentials into the app/config/parameters.yml.dist using your IDE.
1. Run "bin/setup.sh"

#### BASH Helper Scripts 
Below **bin/** there are many BASH "helper" scripts that are used for symfony interactions,
to "build" the environments **dev**, **prod**, **stage** and **test**.

Additionally there are **setup\*.sh** scripts to automate the local "setup".  

##### setup.sh
The inital setup helper script. 

- It checks and downloads composer if it does not exist.
- It removes and creates **DEV, TEST and PROD** databases!!! 
- Runs "population" scripts (fixtures and migrations) for every environment.

##### setupDB.sh
Use this script for the initial and complete setup of all DB's required.

- Drops and create's all required local databases.
- Runs "population" scripts (fixtures and migrations).

##### createTEEST.sh
Use this script to "clean" the local test database and redo a setup.

- It will drop,create,validate and update the database mealmatch_local_test.
- Runs "population" scripts (fixtures and migrations)

##### runPHPUnit.sh

- Run's PHPUnit with code coverage (if driver is configured) exporting the results 
to **build/phpunit/***

##### createDEV.sh 

Build helper to create the symfony "dev" environment.
It will validate the database mealmatch_local. 

##### createTEST.sh
Build helper to create the symfony "test" environment. 

##### createPROD.sh
Build helper to create the symfony "prod" environment.
It will validate and update the database mealmatch_local. 

     