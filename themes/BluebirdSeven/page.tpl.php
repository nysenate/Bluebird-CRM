<?php
  //trigger function to handle header construction and job ID
  $addlVars = _bbSetupHeader();
?>

<div id="branding" class="clearfix">
  <?php print $breadcrumb; ?>
  <div id="bb-header">
    <?php
      print $addlVars['bbheader'];
      print $addlVars['bbjob'];
    ?>
  </div>
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

<div id="dialogJobID" style="display: none;">
  <form action="" method="post" id="formSetJob">
    Enter a new SOS Job #<br/>
    <input type="text" id="bbSetJobId" name="bbSetJobId" />
    <input type="hidden" id="bbClearJobId" name="bbClearJobId" value=0 />
  </form>
</div>

<script type="text/javascript">
  function setJobID( ) {
    cj("#dialogJobID").show();
    cj("#dialogJobID").dialog({
      modal: true,
      title: 'Set Job ID',
      bgiframe: true,
      overlay: {
        opacity: 0.5,
        background: "black"
      },
      beforeclose: function(event, ui) {
        cj(this).dialog("destroy");
        },
      buttons: {
        "Set ID": function() {
          cj('#bbCurrentJobId').text(' :: ' + cj('#bbSetJobId').val())
          cj("#formSetJob").submit();
          cj(this).dialog("close");
        },
        "Clear Existing ID": function() {
          cj('#bbClearJobId').val(1);
          cj("#formSetJob").submit();
          cj(this).dialog("close");
        }
      }
    });
  }
</script>

<?php

function _bbSetupHeader() {
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
  global $user;
  $rolesList = implode('',$user->roles);
  $rolesList = str_replace('authenticated user','', $rolesList);

  civicrm_initialize();
  $contact = civicrm_api3('contact', 'getsingle', array(
    'id' => CRM_Core_Session::singleton()->getLoggedInContactID(),
  ));

  $instance = substr( $_SERVER['HTTP_HOST'], 0, strpos( $_SERVER['HTTP_HOST'], '.' ) );

  $variables['bbheader'] = "{$contact['display_name']} &raquo; {$instance} &raquo; {$rolesList}";

  //setup job dialog
  $job_roles = array('Superuser', 'SOS', 'Administrator');
  $jobblock = '';
  foreach ($user->roles as $user_role) {
    if (in_array($user_role, $job_roles)) {
      $jobId = (isset($_SESSION['CiviCRM']['jobID']) && $_SESSION['CiviCRM']['jobID']) ?
        '<span id="bbCurrentJobId"> :: '.$_SESSION['CiviCRM']['jobID']."</span>" : '';
      $jobblock = "
        <div id='bb-sosjob'>
          [<a href='#' title='Set SOS JobID' onclick='setJobID();return false;'>Job #</a>{$jobId}]
        </div>
      ";

      break;
    }
  }
  $variables['bbjob'] = $jobblock;

  return $variables;
}


