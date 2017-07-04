#!/bin/bash

echo "Starting allsky camera..."

CAMERA_CONFIG='/var/www/html/camera.conf'

ARGUMENTS=""
while IFS='' read -r line || [[ -n "$line" ]]; do
    ARGUMENTS="$ARGUMENTS -${line%=*} ${line#*=} "
done < "$CAMERA_CONFIG"

echo "./capture $ARGUMENTS"
