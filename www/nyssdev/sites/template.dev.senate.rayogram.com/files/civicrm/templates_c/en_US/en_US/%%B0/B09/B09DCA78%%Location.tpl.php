<?php /* Smarty version 2.6.26, created on 2010-06-07 12:59:35
         compiled from CRM/Contact/Form/Search/Criteria/Location.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Search/Criteria/Location.tpl', 30, false),array('modifier', 'replace', 'CRM/Contact/Form/Search/Criteria/Location.tpl', 33, false),array('modifier', 'crmReplace', 'CRM/Contact/Form/Search/Criteria/Location.tpl', 34, false),)), $this); ?>
<div id="location" class="form-item">
<?php if ($this->_tpl_vars['form']['postal_code']['html']): ?>
    <div class="postal-code-search">
    <?php echo $this->_tpl_vars['form']['postal_code']['label']; ?>
<br />
    <?php echo $this->_tpl_vars['form']['postal_code']['html']; ?>
&nbsp;<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>OR<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><br />
     <br />
     <label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Postal Code<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
            <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['postal_code_low']['label'])) ? $this->_run_mod_handler('replace', true, $_tmp, '-', '<br />') : smarty_modifier_replace($_tmp, '-', '<br />')); ?>
<br />
            <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['postal_code_low']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'six') : smarty_modifier_crmReplace($_tmp, 'class', 'six')); ?>
<br />
            <?php echo $this->_tpl_vars['form']['postal_code_high']['label']; ?>
<br />
    		<?php echo ((is_array($_tmp=$this->_tpl_vars['form']['postal_code_high']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'six') : smarty_modifier_crmReplace($_tmp, 'class', 'six')); ?>

     </div>				
<?php endif; ?>




    <table class="form-layout">
	<tr>
        <td>
        
        
        
		<?php echo $this->_tpl_vars['form']['location_type']['label']; ?>
<br />
        <?php echo $this->_tpl_vars['form']['location_type']['html']; ?>
 
        <div class="description" >
            <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Location search uses the PRIMARY location for each contact by default.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 
        </div> 
            <?php echo $this->_tpl_vars['form']['street_address']['label']; ?>
<br />
            <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['street_address']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>
<br />
            <?php echo $this->_tpl_vars['form']['city']['label']; ?>
<br />
            <?php echo $this->_tpl_vars['form']['city']['html']; ?>

  	    </td>	   
    </tr>
           
    <tr>
       
        <td><?php echo $this->_tpl_vars['form']['state_province']['label']; ?>
<br />
            <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['state_province']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'bigSelect') : smarty_modifier_crmReplace($_tmp, 'class', 'bigSelect')); ?>

        </td>
    </tr>
    <?php if ($this->_tpl_vars['addressGroupTree']): ?>
        <tr>
	    <td>
	        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/Search.tpl", 'smarty_include_vars' => array('groupTree' => $this->_tpl_vars['addressGroupTree'],'showHideLinks' => false)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            </td>
        </tr>
    <?php endif; ?>
    </table>
</div>
