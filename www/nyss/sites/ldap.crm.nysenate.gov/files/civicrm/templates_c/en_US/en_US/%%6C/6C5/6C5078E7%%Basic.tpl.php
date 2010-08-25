<?php /* Smarty version 2.6.26, created on 2010-04-14 20:34:11
         compiled from CRM/Contact/Form/Search/Criteria/Basic.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'help', 'CRM/Contact/Form/Search/Criteria/Basic.tpl', 28, false),array('block', 'ts', 'CRM/Contact/Form/Search/Criteria/Basic.tpl', 31, false),)), $this); ?>
	<table class="form-layout">
		<tr>
            <td class="font-size12pt"><?php echo $this->_tpl_vars['form']['sort_name']['label']; ?>
 <?php echo smarty_function_help(array('id' => 'id-advanced-intro'), $this);?>
</td>
            <td><?php echo $this->_tpl_vars['form']['sort_name']['html']; ?>

                <div class="description font-italic">
                    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Complete OR partial Contact Name.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                </div>
                <?php echo $this->_tpl_vars['form']['email']['html']; ?>

                <div class="description font-italic">
                    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Complete OR partial Email Address.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                </div>
            </td>
            <td>
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
            </td>
            <td class="label"><?php echo $this->_tpl_vars['form']['buttons']['html']; ?>
</td>       
        </tr>
		<tr>
<?php if ($this->_tpl_vars['form']['contact_type']): ?>
            <td><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact Type(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label><br />
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

            </td>
<?php else: ?>
            <td>&nbsp;</td>
<?php endif; ?>
<?php if ($this->_tpl_vars['form']['groups']): ?>
            <td><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Group(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
                <?php echo $this->_tpl_vars['form']['groups']['html']; ?>

                    <?php echo '
					<script type="text/javascript">

								cj("select#groups").crmasmSelect({
									addItemTarget: \'bottom\',
									animate: false,
									highlight: true,
									sortable: true,
									respectParents: true
								});

						</script>
					'; ?>

                
            </td>
<?php else: ?>
            <td>&nbsp;</td>
<?php endif; ?>

<?php if ($this->_tpl_vars['form']['contact_tags']): ?>
            <td colspan="2"><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Tag(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
                    <?php echo $this->_tpl_vars['form']['contact_tags']['html']; ?>

                    <?php echo '
					<script type="text/javascript">

								cj("select#tags").crmasmSelect({
									addItemTarget: \'bottom\',
									animate: false,
									highlight: true,
									sortable: true,
									respectParents: true
								});

						</script>
					'; ?>
   
            </td>
<?php else: ?>
            <td colspan="2">&nbsp;</td>
<?php endif; ?>
	    </tr>
        <tr>
            <td colspan="2">
                <?php echo $this->_tpl_vars['form']['privacy']['label']; ?>
<br />
                <?php echo $this->_tpl_vars['form']['privacy']['html']; ?>
 <?php echo smarty_function_help(array('id' => "id-privacy"), $this);?>

            </td>
            <td colspan="2">
                <?php echo $this->_tpl_vars['form']['preferred_communication_method']['label']; ?>
<br />
                <?php echo $this->_tpl_vars['form']['preferred_communication_method']['html']; ?>
<br />
                <div class="spacer"></div>
                <?php echo $this->_tpl_vars['form']['email_on_hold']['html']; ?>
 <?php echo $this->_tpl_vars['form']['email_on_hold']['label']; ?>

            </td>
        </tr>
        <tr>
            <td><?php echo $this->_tpl_vars['form']['contact_source']['label']; ?>
</td>
            <td><?php echo $this->_tpl_vars['form']['contact_source']['html']; ?>
</td>
            <td colspan="2"><?php echo $this->_tpl_vars['form']['job_title']['label']; ?>
&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['job_title']['html']; ?>
</td>
        </tr>
        <?php if ($this->_tpl_vars['form']['deleted_contacts']): ?>
          <tr>
            <td colspan="4"><?php echo $this->_tpl_vars['form']['deleted_contacts']['html']; ?>
 <?php echo $this->_tpl_vars['form']['deleted_contacts']['label']; ?>
</td>
          </tr>
        <?php endif; ?>
    </table>