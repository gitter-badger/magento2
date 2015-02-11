#!/bin/bash

set -e

sudo wget -O /opt/firefox.tar.bz2 ftp://ftp.mozilla.org/pub/firefox/releases/33.0/linux-x86_64/en-GB/firefox-33.0.tar.bz2
cd /opt/
sudo tar xjf firefox.tar.bz2

sudo chmod a+x /opt/firefox/firefox
