#!/usr/bin/env bash
docker system prune
docker rmi $(docker images -a -q)