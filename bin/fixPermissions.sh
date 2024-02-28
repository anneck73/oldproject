#!/usr/bin/env bash
# Helper to fix file based permission problems when running the web-server with a different user (e.g. Apache)
developer=$(whoami)
webserver=www-data
# fix with chown
sudo chown -R $developer:$webserver var/cache/ var/logs/ web/
# using setfacl to make it permanent (@todo: ? doesnt work as permanent as expected!)
sudo setfacl -dR -m g::rwX var/cache/ var/logs/ web/

# fix with chown
sudo chown -R $developer:$webserver var/ web/
# using setfacl to make it permanent (@todo: ? doesnt work as permanent as expected!)
sudo setfacl -dR -m g::rwX var/ web/

sudo chmod -R 777 /tmp