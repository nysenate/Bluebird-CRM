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
{if $showCMS }{*true if is_cms_user field is set *}
   <fieldset class="crm-group crm_user-group">
      <div class="messages help cms_user_help-section">
	 {if !$isCMS}
	    {ts}If you would like to create an account on this site, check the box below and enter a user name{/ts}
	    {if $form.cms_pass}
	       {ts}and a password{/ts}
	    {/if}
	 {else}
	    {ts}Please enter a user name to create an account{/ts}
	 {/if}.
	 {ts 1=$loginUrl}If you already have an account, <a href='%1'>please login</a> before completing this form.{/ts}
      </div>
      <div>{$form.cms_create_account.html} {$form.cms_create_account.label}</div>
     <div id="details" class="crm_user_signup-section">
	 <table class="form-layout-compressed">
	    <tr class="cms_name-section">
	       <td>{$form.cms_name.label}</td>
	       <td>{$form.cms_name.html} <a id="checkavailability" href="#" onClick="return false;">{ts}<strong>Check Availability</strong>{/ts}</a>
	          <span id="msgbox" style="display:none"></span><br />
	          <span class="description">{ts}Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.{/ts}</span>
	       </td>
	    </tr>
    
	    {if $form.cms_pass}
	       <tr class="cms_pass-section">
	          <td>{$form.cms_pass.label}</td>
	          <td>{$form.cms_pass.html}</td>
	       </tr>        
	       <tr class="crm_confirm_pass-section">
	          <td>{$form.cms_confirm_pass.label}</td>
	          <td>{$form.cms_confirm_pass.html}<br />
	             <span class="description">{ts}Provide a password for the new account in both fields.{/ts}
	          </td>
	       </tr>
	    {/if}
	 </table>        
     </div>
   </fieldset>

   {literal}
   <script type="text/javascript">
   {/literal}
   {if !$isCMS}
      {literal}
      if ( document.getElementsByName("cms_create_account")[0].checked ) {
	 show('details');
      } else {
	 hide('details');
      }
      {/literal}
   {/if}
   {literal}
   function showMessage( frm )
   {
      var cId = {/literal}'{$cId}'{literal};
      if ( cId ) {
	 alert('{/literal}{ts}You are logged-in user{/ts}{literal}');
	 frm.checked = false;
      } else {
	 var siteName = {/literal}'{$config->userFrameworkBaseURL}'{literal};
	 alert('{/literal}{ts}Please login if you have an account on this site with the link{/ts}{literal} ' + siteName  );
      }
   }
   var lastName = null;
   cj("#checkavailability").click(function() {
      var cmsUserName = cj.trim(cj("#cms_name").val());
      if ( lastName == cmsUserName) {
	 /*if user checking the same user name more than one times. avoid the ajax call*/
	 return;
      }
      /*don't allow special character and for joomla minimum username length is two*/

      var spchar = "\<|\>|\"|\'|\%|\;|\(|\)|\&|\\\\|\/";

      {/literal}{if $config->userFramework == "Drupal"}{literal}
	 spchar = spchar + "|\~|\`|\:|\@|\!|\=|\#|\$|\^|\*|\{|\}|\\[|\\]|\+|\?|\,"; 
      {/literal}{/if}{literal}	
      var r = new RegExp( "["+spchar+"]", "i");
      /*regular expression \\ matches a single backslash. this becomes r = /\\/ or r = new RegExp("\\\\").*/
      if ( r.exec(cmsUserName) ) {
	 alert('{/literal}{ts}Your username contains invalid characters{/ts}{literal}');
      	 return;
      } 
      {/literal}{if $config->userFramework == "Joomla"}{literal}
	 else if ( cmsUserName && cmsUserName.length < 2 ) {
	    alert('{/literal}{ts}Your username is too short{/ts}{literal}');
	    return;	
	 }
      {/literal}{/if}{literal}
      if (cmsUserName) {
	 /*take all messages in javascript variable*/
	 var check        = "{/literal}{ts}Checking...{/ts}{literal}";
	 var available    = "{/literal}{ts}This username is currently available.{/ts}{literal}";
	 var notavailable = "{/literal}{ts}This username is taken.{/ts}{literal}";
         
         //remove all the class add the messagebox classes and start fading
         cj("#msgbox").removeClass().addClass('cmsmessagebox').css({"color":"#000","backgroundColor":"#FFC","border":"1px solid #c93"}).text(check).fadeIn("slow");
	 
      	 //check the username exists or not from ajax
	 var contactUrl = {/literal}"{crmURL p='civicrm/ajax/cmsuser' h=0 }"{literal};
	 
	 cj.post(contactUrl,{ cms_name:cj("#cms_name").val() } ,function(data) {
	    if ( data.name == "no") {/*if username not avaiable*/
	       cj("#msgbox").fadeTo(200,0.1,function() {
		  cj(this).html(notavailable).addClass('cmsmessagebox').css({"color":"#CC0000","backgroundColor":"#F7CBCA","border":"1px solid #CC0000"}).fadeTo(900,1);
	       });
	    } else {
	       cj("#msgbox").fadeTo(200,0.1,function() {
		  cj(this).html(available).addClass('cmsmessagebox').css({"color":"#008000","backgroundColor":"#C9FFCA", "border": "1px solid #349534"}).fadeTo(900,1);
	       });
	    }	    
	 }, "json");
	 lastName = cmsUserName;
      } else {
	 cj("#msgbox").removeClass().text('').css({"backgroundColor":"#FFFFFF", "border": "0px #FFFFFF"}).fadeIn("fast");
      }
   });

   </script>
   {/literal}
   {if !$isCMS}	
      {include file="CRM/common/showHideByFieldValue.tpl" 
      trigger_field_id    ="cms_create_account"
      trigger_value       =""
      target_element_id   ="details" 
      target_element_type ="block"
      field_type          ="radio"
      invert              = 0
      }
   {/if}
{/if}
