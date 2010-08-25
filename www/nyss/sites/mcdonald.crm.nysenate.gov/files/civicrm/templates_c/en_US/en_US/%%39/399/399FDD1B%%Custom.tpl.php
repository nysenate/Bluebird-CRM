<?php /* Smarty version 2.6.26, created on 2010-08-25 11:52:44
         compiled from CRM/Contact/Form/Search/Custom.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Search/Custom.tpl', 33, false),array('function', 'counter', 'CRM/Contact/Form/Search/Custom.tpl', 103, false),array('function', 'cycle', 'CRM/Contact/Form/Search/Custom.tpl', 105, false),array('function', 'crmURL', 'CRM/Contact/Form/Search/Custom.tpl', 111, false),)), $this); ?>
<div class="crm-block crm-form-block crm-contact-custom-search-form-block">
<div class="crm-accordion-wrapper crm-custom_search_form-accordion <?php if ($this->_tpl_vars['rows']): ?>crm-accordion-closed<?php else: ?>crm-accordion-open<?php endif; ?>">
    <div class="crm-accordion-header crm-master-accordion-header">
      <div class="icon crm-accordion-pointer"></div>
      <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Search Criteria<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
        <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
        <table class="form-layout-compressed">
                        <?php $_from = $this->_tpl_vars['elements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['element']):
?>
                <?php if ($this->_tpl_vars['element'] != 'tag'): ?>
                <tr class="crm-contact-custom-search-form-row-<?php echo $this->_tpl_vars['element']; ?>
">
                    <td class="label"><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element']]['label']; ?>
</td>
                    <?php if ($this->_tpl_vars['element'] == 'start_date'): ?>
                        <td><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'start_date')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                    <?php elseif ($this->_tpl_vars['element'] == 'end_date'): ?>
                        <td><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'end_date')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                    <?php else: ?>
                        <td><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element']]['html']; ?>
</td>
                    <?php endif; ?>
                </tr>
                <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
        </table>
        <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
    </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->

<?php if ($this->_tpl_vars['rowsEmpty'] || $this->_tpl_vars['rows']): ?>
<div class="crm-content-block">
<?php if ($this->_tpl_vars['rowsEmpty']): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Search/Custom/EmptyResults.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['summary']): ?>
    <?php echo $this->_tpl_vars['summary']['summary']; ?>
: <?php echo $this->_tpl_vars['summary']['total']; ?>

<?php endif; ?>

<?php if ($this->_tpl_vars['rows']): ?>
	<div class="crm-results-block">
                   <div class="crm-search-tasks">        
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Search/ResultTasks.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</div>
        	    <div class="crm-search-results">

        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pager.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

                <?php if ($this->_tpl_vars['atoZ']): ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pagerAToZ.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php endif; ?>
        
        <?php echo '<table class="selector" summary="'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Search results listings.'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '"><thead class="sticky"><th scope="col" title="Select All Rows">'; ?><?php echo $this->_tpl_vars['form']['toggleSelect']['html']; ?><?php echo '</th>'; ?><?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['header']):
?><?php echo '<th scope="col">'; ?><?php if ($this->_tpl_vars['header']['sort']): ?><?php echo ''; ?><?php $this->assign('key', $this->_tpl_vars['header']['sort']); ?><?php echo ''; ?><?php echo $this->_tpl_vars['sort']->_response[$this->_tpl_vars['key']]['link']; ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php echo $this->_tpl_vars['header']['name']; ?><?php echo ''; ?><?php endif; ?><?php echo '</th>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '<th>&nbsp;</th></thead>'; ?><?php echo smarty_function_counter(array('start' => 0,'skip' => 1,'print' => false), $this);?><?php echo ''; ?><?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?><?php echo '<tr id=\'rowid'; ?><?php echo $this->_tpl_vars['row']['contact_id']; ?><?php echo '\' class="'; ?><?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?><?php echo '">'; ?><?php $this->assign('cbName', $this->_tpl_vars['row']['checkbox']); ?><?php echo '<td>'; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['cbName']]['html']; ?><?php echo '</td>'; ?><?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['header']):
?><?php echo ''; ?><?php $this->assign('fName', $this->_tpl_vars['header']['sort']); ?><?php echo ''; ?><?php if ($this->_tpl_vars['fName'] == 'sort_name'): ?><?php echo '<td><a href="'; ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['row']['contact_id'])), $this);?><?php echo '">'; ?><?php echo $this->_tpl_vars['row']['sort_name']; ?><?php echo '</a></td>'; ?><?php else: ?><?php echo '<td>'; ?><?php echo $this->_tpl_vars['row'][$this->_tpl_vars['fName']]; ?><?php echo '</td>'; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '<td>'; ?><?php echo $this->_tpl_vars['row']['action']; ?><?php echo '</td></tr>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</table>'; ?>


        <script type="text/javascript">
                var fname = "<?php echo $this->_tpl_vars['form']['formName']; ?>
";	
        on_load_init_checkboxes(fname);
        </script>

        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pager.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

        </p>
        </div>
    </div>
<?php endif; ?>



</div>
<?php endif; ?>
<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>