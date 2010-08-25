<?php /* Smarty version 2.6.26, created on 2010-08-16 12:12:46
         compiled from CRM/common/pager.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/common/pager.tpl', 32, false),)), $this); ?>
<?php if ($this->_tpl_vars['pager'] && $this->_tpl_vars['pager']->_response): ?>
    <?php if ($this->_tpl_vars['pager']->_response['numPages'] > 1): ?>
        <div class="crm-pager">
          <?php if (! isset ( $this->_tpl_vars['noForm'] ) || ! $this->_tpl_vars['noForm']): ?>
            <span class="element-right">
            <?php if ($this->_tpl_vars['location'] == 'top'): ?>
              <?php echo $this->_tpl_vars['pager']->_response['titleTop']; ?>
&nbsp;<input class="form-submit" name="<?php echo $this->_tpl_vars['pager']->_response['buttonTop']; ?>
" value="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Go<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" type="submit"/>
            <?php else: ?>
              <?php echo $this->_tpl_vars['pager']->_response['titleBottom']; ?>
&nbsp;<input class="form-submit" name="<?php echo $this->_tpl_vars['pager']->_response['buttonBottom']; ?>
" value="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Go<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" type="submit"/>
            <?php endif; ?>
            </span>
          <?php endif; ?>
          <span class="crm-pager-nav">
          <?php echo $this->_tpl_vars['pager']->_response['first']; ?>
&nbsp;
          <?php echo $this->_tpl_vars['pager']->_response['back']; ?>
&nbsp;
          <?php echo $this->_tpl_vars['pager']->_response['next']; ?>
&nbsp;
          <?php echo $this->_tpl_vars['pager']->_response['last']; ?>
&nbsp;          
          <?php echo $this->_tpl_vars['pager']->_response['status']; ?>
          
          </span>

        </div>
    <?php endif; ?>
    
        <?php if ($this->_tpl_vars['location'] == 'bottom' && $this->_tpl_vars['pager']->_totalItems > 25): ?>
     <div class="form-item float-right">
           <label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Rows per page:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label> &nbsp; 
           <?php echo $this->_tpl_vars['pager']->_response['twentyfive']; ?>
&nbsp; | &nbsp;
           <?php echo $this->_tpl_vars['pager']->_response['fifty']; ?>
&nbsp; | &nbsp;
           <?php echo $this->_tpl_vars['pager']->_response['onehundred']; ?>
&nbsp; 
     </div>
     <div class="clear"></div>
    <?php endif; ?>

<?php endif; ?>