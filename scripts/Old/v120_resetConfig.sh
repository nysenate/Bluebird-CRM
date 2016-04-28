#!/bin/sh
#
# v120_resetConfig.sh
#
# Project: BluebirdCRM
# Author: Brian Shaughnessy
# Organization: New York State Senate
# Date: 2011-03-11
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
drush=$script_dir/drush.sh

. $script_dir/defaults.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi

instance="$1"

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi


## set reset config
config='a:74:{s:9:"enableSSL";s:0:"";s:15:"civiRelativeURL";s:1:"/";s:11:"mapProvider";s:6:"Google";s:9:"mapAPIKey";s:86:"ABQIAAAAzY9VyTEuublBDc-Htl9EvhQuOUP8hd2qhSL-nJEVisOKANWd3xQi7-zJ-V3SB2GbiDzS7GSEa0pZeg";s:11:"geoProvider";s:4:"SAGE";s:9:"geoAPIKey";s:31:"SQ0lzOepSH3qnh2r4kN1QeRCMAAan2u";s:21:"includeWildCardInName";s:1:"0";s:18:"includeEmailInName";s:1:"1";s:21:"includeNickNameInName";s:1:"0";s:24:"includeAlphabeticalPager";s:1:"1";s:20:"includeOrderByClause";s:1:"1";s:22:"smartGroupCacheTimeout";s:1:"0";s:22:"defaultSearchProfileID";s:2:"11";s:25:"autocompleteContactSearch";a:2:{i:1;s:1:"1";i:2;s:1:"1";}s:5:"debug";s:1:"0";s:20:"userFrameworkLogging";s:1:"0";s:9:"backtrace";s:1:"0";s:18:"fatalErrorTemplate";s:20:"CRM/common/fatal.tpl";s:17:"fatalErrorHandler";s:0:"";s:15:"civiAbsoluteURL";s:29:"http://sd99.crm.nysenate.gov/";s:16:"enableComponents";a:2:{i:0;s:10:"CiviReport";i:1;s:8:"CiviCase";}s:18:"enableComponentIDs";a:2:{i:0;s:1:"8";i:1;s:1:"7";}s:13:"userFramework";s:6:"Drupal";s:11:"initialized";s:1:"0";s:15:"DAOFactoryClass";s:23:"CRM_Contact_DAO_Factory";s:17:"componentRegistry";O:18:"CRM_Core_Component":0:{}s:9:"inCiviCRM";s:0:"";s:18:"recaptchaPublicKey";s:0:"";s:12:"resourceBase";s:0:"";s:12:"countryLimit";a:1:{i:0;s:4:"1228";}s:13:"provinceLimit";a:1:{i:0;s:4:"1228";}s:21:"defaultContactCountry";s:4:"1228";s:15:"defaultCurrency";s:3:"USD";s:10:"lcMessages";s:5:"en_US";s:18:"dateformatDatetime";s:20:"%B %E%f, %Y %l:%M %P";s:14:"dateformatFull";s:11:"%B %E%f, %Y";s:17:"dateformatPartial";s:5:"%B %Y";s:14:"dateformatYear";s:2:"%Y";s:14:"dateformatTime";s:8:"%l:%M %P";s:15:"timeInputFormat";s:1:"1";s:15:"dateInputFormat";s:8:"mm/dd/yy";s:15:"fiscalYearStart";a:2:{s:1:"M";i:1;s:1:"d";i:1;}s:11:"moneyformat";s:5:"%c %a";s:16:"moneyvalueformat";s:3:"%!i";s:15:"currencySymbols";s:0:"";s:21:"defaultCurrencySymbol";s:1:"$";s:20:"monetaryDecimalPoint";s:1:".";s:25:"monetaryThousandSeparator";s:1:",";s:14:"gettextCodeset";s:5:"utf-8";s:13:"gettextDomain";s:7:"civicrm";s:20:"userFrameworkVersion";s:4:"6.15";s:27:"userFrameworkUsersTableName";s:5:"users";s:21:"userFrameworkFrontend";s:0:"";s:17:"maxImportFileSize";s:7:"1048576";s:14:"maxAttachments";s:1:"3";s:11:"maxFileSize";s:1:"2";s:7:"civiHRD";s:1:"0";s:13:"geocodeMethod";s:0:"";s:12:"mapGeoCoding";s:1:"1";s:15:"contactUndelete";s:1:"1";s:12:"versionCheck";s:1:"1";s:14:"legacyEncoding";s:12:"Windows-1252";s:14:"fieldSeparator";s:1:",";s:17:"maxLocationBlocks";s:1:"2";s:15:"captchaFontPath";s:25:"/usr/X11R6/lib/X11/fonts/";s:11:"captchaFont";s:17:"HelveticaBold.ttf";s:21:"dashboardCacheTimeout";s:4:"1440";s:15:"doNotResetCache";s:1:"0";s:13:"oldInputStyle";s:1:"1";s:14:"formKeyDisable";s:0:"";s:13:"verpSeparator";s:1:".";s:12:"mailerPeriod";s:3:"180";s:16:"mailerSpoolLimit";s:1:"0";s:16:"mailerBatchLimit";s:1:"0";}'

configUpdate="UPDATE civicrm_domain SET config_backend='$config'"
$execSql -i $instance -c "$configUpdate"

customPaths="
UPDATE civicrm_option_value SET value = '/opt/bluebird_prod/civicrm/custom/templates' WHERE name = 'customTemplateDir';
UPDATE civicrm_option_value SET value = '/opt/bluebird_prod/civicrm/custom/php' WHERE name = 'customPHPPathDir';"
$execSql -i $instance -c "$customPaths"

preferences="UPDATE civicrm_preferences SET mailing_format = '{contact.addressee}
{contact.supplemental_address_2}
{contact.street_address}
{contact.supplemental_address_1}
{contact.city}{, }{contact.state_province}{ }{contact.postal_code}', contact_autocomplete_options = '125' WHERE id = 1;"
$execSql -i $instance -c "$preferences"

$script_dir/manageCiviConfig.sh $instance

$drush $instance civicrm-update-cfg
$script_dir/fixPermissions.sh
$script_dir/clearCache.sh $instance
