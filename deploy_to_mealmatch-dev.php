<?php
// This is a command line PHP Helper script!
// It is supposed to be run from a bitbucket pipeline!
// Its purpose is to execute all required "build steps" & "QA steps"
// AND to deploy to fortrabbit using stored SSH credentials on bitbucket!
// -------------------------------------------------------------------------------
# Prepare the bitbucket pipeline
`curl -sL https://deb.nodesource.com/setup_10.x | bash -`;
`apt-get install -y nodejs`;
`curl -o- -L https://yarnpkg.com/install.sh | bash`;
shell_exec("export PATH=\$HOME/.yarn/bin:\$PATH");
`yarn add @symfony/webpack-encore --dev`;

// (1) Gather all required variables ...
# git clone mealmatch-dev@deploy.eu2.frbit.com:mealmatch-dev.git
$frRepoUrl = 'mealmatch-dev@deploy.eu2.frbit.com:mealmatch-dev.git';
$frRepoDir = sprintf('%s/mealmatch-dev', sys_get_temp_dir());
$frRepoTarget = "mealmatch-dev";
$bitBucketCurrentBranch = getenv('BITBUCKET_BRANCH');
$gitAuthorEmail = "wizard@mealmatch.de";
$gitAuthorName = "wizard";
$curDir = getcwd();

// Get commit so it can be forwarded
echo "# Gathering commit message\n";
$lastCommitMessage =`git log -1 --pretty=%B`;
$lastCommitMessage =  preg_replace('/["\'\n\r]/', '', $lastCommitMessage);
echo " > $lastCommitMessage\n\n";

// Update SSH config:
//   StrictHostKeyChecking=no is not a secure setting, but "yes" or "ask" will
//   require read from /dev/tty, which is not available.
//   Should be replaced by adding the fingerprint to ~/.ssh/known_hosts instead.
echo "# Updating SSH config\n";
`grep -q PreferredAuthentications ~/.ssh/config || echo "PreferredAuthentications publickey" >> ~/.ssh/config`;
`grep -q StrictHostKeyChecking ~/.ssh/config || echo "StrictHostKeyChecking no" >> ~/.ssh/config`;
//`grep -q LogLevel ~/.ssh/config || echo "LogLevel DEBUG3" >> ~/.ssh/config`;
echo " > Done\n\n";

// Git author must be set, or STDIN is again required
echo "# Init git\n";
`git config --global user.email "$gitAuthorEmail"`;
`git config --global user.name "$gitAuthorName"`;
// Adding target repository as remote
echo `git remote add "$frRepoTarget" "$frRepoUrl"`;
echo `git remote -v`;
// echo "# user.email $gitAuthorEmail\n\n";
// echo "# user.email $gitAuthorName\n\n";
echo " > Done\n\n";

// Push everything to fortrabbit
echo "# Deploying with push -f to $frRepoTarget:master ...\n";
echo `git push -f "$frRepoTarget" "$bitBucketCurrentBranch":master`;
echo " > Done\n\n";

echo "# Waiting for deploy to finish\n";
sleep(15);
echo " > Done\n\n";

// Now update the database on fortrabbit ...
echo "# Force database update on fortrabbit!!!! \n";
// dump what will be changed
echo `ssh mealmatch-dev@deploy.eu2.frbit.com php bin/console d:s:u --dump-sql`;
// wait a bit for the next command
sleep(5);
echo `ssh mealmatch-dev@deploy.eu2.frbit.com php bin/console d:s:u --force`;
echo " > Done\n\n";

echo "# Sync to webpack encore result to fortrabbit after push \n";
echo `yarn encore production`;
echo `rsync -av ./web/static/ mealmatch-dev@deploy.eu2.frbit.com:web/static/`;
echo " > Done\n\n";
