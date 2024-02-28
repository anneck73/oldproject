#!/usr/bin/env bash
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:create SYSTEM system@mealmatch.de 123 --super-admin --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:activate SYSTEM --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:promote SYSTEM ROLE_SYSTEM --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:create MMTestGuest mmtestguest@mealmatch.de 123  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:activate MMTestGuest  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:create MMTestRestaurant mmtestrestaurant@mealmatch.de 123  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:activate MMTestRestaurant  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:promote MMTestRestaurant ROLE_RESTAURANT_USER  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:create MMTestHome mmtesthome@mealmatch.de 123  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:activate MMTestHome  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:promote MMTestHome ROLE_HOME_USER  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:create MMTestHomeHost mmtesthomehost@mealmatch.de 123  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:activate MMTestHomeHost  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:promote MMTestHomeHost ROLE_HOME_USER  --no-debug
sleep 2
ssh mealmatch-stage@deploy.eu2.frbit.com php bin/console fos:user:promote MMTestHomeHost ROLE_HOME_HOST_USER  --no-debug
