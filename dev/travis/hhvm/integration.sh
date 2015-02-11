#!/bin/bash

export MAX_INSTANCE=4
export BASE_DIR=$TRAVIS_BUILD_DIR
export PHPUNIT_COMMAND=$1

cd $BASE_DIR/dev/tests/integration
for (( i=0; i<$MAX_INSTANCE; i++ )); do
    cat ./phpunit.xml.dist \
    | sed "s#(TESTS_PARALLEL_THREAD.\s*value=.)\d#\1${i}#" \
    | sed "s#etc/install-config-mysql.php#etc/install-config-mysql-${i}.php#" > ./phpunit-${i}.xml
    cat ./etc/install-config-mysql.php | sed "s#magento_integration_tests#magento_integration_tests_${i}#" > ./etc/install-config-mysql-${i}.php
    mysql -uroot -e"create database magento_integration_tests_${i};"
done
cat ./phpunit-1.xml
cat ./etc/install-config-mysql-1.php
exit 0

run_tests() {
    dir=$1
    id=$(( $2 - 1 ))
    echo -e "\nRunning ${dir} tests"
    FOLDER=$(echo $dir | cut -d'/' -f4)
    RESULT="--stderr -c ${BASE_DIR}/dev/tests/integration/phpunit-${id}.xml --log-junit ${BASE_DIR}/integration_tests_${FOLDER}.xml"
    fl="/tmp/_test_${id}.log"
    echo -e "\n $PHPUNIT_COMMAND $RESULT $dir \n"
    $PHPUNIT_COMMAND $RESULT $dir | tee $fl
    ls -l ./tmp/
    cat $fl | grep -i -e fatal -e error > /dev/null && exit 1
    echo ""
}

export -f run_tests
ls -dX ./testsuite/Magento/* | grep -v _files | $BASE_DIR/dev/travis/parallel --nn --gnu -P $MAX_INSTANCE 'run_tests {} {%}' || exit 1

unset -f run_tests
unset MAX_INSTANCES
unset BASE_DIR
unset PHPUNIT_COMMAND
