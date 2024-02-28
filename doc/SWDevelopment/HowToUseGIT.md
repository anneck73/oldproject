# How to deliver your work results from "coding" using Mealmatch CI/CD
###### Author: wizard@mealmatch.de 
###### STATUS: WiP/in Arbeit! | VERSION: 0.0.1

Status: quick brain dump.

Examples and best practices on how to use GIT.

----

## How to update your local branches
When working with dev-mealmatch (bitbucket/mmwebapp) do the following:

* Create a local branch for the named, current, main-development version. Example: 0.2.18-dev
* (or make sure its up-to-date using git pull)
* Create a local branch "copy" with your username as a prefix. Examples: 'username/0.2.18-dev', 'dinesh/0.2.18-dev',
'john-doe/0.2.18-dev', 'andre/0.2.18-dev', etc. ...
 

### GIT Examples 
With user **andre** and branch **0.2.18-dev**

### Prepare/Create you local "named" copy (initial) of the main-dev branch.

#### UPDATE your local -dev Version using pull

    dev@local ~/Mealmatch/PRJ/mmwebapp (0.2.18-dev) $ git pull
    Already up to date.

or

todo: add GIT CLONE example.


#### CREATE your personal "named" copy of the current main-dev branch (0.2.18-dev). 
    dev@local ~/Mealmatch/PRJ/mmwebapp (0.2.18-dev) $ git checkout -b andre/0.2.18-dev
    Switched to a new branch 'andre/0.2.18-dev'

### UPDATE after changes to the main-dev branch on bitbucket(mmwebapp).

#### First Checkout your local original 0.2.18-dev branch.
    dev@local ~/Mealmatch/PRJ/mmwebapp (andre/0.2.18-dev) $ git checkout 0.2.18-dev 
    Switched to branch '0.2.18-dev'
    Your branch is up to date with 'dev-mealmatch/0.2.18-dev'.

#### 2nd Pull changes into your local original 0.2.18-dev branch
    
    dev@local ~/Mealmatch/PRJ/mmwebapp (0.2.18-dev) $ git pull
    remote: Counting objects: 17, done.
    remote: Compressing objects: 100% (16/16), done.
    remote: Total 17 (delta 14), reused 2 (delta 1)
    Unpacking objects: 100% (17/17), done.
    From bitbucket.org:mealmatch/mmwebapp
       f2c95f190..7cf9eb65c  0.2.18-dev -> dev-mealmatch/0.2.18-dev
    Updating f2c95f190..7cf9eb65c
    Fast-forward
     app/AppKernel.php                                      |   9 +++----
     src/MMUserBundle/Entity/MMRestaurantProfile.php        |   6 ++++-
     src/MMWebFrontBundle/Controller/DefaultController.php  |   3 ---
     src/Mealmatch/ApiBundle/Controller/ApiController.php   |   2 +-
     src/Mealmatch/ApiBundle/Services/RestaurantService.php | 265 +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++---------------------------------------------------------------------------------
     5 files changed, 168 insertions(+), 117 deletions(-)

#### CHECKOUT your local named copy of the main-branch (BRANCH FIRST!)
    dev@local ~/Mealmatch/PRJ/mmwebapp (0.2.18-dev) $ git checkout andre/0.2.18-dev
    Switched to branch 'andre/0.2.18-dev'

#### REBASE your local changes "on top" of changes from you local copy of the main-dev branch.
    dev@local ~/Mealmatch/PRJ/mmwebapp (andre/0.2.18-dev) $ git rebase 0.2.18-dev 
    First, rewinding head to replay your work on top of it...
    Fast-forwarded andre/0.2.18-dev to 0.2.18-dev.

As you can see, your changes in john/0.2.18-dev are "replayed" and put "on top" of the downstream changes from bitbucket.

----

## Section Title
### Sub-Section
#### Sub-Sub-Section

use inside lists for more "sub-sections."

## Section Title
### Sub-Section
#### Sub-Sub-Section

use inside lists for more "sub-sections."

----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
