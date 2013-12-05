{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
{literal}
cj(function($) {
{/literal}
var cid=parseFloat("{$contactId}");//parseInt is octal by default
var contactIndividual = "{crmURL p='civicrm/ajax/rest' q='entity=contact&action=get&json=1&contact_type=Individual&return=display_name,sort_name,email,phone,city,street_address&rowCount=50' h=0}";
var viewIndividual = "{crmURL p='civicrm/contact/view' q='reset=1&cid=' h=0}";
var editIndividual = "{crmURL p='civicrm/contact/add' q='reset=1&action=update&cid=' h=0}";
var checkSimilar =  {$checkSimilar};
  var lastnameMsg;
{literal}
  $(document).ready(function() {
     if (cj('#contact_sub_type *').length == 0) {//if they aren't any subtype we don't offer the option
        cj('#contact_sub_type').parent().hide();
     }
    if (!isNaN(cid) || ! checkSimilar) {
       return;//no dupe check if this is a modif or if checkSimilar is disabled (contact_ajax_check_similar in civicrm_setting table)
    }
	     cj('#last_name').blur(function () {
         // Close msg if it exists
         lastnameMsg && lastnameMsg.close && lastnameMsg.close();
         if (this.value =='') return;
         //NYSS 7435 alter ajax contact search
         cj.getJSON(contactIndividual,{first_name:cj('#first_name').val()+'%',last_name:cj('#last_name').val()+'%',sort:'sort_name',rowCount:'15'},
         function(data){
           if (data.is_error == 1 || data.count == 0) {
             return;
           }
          var msg = "<em>{/literal}{ts escape='js'}If the person you were trying to add is listed below, click their name to view or edit their record{/ts}{literal}:</em>";
           if ( data.count == 1 ) {
            var title = "{/literal}{ts escape='js'}Similar Contact Found{/ts}{literal}";
           } else {
            var title = "{/literal}{ts escape='js'}Similar Contacts Found{/ts}{literal}";
           }
          msg += '<ul class="matching-contacts-actions">';
           cj.each(data.values, function(i,contact){
	     if ( !(contact.email) ) {
	       contact.email = '';
	     }
             //NYSS 7435 logic to construct additional data fields
             var contactDetails = (contact.email) ? ' | '+contact.email.trim() : '';
             contactDetails += (contact.phone) ? ' | '+contact.phone.trim() : '';
             contactDetails += (contact.street_address) ? ' | '+contact.street_address.trim() : '';
             contactDetails += (contact.city) ? ' | '+contact.city.trim() : '';
          msg += '<li><a href="'+viewIndividual+contact.id+'">'+ contact.sort_name +'</a> '+contactDetails+'</li>';
           });
        msg += '</ul>';
        lastnameMsg = CRM.alert(msg, title);
        cj('.matching-contacts-actions a').click(function(){
          // No confirmation dialog on click
          global_formNavigate = true;
          return true;
        });
      });
         });
	    });
  });
</script>
{/literal}

{assign var=formtextbig value='form-text big'}{*NYSS*}
<table class="form-layout-compressed individual-contact-details">
  <tr>
        {if $form.prefix_id}
    <td>
        	{$form.prefix_id.label}<br/>
            {$form.prefix_id.html}
            </td>    
        {/if}
        <td>
            {$form.first_name.label}<br /> 
	    {$form.first_name.html}
        </td>
        <td>
            {$form.middle_name.label}<br />
            {$form.middle_name.html}
        </td>
        <td>
            {$form.last_name.label}<br />
            {$form.last_name.html}
        </td>
	{if $form.suffix_id}
    <td>
        	{$form.suffix_id.label}<br/>
        	{$form.suffix_id.html}
            </td>
        {/if}
    <td>&nbsp;</td>
  </tr>
    
  <tr>
	<td>
		{$form.nick_name.label}<br />
		{$form.nick_name.html|crmReplace:class:$formtextbig}
	</td>
	<td>
		{assign var='custom_42' value=$groupTree.1.fields.42.element_name}
        {$form.$custom_42.label}<br />
		{$form.$custom_42.html}                    
	</td>
	<td>
		{assign var='custom_60' value=$groupTree.1.fields.60.element_name}
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

{include file="CRM/Contact/Form/CurrentEmployer.tpl"}
