<?php
if ( isset($_POST['set_JobID']) && $_POST['set_JobID'] ) $_SESSION['CiviCRM']['jobID'] = $_POST['set_JobID'];
?>

<?php // $Id: page.tpl.php,v 1.15.4.7 2008/12/23 03:40:02 designerbrent Exp $ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language ?>" lang="<?php print $language->language ?>">
<head>
	<title><?php print strip_tags($title) ?> | Bluebird</title>
	<meta http-equiv="content-language" content="<?php print $language->language ?>" />
	<?php print $meta; ?>
  <?php print $head; ?>
  <?php print $styles; ?>
  <!--[if lte IE 8]>
    <?php /*?><link rel="stylesheet" href="<?php print $path; ?>blueprint/blueprint/ie.css" type="text/css" media="screen, projection"><?php */?>
  	<link href="<?php print $path; ?>css/ie.css" rel="stylesheet"  type="text/css"  media="screen, projection" />
  <![endif]-->
  <!--[if lte IE 6]>
  	<link href="<?php print $path; ?>css/ie6.css" rel="stylesheet"  type="text/css"  media="screen, projection" />
  <![endif]-->

  <?php print $scripts ?>

</head>

<?php
	$rolesList = implode(', ',$user->roles);
	$role = str_replace('authenticated user, ','', $rolesList).'&nbsp;';
?>

<body class="<?php print $body_classes;?><?php print 'role-'.$role;?>">
<?php //print_r($_SESSION); ?>
<?php if ($user->uid && arg(0) == 'civicrm') { ?>
 <?php if ($footer): ?>
      <div id="footer-bg"></div>
      <?php if ($user->uid) { ?>
    <?php if ($footer_message | $footer): ?>
      <div id="footer" class="clear span-24">
      	<div id="dashboard-link-wrapper">
      		<a href="<?php print base_path(); ?>civicrm?reset=1"><div class="icon dashboard-icon"></div> Dashboard</a>
      	</div>
        <?php if ($footer): ?>
          <?php print $footer; ?>
        <?php endif; ?>
        <?php if ($footer_message): ?>
          <div id="footer-message"><?php print $footer_message; ?></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
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
    <?php print $header; ?>
    <?php if (isset($primary_links)) : ?>
      <?php print theme('links', $primary_links, array('id' => 'nav', 'class' => 'links')) ?>
    <?php endif; ?>
    <?php if (isset($secondary_links)) : ?>
      <?php print theme('links', $secondary_links, array('id' => 'subnav', 'class' => 'links')) ?>
    <?php endif; ?>
  </div>

  <?php if ($left): ?>
    <div class="<?php print $left_classes; ?>"><?php print $left; ?></div>
  <?php endif ?>
  <?php
  if ($head_title != '' && arg(0) != 'civicrm' && $user->uid) {
  		$quickTitle = explode("|", $head_title);
        print '<a href="/" style="float:right;"> &raquo; back to Bluebird</a><h2><a href="/">'. $quickTitle[0] .'</a></h2> ';
      }
 ?>
  <div class="clear span-24 main-container <?php if (arg(0) != 'civicrm') {?>standard-container<?php } ?>">
  <div id="breadcrumb"><?php print $breadcrumb; ?></div>
  <?php if ($user->uid && arg(0) == 'civicrm') { ?>
  	<div id="edit-profile">
    <?php /*?><a href="<?php print base_path(); ?>user/<?php print $user->uid; ?>/edit">
  		<div class="icon settings-icon"></div>Update your Profile
  	</a><?php */?> <!--#2288-->
  		<a href="<?php print base_path(); ?>logout" class="logout">
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
        print '<div class="tabs">'. $tabs .'</div>';
      }

      print $help; // Drupal already wraps this one in a class
      ?>
     <!-- <div class="crm-title">
		<h1 class="title"><?php print $title; ?></h1>
      </div> -->
    <?php
      print $content;

      print $feed_icons;
    ?>
    <script>
       $('.messages br').remove();
       $('.messages').each(function(index){
           if($(this).html() == '') { $(this).remove();}
           });
	   $('.messages').appendTo('#status .messages-container');
	   if($('#status .messages-container').children().length > 0) {
	   	$('#status').append('<div id="status-handle"><span class="ui-icon ui-icon-arrowthickstop-1-n"></span></div>');
	   }
	   $('#status-handle').click(function(){
	   	$('.messages-container').slideToggle('fast');
	   	$('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-n');
	   	$('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-s');
	   });
    </script>


  <?php print $closure; ?>

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
		title: "Set SOS Job ID",
		modal: true,
		bgiframe: true,
		overlay: { opacity: 0.5, background: "black" },
		beforeclose: function(event, ui) { cj(this).dialog("destroy"); },
		buttons: { "Set ID": function() { $("#formSetJob").submit(); cj(this).dialog("close"); }}
	})
}
</script>

</body>
</html>
