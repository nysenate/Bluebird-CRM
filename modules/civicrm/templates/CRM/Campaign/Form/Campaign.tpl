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
<div class="crm-block crm-form-block crm-campaign-form-block">
<div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
</div>
{if $action eq 8}
  <table class="form-layout">
    <tr>
      <td colspan="2">
        <div class="status"><div class="icon inform-icon"></div>&nbsp;{ts}Are you sure you want to delete this Campaign?{/ts}</div>
      </td>
    </tr>
  </table>
{else}

    <table class="form-layout-compressed">
	<tr class="crm-campaign-form-block-title">
	    <td class="label">{$form.title.label}</td>
	    <td class="view-value">{$form.title.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-campaign_type_id">
	    <td class="label">{$form.campaign_type_id.label}</td>
	    <td class="view-value">{$form.campaign_type_id.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-description">
	    <td class="label">{$form.description.label}</td>
	    <td class="view-value">{$form.description.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-includeGroups">
	    <td class="label">{$form.includeGroups.label}</td>
	    <td>{$form.includeGroups.html}</td>  
	</tr>
	<tr class="crm-campaign-form-block-start_date">
	    <td class="label">{$form.start_date.label}</td>
	    <td class="view-value">{include file="CRM/common/jcalendar.tpl" elementName=start_date}
	    </td>
	</tr>
	<tr class="crm-campaign-form-block-end_date">
	    <td class="label">{$form.end_date.label}</td>
	    <td class="view-value">{include file="CRM/common/jcalendar.tpl" elementName=end_date}</td>
	</tr>
	<tr class="crm-campaign-form-block-status_id">
	    <td class="label">{$form.status_id.label}</td>
	    <td class="view-value">{$form.status_id.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-external_identifier">
	    <td class="label">{$form.external_identifier.label}</td>
	    <td class="view-value">{$form.external_identifier.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-parent_id">
	    <td class="label">{$form.parent_id.label}</td>
	    <td class="view-value">{$form.parent_id.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-is_active">
	    <td class="label">{$form.is_active.label}</td>
	    <td class="view-value">{$form.is_active.html}</td>
	</tr>
    </table>

{/if}
<div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
</div>

{if $context eq 'dialog'}
{literal}
<script type="text/javascript">

   var options = { 
        beforeSubmit:  showRequest  // pre-submit callback  
   }; 

   // bind form using 'ajaxForm'
   cj('form#Campaign').ajaxForm( options );

   // pre-submit function
   function showRequest(formData, jqForm, options) { 
        var queryString = cj.param(formData); 
        queryString = queryString + '&snippet=5';
        var postUrl = {/literal}"{crmURL p='civicrm/campaign/add' q='context=dialog' h=0 }"{literal}; 
        var response = cj.ajax({
           type: "POST",
           url: postUrl,
           async: false,
           data: queryString,
           dataType: "json",
           success: function( response ) {
               if ( response.returnSuccess ) {
                   cj("#campaign-dialog").dialog("close");
		   
		   // reload page to show updated data
		   document.location = {/literal}'{crmURL p="civicrm/campaign" q="reset=1&subPage=campaign" h=0 }'{literal};
               }
           }
         }).responseText;
	 
         cj("#campaign-dialog").html( response );
	 
        // here we could return false to prevent the form from being submitted; 
        // returning anything other than false will allow the form submit to continue 
        return false; 
    }
 
   // hide hidden elements on form
   cj(document).ready( function() {
     cj('input.hiddenElement').each( function() {
     	 cj(this).attr('style','display:none' );
      });
   });

</script>
{/literal}
{/if}