<?php /* Smarty version 2.6.26, created on 2010-07-28 00:15:23
         compiled from CRM/Contact/Form/Edit/Address/geo_code.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'docURL', 'CRM/Contact/Form/Edit/Address/geo_code.tpl', 27, false),array('block', 'ts', 'CRM/Contact/Form/Edit/Address/geo_code.tpl', 33, false),)), $this); ?>
<?php if ($this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['geo_code_1'] && $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['geo_code_2']): ?>
    <?php ob_start(); ?><?php echo smarty_function_docURL(array('page' => 'Mapping and Geocoding'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('docLink', ob_get_contents());ob_end_clean(); ?>
   <tr>
      <td colspan="2">
          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['geo_code_1']['label']; ?>
,&nbsp;<?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['geo_code_2']['label']; ?>
<br />
          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['geo_code_1']['html']; ?>
,&nbsp;<?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['geo_code_2']['html']; ?>
<br />
          <span class="description font-italic">
            <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Latitude and longitude may be automatically populated by enabling a Mapping Provider.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo $this->_tpl_vars['docLink']; ?>

          </span>
      </td>
   </tr>
<?php endif; ?>