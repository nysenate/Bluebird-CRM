<?php /* Smarty version 2.6.26, created on 2010-05-27 18:03:43
         compiled from CRM/Contact/Page/View/Summary.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Contact/Page/View/Summary.tpl', 43, false),array('function', 'cycle', 'CRM/Contact/Page/View/Summary.tpl', 232, false),array('block', 'ts', 'CRM/Contact/Page/View/Summary.tpl', 43, false),array('modifier', 'nl2br', 'CRM/Contact/Page/View/Summary.tpl', 168, false),array('modifier', 'mb_truncate', 'CRM/Contact/Page/View/Summary.tpl', 214, false),)), $this); ?>
<?php if ($this->_tpl_vars['imageURL']): ?>
    <div>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/ContactImage.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </div>
<?php endif; ?>
<?php if ($this->_tpl_vars['action'] == 2): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Contact.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>

<div class="crm-actions-ribbon">
                    <ul id="actions">
                    	                                                <?php if (( call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'delete contacts' ) ) && ( $this->_tpl_vars['permission'] == 'edit' )): ?>
                        <?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'check' ) , 'access deleted contacts' ) && $this->_tpl_vars['is_deleted']): ?>
                        <li class="crm-delete-action crm-contact-restore">
                        <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/delete','q' => "reset=1&cid=".($this->_tpl_vars['contactId'])."&restore=1"), $this);?>
" class="delete button" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Restore<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                        <span><div class="icon restore-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Restore from Trash<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                        </a>
                        </li>
                        
                        <li class="crm-delete-action crm-contact-permanently-delete">
                        <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/delete','q' => "reset=1&delete=1&cid=".($this->_tpl_vars['contactId'])."&skip_undelete=1"), $this);?>
" class="delete button" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Permanently<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                        <span><div class="icon delete-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Permanently<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                        </a>
                        </li>
                        <?php else: ?>
                        <li class="crm-delete-action crm-contact-delete">
                        <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/delete','q' => "reset=1&delete=1&cid=".($this->_tpl_vars['contactId'])), $this);?>
" class="delete button" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                        <span><div class="icon delete-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                        </a>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                    
                    	                        <?php if ($this->_tpl_vars['permission'] == 'edit' && ! $this->_tpl_vars['isDeleted']): ?>
                        <li class="crm-contact-activity">
                            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/ActionsButton.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        </li>
                        <li>
                        <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/add','q' => "reset=1&action=update&cid=".($this->_tpl_vars['contactId'])), $this);?>
" class="edit button" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                        <span><div class="icon edit-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                        </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ($this->_tpl_vars['groupOrganizationUrl']): ?>
                        <li class="crm-contact-associated-groups">
                        <a href="<?php echo $this->_tpl_vars['groupOrganizationUrl']; ?>
" class="associated-groups button" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Associated Multi-Org Group<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                        <span><div class="icon associated-groups-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Associated Multi-Org Group<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                        </a>   
                        </li>
                        <?php endif; ?>
                    </ul> 
                    <div class="clear"></div>
                        
                        
                </div><!-- .crm-actions-ribbon -->

<div class="crm-block crm-content-block crm-contact-page">

    <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="crm-contact-tabs-list">
            <li id="tab_summary" class="crm-tab-button">
            	<a href="#contact-summary" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Summary<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
            	<span> </span> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Summary<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
            	<em>&nbsp;</em>
            	</a>
            </li>
            <?php $_from = $this->_tpl_vars['allTabs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tabName'] => $this->_tpl_vars['tabValue']):
?>
            <li id="tab_<?php echo $this->_tpl_vars['tabValue']['id']; ?>
" class="crm-tab-button crm-count-<?php echo $this->_tpl_vars['tabValue']['count']; ?>
">
            	<a href="<?php echo $this->_tpl_vars['tabValue']['url']; ?>
" title="<?php echo $this->_tpl_vars['tabValue']['title']; ?>
">
            		<span> </span> <?php echo $this->_tpl_vars['tabValue']['title']; ?>

            		<em><?php echo $this->_tpl_vars['tabValue']['count']; ?>
</em>
            	</a>
            </li>
            <?php endforeach; endif; unset($_from); ?>
        </ul>

        <div title="Summary" id="contact-summary" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
            <?php if ($this->_tpl_vars['hookContentPlacement'] != 3): ?>
                
                <?php if ($this->_tpl_vars['hookContent'] && $this->_tpl_vars['hookContentPlacement'] == 2): ?>
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/View/SummaryHook.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endif; ?>
                
                <?php if ($this->_tpl_vars['contact_type_label'] || $this->_tpl_vars['current_employer_id'] || $this->_tpl_vars['job_title'] || $this->_tpl_vars['legal_name'] || $this->_tpl_vars['sic_code'] || $this->_tpl_vars['nick_name'] || $this->_tpl_vars['contactTag'] || $this->_tpl_vars['source']): ?>
                <div id="contactTopBar">
                    <table>
                        <?php if ($this->_tpl_vars['contact_type_label'] || $this->_tpl_vars['current_employer_id'] || $this->_tpl_vars['job_title'] || $this->_tpl_vars['legal_name'] || $this->_tpl_vars['sic_code'] || $this->_tpl_vars['nick_name']): ?>
                        <tr>
                            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                            <td><?php echo $this->_tpl_vars['contact_type_label']; ?>
</td>
                            <?php if ($this->_tpl_vars['current_employer_id']): ?>
                            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Employer<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                            <td><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['current_employer_id'])), $this);?>
" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>view current employer<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"><?php echo $this->_tpl_vars['current_employer']; ?>
</a></td>
                            <?php endif; ?>
                            <?php if ($this->_tpl_vars['job_title']): ?>
                            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Position<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                            <td><?php echo $this->_tpl_vars['job_title']; ?>
</td>
                            <?php endif; ?>
                            <?php if ($this->_tpl_vars['legal_name']): ?>
                            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Legal Name<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                            <td><?php echo $this->_tpl_vars['legal_name']; ?>
</td>
                            <?php if ($this->_tpl_vars['sic_code']): ?>
                            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>SIC Code<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                            <td><?php echo $this->_tpl_vars['sic_code']; ?>
</td>
                            <?php endif; ?>
                            <?php elseif ($this->_tpl_vars['nick_name']): ?>
                            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Nickname<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                            <td><?php echo $this->_tpl_vars['nick_name']; ?>
</td>
                            <?php endif; ?>
                        </tr>
                        <?php endif; ?>
                        <?php if ($this->_tpl_vars['contactTag'] || $this->_tpl_vars['source']): ?>
                        <tr>
                            <td class="label" id="tagLink"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['contactId'])."&selectedChild=tag"), $this);?>
" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Tags<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Tags<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></td><td id="tags"><?php echo $this->_tpl_vars['contactTag']; ?>
</td>
                            <?php if ($this->_tpl_vars['source']): ?>
                            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Source<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo $this->_tpl_vars['source']; ?>
</td>
                            <?php endif; ?>
                        </tr>
                        <?php endif; ?>
                    </table>

                    <div class="clear"></div>
                </div><!-- #contactTopBar -->
                <?php endif; ?>
                <div class="contact_details">
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                            <table>
                                <?php $_from = $this->_tpl_vars['email']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['blockId'] => $this->_tpl_vars['item']):
?>
                                    <?php if ($this->_tpl_vars['item']['email']): ?>
                                    <tr>
                                        <td class="label"><?php echo $this->_tpl_vars['item']['location_type']; ?>
&nbsp;<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                                        <td><span class=<?php if ($this->_tpl_vars['privacy']['do_not_email']): ?>"do-not-email" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Privacy flag: Do Not Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" <?php elseif ($this->_tpl_vars['item']['on_hold']): ?>"email-hold" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email on hold - generally due to bouncing.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" <?php elseif ($this->_tpl_vars['item']['is_primary'] == 1): ?>"primary"<?php endif; ?>><a href="mailto:<?php echo $this->_tpl_vars['item']['email']; ?>
"><?php echo $this->_tpl_vars['item']['email']; ?>
</a><?php if ($this->_tpl_vars['item']['on_hold']): ?>&nbsp;(<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>On Hold<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)<?php endif; ?><?php if ($this->_tpl_vars['item']['is_bulkmail']): ?>&nbsp;(<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Bulk<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)<?php endif; ?></span></td>
					                    <td class="description"><?php if ($this->_tpl_vars['item']['signature_text'] || $this->_tpl_vars['item']['signature_html']): ?><a href="#" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Signature<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" onClick="showHideSignature( '<?php echo $this->_tpl_vars['blockId']; ?>
' ); return false;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>(signature)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><?php endif; ?></td>
                                    </tr>
                                    <tr id="Email_Block_<?php echo $this->_tpl_vars['blockId']; ?>
_signature" class="hiddenElement">
                                        <td><strong><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Signature HTML<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></strong><br /><?php echo $this->_tpl_vars['item']['signature_html']; ?>
<br /><br />
                                        <strong><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Signature Text<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></strong><br /><?php echo ((is_array($_tmp=$this->_tpl_vars['item']['signature_text'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; endif; unset($_from); ?>
                                <?php if ($this->_tpl_vars['website']): ?>
                                <?php $_from = $this->_tpl_vars['website']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
                                    <?php if ($this->_tpl_vars['item']['url']): ?>
                                    <tr>
                                        <td class="label"><?php echo $this->_tpl_vars['item']['website_type']; ?>
</td>
                                        <td><a href="<?php echo $this->_tpl_vars['item']['url']; ?>
" target="_blank"><?php echo $this->_tpl_vars['item']['url']; ?>
</a></td>
                                        <td></td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; endif; unset($_from); ?>
                                <?php endif; ?>
                                <?php if ($this->_tpl_vars['user_unique_id']): ?>
                                    <tr>
                                        <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Unique Id<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                                        <td><?php echo $this->_tpl_vars['user_unique_id']; ?>
</td>
                                        <td></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div><!-- #contactCardLeft -->

                        <div class="contactCardRight">
                            <?php if ($this->_tpl_vars['phone'] || $this->_tpl_vars['im'] || $this->_tpl_vars['openid']): ?>
                                <table>
                                    <?php $_from = $this->_tpl_vars['phone']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
                                        <?php if ($this->_tpl_vars['item']['phone']): ?>
                                        <tr>
                                            <td class="label"><?php echo $this->_tpl_vars['item']['location_type']; ?>
&nbsp;<?php echo $this->_tpl_vars['item']['phone_type']; ?>
</td>
                                            <td <?php if ($this->_tpl_vars['item']['is_primary'] == 1): ?>class="primary"<?php endif; ?>><span <?php if ($this->_tpl_vars['privacy']['do_not_phone']): ?> class="do-not-phone" title=<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>"Privacy flag: Do Not Phone"<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php endif; ?>><?php echo $this->_tpl_vars['item']['phone']; ?>
</span></td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; endif; unset($_from); ?>
                                    <?php $_from = $this->_tpl_vars['im']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
                                        <?php if ($this->_tpl_vars['item']['name'] || $this->_tpl_vars['item']['provider']): ?>
                                        <?php if ($this->_tpl_vars['item']['name']): ?><tr><td class="label"><?php echo $this->_tpl_vars['item']['provider']; ?>
&nbsp;(<?php echo $this->_tpl_vars['item']['location_type']; ?>
)</td><td <?php if ($this->_tpl_vars['item']['is_primary'] == 1): ?>class="primary"<?php endif; ?>><?php echo $this->_tpl_vars['item']['name']; ?>
</td></tr><?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; endif; unset($_from); ?>
                                    <?php $_from = $this->_tpl_vars['openid']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
                                        <?php if ($this->_tpl_vars['item']['openid']): ?>
                                            <tr>
                                                <td class="label"><?php echo $this->_tpl_vars['item']['location_type']; ?>
&nbsp;<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>OpenID<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                                                <td <?php if ($this->_tpl_vars['item']['is_primary'] == 1): ?>class="primary"<?php endif; ?>><a href="<?php echo $this->_tpl_vars['item']['openid']; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['item']['openid'])) ? $this->_run_mod_handler('mb_truncate', true, $_tmp, 40) : smarty_modifier_mb_truncate($_tmp, 40)); ?>
</a>
                                                    <?php if ($this->_tpl_vars['config']->userFramework == 'Standalone' && $this->_tpl_vars['item']['allowed_to_login'] == 1): ?>
                                                        <br/> <span style="font-size:9px;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>(Allowed to login)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; endif; unset($_from); ?>
                                </table>
    						<?php endif; ?>
                        </div><!-- #contactCardRight -->

                        <div class="clear"></div>
                    </div><!-- #contact_panel -->

					<?php if ($this->_tpl_vars['address']): ?>
                    <div class="contact_panel">
                        <?php $_from = $this->_tpl_vars['address']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['locationIndex'] => $this->_tpl_vars['add']):
?>
                        <div class="<?php echo smarty_function_cycle(array('name' => 'location','values' => "contactCardLeft,contactCardRight"), $this);?>
 crm-address_<?php echo $this->_tpl_vars['locationIndex']; ?>
 crm-address-block crm-address_type_<?php echo $this->_tpl_vars['add']['location_type']; ?>
">
                            <table>
                                <tr>
                                    <td class="label"><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['add']['location_type'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>%1&nbsp;Address<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                                        <?php if ($this->_tpl_vars['config']->mapAPIKey && $this->_tpl_vars['add']['geo_code_1'] && $this->_tpl_vars['add']['geo_code_2']): ?>
                                            <br /><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/map','q' => "reset=1&cid=".($this->_tpl_vars['contactId'])."&lid=".($this->_tpl_vars['add']['location_type_id'])), $this);?>
" title="<?php $this->_tag_stack[] = array('ts', array('1' => '&#123;$add.location_type&#125;')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Map %1 Address<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"><span class="geotag"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Map<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
                                        <?php endif; ?></td>
                                    <td>
                                        <?php if ($this->_tpl_vars['householdName'] && $this->_tpl_vars['locationIndex'] == 1): ?>
                                        <strong><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Household Address:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></strong><br />
                                        <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['mail_to_household_id'])), $this);?>
"><?php echo $this->_tpl_vars['householdName']; ?>
</a><br />
                                        <?php endif; ?>
                                        <?php echo ((is_array($_tmp=$this->_tpl_vars['add']['display'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

                                    </td>
                                </tr>
                            </table>
			    <?php $_from = $this->_tpl_vars['add']['custom']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cgId'] => $this->_tpl_vars['customGroup']):
?>
                            <?php $this->assign('isAddressCustomPresent', 1); ?>
			        <?php $_from = $this->_tpl_vars['customGroup']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cvId'] => $this->_tpl_vars['customValue']):
?>
			            <div id="address_custom_<?php echo $this->_tpl_vars['cgId']; ?>
_<?php echo $this->_tpl_vars['locationIndex']; ?>
" class="crm-accordion-wrapper crm-address-custom-<?php echo $this->_tpl_vars['cgId']; ?>
-<?php echo $this->_tpl_vars['locationIndex']; ?>
-accordion crm-accordion-closed">
			                <div class="crm-accordion-header">
			                    <div class="icon crm-accordion-pointer"></div>
				            <?php echo $this->_tpl_vars['customValue']['title']; ?>

			                </div>
			                <div class="crm-accordion-body">
				            <table>
				                <?php $_from = $this->_tpl_vars['customValue']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cfId'] => $this->_tpl_vars['customField']):
?>
					            <tr><td class="label"><?php echo $this->_tpl_vars['customField']['field_title']; ?>
</td><td><?php echo $this->_tpl_vars['customField']['field_value']; ?>
</td></tr>
	                  	                <?php endforeach; endif; unset($_from); ?>
			                    </table>
			                </div>
			            </div>
                                    <script type="text/javascript">
                                        <?php if ($this->_tpl_vars['customValue']['collapse_display'] == 1): ?>
                                            cj('#address_custom_<?php echo $this->_tpl_vars['cgId']; ?>
_<?php echo $this->_tpl_vars['locationIndex']; ?>
').removeClass('crm-accordion-open').addClass('crm-accordion-closed');
                                        <?php else: ?>
                                            cj('#address_custom_<?php echo $this->_tpl_vars['cgId']; ?>
_<?php echo $this->_tpl_vars['locationIndex']; ?>
').removeClass('crm-accordion-closed').addClass('crm-accordion-open');
                                        <?php endif; ?>
                                    </script>
                                <?php endforeach; endif; unset($_from); ?>
                            <?php endforeach; endif; unset($_from); ?>
                        </div>
                        <?php endforeach; endif; unset($_from); ?>

                        <div class="clear"></div>
                    </div>
					<?php endif; ?>
					
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                            <table>
                                <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Privacy<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                                    <td><span class="font-red upper">
                                        <?php $_from = $this->_tpl_vars['privacy']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['index'] => $this->_tpl_vars['priv']):
?>
                                            <?php if ($this->_tpl_vars['priv']): ?><?php echo $this->_tpl_vars['privacy_values'][$this->_tpl_vars['index']]; ?>
<br /><?php endif; ?>
                                        <?php endforeach; endif; unset($_from); ?>
                                        <?php if ($this->_tpl_vars['is_opt_out']): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>No Bulk Emails (User Opt Out)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?>
                                    </span></td>
                                </tr>
                                <tr>
                                    <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Preferred Method(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo $this->_tpl_vars['preferred_communication_method_display']; ?>
</td>
                                </tr>
                                <tr>
                                    <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Preferred Language<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo $this->_tpl_vars['preferred_language']; ?>
</td>
                                </tr>
                                <tr>
                                    <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email Format<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo $this->_tpl_vars['preferred_mail_format']; ?>
</td>
                                </tr>
                            </table>
                        </div>

                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/View/Demographics.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
						
                        <div class="clear"></div>
                        <div class="separator"></div>
						
						<div class="contactCardLeft">
						<?php if ($this->_tpl_vars['contact_type'] != 'Organization'): ?>
						 <table>
							<tr>
								<td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email Greeting<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php if ($this->_tpl_vars['email_greeting_custom']): ?><br/><span style="font-size:8px;">(<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Customized<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)</span><?php endif; ?></td>
								<td><?php echo $this->_tpl_vars['email_greeting_display']; ?>
</td>
							</tr>
							<tr>
								<td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Postal Greeting<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php if ($this->_tpl_vars['postal_greeting_custom']): ?><br/><span style="font-size:8px;">(<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Customized<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)</span><?php endif; ?></td>
								<td><?php echo $this->_tpl_vars['postal_greeting_display']; ?>
</td>
							</tr>
						 </table>
						 <?php endif; ?>
						</div>
						<div class="contactCardRight">
						 <table>
							<tr>
								<td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Addressee<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php if ($this->_tpl_vars['addressee_custom']): ?><br/><span style="font-size:8px;">(<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Customized<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)</span><?php endif; ?></td>
								<td><?php echo $this->_tpl_vars['addressee_display']; ?>
</td>
							</tr>
						 </table>
						</div>
						
                        <div class="clear"></div>
                    </div>
                </div><!--contact_details-->

                <div id="customFields">
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/View/CustomDataView.tpl", 'smarty_include_vars' => array('side' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        </div><!--contactCardLeft-->

                        <div class="contactCardRight">
                            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/View/CustomDataView.tpl", 'smarty_include_vars' => array('side' => '0')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        </div>

                        <div class="clear"></div>
                    </div>
                </div>
                
                <?php if ($this->_tpl_vars['hookContent'] && $this->_tpl_vars['hookContentPlacement'] == 1): ?>
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/View/SummaryHook.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endif; ?>
            <?php else: ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/View/SummaryHook.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
        </div>
		<div class="clear"></div>
    </div>
 <script type="text/javascript"> 
 var selectedTab  = 'summary';
 var spinnerImage = '<img src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/loading.gif" style="width:10px;height:10px"/>';
 <?php if ($this->_tpl_vars['selectedChild']): ?>selectedTab = "<?php echo $this->_tpl_vars['selectedChild']; ?>
";<?php endif; ?>  
 <?php echo '
 function fixTabAbort(event,ui){
//	jQuery(ui.tab).data("cache.tabs",(jQuery(ui.panel).html() == "") ? false : true);
    }
 cj( function() {
     var tabIndex = cj(\'#tab_\' + selectedTab).prevAll().length;
     cj("#mainTabContainer").tabs({ selected: tabIndex, spinner: spinnerImage,cache: true, select: fixTabAbort});
     cj(".crm-tab-button").addClass("ui-corner-bottom");     
 });
 '; ?>

 </script>

<?php endif; ?>
<?php echo '
<script type="text/javascript">
function showHideSignature( blockId ) {
	  cj("#Email_Block_" + blockId + "_signature").show( );   
	  
	  cj("#Email_Block_" + blockId + "_signature").dialog({
		title: "Signature",
		modal: true,
		bgiframe: true,
		width: 900,
		height: 500,
		overlay: { 
			opacity: 0.5, 
			background: "black"
		},

		beforeclose: function(event, ui) {
            		cj(this).dialog("destroy");
        	},

		open:function() {
		},

		buttons: { 
			"Done": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			} 
		} 
		
	  });
}

</script>
'; ?>


<?php if ($this->_tpl_vars['isAddressCustomPresent']): ?>
    <?php echo '
        <script type="text/javascript">
            cj(function() {
                cj().crmaccordions(); 
            });
        </script>
    '; ?>

<?php endif; ?>
<div class="clear"></div>
</div><!-- /.crm-content-block -->