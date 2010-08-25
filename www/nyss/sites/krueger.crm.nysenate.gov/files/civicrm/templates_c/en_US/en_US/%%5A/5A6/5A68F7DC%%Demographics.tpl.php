<?php /* Smarty version 2.6.26, created on 2010-08-17 10:26:19
         compiled from CRM/Contact/Form/Edit/Demographics.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Edit/Demographics.tpl', 39, false),array('function', 'crmURL', 'CRM/Contact/Form/Edit/Demographics.tpl', 45, false),)), $this); ?>
<div class="crm-accordion-wrapper crm-demographics-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	<?php echo $this->_tpl_vars['title']; ?>
 
  </div><!-- /.crm-accordion-header -->
  <div id="demographics" class="crm-accordion-body">
  <div class="form-item">
        <table id="other-gender"></table>
        <div id="other-gender-hidden"></div>
        <span class="labels"><?php echo $this->_tpl_vars['form']['gender_id']['label']; ?>
</span>
        
	<span class="fields">
        <?php echo $this->_tpl_vars['form']['gender_id']['html']; ?>

        <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('gender_id', '<?php echo $this->_tpl_vars['form']['formName']; ?>
'); return false;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>clear<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>)</span>
        </span>
         
        
      <script>
            <?php echo '
	           otherGenderurl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/add','q' => "snippet=1&qfKey=".($this->_tpl_vars['qfKey'])."&searchPane=",'h' => 0), $this);?>
<?php echo '" + \'customData1 .custom_45_-1-row\';
	           cj(\'#other-gender\').load(otherGenderurl);
	           /* apparently not needed... this was supposed to load the hidden input field for other gender...
	           otherGenderHiddenurl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/add','q' => "snippet=1&qfKey=".($this->_tpl_vars['qfKey'])."&searchPane=",'h' => 0), $this);?>
<?php echo '" + \'customData1 input[type=hidden]\';
             cj(\'#other-gender-hidden\').load(otherGenderHiddenurl); */
            '; ?>

            </script>
  </div>
  <div class="form-item">
        <span class="labels"><?php echo $this->_tpl_vars['form']['birth_date']['label']; ?>
</span>
        <span class="fields"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'birth_date')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></span>
  </div>
  <div class="form-item">
       <?php echo $this->_tpl_vars['form']['is_deceased']['html']; ?>

       <?php echo $this->_tpl_vars['form']['is_deceased']['label']; ?>

  </div>
  <div id="showDeceasedDate" class="form-item">
       <span class="labels"><?php echo $this->_tpl_vars['form']['deceased_date']['label']; ?>
</span>
       <span class="fields"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'deceased_date')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></span>
  </div> 
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

<?php echo '
<script type="text/javascript">
    showDeceasedDate( );    
    function showDeceasedDate( )
    {
        if (document.getElementsByName("is_deceased")[0].checked) {
      	    show(\'showDeceasedDate\');
        } else {
	    hide(\'showDeceasedDate\');
        }
    }     
</script>
'; ?>