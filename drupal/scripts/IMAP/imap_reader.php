<?php
$docroot = array_key_exists('DOCUMENT_ROOT',$_SERVER) ? $_SERVER['DOCUMENT_ROOT'] : '';
if (php_sapi_name() != "apache2handler" || !$docroot) {
  echo "This file should not be called from command line.";
  exit(1);
}

// bootstrap Civi
require_once $_SERVER['DOCUMENT_ROOT'].'/../civicrm/scripts/script_utils.php';
require_once SCRIPT_UTILS_CIVIROOT.'/civicrm.config.php';
$config = &CRM_Core_Config::singleton();
$session = &CRM_Core_Session::singleton();

// get the bluebird config
add_scripts_to_include_path();
require_once 'bluebird_config.php';

// add local resources to include path
set_include_path(dirname(__FILE__).PATH_SEPARATOR.get_include_path());
?><!DOCTYPE html>
<html>
  <head>
    <title>IMAP Reader (for testing and debugging only!)</title>
    <script src="//code.jquery.com/jquery-2.1.4.js" type="text/javascript"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js" type="text/javascript"></script>
    <script src="/scripts/IMAP/imap_ajax_helper.js" type="text/javascript"></script>
    <script src="/scripts/IMAP/imap_helper.js" type="text/javascript"></script>
    <link href="/scripts/IMAP/imap_reader.css" type="text/css" rel="stylesheet" />
  </head>
  <body>
    <div id="top-container">
      <h1 id="page-header">IMAP Reader &amp Analysis Tool</h1>
      <div id="column-left">
        <h2>Control Panel</h2>
        <div id="account-selection">
          <div id="instance-selector">
            <span>Select an instance account:</span>
            <select name="instance_account" id="instance-account">
            </select>
            <span class="sub ui-button" id="instance-account-refresh">refresh list</span>
          </div>
          <label for="use-custom-server">
            <input type="checkbox" name="use_custom_server" id="use-custom-server" />
            <span>Use custom connection</span>
          </label>
          <div id="account-information">
            <table id="custom-server-props">
              <thead />
              <tbody>
                <tr>
                  <td class="cell-label">Server:</td>
                  <td class="cell-value"><input name="custom_server_name" id="custom-server-name" type="text" value="" /></td>
                </tr>
                <tr>
                  <td class="cell-label">Port:</td>
                  <td class="cell-value"><input name="custom_server_port" id="custom-server-port" type="text" value="" /></td>
                </tr>
                <tr>
                  <td class="cell-label">User:</td>
                  <td class="cell-value"><input name="custom_server_user" id="custom-server-user" type="text" value="" /></td>
                </tr>
                <tr>
                  <td class="cell-label">Pass:</td>
                  <td class="cell-value"><input name="custom_server_pass" id="custom-server-pass" type="text" value="" /></td>
                </tr>
                <tr>
                  <td class="cell-label" colspan="2">
                    <input type="button" name="select_instance" id="select-instance" value="Use" disabled="disabled" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div id="folder-selection">
          Select a folder:
          <select id="folder-request" name="folder_request">
            <option value="">No Folders Found</option>
          </select>
          <div id="folder-overview"></div>
        </div>
        <div id="command-selection">
          Select a command:
          <select id="command-request" name="command_request">
            <option value="">Select Command</option>
            <option value="showMsg">Show Message</option>
          </select>
          <div id="command-group-showMsg" class="command-group">
            Message Number: <input type="text" name="showMsg_number" id="showMsg-number" value="" size="3" />
            <span class="ui-button showMsg-counter" id="showMsg-next"> + </span>
            <span class="ui-button showMsg-counter" id="showMsg-prev"> - </span>
          </div>
          <input type="button" name="execute_command" id="execute-command" value="Execute" />
        </div>
      </div>
      <div id="column-right">
        <h2>Server Return</h2>
        <div id="results-message-rfc822" class="fillable collapsible">
          <h3 class="results-section-header">
            RFC822 Body
            <div class="toggle-button" title="Toggle RFC822 Body"></div>
          </h3>
          <pre id="results-message-rfc822-content"></pre>
        </div>
        <div id="results-message-overview" class="fillable collapsible">
          <h3 class="results-section-header">
            Message Overview
            <div class="toggle-button" title="Toggle Overview"></div>
          </h3>
          <pre id="results-message-overview-content"></pre>
        </div>
        <div id="results-possible-senders" class="fillable collapsible">
          <h3 class="results-section-header">
            Detected Senders
            <div class="toggle-button" title="Toggle Detected Senders"></div>
          </h3>
          <pre id="results-possible-senders-content"></pre>
        </div>
        <div id="results-message-headers" class="fillable collapsible">
          <h3 class="results-section-header">
            Found IMAP Headers
            <div class="toggle-button" title="Toggle Headers"></div>
          </h3>
          <pre id="results-message-headers-content"></pre>
        </div>
        <div id="results-message-parsed" class="fillable collapsible">
          <h3 class="results-section-header">
            Parsed Metadata
            <div class="toggle-button" title="Toggle Parsed Metadata"></div>
          </h3>
          <pre id="results-message-parsed-content"></pre>
        </div>
      </div>
    </div>

    <!-- debug window -->
    <div id="debug-box-open-handle">debug</div>
    <div id="debug-box">
      <div id="debug-box-drag-handle"><div id="debug-box-close-handle">X</div></div>
      <div id="debug-box-content">
        <h2 id="debug-box-header">
          Debug Output
          <div id="debug-box-flush" class="sub ui-button">clear</div>
        </h2>
        <div id="debug-output"></div>
      </div>
    </div>


  </body>
</html>