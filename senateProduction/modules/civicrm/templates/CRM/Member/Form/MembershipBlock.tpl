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
{* Configure Membership signup/renewal block for an Online Contribution page *}
{* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
{include file="CRM/common/WizardHeader.tpl"}
<div id="form" class="crm-block crm-form-block crm-member-membershipblock-form-block">
<div id="help">
    {ts}Use this form to enable and configure a Membership Signup and Renewal section for this Online Contribution Page. If you're not using this page for membership signup, leave the <strong>Enabled</strong> box un-checked..{/ts} {docURL page="Configure Membership"}
</div>
  {if $form.membership_type.html}   
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div> 
    <table class="form-layout-compressed">   
        <tr class="crm-member-membershipblock-form-block-is_active">
            <td class="label"></td><td class="html-adjust">{$form.is_active.html}&nbsp;{$form.is_active.label}<br />
            <span class="description">{ts}Include a Membership Signup section in this Online Contribution page?{/ts}</span></td>
        </tr>
    </table>
    <div id="memberFields">
      <table class="form-layout-compressed"> 
          <tr class="crm-member-membershipblock-form-block-new_title">
              <td class="label">{$form.new_title.label}
              {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_membership_block' field='new_title' id=$membershipBlockId}{/if}</td><td>{$form.new_title.html}<br />
              <span class="description">{ts}Membership section title - for new member signups.{/ts}</span></td>
          </tr>
          <tr class="crm-member-membershipblock-form-block-new_text">
              <td class="label">{$form.new_text.label}</td>
              <td>{$form.new_text.html}<br />
              <span class="description">{ts}Membership section introductory text - for new member signups.{/ts}<br /></span><br /></td>
          </tr>
          <tr class="crm-member-membershipblock-form-block-renewal_title">
              <td class="label">{$form.renewal_title.label} 
              {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_membership_block' field='renewal_title' id=$membershipBlockId}{/if}</td><td>{$form.renewal_title.html}<br />
              <span class="description">{ts}Membership section title - displayed to renewing members.{/ts}</span></td>
          </tr>
          <tr class="crm-member-membershipblock-form-block-renewal_text">
              <td class="label">{$form.renewal_text.label}</td>
              <td>{$form.renewal_text.html}<br />
              <span class="description">{ts}Membership section introductory text - displayed to renewing members.{/ts}</span><br /></td>
          </tr>
          <tr class="crm-member-membershipblock-form-block-membership_type">
              <td>{$form.membership_type.label}</td> 
              <td>
                {assign var="count" value="1"}
                {strip}
                  <table class="report">
                    <tr class="columnheader" style="vertical-align:top;"><th style="border-right: 1px solid #4E82CF;">{ts}Include these membership types{/ts}:</th><th>{ts}Default{/ts}:<br />
                    <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('membership_type_default', 'MembershipBlock'); return false;" >unselect</a>)</span></th></tr>
                      {assign var="index" value="1"}
                      {foreach name=outer key=key item=item from=$form.membership_type}
                        {if $index < 10}
                          {assign var="index" value=`$index+1`}
                        {else}
                         <tr>  
                          <td class="labels font-light">{$form.membership_type.$key.html}</td>
                          <td class="labels font-light">{$form.membership_type_default.$key.html}</td>
                         </tr>
                        {/if}
                      {/foreach}
                  </table>
                {/strip}
              </td>    
          <tr class="crm-member-membershipblock-form-block-is_required">
              <td class="label"></td><td class="html-adjust">{$form.is_required.html}&nbsp;{$form.is_required.label}<br />
              <span class="description">{ts}If checked, user must signup for one of the displayed membership options before continuing.{/ts}</span></td>
          </tr>
          <tr class="crm-member-membershipblock-form-block-is_separate_payment">
              <td class="label"></td><td class="html-adjust">{$form.is_separate_payment.html}&nbsp;{$form.is_separate_payment.label} {help id="id-separate-pay"}<br />
              <span class="description">{ts}Check this box if you are including both Membership Signup/Renewal AND a Contribution Amount section, AND you want the membership fee to be charged separately from any additional contribution amount.{/ts}</span></td>
          </tr>
          <tr class="crm-member-membershipblock-form-block-display_min_fee">
              <td class="label"></td><td class="html-adjust">{$form.display_min_fee.html}&nbsp;{$form.display_min_fee.label} {help id="id-display-fee"}<br />
              <span class="description">{ts}Display the membership fee along with the membership name and description for each membership option?{/ts}</span></td>
      </table>
   </div>
  {else}
      <div class="status message">
         {capture assign=docURL}{crmURL p="civicrm/admin/member/membershipType" q="reset=1"}{/capture}
         {ts 1=$docURL}You need to have at least one <a href="%1">Membership Type</a> to enable Member Signup.{/ts}
      </div>
  {/if} 
      <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>

{literal}
<script type="text/javascript">
	var is_act = document.getElementsByName('is_active');
  	if ( ! is_act[0].checked) {
           hide('memberFields');
	}
       function memberBlock(chkbox) {
           if (chkbox.checked) {
	      show('memberFields');
	      return;
           } else {
	      hide('memberFields');
    	      return;
	   }
       }
</script>
{/literal}

{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}
