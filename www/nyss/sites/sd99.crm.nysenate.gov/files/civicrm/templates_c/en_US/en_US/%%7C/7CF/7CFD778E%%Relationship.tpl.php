<?php /* Smarty version 2.6.26, created on 2010-08-23 16:07:18
         compiled from CRM/Contact/Form/Search/Criteria/Relationship.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'crmReplace', 'CRM/Contact/Form/Search/Criteria/Relationship.tpl', 35, false),array('block', 'ts', 'CRM/Contact/Form/Search/Criteria/Relationship.tpl', 37, false),)), $this); ?>
<div id="relationship" class="form-item">
    <table class="form-layout">
         <tr>
            <td>
               <?php echo $this->_tpl_vars['form']['relation_type_id']['label']; ?>
<br />
               <?php echo $this->_tpl_vars['form']['relation_type_id']['html']; ?>

            </td>
            <td>
               <?php echo $this->_tpl_vars['form']['relation_target_name']['label']; ?>
<br />
               <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['relation_target_name']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>

                <div class="description font-italic">
                    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Complete OR partial contact name.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                </div>
            </td>    
            <td>
               <?php echo $this->_tpl_vars['form']['relation_status']['label']; ?>
<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('relation_status', 'Advanced'); return false;" ><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>clear<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>)</span><br />
               <?php echo $this->_tpl_vars['form']['relation_status']['html']; ?>

            </td>
         </tr>
         <?php if ($this->_tpl_vars['relationshipGroupTree']): ?>
         <tr>
	        <td colspan="3">
	        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/Search.tpl", 'smarty_include_vars' => array('groupTree' => $this->_tpl_vars['relationshipGroupTree'],'showHideLinks' => false)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            </td>
         </tr>
         <?php endif; ?>
    </table>         
</div>