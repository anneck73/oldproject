# Mealmatch WebApp - ServiceTasks Bundle Dokumentation

Status: in Arbeit
Version: 0.2.x

##### Synopsis

Service "Tasks" sind Aufgaben die mit Hilfe der Mealmatch-Services ausgeführt werden.
Jede Aufgabe (Task) wird über ein Konsolencommando (Command) ausgeführt.

Alle Aufgaben sind dazu gedacht Regelmäßig ausgeführt zu werden. 
Jede Aufgabe wird über das Symfony CRON Bundle eingebunden

### Symfony CRON

Um cron jobs (os) automatisch über die cron-tab laufen zu lassen, 
diese Zeile hinzufügen:

  ```shell
  * * * * * /path/to/symfony/install/app/console cron:run 1>> /dev/null 2>&1
  ```

#### Ein ServiceTask zur Symfony CRON hinzufügen




Symfony CRON Kommandos
------------------

### list
```shell
bin/console cron:list
```
Show a list of all jobs. Job names are show with ```[x]``` if they are enabled and ```[ ]``` otherwise.

### create
```shell
bin/console cron:create
```
Create a new job.

### delete
```shell
bin/console cron:delete _jobName_
```
Delete a job. For your own protection, the job must be disabled first.

### enable
```shell
bin/console cron:enable _jobName_
```
Enable a job.

### disable
```shell
bin/console cron:disable _jobName_
```
Disable a job.

### run
```shell
bin/console cron:run [--force] [job]
```  

 
