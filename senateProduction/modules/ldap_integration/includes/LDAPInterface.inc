<?php
// $Id: LDAPInterface.inc,v 1.6 2009/10/27 14:29:18 miglius Exp $

/**
 * @file
 * LDAPInterface class definition.
 */

class LDAPInterface {

  function LDAPInterface() {
    $this->connection = NULL;
    //http://drupal.org/node/158671
    $this->server = NULL;
    $this->port = "389";
    $this->secretKey = NULL;
    $this->tls = FALSE;
    $this->attr_filter = array('LDAPInterface', '__empty_attr_filter');
  }

  var $connection;
  var $server;
  var $port;
  var $tls;
  var $attr_filter;
  var $sid;

  // This should be static, but that's not supported in PHP4
  function __empty_attr_filter($sid, $x) {
    return $x;
  }

  function setOption($option, $value) {
    switch ($option) {
      case 'sid' :
        $this->sid = $value;
        break;
      case 'name':
        $this->name = $value;
        break;
      case 'server':
        $this->server = $value;
        break;
      case 'port':
        $this->port = $value;
        break;
      case 'tls':
        $this->tls = $value;
        break;
      case 'encrypted':
        $this->encrypted = $value;
        break;
      case 'user_attr':
        $this->user_attr = $value;
        break;
      case 'attr_filter':
        $this->attr_filter = $value;
        break;
      case 'basedn':
        $this->basedn = $value;
        break;
      case 'mail_attr':
        $this->mail_attr = $value;
        break;
      case 'binddn':
        $this->binddn = $value;
        break;
      case 'bindpw':
        $this->bindpw = $value;
        break;
    }
  }

