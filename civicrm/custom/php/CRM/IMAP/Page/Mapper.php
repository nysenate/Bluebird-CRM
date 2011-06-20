<?php

require_once 'CRM/Core/Page.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/IMAP/Form/Filter.php';

class CRM_IMAP_Page_Mapper extends CRM_Core_Page {

    function run() {
        $start = microtime(true);
        $server = "{webmail.senate.state.ny.us/imap/notls}";
        $conn = imap_open($server ,'crmdev','p9x64');
        $ids = imap_search($conn,"ALL",SE_UID);
        $headers = imap_fetch_overview($conn, implode(',',$ids),FT_UID);
        foreach($headers as $header) {
            $date_parts = explode(" ",$header->date);
            $messages[$header->uid] = array(
                    'date' => implode(' ',array($date_parts[0],$date_parts[2],$date_parts[1])),
                    'subject' => $header->subject,
                    'uid' => $header->uid,
                    'from' => $header->from,
                );
        }
        $end = microtime(true);
        CRM_Core_Error::debug('IMAP Layer', $end-$start);

        $start = microtime(true);
        $db = new mysqli('localhost','root','windows','senate_c_sd99');
        $result = $db->query(<<<ENDQUERY
SELECT
  state.id AS state_id,
  state.name AS state_name,
  contact.first_name AS first_name,
  contact.last_name AS last_name,
  contact.id AS id,
  address.city AS city
FROM civicrm_contact AS contact
  JOIN civicrm_address AS address
    ON address.contact_id = contact.id
  LEFT JOIN civicrm_state_province AS state
    ON address.state_province_id=state.id
WHERE contact.is_deleted=0
ORDER BY state.name
ENDQUERY
);
        while($row = $result->fetch_assoc()) {
            $contacts[] = $row;
            $first_names[] = $row['first_name'];
            $last_names[] = $row['last_name'];
            $city_names[] = $row['city'];
            $state_options[$row['state_id']] = $row['state_name'];
        }
        $first_names_clean = array_values(array_unique(array_filter($first_names)));
        $last_names_clean = array_values(array_unique(array_filter($last_names)));
        $city_names_clean = array_unique(array_filter($city_names));
        sort($first_names_clean,SORT_STRING);
        sort($last_names_clean,SORT_STRING);
        sort($city_names_clean,SORT_STRING);
        $end = microtime(true);
        CRM_Core_Error::debug('Mysqli Layer', $end-$start);

        $this->assign('messages',$messages);
        $this->assign('contacts',$contacts);
        $this->assign('first_names',json_encode($first_names_clean));
        $this->assign('last_names',json_encode($last_names_clean));
        $this->assign('city_names',json_encode($city_names_clean));

        $form = new CRM_Core_Form();
        $form->addElement('text','first_name','First Name');
        $form->addElement('text','last_name','Last Name');
        $form->addElement('text','city','City');
        $form->addElement('select','state','State',$state_options);
        $form->setDefaults(array(
        		'state'=>array('1031') //New York
            ));
        $this->assign( 'form', $form->toSmarty());

        parent::run();

        /* Using DB_DataObject
        require_once 'DB/DataObject.php';
        $start = microtime(true);
        $db = new DB_DataObject();
        $db->query("SELECT *
        			FROM civicrm_contact
        			WHERE is_deleted=0");
        while($db->fetch()) {
            //$contacts[] = $db->toArray();
        }
        $end = microtime(true);
        CRM_Core_Error::debug('DB_DataObject Layer', $end-$start);
		*/
        /* Using DOA_Contact
        require_once 'CRM/Contact/DAO/Contact.php';
        $start = microtime(true);
        $contact = new CRM_Contact_DAO_Contact();
        $contact->is_deleted=0;
        $contact->find();
        while($contact->fetch())
            //$contacts[] = $contact->toArray();
            $contacts[] = clone $contact;
        $end = microtime(true);
        CRM_Core_Error::debug('DOA Layer',$end-$start);
        */
   }
}
