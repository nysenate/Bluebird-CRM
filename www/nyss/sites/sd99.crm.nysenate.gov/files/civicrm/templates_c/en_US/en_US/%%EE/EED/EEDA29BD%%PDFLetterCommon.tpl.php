<?php /* Smarty version 2.6.26, created on 2010-08-23 11:56:18
         compiled from CRM/Contact/Form/Task/PDFLetterCommon.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'help', 'CRM/Contact/Form/Task/PDFLetterCommon.tpl', 41, false),array('function', 'crmURL', 'CRM/Contact/Form/Task/PDFLetterCommon.tpl', 148, false),array('block', 'ts', 'CRM/Contact/Form/Task/PDFLetterCommon.tpl', 44, false),array('modifier', 'crmReplace', 'CRM/Contact/Form/Task/PDFLetterCommon.tpl', 74, false),)), $this); ?>
<table class="form-layout-compressed">
    <tr>
        <td class="label-left"><?php echo $this->_tpl_vars['form']['template']['label']; ?>
</td>
	    <td><?php echo $this->_tpl_vars['form']['template']['html']; ?>
</td>
    </tr>
</table>

<div class="crm-accordion-wrapper crm-html_email-accordion crm-accordion-open">
    <div class="crm-accordion-header">
        <?php echo $this->_tpl_vars['form']['html_message']['label']; ?>

    </div>
    <div class="crm-accordion-body">
        <span class="helpIcon" id="helphtml">
		<a href="#" onClick="return showToken('Html', 1);"><?php echo $this->_tpl_vars['form']['token1']['label']; ?>
</a> 
		<?php echo smarty_function_help(array('id' => "id-token-html",'file' => "CRM/Contact/Form/Task/Email.hlp"), $this);?>

		<div id='tokenHtml' style="display:none">
		    <input style="border:1px solid #999999;" type="text" id="filter1" size="20" name="filter1" onkeyup="filter(this, 1)"/><br />
		    <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Begin typing to filter list of tokens<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span><br/>
		    <?php echo $this->_tpl_vars['form']['token1']['html']; ?>

		</div>
	    </span>
	    <div class="clear"></div>
        <div class='html'>
        <?php if ($this->_tpl_vars['editor'] == 'textarea'): ?>
            <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>If you are composing HTML-formatted messages, you may want to enable a WYSIWYG editor (Administer CiviCRM &raquo; Global Settings &raquo; Site Preferences).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span><br />
            <?php endif; ?>
            <?php echo $this->_tpl_vars['form']['html_message']['html']; ?>
<br />
        </div>

<?php if (! $this->_tpl_vars['noAttach']): ?>
    <div class="spacer"></div>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Form/attachment.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<div class="spacer"></div>

<div id="editMessageDetails">
    <div id="updateDetails" >
        <?php echo $this->_tpl_vars['form']['updateTemplate']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['updateTemplate']['label']; ?>

    </div>
    <div>
        <?php echo $this->_tpl_vars['form']['saveTemplate']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['saveTemplate']['label']; ?>

    </div>
</div>

<div id="saveDetails" class="section">
    <div class="label"><?php echo $this->_tpl_vars['form']['saveTemplateName']['label']; ?>
</div>
    <div class="content"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['saveTemplateName']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</div>
</div>

    </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

<?php echo '
<script type="text/javascript" >
'; ?>

<?php if ($this->_tpl_vars['templateSelected']): ?>
    <?php echo '
    if ( document.getElementsByName("saveTemplate")[0].checked ) {
	document.getElementById(\'template\').selectedIndex = "'; ?>
<?php echo $this->_tpl_vars['templateSelected']; ?>
<?php echo '";  	
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
            oEditor = CKEDITOR.instances[\'html_message\'];
            oEditor.setData( msg );
        } else if ( editor == "tinymce" ) {
            tinyMCE.get(\'html_message\').setContent( msg );
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
        document.getElementById("text_message").value ="";
        document.getElementById("subject").value ="";
        if ( editor == "ckeditor" ) {
            oEditor = CKEDITOR.instances[\'html_message\'];
            oEditor.setData(\'\');
        } else if ( editor == "tinymce" ) {
            tinyMCE.get(\'html_message\').setContent(\'\');
        } else {	
            document.getElementById("html_message").value = \'\' ;
        }
        return;
    }

    var dataUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/template','h' => 0), $this);?>
"<?php echo ';

    cj.post( dataUrl, {tid: val}, function( data ) {
        cj("#subject").val( data.subject );

        if ( data.msg_text ) {      
            cj("#text_message").val( data.msg_text );
        } else {
            cj("#text_message").val("");
        }

        var html_body  = "";
        if (  data.msg_html ) {
           html_body = data.msg_html;
        }

        if ( editor == "ckeditor" ) {
           oEditor = CKEDITOR.instances[\'html_message\'];
           oEditor.setData(html_body);
        } else if ( editor == "tinymce" ) {
            tinyMCE.get(\'html_message\').setContent( html_body );
        } else {	
            cj("#html_message").val( html_body );
        }
       
    }, \'json\');    
}

 
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

function tokenReplHtml ( )
{
    var token1 = cj("#token1").val( )[0];
    var editor = '; ?>
"<?php echo $this->_tpl_vars['editor']; ?>
"<?php echo ';
    if ( editor == "tinymce" ) {
        var content= tinyMCE.get(\'html_message\').getContent() +token1;
        tinyMCE.get(\'html_message\').setContent(content);
    } else if ( editor == "ckeditor" ) {
           oEditor = CKEDITOR.instances[\'html_message\'];
           oEditor.insertHtml(token1);        
    } else {
        document.getElementById("html_message").value =  document.getElementById("html_message").value + token1;
    }
    verify();
}
'; ?>

<?php if ($this->_tpl_vars['editor'] == 'ckeditor'): ?>
<?php echo '
	function CKeditor_OnComplete( editorInstance )
	{
        oEditor = CKEDITOR.instances[\'html_message\'];
		oEditor.setData( '; ?>
'<?php echo $this->_tpl_vars['message_html']; ?>
'<?php echo ');
		loadEditor();	
		oEditor.on( \'onFocus\', verify );
    }

'; ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['editor'] == 'tinymce'): ?>
<?php echo '
	function customEvent() {
		loadEditor();
		tinyMCE.get(\'html_message\').onKeyPress.add(function(ed, e) {
 		verify();
		});
	}

tinyMCE.init({
	oninit : "customEvent"
});

'; ?>

<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/Filter.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php echo '
    function showToken(element, id) {
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
			    cj(\'#text_message\').focus();
			} else if (element == \'Html\' ) {
			    switch ('; ?>
"<?php echo $this->_tpl_vars['editor']; ?>
"<?php echo ') {
				case \'ckeditor\': { CKEDITOR.instances[\'html_message\'].focus(); break;}
				case \'tinymce\'  : { tinyMCE.get(\'html_message\').focus(); break; } 
				default         : { cj("#html_message").focus(); break; } 
			}
		    }
		}
	    }
	});
	return false;
    }
</script>
'; ?>
