#!groovy
// Mealmatch Multi Pipeline Build
// Author: wizard@mealmatch.de
// This Jenkinsfile delegates all 3rd party function calls to SHELL commands.
// Note to DEV:
// (1) Prefer Jenkins build language features over 3rd party function calls.
// for Version :
//
// --> 0.2.7 (LIVE) <---
// --> 0.2.8 (STAGE) <---
//
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
pipeline {
    agent any
    parameters {
        string(name: 'QUALITY_BUILD_ON', defaultValue: 'OFF', description: 'Quality build switch')
        string(name: 'QUALITY_BUILD_PROJECT', defaultValue: '-NONE-', description: 'Quality build switch and project name')
        string(name: 'QUALITY_BUILD_VERSION', defaultValue: '-NONE-', description: 'Quality build VERSION')
        string(name: 'SOURCE_BRANCH', defaultValue: '-NONE-', description: 'Integration source branch')
        string(name: 'INTEGRATION_BUILD_ON', defaultValue: 'OFF', description: 'Integration build switch')
    }
    // =================================================================================================================
    environment {
        APP_NAME = 'Mealmatch-WebApp'
        GIT_MM_SYSTEMUSER_PW = 'vVya47~3'
        STAGE_URL = 'https://mealmatch-stage.frb.io/'
    }
    options {
        buildDiscarder(logRotator(daysToKeepStr: '10', numToKeepStr: '9'))
    }
    // =================================================================================================================
    stages {
        // SETUP =======================================================================================================
        stage('setup') {
            steps {
                checkout scm
                sh 'composer install -v --no-scripts --no-interaction'
                sh 'git remote remove sk-mealmatch-test || true'
                sh 'git remote add sk-mealmatch-test http://mm_systemuser:vVya47~3@test.meal-match.com/plesk-git/mealmatch-test.git || true'
                sh 'git remote remove sk-mealmatch-test2 || true'
                sh 'git remote add sk-mealmatch-test2 http://mm_systemuser:vVya47~3@test2.meal-match.com/plesk-git/mealmatch-test2.git || true'
                sh 'git remote remove fortrabbit-stage || true'
                sh 'git remote add fortrabbit-stage mealmatch-stage@deploy.eu2.frbit.com:mealmatch-stage.git || true'
                sh 'git config user.email "wizard@mealmatch.de"'
                sh 'git config user.name "Wizard"'
                sh 'chmod -R 777 ./'
                sh 'mkdir -p build/'
                sh 'touch build/build.log'
                echo "Running on "
                sh 'git show --pretty=oneline Jenkinsfile | head -1'
            }
        }

        // Build =======================================================================================================

        stage('build - multibranch') {
            when {
                expression {
                    return env.BRANCH_NAME != null
                }
            }
            steps {
                echo "Build ${env.APP_NAME} from ${env.BRANCH_NAME}"

                echo "Notify BitBucket -> Build in progress ... "
                bitbucketStatusNotify(buildState: 'INPROGRESS')

                // SCM Checkout multibranch branch ...
                checkout changelog: true, poll: true,
                    scm: [$class: 'GitSCM',
                        branches: [[name: env.BRANCH_NAME]],
                        doGenerateSubmoduleConfigurations: false,
                        extensions: [],
                        submoduleCfg: [],
                        userRemoteConfigs: [
                            [credentialsId: '186ebc72-d3ee-40c8-b893-25cbbab242dd', name: 'origin', url: 'git@bitbucket.org:mealmatch/mmwebapp.git']
                        ]
                    ]

                // This is the JENKINS-BUILD-CONFIG, required before console can run!
                sh "cp -v ./etc/Jenkins/*.yml ./app/config/"
                sh "composer dump-autoload"
                script {
                    env.VERSION = sh(script: "bin/createVersion.sh", returnStdout: true).trim()
                }

                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "Pipeline-Build from ${env.BRANCH_NAME} version ${env.VERSION}#${env.BUILD_NUMBER} started",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true

                // Cleanup before build ...
                sh "rm -rf var/cache/*"
                sh "rm -rf var/logs/*.log"

                echo "Composer building version ${env.VERSION}"

                sh "composer run-script build-parameters"
                sh "composer run-script symfony-scripts"
                sh "composer run-script install-prod"

                echo "Run CodeQuality anyway ... "
                sh "bin/runCODEQUALITY.sh || true"

                // Create the PHPDocs
                sh "~/phpDocumentor.phar -d ./src/Mealmatch/ -t ./build/docs/mealmatch --cache-folder=~/tmp/|| true"

                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "Pipeline-Build from ${env.BRANCH_NAME} version ${env.VERSION}#${env.BUILD_NUMBER} finished",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true

            }
        }

        stage('build-test') {
            when {
                expression {
                    params.QUALITY_BUILD_PROJECT != '-NONE-'
                }
            }
            steps {
                echo "CodeQuality Build ${env.APP_NAME} for ${params.QUALITY_BUILD_PROJECT} version ${params.QUALITY_BUILD_VERSION}"

                script {
                    QUALITY_BUILD_BRANCH = params.QUALITY_BUILD_VERSION
                }

                // SCM Checkout multibranch branch ...
                checkout changelog: true, poll: true,
                    scm: [$class: 'GitSCM',
                        branches: [[name: QUALITY_BUILD_BRANCH]],
                        doGenerateSubmoduleConfigurations: false,
                        extensions: [],
                        submoduleCfg: [],
                        userRemoteConfigs: [
                            [credentialsId: '186ebc72-d3ee-40c8-b893-25cbbab242dd', name: 'origin', url: 'git@bitbucket.org:mealmatch/mmwebapp.git']
                        ]
                    ]

                // This is the JENKINS-BUILD-CONFIG including parameters.yml for the jenkins build.
                // (!) REQUIRED BEFORE first time console execution of console app:version:bump.
                sh "cp -v ./etc/Jenkins/*.yml ./app/config/"

                // TAGGING THE QUALITY BUILD, good practice and to have a nice version string.
                // tags current changeset as QB with version and #build
                sh "git tag -a ${params.QUALITY_BUILD_VERSION}-QB${env.BUILD_NUMBER} -m \"Mark QB ${params.QUALITY_BUILD_VERSION}-QB${env.BUILD_NUMBER}\""

                script {
                    env.VERSION = sh(script: "php bin/console app:version:bump", returnStdout: true).trim()
                }

                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "CodeQuality-Build from ${params.QUALITY_BUILD_PROJECT} version ${env.VERSION} started",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true

                script {
                    SOURCE_BRANCH_TO_TEST = params.QUALITY_BUILD_PROJECT.replaceAll('Mealmatch/','')
                }

                // Cleanup before build ...
                sh "rm -rf var/cache/*"
                sh "rm -rf var/logs/*.log"

                echo "Composer BUILD ... "
                sh 'composer install -v --no-scripts --no-interaction'
                sh "composer run-script build-parameters"
                sh "composer run-script symfony-scripts"
                sh "composer run-script install-prod"
                sh "composer run-script install-test"

                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "CodeQuality-Build from ${params.QUALITY_BUILD_PROJECT} version ${env.VERSION} build done, now running PHPUnit...",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true

                echo "Building version ${env.VERSION}"

            }
        }

        // UNIT-TEST====================================================================================================
        stage('unit-test') {
            when {
                expression {
                    params.QUALITY_BUILD_ON == 'ON'
                }
            }

            steps {
                sh 'mkdir -p build/phpunit/html/'
                sh 'mkdir -p build/phpunit/logs/'
                sh 'mkdir -p build/phpunit/coverage/'
                sh 'SYMFONY_DEPRECATIONS_HELPER=disabled php vendor/bin/phpunit -dxdebug.coverage_enable=1 -c phpunit.xml.dist --coverage-clover build/phpunit/clover/MMWebApp.coverage --coverage-html build/phpunit/html/ --coverage-xml build/phpunit/coverage/ --log-junit build/phpunit/logs/phpunit_integration.xml || true'
                junit allowEmptyResults: true, testResults: 'build/phpunit/logs/*.xml'
            }

        }

        stage('Code-Quality - Prepare Checkstyle Symfony3 Ruleset') {
            when {
                expression {
                    CHECK_CS_RULES = sh (script: 'vendor/bin/phpcs -i | grep Symfony3 | wc -l', returnStdout: true).trim()
                    return CHECK_CS_RULES > 1 && params.QUALITY_BUILD_ON == 'ON'
                    // GIT_BRANCH = 'origin/' + sh(returnStdout: true, script: 'git rev-parse --abbrev-ref HEAD').trim()
                    // return GIT_BRANCH == 'origin/master' || params.FORCE_FULL_BUILD
                }
            }
            steps {
                sh 'vendor/bin/phpcs --config-set installed_paths ../../endouble/symfony3-custom-coding-standard'
            }
        }

        // Code-Quality ================================================================================================
        stage('Code-Quality - Prepare directories') {
            when {
                expression {
                    params.QUALITY_BUILD_ON == 'ON'
                }
            }
            steps {
                sh 'mkdir -p build/reports'
                sh 'mkdir -p build/phpmetrics'
                sh 'mkdir -p build/phpcs'
                sh 'mkdir -p build/phpmd'
                sh 'mkdir -p build/pdepend'
                sh 'touch build/pdepend/summary.xml'
                sh 'mkdir -p build/phpunit/logs'
            }
        }

        stage('Code-Quality - Main') {
            when {
                expression {
                    params.QUALITY_BUILD_ON == 'ON'
                }
            }

            steps {
                parallel (
                    "PHP Metrics" : {
                        sh 'vendor/bin/phpmetrics --report-html=build/phpmetrics/index.html --junit=build/phpunit/logs/phpunit_integration.xml src/ || true'
                    },
                    "PHP Checkstyle" : {
                        sh 'vendor/bin/phpcs -p --report=checkstyle --standard=Symfony3Custom --report-file=./build/phpcs/checkstyle.xml src/ || true'
                    },
                    "PHP Checkstyle (JUNIT)" : {
                        // vendor/bin/phpcs -p --report=junit --standard=Symfony3Custom --report-file=build/phpunit/logs/phpcs-junit.xml src/
                        echo "skipped for now ..."
                    },
                    "PHP MD" : {
                        sh 'vendor/bin/phpmd src html cleancode,codesize,controversial,design,naming,unusedcode --reportfile build/phpmd/index.html || true'
                    },
                    "PHP SPEC" : {
                        sh 'vendor/bin/phpspec run || true'
                    },
                    "pDepend" : {
                        sh 'vendor/bin/pdepend --summary-xml=build/pdepend/summary.xml src/ || true'
                    },
                    "PHP-CS-FIXER": {
                        sh 'PHP_INI_SCAN_DIR=./etc/php-7.1-cli/conf.d/ php vendor/bin/php-cs-fixer fix --diff --dry-run --format=junit --config=.php_cs.php > build/phpunit/logs/phpcs.xml || true'
                    },
                    "PHP-ANALYZER": {
                        sh 'php vendor/bin/analyze analyze src/ || true'
                        sh 'php vendor/bin/analyze bundle build/analyzer/ || true'
                    }
                )
            }
        }

        stage ('Code-Quality - Codesonar') {
            when {
                expression {
                    params.QUALITY_BUILD_ON == 'ON'
                }
            }

            steps {
                // codesonar conditions: [redAlerts(alertLimit: 3)], credentialId: '', hubAddress: 'sonar.mealmatch.local:9000', projectName: 'MMWebpApp', protocol: 'http'
                echo "skipped for now ... "
            }
        }

        stage ('Code-Quality - Reporting') {
            when {
                expression {
                    params.QUALITY_BUILD_ON == 'ON'
                }
            }

            steps {
                junit allowEmptyResults: true, testResults: 'build/phpunit/logs/*.xml'
                publishHTML([
                        allowMissing: true,
                        alwaysLinkToLastBuild: true,
                        keepAll: false,
                        reportDir: 'build/',
                        reportFiles: 'phpunit/html/index.html, phpunit/html/dashboard.html, phpmd/index.html, phpmetrics/index.html/index.html, analyzer/index.html',
                        reportName: "Mealmatch ${env.APP_NAME} CI/CD Reports",
                        reportTitles: "${env.APP_NAME}"
                    ]
                )

            }
        }

        // DIST ========================================================================================================
        stage('dist') {
            steps {
                echo "Build Distribution ... (${env.APP_NAME}) "
                sh "rm -rf dist/*.zip"
                sh "mkdir -p dist/"
                sh "zip -q -r dist/etc-${env.VERSION}.zip etc/"
                sh "zip -q -r dist/web-app-${env.VERSION}.zip app/ src/ web/"
                sh "zip -q -r dist/binaries-${env.VERSION}.zip bin/"
                sh "zip -q -r dist/vendor-${env.VERSION}.zip vendor/"
                sh "zip -q -r dist/build-${env.VERSION}.zip build/ || true"
            }
        }

        // Archive =====================================================================================================
        stage('archive') {
            steps {
                archiveArtifacts artifacts: 'dist/**'
            }
        }
        // DEPLOY:STAGE=================================================================================================

        stage('deploy pipeline-build - STAGE') {
            when { anyOf { branch '**/0.2.8-*'; branch '**/0.2.8'; branch 'PR-*' } }
            steps {
                // STAGE commands ...
                // Reset, checkout -f and push
                sshagent(['e0352355-b436-4943-988d-9b9db8cd9b64']) {
                    sh 'ssh mealmatch-stage@deploy.eu2.frbit.com reset | true'
                    sh "git checkout -f ${env.BRANCH_NAME}"
                    sh "git pull"
                    sh "git push fortrabbit-stage ${env.BRANCH_NAME}:master"
                }
                // wait for deployement to finish
                sleep 15
                // check version deployed and install-stage
                // sshagent(['e0352355-b436-4943-988d-9b9db8cd9b64']) {
                //    sh 'ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console app:version:bump -d | true'
                //    sh 'ssh mealmatch-stage@deploy.eu2.frbit.com composer run-script install-stage | true'
                //}

                // Notify hipchat ...
                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "Deployed ${JOB_NAME}#${env.BUILD_NUMBER} to: <a href=\"${env.STAGE_URL}/\">STAGE</a>",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true

                bitbucketStatusNotify(buildState: 'SUCCESSFUL')
            }
        }

        // DEPLOY:INTEGRATION ==========================================================================================
        stage('deploy integration - TEST.meal-match.com') {
            when {
                expression {
                    return params.SOURCE_BRANCH != '-NONE-'
                }
            }
            steps {
                echo "Provision TEST with Composer!"
                sh 'rsync -p -h -a --stats --delete composer.* mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/ || true'


                echo "Provision TEST with src/ and app/!"
                sh 'rsync -p -h -a --stats --delete src/ mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/src/ || true'
                sh 'rsync -p -h -a --stats --delete app/ mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/app/ || true'

                // Now overwrite configuration for sk-mealmatch systems
                echo "Provision TEST with configs!"
                sh 'scp etc/TEST/*.yml mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/app/config/ || true'

                // After the push to sk-mealmatch, we need to sync ...
                echo "Provision TEST with vendor!"
                sh 'rsync -z -p -h -a --stats --delete vendor/ mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/vendor/ || true'

                echo "Clear TEST var/cache/!"
                sh 'ssh mm_systemuser@test.meal-match.com rm -rf /var/www/vhosts/mealmatch.de/test.meal-match.com/var/cache/prod || true'
                sh 'ssh mm_systemuser@test.meal-match.com rm -rf /var/www/vhosts/mealmatch.de/test.meal-match.com/var/cache/dev || true'
                sh 'ssh mm_systemuser@test.meal-match.com rm -rf /var/www/vhosts/mealmatch.de/test.meal-match.com/var/cache/test || true'

                echo "Provision TEST with web!"
                sh 'rsync -p -h --stats -a --delete web/ mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/web/ || true'

                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "Deploy Integration-Build result to: <a href=\"http://test.meal-match.com/\">TEST</a>",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true

            }
        }
        // DEPLOY:TEST =================================================================================================
        stage('deploy - TEST.meal-match.com') {
            // ONLY in multibranch ...
            when {
                 branch '**/0.2.2'
            }

            steps {
                echo "Prepare GIT push to sk-mealmatch-test ..."
                // Git interactions ...
                sh "git checkout --"
                sh "git status"
                sh "git checkout --force -B $BRANCH_NAME"

                echo "GIT push to sk-mealmatch-test ..."
                sh "git push sk-mealmatch-test +HEAD:mealmatch-test"

                // Now overwrite configuration for sk-mealmatch systems
                echo "Provision TEST with configs!"
                sh 'scp etc/TEST/*.yml mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/app/config/'

                // After the push to sk-mealmatch, we need to sync ...
                echo "Provision TEST with vendor!"
                sh 'rsync -a --stats --delete vendor/ mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/vendor/'

                echo "Clear TEST var/cache/!"
                sh 'ssh mm_systemuser@test.meal-match.com rm -rf /var/www/vhosts/mealmatch.de/test.meal-match.com/var/cache/prod'
                sh 'ssh mm_systemuser@test.meal-match.com rm -rf /var/www/vhosts/mealmatch.de/test.meal-match.com/var/cache/dev'
                sh 'ssh mm_systemuser@test.meal-match.com rm -rf /var/www/vhosts/mealmatch.de/test.meal-match.com/var/cache/test'

                echo "Provision TEST with web!"
                sh 'rsync --stats -a --delete web/ mm_systemuser@test.meal-match.com:/var/www/vhosts/mealmatch.de/test.meal-match.com/web/'

                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "Deployed Pipeline-Build ($BRANCH_NAME)result to: <a href=\"http://test.meal-match.com/\">TEST</a>",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true

                bitbucketStatusNotify(buildState: 'SUCCESSFUL')
            }

        }
        // DEPLOY:TEST2 ================================================================================================
        stage('deploy - TEST2.meal-match.com') {
            // ONLY in multibranch ...
            when {
                 branch '**/0.3.*'
            }

            steps {
                sh "git push sk-mealmatch-test2 +HEAD:mealmatch-test2"

                // Now overwrite configuration for sk-mealmatch systems
                echo "Provision TEST2 with configs!"
                sh 'scp etc/TEST2/*.yml mm_systemuser@test2.meal-match.com:/var/www/vhosts/mealmatch.de/test2.meal-match.com/app/config/'

                // After the push to sk-mealmatch, we need to sync ...
                echo "Provision TEST2 with vendor!"
                sh 'rsync --stats -a --delete vendor/ mm_systemuser@test2.meal-match.com:/var/www/vhosts/mealmatch.de/test2.meal-match.com/vendor/'

                echo "Provision TEST2 with web!"
                sh 'rsync --stats -a --delete web/ mm_systemuser@test2.meal-match.com:/var/www/vhosts/mealmatch.de/test2.meal-match.com/web/'

                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "Deploy Pipeline-Build ($BRANCH_NAME) result to: <a href=\"http://test2.meal-match.com/\">TEST2</a>",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true

            }
        }
    // REPORTS ========================================================================================================

        stage('publish - quality build reports') {
            when {
                expression {
                    params.QUALITY_BUILD_PROJECT != '-NONE-'
                }
            }

            steps {
                script {
                    env.VERSION = sh(script: "bin/createVersion.sh", returnStdout: true).trim()
                }

                // and publish the reports from build ...
                echo "Publish build reports for Quality-Build ${env.VERSION}#${env.BUILD_NUMBER} to REPORTS!"
                sh "curl ${BUILD_URL}consoleText >> build/build.txt"
                sh "cp -fv etc/HTML/build-index.html build/index.html"
                sh "cat build/build.txt | grep error > build/error.txt"
                sh "ssh mm_systemuser@reports.meal-match.com mkdir -p /var/www/vhosts/mealmatch.de/reports.meal-match.com/web/reports/Quality-Build/${env.BUILD_NUMBER}/"
                sh "rsync -a build/ mm_systemuser@reports.meal-match.com:/var/www/vhosts/mealmatch.de/reports.meal-match.com/web/reports/Quality-Build/${env.BUILD_NUMBER}/"
                hipchatSend color: 'GRAY',
                    credentialId: 'MealmatchMasterchiefToken',
                    message: "Build-Reports (${env.VERSION}#${env.BUILD_NUMBER}) results copied to: <a href=\"http://reports.meal-match.com/reports/Quality-Build/${env.BUILD_NUMBER}/index.html\">Reports</a>",
                    notify: true,
                    room: '3859044',
                    server: 'mealmatch.hipchat.com',
                    v2enabled: true
            }
        }


    // Job Trigger ========================================================================================================

        stage ('Start Quality-Build Job') {
            // Only if NOT currently running "integration" OR "quality-build"
            // This stage only does a quality-build for development
            when {
                expression {
                    return params.QUALITY_BUILD_ON == 'OFF' && params.INTEGRATION_BUILD_ON == 'OFF'
                }
            }
            steps {
                script {
                    env.VERSION = sh(script: "bin/createVersion.sh", returnStdout: true).trim()
                }
                // START BUILD JOB
                // Quality Pipeline-Build
                //
                echo "Trigger CodeQuality Build using jobname: ${JOB_NAME} version: ${env.VERSION}"

                build job: 'Mealmatch-CodeQuality',
                    parameters: [
                        string(name: 'QUALITY_BUILD_ON', value: 'ON'),
                        string(name: 'QUALITY_BUILD_PROJECT', value: "${JOB_NAME}"),
                        string(name: 'QUALITY_BUILD_VERSION', value: "${env.VERSION}")
                        ],
                    propagate: false, quietPeriod: 5

            }
        }

    }

// ====================================================================================================================
    post {
        always {
            echo 'PIPELINE FINISHED'
        }
        success {
            echo 'SUCCESS'
            emailext attachLog: true,
                body: '<h1>Mealmatch CI/CD The Pipeline '+ JOB_NAME + ' ' + env.VERSION + ' was build successfully!</h1>' +
                    '<p>Reports in <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/\">Reports</a></p>' +
                    '<p></p>' +
                    '<p>Quality Build Reports for ' + env.VERSION + ':</p>' +
                    '<p>PHPUnit - <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/phpunit/html/dashboard.html\">PHP Unit</a></p>' +
                    '<p>PHPMetrics - <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/phpmetrics/index.html/index.html\">PHP Metrics</a></p>' +
                    '<p>PHPMessDetector - <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/phpmd/index.html\">PHP Mess Detector</a></p>' +
                    '<p></p>',
                recipientProviders: [[$class: 'UpstreamComitterRecipientProvider']],
                replyTo: 'wizard@mealmatch.de',
                subject: 'Pipline ${JOB_NAME} ' + env.VERSION + ' [SUCCESS] ',
                to: 'webmaster@mealmatch.de',
                attachmentsPattern: 'dist/build-*.zip'
            hipchatSend color: 'GREEN',
                credentialId: 'MealmatchMasterchiefToken',
                message: "Build ${JOB_NAME} Successfull! See <a href=\"http://reports.meal-match.com/reports/${env.VERSION}/${env.BUILD_NUMBER}/index.html\">Build-Report (${env.VERSION}#${env.BUILD_NUMBER})</a> for details.",
                notify: true,
                room: '3859044',
                server: 'mealmatch.hipchat.com',
                v2enabled: true

        }
        failure {
            echo 'FAILURE'
            emailext attachLog: true,
                body: '''Mealmatch CI/CD The Pipeline ${JOB_NAME} ${env.VERSION} failed to build!''',
                recipientProviders: [[$class: 'UpstreamComitterRecipientProvider']],
                replyTo: 'wizard@mealmatch.de',
                subject: 'Pipline ${JOB_NAME} ' + env.VERSION + ' [FAILURE] ',
                to: 'webmaster@mealmatch.de',
                attachmentsPattern: 'dist/build-*.zip'
            hipchatSend color: 'RED',
                message: 'Build ${JOB_NAME} Failed',
                credentialId: 'MealmatchMasterchiefToken',
                notify: true,
                room: '3859044',
                server: 'mealmatch.hipchat.com',
                v2enabled: true

        }
        unstable {
            echo 'WORSE OR STILL BAD'
            emailext attachLog: true,
                body: '<h1>Mealmatch CI/CD The Pipeline '+ JOB_NAME + ' ' + env.VERSION + ' was build successfully and marked unstable!</h1>' +
                    '<p>Reports in <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/\">Reports</a></p>' +
                    '<p></p>' +
                    '<p>Quality Build Reports for ' + env.VERSION + ':</p>' +
                    '<p>PHPUnit - <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/phpunit/html/dashboard.html\">PHP Unit</a></p>' +
                    '<p>PHPMetrics - <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/phpmetrics/index.html/index.html\">PHP Metrics</a></p>' +
                    '<p>PHPMessDetector - <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/phpmd/index.html\">PHP Mess Detector</a></p>' +
                    '<p></p>',
                recipientProviders: [[$class: 'UpstreamComitterRecipientProvider']],
                replyTo: 'wizard@mealmatch.de',
                subject: 'Pipline ${JOB_NAME} ' + env.VERSION + ' [UNSTABLE] ',
                to: 'webmaster@mealmatch.de',
                attachmentsPattern: 'dist/build-*.zip'
            hipchatSend color: 'YELLOW',
                message: 'Build ${JOB_NAME} Unstable',
                credentialId: 'MealmatchMasterchiefToken',
                notify: true,
                room: '3859044',
                server: 'mealmatch.hipchat.com',
                v2enabled: true

        }
        changed {
            echo 'BETTER'
            emailext attachLog: true,
                body: '<h1>Mealmatch CI/CD The Pipeline '+ JOB_NAME + ' ' + env.VERSION + ' has changed!</h1>' +
                    '<p>Reports in <a href=\"http://reports.meal-match.com/reports/' + env.VERSION + '/\">Reports</a></p>' +
                    '<p></p>',
                recipientProviders: [[$class: 'UpstreamComitterRecipientProvider']],
                replyTo: 'wizard@mealmatch.de',
                subject: 'Pipline ${JOB_NAME} ' + env.VERSION + ' [CHANGED] ',
                to: 'webmaster@mealmatch.de',
                attachmentsPattern: 'dist/build-*.zip'

            hipchatSend color: 'PURPLE',
                credentialId: 'MealmatchMasterchiefToken',
                message: 'Build ${JOB_NAME} Changed',
                notify: true,
                room: '3859044',
                server: 'mealmatch.hipchat.com',
                v2enabled: true

        }
    }
}
