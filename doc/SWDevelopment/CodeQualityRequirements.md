# Code Quality-Requirements for Mealmatch 
###### Author: wizard@mealmatch.de | STATUS: WiP | VERSION: 0.0.1

Status, quick brain dump.
This documentation is about ... the quality requirements and standards this code base follows.  

----
## Must (Legal) Requirements
These reqirements are part of the contracts we make and have to be fullfilled before public release or contract work
resolution.

### Code Documentation 100%
Every logical piece of code is required to have lines of documentation explaining the funcionality/logic/etc. of the
code lines.

## Should (to be considered fullfilled)
These requirements describe our view on the "definition of done" regarding code quality and the code writen and the 
associated status in the project management tool. (Jira)

### The associated JIRA task should be beyond "in progress" mostly "resolved".
Depending on your work cycle, use Jira to select the correct "status" for your code.

"resolved" - if you applied a sollution to the context of the Jira issue.

### The associated commit should exist in a Pull-Request or dedicated branch.
In order to be integrated into the release branch every commit is required to exist in a branch on bitbucket inside
the WebApp project. 

### The associated pipeline-build on BitBucket did run.
The pipeline build needs to run in order to generate the code-quality reports and test-reports for the code.

### The associated pipeline-build on BitBucket did not produce any NEW failures
It is required that no new test failures are introduced with the code.

## Can (additional)
These requirements can be considered optional. 

### The associated pipeline-build on BitBucket did not produce any 

## Section Title
### Sub-Section
#### Sub-Sub-Section

use inside lists for more "sub-sections."

----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
