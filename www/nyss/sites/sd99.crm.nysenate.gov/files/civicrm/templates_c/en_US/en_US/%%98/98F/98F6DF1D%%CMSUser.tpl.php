<?php /* Smarty version 2.6.26, created on 2010-08-23 16:36:26
         compiled from CRM/common/CMSUser.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/common/CMSUser.tpl', 30, false),array('function', 'crmURL', 'CRM/common/CMSUser.tpl', 126, false),)), $this); ?>
<?php if ($this->_tpl_vars['showCMS']): ?>   <fieldset class="crm-group crm_user-group">
      <div class="messages help cms_user_help-section">
	 <?php if (! $this->_tpl_vars['isCMS']): ?>
	    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>If you would like to create an account on this site, check the box below and enter a user name<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	    <?php if ($this->_tpl_vars['form']['cms_pass']): ?>
	       <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>and a password<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	    <?php endif; ?>
	 <?php else: ?>
	    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Please enter a user name to create an account<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	 <?php endif; ?>.
	 <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['loginUrl'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>If you already have an account, <a href='%1'>please login</a> before completing this form.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
      </div>
      <div><?php echo $this->_tpl_vars['form']['cms_create_account']['html']; ?>
 <?php echo $this->_tpl_vars['form']['cms_create_account']['label']; ?>
</div>
     <div id="details" class="crm_user_signup-section">
	 <table class="form-layout-compressed">
	    <tr class="cms_name-section">
	       <td><?php echo $this->_tpl_vars['form']['cms_name']['label']; ?>
</td>
	       <td><?php echo $this->_tpl_vars['form']['cms_name']['html']; ?>
 <a id="checkavailability" href="#" onClick="return false;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><strong>Check Availability</strong><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
	          <span id="msgbox" style="display:none"></span><br />
	          <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
	       </td>
	    </tr>
    
	    <?php if ($this->_tpl_vars['form']['cms_pass']): ?>
	       <tr class="cms_pass-section">
	          <td><?php echo $this->_tpl_vars['form']['cms_pass']['label']; ?>
</td>
	          <td><?php echo $this->_tpl_vars['form']['cms_pass']['html']; ?>
</td>
	       </tr>        
	       <tr class="crm_confirm_pass-section">
	          <td><?php echo $this->_tpl_vars['form']['cms_confirm_pass']['label']; ?>
</td>
	          <td><?php echo $this->_tpl_vars['form']['cms_confirm_pass']['html']; ?>
<br />
	             <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Provide a password for the new account in both fields.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	          </td>
	       </tr>
	    <?php endif; ?>
	 </table>        
     </div>
   </fieldset>

   <?php echo '
   <script type="text/javascript">
   '; ?>

   <?php if (! $this->_tpl_vars['isCMS']): ?>
      <?php echo '
      if ( document.getElementsByName("cms_create_account")[0].checked ) {
	 show(\'details\');
      } else {
	 hide(\'details\');
      }
      '; ?>

   <?php endif; ?>
   <?php echo '
   function showMessage( frm )
   {
      var cId = '; ?>
'<?php echo $this->_tpl_vars['cId']; ?>
'<?php echo ';
      if ( cId ) {
	 alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You are logged-in user<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\');
	 frm.checked = false;
      } else {
	 var siteName = '; ?>
'<?php echo $this->_tpl_vars['config']->userFrameworkBaseURL; ?>
'<?php echo ';
	 alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Please login if you have an account on this site with the link<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo ' \' + siteName  );
      }
   }
   var lastName = null;
   cj("#checkavailability").click(function() {
      var cmsUserName = cj.trim(cj("#cms_name").val());
      if ( lastName == cmsUserName) {
	 /*if user checking the same user name more than one times. avoid the ajax call*/
	 return;
      }
      /*don\'t allow special character and for joomla minimum username length is two*/

      var spchar = "\\<|\\>|\\"|\\\'|\\%|\\;|\\(|\\)|\\&|\\\\\\\\|\\/";

      '; ?>
<?php if ($this->_tpl_vars['config']->userFramework == 'Drupal'): ?><?php echo '
	 spchar = spchar + "|\\~|\\`|\\:|\\@|\\!|\\=|\\#|\\$|\\^|\\*|\\{|\\}|\\\\[|\\\\]|\\+|\\?|\\,"; 
      '; ?>
<?php endif; ?><?php echo '	
      var r = new RegExp( "["+spchar+"]", "i");
      /*regular expression \\\\ matches a single backslash. this becomes r = /\\\\/ or r = new RegExp("\\\\\\\\").*/
      if ( r.exec(cmsUserName) ) {
	 alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Your username contains invalid characters<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\');
      	 return;
      } 
      '; ?>
<?php if ($this->_tpl_vars['config']->userFramework == 'Joomla'): ?><?php echo '
	 else if ( cmsUserName && cmsUserName.length < 2 ) {
	    alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Your username is too short<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\');
	    return;	
	 }
      '; ?>
<?php endif; ?><?php echo '
      if (cmsUserName) {
	 /*take all messages in javascript variable*/
	 var check        = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Checking...<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
	 var available    = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>This username is currently available.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
	 var notavailable = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>This username is taken.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
         
         //remove all the class add the messagebox classes and start fading
         cj("#msgbox").removeClass().addClass(\'cmsmessagebox\').css({"color":"#000","backgroundColor":"#FFC","border":"1px solid #c93"}).text(check).fadeIn("slow");
	 
      	 //check the username exists or not from ajax
	 var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/cmsuser','h' => 0), $this);?>
"<?php echo ';
	 
	 cj.post(contactUrl,{ cms_name:cj("#cms_name").val() } ,function(data) {
	    if ( data.name == "no") {/*if username not avaiable*/
	       cj("#msgbox").fadeTo(200,0.1,function() {
		  cj(this).html(notavailable).addClass(\'cmsmessagebox\').css({"color":"#CC0000","backgroundColor":"#F7CBCA","border":"1px solid #CC0000"}).fadeTo(900,1);
	       });
	    } else {
	       cj("#msgbox").fadeTo(200,0.1,function() {
		  cj(this).html(available).addClass(\'cmsmessagebox\').css({"color":"#008000","backgroundColor":"#C9FFCA", "border": "1px solid #349534"}).fadeTo(900,1);
	       });
	    }	    
	 }, "json");
	 lastName = cmsUserName;
      } else {
	 cj("#msgbox").removeClass().text(\'\').css({"backgroundColor":"#FFFFFF", "border": "0px #FFFFFF"}).fadeIn("fast");
      }
   });

   </script>
   '; ?>

   <?php if (! $this->_tpl_vars['isCMS']): ?>	
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/showHideByFieldValue.tpl", 'smarty_include_vars' => array('trigger_field_id' => 'cms_create_account','trigger_value' => "",'target_element_id' => 'details','target_element_type' => 'block','field_type' => 'radio','invert' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
   <?php endif; ?>
<?php endif; ?>