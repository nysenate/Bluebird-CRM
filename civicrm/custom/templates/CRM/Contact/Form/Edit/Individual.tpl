{*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
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
CRM.$(function($) {
  if ($('#contact_sub_type *').length == 0) {//if they aren't any subtype we don't offer the option
    $('#contact_sub_type').parent().hide();
  }
  if (cid.length || !checkSimilar) {
   return;//no dupe check if this is a modif or if checkSimilar is disabled (contact_ajax_check_similar in civicrm_setting table)
  }
  $('#last_name').change(function() {
    // Close msg if it exists
    lastnameMsg && lastnameMsg.close && lastnameMsg.close();
    if (this.value == '') return;
    //NYSS 7435 alter ajax contact search
    CRM.api3('contact', 'get', {
      last_name: $('#last_name').val(),
      first_name: $('#first_name').val(),
      contact_type: 'Individual',
      'return': 'display_name,sort_name,email,phone,street_address,city'
    }).done(function(data) {
      var title = data.count == 1 ? {/literal}"{ts escape='js'}Similar Contact Found{/ts}" : "{ts escape='js'}Similar Contacts Found{/ts}"{literal},
        msg = "<em>{/literal}{ts escape='js'}If the person you were trying to add is listed below, click their name to view or edit their record{/ts}{literal}:</em>";
      if (data.is_error == 1 || data.count == 0) {
        return;
      }
      msg += '<ul class="matching-contacts-actions">';
      $.each(data.values, function(i, contact) {
        //NYSS 7435 logic to construct additional data fields
        var contactDetails = (contact.email) ? ' | '+contact.email.trim() : '';
        contactDetails += (contact.phone) ? ' | '+contact.phone.trim() : '';
        contactDetails += (contact.street_address) ? ' | '+contact.street_address.trim() : '';
        contactDetails += (contact.city) ? ' | '+contact.city.trim() : '';
        msg += '<li><a href="'+viewIndividual+contact.id+'">'+ contact.sort_name +'</a> '+contactDetails+'</li>';
      });
      msg += '</ul>';
      lastnameMsg = CRM.alert(msg, title);
      $('.matching-contacts-actions a').click(function() {
        // No confirmation dialog on click
        $('[data-warn-changes=true]').attr('data-warn-changes', 'false');
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
    {if $form.formal_title}
    <td>
      {$form.formal_title.label}<br/>
      {$form.formal_title.html}
    </td>
    {/if}
    {if $form.first_name}
    <td>
      {$form.first_name.label}<br />
      {$form.first_name.html}
    </td>
    {/if}
    {if $form.middle_name}
    <td>
      {$form.middle_name.label}<br />
      {$form.middle_name.html}
    </td>
    {/if}
    {if $form.last_name}
    <td>
      {$form.last_name.label}<br />
      {$form.last_name.html}
    </td>
    {/if}
    {if $form.suffix_id}
    <td>
      {$form.suffix_id.label}<br/>
      {$form.suffix_id.html}
    </td>
    {/if}
  </tr>

  <tr>
    {*NYSS*}
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
