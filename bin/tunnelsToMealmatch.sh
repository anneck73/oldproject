#!/usr/bin/env bash
ssh -N -L 13306:mealmatch.mysql.eu2.frbit.com:3306 mealmatch@tunnel.eu2.frbit.com &
ssh -N -L 13307:mealmatch-stage.mysql.eu2.frbit.com:3306 mealmatch-stage@tunnel.eu2.frbit.com &
netstat -n --protocol inet | grep ':22'
#mysql -umealmatch -h127.0.0.1 -P13306 -p7Z8DNXTMnxeeH2IO=8Bb.kYg