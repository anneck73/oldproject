<?php
# git clone mealmatch-dev@deploy.eu2.frbit.com:mealmatch-dev.git
$frRepoUrl = 'mealmatch-dev@deploy.eu2.frbit.com:mealmatch-dev.git';
$frRepoDir = sprintf('%s/mealmatch-dev', sys_get_temp_dir());
$frRepoTarget = "mealmatch-dev";
$bitBucketCurrentBranch = getenv('BITBUCKET_BRANCH');

$gitAuthorEmail = "wizard@mealmatch.de";
$gitAuthorName = "wizard";

$curDir = getcwd();

// Simulate changes, which are not in the Git repo, but generated in build
// `date > the-current-date`;

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
echo "# Force database update on fortrabbit!!!! \n";

// just wait a bit for deployment to finish roll-out ...
sleep(15);

echo `ssh mealmatch-dev@deploy.eu2.frbit.com php bin/console d:s:u --dump-sql`;
echo `ssh mealmatch-dev@deploy.eu2.frbit.com php bin/console d:s:u --force`;
echo " > Done\n\n";
