#!/usr/bin/env bash
# Create and send a report into the downloads section of the repository
# We need to to change the special character / with --
branch_name=$(echo $BITBUCKET_BRANCH | sed -e 's/\//--/')
# Zip everything in build/ and put it into named reports.zip
zip -r "build-${branch_name}-reports.zip" build/
# Deliver to downloads section of mmwebapp repository.
# To enable this, you have to set the BB_AUTH_STRING environment variable for your bitbucket user!
curl -X POST --user "${BB_AUTH_STRING}" "https://api.bitbucket.org/2.0/repositories/${BITBUCKET_REPO_OWNER}/${BITBUCKET_REPO_SLUG}/downloads" --form files=@"build-${branch_name}-reports.zip"
