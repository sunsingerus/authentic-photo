#!/bin/bash

# load token from file
source access_token

if [ -z $TOKEN ]; then
	echo Obtain TOKEN first
	exit
fi

# filename to upload
if [ -z "$1" ]; then
	FILE="./example.png"
else
	FILE=$1
fi

echo "Uploading $FILE"

curl http://localhost/api/upload \
	--verbose \
        --header "Authorization: Bearer $TOKEN" \
	--request POST \
	-F "image=@$FILE;type=image/png"

