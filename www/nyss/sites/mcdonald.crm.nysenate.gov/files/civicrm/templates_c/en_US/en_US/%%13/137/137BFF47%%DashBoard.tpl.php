<?php /* Smarty version 2.6.26, created on 2010-08-23 10:50:46
         compiled from CRM/Case/Page/DashBoard.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Case/Page/DashBoard.tpl', 33, false),array('block', 'ts', 'CRM/Case/Page/DashBoard.tpl', 40, false),)), $this); ?>

<div class="crm-block crm-content-block">
<?php if ($this->_tpl_vars['notConfigured']): ?>     <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Case/Page/ConfigureError.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>

<?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/contact/view/case",'q' => "action=add&context=standalone&reset=1"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('newCaseURL', ob_get_contents());ob_end_clean(); ?>




<div class="crm-submit-buttons">
    <?php if ($this->_tpl_vars['newClient']): ?>	
	    <a href="<?php echo $this->_tpl_vars['newCaseURL']; ?>
" class="button"><span><div class="icon add-icon"></div> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add Case<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
    <?php endif; ?>
    <div class="crm-case-dashboard-switch-view-buttons">
        <?php if ($this->_tpl_vars['myCases']): ?>
                        <?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'access all cases and activities' )): ?>
                <a class="button" href="<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/case",'q' => "reset=1&all=1"), $this);?>
"><span><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>All Cases with Upcoming Activities<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
            <?php endif; ?>
        <?php else: ?>
            <a class="button" href="<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/case",'q' => "reset=1&all=0"), $this);?>
"><span><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>My Cases with Upcoming Activities<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
        <?php endif; ?>
        <a class="button" href="<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/case/search",'q' => "reset=1&case_owner=1&force=1"), $this);?>
"><span><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>My Cases<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
    </div>
</div>


<h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Summary of Case Involvement<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
<table class="report">
  <tr class="columnheader">
    <th>&nbsp;</th>
    <?php $_from = $this->_tpl_vars['casesSummary']['headers']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['header']):
?>
    <th scope="col" class="right" style="padding-right: 10px;"><a href="<?php echo $this->_tpl_vars['header']['url']; ?>
"><?php echo $this->_tpl_vars['header']['status']; ?>
</a></th>
    <?php endforeach; endif; unset($_from); ?>
  </tr>
  <?php $_from = $this->_tpl_vars['casesSummary']['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['caseType'] => $this->_tpl_vars['row']):
?>
   <tr class="crm-case-caseStatus">
   <th><strong><?php echo $this->_tpl_vars['caseType']; ?>
</strong></th>
   <?php $_from = $this->_tpl_vars['casesSummary']['headers']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['header']):
?>
    <?php $this->assign('caseStatus', $this->_tpl_vars['header']['status']); ?>
    <td class="label">
    <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['caseStatus']]): ?>
    <a href="<?php echo $this->_tpl_vars['row'][$this->_tpl_vars['caseStatus']]['url']; ?>
"><?php echo $this->_tpl_vars['row'][$this->_tpl_vars['caseStatus']]['count']; ?>
</a>
    <?php else: ?>
     0
    <?php endif; ?>
    </td>
   <?php endforeach; endif; unset($_from); ?>
  </tr>
  <?php endforeach; endif; unset($_from); ?>
</table>
<?php ob_start(); ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/case/search",'q' => "reset=1"), $this);?>
"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Find Cases<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('findCasesURL', ob_get_contents());ob_end_clean(); ?>

<span id='fileOnCaseStatusMsg' style="display:none;"></span><!-- Displays status from copy to case -->

<div class="spacer"></div>
    <h3><?php if ($this->_tpl_vars['myCases']): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>My Cases With Upcoming Activities<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>All Cases With Upcoming Activities<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></h3>
    <?php if ($this->_tpl_vars['upcomingCases']): ?>
    <div class="form-item">
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Case/Page/DashboardSelector.tpl", 'smarty_include_vars' => array('context' => 'dashboard','list' => 'upcoming','rows' => $this->_tpl_vars['upcomingCases'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </div>
    <?php else: ?>
        <div class="messages status">
	    <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['findCasesURL'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There are no open cases with activities scheduled in the next two weeks. Use %1 to expand your search.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </div>
    <?php endif; ?>

<div class="spacer"></div>
    <h3><?php if ($this->_tpl_vars['myCases']): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>My Cases With Recently Performed Activities<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>All Cases With Recently Performed Activities<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></h3>
    <?php if ($this->_tpl_vars['recentCases']): ?>
    <div class="form-item">
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Case/Page/DashboardSelector.tpl", 'smarty_include_vars' => array('context' => 'dashboard','list' => 'recent','rows' => $this->_tpl_vars['recentCases'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </div>
    <?php else: ?>
        <div class="messages status">
	    <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['findCasesURL'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There are no cases with activities scheduled in the past two weeks. Use %1 to expand your search.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </div>
    <?php endif; ?>

        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/activityView.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <div id="view-activity">
        <div id="activity-content"></div>
    </div>
<?php endif; ?>
</div>