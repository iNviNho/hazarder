#!/usr/bin/env bash

rm -rf docker-build

mkdir docker-build
cd docker-build

git clone -b master git@github.com:iNviNho/hazarder.git .

docker build -t vladino.me:5000/hazarder:local -f ../docker/Dockerfile-local .
docker push vladino.me:5000/hazarder


rm -rf ../docker-build