<?php /* Smarty version 2.6.26, created on 2010-05-24 17:31:01
         compiled from CRM/Case/Form/Case.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Case/Form/Case.tpl', 36, false),array('function', 'help', 'CRM/Case/Form/Case.tpl', 71, false),array('modifier', 'crmReplace', 'CRM/Case/Form/Case.tpl', 72, false),)), $this); ?>
<div class="crm-block crm-form-block">

<?php if ($this->_tpl_vars['action'] != 8 && $this->_tpl_vars['action'] != 32768): ?>
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
<?php endif; ?>

<h3><?php if ($this->_tpl_vars['action'] == 8): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Case<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php elseif ($this->_tpl_vars['action'] == 32768): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Restore Case<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></h3>
<?php if ($this->_tpl_vars['action'] == 8 || $this->_tpl_vars['action'] == 32768): ?> 
      <div class="messages status"> 
        <dl> 
          <dt><div class="icon inform-icon"></div></dt> 
          <dd> 
          <?php if ($this->_tpl_vars['action'] == 8): ?>
            <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click Delete to move this case and all associated activities to the Trash.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 
          <?php else: ?>
            <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click Restore to retrieve this case and all associated activities from the Trash.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 
          <?php endif; ?>
          </dd> 
       </dl> 
      </div> 
<?php else: ?>
<table class="form-layout">
<?php if ($this->_tpl_vars['clientName']): ?>
    <tr><td class="label font-size12pt"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Client<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td class="font-size12pt bold view-value"><?php echo $this->_tpl_vars['clientName']; ?>
</td></tr>
<?php elseif (! $this->_tpl_vars['clientName'] && $this->_tpl_vars['action'] == 1): ?> 
    <tr class="form-layout-compressed" border="0">			      
            <?php if ($this->_tpl_vars['context'] == 'standalone'): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/NewContact.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
    </tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['form']['medium_id']['html'] && $this->_tpl_vars['form']['activity_location']['html']): ?>
    <tr>
        <td class="label"><?php echo $this->_tpl_vars['form']['medium_id']['label']; ?>
</td>
        <td class="view-value"><?php echo $this->_tpl_vars['form']['medium_id']['html']; ?>
&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['activity_location']['label']; ?>
 &nbsp;<?php echo $this->_tpl_vars['form']['activity_location']['html']; ?>
</td>
    </tr> 
<?php endif; ?>

<?php if ($this->_tpl_vars['form']['activity_details']['html']): ?>
    <tr>
        <td class="label"><?php echo $this->_tpl_vars['form']['activity_details']['label']; ?>
<?php echo smarty_function_help(array('id' => "id-details",'file' => "CRM/Case/Form/Case.hlp"), $this);?>
</td>
        <td class="view-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['activity_details']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge40') : smarty_modifier_crmReplace($_tmp, 'class', 'huge40')); ?>
</td>
    </tr>
<?php endif; ?>

<?php if ($this->_tpl_vars['groupTree']): ?>
    <tr>
       <td colspan="2"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/CustomData.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
    </tr>    
<?php endif; ?>

<?php if ($this->_tpl_vars['form']['activity_subject']['html']): ?>
    <tr><td class="label"><?php echo $this->_tpl_vars['form']['activity_subject']['label']; ?>
<?php echo smarty_function_help(array('id' => "id-activity_subject",'file' => "CRM/Case/Form/Case.hlp"), $this);?>
</td><td><?php echo $this->_tpl_vars['form']['activity_subject']['html']; ?>
</td></tr>
<?php endif; ?>

<?php if ($this->_tpl_vars['activityTypeFile']): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Case/Form/Activity/".($this->_tpl_vars['activityTypeFile']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['form']['duration']['html']): ?>
    <tr>
      <td class="label"><?php echo $this->_tpl_vars['form']['duration']['label']; ?>
</td>
      <td class="view-value">
        <?php echo $this->_tpl_vars['form']['duration']['html']; ?>

         <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Total time spent on this activity (in minutes).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
      </td>
    </tr> 
<?php endif; ?>

<?php if ($this->_tpl_vars['form']['tag']['html']): ?>
    <tr>
      <td class="label"><?php echo $this->_tpl_vars['form']['tag']['label']; ?>
</td>
      <td class="view-value"><div class="crm-select-container"><?php echo $this->_tpl_vars['form']['tag']['html']; ?>
</div>
                             <?php echo '
                             <script type="text/javascript">
                                                     $("select[multiple]").crmasmSelect({
                                                              addItemTarget: \'bottom\',
                                                              animate: true,
                                                              highlight: true,
                                                              sortable: true,
                                                              respectParents: true
                                                     });
                              </script>
                              '; ?>

      </td>
    </tr>

<?php endif; ?>

</table>
<?php endif; ?>	

<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formNavigate.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>