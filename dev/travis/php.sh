#!/bin/bash

version=$(phpenv version-name)
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' | sudo tee "/home/travis/.phpenv/versions/${version}/etc/conf.d/sendmail.ini"
echo '' > ~/.phpenv/versions/${version}/etc/conf.d/xdebug.ini
echo 'memory_limit = -1' >> ~/.phpenv/versions/${version}/etc/conf.d/travis.ini
