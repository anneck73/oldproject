#!/usr/bin/env bash
# Mealmatch Reporting Helper NOT used by Jenkins yet ...
# SSH Tunnel to LIVE is required ...
# Works from local dev
reportDATE=`date +%Y-%m-%d`
# ssh -N -L 13306:mealmatch.mysql.eu2.frbit.com:3306 mealmatch@tunnel.eu2.frbit.com
mysql mealmatch -umealmatch -h127.0.0.1 -P13306 --password=$1 --batch < bin/userStats.sql > userStats-$reportDATE.csv
