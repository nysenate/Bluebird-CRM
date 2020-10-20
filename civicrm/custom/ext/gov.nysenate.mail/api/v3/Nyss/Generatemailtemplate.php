<?php

/**
 * Nyss.Generatemailtemplate API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_Generatemailtemplate_spec(&$spec) {
  $spec['addupdate'] = [
    'api.required' => 1,
    'api.default'  => 'Add',
    'title' => ts('Add or Update'),
    'description' => 'Add will create a new template, regardless if there is an existing one. Update will check for and update an existing template before adding.',
    'type' => CRM_Utils_Type::T_STRING,
    'options' => ['Add' => 'Add', 'Update' => 'Update'],
  ];
  $spec['senator'] = [
    'title' => 'Senator to pull remote details for',
    'description' => 'Used for testing only. Use shortname. Image links will still be built using the operating site, but address, name, and public URL values will use the selected Senator.',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * Nyss.Generatemailtemplate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_Generatemailtemplate($params) {
  $response = ['result' => ''];

  $bbcfg = get_bluebird_instance_config();
  //Civi::log()->debug(__FUNCTION__, ['$bbcfg' => $bbcfg]);
  $senator = CRM_Utils_Array::value('senator', $params);

  $info = _mget_retrieveSenatorInfo($bbcfg, $senator);

  //if we were unable to retrieve remote details, flag warning and set default values
  if (!$info) {
    $response['warnings'][] = 'Could not retrieve Senate office information. Using generic values.';
    $info = [
      'full_name' => 'John Doe',
      'url' => 'https://www.nysenate.gov/senators/',
      'offices' => [
        [
          'name' => 'Albany Office',
          'last_name' => 'Doe',
          'street' => 'Legislative Office Building',
          'additional' => 'Basement',
          'city' => 'Albany',
          'province' => 'NY',
          'postal_code' => 12247,
          'phone' => '800-123-4567',
        ],
      ],
    ];
  }

  foreach (['content', 'html', 'metadata'] as $file) {
    $$file = _mget_generateContent($file, $info, $bbcfg);
  }

  $title = 'Standard Office Template';
  if (!empty($senator)) {
    $title .= ": {$info['last_name']}";
  }

  $sqlParams = [
    1 => [$title, 'String'],
    2 => [$html, 'String'],
    3 => [$metadata, 'String'],
    4 => [$content, 'String'],
  ];

  switch ($params['addupdate']) {
    case 'Add':
      $sql = "
        INSERT INTO civicrm_mosaico_template
        (title, base, html, metadata, content)
        VALUES
        (%1, 'nyssbase', %2, %3, %4)
      ";
      break;

    case 'Update':
      $mId = _mget_getTemplateId($title);

      if ($mId) {
        $sqlParams[5] = [$mId, 'Positive'];
        $sql = "
          UPDATE civicrm_mosaico_template
          SET html = %2, metadata = %3, content = %4
          WHERE id = %5
        ";
      }
      else {
        $response['warnings'][] = 'Could not update template. No existing template found. Try rerunning in Add mode.';
      }

      break;

    default:
  }

  CRM_Core_DAO::executeQuery($sql, $sqlParams);

  $response['result'] = 'Completed successfully';
  if (!empty($response['warnings'])) {
    $response['result'] = 'Completed with warnings.';
  }

  return civicrm_api3_create_success($response, $params, 'Nyss', 'Generatemailtemplate');
}

function _mget_retrieveSenatorInfo($bbcfg, $senator) {
  $shortname = ($senator) ? $senator : $bbcfg['shortname'];
  $ch = curl_init("https://www.nysenate.gov/senators-json/{$shortname}");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $json = curl_exec($ch);
  curl_close($ch);

  if ($json !== false) {
    $info = json_decode($json);
    //Civi::log()->debug(__FUNCTION__, ['info' => $info]);

    // A non-matching senator name will return the entire array of senators in
    // the JSON data.  Therefore, if the JSON is an array, the lookup failed.
    if (is_array($info)) {
      return NULL;
    }
    else {
      // Convert the single object into an array.
      $info = (array)$info;
      //Civi::log()->debug(__FUNCTION__, ['info' => $info]);

      return $info;
    }
  }
  else {
    return NULL;
  }
}

function _mget_generateContent($file, $info, $bbcfg) {
  $path = CRM_Core_Resources::singleton()->getPath('gov.nysenate.mail');
  $content = file_get_contents($path.'/mosaicotemplate/'.$file.'.txt');
  //Civi::log()->debug(__FUNCTION__, ['$content' => $content]);

  $map = [
    'CRM_URL' => "http://{$bbcfg['shortname']}.{$bbcfg['base.domain']}",
    'CRM_SHORTNAME' => $bbcfg['shortname'],
    'CRM_TIMESTAMP' => time(),
    'CRM_ALBANY_OFFICE' => _mget_buildAddressBlocks($info['offices'], 'Albany Office'),
    'CRM_DISTRICT_OFFICE' => _mget_buildAddressBlocks($info['offices'], 'District Office'),
    'CRM_SENATOR_URL' => $info['url'],
    'CRM_SENATOR_NAME' => $info['full_name'],
  ];
  //Civi::log()->debug(__FUNCTION__, ['$map' => $map]);

  //string replacements
  foreach ($map as $placeholder => $value) {
    $content = str_replace($placeholder, $value, $content);
  }
  //Civi::log()->debug(__FUNCTION__, ['$content' => $content]);

  return $content;
}

function _mget_buildAddressBlocks($addresses, $type = NULL) {
  $blocks = [];

  foreach ($addresses as $address) {
    $address = (array)$address;

    $block = $address['street'].'<br />';
    $block .= (!empty($address['additional'])) ? $address['additional'].'<br />' : '';
    $block .= "{$address['city']}, {$address['province']} {$address['postal_code']}".'<br />';
    $block .= $address['phone'];

    $blocks[$address['name']] = $block;
  }

  if ($type) {
    return $blocks[$type];
  }

  return $blocks;
}

function _mget_getTemplateId($title) {
  return CRM_Core_DAO::singleValueQuery("
    SELECT id
    FROM civicrm_mosaico_template
    WHERE title = %1
    LIMIT 1
  ", [
    1 => [$title, 'String'],
  ]);
}
