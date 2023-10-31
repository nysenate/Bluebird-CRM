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
  }
  catch (CRM_Core_Exception $e) {
    throw new CRM_Core_Exception($e);
  }

  $bbcfg = get_bluebird_instance_config();
  //Civi::log()->debug(__FUNCTION__, ['bbcfg' => $bbcfg]);

  if (empty($bbcfg['oauth.client_id']) || empty($bbcfg['oauth.client_secret'])) {
    throw new CRM_Core_Exception('OAuth client_id and/or client_secret is not set in the Bluebird config file.');
  }

  //create client
  $client = \Civi\Api4\OAuthClient::create(FALSE)
    ->addValue('provider', 'ms-exchange')
    ->addValue('guid', $bbcfg['oauth.client_id'])
    ->addValue('secret', $bbcfg['oauth.client_secret'])
    ->addValue('tenant', $bbcfg['oauth.tenant_id'])
    ->addValue('is_active', 1)
    ->execute()
    ->last();
  $clientId = $client['id'];
  Civi::log()->debug(__FUNCTION__, ['$client' => $client, '$clientId' => $clientId]);

  //grant access via client credentials
  /*$token = \Civi\Api4\OAuthClient::clientCredential(FALSE)
    ->setScopes([
      'https://outlook.office.com/IMAP.AccessAsUser.All',
      'https://outlook.office.com/POP.AccessAsUser.All',
      'https://outlook.office.com/SMTP.Send',
      'openid',
      'email',
      'offline_access',
    ])
    ->addWhere('id', '=', $clientId)
    ->execute();
  Civi::log()->debug(__FUNCTION__, ['$token' => $token]);*/

  //create sys token
  /*if ($clientId) {
    $sysToken = \Civi\Api4\OAuthClient::authorizationCode(FALSE)
      ->addWhere('id', '=', $clientId)
      ->setStorage('OAuthSysToken')
      ->setPrompt('none')
      ->execute();
    Civi::log()->debug(__FUNCTION__, ['$sysToken' => $sysToken]);
  }
  else {
    throw new CRM_Core_Exception('OAuth system token could not be created.');
  }

  //https://stackoverflow.com/questions/39826835/oauth2-token-php
  $authUrl = "https://login.microsoftonline.com/{$bbcfg['oauth.tenant_id']}/oauth2/v2.0/authorize";
  $tokenUrl = "https://login.microsoftonline.com/{$bbcfg['oauth.tenant_id']}/oauth2/v2.0/token";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $tokenUrl);
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'client_id' => $bbcfg['oauth.client_id'],
    'client_secret' => $bbcfg['oauth.client_secret'],
    'username' => $bbcfg['imap.user'],
    'password' => $bbcfg['imap.pass'],
    'grant_type' => 'authorization_code',
    'scope' => [
      'https://outlook.office.com/IMAP.AccessAsUser.All',
      'https://outlook.office.com/POP.AccessAsUser.All',
      'https://outlook.office.com/SMTP.Send',
      'openid,',
      'email',
      'offline_access',
    ],
    'code' => $sysToken //???
  ]);

  $data = curl_exec($ch);
  $auth_string = json_decode($data, true);
  Civi::log()->debug(__FUNCTION__, ['$auth_string' => $auth_string]);*/

  return civicrm_api3_create_success('OAuth Setup Successfully', $params, 'Nyss', 'Oauthsetup');
}
