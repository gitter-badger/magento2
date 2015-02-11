#!/bin/bash

set -e

sudo add-apt-repository -y ppa:nginx/stable
sudo apt-get update -qq
sudo apt-get install -y -qq nginx-extras
