#!/bin/bash

set -e

mysql -e 'create database magento;'
cd ./setup
php -f index.php install --cleanup_database \
    --language=en_US \
    --timezone=America/Los_Angeles \
    --currency=USD \
    --use_secure=0 \
    --use_secure_admin=0 \
    --admin_use_security_key=0 \
    --use_rewrites=0 \
    --admin_lastname=Admin \
    --admin_firstname=Admin \
    --admin_email=admin@example.com \
    --admin_username=admin \
    --admin_password=123123q \
    --session_save=db \
    --backend_frontname=backend \
    --base_url=http://localhost/ \
    --base_url_secure=http://localhost/ \
    --db_host=localhost \
    --db_name=magento \
    --db_user=root
#| tee /tmp/magento.log | grep 'Magento installation complete.'
#if [[ $? -en 0 ]]; then
#    cat /tmp/magento.log
#fi
cd ../
