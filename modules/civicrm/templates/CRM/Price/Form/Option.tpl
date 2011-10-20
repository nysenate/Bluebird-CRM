{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
{if $action eq 8}
      <div class="messages status">
       <div class="icon inform-icon"></div>   
          {ts}WARNING: Deleting this option will result in the loss of all data.{/ts} {ts}This action cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
         
      </div>
     {/if}
<h3>{if $action eq 8 }{ts}Delete Option{/ts}{elseif $action eq 1}{ts}New Option{/ts}{elseif $action eq 2}{ts}Edit Option{/ts}{/if}</h3>
<div class="crm-form-block">
     {if $action neq 8}
	<table class="form-layout">
	    {if $showMember}
            <tr class="crm-price-option-form-block-membership_type_id">
               	<td class="label">{$form.membership_type_id.label}</td>
               	<td>{$form.membership_type_id.html}
               	<br /> <span class="description">{ts}If a membership type is selected, a membership will be created or renewed when users select this option. Leave this blank if you are using this for non-membership options (e.g. magazine subscription).{/ts} {help id="id-member-price-options" file="CRM/Price/Page/Field.hlp"}</span></td>
            </tr>
	    {/if}
	    <tr class="crm-price-option-form-block-label">
               <td class="label">{$form.label.label}</td>
               <td>{$form.label.html}</td>
            </tr>
            <tr class="crm-price-option-form-block-amount">
                <td class="label">{$form.amount.label}</td>
                <td>{$form.amount.html}</td>
            </tr>
            <tr class="crm-price-option-form-block-description">
                <td class="label">{$form.description.label}</td>
                <td>{$form.description.html}</td>
            </tr>
            <tr class="crm-price-option-form-block-count">
                <td class="label">{$form.count.label}</td>
                <td>{$form.count.html}</td>
            </tr>
            <tr class="crm-price-option-form-block-max_value">
                <td class="label">{$form.max_value.label}</td>
                <td>{$form.max_value.html}</td>
            </tr>
            <tr class="crm-price-option-form-block-weight">
               <td class="label">{$form.weight.label}</td>
               <td>{$form.weight.html}</td>
            </tr>
            <tr class="crm-price-option-form-block-is_active">
               <td class="label">{$form.is_active.label}</td>
               <td>{$form.is_active.html}</td>
	{if !$hideDefaultOption}
	    <tr class="crm-price-option-form-block-is_default">
               <td class="label">{$form.is_default.label}</td>
               <td>{$form.is_default.html}</td>
            </tr>
	{/if}
	</table>
      {/if}
    
    
    <div id="crm-submit-buttons" class="form-item">
    <table class="form-layout">
        <tr>
           <td>&nbsp;</td>
           <td>{include file="CRM/common/formButtons.tpl"}</td>
        </tr>
    </table>
    </div>

</div>

{literal}
     <script type="text/javascript">
     
     function calculateRowValues( ) {
      var mtype = cj("#membership_type_id").val();
      var postUrl = "{/literal}{crmURL p='civicrm/ajax/memType' h=0}{literal}";
      cj.post( postUrl, {mtype: mtype}, function( data ) {
              cj("#amount").val( data.total_amount );   
              cj("#label").val( data.name );   
      
              }, 'json');  
     }
    {/literal}
</script>

