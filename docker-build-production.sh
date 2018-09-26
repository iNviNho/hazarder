#!/usr/bin/env bash

mkdir docker-build-production
cd docker-build-production
git clone -b production git@github.com:iNviNho/hazarder.git .

docker build -t vladino.me:5000/hazarder:production -f ../docker/Dockerfile-production .
docker push vladino.me:5000/hazarder


rm -rf docker-build-production