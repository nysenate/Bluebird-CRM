<?

global $SC;

if (!defined(RAYDEBUG)) define(RAYDEBUG, false);
//load the default configs
//afterwards use overrides if specified (see cae statement)
 
//**********************************************************************************************
// DEBUG SETTINGS
//**********************************************************************************************

//set debug true/false for logging
$SC['debug']=false;
$SC['debug']=true;

//don't execute runCmd statements
$SC['noExec']=false;
//$SC['noExec']=true;


//**********************************************************************************************
// CONFIG SETTINGS
//**********************************************************************************************

//TAG file, contains master list of tags
$SC['tagFile'] = 'tags.csv';

//conmmon params. can override in config section
$SC['dbToHost']=$SC['dbHost']='cividb01';
$SC['dbToUser']=$SC['dbUser']='loadsenate';
$SC['dbToPassword']=$SC['dbPassword']='char12tree*!';
$SC['dbToCiviTablePrefix']=$SC['dbCiviTablePrefix'] = "civicrm_";
$SC['rootDir'] = $SC['toRootDir']  = "/data/www/";

$SC['httpauth'] = "loadsenate";
$SC['httppwd'] = "Agency4";

switch ($config) {

	case 'prodtodev':


                $SC['dbCiviPrefix'] = 'senate_c_';
                $SC['dbDrupalPrefix'] = 'senate_d_';
                $SC['drupalRootDir'] = "nyss/";
                $SC['templateDir'] = "/data/senateProduction/civicrmInstallTemplates/";
                $SC['rootDomain'] = ".crm.nysenate.gov";

		$SC['dbToCiviPrefix'] = 'senate_dev_c_';
		$SC['dbToDrupalPrefix'] = 'senate_dev_d_';
		$SC['toDrupalRootDir'] = "nyssdev/";
		$SC['toTemplateDir'] = "/data/senateDevelopment/civicrmInstallTemplates/";
		$SC['toRootDomain'] = ".dev.senate.rayogram.com";
		break;

        case 'devtoprod':


                $SC['dbCiviPrefix'] = 'senate_dev_c_';
                $SC['dbDrupalPrefix'] = 'senate_dev_d_';
                $SC['drupalRootDir'] = "nyssdev/";
                $SC['templateDir'] = "/data/senateDevelopment/civicrmInstallTemplates/";
                $SC['rootDomain'] = ".dev.senate.rayogram.com";

                $SC['dbToCiviPrefix'] = 'senate_c_';
                $SC['dbToDrupalPrefix'] = 'senate_d_';
                $SC['toDrupalRootDir'] = "nyss/";
                $SC['toTemplateDir'] = "/data/senateProduction/civicrmInstallTemplates/";
                $SC['toRootDomain'] = ".crm.nysenate.gov";
                break;

        case 'crmtocrm2':


                $SC['dbCiviPrefix'] = 'senate_c_';
                $SC['dbDrupalPrefix'] = 'senate_d_';
                $SC['drupalRootDir'] = "nyss/";
                $SC['templateDir'] = "/data/senateProduction/civicrmInstallTemplates/";
                $SC['rootDomain'] = ".crm.nysenate.gov";

                $SC['dbToCiviPrefix'] = 'senate_c_';
                $SC['dbToDrupalPrefix'] = 'senate_d_';
                $SC['toDrupalRootDir'] = "nyss/";
                $SC['toTemplateDir'] = "/data/senateProduction/civicrmInstallTemplates/";
                $SC['toRootDomain'] = ".crm2.nysenate.gov";
                break;

        case 'prod':

                $SC['dbCiviPrefix'] = $SC['dbToCiviPrefix'] = 'senate_c_';
                $SC['dbDrupalPrefix'] = $SC['dbToDrupalPrefix'] = 'senate_d_';
                $SC['drupalRootDir'] = $SC['toDrupalRootDir'] = "nyss/";
                $SC['templateDir'] = $SC['toTemplateDir'] = "/data/senateProduction/civicrmInstallTemplates/";
                $SC['rootDomain'] = $SC['toRootDomain'] = ".crm.nysenate.gov";
                $SC['installDir'] = $SC['toInstallDir'] = "/data/senateProduction";
		break;

        case 'dev':

                $SC['dbCiviPrefix'] = $SC['dbToCiviPrefix'] = 'senate_dev_c_';
                $SC['dbDrupalPrefix'] = $SC['dbToDrupalPrefix'] = 'senate_dev_d_';
                $SC['drupalRootDir'] = $SC['toDrupalRootDir'] = "nyssdev/";
                $SC['templateDir'] = $SC['toTemplateDir'] = "/data/senateDevelopment/civicrmInstallTemplates/";
                $SC['rootDomain'] = $SC['toRootDomain'] = ".dev.senate.rayogram.com";
                $SC['installDir'] = $SC['toInstallDir'] = "/data/senateDevelopment";
                break;

	default:
		die("\n\nrequires a valid configuration\n\n");
		break;	
}

//**********************************************************************************************
// DO NOT USUALLY EDIT BELOW THIS LINE
//**********************************************************************************************

//some shell variables
$SC['mysql'] = "mysql -u{$SC['dbUser']} -p{$SC['dbPassword']} -h{$SC['dbHost']}";
$SC['mysqlTo'] = $SC['mysql'];

$SC['mysqldump'] = "mysqldump -v -u{$SC['dbUser']} -p{$SC['dbPassword']} -h{$SC['dbHost']}";
$SC['mysqldumpTo'] = "mysqldump -v -u{$SC['dbToUser']} -p{$SC['dbToPassword']} -h{$SC['dbToHost']}";

$SC['tmp'] = "/tmp/";
$SC['copy'] = "cp";

if ($SC['debug']) error_reporting(E_ALL);
if ($SC['debug']) ini_set("display_errors", 1);

?>
