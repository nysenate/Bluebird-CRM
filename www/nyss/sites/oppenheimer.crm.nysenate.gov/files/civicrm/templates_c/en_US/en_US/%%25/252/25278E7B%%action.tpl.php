<?php /* Smarty version 2.6.26, created on 2010-08-13 13:22:42
         compiled from CRM/common/action.tpl */ ?>
<?php echo '
<script type="text/javascript">
 cj('; ?>
<?php if ($this->_tpl_vars['isSnippet']): ?>document<?php else: ?>'#crm-container'<?php endif; ?><?php echo ')
 	.bind(\'click\', function(event) {
    if (cj(event.target).is(\'.btn-slide\')) {
      cj(event.target).children().show();
    } else {
    	cj(\'.btn-slide .panel\').hide();	
	} 
  });
</script>
'; ?>