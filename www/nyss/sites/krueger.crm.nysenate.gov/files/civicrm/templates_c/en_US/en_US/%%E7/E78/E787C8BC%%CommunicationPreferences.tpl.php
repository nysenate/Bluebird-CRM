<?php /* Smarty version 2.6.26, created on 2010-08-17 10:26:19
         compiled from CRM/Contact/Form/Edit/CommunicationPreferences.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'help', 'CRM/Contact/Form/Edit/CommunicationPreferences.tpl', 47, false),array('modifier', 'crmReplace', 'CRM/Contact/Form/Edit/CommunicationPreferences.tpl', 53, false),array('block', 'ts', 'CRM/Contact/Form/Edit/CommunicationPreferences.tpl', 55, false),)), $this); ?>

<div class="crm-accordion-wrapper crm-commPrefs-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	<?php echo $this->_tpl_vars['title']; ?>
 
  </div><!-- /.crm-accordion-header -->
<div id="commPrefs" class="crm-accordion-body">
    <table class="form-layout-compressed" >
        <tr>
            <?php if ($this->_tpl_vars['form']['email_greeting_id']): ?>
                <td><?php echo $this->_tpl_vars['form']['email_greeting_id']['label']; ?>
</td>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['postal_greeting_id']): ?>
                <td><?php echo $this->_tpl_vars['form']['postal_greeting_id']['label']; ?>
</td>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['addressee_id']): ?>
                <td><?php echo $this->_tpl_vars['form']['addressee_id']['label']; ?>
</td>
            <?php endif; ?>
	    <?php if ($this->_tpl_vars['form']['email_greeting_id'] || $this->_tpl_vars['form']['postal_greeting_id'] || $this->_tpl_vars['form']['addressee_id']): ?>
                <td>&nbsp;&nbsp;<?php echo smarty_function_help(array('id' => "id-greeting",'file' => "CRM/Contact/Form/Contact.hlp"), $this);?>
</td>
	    <?php endif; ?>
        </tr>
        <tr>
            <?php if ($this->_tpl_vars['form']['email_greeting_id']): ?>
                <td>
                    <span id="email_greeting" <?php if ($this->_tpl_vars['email_greeting_display'] && $this->_tpl_vars['action'] == 2): ?> class="hiddenElement"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['email_greeting_id']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>
</span>
                    <?php if ($this->_tpl_vars['email_greeting_display'] && $this->_tpl_vars['action'] == 2): ?>
                        <div id="email_greeting_display" class="view-data"><?php echo $this->_tpl_vars['email_greeting_display']; ?>
&nbsp;&nbsp;<a href="#" onclick="showGreeting('email_greeting');return false;"><img src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/edit.png" border="0" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"></a></div>
                    <?php endif; ?>
                </td>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['postal_greeting_id']): ?>
                <td>
                    <span id="postal_greeting" <?php if ($this->_tpl_vars['postal_greeting_display'] && $this->_tpl_vars['action'] == 2): ?> class="hiddenElement"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['postal_greeting_id']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>
</span>
                    <?php if ($this->_tpl_vars['postal_greeting_display'] && $this->_tpl_vars['action'] == 2): ?>
                        <div id="postal_greeting_display" class="view-data"><?php echo $this->_tpl_vars['postal_greeting_display']; ?>
&nbsp;&nbsp;<a href="#" onclick="showGreeting('postal_greeting');return false;"><img src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/edit.png" border="0" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"></a></div>
                    <?php endif; ?>
                </td>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['addressee_id']): ?>
                <td>
                    <span id="addressee" <?php if ($this->_tpl_vars['addressee_display'] && $this->_tpl_vars['action'] == 2): ?> class="hiddenElement"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['addressee_id']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>
</span>
                    <?php if ($this->_tpl_vars['addressee_display'] && $this->_tpl_vars['action'] == 2): ?>
                        <div id="addressee_display" class="view-data"><?php echo $this->_tpl_vars['addressee_display']; ?>
&nbsp;&nbsp;<a href="#" onclick="showGreeting('addressee');return false;"><img src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/edit.png" border="0" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"></a></div>
                    <?php endif; ?>
                </td>
            <?php endif; ?>
        </tr>
        <tr id="greetings1" class="hiddenElement">
            <?php if ($this->_tpl_vars['form']['email_greeting_custom']): ?>
                <td><span id="email_greeting_id_label" class="hiddenElement"><?php echo $this->_tpl_vars['form']['email_greeting_custom']['label']; ?>
</span></td>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['postal_greeting_custom']): ?>
                <td><span id="postal_greeting_id_label" class="hiddenElement"><?php echo $this->_tpl_vars['form']['postal_greeting_custom']['label']; ?>
</span></td>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['addressee_custom']): ?>
                <td><span id="addressee_id_label" class="hiddenElement"><?php echo $this->_tpl_vars['form']['addressee_custom']['label']; ?>
</span></td>
            <?php endif; ?>
        </tr>
        <tr id="greetings2" class="hiddenElement">
            <?php if ($this->_tpl_vars['form']['email_greeting_custom']): ?>
                <td><span id="email_greeting_id_html" class="hiddenElement"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['email_greeting_custom']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>
</span></td>
            <?php endif; ?>
             <?php if ($this->_tpl_vars['form']['postal_greeting_custom']): ?>
                <td><span id="postal_greeting_id_html" class="hiddenElement"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['postal_greeting_custom']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>
</span></td>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['addressee_custom']): ?>
                <td><span id="addressee_id_html" class="hiddenElement"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['addressee_custom']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>
</span></td>
            <?php endif; ?>
        </tr>
        <tr>
            <?php $_from = $this->_tpl_vars['commPreference']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['item']):
?>
                <td>
                    <br /><span class="label"<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['key']]['label']; ?>
</span> <?php echo smarty_function_help(array('id' => "id-".($this->_tpl_vars['key']),'file' => "CRM/Contact/Form/Contact.hlp"), $this);?>

                    <?php $_from = $this->_tpl_vars['item']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['i']):
?>
                     <span class="commPreference <?php echo $this->_tpl_vars['k']; ?>
"><br /><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['key']][$this->_tpl_vars['k']]['html']; ?>
</span>
                    <?php endforeach; endif; unset($_from); ?>
                </td>
            <?php endforeach; endif; unset($_from); ?>
                 <td>
                     <br /><span class="label"<?php echo $this->_tpl_vars['form']['preferred_language']['label']; ?>
</span>
                     <br /><?php echo $this->_tpl_vars['form']['preferred_language']['html']; ?>

                </td>
        </tr>
        <tr>
            <td><?php echo $this->_tpl_vars['form']['is_opt_out']['html']; ?>
 <?php echo $this->_tpl_vars['form']['is_opt_out']['label']; ?>
 <?php echo smarty_function_help(array('id' => "id-optOut",'file' => "CRM/Contact/Form/Contact.hlp"), $this);?>
</td>
            <td><?php echo $this->_tpl_vars['form']['preferred_mail_format']['label']; ?>
 &nbsp;
                <?php echo $this->_tpl_vars['form']['preferred_mail_format']['html']; ?>
 <?php echo smarty_function_help(array('id' => "id-emailFormat",'file' => "CRM/Contact/Form/Contact.hlp"), $this);?>

            </td>

        </tr>
    </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->


<?php echo '
<script type="text/javascript">
cj( function( ) {
    var fields = new Array( \'postal_greeting\', \'addressee\', \'email_greeting\');
    for ( var i = 0; i < 3; i++ ) {
        cj( "#" + fields[i] + "_id").change( function( ) {
            var fldName = cj(this).attr( \'id\' );
            if ( cj(this).val( ) == 4 ) {
                cj("#greetings1").show( );
                cj("#greetings2").show( );
                cj( "#" + fldName + "_html").show( );
                cj( "#" + fldName + "_label").show( );
            } else {
                cj( "#" + fldName + "_html").hide( );
                cj( "#" + fldName + "_label").hide( );
                cj( "#" + fldName.slice(0, -3) + "_custom" ).val(\'\');
            }
        });
    }          
});

function showGreeting( element ) {
    cj("#" + element ).show( );
    cj("#" + element + \'_display\' ).hide( );
    
    // TO DO fix for custom greeting
    var fldName = \'#\' + element + \'_id\';
    if ( cj( fldName ).val( ) == 4 ) {
        cj("#greetings1").show( );
        cj("#greetings2").show( );
        cj( fldName + "_html").show( );
        cj( fldName + "_label").show( );
    }
}

</script>
'; ?>