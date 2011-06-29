<?php
require_once 'CRM/Core/Error.php';
require_once 'CRM/Utils/IMAP.php';

class CRM_IMAP_AJAX {
    private static $db = null;

    private static $server = "{webmail.senate.state.ny.us/imap/notls}";
    private static $user = 'crmdev';
    private static $pass = 'p9x64';

    private static function db() {
        if (self::$db == null)
            self::$db = new mysqli('localhost','root','windows','senate_c_sd99');
        return self::$db;
    }

    private static function get($key) {
        return self::db()->escape_string($_GET[$key]);
    }

    public static function message() {
        $id = self::get('id');
        $imap = new CRM_Utils_IMAP(self::$server, self::$user, self::$pass);
        $email = $imap->getmsg_uid($id);
        echo ($email->plainmsg) ? "<pre>{$email->plainmsg}</pre>" : $email->htmlmsg;
        CRM_Utils_System::civiExit();
    }

    public static function contacts() {
        $start = microtime(true);
        $s = self::get('s');
        $phone = self::get('phone');
        $city = self::get('city');
        $state_id = self::get('state');
        $first_name = self::get('first_name');
        $last_name = self::get('last_name');
        $street_address = self::get('street_address');
        $query = <<<EOQ
SELECT DISTINCT *
FROM civicrm_contact AS contact
  JOIN civicrm_address AS address ON contact.id=address.contact_id
  JOIN civicrm_state_province AS state ON address.state_province_id=state.id
  JOIN civicrm_phone as phone ON phone.contact_id=contact.id
WHERE contact.is_deleted=0
  AND state.id='$state_id'
  AND address.city LIKE '%$city%'
  AND contact.first_name LIKE '%$first_name%'
  AND contact.last_name LIKE '%$last_name%'
  AND address.street_address LIKE '%$street_address%'
  AND phone.phone LIKE '%$phone%'
ORDER BY contact.sort_name
EOQ;
        $result = self::db()->query($query);
        while($row = $result->fetch_assoc())
            $results[] = $row;

        echo json_encode(array_values($results));
        $end = microtime(true);
        if(self::get('debug')) echo $end-$start;
        CRM_Utils_System::civiExit();
    }

}