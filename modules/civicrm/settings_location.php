<?php

if (!isset($_SERVER['HTTP_HOST']) && getenv('INSTANCE_NAME') === false) {
  die("Either HTTP_HOST global or INSTANCE_NAME environment variable must be set.\n");
}

define('CIVICRM_CONFDIR', dirname(__FILE__).'/../../drupal/sites/');

