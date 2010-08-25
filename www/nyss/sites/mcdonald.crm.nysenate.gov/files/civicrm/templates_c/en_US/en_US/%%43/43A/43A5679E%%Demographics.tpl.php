<?php /* Smarty version 2.6.26, created on 2010-08-16 22:50:15
         compiled from CRM/Contact/Form/Search/Criteria/Demographics.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'CRM/Contact/Form/Search/Criteria/Demographics.tpl', 30, false),array('block', 'ts', 'CRM/Contact/Form/Search/Criteria/Demographics.tpl', 47, false),)), $this); ?>
<div id="demographics" class="form-item">
    <table class="form-layout">
       <tr>
        <td>
            <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['birth_date_low']['label'])) ? $this->_run_mod_handler('replace', true, $_tmp, '-', '<br />') : smarty_modifier_replace($_tmp, '-', '<br />')); ?>
&nbsp;&nbsp; 
	        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'birth_date_low')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>&nbsp;&nbsp;&nbsp;
            <?php echo $this->_tpl_vars['form']['birth_date_high']['label']; ?>
&nbsp;&nbsp;
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'birth_date_high')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </td>
       </tr>
      <tr>
        <td>
           <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['deceased_date_low']['label'])) ? $this->_run_mod_handler('replace', true, $_tmp, '-', '<br />') : smarty_modifier_replace($_tmp, '-', '<br />')); ?>
&nbsp;&nbsp;
           <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'deceased_date_low')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>&nbsp;&nbsp;&nbsp;
           <?php echo $this->_tpl_vars['form']['deceased_date_high']['label']; ?>
&nbsp;&nbsp;
           <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'deceased_date_high')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </td>    
      </tr>
      <tr>
         <td>
            <?php echo $this->_tpl_vars['form']['gender']['label']; ?>
<br />
            <?php echo $this->_tpl_vars['form']['gender']['html']; ?>
<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('gender', 'Advanced'); return false;" ><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>clear<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>)</span>
         </td>
      </tr>
    </table>            
</div>
