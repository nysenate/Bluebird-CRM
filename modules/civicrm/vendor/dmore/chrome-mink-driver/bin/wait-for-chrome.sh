#!/bin/bash

printf 'Waiting for chrome to start...'
for i in {0..240}; do
    if curl -sS ${CHROME_DEBUG_URL} &> /dev/null ; then
        break
    fi
    printf .
    sleep 1
done

echo ""

if [[ ${i} -eq 240 ]]; then
    echo "Chrome failed to start in 240 seconds"
else
    echo "Chrome started in ${i} seconds"
fi
