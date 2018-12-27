<?php
  //trigger function to handle header construction and job ID
  $addlVars = _bbSetupHeader();
?>

<?php if (!empty($addlVars)) { ?>
  <div id="branding" class="clearfix">
    <div id="bb-recentitems"></div>
    <?php print $breadcrumb; ?>
    <div id="bb-header">
      <?php
        if ($addlVars['isCiviCRM']) {
          print $addlVars['bbheader'];
          print $addlVars['bbjob'];
        }
      ?>
    </div>
    <?php print render($title_prefix); ?>
    <?php if ($title): ?>
      <h1 class="page-title"><?php print $title; ?></h1>
    <?php endif; ?>
    <?php print render($title_suffix); ?>
    <?php print render($primary_local_tasks); ?>
  </div>
<?php } ?>

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
      width: 400,
      overlay: {
        opacity: 0.5,
        background: "black"
      },
      beforeclose: function(event, ui) {
        cj(this).dialog("destroy");
        },
      buttons: {
        "Cancel": function() {
          cj(this).dialog("close");
        },
        "Clear Existing ID": function() {
          cj('#bbClearJobId').val(1);
          cj("#formSetJob").submit();
          cj(this).dialog("close");
        },
        "Set ID": function() {
          cj('#bbCurrentJobId').text(' :: ' + cj('#bbSetJobId').val())
          cj("#formSetJob").submit();
          cj(this).dialog("close");
        }
      }
    });
  }
</script>

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
      $jobblock .= " &raquo; [<a href='#' title='Set SOS Job ID' onclick='setJobID();return false;'>Job #</a>{$jobId}]";

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

  return $variables;
}


