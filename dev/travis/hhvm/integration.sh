#!/bin/bash

export MAX_INSTANCE=4
export BASE_DIR=$TRAVIS_BUILD_DIR
export PHPUNIT_COMMAND=$1

cd $BASE_DIR/dev/tests/integration
for (( i=0; i<$MAX_INSTANCE; i++ )); do
    cat ./phpunit.xml.dist \
    | sed 's#</php>#    <const name="TESTS_PARALLEL_THREAD" value="${i}"/>\n    </php>#' \
    | sed 's#etc/install-config-mysql.php#etc/install-config-mysql-${i}.php#' > ./phpunit-${i}.xml
    cat ./etc/install-config-mysql.travis.php | sed 's#magento_integration_tests#magento_integration_tests_${i}#' > ./etc/install-config-mysql-${i}.php
    mysql -uroot -e"create database magento_integration_tests_${i};"
done

run_tests() {
    dir=$1
    id=$(( $2 - 1 ))
    echo -e "\nRunning ${dir} tests"
    FOLDER=$(echo $dir | cut -d'/' -f4)
    RESULT="--stderr -c ${BASE_DIR}/dev/tests/integration/phpunit-${id}.xml --log-junit ${BASE_DIR}/integration_tests_${FOLDER}.xml"
    fl="/tmp/_test_${id}.log"
    $PHPUNIT_COMMAND $RESULT $dir | tee $fl
    cat $fl | grep -i -e fatal -e error > /dev/null && exit 1
    echo ""
}

export -f run_tests
ls -dX ./testsuite/Magento/* | grep -v _files | $BASE_DIR/dev/travis/parallel --gnu -P $MAX_INSTANCE 'run_tests {} {%}' || exit 1

unset -f run_tests
unset MAX_INSTANCES
unset BASE_DIR
unset PHPUNIT_COMMAND
