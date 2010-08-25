<?php /* Smarty version 2.6.26, created on 2010-08-17 16:57:01
         compiled from CRM/Contact/Form/Edit/Individual.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Contact/Form/Edit/Individual.tpl', 29, false),array('block', 'ts', 'CRM/Contact/Form/Edit/Individual.tpl', 55, false),)), $this); ?>
<script type="text/javascript">
var cid=parseFloat("<?php echo $this->_tpl_vars['contactId']; ?>
");//parseInt is octal by default
var contactIndividual = "<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest?fnName=civicrm/contact/search&json=1&contact_type=Individual&return[display_name]&return[sort_name]=1&return[email]=1&rowCount=50'), $this);?>
";
var viewIndividual = "<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view?reset=1&cid='), $this);?>
";
var editIndividual = "<?php echo CRM_Utils_System::crmURL(array('p' => 'contact/add?reset=1&action=update&cid='), $this);?>
";
var checkSimilar =  <?php echo $this->_tpl_vars['checkSimilar']; ?>
;
<?php echo '

  jQuery(function($) {

     if ($(\'#contact_sub_type *\').length ==1) {//if they aren\'t any subtype we don\'t offer the option
        $(\'#contact_sub_type\').parent().hide();
     }

     if (!isNaN(cid) || ! checkSimilar)
       return;//no dupe check if this is a modif or if checkSimilar is disabled (CIVICRM_CONTACT_AJAX_CHECK_SIMILAR in civicrm_setting)

	     $(\'#last_name\').blur(function () {
         $(\'#lastname_msg\').remove();
             if (this.value ==\'\') return;
	     $.getJSON(contactIndividual,{sort_name:$(\'#last_name\').val()},
         function(data){
           if (data.is_error== 0) {
             return;
           }
           var msg="<tr id=\'lastname_msg\'><td colspan=\'5\'><div class=\'messages status\'><div class=\'icon inform-icon\'></div>";
           //$(\'#lastname_msg\').remove();
           if (data.length ==1) {
             msg = msg + "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There is a contact with a similar last name. If the person you were trying to add is listed below, click on their name to view or edit their record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";  
           } else {
             // ideally, should use a merge with data.length
             msg = msg + "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There are contacts with a similar last name. If the person you were trying to add is listed below, click on their name to view or edit their record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
           }
           msg = msg+ \'<table class="matching-contacts-actions">\';
           $.each(data, function(i,contact){
             msg = msg + \'<tr><td><a href="\'+viewIndividual+contact.contact_id+\'">\'+ contact.display_name +\'</a></td><td>\'+contact.email+\'</td><td class="action-items"><a class="action-item action-item-first" href="\'+viewIndividual+contact.contact_id+\'">'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>View<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a><a class="action-item" href="\'+editIndividual+contact.contact_id+\'">'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a></td></tr>\';
           });
           msg = msg+ \'</table>\';
           $(\'#last_name\').parent().parent().after(msg+\'</div><td></tr>\');
           $(\'#lastname_msg a\').click(function(){global_formNavigate =true; return true;});// No confirmation dialog on click
         });
	    });
  });
</script>
'; ?>


<table class="form-layout-compressed individual-contact-details">
    <tr>
        <?php if ($this->_tpl_vars['form']['prefix_id']): ?>
	    <td>
                <?php echo $this->_tpl_vars['form']['prefix_id']['label']; ?>
<br/>
                <?php echo $this->_tpl_vars['form']['prefix_id']['html']; ?>

            </td></tr><tr>    
        <?php endif; ?>
        <td>
            <?php echo $this->_tpl_vars['form']['first_name']['label']; ?>
<br /> 
            <?php if ($this->_tpl_vars['action'] == 2): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'CRM/Core/I18n/Dialog.tpl', 'smarty_include_vars' => array('table' => 'civicrm_contact','field' => 'first_name','id' => $this->_tpl_vars['contactId'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
            <?php echo $this->_tpl_vars['form']['first_name']['html']; ?>

        </td>
        </tr><tr>
        <td>
            <?php echo $this->_tpl_vars['form']['middle_name']['label']; ?>
<br />
            <?php if ($this->_tpl_vars['action'] == 2): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'CRM/Core/I18n/Dialog.tpl', 'smarty_include_vars' => array('table' => 'civicrm_contact','field' => 'middle_name','id' => $this->_tpl_vars['contactId'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
            <?php echo $this->_tpl_vars['form']['middle_name']['html']; ?>

        </td>
        </tr><tr>
        <td>
            <?php echo $this->_tpl_vars['form']['last_name']['label']; ?>
<br />
            <?php if ($this->_tpl_vars['action'] == 2): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'CRM/Core/I18n/Dialog.tpl', 'smarty_include_vars' => array('table' => 'civicrm_contact','field' => 'last_name','id' => $this->_tpl_vars['contactId'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
            <?php echo $this->_tpl_vars['form']['last_name']['html']; ?>

        </td>
	<?php if ($this->_tpl_vars['form']['suffix_id']): ?>
	       </tr><tr>
            <td>
                <?php echo $this->_tpl_vars['form']['suffix_id']['label']; ?>
<br/>
                <?php echo $this->_tpl_vars['form']['suffix_id']['html']; ?>

            </td>
	<?php endif; ?>
    </tr>
    
</table>