#!/bin/bash

source access_token

if [ -z $TOKEN ]; then
        echo Obtain TOKEN first
	exit
fi

# file id to delete
if [ -z "$1" ]; then
	echo "usage $0 FILE_ID"
	exit
fi

FILE_ID=$1

curl http://localhost/api/media-file/$FILE_ID \
	--verbose \
        --header "Authorization: Bearer $TOKEN" \
	--request DELETE

