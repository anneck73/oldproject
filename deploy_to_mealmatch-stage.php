<?php
# git clone mealmatch-dev@deploy.eu2.frbit.com:mealmatch-dev.git
$frRepoUrl = 'mealmatch-stage@deploy.eu2.frbit.com:mealmatch-stage.git';
$frRepoDir = sprintf('%s/mealmatch-stage', sys_get_temp_dir());

$gitAuthorEmail = "wizard@mealmatch.de";
$gitAuthorName = "wizard";

$curDir = getcwd();

// get the current data in
`date > the-current-date`;

// execute yarn encore production
`yarn encore production`;

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
echo "# user.email $gitAuthorEmail\n\n";
echo "# user.email $gitAuthorName\n\n";
echo " > Done\n\n";


// make sure fortrabbit repo is cloned - or pulled
if (file_exists("$frRepoDir/.git")) {
    chdir($frRepoDir);
    echo "# Updating \"$frRepoUrl\" repo in \"$frRepoDir\"\n";
    echo `git pull`;
} else {
    echo "# Cloning \"$frRepoUrl\" repo into \"$frRepoDir\"\n";
    chdir(dirname($frRepoDir));
    $command = "git clone \"$frRepoUrl\" \"$frRepoDir\"";
    echo `git clone "$frRepoUrl" "$frRepoDir"`;
}
echo " > Done\n\n";


// Abort in case previous clone failed
if (!file_exists("$frRepoDir/.git")) {
    error_log("Could not clone/update repo from fortrabbit");
    exit(1);
}
// Sync all changed files, including those which were generated during build
echo "# Syncing changes\n";
echo `rsync -avC --delete-after --exclude=/vendor/ "$curDir"/ "$frRepoDir"/`;
echo " > Done\n\n";

// Commit & push everything to fortrabbit
echo "# Deploying changes\n";
chdir($frRepoDir);
echo `git add -Av`;
echo `git commit -am "$lastCommitMessage"`;
echo `git push origin master`;
echo " > Done\n\n";

echo "# Waiting for deploy to finish\n";
sleep(5);
echo " > Done\n\n";

echo "# Sync to webpack encore result to fortrabbit after push \n";
echo `rsync -av ./web/bundles/ mealmatch-dev@deploy.eu2.frbit.com:~/htdocs/web/bundles/`;


