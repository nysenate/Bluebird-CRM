<?php /* Smarty version 2.6.26, created on 2010-08-25 15:04:22
         compiled from string:%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%0A%7Bts%7DActivity+Summary%7B/ts%7D+-+%7B%24activityTypeName%7D%0A%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%0A%7Bif+%24isCaseActivity%7D%0A%7Bts%7DYour+Case+Role%28s%29%7B/ts%7D+:+%7B%24contact.role%7D%0A%7B/if%7D%0A%0A%7Bforeach+from%3D%24activity.fields+item%3Dfield%7D%0A%7Bif+%24field.type+eq+%27Date%27%7D%0A%7B%24field.label%7D%7Bif+%24field.category%7D%28%7B%24field.category%7D%29%7B/if%7D+:+%7B%24field.value%7CcrmDate:%24config-%3EdateformatDatetime%7D%0A%7Belse%7D%0A%7B%24field.label%7D%7Bif+%24field.category%7D%28%7B%24field.category%7D%29%7B/if%7D+:+%7B%24field.value%7D%0A%7B/if%7D%0A%7B/foreach%7D%0A%0A%7Bforeach+from%3D%24activity.customGroups+key%3DcustomGroupName+item%3DcustomGroup%7D%0A%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%0A%7B%24customGroupName%7D%0A%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%3D%0A%7Bforeach+from%3D%24customGroup+item%3Dfield%7D%0A%7Bif+%24field.type+eq+%27Date%27%7D%0A%7B%24field.label%7D+:+%7B%24field.value%7CcrmDate:%24config-%3EdateformatDatetime%7D%0A%7Belse%7D%0A%7B%24field.label%7D+:+%7B%24field.value%7D%0A%7B/if%7D%0A%7B/foreach%7D%0A%0A%7B/foreach%7D%0A */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'string:===========================================================
{ts}Activity Summary{/ts} - {$activityTypeName}
===========================================================
{if $isCaseActivity}
{ts}Your Case Role(s){/ts} : {$contact.role}
{/if}

{foreach from=$activity.fields item=field}
{if $field.type eq \'Date\'}
{$field.label}{if $field.category}({$field.category}){/if} : {$field.value|crmDate:$config->dateformatDatetime}
{else}
{$field.label}{if $field.category}({$field.category}){/if} : {$field.value}
{/if}
{/foreach}

{foreach from=$activity.customGroups key=customGroupName item=customGroup}
==========================================================
{$customGroupName}
==========================================================
{foreach from=$customGroup item=field}
{if $field.type eq \'Date\'}
{$field.label} : {$field.value|crmDate:$config->dateformatDatetime}
{else}
{$field.label} : {$field.value}
{/if}
{/foreach}

{/foreach}
', 2, false),array('modifier', 'crmDate', 'string:===========================================================
{ts}Activity Summary{/ts} - {$activityTypeName}
===========================================================
{if $isCaseActivity}
{ts}Your Case Role(s){/ts} : {$contact.role}
{/if}

{foreach from=$activity.fields item=field}
{if $field.type eq \'Date\'}
{$field.label}{if $field.category}({$field.category}){/if} : {$field.value|crmDate:$config->dateformatDatetime}
{else}
{$field.label}{if $field.category}({$field.category}){/if} : {$field.value}
{/if}
{/foreach}

{foreach from=$activity.customGroups key=customGroupName item=customGroup}
==========================================================
{$customGroupName}
==========================================================
{foreach from=$customGroup item=field}
{if $field.type eq \'Date\'}
{$field.label} : {$field.value|crmDate:$config->dateformatDatetime}
{else}
{$field.label} : {$field.value}
{/if}
{/foreach}

{/foreach}
', 10, false),)), $this); ?>
===========================================================
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Activity Summary<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> - <?php echo $this->_tpl_vars['activityTypeName']; ?>

===========================================================
<?php if ($this->_tpl_vars['isCaseActivity']): ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Your Case Role(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> : <?php echo $this->_tpl_vars['contact']['role']; ?>

<?php endif; ?>

<?php $_from = $this->_tpl_vars['activity']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field']):
?>
<?php if ($this->_tpl_vars['field']['type'] == 'Date'): ?>
<?php echo $this->_tpl_vars['field']['label']; ?>
<?php if ($this->_tpl_vars['field']['category']): ?>(<?php echo $this->_tpl_vars['field']['category']; ?>
)<?php endif; ?> : <?php echo ((is_array($_tmp=$this->_tpl_vars['field']['value'])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['config']->dateformatDatetime) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['config']->dateformatDatetime)); ?>

<?php else: ?>
<?php echo $this->_tpl_vars['field']['label']; ?>
<?php if ($this->_tpl_vars['field']['category']): ?>(<?php echo $this->_tpl_vars['field']['category']; ?>
)<?php endif; ?> : <?php echo $this->_tpl_vars['field']['value']; ?>

<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

<?php $_from = $this->_tpl_vars['activity']['customGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['customGroupName'] => $this->_tpl_vars['customGroup']):
?>
==========================================================
<?php echo $this->_tpl_vars['customGroupName']; ?>

==========================================================
<?php $_from = $this->_tpl_vars['customGroup']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field']):
?>
<?php if ($this->_tpl_vars['field']['type'] == 'Date'): ?>
<?php echo $this->_tpl_vars['field']['label']; ?>
 : <?php echo ((is_array($_tmp=$this->_tpl_vars['field']['value'])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['config']->dateformatDatetime) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['config']->dateformatDatetime)); ?>

<?php else: ?>
<?php echo $this->_tpl_vars['field']['label']; ?>
 : <?php echo $this->_tpl_vars['field']['value']; ?>

<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

<?php endforeach; endif; unset($_from); ?>