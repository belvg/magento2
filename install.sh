#!/bin/bash

echo "Enter your web server domain name:"
read domain

echo "Enter your web server port:"
read port

echo "Enter Magento application base URL path:"
read url

cat app/etc/local.xml.template | sed "s|{DOMAIN}|$domain|g" | sed "s|{PORT}|$port|g" | sed "s|{URL}|$url|g" >app/etc/local.xml
cat www/.htaccess.template | sed "s|{PWD}|`pwd`|g" >www/.htaccess

chmod 0777 var
