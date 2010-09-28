For installation instructions, see http://www.amfphp.org/docs

CHANGELOG 

MS2 01/07/2005

 - Added new methodTable option (per method) "fastArray" => true|false for 
   fast array serializing on return (will only make a difference for large 
   multidimensional nested voodoo arrays)
 - Added new method Headers::getHeader($key) available from all services, 
   also HeadersFilter.php added
 - Added FrontBase support
 - Added Pear::db support
 - Added CSV-based recordsets support (to use, set returns => "csv recordset" 
   in methodTable and return an associative array containing keys 
   "cols" => array("colname1", "colname2") and "filename" => "filename.csv")
 - Renamed sql folder to adapters to fit with the CSV recordsets
 - Various bugfixes for PHP4 MethodTable class
 - Major overhaul of service browser, should work much better now
 - New actionscript template system for service browser, see browser/templates/ for examples
 - Added new return type binary that will write the value as a string but without charsetHandling
 - Added new return type raw that will write to the output stream directly (careful)
 - SSL with ie hopefully works now
 - Bugfixes

MS2 07/07/2005

 - SSL with IE issue confirmed working
 - Removed NetDebug::trace calls in debugging fastArray

MS2 07/21/2005

 - Completely reimplemented util/MethodTable using PHP tokenizer
 - Added PDO adapter
 - Added automatic PEAR::DB and PDO recognition
 - Fixed trigger_error in PHP4

MS2 07/22/2005

 - Tweaks to generated code
 - Further improvements in MethodTable.php
 
MS2 97/08/2005

 - non user-errors in PHP5 return correct line number
 - PHP5 root error is returned instead of last error if there are multiple 
   exceptions in exceptionStack
 - PHP5 "code" attribute in errors returned correctly
 - unserialized object references return "(unresolved object #n)"
   instead of null, change the name of readFlushedSO to readReference and
   thus solved the mistery of type 0x07
 - Protection for circular references in serializer (only works with 
   objects)
   
MS2 10/09/2005

 - Security enhancement for _authenticate: AMFPHP replaces credentials with a cleared header
   after each call to setCredentials, so username and password are passed only once
   when using authentication. Note: this behaviour is different from previous behaviour
   and may break some installations
 - New test suite actionscript generation in browser for test purposes
 - New, better IE SSL support

MS2 16/09/2005

 - Bug fix: strlen() > 2^16 won't break serializer 
   (strings are truncated to 64000 characters. Type as XML if you require longer strings)
 - new gateway methods: logIncomingMessages and logOutgoingMessages
 - Sample deserializer file in extra folder for reverse engineering / debugging purposes
 - Tweaks to service browser to handle custom AMFPHP installation situations
 - Changed ReplaceGatewayUrl to AppendToGatewayUrl so https won't redirect to http
 - New gateway methods: addAdapterMapping for classes -> recordset mapping

MS3 23/09/2005
 
 - Automatic typing of MySQLi oo-style results
 - Solved uncaught exception if attempting to load a service in a folder that doesn't exist
 - Introduced possibility of using MethodTable::create(__FILE__)
 - More verbose errors in MethodTable::create
 - Added support for type 0x0C, long string, no need to type as XML anymore
 - Refined MixedArray (0x08) handling
 
MS3 05/10/2005
 
  - Custom classes return correct case if "returns" is set
  - Templating system does not rely on relative paths
  - Corrected syntax error in ODBC adapter
  
MS3 11/10/2005
 
  - Deactivated logIncomingMessages and logOutgoingMessages in gateway.php
  
MS3 09/11/2005
  
  - Corrected notices in php4Exception.php, and stoppeed logging to a hard-coded filename
  - Put calls to translitereate in Gateway.php before an if on content
    so any charset errors should show up on opening gateway.php
  - Added long string support for database adapters
  
1.0 25/12/2005

  - docs committed
  - site updated
  - second SSL method added
  - issues with null arrays solved
  
1.0.1 07/01/2006

  - Issue with returns and pageable recordsets solved
  - Issue with MethodTable::create and @returns with multiple words solved
  - Cleaned up IE SSL handling
  - Automatic typing of common XML types (domxml, simpleXML)
  - PHP4 errors should work beyond the first error
  - XML return types are stripped of whitespace
  
1.1.0 30/01/2006

  - Introduction of the debuggateway.php, the final nail in the coffin 
    of NetConnection.Call.BadVersion
  - Various bugfixes

1.1.1 30/01/2006

  - Debug gateway now forwards session id so sessions work properly

1.1.2 02/02/2006

  - Debug gateway should find regular gateway properly if not installed in root
  
1.2.0 beta 26/02/2006

  - Major overhaul of the service browser, now features test capability
  - change of directory structure to amf-core, services and browser
  - cleanup of dead code, amf-core now takes 40% less disk space
  - Mods to recordset adapters to reset recordset before serializing
  - Comprehensive class mapping
  
1.2.0 25/03/2006

  - A bunch of bug fixes, such as:
  - Fixed AMF corruption issues with outputted dates
  - Fixed PHP5 SOAP support
  - Standardized error codes: AMFPHP_RUNTIME_ERROR for runtime errors, AMFPHP_AUTH_MISMATCH for
    login auth errors, etc.
  - throw new Exception("error", CODE) now overrides AMFPHP_RUNTIME_ERROR for custom
    error codes
  - Fixed bugs in PEAR::DB and oracle drivers
  - Service browser now works without cURL thanks to PEAR libraries
  - Changed error output so it looks more like ColdFusion Remoting errors
  - Made actions and filters not classes anymore since they never used members for much 
    reduced resource usage
  - Added new code types in service browser, including ARP, ability to save code
  - Ability to save methodTable from service browser
  - Updated docs
  - Restricted AMFPHP globals to the $GLOBALS['amfphp'] array, presets now in app/Globals.php
  - Fixed pageable recordset support
  - New reusable AMFClient class in browser/client
  
1.2.4

  - Corrected unclosed comment in ARP code generator
  
1.2.5

  - Fixed issue with getClassPath
  - Fixed MethodTable not recognizing encapsulated variables
  - Modified ServiceBrowser for private/public methods
  - Fixed bug in class loader for class name finding with packages
  - Added correct typing for AdoDB with MySQL databases