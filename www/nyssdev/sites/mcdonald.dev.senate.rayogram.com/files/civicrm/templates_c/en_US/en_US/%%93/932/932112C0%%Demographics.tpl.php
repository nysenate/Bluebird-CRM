<?php /* Smarty version 2.6.26, created on 2010-07-07 15:33:06
         compiled from CRM/Contact/Page/View/Demographics.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Page/View/Demographics.tpl', 30, false),array('modifier', 'crmDate', 'CRM/Contact/Page/View/Demographics.tpl', 35, false),)), $this); ?>
<div class="contactCardRight">
    <?php if ($this->_tpl_vars['contact_type'] == 'Individual' && $this->_tpl_vars['showDemographics']): ?>
    <table>
        <tr>
            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Gender<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo $this->_tpl_vars['gender_display']; ?>
</td>
        </tr>
        <tr>
            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Date of birth<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td>
            <?php if ($this->_tpl_vars['birthDateViewFormat']): ?>	 
                <?php echo ((is_array($_tmp=$this->_tpl_vars['birth_date_display'])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['birthDateViewFormat']) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['birthDateViewFormat'])); ?>

            <?php else: ?>
                <?php echo ((is_array($_tmp=$this->_tpl_vars['birth_date_display'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
</td>
            <?php endif; ?> 
        </tr>
        <tr>
        <?php if ($this->_tpl_vars['is_deceased'] == 1): ?>
           <?php if ($this->_tpl_vars['deceased_date']): ?><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Date Deceased<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
             <td>
             <?php if ($this->_tpl_vars['birthDateViewFormat']): ?>          
		<?php echo ((is_array($_tmp=$this->_tpl_vars['deceased_date_display'])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['birthDateViewFormat']) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['birthDateViewFormat'])); ?>

             <?php else: ?>
                <?php echo ((is_array($_tmp=$this->_tpl_vars['deceased_date_display'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>

             <?php endif; ?>
             </td>
           <?php else: ?><td class="label" colspan=2><span class="font-red upper"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact is Deceased<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></td>
        <?php endif; ?>
         <?php else: ?>
            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Age<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
            <td><?php if ($this->_tpl_vars['age']['y']): ?><?php $this->_tag_stack[] = array('ts', array('count' => $this->_tpl_vars['age']['y'],'plural' => '%count years')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>%count year<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php elseif ($this->_tpl_vars['age']['m']): ?><?php $this->_tag_stack[] = array('ts', array('count' => $this->_tpl_vars['age']['m'],'plural' => '%count months')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>%count month<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?> </td>
         <?php endif; ?>
    </table>
  <?php endif; ?>
</div><!-- #contactCardRight -->