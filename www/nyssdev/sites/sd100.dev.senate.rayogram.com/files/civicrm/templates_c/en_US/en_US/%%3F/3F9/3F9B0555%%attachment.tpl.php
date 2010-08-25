<?php /* Smarty version 2.6.26, created on 2010-05-24 18:06:51
         compiled from CRM/Form/attachment.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Form/attachment.tpl', 28, false),array('modifier', 'cat', 'CRM/Form/attachment.tpl', 66, false),)), $this); ?>
<?php if ($this->_tpl_vars['form']['attachFile_1']): ?>
<?php if ($this->_tpl_vars['action'] == 4 && $this->_tpl_vars['currentAttachmentURL']): ?>     <fieldset><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Attachment(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
    <table class="form-layout-compressed">
    <tr>
        <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Current Attachment(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
        <td class="view-value"><strong><?php echo $this->_tpl_vars['currentAttachmentURL']; ?>
</strong></td>
    </tr>
    </table>
    </fieldset>

<?php elseif ($this->_tpl_vars['action'] != 4): ?>
    <?php if ($this->_tpl_vars['context'] == 'pcpCampaign'): ?>
        <?php ob_start(); ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Include a Picture or an Image<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('attachTitle', ob_get_contents());ob_end_clean(); ?>
        <?php $this->assign('openCloseStyle', 'crm-accordion-open'); ?>
    <?php else: ?>
        <?php ob_start(); ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Attachment(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('attachTitle', ob_get_contents());ob_end_clean(); ?>
        <?php $this->assign('openCloseStyle', 'crm-accordion-closed'); ?>
    <?php endif; ?>
    <?php if (! $this->_tpl_vars['noexpand']): ?>
    <div class="crm-accordion-wrapper crm-accordion_title-accordion <?php echo $this->_tpl_vars['openCloseStyle']; ?>
">
 		<div class="crm-accordion-header">
  			<div class="icon crm-accordion-pointer"></div> 
  			<?php echo $this->_tpl_vars['attachTitle']; ?>

			</div><!-- /.crm-accordion-header -->
 		<div class="crm-accordion-body">    
 	<?php endif; ?>
    <div id="attachments">
        <?php if ($this->_tpl_vars['context'] == 'pcpCampaign'): ?>
            <div class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You can upload a picture or image to include on your page. Your file should be in .jpg, .gif, or .png format. Recommended image size is 250 x 250 pixels. Maximum size is 360 x 360 pixels.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
        <?php endif; ?>
        <table class="form-layout-compressed">
            <tr>
                <td class="label"><?php echo $this->_tpl_vars['form']['attachFile_1']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['attachFile_1']['html']; ?>
<br />
                    <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Browse to the <strong>file</strong> you want to upload.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php if ($this->_tpl_vars['numAttachments'] > 1): ?> <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['numAttachments'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You can have a maximum of %1 attachment(s).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></span>
                </td>
            </tr>
    <?php unset($this->_sections['attachLoop']);
$this->_sections['attachLoop']['name'] = 'attachLoop';
$this->_sections['attachLoop']['start'] = (int)2;
$this->_sections['attachLoop']['loop'] = is_array($_loop=$this->_tpl_vars['numAttachments']+1) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['attachLoop']['show'] = true;
$this->_sections['attachLoop']['max'] = $this->_sections['attachLoop']['loop'];
$this->_sections['attachLoop']['step'] = 1;
if ($this->_sections['attachLoop']['start'] < 0)
    $this->_sections['attachLoop']['start'] = max($this->_sections['attachLoop']['step'] > 0 ? 0 : -1, $this->_sections['attachLoop']['loop'] + $this->_sections['attachLoop']['start']);
else
    $this->_sections['attachLoop']['start'] = min($this->_sections['attachLoop']['start'], $this->_sections['attachLoop']['step'] > 0 ? $this->_sections['attachLoop']['loop'] : $this->_sections['attachLoop']['loop']-1);
if ($this->_sections['attachLoop']['show']) {
    $this->_sections['attachLoop']['total'] = min(ceil(($this->_sections['attachLoop']['step'] > 0 ? $this->_sections['attachLoop']['loop'] - $this->_sections['attachLoop']['start'] : $this->_sections['attachLoop']['start']+1)/abs($this->_sections['attachLoop']['step'])), $this->_sections['attachLoop']['max']);
    if ($this->_sections['attachLoop']['total'] == 0)
        $this->_sections['attachLoop']['show'] = false;
} else
    $this->_sections['attachLoop']['total'] = 0;
if ($this->_sections['attachLoop']['show']):

            for ($this->_sections['attachLoop']['index'] = $this->_sections['attachLoop']['start'], $this->_sections['attachLoop']['iteration'] = 1;
                 $this->_sections['attachLoop']['iteration'] <= $this->_sections['attachLoop']['total'];
                 $this->_sections['attachLoop']['index'] += $this->_sections['attachLoop']['step'], $this->_sections['attachLoop']['iteration']++):
$this->_sections['attachLoop']['rownum'] = $this->_sections['attachLoop']['iteration'];
$this->_sections['attachLoop']['index_prev'] = $this->_sections['attachLoop']['index'] - $this->_sections['attachLoop']['step'];
$this->_sections['attachLoop']['index_next'] = $this->_sections['attachLoop']['index'] + $this->_sections['attachLoop']['step'];
$this->_sections['attachLoop']['first']      = ($this->_sections['attachLoop']['iteration'] == 1);
$this->_sections['attachLoop']['last']       = ($this->_sections['attachLoop']['iteration'] == $this->_sections['attachLoop']['total']);
?>
        <?php $this->assign('index', $this->_sections['attachLoop']['index']); ?>
        <?php $this->assign('attachName', ((is_array($_tmp='attachFile_')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['index']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['index']))); ?>
            <tr>
                <td class="label"></td>
                <td><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['attachName']]['html']; ?>
</td>
            </tr>
    <?php endfor; endif; ?>
    <?php if ($this->_tpl_vars['currentAttachmentURL']): ?>
        <tr>
            <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Current Attachment(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
            <td class="view-value"><strong><?php echo $this->_tpl_vars['currentAttachmentURL']; ?>
</strong></td>
        </tr>
        <tr>
            <td class="label">&nbsp;</td>
            <td><?php echo $this->_tpl_vars['form']['is_delete_attachment']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['is_delete_attachment']['label']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Check this box and click Save to delete all current attachments.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            </td>
        </tr>
    <?php endif; ?>
        </table>
    </div>
	</div><!-- /.crm-accordion-body -->
	</div><!-- /.crm-accordion-wrapper -->
<?php if (! $this->_tpl_vars['noexpand']): ?>
    <?php echo '
    <script type="text/javascript">
        var attachmentUrl = '; ?>
'<?php echo $this->_tpl_vars['currentAttachmentURL']; ?>
'<?php echo ';
		cj(function() {
		   cj().crmaccordions(); 
		});
    </script>
    '; ?>

<?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
