<?php

/**
 * @file
 * Provides functions for encryption/decryption.
 */

/**
 * Return a random salt of a given length for crypt-style passwords.
 *
 * @param int length
 *   The requested length.
 *
 * @return string
 *   A (fairly) random salt of the requested length.
 */
function ldap_servers_random_salt($length) {
  $possible = '0123456789' . 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . './';
  $salt = "";

  mt_srand((double) microtime() * 1000000);
  while (strlen($salt) < $length) {
    $salt .= substr($possible, (rand() % strlen($possible)), 1);
  }

  return $salt;
}

/**
 * Encryption options available.
 *
 * @return array
 *   Options.
 */
function _ldap_servers_encrypt_types() {
  $options = [
    LDAP_SERVERS_ENC_TYPE_CLEARTEXT => 'Clear text',
  ];

  if (extension_loaded('openssl')) {
    $options[LDAP_SERVERS_ENC_TYPE_OPENSSL] = 'OpenSSL';
  }

  return $options;
}

/**
 * Encrypt string.
 *
 * @param $input
 *   Clear text.
 * @param null $encryption_enabled
 *   OpenSSL or clear text.
 *
 * @return string
 *   Plain or encrypted.
 */
function _ldap_servers_encrypt($input, $encryption_enabled = NULL) {

  if (!$encryption_enabled) {
    $encryption_enabled = variable_get('ldap_servers_encryption', LDAP_SERVERS_ENC_TYPE_CLEARTEXT);
  }

  if ($encryption_enabled == LDAP_SERVERS_ENC_TYPE_CLEARTEXT) {
    return $input;
  }

  $key = variable_get('ldap_servers_encrypt_key', drupal_get_hash_salt());
  $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(LDAP_SERVERS_CYPHER_MODE));
  $encrypted_data = openssl_encrypt($input, LDAP_SERVERS_CYPHER_MODE, $key, 0, $iv);

  return base64_encode($encrypted_data . '::' . $iv);
}

/**
 * Decrypt string.
 *
 * @param string $input
 *   Clear text or encrypted text.
 * @param null $encryption_enabled
 *   OpenSSL or clear text.
 *
 * @return string
 *   Clear text.
 */
function _ldap_servers_decrypt($input, $encryption_enabled = NULL) {

  if (!$encryption_enabled) {
    $encryption_enabled = variable_get('ldap_servers_encryption', LDAP_SERVERS_ENC_TYPE_CLEARTEXT);
  }

  if ($encryption_enabled == LDAP_SERVERS_ENC_TYPE_CLEARTEXT) {
    return $input;
  }

  $key = variable_get('ldap_servers_encrypt_key', drupal_get_hash_salt());
  list($encrypted_data, $iv) = explode('::', base64_decode($input), 2);
  return openssl_decrypt($encrypted_data, LDAP_SERVERS_CYPHER_MODE, $key, 0, $iv);
}
