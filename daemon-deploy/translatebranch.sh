#!/usr/bin/env bash

# This is /bin/translatebranch

if [ $# -eq 0 ]
  then
    echo "No arguments supplied"
    exit
fi


case "$1" in
        master)
            echo "stable"
            ;;

        staging)
            echo "alpha"
            ;;

        beta)
            echo $1
            ;;

        *)
            echo $"Usage: $0 {master|staging|beta}"
            exit 1
esac
