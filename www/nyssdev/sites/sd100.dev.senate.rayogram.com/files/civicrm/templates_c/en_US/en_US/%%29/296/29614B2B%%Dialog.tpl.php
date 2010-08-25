<?php /* Smarty version 2.6.26, created on 2010-05-24 17:42:28
         compiled from CRM/Core/I18n/Dialog.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'CRM/Core/I18n/Dialog.tpl', 26, false),array('function', 'crmURL', 'CRM/Core/I18n/Dialog.tpl', 27, false),)), $this); ?>
<?php if (count($this->_tpl_vars['config']->languageLimit) >= 2 && $this->_tpl_vars['translatePermission']): ?>
<a href="javascript:" onClick="loadDialog('<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/i18n','q' => "reset=1&table=".($this->_tpl_vars['table'])."&field=".($this->_tpl_vars['field'])."&id=".($this->_tpl_vars['id'])."&snippet=1&context=dialog",'h' => 0), $this);?>
', '<?php echo $this->_tpl_vars['field']; ?>
');"><img src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/langs.png" /></a><div id="locale-dialog_<?php echo $this->_tpl_vars['field']; ?>
" style="display:none"></div>

<?php echo '
<script type="text/javascript">
function loadDialog( url, fieldName ) {
 cj.ajax({
         url: url,
         success: function( content ) {
             cj("#locale-dialog_" +fieldName ).show( ).html( content ).dialog({
             		modal       : true,
			width       : 290,
			height      : 290,
			resizable   : true,
			bgiframe    : true,
			overlay     : { opacity: 0.5, background: "black" },
			beforeclose : function(event, ui) {
			               cj(this).dialog("destroy");
   			              }
             });
         }
      });
}
</script>
'; ?>

<?php endif; ?>