#!/bin/bash

echo $1
for (( i=0; i<$1; i++ )); do
    php smtp_connection_worker.php &
done

wait

echo "Done!"
