<?php /* Smarty version 2.6.26, created on 2010-04-14 20:34:09
         compiled from Custom/header.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'Custom/header.tpl', 29, false),array('function', 'help', 'Custom/header.tpl', 29, false),array('function', 'crmURL', 'Custom/header.tpl', 37, false),)), $this); ?>

<?php if (! $this->_tpl_vars['urlIsPublic']): ?>
 <div id="header-access">
 <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Access Keys:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo smarty_function_help(array('id' => 'accesskeys'), $this);?>

 </div>
<?php endif; ?>

<div class="civi-search-section">
<div class="civi-contact-search">
	<div class="civi-search-title">Find Contacts</div>
<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'giveMeAllACLs' ) )): ?>
            <form action="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/basic','h' => 0), $this);?>
" name="search_block" id="id_search_block" method="post" onsubmit="getSearchURLValue( );">
            	<div class="input-wrapper">
                <input type="text" class="form-text" id="civi_sort_name" name="sort_name" value="enter name or email"/>
                <input type="hidden" id="sort_contact_id" value="" />
                <input type="submit" value="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Go<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" name="_qf_Basic_refresh" class="form-submit default" style="display: none;"/>
            	</div>
            </form>
	<?php endif; ?>

</div>

<div class="civi-general-search">
	<div class="civi-search-title">Find Anything!</div>
<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'giveMeAllACLs' ) )): ?>
  
            
<form id="id_search_block" name="Custom" method="post" action="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/custom','h' => 0), $this);?>
">
            	<div class="input-wrapper" id="gen-search-wrapper">

    <input type="text" class="form-text" id="civi_text_search" name="text" value="enter any text">
    <input type="hidden" id="table" name="table" value="">
	<input type="submit" value="Search" name="_qf_Custom_refresh" style="display: none;" class="form-submit default"> 
</div>
</form>
            
            
            
            
            
	<?php endif; ?>

</div>
<span class="primary-link create-link">
		<span id="create-link" class="main-menu-item">
			<div class="skin-icon link-icon"></div>
			CREATE
			
		</span>
		<div class="menu-container">
			<ul class="menu-ul innerbox">

	<li><div class="menu-item">
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/activity&reset=1&action=add&context=standalone'), $this);?>
">New Activity</a></div></li>
	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'access all cases and activities' )): ?>
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/add&reset=1&action=add&atype=13&context=standalone'), $this);?>
">New Case</a></div></li>
	<?php endif; ?>
	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'access CiviMail' )): ?>
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/activity/add&atype=3&action=add&reset=1&context=standalone'), $this);?>
">New Email</a></div></li>
	<?php endif; ?>
	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'administer CiviCRM' )): ?>
	<li style="position: relative;" class="menu-separator"><div class="menu-item"></div></li>
		<li ><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/tag&reset=1&action=add'), $this);?>
">New Tag</a></div></li>
		<li ><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/tag&reset=1'), $this);?>
">Manage Tags</a></div></li>
	<?php endif; ?>
	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'edit groups' )): ?>
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/group/add&reset=1'), $this);?>
">New Group</a></div></li>
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/group&reset=1'), $this);?>
">Manage Groups</a></div></li>
	<?php endif; ?>
	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'add contacts' )): ?>
		<li style="position: relative;" class="menu-separator"><div class="menu-item"></div></li>	
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/add&reset=1&ct=Individual'), $this);?>
">New Individual</a></div></li>
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/add&reset=1&ct=Household'), $this);?>
">New Household</a></div></li>
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/add&reset=1&ct=Organization'), $this);?>
">New Organization</a></div></li>
	<?php endif; ?>
	</ul>
		</div>
	</span><!-- /.custom-search-link -->	

</div>
<div class="clear"></div>
<div class="civi-navigation-section">
<div class="civi-adv-search-linkwrap">
	<div class="civi-advanced-search-link">
	<div class="civi-advanced-search-link-inner">
		<span>
		<div class="icon crm-accordion-pointer"></div>
		ADVANCED SEARCH
		</span>
	</div>
	</div>	
</div>
<div class="civi-menu">
	<?php if (isset ( $this->_tpl_vars['browserPrint'] ) && $this->_tpl_vars['browserPrint']): ?>
				<div id="printer-friendly">
			<a href="javascript:window.print()" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Print this page.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
			<div class="ui-icon ui-icon-print"></div>
			</a>
		</div>
		<?php else: ?>
				<div id="printer-friendly">
			<a href="<?php echo $this->_tpl_vars['printerFriendly']; ?>
" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Printer-friendly view of this page.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
			<div class="ui-icon ui-icon-print"></div>
			</a>
		</div>
	<?php endif; ?>

<?php 
   	global $user;

  	if (in_array('Superuser', array_values($user->roles))) {
    	$this->assign('viewAdmin','true');
    }
 ?>
	<?php if ($this->_tpl_vars['viewAdmin']): ?> 
	<div class="admin-link">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin&reset=1&snippet=1'), $this);?>
" id="civi-admin-link" class="main-menu-item">
		<div class="skin-icon link-icon"></div>
		ADMINISTER
		
	</a>
	</div><!-- /.admin-link -->
	<?php endif; ?>


	<div class="civi-admin-block-wrapper">
		<div class="civi-admin-block">
			<div class="crm-loading-element"></div>
		</div>
	</div><!-- /.admin-block-wrapper -->
	
	<div class="primary-link custom-search-link">
		<span id="civi-custom-search-link" class="main-menu-item">
			<div class="skin-icon link-icon"></div>
			CUSTOM SEARCHES
			
		</span>
		<div class="menu-container">
		<ul class="menu-ul innerbox">
<li><div class="menu-item">
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/search&reset=1'), $this);?>
">Find Cases</a></div></li>
<li><div class="menu-item">
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/activity/search&reset=1'), $this);?>
">Find Activities</a></div></li>
<li style="position: relative;" class="menu-separator"><div class="menu-item"></div></li>
<li><div class="menu-item">
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/custom&reset=1&csid=8'), $this);?>
">Activity Search</a></div></li>
<li><div class="menu-item">
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/custom&reset=1&csid=11'), $this);?>
">Contacts by Date Added</a></div></li>
<li><div class="menu-item">
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/custom&reset=1&csid=2'), $this);?>
">Contributors by Aggregate Totals</a></div></li>
<li><div class="menu-item">
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/custom&reset=1&csid=6'), $this);?>
">Proximity Search</a></div></li>
<li style="position: relative;" class="menu-separator"><div class="menu-item"></div></li>
<li><div class="menu-item">
Create a custom search with:
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/builder&reset=1'), $this);?>
">Search Builder</a></div></li>

		</ul>
		
		</div>
		
		
	</div><!-- /.custom-search-link -->

<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'access CiviReport' )): ?>
	
	<div class="primary-link reports-link">
		<span href="#" id="reports-link" class="main-menu-item">
			<div class="skin-icon link-icon"></div>
			REPORTS
		</span>
		<div class="menu-container">
			<ul class="menu-ul innerbox">
	<li><div class="menu-item"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/report/list&reset=1'), $this);?>
">Reports Listing</a></div></li>
		<li style="position: relative;" class="menu-separator"><div class="menu-item"></div></li>
	<li><div class="menu-item"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/report/instance/1&reset=1'), $this);?>
">Constituent Report (Summary)</a></div></li>
	<li><div class="menu-item"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/report/instance/2&reset=1'), $this);?>
">Constituent Report (Detail)</a></div></li>
	<li><div class="menu-item"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/report/instance/16&reset=1'), $this);?>
">Activity Report</a></div></li>
	<li><div class="menu-item"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/report/instance/17&reset=1'), $this);?>
">Relationship Report</a></div></li>
<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'administer Reports ' )): ?>
	<li style="position: relative;" class="menu-separator"><div class="menu-item"></div></li>
	<li><div class="menu-item"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/report/template/list&reset=1'), $this);?>
">Create Reports from Templates</a></div></li>
<?php endif; ?>
	</ul>
		</div>
	</div><!-- /.reports-link -->	
<?php endif; ?>


</div><!-- /.civi-menu -->

<div class="civi-adv-search-body crm-form-block">
	

	<div id="advanced-search-form"></div>
	<?php echo '
	<script>
	$(\'#advanced-search-form\').load(\''; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/advanced?reset=1&snippet=1'), $this);?>
<?php echo '\');
	</script>

	'; ?>

</div>





</div>





<?php echo '
<script>
    var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/contactlist','q' => 'context=navigation','h' => 0), $this);?>
"<?php echo ';

    cj( \'#civi_sort_name\' ).autocomplete( contactUrl, {
        width: 200,
        selectFirst: false,
        minChars:2,
        matchContains: true 	 
    }).result(function(event, data, formatted) {
       document.location='; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','h' => 0,'q' => 'reset=1&cid='), $this);?>
"<?php echo '+data[1];
       return false;
    });    

</script>

'; ?>


<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>

