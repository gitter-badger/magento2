#!/bin/bash
echo "PHP Version"
php -r "echo PHP_VERSION, PHP_EOL;"
echo "Travis PHP verion"
echo $TRAVIS_PHP_VERSION
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' | sudo tee "/home/travis/.phpenv/versions/${PHPENV_VERSION}/etc/conf.d/sendmail.ini"
echo '' > ~/.phpenv/versions/${PHPENV_VERSION}/etc/conf.d/xdebug.ini
echo 'memory_limit = -1' >> ~/.phpenv/versions/${PHPENV_VERSION}/etc/conf.d/travis.ini
