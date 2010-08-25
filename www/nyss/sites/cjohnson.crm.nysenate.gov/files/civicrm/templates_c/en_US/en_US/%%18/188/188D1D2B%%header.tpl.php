<?php /* Smarty version 2.6.26, created on 2010-08-13 11:34:41
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
<?php echo '
<script>
$(\'.civi-general-search\').append(\'<div id="general-form-hack"></div>\');
	$(\'#general-form-hack\').hide()
		.load(\''; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/custom&csid=15&reset=1&snippet=1'), $this);?>
<?php echo '\', 
			function(){
				$(\'#general-form-hack #Custom input[type=hidden]\').appendTo(\'#gen-search-wrapper\');
			});
</script>
'; ?>


            
            
            
            
            
	<?php endif; ?>

</div>
<span class="primary-link create-link">
		<span id="create-link" class="main-menu-item">
			<div class="skin-icon link-icon"></div>
			CREATE
			
		</span>
		<div class="menu-container">
			<ul class="menu-ul innerbox">

	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'view all activities' )): ?>
	<li><div class="menu-item">
	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/activity&reset=1&action=add&context=standalone'), $this);?>
">New Activity</a></div></li>
    <?php endif; ?>
	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'access all cases and activities' )): ?>
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/add&reset=1&action=add&atype=13&context=standalone'), $this);?>
">New Case</a></div></li>
	<?php endif; ?>
	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'add contacts' )): ?>
		<li><div class="menu-item">
		<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/activity/add&atype=3&action=add&reset=1&context=standalone'), $this);?>
">New Email</a></div></li>
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
<div id="bluebirds"></div>
<div class="clear"></div>
<div class="civi-navigation-section">
<div class="civi-adv-search-linkwrap">
<?php if ($this->_tpl_vars['ssID'] || $this->_tpl_vars['rows'] || $this->_tpl_vars['savedSearch']): ?>
	<div class="civi-advanced-search-button">
	<div class="civi-advanced-search-link-inner">
		<span>
		<div class="icon crm-accordion-pointer"></div>
		<?php if ($this->_tpl_vars['ssID'] || $this->_tpl_vars['rows']): ?>
  <?php if ($this->_tpl_vars['savedSearch']): ?>
    <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['savedSearch']['name'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit %1 Smart Group Below<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
  <?php else: ?>
    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Search Criteria Below<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
  <?php endif; ?>
  <?php else: ?>
  <?php if ($this->_tpl_vars['savedSearch']): ?>
    <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['savedSearch']['name'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit %1 Smart Group Below<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
  <?php else: ?>
    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Search Criteria Below<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
  <?php endif; ?>
  <?php endif; ?>
		</span>
	</div>
	</div>	
<?php else: ?>
	<div class="civi-advanced-search-link">
	<div class="civi-advanced-search-link-inner">
		<span>
		<div class="icon crm-accordion-pointer"></div>
		ADVANCED SEARCH
		</span>
	</div>
	</div>	
<?php endif; ?>

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
<ul id="nyss-menu">
<?php echo $this->_tpl_vars['navigation']; ?>

</ul>
<?php echo '
<script type="text/javascript">
	   cj(\'div#toolbar-box div.m\').html(cj(".civi-menu").html());
	   cj(\'#nyss-menu\').ready( function(){ 
			cj(\'.outerbox\').css({ \'margin-top\': \'4px\'});
			cj(\'#root-menu-div .menu-ul li\').css({ \'padding-bottom\' : \'2px\', \'margin-top\' : \'2px\' });
			cj(\'img.menu-item-arrow\').css({ \'top\' : \'4px\' }); 
		});
		cj(\'#civicrm-home\').parent().hide();
	var resourceBase   = '; ?>
"<?php echo $this->_tpl_vars['config']->resourceBase; ?>
"<?php echo ';
	cj(\'#nyss-menu\').menu( {arrowSrc: resourceBase + \'packages/jquery/css/images/arrow.png\'} );
</script>
'; ?>


</div><!-- /.civi-menu -->

<div class="civi-adv-search-body crm-form-block">
	

	<div id="advanced-search-form"></div>
	
  <?php if ($this->_tpl_vars['ssID'] || $this->_tpl_vars['rows'] || $this->_tpl_vars['savedSearch']): ?>
    
  <?php else: ?>
    	
	<?php echo '
	<script>
	cj(document).ready(function() {
	 if (cj(\'form#Advanced\').length == 0) {
	  cj(\'#advanced-search-form\').load(\''; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/advanced?reset=1&snippet=1'), $this);?>
<?php echo '\');
	  } else {
	  cj(\'.civi-advanced-search-link\').removeClass(\'civi-advanced-search-link\').addClass(\'civi-advanced-search-button\');
	  }
	  });
	</script>
	'; ?>


  <?php endif; ?>

</div>





</div>





<?php echo '
<script>

    var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest','q' => 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=navigation','h' => 0), $this);?>
"<?php echo ';

    cj( \'#civi_sort_name\' ).autocomplete( contactUrl, {
        width: 200,
        selectFirst: false,
        minChars:3,
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

