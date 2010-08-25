<?php /* Smarty version 2.6.26, created on 2010-08-13 11:34:50
         compiled from CRM/common/Tag.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmKey', 'CRM/common/Tag.tpl', 16, false),array('function', 'crmURL', 'CRM/common/Tag.tpl', 29, false),array('block', 'ts', 'CRM/common/Tag.tpl', 21, false),)), $this); ?>
<?php $_from = $this->_tpl_vars['tagset']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tagset']):
?>

<div class="crm-section tag-section tag-<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
-section">
<div class="label">
<label><?php echo $this->_tpl_vars['tagset']['parentName']; ?>
</label>
</div>
<div class="content">
<?php $this->assign('elemName', 'taglist'); ?>
<?php $this->assign('parID', $this->_tpl_vars['tagset']['parentID']); ?>
<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['elemName']][$this->_tpl_vars['parID']]['html']; ?>

<?php if ($this->_tpl_vars['action'] != 4 || $this->_tpl_vars['form']['formName'] == 'CaseView'): ?>
<script type="text/javascript">
<?php echo '
    eval( \'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } \');
    
    var tagUrl = '; ?>
"<?php echo $this->_tpl_vars['tagset']['tagUrl']; ?>
&key=<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/taglist'), $this);?>
"<?php echo ';
    var entityTags = \'\';
    '; ?>
<?php if ($this->_tpl_vars['tagset']['entityTags']): ?><?php echo '
        eval( \'entityTags = \' + '; ?>
'<?php echo $this->_tpl_vars['tagset']['entityTags']; ?>
'<?php echo ' );
    '; ?>
<?php endif; ?><?php echo '
    var hintText = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Type in a partial or complete name of an existing tag.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
    
    cj( ".tag-'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '-section:not(.crm-processed-input) input")
        .addClass("taglist_'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '")
    cj( ".tag-'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '-section:not(.crm-processed-input) .taglist_'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '"  )
        .tokenInput( tagUrl, { prePopulate: entityTags, classes: tokenClass, hintText: hintText, ajaxCallbackFunction: \'processTags_'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '\'});
    cj( ".tag-'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '-section:not(.crm-processed-input)").addClass("crm-processed-input");    
    function processTags_'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '( action, id ) {
        var postUrl          = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/processTags','h' => 0), $this);?>
<?php echo '";
        var parentId         = "'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '";
        var entityId         = "'; ?>
<?php echo $this->_tpl_vars['tagset']['entityId']; ?>
<?php echo '";
        var entityTable      = "'; ?>
<?php echo $this->_tpl_vars['tagset']['entityTable']; ?>
<?php echo '";
        var skipTagCreate    = "'; ?>
<?php echo $this->_tpl_vars['tagset']['skipTagCreate']; ?>
<?php echo '";
        var skipEntityAction = "'; ?>
<?php echo $this->_tpl_vars['tagset']['skipEntityAction']; ?>
<?php echo '";
         
        cj.post( postUrl, { action: action, tagID: id, parentId: parentId, entityId: entityId, entityTable: entityTable,
                            skipTagCreate: skipTagCreate, skipEntityAction: skipEntityAction, key: '; ?>
"<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/processTags'), $this);?>
"<?php echo ' },
            function ( response ) {
                // update hidden element
                if ( response.id ) {
                    var curVal   = cj( ".taglist_'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '" ).val( );
                    var valArray = curVal.split(\',\');
                    var setVal   = Array( );
                    if ( response.action == \'delete\' ) {
                        for ( x in valArray ) {
                            if ( valArray[x] != response.id ) {
                                setVal[x] = valArray[x];
                            }
                        }
                    } else if ( response.action == \'select\' ) {
                        setVal    = valArray;
                        setVal[ setVal.length ] = response.id;
                    }
                    
                    var actualValue = setVal.join( \',\' );
                    cj( ".taglist_'; ?>
<?php echo $this->_tpl_vars['tagset']['parentID']; ?>
<?php echo '" ).val( actualValue );
                }
            }, "json" );
    }
'; ?>

</script>
<?php else: ?>
    <?php if ($this->_tpl_vars['tagset']['entityTagsArray']): ?>
        <?php $_from = $this->_tpl_vars['tagset']['entityTagsArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['tagsetList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['tagsetList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['val']):
        $this->_foreach['tagsetList']['iteration']++;
?>
            &nbsp;<?php echo $this->_tpl_vars['val']['name']; ?>
<?php if (! ($this->_foreach['tagsetList']['iteration'] == $this->_foreach['tagsetList']['total'])): ?>,<?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
    <?php endif; ?>
<?php endif; ?>
</div>
<div class="clear"></div> 
</div>
<?php endforeach; endif; unset($_from); ?>