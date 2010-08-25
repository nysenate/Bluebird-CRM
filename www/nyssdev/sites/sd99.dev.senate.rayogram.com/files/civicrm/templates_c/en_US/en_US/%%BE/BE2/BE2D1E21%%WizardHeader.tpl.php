<?php /* Smarty version 2.6.26, created on 2010-07-06 10:38:33
         compiled from CRM/common/WizardHeader.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'crmFirstWord', 'CRM/common/WizardHeader.tpl', 33, false),array('modifier', 'cat', 'CRM/common/WizardHeader.tpl', 49, false),array('block', 'ts', 'CRM/common/WizardHeader.tpl', 85, false),)), $this); ?>
<?php if (count ( $this->_tpl_vars['wizard']['steps'] ) > 1): ?>
<div id="wizard-steps">
   <ul class="wizard-bar<?php if ($this->_tpl_vars['wizard']['style']['barClass']): ?>-<?php echo $this->_tpl_vars['wizard']['style']['barClass']; ?>
<?php endif; ?>">
    <?php unset($this->_sections['step']);
$this->_sections['step']['name'] = 'step';
$this->_sections['step']['loop'] = is_array($_loop=$this->_tpl_vars['wizard']['steps']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['step']['show'] = true;
$this->_sections['step']['max'] = $this->_sections['step']['loop'];
$this->_sections['step']['step'] = 1;
$this->_sections['step']['start'] = $this->_sections['step']['step'] > 0 ? 0 : $this->_sections['step']['loop']-1;
if ($this->_sections['step']['show']) {
    $this->_sections['step']['total'] = $this->_sections['step']['loop'];
    if ($this->_sections['step']['total'] == 0)
        $this->_sections['step']['show'] = false;
} else
    $this->_sections['step']['total'] = 0;
if ($this->_sections['step']['show']):

            for ($this->_sections['step']['index'] = $this->_sections['step']['start'], $this->_sections['step']['iteration'] = 1;
                 $this->_sections['step']['iteration'] <= $this->_sections['step']['total'];
                 $this->_sections['step']['index'] += $this->_sections['step']['step'], $this->_sections['step']['iteration']++):
$this->_sections['step']['rownum'] = $this->_sections['step']['iteration'];
$this->_sections['step']['index_prev'] = $this->_sections['step']['index'] - $this->_sections['step']['step'];
$this->_sections['step']['index_next'] = $this->_sections['step']['index'] + $this->_sections['step']['step'];
$this->_sections['step']['first']      = ($this->_sections['step']['iteration'] == 1);
$this->_sections['step']['last']       = ($this->_sections['step']['iteration'] == $this->_sections['step']['total']);
?>
        <?php if (count ( $this->_tpl_vars['wizard']['steps'] ) > 5): ?>
                        <?php $this->assign('title', ((is_array($_tmp=$this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['title'])) ? $this->_run_mod_handler('crmFirstWord', true, $_tmp) : smarty_modifier_crmFirstWord($_tmp))); ?>
        <?php else: ?>
            <?php $this->assign('title', $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['title']); ?>
        <?php endif; ?>
                <?php if (! $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['collapsed'] && $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['name'] != 'Submit' && $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['name'] != 'PartnerSubmit'): ?>
            <?php $this->assign('i', $this->_sections['step']['iteration']); ?>
            <?php if ($this->_tpl_vars['wizard']['currentStepNumber'] > $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['stepNumber']): ?>
                <?php if ($this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['step']): ?>
                    <?php $this->assign('stepClass', "past-step"); ?>
                <?php else: ?>                     <?php $this->assign('stepClass', "past-sub-step"); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['wizard']['style']['hideStepNumbers']): ?>
                    <?php $this->assign('stepPrefix', $this->_tpl_vars['wizard']['style']['subStepPrefixPast']); ?>
                <?php else: ?>
                    <?php $this->assign('stepPrefix', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['wizard']['style']['stepPrefixPast'])) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['stepNumber']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['stepNumber'])))) ? $this->_run_mod_handler('cat', true, $_tmp, ". ") : smarty_modifier_cat($_tmp, ". "))); ?>
                <?php endif; ?>
            <?php elseif ($this->_tpl_vars['wizard']['currentStepNumber'] == $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['stepNumber']): ?>
                <?php if ($this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['step']): ?>
                    <?php $this->assign('stepClass', "current-step"); ?>
                <?php else: ?>
                    <?php $this->assign('stepClass', "current-sub-step"); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['wizard']['style']['hideStepNumbers']): ?>
                    <?php $this->assign('stepPrefix', $this->_tpl_vars['wizard']['style']['subStepPrefixCurrent']); ?>
                <?php else: ?>
                    <?php $this->assign('stepPrefix', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['wizard']['style']['stepPrefixCurrent'])) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['stepNumber']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['stepNumber'])))) ? $this->_run_mod_handler('cat', true, $_tmp, ". ") : smarty_modifier_cat($_tmp, ". "))); ?>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['step']): ?>
                    <?php $this->assign('stepClass', "future-step"); ?>
                <?php else: ?>
                    <?php $this->assign('stepClass', "future-sub-step"); ?>
                <?php endif; ?>
                <?php if ($this->_tpl_vars['wizard']['style']['hideStepNumbers']): ?>
                    <?php $this->assign('stepPrefix', $this->_tpl_vars['wizard']['style']['subStepPrefixFuture']); ?>
                <?php else: ?>
                    <?php $this->assign('stepPrefix', ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['wizard']['style']['stepPrefixFuture'])) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['stepNumber']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['stepNumber'])))) ? $this->_run_mod_handler('cat', true, $_tmp, ". ") : smarty_modifier_cat($_tmp, ". "))); ?>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (! $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['valid']): ?>
                <?php $this->assign('stepClass', ($this->_tpl_vars['stepClass'])." not-valid"); ?>
            <?php endif; ?>
                         
            <li class="<?php echo $this->_tpl_vars['stepClass']; ?>
"><?php echo $this->_tpl_vars['stepPrefix']; ?>
<?php if ($this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['link']): ?><a href="<?php echo $this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['link']; ?>
"><?php endif; ?><?php echo $this->_tpl_vars['title']; ?>
<?php if ($this->_tpl_vars['wizard']['steps'][$this->_sections['step']['index']]['link']): ?></a><?php endif; ?></li>
        <?php endif; ?> 
    <?php endfor; endif; ?>
   </ul>
</div>
<?php if ($this->_tpl_vars['wizard']['style']['showTitle']): ?>
    <h2><?php echo $this->_tpl_vars['wizard']['currentStepTitle']; ?>
 <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['wizard']['currentStepNumber'],'2' => $this->_tpl_vars['wizard']['stepCount'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>(step %1 of %2)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h2>
<?php endif; ?>
<?php endif; ?>
