#!/usr/bin/env bash

D_VER=$1

if [ $# -eq 0 ]
  then
    echo "No arguments supplied"
    exit
fi

echo 'Sleeping 90 seconds to give the build process enough time...'
sleep 90

echo 'Uninstalling Spectero...'
systemctl stop spectero
pkill -f dotnet
pkill -f vpn

wget -O install.sh https://c.spectero.com/installers/spectero-unix-installer.sh
bash install.sh -u
rm -rf /opt/spectero
userdel spectero

echo 'Reinstalling Spectero...'
bash install.sh --version "$D_VER" -a -ai

echo 'Waiting 32 seconds for first init to complete...'
sleep 32

echo 'Reconciling cloud details...'
sqlite3 /opt/spectero/latest/daemon/Database/db.sqlite < /opt/build/reconcile.sql 

echo 'Cleaning up...'
rm -f install.sh

echo 'Restarting Spectero Daemon...'
systemctl restart spectero
