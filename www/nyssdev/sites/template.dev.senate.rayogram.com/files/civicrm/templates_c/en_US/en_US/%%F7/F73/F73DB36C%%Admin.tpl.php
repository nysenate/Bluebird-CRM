<?php /* Smarty version 2.6.26, created on 2010-06-07 12:59:45
         compiled from CRM/Admin/Page/Admin.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Page/Admin.tpl', 30, false),array('function', 'cycle', 'CRM/Admin/Page/Admin.tpl', 72, false),)), $this); ?>
<?php if ($this->_tpl_vars['newVersion']): ?>
    <div class="messages status">
        <div class="icon help-icon"></div>
            <p><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['newVersion'],'2' => $this->_tpl_vars['localVersion'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>A newer version of CiviCRM is available: %1 (this site is currently running %2).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
            <p><?php $this->_tag_stack[] = array('ts', array('1' => 'http://civicrm.org/','2' => 'http://civicrm.org/download/')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Read about the new version on <a href='%1'>our website</a> and <a href='%2'>download it here</a>.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
        
    </div>
<?php endif; ?>

<div id="help" class="description section-hidden-border">
<?php ob_start(); ?><img src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/TreePlus.gif" alt="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>plus sign<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" style="vertical-align: bottom; height: 20px; width: 20px;" /><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('plusImg', ob_get_contents());ob_end_clean(); ?>
<?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['plusImg'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Administer your CiviCRM site using the links on this page. Click %1 for descriptions of the options in each section.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</div>

<?php echo '<div class="crm-content-block">'; ?><?php $_from = $this->_tpl_vars['adminPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['adminLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['adminLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['groupName'] => $this->_tpl_vars['group']):
        $this->_foreach['adminLoop']['iteration']++;
?><?php echo '<div id = "id_'; ?><?php echo $this->_tpl_vars['groupName']; ?><?php echo '_show" class="section-hidden label'; ?><?php if (($this->_foreach['adminLoop']['iteration'] == $this->_foreach['adminLoop']['total']) == false): ?><?php echo ' section-hidden-border'; ?><?php endif; ?><?php echo '"><table class="form-layout"><tr><td width="20%" class="font-size11pt" style="vertical-align: top;">'; ?><?php echo $this->_tpl_vars['group']['show']; ?><?php echo ' '; ?><?php echo $this->_tpl_vars['group']['title']; ?><?php echo '</td><td width="80%" style="white-space: nowrap;;"><table class="form-layout" width="100%"><tr><td width="50%" style="padding: 0px;">'; ?><?php $_from = $this->_tpl_vars['group']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['groupLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['groupLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['panelName'] => $this->_tpl_vars['panelItem']):
        $this->_foreach['groupLoop']['iteration']++;
?><?php echo '&raquo;&nbsp;<a href="'; ?><?php echo $this->_tpl_vars['panelItem']['url']; ?><?php echo '"'; ?><?php if ($this->_tpl_vars['panelItem']['extra']): ?><?php echo ' '; ?><?php echo $this->_tpl_vars['panelItem']['extra']; ?><?php echo ''; ?><?php endif; ?><?php echo ' id="idc_'; ?><?php echo $this->_tpl_vars['panelItem']['id']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['panelItem']['title']; ?><?php echo '</a><br />'; ?><?php if ($this->_foreach['groupLoop']['iteration'] == $this->_tpl_vars['group']['perColumn']): ?><?php echo '</td><td width="50%" style="padding: 0px;">'; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</td></tr></table></td></tr></table></div><div id="id_'; ?><?php echo $this->_tpl_vars['groupName']; ?><?php echo '"><fieldset><legend><strong>'; ?><?php echo $this->_tpl_vars['group']['hide']; ?><?php echo ''; ?><?php echo $this->_tpl_vars['group']['title']; ?><?php echo '</strong></legend><table class="form-layout">'; ?><?php $_from = $this->_tpl_vars['group']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['groupLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['groupLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['panelName'] => $this->_tpl_vars['panelItem']):
        $this->_foreach['groupLoop']['iteration']++;
?><?php echo '<tr class="'; ?><?php echo smarty_function_cycle(array('values' => "odd-row,even-row",'name' => $this->_tpl_vars['groupName']), $this);?><?php echo '"><td style="vertical-align: top; width:24px;"><a href="'; ?><?php echo $this->_tpl_vars['panelItem']['url']; ?><?php echo '"'; ?><?php if ($this->_tpl_vars['panelItem']['extra']): ?><?php echo ' '; ?><?php echo $this->_tpl_vars['panelItem']['extra']; ?><?php echo ''; ?><?php endif; ?><?php echo ' ><img src="'; ?><?php echo $this->_tpl_vars['config']->resourceBase; ?><?php echo 'i/'; ?><?php echo $this->_tpl_vars['panelItem']['icon']; ?><?php echo '" alt="'; ?><?php echo $this->_tpl_vars['panelItem']['title']; ?><?php echo '"/></a></td><td class="report font-size11pt" style="vertical-align: text-top;" width="20%"><a href="'; ?><?php echo $this->_tpl_vars['panelItem']['url']; ?><?php echo '"'; ?><?php if ($this->_tpl_vars['panelItem']['extra']): ?><?php echo ' '; ?><?php echo $this->_tpl_vars['panelItem']['extra']; ?><?php echo ''; ?><?php endif; ?><?php echo ' id="id_'; ?><?php echo $this->_tpl_vars['panelItem']['id']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['panelItem']['title']; ?><?php echo '</a></td><td class="description"  style="vertical-align: text-top;" width="75%">'; ?><?php echo $this->_tpl_vars['panelItem']['desc']; ?><?php echo '</td></tr>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</table></fieldset></div>'; ?><?php endforeach; endif; unset($_from); ?><?php echo ''; ?>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/showHide.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>