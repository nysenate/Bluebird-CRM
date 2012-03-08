#!/bin/bash

for ((worker = 0; worker < $2; worker++)); do
    phpunit $1 > "worker_$worker.log" &
done
wait;

echo "runner.sh: Done";
