<?php /* Smarty version 2.6.26, created on 2010-07-07 15:32:30
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