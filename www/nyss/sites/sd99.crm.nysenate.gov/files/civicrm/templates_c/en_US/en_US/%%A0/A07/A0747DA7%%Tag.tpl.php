<?php /* Smarty version 2.6.26, created on 2010-08-19 15:37:37
         compiled from CRM/Tag/Form/Tag.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Tag/Form/Tag.tpl', 36, false),array('block', 'ts', 'CRM/Tag/Form/Tag.tpl', 107, false),array('modifier', 'print_r', 'CRM/Tag/Form/Tag.tpl', 123, false),)), $this); ?>
<style>
.hit {padding-left:10px;}
.tree li {padding-left:10px;}
#Tag .tree .collapsable .hit {background:url('<?php echo $this->_tpl_vars['config']->resourceBase; ?>
/i/menu-expanded.png') no-repeat left 8px;padding-left: 9px;cursor:pointer}
#Tag .tree .expandable .hit {background:url('<?php echo $this->_tpl_vars['config']->resourceBase; ?>
/i/menu-collapsed.png') no-repeat left 6px;padding-left: 9px;cursor:pointer}
#Tag #tagtree .highlighted {background-color:lightgrey;}
</style>
<script type="text/javascript">

options = { ajaxURL:"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest','h' => 0), $this);?>
"
       ,closetxt:'<div class="icon close-icon"></div>'
      } 
entityID=<?php echo $this->_tpl_vars['entityID']; ?>
;
entityTable='<?php echo $this->_tpl_vars['entityTable']; ?>
';
<?php echo '
function hideStatus( ) {
    cj( \'#restmsg\' ).hide( );
}
cj(document).ready(function(){initTagTree()});

function initTagTree() {
    //unobsctructive elements are there to provide the function to those not having javascript, no need for the others
    cj(".unobstructive").hide();
    cj("#tagtree").treeview({
        animated: "fast",
        collapsed: true,
        unique: true
    });
    cj("#tagtree ul input:checked").each (function(){
        cj(this).parents("li").children(".hit").addClass(\'highlighted\');
    });
    cj("#tagtree input").change(function(){
        tagid = this.id.replace("check_", "");

        //get current tags from Summary and convert to array
        var tagLabels = cj.trim( cj("#tags").text( ) );
        if ( tagLabels ) {
            var tagsArray = tagLabels.split(\',\');
        } else{
            var tagsArray = new Array();
        }

        //get current tag label
        var currentTagLabel = cj("#tagLabel_" + tagid ).text( );
        if (this.checked) {
            //civiREST (\'entity_tag\',\'add\',{entity_table:entityTable,entity_id:entityID,tag_id:tagid},image);
            cj().crmAPI (\'entity_tag\',\'add\',{entity_table:entityTable,entity_id:entityID,tag_id:tagid},options);
            // add check to tab label array
            tagsArray.push( currentTagLabel );
        } else {
            cj().crmAPI (\'entity_tag\',\'remove\',{entity_table:entityTable,entity_id:entityID,tag_id:tagid},options);
            // build array of tag labels
            tagsArray = cj.map(tagsArray, function (a) { 
                 if ( cj.trim( a ) != currentTagLabel ) {
                     return cj.trim( a );
                 }
             });
        }
		//showing count of tags in summary tab
		cj( \'.ui-tabs-nav #tab_tag a\' ).html( \'Tags <em>\' + cj("#tagtree input:checkbox:checked").length + \'</em>\');
        //update summary tab 
        tagLabels = tagsArray.join(\', \');
        cj("#tags").html( tagLabels );
        ( tagLabels ) ? cj("#tagLink,#tags").show( ) : cj("#tagLink,#tags").hide( );
    });
    
    '; ?>

    <?php if ($this->_tpl_vars['permission'] != 'edit'): ?>
    <?php echo '
        cj("#tagtree input").attr(\'disabled\', true);
    '; ?>

    <?php endif; ?>
    <?php echo '
    
};
'; ?>

</script>
<a name="#saved"></a>
<span id="restmsg" style="display:none"></span>
<div id="Tag" class="view-content">
<h3><?php if (! $this->_tpl_vars['hideContext']): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Tags<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></h3>
    <p>
    <?php if ($this->_tpl_vars['action'] == 16): ?>
        <?php if ($this->_tpl_vars['permission'] == 'edit'): ?>
            <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/tag','q' => 'action=update'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('crmURL', ob_get_contents());ob_end_clean(); ?>
            <span class="unobstructive"><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['displayName'],'2' => $this->_tpl_vars['crmURL'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Current tags for <strong>%1</strong> are highlighted. You can add or remove tags from <a href='%2'>Edit Tags</a>.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
        <?php else: ?>
            <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Current tags are highlighted.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        <?php endif; ?>
    <?php else: ?>
        <?php if (! $this->_tpl_vars['hideContext']): ?> 
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Mark or unmark the checkboxes, <span class="unobstructive">and click 'Update Tags' to modify tags.</span><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	<?php endif; ?>
    <?php endif; ?>
    </p>

<!--<pre><?php echo print_r($this->_tpl_vars['tree']); ?>
</pre>-->
    <ul id="tagtree" class="tree">
        <?php $_from = $this->_tpl_vars['tree']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['node']):
?>
        <li id="tag_<?php echo $this->_tpl_vars['id']; ?>
" class="tagset">
            <?php if (! $this->_tpl_vars['node']['children']): ?><input name="tagList[<?php echo $this->_tpl_vars['id']; ?>
]" id="check_<?php echo $this->_tpl_vars['id']; ?>
" type="checkbox" <?php if ($this->_tpl_vars['tagged'][$this->_tpl_vars['id']]): ?>checked="checked"<?php endif; ?>/><?php endif; ?>
            <?php if ($this->_tpl_vars['node']['children']): ?><input name="tagList[<?php echo $this->_tpl_vars['id']; ?>
]" id="check_<?php echo $this->_tpl_vars['id']; ?>
" type="checkbox" <?php if ($this->_tpl_vars['tagged'][$this->_tpl_vars['id']]): ?>checked="checked"<?php endif; ?>/><?php endif; ?>
            <label for="check_<?php echo $this->_tpl_vars['id']; ?>
x" <?php if ($this->_tpl_vars['node']['children']): ?><?php endif; ?> id="tagLabel_<?php echo $this->_tpl_vars['id']; ?>
"><?php echo $this->_tpl_vars['node']['name']; ?>
</label>
            <?php if ($this->_tpl_vars['node']['children']): ?>
            <ul>
                <?php $_from = $this->_tpl_vars['node']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['subid'] => $this->_tpl_vars['subnode']):
?>
                    <li id="tag_<?php echo $this->_tpl_vars['subid']; ?>
">
                        <input id="check_<?php echo $this->_tpl_vars['subid']; ?>
" name="tagList[<?php echo $this->_tpl_vars['subid']; ?>
]" type="checkbox" <?php if ($this->_tpl_vars['tagged'][$this->_tpl_vars['subid']]): ?>checked="checked"<?php endif; ?>/>
                        <label for="check_<?php echo $this->_tpl_vars['subid']; ?>
" <?php if ($this->_tpl_vars['subnode']['children']): ?>class="hit"<?php endif; ?> id="tagLabel_<?php echo $this->_tpl_vars['subid']; ?>
"><?php echo $this->_tpl_vars['subnode']['name']; ?>
</label>
                        <?php if ($this->_tpl_vars['subnode']['children']): ?>
                        <ul>
                            <?php $_from = $this->_tpl_vars['subnode']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['subsubid'] => $this->_tpl_vars['subsubnode']):
?>
                                <li id="tag_<?php echo $this->_tpl_vars['subsubid']; ?>
">
                                    <input id="check_<?php echo $this->_tpl_vars['subsubid']; ?>
" name="tagList[<?php echo $this->_tpl_vars['subsubid']; ?>
]" type="checkbox" <?php if ($this->_tpl_vars['tagged'][$this->_tpl_vars['subsubid']]): ?>checked="checked"<?php endif; ?>/>
                                    <label for="check_<?php echo $this->_tpl_vars['subsubid']; ?>
" id="tagLabel_<?php echo $this->_tpl_vars['subsubid']; ?>
"><?php echo $this->_tpl_vars['subsubnode']['name']; ?>
</label>
                                    
                                                                        <?php if ($this->_tpl_vars['subsubnode']['children']): ?>
                        			<ul>
                            			<?php $_from = $this->_tpl_vars['subsubnode']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['subsubsubid'] => $this->_tpl_vars['subsubsubnode']):
?>
                                		<li id="tag_<?php echo $this->_tpl_vars['subsubsubid']; ?>
">value
                                    	<input id="check_<?php echo $this->_tpl_vars['subsubsubid']; ?>
" name="tagList[<?php echo $this->_tpl_vars['subsubsubid']; ?>
]" type="checkbox" <?php if ($this->_tpl_vars['tagged'][$this->_tpl_vars['subsubsubid']]): ?>checked="checked"<?php endif; ?>/>
                                    	<label for="check_<?php echo $this->_tpl_vars['subsubsubid']; ?>
" id="tagLabel_<?php echo $this->_tpl_vars['subsubsubid']; ?>
"><?php echo $this->_tpl_vars['subsubsubnode']['name']; ?>
</label>
                                		</li>
                            			<?php endforeach; endif; unset($_from); ?> 
                        			</ul>
                        			<?php endif; ?>
                                                                        
                                </li>
                            <?php endforeach; endif; unset($_from); ?> 
                        </ul>
                        <?php endif; ?>
                    </li>	 
                <?php endforeach; endif; unset($_from); ?> 
            </ul>
            <?php endif; ?>
        </li>	 
        <?php endforeach; endif; unset($_from); ?>
        <li>
        <div class="action-link">
    <a href="#saved" class="button" id="tag-refresh-button">
        <span>
            <div class="icon refresh-icon"></div>
            Refresh Tags
            </span>
    </a>
</div>
        </li> 
    </ul>
   
      
        <?php if ($this->_tpl_vars['permission'] == 'edit' && $this->_tpl_vars['action'] == 16): ?>
        </fieldset>
        <div class="action-link unobstructive">
          <a accesskey="N" href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/tag','q' => 'action=update'), $this);?>
" class="button"><span><div class="icon edit-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Tags<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
        </div>
    <?php else: ?>
       <div class="form-item unobstructive"><?php echo $this->_tpl_vars['form']['buttons']['html']; ?>
</div>
       </fieldset>
    <?php endif; ?>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/Tag.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>

<?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2): ?>
 <script type="text/javascript">
     var fname = "<?php echo $this->_tpl_vars['form']['formName']; ?>
";	
    on_load_init_check(fname);
 </script>
<?php endif; ?>