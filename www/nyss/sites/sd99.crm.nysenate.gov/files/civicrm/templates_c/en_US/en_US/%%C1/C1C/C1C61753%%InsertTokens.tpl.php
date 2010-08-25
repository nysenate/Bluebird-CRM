<?php /* Smarty version 2.6.26, created on 2010-08-20 12:04:03
         compiled from CRM/Mailing/Form/InsertTokens.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Mailing/Form/InsertTokens.tpl', 110, false),array('block', 'ts', 'CRM/Mailing/Form/InsertTokens.tpl', 284, false),)), $this); ?>
<?php echo '
<script type="text/javascript" >
var text_message = null;
var html_message = null;
var isMailing    = false;

'; ?>

<?php if ($this->_tpl_vars['form']['formName'] == 'MessageTemplates'): ?>
    <?php echo '
    text_message = "msg_text";
    html_message = "msg_html";
    '; ?>

<?php else: ?>
    <?php echo '
    text_message = "text_message";
    html_message = "html_message";
    isMailing    = true;
    '; ?>

<?php endif; ?>

<?php if ($this->_tpl_vars['templateSelected']): ?>
    <?php echo '
    if ( document.getElementsByName("saveTemplate")[0].checked ) {
        document.getElementById(\'template\').selectedIndex = '; ?>
<?php echo $this->_tpl_vars['templateSelected']; ?>
<?php echo ';  	
    }
    '; ?>

<?php endif; ?>
<?php echo '

var editor = '; ?>
"<?php echo $this->_tpl_vars['editor']; ?>
"<?php echo ';
function loadEditor()
{
    var msg =  '; ?>
"<?php echo $this->_tpl_vars['htmlContent']; ?>
"<?php echo ';
    if (msg) {
        if ( editor == "ckeditor" ) {
            oEditor = CKEDITOR.instances[html_message];
            oEditor.setData( msg );
        }
    }
}

function showSaveUpdateChkBox()
{
    if ( document.getElementById(\'template\') == null ) {
        if (document.getElementsByName("saveTemplate")[0].checked){
            document.getElementById("saveDetails").style.display = "block";
            document.getElementById("editMessageDetails").style.display = "block";
        } else {
            document.getElementById("saveDetails").style.display = "none";
            document.getElementById("editMessageDetails").style.display = "none";
        }
        return;
    }

    if ( document.getElementsByName("saveTemplate")[0].checked && document.getElementsByName("updateTemplate")[0].checked == false  ) {
        document.getElementById("updateDetails").style.display = "none";
    } else if ( document.getElementsByName("saveTemplate")[0].checked && document.getElementsByName("updateTemplate")[0].checked ){
        document.getElementById("editMessageDetails").style.display = "block";	
        document.getElementById("saveDetails").style.display = "block";	
    } else if ( document.getElementsByName("saveTemplate")[0].checked == false && document.getElementsByName("updateTemplate")[0].checked ){
        document.getElementById("saveDetails").style.display = "none";
        document.getElementById("editMessageDetails").style.display = "block";
    } else {
        document.getElementById("saveDetails").style.display = "none";
        document.getElementById("editMessageDetails").style.display = "none";
    }

}

function selectValue( val ) {
    if ( !val ) {
        document.getElementById(text_message).value ="";
        document.getElementById("subject").value ="";
        if ( editor == "ckeditor" ) {
            oEditor = CKEDITOR.instances[html_message];
            oEditor.setData(\'\');
        } else if ( editor == "tinymce" ) {
            tinyMCE.getInstanceById(html_message).setContent( html_body );
        } else {	
            document.getElementById(html_message).value = \'\' ;
        }
        return;
    }

    var dataUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/template','h' => 0), $this);?>
"<?php echo ';

    cj.post( dataUrl, {tid: val}, function( data ) {
        cj("#subject").val( data.subject );

        if ( data.msg_text ) {      
            cj("#"+text_message).val( data.msg_text );
            cj("div.text").show();
            cj(".head").find(\'span\').removeClass().addClass(\'ui-icon ui-icon-triangle-1-s\');
            cj("#helptext").show(); 
        } else {
            cj("#"+text_message).val("");
        }

        var html_body  = "";
        if (  data.msg_html ) {
            html_body = data.msg_html;
        }

        if ( editor == "ckeditor" ) {
            oEditor = CKEDITOR.instances[html_message];
            oEditor.setData( html_body );
        } else if ( editor == "tinymce" ) {
            cj(\'#\'+ html_message).tinymce().execCommand(\'mceSetContent\',false, html_body);
        } else {	
            cj("#"+ html_message).val( html_body );
        }

        }, \'json\');    
    }

 if ( isMailing ) { 
     document.getElementById("editMessageDetails").style.display = "block";
    

    function verify( select )
    {
        if ( document.getElementsByName("saveTemplate")[0].checked  == false ) {
            document.getElementById("saveDetails").style.display = "none";
        }
        document.getElementById("editMessageDetails").style.display = "block";

        var templateExists = true;
        if ( document.getElementById(\'template\') == null ) {
            templateExists = false;
        }

        if ( templateExists && document.getElementById(\'template\').value ) {
            document.getElementById("updateDetails").style.display = \'\';
        } else {
            document.getElementById("updateDetails").style.display = \'none\';
        }

        document.getElementById("saveTemplateName").disabled = false;
    }

    function showSaveDetails(chkbox) 
    {
        if (chkbox.checked) {
            document.getElementById("saveDetails").style.display = "block";
            document.getElementById("saveTemplateName").disabled = false;
        } else {
            document.getElementById("saveDetails").style.display = "none";
            document.getElementById("saveTemplateName").disabled = true;
        }
    }

    showSaveUpdateChkBox();

    '; ?>

    <?php if ($this->_tpl_vars['editor'] == 'ckeditor'): ?>
        <?php echo '
        cj( function() {
            oEditor = CKEDITOR.instances[html_message];
            oEditor.setData( '; ?>
'<?php echo $this->_tpl_vars['message_html']; ?>
'<?php echo ');
            oEditor.BaseHref = \'\' ;
            oEditor.UserFilesPath = \'\' ; 
            loadEditor();
	        oEditor.on( \'focus\', verify );
        });
        '; ?>

    <?php else: ?>
        <?php echo '
        cj( function( ) {
            cj("#"+ html_message).keypress( function( ) {
               if ( isMailing ) { 
                    verify();
               }
               
            });
        });
        '; ?>

    <?php endif; ?>
    <?php echo '
 }

    function tokenReplText ( element )
    {
        var token     = cj("#"+element.id).val( )[0];
        if ( element.id == \'token3\' ) {
           ( isMailing ) ? text_message = "subject" : text_message = "msg_subject"; 
        }else {
           ( isMailing ) ? text_message = "text_message" : text_message = "msg_text";
        }          
        var msg       = cj("#"+ text_message).val( );
        var cursorlen = document.getElementById(text_message).selectionStart;
        var textlen   = msg.length;
        document.getElementById(text_message).value = msg.substring(0, cursorlen) + token + msg.substring(cursorlen, textlen);
        var cursorPos = (cursorlen + token.length);
        document.getElementById(text_message).selectionStart = cursorPos;
        document.getElementById(text_message).selectionEnd   = cursorPos;
        document.getElementById(text_message).focus();
        if ( isMailing ) { 
             verify();
        }
    }

    function tokenReplHtml ( )
    {
        var token2     = cj("#token2").val( )[0];
        var editor     = '; ?>
"<?php echo $this->_tpl_vars['editor']; ?>
"<?php echo ';
        if ( editor == "tinymce" ) {
            cj(\'#\'+ html_message).tinymce().execCommand(\'mceInsertContent\',false, token2);
        } else if ( editor == "ckeditor" ) {
            oEditor = CKEDITOR.instances[html_message];
            oEditor.insertHtml(token2.toString() );
        } else {
            var msg       = document.getElementById(html_message).value;
            var cursorlen = document.getElementById(html_message).selectionStart;
            var textlen   = msg.length;
            document.getElementById(html_message).value = msg.substring(0, cursorlen) + token2 + msg.substring(cursorlen, textlen);
            var cursorPos = (cursorlen + token2.length);
            document.getElementById(html_message).selectionStart = cursorPos;
            document.getElementById(html_message).selectionEnd   = cursorPos;
            document.getElementById(html_message).focus();
        }

        if ( isMailing ) { 
             verify();
        }
    }

    cj(function() {
        cj(\'.accordion .head\').addClass( "ui-accordion-header ui-helper-reset ui-state-default ui-corner-all ");

        // restructuring css as per jQuery tab width
        cj(\'.ui-state-default, .ui-widget-content .ui-state-default\').css( \'width\', \'95%\' );
        cj(\'.resizable-textarea textarea\').css( \'width\', \'99%\' );
        cj(\'.grippie\').css( \'margin-right\', \'3px\');
        cj(\'.accordion .head\').hover( function() { cj(this).addClass( "ui-state-hover");
        }, function() { cj(this).removeClass( "ui-state-hover");
    }).bind(\'click\', function() { 
        var checkClass = cj(this).find(\'span\').attr( \'class\' );
        var len        = checkClass.length;
        if ( checkClass.substring( len - 1, len ) == \'s\' ) {
            cj(this).find(\'span\').removeClass().addClass(\'ui-icon ui-icon-triangle-1-e\');
            cj("span#help"+cj(this).find(\'span\').attr(\'id\')).hide();
        } else {
            cj(this).find(\'span\').removeClass().addClass(\'ui-icon ui-icon-triangle-1-s\');
            cj("span#help"+cj(this).find(\'span\').attr(\'id\')).show();
        }
        cj(this).next().toggle(); return false; }).next().hide();
        cj(\'span#html\').removeClass().addClass(\'ui-icon ui-icon-triangle-1-s\');
        cj("div.html").show();
       
        if ( !isMailing ) {
           cj("div.text").show();
        }   
    });

    '; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/Filter.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '
    function showToken(element, id ) {
	initFilter(id);
	cj("#token"+id).css({"width":"290px", "size":"8"});
	var tokenTitle = '; ?>
'<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select Token<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>'<?php echo ';
        cj("#token"+element ).show( ).dialog({
            title       : tokenTitle,
            modal       : true,
            width       : \'310px\',
            resizable   : false,
            bgiframe    : false,
            overlay     : { opacity: 0.5, background: "black" },
            beforeclose : function(event, ui) { cj(this).dialog("destroy"); },
            buttons     : { 
                "Done": function() { 
                    cj(this).dialog("close");
                        //focus on editor/textarea after token selection     
                        if (element == \'Text\') {
                            cj(\'#\' + text_message).focus();
                        } else if (element == \'Html\' ) {
                            switch ('; ?>
"<?php echo $this->_tpl_vars['editor']; ?>
"<?php echo ') {
                                case \'ckeditor\': { oEditor = CKEDITOR.instances[html_message]; oEditor.focus(); break;}
                                case \'tinymce\'  : { tinyMCE.get(html_message).focus(); break; } 
                                default         : { cj("#"+ html_message).focus(); break; } 
                        }
                    } else if (element == \'Subject\') {
                           var subject = null;
                           ( isMailing ) ? subject = "subject" : subject = "msg_subject";
                           cj(\'#\'+subject).focus();       
                    }
                }
            }
        });
        return false;
    }

    cj(function() {
        if ( !cj().find(\'div.crm-error\').text() ) {            
            setSignature( );
        }

        cj("#fromEmailAddress").change( function( ) {
            setSignature( );
        });
    });
    function setSignature( ) {
        var emailID = cj("#fromEmailAddress").val( );
        if ( !isNaN( emailID ) ) {
            var dataUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/signature','h' => 0), $this);?>
"<?php echo ';
            cj.post( dataUrl, {emailID: emailID}, function( data ) {
                var editor     = '; ?>
"<?php echo $this->_tpl_vars['editor']; ?>
"<?php echo ';
                
                if ( data.signature_text ) {
                    // get existing text & html and append signatue
                    var textMessage =  cj("#"+ text_message).val( ) + \'\\n\\n--\\n\' + data.signature_text;

                    // append signature
                    cj("#"+ text_message).val( textMessage ); 
                }
                
                if ( data.signature_html ) {
                    var htmlMessage =  cj("#"+ html_message).val( ) + \'<br/><br/>--<br/>\' + data.signature_html;

                    // set wysiwg editor
                    if ( editor == "ckeditor" ) {
                        oEditor = CKEDITOR.instances[html_message];
                        var htmlMessage = oEditor.getData( ) + \'<br/><br/>--\' + data.signature_html;
                        oEditor.setData( htmlMessage  );
                    } else if ( editor == "tinymce" ) {
                        cj(\'#\'+ html_message).tinymce().execCommand(\'mceSetContent\',false, htmlMessage );
                    } else {	
                        cj("#"+ html_message).val( htmlMessage );
                    }
                }

            }, \'json\'); 
        } 
    }
</script>
'; ?>