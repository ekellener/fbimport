#!/bin/bash

access_token="AAACEdEose0cBAIYiZCYZAxZB4hlG6JAQ4yNhKMZC0oMJzkFtWTVZCx4PkJxGppa9p70wZAlCcoPZA1CZCeXFk975fDZChpowAhw5ut5rW72uLeAZDZD"

token_url="access_token=${access_token}"

#batch="batch=[ "
batch="batch=[{\"method\":\"GET\",\"relative_url\": \"me\"} "

#batch="${batch}{ \"method\": \"GET \", \"relative_url\": \"pirelli\" } "
#batch="${batch}{\"method\": \"GET\", \"relative_url\": \"yokohamatirecorp\" }," 
#batch="${batch}{\"method\": \"GET\", \"relative_url\": \"hankooktire.global\"},"
#batch="${batch}{\"method\": \"GET\", \"relative_url\": \"pages/Goodyear-Tire-Rubber/106250116077811\" }"
batch="${batch}]"
#echo batch: $batch
#echo url: $token_url
url="curl -g -F '${token_url}' -F '${batch}' https://graph.facebook.com"
echo "${url}"

$url



