<?php
/*
 * Copyright (C) 2007 Jacob Singh, Sam Lerner
 * Licensed to CiviCRM under the Academic Free License version 3.0.
 *
 * Modified by CiviCRM LLC (c) 2007
 */

require_once '../../civicrm.config.php';
require_once 'CRM/Core/Config.php';

$config   = CRM_Core_Config::singleton( );
$template = CRM_Core_Smarty::singleton( );

$flashVars  = $config->resourceBase . "packages/amfphp/gateway.php";
$flashVars  = 'serviceUrl=' . urlencode( $flashVars );
$flashVars .= '&amp;contributionPageID=3&amp;widgetId=CiviCRM.Contribute.1';
$template->assign( 'flashVars', $flashVars );

echo $template->fetch( 'CRM/Widget/widget.tpl' );

