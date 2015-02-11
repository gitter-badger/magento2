#!/bin/bash

set -e

TRAVIS_BUILD_DIR=$(pwd)

sudo cp -f ./dev/travis/hhvm/nginx.conf /etc/nginx/nginx.conf
sed -i -e "s#{{rootPath}}#${TRAVIS_BUILD_DIR}#g" /etc/nginx/nginx.conf
sudo service nginx configtest && sudo service nginx restart
sleep 5
if [[ -z $(netstat -antp | grep 80) ]]; then
    exit 130
fi

sed -i -e "s#{{rootPath}}#${TRAVIS_BUILD_DIR}#g" ./dev/travis/hhvm/hhvm.hdf
hhvm --mode server -vServer.Type=fastcgi -vServer.Port=9000 -c ./dev/travis/hhvm/hhvm.hdf -c ./dev/travis/php.ini &
sleep 5
if [[ -z $(netstat -antp | grep 9000) ]]; then
    exit 131
fi

# Start hub
SELENIUM=$TRAVIS_BUILD_DIR/dev/tests/functional/vendor/netwing/selenium-server-standalone/selenium-server-standalone.jar
#java -jar $SELENIUM -role hub -port 4444 >&2> /dev/null &
PID=/run/selenium-hub.pid
NAME=selenium-hub
SELENIUM_OPTS="-jar $SELENIUM -role hub -port 4444"
start-stop-daemon --start --name $NAME --background --quiet --pidfile $PID --exec /usr/bin/java -- $SELENIUM_OPTS
TIMESTAMP=$(($(date +%s)+40))
while [[ -z $(netstat -antp | grep 4444) && $TIMESTAMP -gt $(date +%s) ]]; do
    sleep 5
done
if [[ -z $(netstat -antp | grep 4444) ]]; then
    exit 132
fi

# Start node
HUB_HOST=http://localhost:4444/grid/register
NODE_PORT=$((RANDOM%1000+5554))
PID=/run/selenium-node.pid
NAME=selenium-node
SELENIUM_OPTS="-jar $SELENIUM -role node -hub $HUB_HOST -port $NODE_PORT -browser browserName=firefox,firefox_binary=/opt/firefox/firefox"
start-stop-daemon --start --name $NAME --background --quiet --pidfile $PID --exec /usr/bin/java -- $SELENIUM_OPTS
#java -jar $SELENIUM -role node -hub $HUB_HOST -port $NODE_PORT -browser browserName=firefox,firefox_binary=/opt/firefox/firefox >&2> /dev/null &
TIMESTAMP=$(($(date +%s)+40))
while [[ -z $(netstat -antp | grep $NODE_PORT) && $TIMESTAMP -gt $(date +%s) ]]; do
    sleep 5
done
if [[ -z $(netstat -antp | grep $NODE_PORT) ]]; then
    exit 133
fi

cd ./dev/tests/functional/
cp phpunit.xml.dist phpunit.xml
php -f ./utils/generate/page.php
php -f ./utils/generate/factory.php
mkdir -p ./var/log
cd $TRAVIS_BUILD_DIR

sh -e /etc/init.d/xvfb start
sleep 5
