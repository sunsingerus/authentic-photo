#!/bin/bash

# load token from file
source access_token

if [ -z $TOKEN ]; then
        echo Obtain TOKEN first
	exit
fi

# get files list
curl http://localhost/api/media-file \
	--verbose \
        --header "Authorization: Bearer $TOKEN" \
	--request GET

