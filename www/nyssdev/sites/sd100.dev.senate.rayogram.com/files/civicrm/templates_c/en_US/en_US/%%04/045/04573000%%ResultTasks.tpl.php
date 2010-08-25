<?php /* Smarty version 2.6.26, created on 2010-05-25 14:31:16
         compiled from CRM/Contact/Form/Search/ResultTasks.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Contact/Form/Search/ResultTasks.tpl', 29, false),array('function', 'help', 'CRM/Contact/Form/Search/ResultTasks.tpl', 48, false),array('block', 'ts', 'CRM/Contact/Form/Search/ResultTasks.tpl', 43, false),)), $this); ?>
<?php ob_start(); ?>
<?php if ($this->_tpl_vars['context'] == 'smog'): ?>
     <?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/group/search/advanced','q' => "gid=".($this->_tpl_vars['group']['id'])."&reset=1&force=1"), $this);?>

<?php elseif ($this->_tpl_vars['context'] == 'amtg'): ?>
     <?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/advanced','q' => "context=amtg&amtgID=".($this->_tpl_vars['group']['id'])."&reset=1&force=1"), $this);?>

<?php else: ?>
    <?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/advanced','q' => "reset=1"), $this);?>

<?php endif; ?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('advSearchURL', ob_get_contents());ob_end_clean(); ?>
<?php ob_start(); ?>
    <?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/builder','q' => "reset=1"), $this);?>

<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('searchBuilderURL', ob_get_contents());ob_end_clean(); ?>

 <div id="search-status">
  <div class="float-right right">
    <?php if ($this->_tpl_vars['action'] == 256): ?>
        <a href="<?php echo $this->_tpl_vars['advSearchURL']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Advanced Search<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><br />
        <?php if ($this->_tpl_vars['context'] == 'search'): ?>             <a href="<?php echo $this->_tpl_vars['searchBuilderURL']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Search Builder<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><br />
        <?php endif; ?>
        <?php if ($this->_tpl_vars['context'] == 'smog'): ?>
            <?php echo smarty_function_help(array('id' => "id-smog-criteria"), $this);?>

        <?php elseif ($this->_tpl_vars['context'] == 'amtg'): ?>
            <?php echo smarty_function_help(array('id' => "id-amtg-criteria"), $this);?>

        <?php else: ?>
            <?php echo smarty_function_help(array('id' => "id-basic-criteria"), $this);?>

        <?php endif; ?>
    <?php elseif ($this->_tpl_vars['action'] == 512): ?>
        <a href="<?php echo $this->_tpl_vars['searchBuilderURL']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Search Builder<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><br />
    <?php elseif ($this->_tpl_vars['action'] == 8192): ?>
        <a href="<?php echo $this->_tpl_vars['advSearchURL']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Advanced Search<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><br />
    <?php endif; ?>
  </div>

  <table class="form-layout-compressed">
  <tr>
    <td class="font-size12pt" style="width: 30%;">
        <?php if ($this->_tpl_vars['savedSearch']['name']): ?><?php echo $this->_tpl_vars['savedSearch']['name']; ?>
 (<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>smart group<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>) - <?php endif; ?>
        <?php $this->_tag_stack[] = array('ts', array('count' => $this->_tpl_vars['pager']->_totalItems,'plural' => '%count Results')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>%count Result<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    </td>
    
        <td class="nowrap">
    <?php if ($this->_tpl_vars['qill']): ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/displaySearchCriteria.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td class="font-size11pt"> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select Records<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>:</td>
    <td class="nowrap">
        <?php echo $this->_tpl_vars['form']['radio_ts']['ts_all']['html']; ?>
 <?php $this->_tag_stack[] = array('ts', array('count' => $this->_tpl_vars['pager']->_totalItems,'plural' => 'All %count records')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The found record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> &nbsp; <?php if ($this->_tpl_vars['pager']->_totalItems > 1): ?> <?php echo $this->_tpl_vars['form']['radio_ts']['ts_sel']['html']; ?>
 <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Selected records only<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?>
    </td>
  </tr>
  <tr>
    <td colspan="2">
          <?php if ($this->_tpl_vars['context'] != 'amtg'): ?>
        <?php if ($this->_tpl_vars['action'] == 512): ?>
<!--<div id="crm-contact-actions-wrapper">
	<div id="crm-contact-actions-link"><span><div class="icon dropdown-icon"></div>Actions</span></div>
		<div class="ac_results" id="crm-contact-actions-list">
			<div class="crm-contact-actions-list-inner">
			</div>
		</div>
	</div>-->
<ul>
   
          <?php echo $this->_tpl_vars['form']['_qf_Advanced_next_print']['html']; ?>
&nbsp; &nbsp;
        <?php elseif ($this->_tpl_vars['action'] == 8192): ?>
          <?php echo $this->_tpl_vars['form']['_qf_Builder_next_print']['html']; ?>
&nbsp; &nbsp;
        <?php elseif ($this->_tpl_vars['action'] == 16384): ?>
                  <?php else: ?>
<!--<div id="crm-contact-actions-wrapper">
	<div id="crm-contact-actions-link"><span><div class="icon dropdown-icon"></div>Actions</span></div>
		<div class="ac_results" id="crm-contact-actions-list">
			<div class="crm-contact-actions-list-inner">
			</div>
		</div>
	</div>-->
<ul>
   
<li class="crm-contact-print crm-button">
                    <div class="icon print-icon"/></div>
<input id="Print" class="form-submit" type="submit" value="<?php echo $this->_tpl_vars['form']['_qf_Basic_next_print']['value']; ?>
" name="_qf_Basic_next_print" onclick="return checkPerformAction('mark_x', 'Basic', 1);"/>
                    </li>
<li class="crm-contact-xls crm-button">
                    <div class="icon xls-icon"/></div>
<input id="Excel" class="form-submit" type="submit" value="xls" name="_qf_Basic_next_print" onclick="return checkPerformAction('mark_x', 'Basic', 1);"/>
                    </li>
</ul>

<!--input id="Doc" class="form-submit" type="submit" value="doc" name="_qf_Basic_next_print" onclick="return checkPerformAction('mark_x', 'Basic', 1);"/-->
        <?php endif; ?>
        <?php echo $this->_tpl_vars['form']['task']['html']; ?>

     <?php endif; ?>
     <?php if ($this->_tpl_vars['action'] == 512): ?>
       <?php echo $this->_tpl_vars['form']['_qf_Advanced_next_action']['html']; ?>

     <?php elseif ($this->_tpl_vars['action'] == 8192): ?>
       <?php echo $this->_tpl_vars['form']['_qf_Builder_next_action']['html']; ?>
&nbsp;&nbsp;
     <?php elseif ($this->_tpl_vars['action'] == 16384): ?>
       <?php echo $this->_tpl_vars['form']['_qf_Custom_next_action']['html']; ?>
&nbsp;&nbsp;
     <?php else: ?>
       <?php echo $this->_tpl_vars['form']['_qf_Basic_next_action']['html']; ?>

     <?php endif; ?>
     </td>
  </tr>
  </table>
 </div>

<?php echo '
<script type="text/javascript">
toggleTaskAction( );
</script>
'; ?>


<?php echo '
<!--<script>
cj( function($) {
//  var tasks=[];
  $(\'#task option\').each(function(){
    if (this.value)
      $(\'.crm-contact-actions-list-inner\').append("<li task=\'"+this.value+"\' class=\'double\'>"+this.text+"</li>");
  });
//  $(\'.crm-contact-actions-list-inner\').append("<div></div>");
  $(\'#task\').parents(\'form\').prepend("<input type=\'hidden\' name=\'task\' id=\'jstask\'/><input type=\'hidden\' name=\'_qf_Basic_next_action\' value=\'Go\'");
//  $(\'#Go\').remove();
//  $(\'#task\').remove();

  $(\'#CIVICRM_QFID_ts_all_4\').attr("checked","true");

  $(\'#crm-contact-actions-list li\').hover(
  	function(){ cj(this).addClass(\'ac_over\');},
  	function(){ cj(this).removeClass(\'ac_over\');}
	).click(function (){
    $(\'#jstask\').attr("value",$(this).attr(\'task\')).parents(\'form\').submit();
	  $(\'#crm-contact-actions-list\').toggle();
    return false;
  });

});
cj(\'body\').click(function() {
	 	$(\'#crm-contact-actions-list\').hide();
	 	});
cj(\'#crm-contact-actions-link\').click(function(event) {
	cj(\'#crm-contact-actions-list\').toggle();
	event.stopPropagation();
	});

</script>-->
'; ?>

