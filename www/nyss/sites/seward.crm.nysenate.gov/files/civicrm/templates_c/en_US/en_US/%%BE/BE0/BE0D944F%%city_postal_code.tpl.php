<?php /* Smarty version 2.6.26, created on 2010-08-16 22:21:41
         compiled from CRM/Contact/Form/Edit/Address/city_postal_code.tpl */ ?>
<tr><td colspan="3" style="padding:0;">
<table style="border:none;">
<tr>
    <?php if ($this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['city']): ?>
       <td>
          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['city']['label']; ?>
<br />
          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['city']['html']; ?>

       </td>
    <?php endif; ?>
    <?php if ($this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['postal_code']): ?>
       <td>
          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['postal_code']['label']; ?>
<br />
          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['postal_code']['html']; ?>

          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['postal_code_suffix']['html']; ?>
<br />
          <span class="description font-italic" style="white-space:nowrap;">Enter optional 'add-on' code after the dash ('plus 4' code for U.S. addresses).</span>
       </td>
    <?php endif; ?>
    <td colspan="2">&nbsp;&nbsp;</td>
</tr>
</table>
</td></tr>