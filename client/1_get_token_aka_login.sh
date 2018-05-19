#!/bin/bash

OUTPUT=$(curl http://localhost/api/token \
	--verbose \
	--header "Content-Type: application/json" \
	--request POST \
	--data '{"data": {"attributes": {"username":"admin@example.com","password":"qwerty"}}}')

echo $OUTPUT
JSON=$(echo $OUTPUT | tail -n 1)
echo $JSON

echo TOKEN=$(php -r "\$j = json_decode('$JSON', TRUE); echo \$j['access_token'];") > access_token

