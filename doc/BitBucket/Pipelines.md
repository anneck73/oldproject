# Pipelines with MMWebAPP
###### Author: wizard@mealmatch.de | STATUS: WiP | VERSION: 0.2.x

Status, quick brain dump.
This documentation is a start for developers and everyone working with BitBucket (GIT).

----

### Basic Rules/Guides

- The build configuration is part of the software and is treated as a software components. Equal to a Symfony Bundle.
- Commits into GIT are required to updated the bitbucket-pipeline.yml IF they need too.
- Pipelines configure **build and deployment** to mealmatch systems. (*.FRB.IO)  

### How to Build & Deploy

#### Tips & Infos 
!!! The default configuration delivers the MAIN BRANCH to https://mealmatch-dev.frb.io. !!!

- If you ever want to push a commit and skip triggering its pipeline, you can add [skip ci] or [ci skip] to the commit 
message.

###ATTENTION!!!
The pipelines documention is wrong for the MYSQL part when you setup it in the definitions. It should be MYSQL_USER: instead of MYSQL_USERNAME:

### #0 - Use JIRA 

Use Jira to create a feature branch for your task. 
Your work as a developer for mealmatch should start with creating a new Task in the JIRA project mmwebapp.
https://mealmatch.atlassian.net/projects/WEBAPP

- Create a new Task
- Assign it to you
- CLICK on "create branch" while viewing the issue.
- fetch & pull the remote branch and start working.
- push & autodeploy to mealmatch-dev will take place automagically.

### #1 - Use auto-deployment (TEST|STAGE)
Choose a branch name conforming to mealmatch conventions to use auto-deployment of branches.

In your branch build pipeline, there will be a __manual deployment step__!!
You should be able to *click* on deploy in order to start deployment of the build according to default values.

#### The following rules apply

Branchname pattern: "feature/**" 
Pipeline Deployment Stage: TEST
Deployment-Target: mealmatch-dev.frb.io (DEV)

Branchname pattern: "release/**" 
Pipeline Deployment Stage: PRODUCTION (LIVE)
Deployment-Target: mealmatch.de

Branchname pattern: "/**" 
Deployment Stage: STAGING
Deployment-Target: mealmatch-dev.frb.io

##### MAIN_BRANCH (STAGE)
The MAIN_BRANCH is always used and updated by the release master, a developer should not change that setting!
The MAIN_BRANCH is build & delivered to mealmatch-STAGE!

##### Feature Branches (TEST)
Features branches are build & delivered to mealmatch-dev if the GLOB pattern 'WEBAPP-**' is matched. 

##### Release Candidates (TEST|STAGE)
Release candidates must match the GLOB pattern '0.2.*-RC**' and are delivered to mealmatch-dev.

### Update bitbucket-pipelines.yml to deploy your branch

In order to deploy your dev branch to mealmatch-dev.frb.io you need to update the file bitbucket-pipeline.yml to match
your branch name. Your branch should be using your bitbucket username!

**By convention the current MAIN_BRANCH name is the prefix and the suffix is your developer name.**

Example:

- Current MAIN_BRANCH: 0.0.1
- Your BitBucket Username: 'xyz'
- Your branch-to-be-deployed-to-test-name: '0.0.1-xyz'

###### bitbucket-pipeline-yml: 

    '**-YOUR-BITBUCKET-USERNAME*':
      - step:
        name: Andre DEV delivery
        deployment: test
        caches:
        - composer
        script:
        - apt-get update && apt-get install -y unzip git rsync
        - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        - composer install -v --no-dev --no-scripts --no-interaction
        - php deploy.php
        - echo "YOUR-BITBUCKET-USERNAME test ... delivered to https://mealmatch-dev.frb.io."
    # ----------------------------------------------------------------------------------------------------------

### #2 Manual deployment

t.b.d.... 

## Deployment

Deployment to fortrabbit is done via calling php scripts which execute all required steps for deployment.

### deploy.php

deploy.php **pushes AND updates the database by force!**

### deploy_stage.php

deploy_stage.php **only pushes AND failes if db updates are reqired!**

This is the intendet setup. STAGE deliveries that change the DB MUST BE MANUALLY UPDATED!!!

And this manual step needs to be carefully done, only by the build master/dev-ops!




