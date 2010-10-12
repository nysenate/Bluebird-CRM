<?php // $Id: page.tpl.php,v 1.15.4.7 2008/12/23 03:40:02 designerbrent Exp $ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language ?>" lang="<?php print $language->language ?>">
<head>
	<title><?php print $head_title ?></title>
	<meta http-equiv="content-language" content="<?php print $language->language ?>" />
	<?php print $meta; ?>
  <?php print $head; ?>
  <?php print $styles; ?>
  <!--[if lte IE 8]>
    <link rel="stylesheet" href="<?php print $path; ?>blueprint/blueprint/ie.css" type="text/css" media="screen, projection">
  	<link href="<?php print $path; ?>css/ie.css" rel="stylesheet"  type="text/css"  media="screen, projection" />
  <![endif]-->  
  <!--[if lte IE 6]>
  	<link href="<?php print $path; ?>css/ie6.css" rel="stylesheet"  type="text/css"  media="screen, projection" />
  <![endif]-->

  <?php print $scripts ?>
    
</head>

<?php 
	$rolesList = implode('',$user->roles);
	$role = str_replace('authenticated user','', $rolesList);
?>

<body class="<?php print $body_classes;?><?php print 'role-'.$role;?>">

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
  if ($head_title != '' && arg(0) != 'civicrm') {
  		$quickTitle = explode("|", $head_title);
        print '<h2>'. $quickTitle[0] .'</h2>';
      }   
 ?> 
  <div class="clear span-24 main-container <?php if (arg(0) != 'civicrm') {?>standard-container<?php } ?>">
  <div id="breadcrumb"><?php print $breadcrumb; ?></div>
  <?php if ($user->uid && arg(0) == 'civicrm') { ?>
  	<div id="edit-profile">
    <a href="<?php print base_path(); ?>user/<?php print $user->uid; ?>/edit">
  		<div class="icon settings-icon"></div>Update your Profile 
  	</a>
  	<a href="<?php print base_path(); ?>logout" class="logout">
  		<div class="icon logout-icon"></div> Logout 
  	</a>
        <div class="icon settings-icon"></div><span style="color:#4EBAFF;"> <?php echo $role; ?> </span>
  	</div>
  	<div class="account-info-wrapper">
  		<div class="account-info">
  			<div class="greeting">
<?php
$morning = "Good Morning";
$afternoon = "Good Afternoon";
$evening = "Good Evening";
$night = "Good Night";
$offset = -4; // time offset from server time (in hours) to adjust time zone
// -------------
$now = time() + (60 * 60 * $offset);
	if(date("G", $now) >= "5" && date("G", $now) <= "11"){ echo $morning;
	}elseif(date("G", $now) >= "12" && date("G", $now) <= "17"){ echo $afternoon;
	}elseif(date("G", $now) >= "18" && date("G", $now) <= "20"){ echo $evening;
	}else{ echo $night; }
			
?>,
  			</div>
  			<div class="user-name">
  				<?php print $user->name; ?>
                <?php $contact_id = $_SESSION['userID']; ?>
  			</div>
  		</div>
  	</div>
    <?php if ( $role == 'Superuser' || $role == 'SOS' ) { ?>
    	<div class="sos_job">
    	    [ <?php if ( $_SESSION['CiviCRM']['jobID'] ) { echo 'Job ID: '.$_SESSION['CiviCRM']['jobID'].' // '; } ?>
        	<a href="#" class="setJob" title="Set SOS JobID" onclick="setJobID( );return false;">Set Job #</a> ]
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

  <?php print $closure; ?>

</div>

<div id="dialog" style="display: none;">
     <form action="<?php $_SESSION['CiviCRM']['jobID'] = $_POST['set_JobID']; ?>" method="post" id="formSetJob">
        Enter a new SOS Job ID<br/>
     	<input type="text" id="set_jobID" name="set_JobID" />
     </form>
</div>

<script type="text/javascript">
function setJobID( ) {
    cj("#dialog").show( );
    cj("#dialog").dialog({
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
