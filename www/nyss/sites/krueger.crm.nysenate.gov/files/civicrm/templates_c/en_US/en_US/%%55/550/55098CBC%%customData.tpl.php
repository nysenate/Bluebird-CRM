<?php /* Smarty version 2.6.26, created on 2010-08-17 10:26:18
         compiled from CRM/common/customData.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/common/customData.tpl', 31, false),)), $this); ?>
<?php echo '
<script type="text/javascript">

function buildCustomData( type, subType, subName, cgCount, groupID, isMultiple )
{
	var dataUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => $this->_tpl_vars['urlPath'],'h' => 0,'q' => 'snippet=4&type='), $this);?>
"<?php echo ' + type; 

	if ( subType ) {
		dataUrl = dataUrl + \'&subType=\' + subType;
	}

	if ( subName ) {
		dataUrl = dataUrl + \'&subName=\' + subName;
		cj(\'#customData\' + subName ).show();
	} else {
		cj(\'#customData\').show();		
	}
	
	'; ?>

		<?php if ($this->_tpl_vars['urlPathVar']): ?>
			dataUrl = dataUrl + '&' + '<?php echo $this->_tpl_vars['urlPathVar']; ?>
'
		<?php endif; ?>
		<?php if ($this->_tpl_vars['groupID']): ?>
			dataUrl = dataUrl + '&groupID=' + '<?php echo $this->_tpl_vars['groupID']; ?>
'
		<?php endif; ?>
		<?php if ($this->_tpl_vars['qfKey']): ?>
			dataUrl = dataUrl + '&qfKey=' + '<?php echo $this->_tpl_vars['qfKey']; ?>
'
		<?php endif; ?>
		<?php if ($this->_tpl_vars['entityID']): ?>
			dataUrl = dataUrl + '&entityID=' + '<?php echo $this->_tpl_vars['entityID']; ?>
'
		<?php endif; ?>
	<?php echo '

	if ( !cgCount ) {
		cgCount = 1;
		var prevCount = 1;		
	} else if ( cgCount >= 1 ) {
		var prevCount = cgCount;	
		cgCount++;
	}

	dataUrl = dataUrl + \'&cgcount=\' + cgCount;


	if ( isMultiple ) {
		var fname = \'#custom_group_\' + groupID + \'_\' + prevCount;
		cj("#add-more-link-"+prevCount).hide();
	} else {
		if ( subName && subName != \'null\' ) {		
			var fname = \'#customData\' + subName ;
		} else {
			var fname = \'#customData\';
		}		
	}
	
	var response = cj.ajax({
						url: dataUrl,
						async: false
					}).responseText;

	cj( fname ).html( response );
}

</script>
'; ?>
