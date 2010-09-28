<?php
	/*
	$Id: gateway.php,v 1.4 2005/07/05 07:40:54 pmineault Exp $
	
	The gateway is a customized entry point to your flash
	services. 
	
	Things you can set here:
	
	  - setBaseClassPath(string path) The absolute path to your services on the server

	  - setLooseMode(bool mode) If true, output buffering is enabled and error_reporting
		is lowered to circumvent a number of documented NetConnection.BadVersion errors

	  - setCharsetHandler(string mode, string phpCharset, string sqlCharset)

		mode can be one of
		  - none        don't do anything
		  - iconv       uses the iconv libray for reencoding
		  - mbstring    uses the mbstring library for reencoding
		  - recode      uses the recode library for reencoding
		  - utf8_decode uses the XML function utf8_decode and encode for
							reencoding - ISO-8859-1 only

		phpCharset is the charset that the system assumes the PHP strings will be in.

		sqlCharset is the charset of sql result sets used (only when outputting results
		  to flash client)

		wsCharset (web service charset) has been eliminated from this release, UTF-8 
		is assumed as the remote encoding. When using PHP5 SoapClient, the SoapClient 
		object will be initialized with "encoding" => phpCharset. When using nusoap, 
		soapclient->soap_defencoding will be initialized with phpCharset.

		The following settings are recommended (try the first setting appropriate for 
		your language, if it doesn't work try the second):
		
		* English:
		
			$gateway->setCharsetHandler( "none", "ISO-8859-1", "ISO-8859-1" );
			
		* Western european languages (French, Spanish, German, etc.):
		
			$gateway->setCharsetHandler( "iconv", "ISO-8859-1", "ISO-8859-1" );
			$gateway->setCharsetHandler( "utf8_decode", "ISO-8859-1", "ISO-8859-1" );
			
		* Eastern european languages (Russian and other slavic languages):
		
			$gateway->setCharsetHandler( "none", "ISO-8859-1", "ISO-8859-1" );
			$gateway->setCharsetHandler( "iconv", "your codepage", "your codepage" );
			
		* Oriental languages (Chinese, japanese, korean):
		
			$gateway->setCharsetHandler( "none", "ISO-8859-1", "ISO-8859-1" );
			$gateway->setCharsetHandler( "iconv", "big5", "big5" );
			$gateway->setCharsetHandler( "iconv", "CP950", "CP950" );
			$gateway->setCharsetHandler( "iconv", "Shift_JIS", "Shift_JIS" );
			$gateway->setCharsetHandler( "iconv", "CP932", "CP932" );
			$gateway->setCharsetHandler( "iconv", "CP949", "CP949" );
			
		* Other languages:
		
			$gateway->setCharsetHandler( "none", "ISO-8859-1", "ISO-8859-1" );
			
		See all the possible codepages for iconv here:
			
		http://www.gnu.org/software/libiconv/
		
		iconv is included by default in php5, but not in php4 although most
		hosts have it installed. utf8_decode is of some use for Western European languages,
		but please remember that it won't work with settings other than ISO-8859-1.
		The other methods also require seldom-used extensions but were included 
		just in case your particular host only supports them.
			
	  - setWebServiceHandler(string handler)
		Handler can be one of:
		  - php5 (that is, PHP5 SoapClient)
		  - pear
		  - nusoap
		This is used for webservices when working with http:// service names in
		new Service(). For php5 and pear, you will need to have it installed on your 
		server. For nusoap, you need nusoap.php instead in ./lib relative to this file.
		 
		If you have PHP5 and the SOAP extension installed it is highly recommended that
		you use it as it is _much_ faster than NuSOAP or PEAR::SOAP
		
	Things you may want to disable for production environments:
	
	  - disableStandalonePlayer()
		Disables the standalone player by filtering out its User-Agent string
		
	  - disableServiceDescription()
		Disable service description from Macromedia's service browser
	  
	  - disableTrace()
		Disables remote tracing
		
	  - disableDebug()
		Stops debug info from being sent (independant of remote trace setting)

	*/

	include "amf-core/app/Gateway.php";
	
	//You can set this constant appropriately to disable traces and debugging headers
	//You will also have the constant available in your classes, for changing
	//the mysql server info for example
	define("PRODUCTION_SERVER", false);

	$gateway = new Gateway();
	
	//Set where the services classes are loaded from, *with trailing slash*
	$gateway->setBaseClassPath("services/");
	
	//Loose mode means echo'ing or whitespace in your file won't make AMFPHP choke
	$gateway->setLooseMode(true);
	
	//Read above large note for explanation of charset handling
	//The main contributor (Patrick Mineault) is French, 
	//so don't be afraid if he forgot to turn off iconv by default!
	//$gateway->setCharsetHandler("utf8_decode", "ISO-8859-1", "ISO-8859-1");
	
	//Error types that will be rooted to the NetConnection debugger
	$gateway->setErrorHandling(E_ALL ^ E_NOTICE);
	
	//choices are php5 (SoapClient), nusoap and pear
	//If you don't plan on using web services with AMFPHP, 
	//you can safely let this setting alone
	//Note that for nusoap to work you MUST place the library under /amf-core/lib/nusoap.php
	$gateway->setWebServiceHandler('php5');

	//Adding an adapter mapping will make returns of the mapped typed be intercepted
	//and mapped in adapters/%adapterName%Adapter.php. This works by using get_class
	//So for example, if you return a PEAR resultset object, it is an instance of DB_result
	//And we want this to be processed as a recordset in adapters/peardbAdapter.php,
	//hence the following line:
	$gateway->addAdapterMapping('db_result', 'peardb');
	//For PDO (PHP 5.1 specific)
	$gateway->addAdapterMapping('pdostatement', 'pdo');
	//For oo-style MySQLi
	$gateway->addAdapterMapping('mysqli_result', 'mysqli');
	//For filtered array 
	//And for filtered typed array (see adapters/lib/Arrayf.php and Arrayft.php)
	$gateway->addAdapterMapping('arrayf', 'arrayf');
	$gateway->addAdapterMapping('arrayft', 'arrayft');
	//And you can add your own after this point... (note lowercase for both args!)
	
	if(PRODUCTION_SERVER)
	{
		//Disable trace actions
		$gateway->disableTrace();
		
		//Disable debugging headers
		$gateway->disableDebug();
		
		//Disable Service description
		$gateway->disableServiceDescription();
	}
	
	//If you are running into low-level issues with corrupt messages and 
	//the like, you can add $gateway->logIncomingMessages('path/to/incoming/messages/');
	//and $gateway->logOutgoingMessages('path/to/outgoing/messages/'); here
	
    // $gateway->logIncomingMessages('/tmp/');
    // $gateway->logOutgoingMessages('/tmp/');

	//If using under SSL (https) with IE6, usually the default amfphp method will 
	//work. It is however possible that it doesn't, in which case, enable the 
	//second SSL method, and apply the patch described here:
	//http://www.gmrweb.net/2005/08/18/flash-remoting-https-internet-explorer/
	//(requires mod_headers)
	//$gateway->useSslSecondMethod();
	
	//This is new to AMFPHP 1.2: 
	//Set custom class mappings (kept in a second config file for convenience)
	include_once('advancedsettings.php');
	
	//Service now
	$gateway->service();

?>
