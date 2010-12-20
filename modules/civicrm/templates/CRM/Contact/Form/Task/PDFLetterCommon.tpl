{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{*common template for compose PDF letters*}
{if $form.template.html}
<table class="form-layout-compressed">
    <tr>
        <td class="label-left">{$form.template.label}</td>
	    <td>{$form.template.html}</td>
    </tr>
</table>
{/if}

<div class="crm-accordion-wrapper crm-html_email-accordion crm-accordion-open">
    <div class="crm-accordion-header">
        {$form.html_message.label}
    </div>
    <div class="crm-accordion-body">
    {if $action neq 4}
        <span class="helpIcon" id="helphtml">
		<a href="#" onClick="return showToken('Html', 1);">{$form.token1.label}</a> 
		{help id="id-token-html" file="CRM/Contact/Form/Task/Email.hlp"}
		<div id='tokenHtml' style="display:none">
		    <input style="border:1px solid #999999;" type="text" id="filter1" size="20" name="filter1" onkeyup="filter(this, 1)"/><br />
		    <span class="description">{ts}Begin typing to filter list of tokens{/ts}</span><br/>
		    {$form.token1.html}
		</div>
	    </span>
	    {/if}
	    <div class="clear"></div>
        <div class='html'>
        {if $editor EQ 'textarea'}
            <span class="description">{ts}If you are composing HTML-formatted messages, you may want to enable a WYSIWYG editor (Administer CiviCRM &raquo; Global Settings &raquo; Site Preferences).{/ts}</span><br />
            {/if}
            {$form.html_message.html}<br />
        </div>

{if ! $noAttach}
    <div class="spacer"></div>
    {include file="CRM/Form/attachment.tpl"}
{/if}

<div class="spacer"></div>

<div id="editMessageDetails">
    <div id="updateDetails" >
        {$form.updateTemplate.html}&nbsp;{$form.updateTemplate.label}
    </div>
    <div>
        {$form.saveTemplate.html}&nbsp;{$form.saveTemplate.label}
    </div>
</div>

<div id="saveDetails" class="section">
    <div class="label">{$form.saveTemplateName.label}</div>
    <div class="content">{$form.saveTemplateName.html|crmReplace:class:huge}</div>
</div>

    </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{literal}
<script type="text/javascript" >
{/literal}
{if $templateSelected}
    {literal}
    if ( document.getElementsByName("saveTemplate")[0].checked ) {
	document.getElementById('template').selectedIndex = "{/literal}{$templateSelected}{literal}";  	
    }
    {/literal}
{/if}
{literal}
var editor = {/literal}"{$editor}"{literal};
function loadEditor()
{
    var msg =  {/literal}"{$htmlContent}"{literal};
    if (msg) {
        if ( editor == "ckeditor" ) {
            oEditor = CKEDITOR.instances['html_message'];
            oEditor.setData( msg );
        } else if ( editor == "tinymce" ) {
            tinyMCE.get('html_message').setContent( msg );
        }
    }
}

function showSaveUpdateChkBox()
{
    if ( document.getElementById('template') == null ) {
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
            oEditor = CKEDITOR.instances['html_message'];
            oEditor.setData('');
        } else if ( editor == "tinymce" ) {
            tinyMCE.get('html_message').setContent('');
        } else {	
            document.getElementById("html_message").value = '' ;
        }
        return;
    }

    var dataUrl = {/literal}"{crmURL p='civicrm/ajax/template' h=0 }"{literal};

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
           oEditor = CKEDITOR.instances['html_message'];
           oEditor.setData(html_body);
        } else if ( editor == "tinymce" ) {
            tinyMCE.get('html_message').setContent( html_body );
        } else {	
            cj("#html_message").val( html_body );
        }
       
    }, 'json');    
}

 
document.getElementById("editMessageDetails").style.display = "block";

function verify( select )
{
    if ( document.getElementsByName("saveTemplate")[0].checked  == false ) {
        document.getElementById("saveDetails").style.display = "none";
    }
    document.getElementById("editMessageDetails").style.display = "block";

    var templateExists = true;
    if ( document.getElementById('template') == null ) {
        templateExists = false;
    }

    if ( templateExists && document.getElementById('template').value ) {
        document.getElementById("updateDetails").style.display = '';
    } else {
        document.getElementById("updateDetails").style.display = 'none';
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
    var editor = {/literal}"{$editor}"{literal};
    if ( editor == "tinymce" ) {
        var content= tinyMCE.get('html_message').getContent() +token1;
        tinyMCE.get('html_message').setContent(content);
    } else if ( editor == "ckeditor" ) {
           oEditor = CKEDITOR.instances['html_message'];
           oEditor.insertHtml(token1);        
    } else {
        document.getElementById("html_message").value =  document.getElementById("html_message").value + token1;
    }
    verify();
}
{/literal}
{if $editor eq "ckeditor"}
{literal}
	function CKeditor_OnComplete( editorInstance )
	{
        oEditor = CKEDITOR.instances['html_message'];
		oEditor.setData( {/literal}'{$message_html}'{literal});
		loadEditor();	
    }
    cj( function() {
     	 oEditor = CKEDITOR.instances['html_message'];
	 oEditor.on( 'focus', verify );
     });
{/literal}
{/if}
{if $editor eq "tinymce"}
{literal}
	function customEvent() {
		loadEditor();
	}

tinyMCE.init({
	oninit : "customEvent"
});

cj( function() {
  cj('div.html').hover( 
  function( ) {
    if ( cj('#html_message').tinymce() ) {
      tinyMCE.get('html_message').onKeyPress.add(function(ed, e) {
        verify( );
      });
    }
  },
  function( ) {
   if ( cj('#html_message').tinymce() ) {
     if ( tinyMCE.get('html_message').getContent() ) {
       verify( );
     } 
   }
  }	
  );
});
{/literal}
{/if}
{include file="CRM/common/Filter.tpl"}
{literal}
    function showToken(element, id) {
	initFilter(id);
	cj("#token"+id).css({"width":"290px", "size":"8"});
	var tokenTitle = {/literal}'{ts}Select Token{/ts}'{literal};
	cj("#token"+element ).show( ).dialog({
	    title       : tokenTitle,
	    modal       : true,
	    width       : '310px',
	    resizable   : false,
	    bgiframe    : false,
	    overlay     : { opacity: 0.5, background: "black" },
	    beforeclose : function(event, ui) { cj(this).dialog("destroy"); },
	    buttons     : { 
		"Done": function() { 
		    cj(this).dialog("close");

			//focus on editor/textarea after token selection     
			if (element == 'Text') {
			    cj('#text_message').focus();
			} else if (element == 'Html' ) {
			    switch ({/literal}"{$editor}"{literal}) {
				case 'ckeditor': { CKEDITOR.instances['html_message'].focus(); break;}
				case 'tinymce'  : { tinyMCE.get('html_message').focus(); break; } 
				default         : { cj("#html_message").focus(); break; } 
			}
		    }
		}
	    }
	});
	return false;
    }
</script>
{/literal}
