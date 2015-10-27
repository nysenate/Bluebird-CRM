<?php
/*
 * Project: BluebirdCRM
 * Author: Ken Zalewski
 * Organization: New York State Senate
 * Date: 2015-10-27
 */

class CRM_NYSS_BAO_Integration_OpenLegislation
{
  // Sort bills by year (descending), and by bill number (ascending)
  static function compareBills($a, $b)
  {
    $billid1 = $a['id'];
    $billid2 = $b['id'];
    list($billno1, $year1) = explode('-', $billid1);
    list($billno2, $year2) = explode('-', $billid2);

    if ($year1 < $year2) {
      return 1;
    }
    else if ($year1 > $year2) {
      return -1;
    }
    else if ($billno1 < $billno2) {
      return -1;
    }
    else if ($billno1 > $billno2) {
      return 1;
    }
    else {
      return 0;
    }
  } // compareBills()


  /**
   *  getBills() - get an array of bills that match a full or partial bill ID
   *
   *  @param billId An OpenLeg bill ID, or partial bill ID, of the
   *         form <printNo>-<sessionYear>
   *  @return an array of bills where each entry is an associative array
   *          containing a bill ID and a sponsor, or NULL if an error occurs
   **/
  static function getBills($billId)
  {
    $bbcfg = get_bluebird_instance_config();
    $apiKey = $apiBase = '';
    if (isset($bbcfg['openleg.api.key'])) {
      $apiKey = $bbcfg['openleg.api.key'];
    }
    if (isset($bbcfg['openleg.api.base'])) {
      $apiBase = $bbcfg['openleg.api.base'];
    }

    if (empty($apiKey) || empty($apiBase)) {
      return null;
    }

    $billIdParts = explode('-', $billId);
    $billNo = $billIdParts[0];
    if (count($billIdParts) > 1) {
      $billPattern = $billNo;
      $sessYear = $billIdParts[1];
      $sessPattern = (strlen($sessYear) == 4) ? $sessYear : '*';
    }
    else {
      $billPattern = $billNo.'*';
      $sessPattern = '*';
    }

    // NYSS 6990 - ORDER BY year DESC as current bills are more relevant.
    // NYSS 8819 - increase pageSize to 100 to accommodate cases where the
    //             exact matching billno is not in the first 10 items
    // NYSS 9575 - Modify API call for OpenLegislation 2.0
    $query = urlencode("basePrintNo:$billPattern AND session:$sessPattern");
    $target_url = "$apiBase/bills/search/?key=$apiKey&full=false&term=$query&sort=basePrintNo:asc,session:desc&limit=10";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $json = curl_exec($ch);
    curl_close($ch);

    if ($json === false) {
      return null;
    }

    $json = json_decode($json, false);
    if ($json === null) {
      return null;
    }

    // Extract the billId (print#-year) and sponsor from each matching bill.
    $bills = array();
    foreach ($json->result->items as $item) {
      $bills[] = array(
        'id' => $item->result->printNo.'-'.$item->result->session,
        'sponsor' => $item->result->sponsor->member->shortName
      );
    }

    // No need to use compareBills() to sort, since ElasticSearch is doing it
    //usort($bills, array('CRM_NYSS_BAO_Integration_OpenLegislation', 'compareBills'));

    return $bills;
  } // getBills()


  /**
   *  getBillSponsor - get the sponsor of the given bill
   *
   *  @param billId A full OpenLeg bill ID of the form <printNo>-<sessionYear>
   *  @return the name of the sponsor of the bill
   **/
  static function getBillSponsor($billId)
  {
    $bills = self::getBills($billId);
    if ($bills == null) {
      return null;
    }

    return $bills[0]['sponsor'];
  } // getBillSponsor()
}
