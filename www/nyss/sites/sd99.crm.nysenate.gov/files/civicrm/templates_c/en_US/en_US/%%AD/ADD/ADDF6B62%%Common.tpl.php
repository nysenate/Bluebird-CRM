<?php /* Smarty version 2.6.26, created on 2010-08-23 16:07:10
         compiled from CRM/Activity/Form/Search/Common.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Activity/Form/Search/Common.tpl', 28, false),array('function', 'cycle', 'CRM/Activity/Form/Search/Common.tpl', 31, false),array('modifier', 'crmReplace', 'CRM/Activity/Form/Search/Common.tpl', 77, false),)), $this); ?>
<tr>
  <?php if ($this->_tpl_vars['form']['activity_type_id']): ?>
     <td><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Activity Type(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
        <div id="Activity" class="listing-box">
          <?php $_from = $this->_tpl_vars['form']['activity_type_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['activity_type_val']):
?> 
             <div class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
               <?php echo $this->_tpl_vars['activity_type_val']['html']; ?>

             </div>
          <?php endforeach; endif; unset($_from); ?>
        </div>
     </td>
  <?php else: ?>
      <td>&nbsp;</td>
  <?php endif; ?> 
  <?php if ($this->_tpl_vars['form']['activity_tags']): ?>
    <td><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Activity Tag(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
      <div id ="Tags" class="listing-box">
         <?php $_from = $this->_tpl_vars['form']['activity_tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tag_val']):
?> 
              <div class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
                   <?php echo $this->_tpl_vars['tag_val']['html']; ?>

              </div>
         <?php endforeach; endif; unset($_from); ?>
    </td>
  <?php else: ?>
  	 <td>&nbsp;</td>
  <?php endif; ?> 
</tr>
<tr>
   <td>
      <?php echo $this->_tpl_vars['form']['activity_date_low']['label']; ?>
<br/>
	  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'activity_date_low')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 
   </td>
   <td>
	  <?php echo $this->_tpl_vars['form']['activity_date_high']['label']; ?>
<br/>
	  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'activity_date_high')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
   </td>
</tr>
<tr>
   <td>
	  <?php echo $this->_tpl_vars['form']['activity_role']['label']; ?>
<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('activity_role', '<?php echo $this->_tpl_vars['form']['formName']; ?>
'); document.getElementById('activity_target_name').value = ''; return false;" ><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>clear<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>)</span><br />
      <?php echo $this->_tpl_vars['form']['activity_role']['html']; ?>

   </td>
   <td colspan="2"><br />
	  <?php echo $this->_tpl_vars['form']['activity_target_name']['html']; ?>
<br />
      <span class="description font-italic"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Complete OR partial Contact Name.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span><br /><br />
	  <?php echo $this->_tpl_vars['form']['activity_test']['label']; ?>
 &nbsp; <?php echo $this->_tpl_vars['form']['activity_test']['html']; ?>
 
   </td>
</tr>
<tr>
   <td>
      <?php echo $this->_tpl_vars['form']['activity_subject']['label']; ?>
<br />
      <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['activity_subject']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>
 
   </td>
   <td colspan="2">
      <?php echo $this->_tpl_vars['form']['activity_status']['label']; ?>
<br />
      <?php echo $this->_tpl_vars['form']['activity_status']['html']; ?>
 
   </td>
</tr>
<?php if ($this->_tpl_vars['activityGroupTree']): ?>
<tr id="activityCustom">
   <td id="activityCustomData" colspan="2">
	  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/Search.tpl", 'smarty_include_vars' => array('groupTree' => $this->_tpl_vars['activityGroupTree'],'showHideLinks' => false)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
   </td>
</tr>
<?php endif; ?>

<?php echo '
<script type="text/javascript">
    cj(document).ready(function() { 
        //Searchable activity custom fields which extend ALL activity types are always displayed in the form 
        //hence hide remaining activity custom data
        cj( \'#activityCustom\' ).children( ).each( function( ) {
            cj( \'#\'+cj( this ).attr( \'id\' )+\' div\' ).each( function( ) {
                if ( cj( this ).children( ).attr( \'id\' ) ) {
                    var activityCustomdataGroup = cj( this ).attr( \'id\' );  //div id
                    var fieldsetId = cj( this ).children( ).attr( \'id\' );  // fieldset id
                    var splitFieldsetId = fieldsetId.split( "" );
                    var splitFieldsetLength = splitFieldsetId.length;  //length of fieldset
                    var show = 0;
                    //setdefault activity custom data group if corresponding activity type is checked
                    cj( \'#Activity div\' ).each( function( ) {
                        var checkboxId = cj( this ).children( ).attr( \'id\' );  //activity type element name
                        if ( document.getElementById( checkboxId ).checked ) {
                            var element = checkboxId.split( \'[\' );
                            var splitElement = element[1].split( \']\' );  // get activity type id
                            for( var i=0;i<splitFieldsetLength;i++ ) {
                                var singleFieldset = splitFieldsetId[i];
                                if ( parseInt( singleFieldset ) ) {
                                    if ( singleFieldset == splitElement[0] ) {
                                        show++;
                                    }
                                }
                            }
                        }
                    });
                    if ( show < 1 ) {
			            cj( \'#\'+activityCustomdataGroup ).hide( );
			        }
                }
            });
        });
    });
</script>


<script type="text/javascript">
function showCustomData( chkbox ) 
{		 
    if ( document.getElementById( chkbox ).checked ) {
        //inject Searchable activity custom fields according to activity type selected
        var element = chkbox.split("[");
        var splitElement = element[1].split("]");    
        cj( \'#activityCustom\').children().each( function( ) {
            cj( \'#\'+cj( this ).attr( \'id\' )+\' div\' ).each( function( ) {
                if ( cj( this ).children().attr( \'id\' ) ) {
                    if ( cj( \'#\'+cj( this ).attr( \'id\' )+( \' fieldset\' )).attr( \'id\' ) ) {
                        var fieldsetId = cj(\'#\'+cj( this ).attr( \'id\' )+( \' fieldset\' )).attr( \'id\' ).split( "" );
                        var activityTypeId = jQuery.inArray( splitElement[0], fieldsetId );                                     
                        if ( fieldsetId[activityTypeId] == splitElement[0] ) {
                            cj( this ).show();
                        }                            
                    } 
                }
            });
        });
    } else {
        //hide activity custom fields if the corresponding activity type is unchecked
        var setcount = 0;
        var element = chkbox.split( "[" );
        var splitElement = element[1].split( "]" );
            cj( \'#activityCustom\').children().each( function( ) {
                cj( \'#\'+cj( this ).attr( \'id\' )+\' div\' ).each(function() {
                    if ( cj( this ).children().attr( \'id\' ) ) {
                        if ( cj( \'#\'+cj( this ).attr( \'id\' )+( \' fieldset\') ).attr( \'id\' ) ) {
                            var fieldsetId = cj( \'#\'+cj( this ).attr( \'id\' )+( \' fieldset\' ) ).attr( \'id\' ).split( "" );
                            var activityTypeId = jQuery.inArray( splitElement[0],fieldsetId );
                                if ( fieldsetId[activityTypeId] ==  splitElement[0] ) {
                                    cj( \'#\'+cj( this ).attr( \'id\' ) ).each( function() {
                                        if ( cj( this ).children().attr( \'id\' ) ) {
                                        //if activity custom data extends more than one activity types then 
                                        //hide that only when all the extended activity types are unchecked
                                            cj( \'#\'+cj( this ).attr( \'id\' )+( \' fieldset\' ) ).each( function( ) {
                                                var splitFieldsetId = cj( this ).attr( \'id\' ).split( "" );
                                                var splitFieldsetLength = splitFieldsetId.length;
                                                for( var i=0;i<splitFieldsetLength;i++ ) {
                                                    var setActivityTypeId = splitFieldsetId[i];
                                                        if ( parseInt( setActivityTypeId ) ) {
                                                            var activityTypeId = \'activity_type_id[\'+setActivityTypeId+\']\';
                                                            if ( document.getElementById( activityTypeId ).checked ) {
                                                                return false;
                                                            } else {
                                                                setcount++;
                                                            }
                                                        }                   
                                                }                                  
                                                if ( setcount > 0 ) {
                                                    cj( \'#\'+cj( this ).parent().attr( \'id\' ) ).hide();
                                                }                                  
                                            });
                                        }
                                    });
                                }
                        } 
                    }
                });
            });
    } 
}
'; ?>
	     
</script>