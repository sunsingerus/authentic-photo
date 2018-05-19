#!/bin/bash

# load token from file

source access_token

if [ -z $TOKEN ]; then
        echo Obtain TOKEN first
	exit
fi


# file id to get
if [ -z "$1" ]; then
	echo "usage $0 FILE_ID"
	exit
fi

# get file details

FILE_ID=$1

JSON=$(curl http://localhost/api/media-file/$FILE_ID \
        --header "Authorization: Bearer $TOKEN" \
	--request GET)
echo $JSON

FILE_NAME=$(php -r "\$j = json_decode('$JSON', TRUE); echo \$j['data']['attributes']['file'];") > access_token
#echo $FILE_NAME


# get file body

curl http://localhost:8080/$FILE_NAME \
	--verbose \
        --header "Authorization: Bearer $TOKEN" \
	--request GET

