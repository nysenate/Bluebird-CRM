<?php

DEFINE('RAYCIVIPATH','/data/senateProduction/modules/civicrm/');

DEFINE('RAYDEBUG', true);

DEFINE ('RAYIMPORTDIR', '/data/importData/');
DEFINE('RAYROOTDOMAIN',".crm.nysenate.gov");
DEFINE('RAYROOTDIR',"/data/www/nyss/");

DEFINE ('RAYTMP', '/tmp/');

DEFINE('SOLRDEBUG', false);
DEFINE('SOLRUPDATE',1);
DEFINE('SOLRDELETE',2);
DEFINE('SOLRDELETEALL',3);
DEFINE('SOLRURL','http://localhost:8180/solr/update');

//email address of the contact to file unknown emails against.
DEFINE('UNKNOWNCONTACTEMAIL','unknown.contact@nysenate.gov');

$aIncomingIMAPAccount["sd99"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => false,
        'login' =>'sd99crm',
        'password' => 'p6t94',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["addabbo"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmaddabbo',
        'password' => 'p7f39',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["cjohnson"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmcjohnson',
        'password' => 'p5c56',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["krueger"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmkrueger',
        'password' => 'p8m63',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["mcdonald"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmmcdonald',
        'password' => 'p9p37',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["oppenheimer"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmoppenheimer',
        'password' => 'p9f47',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["savino"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmsavino',
        'password' => 'p4d67',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["seward"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmseward',
        'password' => 'p6h77',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["smith"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmsmith',
        'password' => 'p5j84',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

$aIncomingIMAPAccount["valesky"][] = array(
        'server'=>'webmail.senate.state.ny.us',
        'mailbox'=>'INBOX',
        'archiveMailbox'=>'archive',
        'processUnreadOnly'=> true,
        'moveMailToArchive' => true,
        'login' =>'crmvalesky',
        'password' => 'p5z43',
        'imapOpts' => '/imap/ssl/notls',
        'customHandler' => null
);

?>