  function getOption($option) {
    $ret = '';
    switch ($option) {
      case 'sid':
        $ret = $this->sid;
        break;
      case 'version':
        $ret = -1;
        ldap_get_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $ret);
        break;
      case 'name':
        $ret = $this->name;
        break;
      case 'port':
        $ret = $this->port;
        break;
      case 'tls':
        $ret = $this->tls;
        break;
      case 'encrypted':
        $ret = $this->encrypted;
        break;
      case 'user_attr':
        $ret = isset($this->user_attr) ? $this->user_attr : NULL;
        break;
      case 'attr_filter':
        $ret = isset($this->attr_filter) ? $this->attr_filter : NULL;
        break;
      case 'basedn':
        $ret = isset($this->basedn) ? $this->basedn : NULL;
        break;
      case 'mail_attr':
        $ret = isset($this->mail_attr) ? $this->mail_attr : NULL;
        break;
      case 'binddn':
        $ret = isset($this->binddn) ? $this->binddn : NULL;
        break;
      case 'bindpw':
        $ret = isset($this->bindpw) ? $this->bindpw : NULL;
        break;
    }
    return $ret;
  }

  function connect($dn = '', $pass = '') {
    $ret = FALSE;
    // http://drupal.org/node/164049
    // If a connection already exists, it should be terminated
    $this->disconnect();

    if ($this->connectAndBind($dn, $pass)) {
      $ret = TRUE;
    }

    return $ret;
  }

  function initConnection() {
    if (!$con = ldap_connect($this->server, $this->port)) {
      watchdog('ldap', 'LDAP Connect failure to @server:@port', array('@server' => $this->server, '@port' => $this->port), WATCHDOG_ERROR);
      return;
    }

    ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($con, LDAP_OPT_REFERRALS, 0);
    // TLS encryption contributed by sfrancis@drupal.org
    if ($this->tls) {
      ldap_get_option($con, LDAP_OPT_PROTOCOL_VERSION, $vers);
      if ($vers == -1) {
        watchdog('ldap', 'Could not get LDAP protocol version.', array(), WATCHDOG_ERROR);
        return;
      }
      if ($vers != 3) {
        watchdog('ldap', 'Could not start TLS, only supported by LDAP v3.', array(), WATCHDOG_ERROR);
        return;
      }
      else if (!function_exists('ldap_start_tls')) {
        watchdog('ldap', 'Could not start TLS. It does not seem to be supported by this PHP setup.', array(), WATCHDOG_ERROR);
        return;
      }
      else if (!ldap_start_tls($con)) {
        watchdog('ldap', 'Could not start TLS. (Error %errno: %error).', array('%errno' => ldap_errno($con), '%error' => ldap_error($con)), WATCHDOG_ERROR);
        return;
      }
    }
    $this->connection = $con;
  }

  function connectAndBind($dn = '', $pass = '') {
    $this->initConnection();

    $con = $this->connection;
    if (!$this->bind($dn, $pass)) {
      watchdog('ldap', 'LDAP Bind failure for user %user. Error %errno: %error', array('%user' => $dn, '%errno' => ldap_errno($con), '%error' => ldap_error($con)));
      return NULL;
    }

    return $con;
  }

  function bind($dn, $pass) {
    ob_start();
    set_error_handler(array('LDAPInterface', 'void_error_handler'));
    $ret = ldap_bind($this->connection, $dn, $pass);
    restore_error_handler();

    ob_end_clean();

    return $ret;
  }

  function disconnect() {
    if ($this->connection) {
      ldap_unbind($this->connection);
      $this->connection = NULL;
    }
  }

  function search($base_dn, $filter, $attributes = array()) {
    $ret = array();

    // For the AD the '\,' should be replaced by the '\\,' in the search filter.
    $filter = preg_replace('/\\\,/', '\\\\\,', $filter);

    set_error_handler(array('LDAPInterface', 'void_error_handler'));
    $x = @ldap_search($this->connection, $base_dn, $filter, $attributes);
    restore_error_handler();

    if ($x && ldap_count_entries($this->connection, $x)) {
      $ret = ldap_get_entries($this->connection, $x);
    }
    return $ret;
  }

  // WARNING! WARNING! WARNING!
  // This function returns its entries with lowercase attribute names.
  // Don't blame me, blame PHP's own ldap_get_entries()
  function retrieveAttributes($dn) {
    set_error_handler(array('LDAPInterface', 'void_error_handler'));
    $result = ldap_read($this->connection, $dn, 'objectClass=*');
    $entries = ldap_get_entries($this->connection, $result);
    restore_error_handler();

    return call_user_func($this->attr_filter, $this->sid, $entries[0]);
  }

  function retrieveAttribute($dn, $attrname) {
    $entries = $this->retrieveAttributes($dn);
    return isset($entries[strtolower($attrname)]) ? $entries[strtolower($attrname)][0] : NULL;
  }

  function retrieveMultiAttribute($dn, $attrname) {
    $entries = $this->retrieveAttributes($dn);

    $result = array();
    $retrieved = $entries[strtolower($attrname)];
    $retrieved = $retrieved ? $retrieved : array();
    foreach ($retrieved as $key => $value) {
      if ($key !== 'count') {
        $result[] = $value;
      }
    }
    return $result;
  }

  function writeAttributes($dn, $attributes) {
    foreach ($attributes as $key => $cur_val) {
      if ($cur_val == '') {
        unset($attributes[$key]);
        $old_value = $this->retrieveAttribute($dn, $key);
        if (isset($old_value)) {
          ldap_mod_del($this->connection, $dn, array($key => $old_value));
        }
      }
      if (is_array ($cur_val)) {
        foreach ($cur_val as $mv_key => $mv_cur_val) {
          if ($mv_cur_val == '') {
            unset($attributes[$key][$mv_key]);
          }
          else {
            $attributes[$key][$mv_key] = $mv_cur_val;
          }
        }
      }
    }

    return ldap_modify($this->connection, $dn, $attributes);
  }

  function create_entry($dn, $attributes) {
    set_error_handler(array('LDAPInterface', 'void_error_handler'));
    $ret = ldap_add($this->connection, $dn, $attributes);
    restore_error_handler();

    return $ret;
  }

  function rename_entry($dn, $newrdn, $newparent, $deleteoldrdn) {
    set_error_handler(array('LDAPInterface', 'void_error_handler'));
    $ret = ldap_rename($this->connection, $dn, $newrdn, $newparent, $deleteoldrdn);
    restore_error_handler();

    return $ret;
  }

  function delete_entry($dn) {
    set_error_handler(array('LDAPInterface', 'void_error_handler'));
    $ret = ldap_delete($this->connection, $dn);
    restore_error_handler();

    return $ret;
  }

  // This function is used by other modules to delete attributes once they are
  // moved to profiles cause ldap_mod_del does not delete facsimileTelephoneNumber if
  // attribute value to delete is passed to the function.
  // OpenLDAP as per RFC 2252 doesn't have equality matching for facsimileTelephoneNumber
  // http://bugs.php.net/bug.php?id=7168
  function deleteAttribute($dn, $attribute) {
    ldap_mod_del($this->connection, $dn, array($attribute => array()));
  }

  // This should be static, but that's not supported in PHP4
  // Made it static and introduced a requirenment of php version 5.0.
  static function void_error_handler($p1, $p2, $p3, $p4, $p5) {
    // Do nothing
  }
}
