#!/bin/bash
if [[ ! -e node_modules/.bin/sass ]]
then
  if [[ -d node_modules ]]
  then
    echo "Weird, node_modules exists as if you've run 'npm install' but we don't have sass."
    echo "You could try removing node_modules and re-running npm install."
    exit 1
  fi
  echo "Sass not installed. Try running: npm install"
  echo "Then try again."
  exit 1
fi

if [[ ! -d org.civicrm.shoreditch ]]
then
  echo "Annoyingly, this project requires the Shoreditch theme to be cloned"
  echo "because we import a copy of its SASS in building ours."
  echo ""
  echo "Please run: git clone 'git@github.com:civicrm/org.civicrm.shoreditch.git'"
  echo "Then try this again."
  exit 1
fi

node_modules/.bin/sass \
  sass/mosaico-bootstrap.scss:css/mosaico-bootstrap.css \
  sass/legacy.scss:css/legacy.css \
  && echo "OK" || echo "Failed"
