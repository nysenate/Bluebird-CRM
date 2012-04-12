#!/bin/bash

phpunit $1 > "temp.log" &
wait;
