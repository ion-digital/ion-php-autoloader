#!/usr/bin/env sh

SCRIPT=$1
if [ $SCRIPT = "" ]; then
   SCRIPT="package"
fi

 composer run-script make $SCRIPT