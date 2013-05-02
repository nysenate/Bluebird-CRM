<?php
if ( isset($_POST['set_JobID']) && $_POST['set_JobID'] ) $_SESSION['CiviCRM']['jobID'] = $_POST['set_JobID'];
?>

<?php
	$rolesList = implode(', ',$user->roles);
	$role      = str_replace('authenticated user, ','', $rolesList).'&nbsp;';

	if ( strpos($_SERVER['HTTP_HOST'], 'crmtest') ) {
		$env = 'env-crmtest';
	} elseif ( strpos($_SERVER['HTTP_HOST'], 'crmdev') ) {
		$env = 'env-crmdev';
	} else {
		$env = 0;
	}
?>

<div class="<?php print $body_classes;?><?php print 'role-'.$role;?>">
<?php //print_r($_SESSION); ?>

<?php if ($user->uid && arg(0) == 'civicrm') { ?>

  <?php if ( $env ) { //4343 ?>
    <div class="<?php echo $env; ?>"></div>
  <?php } ?>

  <?php if ($page['footer']): ?>
    <div id="footer-bg"></div>
    <?php if ($user->uid) { ?>
      <div id="footer" class="clear span-24">
      	<div id="dashboard-link-wrapper">
      		<a href="<?php print base_path(); ?>civicrm?reset=1"><div class="icon dashboard-icon"></div> Dashboard</a>
      	</div>
        <?php print render($page['footer']); ?>
      </div>
    <?php } ?>
  <?php endif; ?>
<?php } ?>

