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
{* Contact Summary template for new tabbed interface. Replaces Basic.tpl *}
{if !empty($imageURL)}
    <div>
        {include file="CRM/Contact/Page/ContactImage.tpl"}
    </div>
{/if}
{if $action eq 2}
    {include file="CRM/Contact/Form/Contact.tpl"}
{else}

<div class="crm-actions-ribbon">
    <ul id="actions">
        {assign var='urlParams' value="reset=1"}
        {if $searchKey}
            {assign var='urlParams' value=$urlParams|cat:"&key=$searchKey"}
            {/if}
        {if $context}
            {assign var='urlParams' value=$urlParams|cat:"&context=$context"}
        {/if}

    	{* Include the Actions and Edit buttons if user has 'edit' permission and contact is NOT in trash. *}
        {if $permission EQ 'edit' and !$isDeleted}
            <li class="crm-contact-activity">
                {include file="CRM/Contact/Form/ActionsButton.tpl"}
            </li>
            <li>
                {assign var='editParams' value=$urlParams|cat:"&action=update&cid=$contactId"}
                <a href="{crmURL p='civicrm/contact/add' q=$editParams}" class="edit button" title="{ts}Edit{/ts}">
                <span><div class="icon edit-icon"></div>{ts}Edit{/ts}</span>
                </a>
            </li>
        {/if}

        {* Check for permissions to provide Restore and Delete Permanently buttons for contacts that are in the trash. *}
        {if (call_user_func(array('CRM_Core_Permission','check'), 'access deleted contacts') and 
        $is_deleted)}
            <li class="crm-delete-action crm-contact-restore">
                <a href="{crmURL p='civicrm/contact/view/delete' q="reset=1&cid=$contactId&restore=1"}" class="delete button" title="{ts}Restore{/ts}">
                <span><div class="icon restore-icon"></div>{ts}Restore from Trash{/ts}</span>
                </a>
            </li>

            {if call_user_func(array('CRM_Core_Permission','check'), 'delete contacts') && 
                call_user_func(array('CRM_Core_Permission','check'), 'delete contacts permanently') } 
                <li class="crm-delete-action crm-contact-permanently-delete">
                    <a href="{crmURL p='civicrm/contact/view/delete' q="reset=1&delete=1&cid=$contactId&skip_undelete=1"}" class="delete button" title="{ts}Delete Permanently{/ts}">
                    <span><div class="icon delete-icon"></div>{ts}Delete Contact Permanently{/ts}</span>{*NYSS*}
                    </a>
                </li>
            {/if}

        {elseif call_user_func(array('CRM_Core_Permission','check'), 'delete contacts')}
            <li class="crm-delete-action crm-contact-delete">
                <a href="{crmURL p='civicrm/contact/view/delete' q="reset=1&delete=1&cid=$contactId"}" class="delete button" title="{ts}Delete{/ts}">
                <span><div class="icon delete-icon"></div>{ts}Delete Contact{/ts}</span>
                </a>
            </li>
        {/if}

        {* Previous and Next contact navigation when accessing contact summary from search results. *}
        {if $nextContactID}
           {assign var='viewParams' value=$urlParams|cat:"&cid=$nextContactID"}
           <li class="crm-next-action">
             <a href="{crmURL p='civicrm/contact/view' q=$viewParams}" class="view button" title="{$nextContactName}">
             <span title="{$nextContactName}"><div class="icon next-icon"></div>{ts}Next{/ts}</span>
             </a>
           </li>
        {/if}
        {if $prevContactID}
           {assign var='viewParams' value=$urlParams|cat:"&cid=$prevContactID"}
           <li class="crm-previous-action">
             <a href="{crmURL p='civicrm/contact/view' q=$viewParams}" class="view button" title="{$prevContactName}">
             <span title="{$prevContactName}"><div class="icon previous-icon"></div>{ts}Previous{/ts}</span>
             </a>
           </li>
        {/if}


        {if !empty($groupOrganizationUrl)}
        <li class="crm-contact-associated-groups">
            <a href="{$groupOrganizationUrl}" class="associated-groups button" title="{ts}Associated Multi-Org Group{/ts}">
            <span><div class="icon associated-groups-icon"></div>{ts}Associated Multi-Org Group{/ts}</span>
            </a>   
        </li>
        {/if}
    </ul> 
    <div class="clear"></div>                        
</div><!-- .crm-actions-ribbon -->

