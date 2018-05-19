#!/bin/bash

source access_token

if [ -z $TOKEN ]; then
        echo Obtain TOKEN first
	exit
fi

curl http://localhost/api/media-file \
	--verbose \
        --header "Authorization: Bearer $TOKEN" \
	--request GET

