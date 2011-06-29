<?php

require_once 'CRM/Core/Page.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/Core/Form.php';

class CRM_IMAP_Page_Mapper extends CRM_Core_Page {

    private static $server = "{webmail.senate.state.ny.us/imap/notls}";
    private static $user = 'crmdev';
    private static $pass = 'p9x64';

    function run() {
        //Fetch the IMAP Headers
        $conn = imap_open(self::$server ,self::$user,self::$pass);
        $ids = imap_search($conn,"ALL",SE_UID);
        $headers = imap_fetch_overview($conn,implode(',',$ids),FT_UID);
        foreach($headers as $header) {
            if( in_array($header->uid,$ids)) {

                //Clean up the date to Mon DD, YYYY format
                $date_parts = explode(' ',$header->date);
                $header->date_fmt = "{$date_parts[2]} {$date_parts[1]}, {$date_parts[3]}";

                //Parse out the name and email portions of the from header argument
                //Generally these fall into one of the two forms
                //    crmdev@nysenate.gov
                //    CRM Dev <crmdev@nysenate.gov>
                $from_parts = explode(' ',$header->from);
                if(count($from_parts)==1) {
                    $header->from_email = $header->from;
                    $header->from_name = '';
                } else {
                    $header->from_email = str_replace(array('<','>'),'',array_pop($from_parts));
                    $header->from_name = implode(' ',$from_parts);
                }

                $messages[$header->uid] = $header;
            }
        }
        //Build the filter form
        $form = new CRM_Core_Form();
        $form->addElement('text','first_name','First Name');
        $form->addElement('text','last_name','Last Name');
        $form->addElement('text','city','City');
        $form->addElement('text','phone','Phone Number');
        $form->addElement('text','street_address','Street Address');

        //Assign the variables
        $this->assign('messages',$messages);
        $this->assign( 'form', $form->toSmarty());

        parent::run();
   }
}
