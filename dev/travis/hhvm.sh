#!/bin/bash

cat ./dev/travis/php.ini >> /etc/hhvm/php.ini;
#    cat $TRAVIS_BUILD_DIR/dev/travis/php.ini >> ~/.phpenv/versions/$(phpenv version-name)/php.ini;
