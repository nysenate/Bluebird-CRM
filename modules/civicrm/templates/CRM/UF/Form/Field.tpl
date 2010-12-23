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
<h3>{if $action eq 8}{ts}Delete Field{/ts} - {$fieldTitle}{elseif $action eq 1}{ts}Add Field{/ts}{elseif $action eq 2}{ts}Edit Field{/ts} - {$fieldTitle}{/if}</h3>
<div class="crm-block crm-form-block crm-uf-field-form-block">
    {if $action eq 8}
      	<div class="messages status">
          <div class="icon inform-icon"></div>
            {ts}WARNING: Deleting this profile field will remove it from Profile forms and listings. If this field is used in any 'stand-alone' Profile forms, you will need to update those forms to remove this field.{/ts} {ts}Do you want to continue?{/ts}
      </div>
    {else}
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div> 
        <table class="form-layout-compressed">
        <tr class="crm-uf-field-form-block-field_name">
            <td class="label">{$form.field_name.label}</td>
            <td>{$form.field_name.html}<br />
            <span class="description">&nbsp;{ts}Select the type of CiviCRM record and the field you want to include in this Profile.{/ts}</span></td>
        </tr> 
        <tr class="crm-uf-field-form-block-label">
            <td class="label">{$form.label.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_uf_field' field='label' id=$fieldId}{/if}</td>
            <td>{$form.label.html}<br />       
            <span class="description">{ts}The field label displayed on the form (over-ride the default field label here, if desired).{/ts}</span></td>
        </tr>
        <tr class="crm-uf-field-form-block-is_required">
            <td class="label">{$form.is_required.label}</td>
            <td>{$form.is_required.html}<br />
            <span class="description">{ts}Are users required to complete this field?{/ts}</span></td>
        </tr>
        <tr class="crm-uf-field-form-block-is_view">
            <td class="label">{$form.is_view.label}</td>
            <td>{$form.is_view.html}<br />
            <span class="description">&nbsp;{ts}If checked, users can view but not edit this field.{/ts}<br />&nbsp;{ts}NOTE: View Only fields can not be included in Profile Search forms.{/ts}</span></td>
        </tr>
        <tr class="crm-uf-field-form-block-visibility">
            <td class="label">{$form.visibility.label}</td>
            <td>{$form.visibility.html}<br />
            <span class="description">&nbsp;{ts}Is this field hidden from other users ('User and User Admin Only'), or is it visible to others and potentially searchable in the Profile Search form ('Public Pages' or 'Public Pages and Listings')? When visibility is 'Public Pages and Listings', users can also click the field value when viewing a contact in order to locate other contacts with the same value(s) (i.e. other contacts who live in Poland).{/ts}</span></td>
        </tr>                                                     
        <tr class="crm-uf-field-form-block-is_searchable">
            <td class="label"><div id="is_search_label">{$form.is_searchable.label}</div></td>
            <td><div id="is_search_html">{$form.is_searchable.html}<br />
            <span class="description" id="is_search_desSpan">{ts}Do you want to include this field in the Profile's Search form?{/ts}</span></div></td>
        </tr>
        <tr class="crm-uf-field-form-block-in_selector">
            <td class="label"><div id="in_selector_label">{$form.in_selector.label}</div></td>
            <td><div id="in_selector_html">{$form.in_selector.html}<br />         
            <span id="in_selector_desSpan" class="description">{ts}Is this field included as a column in the search results table? This setting applies only to fields with 'Public Pages' or 'Public Pages and Listings' visibility.{/ts}</span></div></td>
        </tr>
        <tr class="crm-uf-field-form-block-help_post">
            <td class="label">{$form.help_post.label}{if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_uf_field' field='help_post' id=$fieldId}{/if}</td>
            <td>{$form.help_post.html|crmReplace:class:huge}<br /> 
            <span class="description">&nbsp;{ts}Explanatory text displayed to users for this field (can include HTML formatting tags).{/ts}</span></td>
        </tr>
        <tr class="crm-uf-field-form-block-weight"> 
            <td class="label">{$form.weight.label}</td>
            <td>&nbsp;{$form.weight.html}<br />
            <span class="description">&nbsp;{ts}Weight controls the order in which fields are displayed within a profile. Enter a positive or negative integer - lower numbers are displayed ahead of higher numbers.{/ts}</span></td>
        </tr>
        <tr class="crm-uf-field-form-block-is_active">
            <td class="label">{$form.is_active.label}</td>
            <td>{$form.is_active.html}</td>
        </tr>
        </table>
    {/if}
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>

 {$initHideBoxes}

{literal}
<script type="text/javascript">
function showLabel( ) {
    var labelValue = '';
    /* Code to set the Field Label */		
    if (document.forms.Field['field_name[0]'].options[document.forms.Field['field_name[0]'].selectedIndex].value) { 
        if ( document.forms.Field['field_name[1]'].value ) {
            labelValue = document.forms.Field['field_name[1]'].options[document.forms.Field['field_name[1]'].selectedIndex].text; 
        }

        if ( document.forms.Field['field_name[3]'].value ) { 
            labelValue = labelValue + '-' + document.forms.Field['field_name[3]'].options[document.forms.Field['field_name[3]'].selectedIndex].text + ''; 
        }   
        if ( document.forms.Field['field_name[2]'].value ) { 
            labelValue = labelValue + ' (' + document.forms.Field['field_name[2]'].options[document.forms.Field['field_name[2]'].selectedIndex].text + ')'; 
        }   
    } else {
        labelValue = '';  
    }

    var custom = document.forms.Field['field_name[1]'].value;
    if ( custom.substring( 0, 7 ) == 'custom_' ) {
        var customFieldLabel = labelValue.split(' :: ', 2);
        labelValue = customFieldLabel[0];
        if ( document.forms.Field['field_name[2]'].value ) { 
            labelValue = labelValue + ' (' + document.forms.Field['field_name[2]'].options[document.forms.Field['field_name[2]'].selectedIndex].text + ')'; 
        }
    }
    
    var input = document.getElementById('label');
    input.value = labelValue;

    /* Code to hide searchable attribute for no searchable fields */
    if (document.getElementsByName("field_name[1]")[0].selectedIndex == -1) {
        return;
    }
    var field2 = document.getElementsByName("field_name[1]")[0][document.getElementsByName("field_name[1]")[0].selectedIndex].text;
    var noSearch;
    {/literal}
    {foreach from=$noSearchable key=dnc item=val}
        {literal}noSearch = "{/literal}{$val}{literal}";
        if (field2 == noSearch) {
            hide("is_search_label");
            hide("is_search_html");
            hide("is_search_desSpan");
        }
        {/literal}
    {/foreach}
    {literal}

    /* Code to set Profile Field help, from custom data field help */
    var fieldId = null;

    if ( custom.substring( 0, 7 ) == 'custom_' ) {
        fieldId = custom.substring( custom.length, 7);
    } else {
        cj('#help_post').val(" ");
        return;
    }

    var dataUrl = {/literal}"{crmURL p='civicrm/ajax/custom' h=0 }"{literal};
    cj.post( dataUrl, { id: fieldId }, function(data) {
       cj('#help_post').val( data );
    });
} 

{/literal}{if $action neq 8}{literal}
   showHideSeletorSearch();
	
   function showHideSeletorSearch()
   {
       var vsbl= cj("#visibility").val( );
       if ( vsbl == "User and User Admin Only"){
           hide("is_search_label");
           hide("is_search_html");
           hide("is_search_desSpan");
           hide("in_selector_label");
           hide("in_selector_html");
           hide("in_selector_desSpan");
           cj("#is_searchable").attr('checked',false);
       } else {
           if ( ! cj("#is_view").attr('checked') ) {
               show("is_search_label");
               show("is_search_html");
               show("is_search_desSpan");
           }
           var fldName = cj("#field_name\\[1\\]").val();
           if ( fldName == 'group' || fldName == 'tag' ) { 
               hide("in_selector_label");
               hide("in_selector_html");
               hide("in_selector_desSpan");
           } else {
               show("in_selector_label");
               show("in_selector_html");
               show("in_selector_desSpan");
           }
       }
   }

   cj("#field_name\\[1\\]").bind( 'change blur', function( ) {
       showHideSeletorSearch( );
   }); 

cj( function( ) {
    cj("#field_name\\[1\\]").addClass( 'huge' );
    viewOnlyShowHide( );
    cj("#is_view").click( function(){
        viewOnlyShowHide();
    });
    
});
{/literal}{/if}{literal}

function viewOnlyShowHide( ) {
    var vsbl= cj("#visibility").val( );
    if (cj("#is_view").attr('checked')) {
       hide("is_search_label");
       hide("is_search_html");
       hide("is_search_desSpan");
       cj("#is_searchable").attr('checked',false);
    } else if ( vsbl != "User and User Admin Only")  {
       show("is_search_label");
       show("is_search_html");
       show("is_search_desSpan");
    }
}

//CRM-4363	
function mixProfile( ) {
    var allMixTypes = ["Participant", "Membership", "Contribution"];
    var type = document.forms.Field['field_name[0]'].value;
    var alreadyMixProfile = {/literal}{if $alreadyMixProfile}true{else}false{/if}{literal};
    if ( allMixTypes.indexOf( type ) != -1 || alreadyMixProfile ) {
        if ( document.getElementById("is_searchable").checked ) {
            document.getElementById("is_searchable").checked = false;
            if ( alreadyMixProfile ) {
                var message = {/literal}'{ts}Oops. You can not mark fields as Searchable in a profile that contains fields for multiple record types.{/ts}'{literal};
                alert( message ); 
            } else {
                var message = {/literal}{ts}'Oops. '{/ts} 
                + type + '{ts} fields can not be marked as Searchable in a profile.{/ts}'{literal};
                alert( message ); 
            }
        }
        if ( document.getElementById("in_selector").checked ) {
            document.getElementById("in_selector").checked = false;
            if ( alreadyMixProfile ) {
                var message = {/literal}'{ts}Oops. You can not mark a field as a Result Column in a profile that contains fields from multiple record types.{/ts}'{literal};
                alert( message );
            } else {
                var message = {/literal}{ts}'Oops. '{/ts} 
                + type + '{ts} can not be used as a Result Column for profile searches.{/ts}'{literal};
                alert( message ); 
            }
        }
    }
}

function verify( ) {
    var allMixTypes = ["Participant", "Membership", "Contribution"];
    var type = document.forms.Field['field_name[0]'].value;
    if ( allMixTypes.indexOf( type ) != -1 ) {
        var message = {/literal}{ts}'Oops. One or more fields in this profile are configured to be Searchable and / or shown in a Results Column, AND you are trying to add a '{/ts} 
        + type + '{ts} field. Profiles with a mixture of field types can not include Searchable or Results Column fields. If you save this field now, the Seachable and Results Column settings will be removed for all fields in this profile. Do you want to continue?{/ts}'{literal};
        var ok = confirm( message );    
        if ( !ok ) {
            return false;
        }
    }
}

</script> 
{/literal}

{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}

