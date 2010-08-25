<?php /* Smarty version 2.6.26, created on 2010-08-16 22:59:10
         compiled from CRM/Contact/Form/Search/Criteria/ChangeLog.tpl */ ?>
<div id="changelog" class="form-item">
    <table class="form-layout">
        <tr>
            <td>
                <?php echo $this->_tpl_vars['form']['changed_by']['label']; ?>
<br />
                <?php echo $this->_tpl_vars['form']['changed_by']['html']; ?>

            </td>
            <td>
                <?php echo $this->_tpl_vars['form']['modified_date_low']['label']; ?>
<br />
	            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'modified_date_low')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>&nbsp;
		        <?php echo $this->_tpl_vars['form']['modified_date_high']['label']; ?>
&nbsp; 
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'modified_date_high')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            </td>
        </tr>
    </table>
 </div>