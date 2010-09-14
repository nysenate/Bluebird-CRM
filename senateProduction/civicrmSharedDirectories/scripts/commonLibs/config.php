<?php

define('RAYCIVIPATH', '/data/senateProduction/modules/civicrm/');
define('RAYDEBUG', true);
define('RAYIMPORTDIR', '/data/importData/');
define('RAYROOTDOMAIN', ".crm.nysenate.gov");
define('RAYROOTDIR', "/data/www/nyss/");
define ('RAYTMP', '/tmp/');

define('SOLRDEBUG', false);
define('SOLRUPDATE', 1);
define('SOLRDELETE', 2);
define('SOLRDELETEALL', 3);
define('SOLRURL', 'http://localhost:8180/solr/update');

//email address of the contact to file unknown emails against.
define('UNKNOWNCONTACTEMAIL', 'unknown.contact@nysenate.gov');

// Mailbox settings common to all CRM instances
define('IMAP_SERVER', 'webmail.nysenate.gov');
define('IMAP_MAILBOX', 'INBOX');
define('IMAP_ARCHIVEBOX', 'archive');
define('IMAP_PROCESS_UNREAD_ONLY', true);
define('IMAP_MOVE_MAIL_TO_ARCHIVE', false);
define('IMAP_OPTS', '/imap/ssl/notls');
define('IMAP_CUSTOM_HANDLER', null);

?>
