{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
{* tpl for building Individual related fields *}
<script type="text/javascript">
var cid=parseFloat("{$contactId}");//parseInt is octal by default
var contactIndividual = "{crmURL p='civicrm/ajax/rest?fnName=civicrm/contact/search&json=1&contact_type=Individual&return[display_name]&return[sort_name]=1&return[email]=1&rowCount=50'}";
var viewIndividual = "{crmURL p='civicrm/contact/view?reset=1&cid='}";
var editIndividual = "{crmURL p='contact/add?reset=1&action=update&cid='}";
var checkSimilar =  {$checkSimilar};
{literal}

  jQuery(function($) {

     if ($('#contact_sub_type *').length ==1) {//if they aren't any subtype we don't offer the option
        $('#contact_sub_type').parent().hide();
     }

     if (!isNaN(cid) || ! checkSimilar)
       return;//no dupe check if this is a modif or if checkSimilar is disabled (CIVICRM_CONTACT_AJAX_CHECK_SIMILAR in civicrm_setting)

	     $('#last_name').blur(function () {
         $('#lastname_msg').remove();
             if (this.value =='') return;
	     $.getJSON(contactIndividual,{sort_name:$('#last_name').val()},
         function(data){
           if (data.is_error== 0) {
             return;
           }
           var msg="<tr id='lastname_msg'><td colspan='5'><div class='messages status'><div class='icon inform-icon'></div>";
           //$('#lastname_msg').remove();
           if (data.length ==1) {
             msg = msg + "{/literal}{ts}There is a contact with a similar last name. If the person you were trying to add is listed below, click on their name to view or edit their record{/ts}{literal}";  
           } else {
             // ideally, should use a merge with data.length
             msg = msg + "{/literal}{ts}There are contacts with a similar last name. If the person you were trying to add is listed below, click on their name to view or edit their record{/ts}{literal}";
           }
           msg = msg+ '<table class="matching-contacts-actions">';
           $.each(data, function(i,contact){
             msg = msg + '<tr><td><a href="'+viewIndividual+contact.contact_id+'">'+ contact.display_name +'</a></td><td>'+contact.email+'</td><td class="action-items"><a class="action-item action-item-first" href="'+viewIndividual+contact.contact_id+'">{/literal}{ts}View{/ts}{literal}</a>&nbsp;<a class="action-item" href="'+editIndividual+contact.contact_id+'">{/literal}{ts}Edit{/ts}{literal}</a>&nbsp;</td></tr>';
           });
           msg = msg+ '</table>';
           $('#last_name').parent().parent().after(msg+'</div><td></tr>');
           $('#lastname_msg a').click(function(){global_formNavigate =true; return true;});// No confirmation dialog on click
         });
	    });
  });
</script>
{/literal}

{assign var=formtextbig value='form-text big'}
<table class="form-layout-compressed individual-contact-details">
  <tr>
    <td>
        {if $form.prefix_id}
        	{$form.prefix_id.label}<br/>
            {$form.prefix_id.html}
        {/if}
    </td>
    <td>
        {$form.first_name.label}<br /> 
        {if $action == 2}
            {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact' field='first_name' id=$contactId}
        {/if}
        {$form.first_name.html}
    </td>
    <td>
        {$form.middle_name.label}<br />
        {if $action == 2}
            {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact' field='middle_name' id=$contactId}
        {/if}
        {$form.middle_name.html}
    </td>
    <td>
        {$form.last_name.label}<br />
        {if $action == 2}
            {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact' field='last_name' id=$contactId}
        {/if}
        {$form.last_name.html}
    </td>
    <td>
		{if $form.suffix_id}
        	{$form.suffix_id.label}<br/>
        	{$form.suffix_id.html}
        {/if}
	</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
	<td>
		{$form.nick_name.label}<br />
		{$form.nick_name.html|crmReplace:class:$formtextbig}
	</td>
	<td>
		{if $contactId}{assign var='custom_42' value=custom_42_`$contactId`}
        {else}{assign var='custom_42' value='custom_42_-1'}{/if}
        {$form.$custom_42.label}<br />
		{$form.$custom_42.html}                    
	</td>
	<td>
		{if $contactId}{assign var='custom_60' value=custom_60_`$contactId`}
        {else}{assign var='custom_60' value='custom_60_-1'}{/if}
        {$form.$custom_60.label}<br />
		{$form.$custom_60.html}                    
	</td>
	<td>
		Other {$form.contact_source.label}<br />
		{$form.contact_source.html|crmReplace:class:$formtextbig}
	</td>
    <td>
		{$form.external_identifier.label}<br />
		{$form.external_identifier.value}
    </td>
    <td>
		<label for="internal_identifier">{ts}Internal Id{/ts}</label><br />
		{$contactId}
    </td>
  </tr>
    
</table>
