<?php
  //trigger function to handle header construction and job ID
  $addlVars = _bbSetupHeader();
?>

<div id="bb-header-title">
  <?php print render($title_prefix); ?>
  <?php if ($title): ?>
    <h1 class="page-title"><?php print $title; ?></h1>
  <?php endif; ?>
  <?php print render($title_suffix); ?>
  <?php print render($primary_local_tasks); ?>
</div>

<div id="page">
  <?php if ($secondary_local_tasks): ?>
    <div class="tabs-secondary clearfix"><?php print render($secondary_local_tasks); ?></div>
  <?php endif; ?>

  <div id="content" class="clearfix">
    <div class="element-invisible"><a id="main-content"></a></div>
    <?php if ($messages): ?>
      <div id="console" class="clearfix"><?php print $messages; ?></div>
    <?php endif; ?>
    <?php if ($page['help']): ?>
      <div id="help">
        <?php print render($page['help']); ?>
      </div>
    <?php endif; ?>
    <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>
    <?php print render($page['content']); ?>
  </div>

  <div id="footer">
    <?php print $feed_icons; ?>
  </div>
</div>

<div id="branding" class="clearfix">
  <?php
  if ($addlVars['recent_items']) {
    print $addlVars['recent_items'];
  }
  ?>

  <div id="bb-header">
    <?php
    if ($addlVars['isCiviCRM']) {
      print $addlVars['bbheader'];
      print $addlVars['bbjob'];
    }
    ?>
  </div>
</div>

<div id="dialogJobID" style="display: none;">
  <form action="" method="post" id="formSetJob">
    Enter a new SOS Job #<br/>
    <input type="text" id="bbSetJobId" name="bbSetJobId" />
    <input type="hidden" id="bbClearJobId" name="bbClearJobId" value=0 />
  </form>
</div>

<?php

function _bbSetupHeader() {
  //check if logged in; exit if not;
  global $user;
  if (!$user->uid) {
    return array();
  }

  //store job id in db variable and session
  //Civi::log()->debug('page', array('session' => $_SESSION, 'post' => $_POST));
  if (!empty($_POST['bbSetJobId']) && empty($_POST['bbClearJobId'])) {
    $_SESSION['CiviCRM']['jobID'] = $_POST['bbSetJobId'];
    CRM_Core_DAO::executeQuery('SET @jobID = %1', array(
      1 => array($_POST['bbSetJobId'], 'String'),
    ));
  }
  //if clearing, set session and db to empty/null
  elseif (!empty($_POST['bbClearJobId'])) {
    $_SESSION['CiviCRM']['jobID'] = '';
    CRM_Core_DAO::executeQuery('SET @jobID = NULL');
  }

  //setup header line
  $rolesList = implode('',$user->roles);
  $rolesList = str_replace('authenticated user','', $rolesList);

  civicrm_initialize();
  $contact = civicrm_api3('contact', 'getsingle', array(
    'id' => CRM_Core_Session::singleton()->getLoggedInContactID(),
  ));

  $instance = substr( $_SERVER['HTTP_HOST'], 0, strpos( $_SERVER['HTTP_HOST'], '.' ) );

  $variables['bbheader'] = "<span title='{$rolesList}'>{$contact['display_name']}</span>";

  //setup job dialog
  $job_roles = array('Superuser', 'SOS', 'Administrator');
  $jobblock = "<div id='bb-sitejob'>{$instance}";
  foreach ($user->roles as $user_role) {
    if (in_array($user_role, $job_roles)) {
      $jobId = (isset($_SESSION['CiviCRM']['jobID']) && $_SESSION['CiviCRM']['jobID']) ?
        '<span id="bbCurrentJobId"> :: '.$_SESSION['CiviCRM']['jobID']."</span>" : '';
      $jobblock .= " &raquo; [<a href='#' title='Set SOS Job ID' id='bbSetJobId'>Job #</a>{$jobId}]";

      break;
    }
  }
  $jobblock .= '</div>';
  $variables['bbjob'] = $jobblock;

  //get path to flag if CiviCRM
  $path = explode('/', current_path());
  $variables['isCiviCRM'] = FALSE;
  if ($path[0] == 'civicrm') {
    $variables['isCiviCRM'] = TRUE;
  }

  $variables['recent_items'] = bb_buildRecentItemsList();

  return $variables;
}

function bb_buildRecentItemsList() {
  $icons = [
    'Individual' => 'fa-user',
    'Organization' => 'fa-building',
    'Household' => 'fa-home',
    'Relationship' => 'fa-user-circle-o',
    'Activity' => 'fa-pencil-square-o',
    'Note' => 'fa-sticky-note',
    'Group' => 'fa-users',
    'Case' => 'fa-folder-open',
  ];
  $recent = CRM_Utils_Recent::get();
  //Civi::log()->debug('bb_buildRecentItemsList', ['recent' => $recent]);

  $html = '
    <div id="nyss-recentitems" title="Recent Items">Recent Items: 
      <ul id="nyss-recentitems-list">
  ';

  $i = 1;
  foreach ($recent as $item) {
    if ($i > 5) break;

    $editUrl = (!empty($item['edit_url'])) ?
      " (<a href='{$item['edit_url']}'><span class='nyss-recentitems-edit'>edit</span></a>)" : '';
    $icon = CRM_Utils_Array::value($item['type'], $icons);
    $html .= "
      <li><i class='nyss-i {$icon}'></i>&nbsp;<a href='{$item['url']}'>{$item['title']}</a>{$editUrl}</li>
    ";

    $i++;
  }

  $html .= '</ul></div>';

  return $html;
}
