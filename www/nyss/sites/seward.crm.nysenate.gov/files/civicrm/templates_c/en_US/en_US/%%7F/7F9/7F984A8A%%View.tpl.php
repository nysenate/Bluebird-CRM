<?php /* Smarty version 2.6.26, created on 2010-08-16 22:26:15
         compiled from CRM/Profile/Page/View.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Profile/Page/View.tpl', 44, false),)), $this); ?>

<?php if ($this->_tpl_vars['overlayProfile']): ?> 
<?php $_from = $this->_tpl_vars['profileGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group']):
?>
    <div class="crm-summary-group">
    	 <?php echo $this->_tpl_vars['group']['content']; ?>

    </div>
<?php endforeach; endif; unset($_from); ?>
<?php else: ?>
<?php $_from = $this->_tpl_vars['profileGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group']):
?>
    <h2><?php echo $this->_tpl_vars['group']['title']; ?>
</h2>
    <div id="profilewrap<?php echo $this->_tpl_vars['groupID']; ?>
">
    	 <?php echo $this->_tpl_vars['group']['content']; ?>

    </div>
<?php endforeach; endif; unset($_from); ?>
<div class="action-link">
<?php if ($this->_tpl_vars['listingURL']): ?>
    <a href="<?php echo $this->_tpl_vars['listingURL']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Back to Listings<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php endif; ?>
    <?php if ($this->_tpl_vars['mapURL']): ?>
    <a href="<?php echo $this->_tpl_vars['mapURL']; ?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Map Primary Address<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
    <?php endif; ?>

</div>
<?php endif; ?>
<?php echo '
     <script type=\'text/javascript\'>
          function contactImagePopUp (url, width, height) {
 	      newWindow = window.open( url,\'name\', \'width=\'+width+\', height=\'+height );
          }
     </script>
'; ?>