<?php

/**
 * Nyss.Oauthsetup API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_oauthsetup_spec(&$spec) {
}

/**
 * Nyss.Oauthsetup API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_oauthsetup($params) {
  //install oauth-client extension
  try {
    $ext = civicrm_api3('Extension', 'getsingle', ['key' => 'oauth-client']);
    if ($ext['status'] != 'installed') {
      civicrm_api3('Extension', 'enable', ['key' => 'oauth-client']);
    }

    civicrm_api3('Extension', 'upgrade');
  }
  catch (CRM_Core_Exception $e) {
    throw new CRM_Core_Exception($e);
  }

  $bbcfg = get_bluebird_instance_config();
  //Civi::log()->debug(__FUNCTION__, ['bbcfg' => $bbcfg]);

  if (empty($bbcfg['oauth.client_id']) ||
    empty($bbcfg['oauth.client_secret']) ||
    empty($bbcfg['oauth.tenant_id'])
  ) {
    throw new CRM_Core_Exception('OAuth client_id, tenant_id, and client_secret must be set in the Bluebird config file.');
  }

  //delete existing client(s) if they exist
  \Civi\Api4\OAuthClient::delete(FALSE)
    ->addWhere('provider', '=', 'ms-exchange')
    ->execute();

  //create client
  \Civi\Api4\OAuthClient::create(FALSE)
    ->addValue('provider', 'ms-exchange')
    ->addValue('guid', $bbcfg['oauth.client_id'])
    ->addValue('secret', $bbcfg['oauth.client_secret'])
    ->addValue('tenant', $bbcfg['oauth.tenant_id'])
    ->addValue('is_active', 1)
    ->execute()
    ->last();

  return civicrm_api3_create_success('OAuth Setup Successfully', $params, 'nyss', 'oauthsetup');
}
