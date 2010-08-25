<?php /* Smarty version 2.6.26, created on 2010-08-23 16:44:21
         compiled from CRM/Case/Audit/Report.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'truncate', 'CRM/Case/Audit/Report.tpl', 26, false),array('modifier', 'escape', 'CRM/Case/Audit/Report.tpl', 134, false),array('function', 'crmURL', 'CRM/Case/Audit/Report.tpl', 30, false),array('block', 'ts', 'CRM/Case/Audit/Report.tpl', 38, false),)), $this); ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
">
<head>
  <title><?php echo $this->_tpl_vars['pageTitle']; ?>
</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <base href="<?php echo CRM_Utils_System::crmURL(array('p' => "",'a' => true), $this);?>
" /><!--[if IE]></base><![endif]-->
  <style type="text/css" media="screen, print">@import url(<?php echo $this->_tpl_vars['config']->userFrameworkResourceURL; ?>
css/print.css);</style>
</head>

<body>
<div id="crm-container">
<h1><?php echo $this->_tpl_vars['pageTitle']; ?>
</h1>
<div id="report-date"><?php echo $this->_tpl_vars['reportDate']; ?>
</div>
<h2><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Summary<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h2>
<table class="report-layout">
    <tr>
    	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Client<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
       	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Status<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
        <th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Start Date<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case ID<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    </tr>
    <tr>
        <td class="crm-case-report-clientName"><?php echo $this->_tpl_vars['case']['clientName']; ?>
</td>
        <td class="crm-case-report-caseType"><?php echo $this->_tpl_vars['case']['caseType']; ?>
</td>
        <td class="crm-case-report-status"><?php echo $this->_tpl_vars['case']['status']; ?>
</td>
        <td class="crm-case-report-start_date"><?php echo $this->_tpl_vars['case']['start_date']; ?>
</td>
        <td class="crm-case-report-<?php echo $this->_tpl_vars['caseId']; ?>
"><?php echo $this->_tpl_vars['caseId']; ?>
</td> 
    </tr>
</table>
<h2><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Roles<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h2>
<table class ="report-layout">
    <tr>
    	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Role<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Name<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
       	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Phone<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
        <th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    </tr>

    <?php $_from = $this->_tpl_vars['caseRelationships']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relId'] => $this->_tpl_vars['row']):
?>
       <tr>
          <td class="crm-case-report-caserelationships-relation"><?php echo $this->_tpl_vars['row']['relation']; ?>
</td>
          <td class="crm-case-report-caserelationships-name"><?php echo $this->_tpl_vars['row']['name']; ?>
</td>
          <td class="crm-case-report-caserelationships-phone"><?php echo $this->_tpl_vars['row']['phone']; ?>
</td>
          <td class="crm-case-report-caserelationships-email"><?php echo $this->_tpl_vars['row']['email']; ?>
</td> 
       </tr>
    <?php endforeach; endif; unset($_from); ?>
    <?php $_from = $this->_tpl_vars['caseRoles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relTypeID'] => $this->_tpl_vars['relName']):
?>
         <?php if ($this->_tpl_vars['relTypeID'] != 'client'): ?> 
           <tr>
               <td><?php echo $this->_tpl_vars['relName']; ?>
</td>
               <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>(not assigned)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
               <td></td>
               <td></td>
           </tr>
         <?php else: ?>
           <tr>
               <td class="crm-case-report-caseroles-role"><?php echo $this->_tpl_vars['relName']['role']; ?>
</td>
               <td class="crm-case-report-caseroles-sort_name"><?php echo $this->_tpl_vars['relName']['sort_name']; ?>
</td>
               <td class="crm-case-report-caseroles-phone"><?php echo $this->_tpl_vars['relName']['phone']; ?>
</td>
               <td class="crm-case-report-caseroles-email"><?php echo $this->_tpl_vars['relName']['email']; ?>
</td>
           </tr> 
         <?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
</table>
<br />

<?php if ($this->_tpl_vars['otherRelationships']): ?>
    <table  class ="report-layout">
       	<tr>
    		<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Client Relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Name<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Phone<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	</tr>
        <?php $_from = $this->_tpl_vars['otherRelationships']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relId'] => $this->_tpl_vars['row']):
?>
        <tr>
            <td class="crm-case-report-otherrelationships-relation"><?php echo $this->_tpl_vars['row']['relation']; ?>
</td>
            <td class="crm-case-report-otherrelationships-name"><?php echo $this->_tpl_vars['row']['name']; ?>
</td>
            <td class="crm-case-report-otherrelationships-phone"><?php echo $this->_tpl_vars['row']['phone']; ?>
</td>
            <td class="crm-case-report-otherrelationships-email"><?php echo $this->_tpl_vars['row']['email']; ?>
</td>
        </tr>
        <?php endforeach; endif; unset($_from); ?>
    </table>
    <br />
<?php endif; ?>

<?php if ($this->_tpl_vars['globalRelationships']): ?>
    <table class ="report-layout">
       	<tr>
    	 	<th class="reports-header"><?php echo $this->_tpl_vars['globalGroupInfo']['title']; ?>
</th>
     	 	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Phone<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	 	<th class="reports-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	</tr>
        <?php $_from = $this->_tpl_vars['globalRelationships']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relId'] => $this->_tpl_vars['row']):
?>
        <tr>
            <td class="crm-case-report-globalrelationships-sort_name"><?php echo $this->_tpl_vars['row']['sort_name']; ?>
</td>
            <td class="crm-case-report-globalrelationships-phone"><?php echo $this->_tpl_vars['row']['phone']; ?>
</td>
            <td class="crm-case-report-globalrelationships-email"><?php echo $this->_tpl_vars['row']['email']; ?>
</td>
        </tr>
	    <?php endforeach; endif; unset($_from); ?>
    </table>
<?php endif; ?>

<h2><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Activities<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h2>
<?php $_from = $this->_tpl_vars['activities']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['activity']):
?>
  <table  class ="report-layout">
       <?php $_from = $this->_tpl_vars['activity']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fieldloop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fieldloop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['field']):
        $this->_foreach['fieldloop']['iteration']++;
?>
           <tr class="crm-case-report-activity-<?php echo $this->_tpl_vars['field']['label']; ?>
">
             <th scope="row" class="label"><?php echo ((is_array($_tmp=$this->_tpl_vars['field']['label'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</th>
             <?php if ($this->_tpl_vars['field']['label'] == 'Activity Type' || $this->_tpl_vars['field']['label'] == 'Status'): ?>
                <td class="bold"><?php echo ((is_array($_tmp=$this->_tpl_vars['field']['value'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</th> 
             <?php else: ?>
                <td><?php echo ((is_array($_tmp=$this->_tpl_vars['field']['value'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</th> 
             <?php endif; ?>
           </tr>
       <?php endforeach; endif; unset($_from); ?>
  </table>
  <br />
<?php endforeach; endif; unset($_from); ?>
</div>
</body>
</html>




