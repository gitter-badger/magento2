#!/bin/bash

#echo 'alias phpunit="hhvm -c $TRAVIS_BUILD_DIR/dev/travis/php.ini -v Eval.Jit=1 $TRAVIS_BUILD_DIR/vendor/phpunit/phpunit/phpunit"' >> ~/.bashrc
#. ~/.bashrc
TRAVIS_BUILD_DIR=$(pwd)
cd ./dev/tests/unit/

HHVM_OPTS="-c ${TRAVIS_BUILD_DIR}/dev/travis/php.ini -v Eval.Jit=1 -f ${TRAVIS_BUILD_DIR}/vendor/phpunit/phpunit/phpunit -- --configuration ./phpunit.xml.dist "
ls -d ./testsuite/Magento/* | grep -v _files | parallel --gnu -P 0 'echo "Running {} tests"; hhvm ${TRAVIS_BUILD_DIR}/dev/travis/php.ini -v Eval.Jit=1 -f ${TRAVIS_BUILD_DIR}/vendor/phpunit/phpunit/phpunit -- --configuration ./phpunit.xml.dist {};' || exit 1
#for i in $(find ./testsuite -maxdepth 2 -mindepth 2 -type d | grep -v '_' | sort); do
#    hhvm -c $TRAVIS_BUILD_DIR/dev/travis/php.ini -v Eval.Jit=1 $TRAVIS_BUILD_DIR/vendor/bin/phpunit $i
#    echo $i
#    echo ""
#done
