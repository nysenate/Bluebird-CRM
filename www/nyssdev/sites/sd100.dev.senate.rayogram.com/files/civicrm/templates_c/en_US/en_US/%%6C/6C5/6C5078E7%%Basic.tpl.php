<?php /* Smarty version 2.6.26, created on 2010-05-25 12:25:22
         compiled from CRM/Contact/Form/Search/Criteria/Basic.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Search/Criteria/Basic.tpl', 34, false),array('function', 'help', 'CRM/Contact/Form/Search/Criteria/Basic.tpl', 82, false),array('function', 'crmURL', 'CRM/Contact/Form/Search/Criteria/Basic.tpl', 91, false),array('modifier', 'replace', 'CRM/Contact/Form/Search/Criteria/Basic.tpl', 130, false),)), $this); ?>


<table class="form-layout">
<tr>
    <td valign="top">
        Search Contacts<br />
        <?php echo $this->_tpl_vars['form']['sort_name']['html']; ?>

            <div class="description font-italic">
                <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Complete OR partial Contact Name.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
            </div>
            <?php echo $this->_tpl_vars['form']['email']['html']; ?>

            <div class="description font-italic">
                <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Complete OR partial Email Address.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
            </div>
            
    </td>
    <td valign="top">
    <?php if ($this->_tpl_vars['form']['group']): ?>
            <label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Group(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
                <?php echo $this->_tpl_vars['form']['group']['html']; ?>

                <?php echo '
                <script type="text/javascript">
                cj("select#group").crmasmSelect({
                    addItemTarget: \'bottom\',
                    animate: false,
                    highlight: true,
                    sortable: true,
                    respectParents: true
                });

                </script>
                '; ?>

    <?php endif; ?>
    
    </td>
    <td valign="top">
    <?php if ($this->_tpl_vars['form']['contact_tags']): ?>
    <label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Tag(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
                <?php echo $this->_tpl_vars['form']['contact_tags']['html']; ?>

                <?php echo '
                <script type="text/javascript">

                cj("select#contact_tags").crmasmSelect({
                    addItemTarget: \'bottom\',
                    animate: false,
                    highlight: true,
                    sortable: true,
                    respectParents: true
                });

                </script>
                '; ?>
   
    <?php endif; ?>
    
    </td>
    <td>
    <?php echo $this->_tpl_vars['form']['buttons']['html']; ?>
 <?php echo smarty_function_help(array('id' => 'id-advanced-intro'), $this);?>
<br />
    </td>
</tr>
<tr>
    <td>
    <div id="locationSection"></div>
    <div id="locationSectionHidden"></div>
    <script>
            <?php echo '
	           locationurl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/advanced','q' => "snippet=1&qfKey=".($this->_tpl_vars['qfKey'])."&searchPane=",'h' => 0), $this);?>
<?php echo '" + \'location .postal-code-search\';
	           cj(\'#locationSection\').load(locationurl);
	           locationHiddenurl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/search/advanced','q' => "snippet=1&qfKey=".($this->_tpl_vars['qfKey'])."&searchPane=",'h' => 0), $this);?>
<?php echo '" + \'location input[type=hidden]\';
            cj(\'#locationSectionHidden\').load(locationHiddenurl);
            '; ?>

            </script>
            
    </td> 
    <td>
    <?php if ($this->_tpl_vars['form']['contact_type']): ?>
            <label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact Type(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label><br />
                <?php echo $this->_tpl_vars['form']['contact_type']['html']; ?>

                 <?php echo '
					<script type="text/javascript">

								cj("select#contact_type").crmasmSelect({
									addItemTarget: \'bottom\',
									animate: false,
									highlight: true,
									sortable: true,
									respectParents: true
								});

						</script>
					'; ?>

    <?php endif; ?>
    <?php echo $this->_tpl_vars['form']['contact_source']['label']; ?>
<br />
    <?php echo $this->_tpl_vars['form']['contact_source']['html']; ?>
<br />
    <?php echo $this->_tpl_vars['form']['job_title']['label']; ?>
<br />
    <?php echo $this->_tpl_vars['form']['job_title']['html']; ?>

    <br />
    <?php echo $this->_tpl_vars['form']['preferred_communication_method']['label']; ?>
<br />
    <?php echo $this->_tpl_vars['form']['preferred_communication_method']['html']; ?>
<br />
    <?php echo $this->_tpl_vars['form']['email_on_hold']['html']; ?>
 <?php echo $this->_tpl_vars['form']['email_on_hold']['label']; ?>

    </td>
    <td colspan="2">
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/Tag.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <br />
        <?php echo $this->_tpl_vars['form']['privacy']['label']; ?>
<br />
        <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['privacy']['html'])) ? $this->_run_mod_handler('replace', true, $_tmp, '<input type="hidden" name="privacy[do_not_trade]" value="" /><input id="privacy[do_not_trade]" name="privacy[do_not_trade]" type="checkbox" value="1" class="form-checkbox" /><label for="privacy[do_not_trade]">Do not trade</label>', "") : smarty_modifier_replace($_tmp, '<input type="hidden" name="privacy[do_not_trade]" value="" /><input id="privacy[do_not_trade]" name="privacy[do_not_trade]" type="checkbox" value="1" class="form-checkbox" /><label for="privacy[do_not_trade]">Do not trade</label>', "")); ?>
 <?php echo smarty_function_help(array('id' => "id-privacy"), $this);?>

    </td>
</tr>
<tr>       
    <td></td>
    <td>
    </td>
    </tr>
</table>
<div style="display:none">
  <?php echo $this->_tpl_vars['form']['uf_group_id']['label']; ?>
 <?php echo $this->_tpl_vars['form']['uf_group_id']['html']; ?>

                <br /><br />
                <div class="form-item">
                    <?php if ($this->_tpl_vars['form']['uf_user']): ?><?php echo $this->_tpl_vars['form']['uf_user']['label']; ?>
 <?php echo $this->_tpl_vars['form']['uf_user']['html']; ?>

                    <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('uf_user', 'Advanced'); return false;" ><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>clear<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>)</span>

                    <div class="description font-italic">
                        <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['config']->userFramework)); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Does the contact have a %1 Account?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                    </div>
                    <?php endif; ?>
                </div>
</div>