<?php /* Smarty version 2.6.26, created on 2010-08-13 13:22:53
         compiled from CRM/Contact/Form/Search/Intro.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Contact/Form/Search/Intro.tpl', 30, false),array('block', 'ts', 'CRM/Contact/Form/Search/Intro.tpl', 32, false),)), $this); ?>
<?php if ($this->_tpl_vars['context'] == 'smog'): ?>
    <?php if ($this->_tpl_vars['permissionedForGroup']): ?>
    <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('q' => "context=amtg&amtgID=".($this->_tpl_vars['group']['id'])."&reset=1"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('addMembersURL', ob_get_contents());ob_end_clean(); ?>
    <div class="action-link">
        <a href="<?php echo $this->_tpl_vars['addMembersURL']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['group']['title'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add Contacts to %1<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
    </div>
    <?php endif; ?>
    
        <?php if ($this->_tpl_vars['ssID']): ?>
        <?php if ($this->_tpl_vars['ssMappingID']): ?>
            <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/contact/search/builder",'q' => "reset=1&force=1&ssID=".($this->_tpl_vars['ssID'])), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('editSmartGroupURL', ob_get_contents());ob_end_clean(); ?>
        <?php else: ?>
            <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/contact/search/advanced",'q' => "reset=1&force=1&ssID=".($this->_tpl_vars['ssID'])), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('editSmartGroupURL', ob_get_contents());ob_end_clean(); ?>
        <?php endif; ?> 
        <div class="action-link">
            <a href="<?php echo $this->_tpl_vars['editSmartGroupURL']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['group']['title'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Smart Group Search Criteria for %1<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
        </div>
    <?php endif; ?>
<?php endif; ?>