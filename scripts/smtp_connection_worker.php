<?php

# Pretend we are in a web request
$_SERVER["HTTP_HOST"] = 'mcdonald';
$_SERVER["SERVER_NAME"] = 'mcdonald';

# Bootstrap the dedupe module
$root = dirname(dirname(__FILE__));
require_once "$root/drupal/sites/default/civicrm.settings.php";
require_once "$root/modules/nyss_dedupe/nyss_dedupe.module";
require_once "CRM/Core/Config.php";
require_once "CRM/Core/DAO.php";
$config = CRM_Core_Config::singleton();

$mailer = $config->getMailer();
$mailer->getSMTPObject();
echo $mailer->_smtp->host."\t".$mailer->_smtp->getGreeting(),"\n";
sleep(10);

?>
