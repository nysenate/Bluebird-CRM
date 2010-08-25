<?php /* Smarty version 2.6.26, created on 2010-07-01 11:40:39
         compiled from CRM/Contact/Page/DashBoardDashlet.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Page/DashBoardDashlet.tpl', 31, false),array('function', 'crmURL', 'CRM/Contact/Page/DashBoardDashlet.tpl', 33, false),array('function', 'help', 'CRM/Contact/Page/DashBoardDashlet.tpl', 47, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/dashboard.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/openFlashChart.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div class="crm-dashboard-buttons crm-submit-buttons">
<a href="javascript:addDashlet( );" class="button show-add">
	<span><div class="icon settings-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Configure Your Dashboard<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>

<a style="display:none;" href="<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/dashboard",'q' => "reset=1"), $this);?>
" class="button show-done" style="margin-left: 6px;">
	<span><div class="icon check-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Done<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>

<a style="float:right;" href="<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/dashboard",'q' => "reset=1&resetCache=1"), $this);?>
" class="button show-refresh" style="margin-left: 6px;">
	<span> <div class="icon refresh-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Refresh Dashboard Data<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>

</div>
<div class="crm-block crm-content-block">
<div id="empty-message" class='hiddenElement'>
    <div class="status">
        <div class="font-size12pt bold"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Welcome to your Home Dashboard<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
        <div class="display-block">
            <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Your dashboard provides a one-screen view of the data that's most important to you. Graphical or tabular data is pulled from the reports you select,
            and is displayed in 'dashlets' (sections of the dashboard).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => "id-dash_welcome",'file' => "CRM/Contact/Page/Dashboard.hlp"), $this);?>

        </div>
    </div>
</div>

<div id="configure-dashlet" class='hiddenElement'></div>
<div id="civicrm-dashboard">
  <!-- You can put anything you like here.  jQuery.dashboard() will remove it. -->
  <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Javascript must be enabled in your browser in order to use the dashboard features.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</div>
<div class="clear"></div>

<?php echo '
<script type="text/javascript">
  function addDashlet(  ) {
      var dataURL = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/dashlet','q' => 'reset=1&snippet=1','h' => 0), $this);?>
"<?php echo ';

      cj.ajax({
         url: dataURL,
         success: function( content ) {
             cj("#civicrm-dashboard").hide( );
             cj(\'.show-add\').hide( );
             cj(\'.show-refresh\').hide( );
             cj(\'.show-done\').show( );
             cj("#empty-message").hide( );
             cj("#configure-dashlet").show( ).html( content );
         }
      });
  }
        
</script>
'; ?>

</div>