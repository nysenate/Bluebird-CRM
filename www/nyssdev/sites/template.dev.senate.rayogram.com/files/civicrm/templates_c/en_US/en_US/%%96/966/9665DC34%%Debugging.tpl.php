<?php /* Smarty version 2.6.26, created on 2010-05-27 12:25:58
         compiled from CRM/Admin/Form/Setting/Debugging.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/Setting/Debugging.tpl', 32, false),array('function', 'help', 'CRM/Admin/Form/Setting/Debugging.tpl', 32, false),)), $this); ?>
<div class="form-item crm-block crm-form-block crm-debugging-form-block">
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
         <table>
            <tr class="crm-debugging-form-block-debug">
                <td class="label"><?php echo $this->_tpl_vars['form']['debug']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['debug']['html']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Set this value to <strong>Yes</strong> if you want to use one of CiviCRM's debugging tools. <strong>This feature should NOT be enabled for production sites</strong><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => 'debug'), $this);?>
</span></td>
            </tr>
            <?php if ($this->_tpl_vars['form']['userFrameworkLogging']): ?>
            <tr class="crm-debugging-form-block-userFrameworkLogging">
                <td class="label"><?php echo $this->_tpl_vars['form']['userFrameworkLogging']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['userFrameworkLogging']['html']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Set this value to <strong>Yes</strong> if you want CiviCRM error/debugging messages to also appear in Drupal error logs<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => 'userFrameworkLogging'), $this);?>
</span></td>
            </tr>
            <?php endif; ?>
            <tr class="crm-debugging-form-block-backtrace">
                <td class="label"><?php echo $this->_tpl_vars['form']['backtrace']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['backtrace']['html']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Set this value to <strong>Yes</strong> if you want to display a backtrace listing when a fatal error is encountered. <strong>This feature should NOT be enabled for production sites</strong><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></td>
            </tr>
            <tr class="crm-debugging-form-block-fatalErrorTemplate">
                <td><?php echo $this->_tpl_vars['form']['fatalErrorTemplate']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['fatalErrorTemplate']['html']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Enter the path and filename for a custom Smarty template if you want to define your own screen for displaying fatal errors.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></td>
            </tr>
            <tr class="crm-debugging-form-block-fatalErrorHandler">
                <td><?php echo $this->_tpl_vars['form']['fatalErrorHandler']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['fatalErrorHandler']['html']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Enter the path and class for a custom PHP error-handling function if you want to override built-in CiviCRM error handling for your site.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></td>
            </tr>
        </table>
        <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
<div class="spacer"></div>
</div>