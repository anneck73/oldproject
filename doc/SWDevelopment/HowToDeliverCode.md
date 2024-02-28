# How to deliver your work results from "coding" using Mealmatch CI/CD
###### Author: wizard@mealmatch.de 
###### STATUS: WiP/in Arbeit! | VERSION: 0.0.1

Status: quick brain dump.

This documentation is about how to use the tools available at mealmatch to "transport" you local code changes to the 
mealmatch systems: 
* "DEV": https://mealmatch-dev.frb.io
* "STAGE": https://mealmatch-dev.frb.io

currently in planning are TEST and PRODUCTION.

----

## Pre-Requisites / Requirements
In order to participate you are required to install GIT on your local system and create a BitBucket user account in 
order to join the Mealmatch Team on BitBucket.

### GIT

Mealmatch preferred OS is Linux. GIT is available for every platform. Each developer is responsible for his/her GIT 
toolset. GIT itself requires good understanding, the tools to use are up to every developer.

### BitBucket User Account

You will fetch, push and pull with the Mealmatch Repositories on BitBucket.

----

### Create a new BitBucket account
...2do
#### I am a Mealmatch employee.
...2do
#### I am a 3rd Party developer.
...2do
### Already have an account on BitBucket?
...2do
#### I am a Mealmatch employee.
...2do
#### I am a 3rd Party developer.
...2do
### Join the Mealmatch Team on BitBucket

...2do
   
----

## Development Cycle
At Mealmatch we use the MAIN_BRANCH of the webapp project.
As a convention we use "named branches" with the VERSION as a fixed prefix.

**!!!We have no branch called master!!!**

**!!!We have no branch called master!!!**

**!!!We have no branch called master!!!**

The "current" MAIN_BRANCH is always set to a specific branch for development.
The development branch changes with VERSION updates.
#### Why? 
 1. This enables easy versionen using lots of (GIT) branches. Once for each version.
 2. This processs enforces parallel branches by nature which supports pipline builds for each branch.
 3. It integrates well with continuous integrations like the pipeline builds.

#### Example

Current release version: 7.7.1
Location: mealmatch-live@frb.io:master
Location on BitBucket: webapp:7.7.1-live-master
Next target version: 
Location for final testing: mealmatch-stage@frb.io:master
Location for development team work: webapp@bitbucket:MAIN_BRANCH
MAIN_BRANCH: 7.8.0-RC1


### Initial Clone
### Create local Branches
#### for any dev work
#### for updates to mealmatch
### Create Pull-Requests
### Create 


----

## Delivery 
### With pipelines
### With SSH

----

## Section Title
### Sub-Section
#### Sub-Sub-Section

use inside lists for more "sub-sections."
----

## Section Title
### Sub-Section
#### Sub-Sub-Section

use inside lists for more "sub-sections."

----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
