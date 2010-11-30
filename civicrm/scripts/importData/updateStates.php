<?
function updateStates()
{

  $session =& CRM_Core_Session::singleton();

  //select the right notes
  $dao = &CRM_Core_DAO::executeQuery("SELECT entity_id, note from civicrm_note where entity_table='civicrm_contact' and subject like '% DATA';", CRM_Core_DAO::$_nullArray);

  cLog(0,'info',"updating states...");

  while ($dao->fetch()) {

        $details = $dao->note;
        $cID = $dao->entity_id;

//	 cLog(0,'info',"checking contact ID: $cID");

	$matches = array();
        preg_match_all("/STATE: (.*)/", $details, $matches);

	setState($cID, $matches, LOC_TYPE_HOME);

        preg_match_all("/ADDR_WORK_STATE: (.*)/", $details, $matches);
        setState($cID, $matches, LOC_TYPE_WORK);

  }
}

function setState($cID, $matches, $locType = LOC_TYPE_WORK) {

	global $aStates;

        foreach ($matches[1] as $match) {

                $state = $aStates[$match];

                if (!empty($state)) {

                        if ($match<>"NY") cLog(0,'info',"found non-NY state ID $state for $match, contact ID: $cID");

                        $dao1 = &CRM_Core_DAO::executeQuery("UPDATE civicrm_address set state_province_id = $state WHERE contact_id = $cID and location_type_id=$locType;", CRM_Core_DAO::$_nullArray);
                }
        }
}
?>
