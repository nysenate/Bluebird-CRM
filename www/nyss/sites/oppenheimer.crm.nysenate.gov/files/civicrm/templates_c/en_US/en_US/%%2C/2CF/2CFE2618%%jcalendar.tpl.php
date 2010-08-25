<?php /* Smarty version 2.6.26, created on 2010-08-17 16:57:02
         compiled from CRM/common/jcalendar.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'CRM/common/jcalendar.tpl', 29, false),array('modifier', 'crmReplace', 'CRM/common/jcalendar.tpl', 31, false),array('block', 'ts', 'CRM/common/jcalendar.tpl', 47, false),)), $this); ?>
<?php echo ''; ?><?php if ($this->_tpl_vars['batchUpdate']): ?><?php echo ''; ?><?php $this->assign('elementId', $this->_tpl_vars['form']['field'][$this->_tpl_vars['elementIndex']][$this->_tpl_vars['elementName']]['id']); ?><?php echo ''; ?><?php $this->assign('tElement', ((is_array($_tmp=$this->_tpl_vars['elementName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_time') : smarty_modifier_cat($_tmp, '_time'))); ?><?php echo ''; ?><?php $this->assign('timeElement', "field_".($this->_tpl_vars['elementIndex'])."_".($this->_tpl_vars['elementName'])."_time"); ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['field'][$this->_tpl_vars['elementIndex']][$this->_tpl_vars['elementName']]['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'dateplugin') : smarty_modifier_crmReplace($_tmp, 'class', 'dateplugin')); ?><?php echo '&nbsp;&nbsp;'; ?><?php echo $this->_tpl_vars['form']['field'][$this->_tpl_vars['elementIndex']][$this->_tpl_vars['tElement']]['label']; ?><?php echo '&nbsp;&nbsp;'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['field'][$this->_tpl_vars['elementIndex']][$this->_tpl_vars['tElement']]['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'six') : smarty_modifier_crmReplace($_tmp, 'class', 'six')); ?><?php echo ''; ?><?php elseif ($this->_tpl_vars['elementIndex']): ?><?php echo ''; ?><?php $this->assign('elementId', $this->_tpl_vars['form'][$this->_tpl_vars['elementName']][$this->_tpl_vars['elementIndex']]['id']); ?><?php echo ''; ?><?php $this->assign('timeElement', ((is_array($_tmp=$this->_tpl_vars['elementName'])) ? $this->_run_mod_handler('cat', true, $_tmp, "_time.".($this->_tpl_vars['elementIndex'])) : smarty_modifier_cat($_tmp, "_time.".($this->_tpl_vars['elementIndex'])))); ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['form'][$this->_tpl_vars['elementName']][$this->_tpl_vars['elementIndex']]['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'dateplugin') : smarty_modifier_crmReplace($_tmp, 'class', 'dateplugin')); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->assign('elementId', $this->_tpl_vars['form'][$this->_tpl_vars['elementName']]['id']); ?><?php echo ''; ?><?php $this->assign('timeElement', ((is_array($_tmp=$this->_tpl_vars['elementName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_time') : smarty_modifier_cat($_tmp, '_time'))); ?><?php echo ''; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['form'][$this->_tpl_vars['elementName']]['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'dateplugin') : smarty_modifier_crmReplace($_tmp, 'class', 'dateplugin')); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $this->assign('dateFormated', ((is_array($_tmp=$this->_tpl_vars['elementId'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_hidden') : smarty_modifier_cat($_tmp, '_hidden'))); ?><?php echo '<input type="text" name="'; ?><?php echo $this->_tpl_vars['dateFormated']; ?><?php echo '" id="'; ?><?php echo $this->_tpl_vars['dateFormated']; ?><?php echo '" class="hiddenElement"/>'; ?><?php if ($this->_tpl_vars['timeElement'] && ! $this->_tpl_vars['tElement']): ?><?php echo '&nbsp;&nbsp;'; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['timeElement']]['label']; ?><?php echo '&nbsp;&nbsp;'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['form'][$this->_tpl_vars['timeElement']]['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'six') : smarty_modifier_crmReplace($_tmp, 'class', 'six')); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['action'] != 4 && $this->_tpl_vars['action'] != 1028): ?><?php echo '<span class="crm-clear-link">(<a href="javascript:clearDateTime( \''; ?><?php echo $this->_tpl_vars['elementId']; ?><?php echo '\' );">'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'clear'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a>)</span>'; ?><?php endif; ?><?php echo '<script type="text/javascript">'; ?><?php echo '
    cj( function() {
      '; ?><?php echo 'var element_date   = "#'; ?><?php echo $this->_tpl_vars['elementId']; ?><?php echo '";'; ?><?php if ($this->_tpl_vars['timeElement']): ?><?php echo 'var element_time  = "#'; ?><?php echo $this->_tpl_vars['timeElement']; ?><?php echo '";var time_format   = cj( element_time ).attr(\'timeFormat\');'; ?><?php echo '
              cj(element_time).timeEntry({ show24Hours : time_format, spinnerImage: \'\' });
          '; ?><?php echo ''; ?><?php endif; ?><?php echo 'var currentYear = new Date().getFullYear();var date_format = cj( element_date ).attr(\'format\');var alt_field   = \'input#'; ?><?php echo $this->_tpl_vars['dateFormated']; ?><?php echo '\';var yearRange   = currentYear - parseInt( cj( element_date ).attr(\'startOffset\') );yearRange  += \':\';yearRange  += currentYear + parseInt( cj( element_date ).attr(\'endOffset\'  ) );'; ?><?php echo '
 
      cj(element_date).datepicker({
                                    closeAtTop        : true, 
                                    dateFormat        : date_format,
                                    changeMonth       : true,
                                    changeYear        : true,
                                    altField          : alt_field,
                                    altFormat         : \'mm/dd/yy\',
                                    yearRange         : yearRange
                                });
    
      cj(element_date).click( function( ) {
          hideYear( this );
      });  
      cj(\'.ui-datepicker-trigger\').click( function( ) {
          hideYear( cj(this).prev() );
      });  
    });
    
    function hideYear( element ) {
        var format = cj( element ).attr(\'format\');
        if ( format == \'dd-mm\' || format == \'mm/dd\' ) {
            cj(".ui-datepicker-year").css( \'display\', \'none\' );
        }
    }
    
    function clearDateTime( element ) {
        cj(\'input#\' + element + \',input#\' + element + \'_time\').val(\'\');
    }
    '; ?><?php echo '</script>'; ?>
