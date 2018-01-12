#!/bin/bash
source /home/pi/allsky/config.sh

echo "Restarting Capture with new settings"
cd /home/pi/allsky

# Building the arguments to pass to the capture binary
ARGUMENTS=""
KEYS=( $(jq -r 'keys[]' $CAMERA_SETTINGS) )
for KEY in ${KEYS[@]}
do
	ARGUMENTS="$ARGUMENTS -$KEY `jq -r '.'$KEY $CAMERA_SETTINGS` "
done
echo "Restarting with new arguments $ARGUMENTS">>/home/pi/allsky/log.txt

# We kill the capture process and restart it with new arguments
killall -9 capture ; /home/pi/allsky/capture $ARGUMENTS