<div class="crm-block crm-content-block crm-contact-page">

    <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="crm-contact-tabs-list">
            <li id="tab_summary" class="crm-tab-button">
            	<a href="#contact-summary" title="{ts}Summary{/ts}">
            	<span> </span> {ts}Summary{/ts}
            	<em>&nbsp;</em>
            	</a>
            </li>
            {foreach from=$allTabs key=tabName item=tabValue}
            <li id="tab_{$tabValue.id}" class="crm-tab-button crm-count-{$tabValue.count}">
            	<a href="{$tabValue.url}" title="{$tabValue.title}">
            		<span> </span> {$tabValue.title}
            		<em>{$tabValue.count}</em>
            	</a>
            </li>
            {/foreach}
        </ul>

{*Assign AddConstInfo custom fields*}
{foreach from=$viewCustomData.1 item=addConstInfo}
	{foreach from=$addConstInfo.fields item=addConstInfoField key=customId}
        {assign var="custom_$customId" value=$addConstInfoField}
	{/foreach}
{/foreach}

{*Assign ContactDetails custom fields*}
{foreach from=$viewCustomData.8 item=contactDetails}
	{foreach from=$contactDetails.fields item=contactDetailsField key=customId}
        {assign var="customCD_$customId" value=$contactDetailsField}
	{/foreach}
{/foreach}

        <div title="Summary" id="contact-summary" class="ui-tabs-panel ui-widget-content ui-corner-bottom {if substr_count($custom_19.field_value, 'Yes')}friend-of-senator{/if}">
            {if (isset($hookContentPlacement) and ($hookContentPlacement neq 3)) or empty($hookContentPlacement)}
                
                {if !empty($hookContent) and isset($hookContentPlacement) and $hookContentPlacement eq 2}
                    {include file="CRM/Contact/Page/View/SummaryHook.tpl"}
                {/if}
                
                {if !empty($contact_type_label) OR !empty($current_employer_id) OR !empty($job_title) OR !empty($legal_name) OR $sic_code OR !empty($nick_name) OR !empty($contactTag) OR !empty($source)}
                <div id="contactTopBar">
                	<div class="subHeader"><!--Basic Constituent Information-->{$display_name}</div>
                    
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                    	<table>
                        {if $nick_name}
                            <tr>
                                <td class="label">{ts}Nickname{/ts}</td>
                                <td>
                                    {$nick_name}
                                </td>
                            </tr>
                        {/if}
                        {if $custom_60.field_value || $source}
                        	<tr>
                                <td class="label">
                                   {if $custom_60.field_value}{$custom_60.field_title}{else}Source{/if}
                                </td>
                                <td>
                                   {$custom_60.field_value}
                                   {if $custom_60.field_value && $source}<br />{/if}
                                   {if $source}{$source}{/if}
                                </td>
                        	</tr>
                        {/if}
                        {if $legal_name}
                        	<tr>
                                <td class="label">{ts}Legal Name{/ts}</td>
                                <td>{$legal_name}</td>
                        	</tr>
                        {/if}
                        {if $sic_code}
                        	<tr>
                                <td class="label">{ts}SIC Code{/ts}</td>
                                <td>{$sic_code}</td>
                        	</tr>
                        {/if}
                        <tr>
                            <td class="label">{ts}Contact Type{/ts}
                            	{if $custom_42.field_value}<br />{$custom_42.field_title}{/if}
                            </td>
                            <td>{$contact_type_label}
                            	{if $custom_42.field_value}<br />{$custom_42.field_value}{/if}
                            </td>
                        </tr>
                    	</table>
                    	</div>
                    </div>
                    
                    <div class="contact_panel">
                        <div class="contactCardRight">
                    	<table>
                        {if $custom_58.field_value}{*Ethnicity*}
                        	<tr>
                             	<td class="label">
                             	   {$custom_58.field_title}
                             	</td>
                             	<td>
                             	   {$custom_58.field_value}
                             	   {if $custom_62.field_value && $custom_58.field_value}<br />{/if}
                             	   {$custom_62.field_value}
                             	</td>
                        	</tr>
                        {/if}
                        {if $gender_display}
                        	<tr>
                             	<td class="label">
                             	   	Gender
                             	</td>
                             	<td>
                            	    {$gender_display}
                             	   	{if $custom_45.field_value && $gender_display}<br />{/if}
                            	    {$custom_45.field_value}
                             	</td>
                        	<tr>
                        {/if}
                        {if $preferred_language}
                        	<tr>
                            	<td class="label">{ts}Preferred Language{/ts}</td>
                            	<td>{$preferred_language}</td>
                        	</tr>
                        {/if}
                    	</table>
                        </div>
					</div>
                    <div class="clear"></div>
                </div><!-- #contactTopBar -->
                {/if}
                <div class="contact_details">
                    {if $address}
                    <div class="contact_panel">
                        {foreach from=$address item=add key=locationIndex}
                        <div class="{cycle name=location values="contactCardLeft,contactCardRight"} crm-address_{$locationIndex} crm-address-block crm-address_type_{$add.location_type}">
                            <table>
                                <tr>
                                    <td class="label">{ts 1=$add.location_type}%1&nbsp;Address{/ts}
                                        {if $config->mapAPIKey AND $add.geo_code_1 AND $add.geo_code_2}
                                            <br /><a href="{crmURL p='civicrm/contact/map' q="reset=1&cid=`$contactId`&lid=`$add.location_type_id`"}" title="{ts 1=`$add.location_type`}Map %1 Address{/ts}"><span class="geotag">{ts}Map{/ts}</span></a>
                                        {/if}</td>
                                    <td class="crm-contact-address_display">
                                        {if !empty($sharedAddresses.$locationIndex.shared_address_display.name)}
                                             <strong>{ts}Shared with:{/ts}</strong><br />
                                             {$sharedAddresses.$locationIndex.shared_address_display.name}<br />
                                         {/if}
                                         {$add.display|nl2br}
                                    </td>
                                </tr>
                            </table>
			    {foreach from=$add.custom item=customGroup key=cgId}
                            {assign var="isAddressCustomPresent" value=1}
			        {foreach from=$customGroup item=customValue key=cvId}
			            <div id="address_custom_{$cgId}_{$locationIndex}" class="crm-accordion-wrapper crm-address-custom-{$cgId}-{$locationIndex}-accordion crm-accordion-closed">
			                <div class="crm-accordion-header">
			                    <div class="icon crm-accordion-pointer"></div>
				            {$customValue.title}
			                </div>
			                <div class="crm-accordion-body">
				            <table>
				                {foreach from=$customValue.fields item=customField key=cfId}
					            <tr><td class="label">{$customField.field_title}</td><td class="crm-contact_custom_field_value">{$customField.field_value}</td></tr>
	                  	                {/foreach}
			                    </table>
			                </div>
			            </div>
                                    <script type="text/javascript">
                                        {if $customValue.collapse_display eq 1 }
                                            cj('#address_custom_{$cgId}_{$locationIndex}').removeClass('crm-accordion-open').addClass('crm-accordion-closed');
                                        {else}
                                            cj('#address_custom_{$cgId}_{$locationIndex}').removeClass('crm-accordion-closed').addClass('crm-accordion-open');
                                        {/if}
                                    </script>
                                {/foreach}
                            {/foreach}
                        </div>
                        {/foreach}

                        <div class="clear"></div>
                    </div>
					{/if}
					
					<!-- /address section -->
					
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                            {if $phone OR $im OR $openid}
                                <table>
                                    {foreach from=$phone item=item}
                                        {if $item.phone}
                                        <tr>
                                            <td class="label">{$item.location_type}&nbsp;{$item.phone_type}</td>
                                            {*NYSS 3752*}
                                            <td {if $item.is_primary eq 1}class="primary"{/if}><span {if $privacy.do_not_phone} class="do-not-phone" title={ts}"Privacy flag: Do Not Phone"{/ts} {/if}>{$item.phone}{if $item.phone_ext}&nbsp;&nbsp;{ts}ext.{/ts} {$item.phone_ext}{/if}</span></td>
                                        </tr>
                                        {/if}
                                    {/foreach}
                                    {foreach from=$im item=item}
                                        {if $item.name or $item.provider}
                                        {if $item.name}<tr><td class="label">{$item.provider}&nbsp;({$item.location_type})</td><td {if $item.is_primary eq 1}class="primary"{/if}>{$item.name}</td></tr>{/if}
                                        {/if}
                                    {/foreach}
                                    {foreach from=$openid item=item}
                                        {if $item.openid}
                                            <tr>
                                                <td class="label">{$item.location_type}&nbsp;{ts}OpenID{/ts}</td>
                                                <td {if $item.is_primary eq 1}class="primary"{/if}><a href="{$item.openid}">{$item.openid|mb_truncate:40}</a>
                                                    {if $config->userFramework eq "Standalone" AND $item.allowed_to_login eq 1}
                                                        <br/> <span style="font-size:9px;">{ts}(Allowed to login){/ts}</span>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                
                                </table>
    						{/if}
                             <table>
                                {foreach from=$email key="blockId" item=item}
                                    {if $item.email}
                                    <tr>
                                        <td class="label">{$item.location_type}&nbsp;{ts}Email{/ts}</td>
                                        <td><span class={if $privacy.do_not_email}"do-not-email" title="{ts}Privacy flag: Do Not Email{/ts}" {elseif $item.on_hold}"email-hold" title="{ts}Email on hold - generally due to bouncing.{/ts}" {elseif $item.is_primary eq 1}"primary"{/if}>
                                        {*NYSS - LCD #2555*}
                                        {if $privacy.do_not_email || $item.on_hold}{$item.email}
                                        {else}<a href="mailto:{$item.email}">{$item.email}</a>{/if}
                                        {if $item.on_hold}&nbsp;({ts}On Hold{/ts}){/if}{if $item.is_bulkmail}&nbsp;({ts}Bulk{/ts}){/if}</span></td>
					                    <td class="description">{if $item.signature_text OR $item.signature_html}<a href="#" title="{ts}Signature{/ts}" onClick="showHideSignature( '{$blockId}' ); return false;">{ts}(signature){/ts}</a>{/if}</td>
                                    </tr>
                                    <tr id="Email_Block_{$blockId}_signature" class="hiddenElement">
                                        <td><strong>{ts}Signature HTML{/ts}</strong><br />{$item.signature_html}<br /><br />
                                        <strong>{ts}Signature Text{/ts}</strong><br />{$item.signature_text|nl2br}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    {/if}
                                {/foreach}
                                {if $website}
                                {foreach from=$website item=item}
                                    {if $item.url}
                                    <tr>
                                        <td class="label">{$item.website_type}</td>
                                        <td><a href="{$item.url}" target="_blank">{$item.url}</a></td>
                                        <td></td>
                                    </tr>
                                    {/if}
                                {/foreach}
                                {/if}
                                {if $user_unique_id}
                                    <tr>
                                        <td class="label">{ts}Unique Id{/ts}</td>
                                        <td>{$user_unique_id}</td>
                                        <td></td>
                                    </tr>
                                {/if}
                                
                            </table>
                            <table>
						 </table>
                        </div><!-- #contactCardLeft -->
                        
                        <div class="contactCardRight">
    					{if $contact_type eq 'Individual' AND $showDemographics}
    					<table>
                        {if $current_employer}
                        <tr>
                        	<td class="label">{ts}Employer{/ts}</td>
                            <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$current_employer_id`"}" title="{ts}view current employer{/ts}">{$current_employer}</a></td>
                        </tr>
                        {/if}
                        {if $job_title}
                        <tr>
                        	<td class="label">{ts}Job Title{/ts}</td>
                        	<td>{$job_title}</td>
                        </tr>
                        {/if}
        				<!--<tr>
        				    <td class="label">{ts}Gender{/ts}</td>
                            <td>{$gender_display}
                                {if $custom_45.field_value && $gender_display}<br />{/if}
                                {$custom_45.field_value}
                            </td>
        				</tr>-->
        				<tr>
        				    <td class="label">{ts}Date of birth{/ts}</td><td>
        				    {if $birthDateViewFormat}	 
        				        {$birth_date_display|crmDate:$birthDateViewFormat}
        				    {else}
        				        {$birth_date_display|crmDate}
        				    {/if} 
        					</td>
        				</tr>
        				
        				{if $is_deceased eq 1}
                            <tr>
                            <td class="label" colspan=2><span class="font-red upper">{ts}Contact is Deceased{/ts}</span></td>
                            </tr>
        				    {if $deceased_date}
                                <tr>
                                <td class="label">{ts}Date Deceased{/ts}</td>
        				        <td>
        				        {if $birthDateViewFormat}          
						            {$deceased_date_display|crmDate:$birthDateViewFormat}
        				        {else}
        				            {$deceased_date_display|crmDate}
        				        {/if}
        				        </td>
                                </tr>
        				    {/if}
         				{else}
         				    <tr>
                            <td class="label">{ts}Age{/ts}</td>
         				    <td>{if $age.y}{ts count=$age.y plural='%count years'}%count year{/ts}{elseif $age.m}{ts count=$age.m plural='%count months'}%count month{/ts}{/if} </td>
        				    </tr>
                        {/if}
                        
                        {if $custom_63.field_value}{*religion*}
                        <tr>
                        	<td class="label">{$custom_63.field_title}</td>
                        	<td>{$custom_63.field_value}</td>
                        </tr>
                        {/if}
    				</table>
				    {/if}
					</div><!-- #contactCardRight -->
					{*include file="CRM/Contact/Page/View/Demographics.tpl"*}

                    <div class="clear"></div>
                    </div><!-- #contact_panel -->
                    
                </div><!--contact_details-->

                <div id="customFields" style="width:99%;">
                    <div class="contact_panel">
                    {*include file="CRM/Contact/Page/View/CustomDataView.tpl" side='1'*}
                    
                    <!--Additional Constituent Info-->
                    <div class="customFieldGroup ui-corner-all">
                	  <div id="Additional_Constituent_Information_1">
                  		<div class="crm-accordion-header">
                    	<div onclick="cj(&quot;table#Additional_Constituent_Information_1&quot;).toggle(); cj(this).toggleClass(&quot;expanded&quot;); return false;" class="show-block expanded collapsed ">
                        Additional Constituent Information
                    	</div>
                  		</div>
                        <table id="Additional_Constituent_Information_1"><tr>
                        <td style="padding:0;background:none;">
                        <div class="contactCardLeft">
                  		<table><tbody>
                        	<tr>
                            	<td class="label">{$custom_18.field_title}</td><!--active const-->
                				<td class="html-adjust crm-custom-data">{$custom_18.field_value}</td>
                            </tr>
                            
                            <tr>
      							<td class="label">{$custom_17.field_title}</td><!--interest in vol-->
                				<td class="html-adjust crm-custom-data">{$custom_17.field_value}</td>
      						</tr>
                            <tr>
      							<td class="label">{$custom_19.field_title}</td><!--friend of sen-->
                				<td class="html-adjust crm-custom-data">{$custom_19.field_value}</td>
      						</tr>
                            <tr>
                            	<td class="label">Voter Registration Status</td><!--voter reg-->
                				<td class="html-adjust crm-custom-data">{$custom_23.field_value}</td>
                            </tr>
                            <tr>
                            	<td class="label">BOE Date of Registration</td><!--boe date-->
                				<td class="html-adjust crm-custom-data">{$custom_24.field_value}</td>
                            </tr>
                    	</tbody></table>
                        </div>
                        <div class="contactCardRight">
                        <table><tbody>
                        	<tr>
                                <td class="label">Professional Accreditations</td><!--prof acc-->
                				<td class="html-adjust crm-custom-data">{$custom_16.field_value}</td>
                            </tr>
                            <tr>
                                <td class="label">Skills/Areas of Interest</td><!--skills-->
                				<td class="html-adjust crm-custom-data">{$custom_20.field_value}</td>
                            </tr>
                            <tr>
                                <td class="label">Honors and Awards</td><!--honors-->
                				<td class="html-adjust crm-custom-data">{$custom_21.field_value}</td>
                            </tr>
                            <tr>
                                <td class="label">Record Type</td><!--record type-->
                				<td class="html-adjust crm-custom-data">{$custom_61.field_value}</td>
                            </tr>
                        </tbody></table>
                        </div>
                        </td></tr></table>
                	  </div>
            		</div>
                    <!--Additional Constituent END-->
                    
                    </div>
                    <div class="clear"></div>
                    
                    <div class="contact_panel">
                      <div style="width:100%">
                        <div class="contactCardLeft">
                            {include file="CRM/Contact/Page/View/CustomDataView.tpl" side='0'}
                        </div><!--contactCardLeft-->

                        <div class="contactCardRight">
                            <div class="crm-accordion-wrapper crm-communications_preferences-accordion crm-accordion-open">
                             <div class="crm-accordion-header">
                              <div class="icon crm-accordion-pointer"></div>
                               Communication Preferences
                             </div><!-- /.crm-accordion-header -->
                             <div class="crm-accordion-body">
                              <table>
                                <tr><td class="label">{ts}Privacy{/ts}</td>
                                    <td class="crm-contact-privacy_values">
                                    	<span class="font-red upper">
                                        {foreach from=$privacy item=priv key=index}
                                            {if $priv}{$privacy_values.$index}<br />{/if}
                                        {/foreach}
                                        {if $is_opt_out}{ts}No Bulk Emails (User Opt Out){/ts}{/if}
                                    	</span>
                                        {if $customCD_64.field_value}
                                        	<span id="privacyNote">
                                            	{$customCD_64.field_value}
                                            </span>
                                        {/if}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">{ts}Preferred Method(s){/ts}</td><td class="crm-contact-preferred_communication_method_display">{$preferred_communication_method_display}</td>
                                </tr>
                                
                                <tr>
                                    <td class="label">{ts}Email Format{/ts}</td><td class="crm-contact-preferred_mail_format">{$preferred_mail_format}</td>
                                </tr>
                            
							<tr>
								<td class="label">{ts}Email Greeting{/ts}{if !empty($email_greeting_custom)}<br/><span style="font-size:8px;">({ts}Customized{/ts})</span>{/if}</td>
								<td class="crm-contact-email_greeting_display">{$email_greeting_display}</td>
							</tr>
							<tr>
								<td class="label">{ts}Postal Greeting{/ts}{if !empty($postal_greeting_custom)}<br/><span style="font-size:8px;">({ts}Customized{/ts})</span>{/if}</td>
								<td class="crm-contact-postal_greeting_display">{$postal_greeting_display}</td>
							</tr>

							<tr>
								<td class="label">{ts}Addressee{/ts}{if !empty($addressee_custom)}<br/><span style="font-size:8px;">({ts}Customized{/ts})</span>{/if}</td>
								<td class="crm-contact-addressee_display">{$addressee_display}</td>
							</tr>
						 </table>
						 
                              
                             </div><!-- /.crm-accordion-body -->
                            </div><!-- /.crm-accordion-wrapper -->

                        </div>
                      </div><!--end-->
                    </div>
                    <div class="clear"></div>
                </div>
                {literal}
                <script type="text/javascript">
                    cj('.columnheader').click( function( ) {
                        var aTagObj = cj(this).find('a');
                        if ( aTagObj.hasClass( "expanded" ) ) {
                            cj(this).parent().find('tr:not(".columnheader")').hide( );
                        } else {    
                            cj(this).parent().find('tr:not(".columnheader")').show( );
                        }
                        aTagObj.toggleClass("expanded");
                        return false;
                    });
                </script>
                {/literal}
                {if !empty($hookContent) and isset($hookContentPlacement) and $hookContentPlacement eq 1}
                    {include file="CRM/Contact/Page/View/SummaryHook.tpl"}
                {/if}
            {else}
                {include file="CRM/Contact/Page/View/SummaryHook.tpl"}
            {/if}
        </div>
		<div class="clear"></div>
    </div>
 <script type="text/javascript"> 
 var selectedTab  = 'summary';
 var spinnerImage = '<img src="{$config->resourceBase}i/loading.gif" style="width:10px;height:10px"/>';
 {if $selectedChild}selectedTab = "{$selectedChild}";{/if}  
 {literal}
 function fixTabAbort(event,ui){
//	jQuery(ui.tab).data("cache.tabs",(jQuery(ui.panel).html() == "") ? false : true);
    }

//explicitly stop spinner
function stopSpinner( ) {
 cj('li.crm-tab-button').each(function(){ cj(this).find('span').text(' ');})	 
}
 cj( function() {
     var tabIndex = cj('#tab_' + selectedTab).prevAll().length;
     cj("#mainTabContainer").tabs({ selected: tabIndex, spinner: spinnerImage,cache: true, select: fixTabAbort, load: stopSpinner});
     cj(".crm-tab-button").addClass("ui-corner-bottom");     
 });
 {/literal}
 </script>

{/if}
{literal}
<script type="text/javascript">
function showHideSignature( blockId ) {
	  cj("#Email_Block_" + blockId + "_signature").show( );   
	  
	  cj("#Email_Block_" + blockId + "_signature").dialog({
		title: "Signature",
		modal: true,
		bgiframe: true,
		width: 900,
		height: 500,
		overlay: { 
			opacity: 0.5, 
			background: "black"
		},

		beforeclose: function(event, ui) {
            		cj(this).dialog("destroy");
        	},

		open:function() {
		},

		buttons: { 
			"Done": function() { 
				cj(this).dialog("destroy"); 
			} 
		} 
		
	  });
}

</script>
{/literal}

{if !empty($isAddressCustomPresent)}
    {literal}
        <script type="text/javascript">
            cj(function() {
                cj().crmaccordions(); 
            });
        </script>
    {/literal}
{/if}
<div class="clear"></div>
</div><!-- /.crm-content-block -->
