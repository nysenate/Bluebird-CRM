<?php /* Smarty version 2.6.26, created on 2010-08-20 15:28:11
         compiled from CRM/Admin/Page/Navigation.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Page/Navigation.tpl', 30, false),array('function', 'help', 'CRM/Admin/Page/Navigation.tpl', 30, false),array('function', 'crmURL', 'CRM/Admin/Page/Navigation.tpl', 35, false),array('function', 'crmKey', 'CRM/Admin/Page/Navigation.tpl', 52, false),)), $this); ?>
<?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2 || $this->_tpl_vars['action'] == 8): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Admin/Form/Navigation.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
    <div id="help">
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Customize the CiviCRM navigation menu bar for your users here.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => "id-navigation"), $this);?>

    </div>

<div class="crm-block crm-content-block">
    <div id="new-menu-item">
        <a href="<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/admin/menu",'q' => "action=add&reset=1"), $this);?>
" class="button" style="margin-left: 6px;"><span>&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>New Menu Item<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>&nbsp;&nbsp;&nbsp;&nbsp;
        <span id="reset-menu" class="success-status" style="display:none">
        <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/menu','q' => "reset=1"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('rebuildURL', ob_get_contents());ob_end_clean(); ?>
        <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['rebuildURL'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><a href='%1' title="Reload page"><strong>Click here</strong></a> to reload the page and see your changes in the menu bar above.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </span><br/><br/>
    </div>
    <div class="spacer"></div>
    <div id="navigation-tree" class="navigation-tree" style="height:auto; border-collapse:separate;"></div>
    <div class="spacer"></div>
</div>
    <?php echo '
    <script type="text/javascript">
    cj(function () {
        cj("#navigation-tree").tree({
            data  : {
                type  : "json",
                async : true, 
                url : '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/menu','h' => 0), $this);?>
?key=<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/menu'), $this);?>
"<?php echo '
            },
            rules : {
                droppable : [ "tree-drop" ],
                multiple : true,
                deletable : "all",
                draggable : "all"
            },
            ui : {
                context	: 
                [ 
                    { 
                        id		: "edit",
                        label	: "Edit", 
                        icon	: "create.png",
                        visible	: function (node, treeObject) { if(node.length != 1) return false; return treeObject.check("renameable", node); }, 
                        action	: function (node, treeObject) { 
                                    var nid = cj(node).attr(\'id\');
                                    var nodeID = nid.substr( 5 );
                                    var editURL = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/menu','h' => 0,'q' => 'action=update&reset=1&id='), $this);?>
"<?php echo ' + nodeID;
                                    location.href =  editURL;  
                                  } 
                    },
                    "separator",
                    { 
                        id		: "rename",
                        label	: "Rename", 
                        icon	: "rename.png",
                        visible	: function (node, treeObject) { if(node.length != 1) return false; return treeObject.check("renameable", node); }, 
                        action	: function (node, treeObject) { treeObject.rename(node); } 
                    },
                    "separator",
                    { 
                        id		: "delete",
                        label	: "Delete",
                        icon	: "remove.png",
                        visible	: function (node, treeObject) { var ok = true; cj.each(node, function () { if(treeObject.check("deletable", this) == false) ok = false; return false; }); return ok; }, 
                        action	: function (node, treeObject) { cj.each(node, function () { treeObject.remove(this); }); } 
                    }
                ]
            },                
            callback : {
                onmove  : function( node, reference, type ) {
                    var postURL = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/menutree','h' => 0), $this);?>
?key=<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/menutree'), $this);?>
"<?php echo ';
                    cj.get( postURL + \'&type=move&id=\' + node.id + \'&ref_id=\' + (reference === -1 ? 0 : reference.id) + \'&move_type=\' + type, 
                        function (data) {
            			    cj("#reset-menu").show( );
            		    }
            		);                		                    
                },
                onrename : function( node ) {
                    var postURL = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/menutree','h' => 0), $this);?>
?key=<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/menutree'), $this);?>
"<?php echo ';
                    cj.get( postURL + \'&type=rename&id=\' + node.id + \'&data=\' + cj( node ).children("a:visible").text(), 
                        function (data) {
            			    cj("#reset-menu").show( );
            		    }
            		);
    			},
    			beforedelete : function( node ) {
    			    var nid = cj( node ).attr("id");
    			    var menuItem = cj("#" + nid ).find("a").html();
    			    var deleteMsg = '; ?>
"Are you sure you want to delete this menu item: "<?php echo ' + menuItem + '; ?>
" ? This action can not be undone."<?php echo ';
    				return confirm( deleteMsg );
    			},
    			ondelete : function ( node ) {
                    var postURL = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/menutree','h' => 0), $this);?>
?key=<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/menutree'), $this);?>
"<?php echo ';
                    cj.get( postURL + \'&type=delete&id=\' + node.id, 
                        function (data) {
            			    cj("#reset-menu").show( );
            		    }
            		);
    			}
            }
        });
    });

    </script>
    '; ?>


<?php endif; ?>