<div class="container">
  <div id="status">
   	<div class="messages-container">
      <?php	if ($messages != '') {?>
   		  <div id="messages">
   		    <?php print $messages; ?>
   		  </div>
      <?php } ?>
    </div>
  </div>

  <div id="header">
   <!-- <h1 id="logo">
      <a title="<?php print $site_name; ?><?php if ($site_slogan != '') print ' &ndash; '. $site_slogan; ?>" href="<?php print url(); ?>"><?php print $site_name; ?><?php if ($site_slogan != '') print ' &ndash; '. $site_slogan; ?></a>
    </h1> -->
    <?php print render($page['header']); ?>
    <?php if (isset($main_menu)) : ?>
      <?php print theme('links', $main_menu, array('id' => 'nav', 'class' => 'links')) ?>
    <?php endif; ?>
    <?php if (isset($secondary_menu)) : ?>
      <?php print theme('links', $secondary_menu, array('id' => 'subnav', 'class' => 'links')) ?>
    <?php endif; ?>
  </div>

  <?php if (isset($sidebar_first) && $sidebar_first): ?>
    <div class="<?php print $left_classes; ?>"><?php print $sidebar_first; ?></div>
  <?php endif ?>
  <?php
  if ($head_title != '' && arg(0) != 'civicrm' && $user->uid) {
  		$quickTitle = explode("|", $head_title);
        print '<a href="/" style="float:right;"> &raquo; back to Bluebird</a><h2><a href="/">'. $quickTitle[0] .'</a></h2> ';
      }
 ?>
  <div class="clear span-24 main-container <?php 
    if ( arg(0) != 'civicrm' ) { echo 'standard-container '; }
	if ( arg(0) == 'admin' && arg(2) == 'user' ) { echo 'user-admin'; } //NYSS 5253
  ?>">
  <div id="breadcrumb"><?php print $breadcrumb; ?></div>
  <?php if ($user->uid && arg(0) == 'civicrm') { ?>
  	<div id="edit-profile">
    <?php /*?><a href="<?php print base_path(); ?>user/<?php print $user->uid; ?>/edit">
  		<div class="icon settings-icon"></div>Update your Profile
  	</a><?php */?> <!--#2288-->
  		<a href="<?php print base_path(); ?>user/logout" class="logout">
  			<div class="icon logout-icon"></div> Logout
  		</a>
        <div class="sitedetails"><div class="icon key-icon"></div><span> <?php echo $role; ?> </span></div>
        <?php $instance = substr( $_SERVER['HTTP_HOST'], 0, strpos( $_SERVER['HTTP_HOST'], '.' ) ); ?>
        <div class="sitedetails"><div class="icon flag-icon"></div><span> <?php echo $instance; ?> </span></div>
  	</div>
  	<div class="account-info-wrapper">
  		<div class="account-info">
  			<div class="greeting">
<?php
$morning = "Good Morning";
$afternoon = "Good Afternoon";
$evening = "Good Evening";
$night = "Good Night";
$offset = 0; // time offset from server time (in hours) to adjust time zone (orig -4)
// -------------
$now = time() + (60 * 60 * $offset);
	if(date("G", $now) >= "5" && date("G", $now) <= "11"){ echo $morning;
	}elseif(date("G", $now) >= "12" && date("G", $now) <= "17"){ echo $afternoon;
	}elseif(date("G", $now) >= "18" && date("G", $now) <= "20"){ echo $evening;
	}else{ echo $night; }

?>,
  			</div>
  			<div class="user-name">
  				<?php //print $user->name; ?>
          <?php //insert first name in header greeting; #2288
					civicrm_initialize( );
					require_once 'CRM/Core/Config.php';
					$config =& CRM_Core_Config::singleton( );

					require_once "api/v2/UFGroup.php";
					$uid = $user->uid;
					$contactid = civicrm_uf_match_id_get( $uid );

					require_once "api/v2/Contact.php";
					$params = array( 'contact_id' => $contactid );
					$contactrecord = civicrm_contact_get( $params );
					echo $contactrecord[$contactid]['first_name'];
				?>

  			</div>
  		</div>
  	</div>
    <?php
	$job_roles = array( 'Superuser', 'SOS', 'Administrator' );
	$jobuser = 0;
	foreach ( $user->roles as $user_role ) {
		if ( in_array( $user_role, $job_roles ) ) { $jobuser = 1; }
	}
	  if ( $jobuser ) { ?>
    	<div class="sos_job">
    	    [<?php if ( isset($_SESSION['CiviCRM']['jobID']) and $_SESSION['CiviCRM']['jobID'] ) { echo 'Job ID: '.$_SESSION['CiviCRM']['jobID'].' // '; } ?>
        	<a href="#" class="setJob" title="Set SOS JobID" onclick="setJobID( );return false;">Set Job#</a>]
      </div>
    <?php } ?>

  	<?php } ?>

    <?php
      if ($tabs != '') {
        print '<div class="tabs">'. render($tabs) .'</div>';
      }
      print render($page['help']); // Drupal already wraps this one in a class
      print render($page['content']);
      print $feed_icons;
    ?>
    <script>
    if(typeof cj == 'function') {
      cj('.messages br').remove();
      cj('.messages').each(function(index){
        if(cj(this).html() == '') { cj(this).remove(); }
      });
      cj('.messages').appendTo('#status .messages-container');
      if(cj('#status .messages-container').children().length > 0) {
        cj('#status').append('<div id="status-handle"><span class="ui-icon ui-icon-arrowthickstop-1-n"></span></div>');
      }
      cj('#status-handle').click(function(){
        cj('.messages-container').slideToggle('fast');
        cj('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-n');
        cj('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-s');
      });
    }
    </script>

  <?php
    if ( isset($closure) ) {
      print $closure;
    }
  ?>

</div>

<div id="dialogJobID" style="display: none;">
  <form action="" method="post" id="formSetJob">
    Enter a new SOS Job ID<br/>
    <input type="text" id="set_jobID" name="set_JobID" />
  </form>
</div>

<script type="text/javascript">
function setJobID( ) {
  cj("#dialogJobID").show( );
  cj("#dialogJobID").dialog({
	  modal: true,
	  bgiframe: true,
	  overlay: { opacity: 0.5, background: "black" },
	  beforeclose: function(event, ui) { cj(this).dialog("destroy"); },
	  buttons: { "Set ID": function() { $("#formSetJob").submit(); cj(this).dialog("close"); }}
	})
}
</script>

<?php
//store job id in db variable
if ( isset($_SESSION['CiviCRM']['jobID']) && $_SESSION['CiviCRM']['jobID'] ) {
  $jobID = CRM_Core_DAO::singleValueQuery('SELECT @jobID');
  if ( !$jobID ) {
      $jobID = $_SESSION['CiviCRM']['jobID'];
      CRM_Core_DAO::executeQuery('SET @jobID = %1', array(1 => array($jobID, 'String')));
  }
  //CRM_Core_Error::debug_var('jobID',CRM_Core_DAO::singleValueQuery('SELECT @jobID'));
}
?>

</div>
