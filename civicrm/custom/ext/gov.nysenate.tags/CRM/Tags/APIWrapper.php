<?php

/**
 * Class CRM_Tags_APIWrapper
 *
 * Originally created to support leg position selection in contact record
 * Unused as we are handling the API configuration via the entityRef hook
 * Leaving in place in case we need to resurrect at some point...
 *
 * Update by Nate Frank 10/6/2025
 * Resurrected! The custom entity type was causing some problems on the case
 * management page. Logic from the custom entity type has been implemented here.
 * NYSS #17149
 */
#[CRM_NYSS_Attribute_IssueRefs('17149')]
class CRM_Tags_APIWrapper {
  /**
   * Callback to wrap completetransaction API calls.
   */
  public static function PREPARE($event) {
    $request = $event->getApiRequestSig();
    //Civi::log()->debug(__FUNCTION__, ['request' => $request]);

    switch ($request) {
      // Wrap completetransaction in the v3 API.
      // Doesn't exist yet in the v4 API.
      case '3.tag.get':
        $event->wrapAPI(['CRM_Tags_APIWrapper', 'completeTransaction']);
        break;
      case '3.tag.getlist':
        $event->wrapAPI(function($apiRequest, $continue){
          $params = $apiRequest['params'] ?? [];
          $parent_id = self::getParentIdParam($apiRequest);
          //Civi::log()->debug('just seeing if Im here', [$apiRequest['params']]);
          if ($parent_id == 292) {
            if (!empty($params['id'])) {
              // with an id parameter, we want to find those entries.
              // So, allow the API query to do a normal query.
              return $continue($apiRequest);
            } else {
              // otherwise, we skip the default query altogether and
              // build our own list in RESPONSE from OpenLeg
              return civicrm_api3_create_success([]);
            }
          } else {
            // proceed as normal without intervention
            return $continue($apiRequest);
          }
        });
    }
  }

  public static function RESPOND($event) {
    $request = $event->getApiRequestSig();
    $apiRequest = $event->getApiRequest();

    switch($request) {
      case '3.tag.getlist':
        $params = $apiRequest['params'] ?? [];
        $parent_id = self::getParentIdParam($apiRequest);
        if ($parent_id == 292) {
          $result= $event->getResponse();
          self::positions_getlist($params,$result);
          //Civi::log()->debug('Result', [$result]);
          // save modified response
          $event->setResponse($result);
        }
        break;
      default:
        break;
    }
  }

  public static function getParentIdParam($apiRequest) {
    // I'm seeing the parent ID in different places,
    // depending on the source of the API request.
    return $apiRequest['params']['params']['parent_id'] ?? $params['parent_id'] ?? 0;
  }
  /**
   * <insert appropriate docs here>
   * @param array $apiRequest
   * @param array $callsame - function callback see \Civi\Api\Provider\WrappingProvider
   *
   * #14336
   */
  public static function completeTransaction($apiRequest, $callsame) {
    //Civi::log()->debug(__FUNCTION__, ['$apiRequest' => $apiRequest]);

    if (!empty($apiRequest['params']['name']['LIKE'])
      && !str_starts_with($apiRequest['params']['name']['LIKE'], '%')
    ) {
      $apiRequest['params']['name']['LIKE'] = '%'.$apiRequest['params']['name']['LIKE'];
    }

    //fix for tag search for numerical values
    if (!empty($apiRequest['params']['options']['offset']) &&
      $apiRequest['params']['options']['offset'] < 0
    ) {
      $apiRequest['params']['options']['offset'] = 0;
    }

    return $callsame($apiRequest);
  }

  /**
   * @param $params
   * Gets leg Position tags from OpenLeg.
   * @return array
   */
  public static function positions_getlist($params, &$result): void {
    //if $param['input'] is passed, we are doing a lookup
    //if $param['input'] is not passed and IDs are passed, we restructure into the appropriate format to pass back

    if (!empty($params['input'])) {
      $values = new ArrayObject();
      if (is_a($result , 'Civi\Api4\Generic\Result')) {
        // not sure if this would ever be an issue or not
        $values = $result; // Civi\Api4\Generic\Result is an ArrayObject
      } else if (is_array($result) && array_key_exists('values', $result)) {
        $values = new ArrayObject($result['values']);
      }
      try {
        self::getLegPositions($params['input'], $values);
        $result['count'] = $values->count();
        $result['values'] = $values->getArrayCopy();
      } catch(Exception $e) {
        $result['is_error'] = 1;
      }
    }
  }

  /**
   * helper function to get leg positions
   * IDs are hardcoded as this is a very unique requirement and not
   * likely reusable in other contexts
   */
  public static function getLegPositions($input, ArrayObject &$values) {
    get_bluebird_instance_config();
    $input = CRM_Utils_Type::escape($input, 'String');
    $tags = [];

    /*
     * NYSS leg positions should retrieve list from OpenLegislation
     * and create value in tag table.
     */
    require_once 'CRM/NYSS/BAO/Integration/OpenLegislation.php';
    $bills = CRM_NYSS_BAO_Integration_OpenLegislation::getBills($input);
    $billcnt = is_iterable($bills) ? count($bills) : 0;

    if ($bills === null) {
      throw new CRM_Core_Exception('Unable to fetch bills from OpenLegislation');
    }

    $query = "SELECT id, name FROM civicrm_tag WHERE parent_id = 292";
    $tag_dao = CRM_Core_DAO::executeQuery($query);
    $existing_tags = $tag_dao->fetchAll();

    for ($j = 0; $j < $billcnt; $j++) {
      $billName = $bills[$j]['id'];
      $billSponsor = '';
      if (isset($bills[$j]['sponsor'])) {
        $billSponsor = $bills[$j]['sponsor'];
        $billName .= " ($billSponsor)";
      }

      //construct positions
      $billTags = array($billName, "$billName: SUPPORT", "$billName: OPPOSE");

      //construct tags array
      foreach ($billTags as $billTag) {
        // Do lookup to see if tag exists in system already,
        // else construct using standard format
        // NYSS 4315 - escape position tag name
        $exists = false;
        $tagID = NULL;// existing id
        foreach($existing_tags as $e_tag) {
          if ($e_tag['name'] === $billTag) {
            $exists = true;
            $tagID = $e_tag['id'];
            break;
          }
        }

        // When the tag doesn't already exist, :::value is a
        // local convention that will be caught elsewhere and the tag
        // will be created.
        if (!$exists || $tagID == NULL) {
          $tagID = $billTag.':::value';
        }

        // appends to $values parameter
        $values->append([
          'label' => $billTag,
          'id' => $tagID,
          'sponsor' => $billSponsor
        ]);
      }//end foreach
    }
  }

}
