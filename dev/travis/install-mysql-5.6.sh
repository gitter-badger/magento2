#!/bin/bash

set -e
PHP_VERSION=$1
echo $PHP_VERSION
sudo apt-get remove -qq --purge mysql-common mysql-server-5.5 mysql-server-core-5.5 mysql-client-5.5 mysql-client-core-5.5
sudo apt-get autoremove -qq
sudo apt-get autoclean -qq
sudo apt-add-repository ppa:ondrej/mysql-5.6 -y
sudo apt-get update -qq
sudo apt-get install -y -qq mysql-server-5.6 mysql-client-5.6
if [ '$PHP_VERSION' == 'hhvm' ]; then
    sudo apt-get install -y -qq hhvm-dbg
fi
