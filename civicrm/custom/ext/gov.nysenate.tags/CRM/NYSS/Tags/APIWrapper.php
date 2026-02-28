<?php

/**
 * Class CRM_NYSS_Tags_APIWrapper
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
#[CRM_NYSS_Attribute_IssueRefs('17149', '17833')]
class CRM_NYSS_Tags_APIWrapper {
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
        $event->wrapAPI(['CRM_NYSS_Tags_APIWrapper', 'completeTransaction']);
        break;
      case '3.tag.getlist':
        $event->wrapAPI(function($apiRequest, $continue){
          $params = $apiRequest['params'] ?? [];
          $parent_id = self::getParentIdParam($apiRequest);
          if ($parent_id == CRM_NYSS_Tags_Constants::POSITIONS_TAG_ID) {
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
        break;
        // NYSS #17833 - not solely, but as a result of this ticket
      case '4.entitytag.create':
          $event->wrapAPI(function($apiRequest, $continue){
              $values = $apiRequest->getValues();
              $tag_id = self::preprocessPositionTags($values['tag_id'] ?? '');
              // Now update $apiRequest['values]['tag_id'] with ID,
              // which might be changed by preprocessPositionTags
              $values['tag_id'] = $tag_id;
              $apiRequest->setValues($values);
              return $continue($apiRequest);
          });
          break;
          // NYSS #17833 - not solely, but as a result of this ticket
      case '4.entitytag.save':
          $event->wrapAPI(function($apiRequest, $continue){
              $records = $apiRequest->getRecords();
              for($i=0; $i < sizeof($records); $i++) {
                  $tag_id = self::preprocessPositionTags($records[$i]['tag_id'] ?? '');
                  // Now update record with new tag_id,
                  // which might be changed by preprocessPositionTags
                  $records[$i]['tag_id'] = $tag_id;
              }
              $apiRequest->setRecords($records);
              return $continue($apiRequest);
          });
          break;
    }
  }

  public static function RESPOND($event) {
    $request = $event->getApiRequestSig();
    $apiRequest = $event->getApiRequest();

    switch($request) {
      case '3.tag.getlist':
        $params = $apiRequest['params'] ?? [];
        $parent_id = self::getParentIdParam($apiRequest);
        if ($parent_id == CRM_NYSS_Tags_Constants::POSITIONS_TAG_ID) {
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
     * When given the special ":::value" tag ID, it creates the associated Tag entity and returns the "real" tag id.
     * :::value is just a bit of magic that let's us know that the tag doesn't yet exist in the civicrm_tag table.
     * @param string $tag_id
     * @return mixed|string
     * @throws CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    #[CRM_NYSS_Attribute_IssueRefs('17833')]
  public static function preprocessPositionTags(string $tag_id) {
      if (str_ends_with($tag_id, ':::value') &&  // short circuit to avoid preg_match() when unnecessary.
          preg_match('/^([A-Z]{1}[0-9]+.+):{3}value$/', $tag_id ?? '', $matches)) {
          $tag = $matches[1];
          $result = \Civi\Api4\Tag::save(TRUE)
              ->addRecord([
                  'name' => $tag,
                  'label' => $tag,
                  'parent_id' => CRM_NYSS_Tags_Constants::POSITIONS_TAG_ID,
                  'is_selectable' => true,
                  'used_for' => ['civicrm_contact','civicrm_activity','civicrm_case'],
              ])
              ->setMatch([
                  'name',
              ])
              ->execute()->single();
          return $result['id'];
      } else {
          return $tag_id;
      }
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

    $query = "SELECT id, name FROM civicrm_tag WHERE parent_id = ". CRM_NYSS_Tags_Constants::POSITIONS_TAG_ID;
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
          'id' => $tagID,
          'label' => $billTag,
          'sponsor' => $billSponsor,
          'description' => []
        ]);
      }//end foreach
    }
  }

}
