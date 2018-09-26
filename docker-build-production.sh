#!/usr/bin/env bash

mkdir docker-build
cd docker-build
git clone -b production git@github.com:iNviNho/hazarder.git .

docker build -t localhost:5000/hazarder:production -f ../docker/Dockerfile-production .
docker push localhost:5000/hazarder


rm -rf docker-build