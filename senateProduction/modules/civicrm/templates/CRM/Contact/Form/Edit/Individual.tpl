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
var editIndividual = "{crmURL p='civicrm/contact/add?reset=1&action=update&cid='}";
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
             msg = msg + '<tr><td><a href="'+viewIndividual+contact.contact_id+'">'+ contact.display_name +'</a></td><td>'+contact.email+'</td><td class="action-items"><a class="action-item action-item-first" href="'+viewIndividual+contact.contact_id+'">{/literal}{ts}View{/ts}{literal}</a><a class="action-item" href="'+editIndividual+contact.contact_id+'">{/literal}{ts}Edit{/ts}{literal}</a></td></tr>';
           });
           msg = msg+ '</table>';
           $('#last_name').parent().parent().after(msg+'</div><td></tr>');
           $('#lastname_msg a').click(function(){global_formNavigate =true; return true;});// No confirmation dialog on click
         });
	    });
  });
</script>
{/literal}

<table class="form-layout-compressed">
    <tr>
        {if $form.prefix_id}
	    <td>
                {$form.prefix_id.label}<br/>
                {$form.prefix_id.html}
            </td>    
        {/if}
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
	{if $form.suffix_id}
            <td>
                {$form.suffix_id.label}<br/>
                {$form.suffix_id.html}
            </td>
	{/if}
    </tr>
    
    <tr>
        <td colspan="2">
            {$form.current_employer.label}&nbsp;&nbsp;<br />
            {$form.current_employer.html|crmReplace:class:twenty}
            <div id="employer_address" style="display:none;"></div>
        </td>
        <td>
            {$form.job_title.label}<br />
            {$form.job_title.html}
        </td>
        <td>
            {$form.nick_name.label}<br />
            {$form.nick_name.html|crmReplace:class:big}
        </td>
        <td>
            {if $action == 1 and $contactSubType}&nbsp;{else}
               {$form.contact_sub_type.label}<br />
               {$form.contact_sub_type.html}
            {/if}
        </td>
    </tr>
</table>
{literal}
<script type="text/javascript">
var dataUrl        = "{/literal}{$employerDataURL}{literal}";
var newContactText = "{/literal}({ts}new contact record{/ts}){literal}";
cj('#current_employer').autocomplete( dataUrl, { 
                                      width        : 250, 
                                      selectFirst  : false,
                                      matchCase    : true, 
                                      matchContains: true
    }).result( function(event, data, formatted) {
        var foundContact   = ( parseInt( data[1] ) ) ? cj( "#current_employer_id" ).val( data[1] ) : cj( "#current_employer_id" ).val('');
        if ( ! foundContact.val() ) {
            cj('div#employer_address').html(newContactText).show();    
        } else {
            cj('div#employer_address').html('').hide();    
        }
    }).bind('change blur', function() {
        if ( !cj( "#current_employer_id" ).val( ) ) {
            cj('div#employer_address').html(newContactText).show();    
        }
});

// remove current employer id when current employer removed.
cj("form").submit(function() {
  if ( !cj('#current_employer').val() ) cj( "#current_employer_id" ).val('');
});

//current employer default setting
var employerId = "{/literal}{$currentEmployer}{literal}";
if ( employerId ) {
    var dataUrl = "{/literal}{crmURL p='civicrm/ajax/rest' h=0 q="className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&org=1&id=" }{literal}" + employerId ;
    cj.ajax({ 
        url     : dataUrl,   
        async   : false,
        success : function(html){
            //fixme for showing address in div
            htmlText = html.split( '|' , 2);
            cj('input#current_employer').val(htmlText[0]);
            cj('input#current_employer_id').val(htmlText[1]);
        }
    }); 
}

cj("input#current_employer").click( function( ) {
    cj("input#current_employer_id").val('');
});
</script>
{/literal}
