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
{* Contact Summary template for new tabbed interface. Replaces Basic.tpl *}
{if $imageURL }
    <div>
        {include file="CRM/Contact/Page/ContactImage.tpl"}
    </div>
{/if}
{if $action eq 2}
    {include file="CRM/Contact/Form/Contact.tpl"}
{else}

<div class="crm-actions-ribbon">
                    <ul id="actions">
                    	{* CRM-4418 *}
                        {* user should have edit permission to delete contact *}
                        {if (call_user_func(array('CRM_Core_Permission','check'), 'delete contacts') and $permission == 'edit') or (call_user_func(array('CRM_Core_Permission','check'), 'access deleted contacts') and $is_deleted)}
                        {if call_user_func(array('CRM_Core_Permission','check'), 'access deleted contacts') and $is_deleted}
                        <li class="crm-delete-action crm-contact-restore">
                        <a href="{crmURL p='civicrm/contact/view/delete' q="reset=1&cid=$contactId&restore=1"}" class="delete button" title="{ts}Restore{/ts}">
                        <span><div class="icon restore-icon"></div>{ts}Restore from Trash{/ts}</span>
                        </a>
                        </li>
                        
                        <li class="crm-delete-action crm-contact-permanently-delete">
                        <a href="{crmURL p='civicrm/contact/view/delete' q="reset=1&delete=1&cid=$contactId&skip_undelete=1"}" class="delete button" title="{ts}Delete Permanently{/ts}">
                        <span><div class="icon delete-icon"></div>{ts}Delete Permanently{/ts}</span>
                        </a>
                        </li>
                        {else}
                        <li class="crm-delete-action crm-contact-delete">
                        <a href="{crmURL p='civicrm/contact/view/delete' q="reset=1&delete=1&cid=$contactId"}" class="delete button" title="{ts}Delete{/ts}">
                        <span><div class="icon delete-icon"></div>{ts}Delete{/ts}</span>
                        </a>
                        </li>
                        {/if}
                        {/if}
                    
                    	{* Include the Actions button with dropdown if session has 'edit' permission *}
                        {if $permission EQ 'edit' and !$isDeleted}
                        <li class="crm-contact-activity">
                            {include file="CRM/Contact/Form/ActionsButton.tpl"}
                        </li>
                        <li>
			{assign var='urlParams' value="reset=1&action=update&cid=$contactId"}
		        {if $searchKey}
		            {assign var='urlParams' value="reset=1&action=update&cid=$contactId&key=$searchKey"}
 		        {/if}
			{if $context}
			    {assign var='urlParams' value=$urlParams|cat:"&context=$context"}
			{/if}
			
                        <a href="{crmURL p='civicrm/contact/add' q=$urlParams}" class="edit button" title="{ts}Edit{/ts}">
                        <span><div class="icon edit-icon"></div>{ts}Edit{/ts}</span>
                        </a>
                        </li>
                        {/if}
                        
                        {if $groupOrganizationUrl}
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

        <div title="Summary" id="contact-summary" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
            {if $hookContentPlacement neq 3}
                
                {if $hookContent and $hookContentPlacement eq 2}
                    {include file="CRM/Contact/Page/View/SummaryHook.tpl"}
                {/if}
                
                {if $contact_type_label OR $current_employer_id OR $job_title OR $legal_name OR $sic_code OR $nick_name OR $contactTag OR $source}
                <div id="contactTopBar">
                    <table>
                        {if $contact_type_label OR $userRecordUrl OR $current_employer_id OR $job_title OR $legal_name OR $sic_code OR $nick_name}
                        <tr>
                            <td class="label">{ts}Contact Type{/ts}</td>
                            <td>{$contact_type_label}</td>
                            {if $current_employer_id}
                            <td class="label">{ts}Employer{/ts}</td>
                            <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$current_employer_id`"}" title="{ts}view current employer{/ts}">{$current_employer}</a></td>
                            {/if}
                            {if $job_title}
                            <td class="label">{ts}Position{/ts}</td>
                            <td>{$job_title}</td>
                            {/if}
                            {if $legal_name}
                            <td class="label">{ts}Legal Name{/ts}</td>
                            <td>{$legal_name}</td>
                            {if $sic_code}
                            <td class="label">{ts}SIC Code{/ts}</td>
                            <td>{$sic_code}</td>
                            {/if}
                            {elseif $nick_name}
                            <td class="label">{ts}Nickname{/ts}</td>
                            <td>{$nick_name}</td>
                            {/if}
                        </tr>
                        {/if}
                        {if $contactTag OR $userRecordUrl OR $source}
                        <tr>
                            {if $contactTag}
                            <td class="label" id="tagLink"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=$contactId&selectedChild=tag"}" title="{ts}Edit Tags{/ts}">{ts}Tags{/ts}</a></td><td id="tags">{$contactTag}</td>
                            {/if}
                            {if $userRecordUrl}
                            <td class="label">{ts}User ID{/ts}</td><td><a title="View user record" class="user-record-link" href="{$userRecordUrl}">{$userRecordId}</a></div>
                            {/if}
                            {if $source}
                            <td class="label">{ts}Source{/ts}</td><td>{$source}</td>
                            {/if}
                        </tr>
                        {/if}
                    </table>

                    <div class="clear"></div>
                </div><!-- #contactTopBar -->
                {/if}
                <div class="contact_details">
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                            <table>
                                {foreach from=$email key="blockId" item=item}
                                    {if $item.email}
                                    <tr>
                                        <td class="label">{$item.location_type}&nbsp;{ts}Email{/ts}</td>
                                        <td><span class={if $privacy.do_not_email}"do-not-email" title="{ts}Privacy flag: Do Not Email{/ts}" {elseif $item.on_hold}"email-hold" title="{ts}Email on hold - generally due to bouncing.{/ts}" {elseif $item.is_primary eq 1}"primary"{/if}><a href="mailto:{$item.email}">{$item.email}</a>{if $item.on_hold}&nbsp;({ts}On Hold{/ts}){/if}{if $item.is_bulkmail}&nbsp;({ts}Bulk{/ts}){/if}</span></td>
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
                                        <td class="label">{$item.website_type} {ts}Website{/ts}</td>
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
                        </div><!-- #contactCardLeft -->

                        <div class="contactCardRight">
                            {if $phone OR $im OR $openid}
                                <table>
                                    {foreach from=$phone item=item}
                                        {if $item.phone}
                                        <tr>
                                            <td class="label">{$item.location_type}&nbsp;{$item.phone_type}</td>
                                            <td {if $item.is_primary eq 1}class="primary"{/if}><span {if $privacy.do_not_phone} class="do-not-phone" title={ts}"Privacy flag: Do Not Phone"{/ts} {/if}>{$item.phone}</span></td>
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
                        </div><!-- #contactCardRight -->

                        <div class="clear"></div>
                    </div><!-- #contact_panel -->

					{if $address}
                    <div class="contact_panel">
                        {foreach from=$address item=add key=locationIndex}
                        <div class="{cycle name=location values="contactCardLeft,contactCardRight"} crm-address_{$locationIndex} crm-address-block crm-address_type_{$add.location_type}">
                            <table>
                                <tr>
                                    <td class="label">{ts 1=$add.location_type}%1&nbsp;Address{/ts}
                                        {if $config->mapAPIKey AND $add.geo_code_1 AND $add.geo_code_2}
                                            <br /><a href="{crmURL p='civicrm/contact/map' q="reset=1&cid=`$contactId`&lid=`$add.location_type_id`"}" title="{ts 1='&#123;$add.location_type&#125;'}Map %1 Address{/ts}"><span class="geotag">{ts}Map{/ts}</span></a>
                                        {/if}</td>
                                    <td>
                                        {if $sharedAddresses.$locationIndex.shared_address_display.name}
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
					            <tr><td class="label">{$customField.field_title}</td><td>{$customField.field_value}</td></tr>
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
					
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                            <table>
                                <tr><td class="label">{ts}Privacy{/ts}</td>
                                    <td><span class="font-red upper">
                                        {foreach from=$privacy item=priv key=index}
                                            {if $priv}{$privacy_values.$index}<br />{/if}
                                        {/foreach}
                                        {if $is_opt_out}{ts}No Bulk Emails (User Opt Out){/ts}{/if}
                                    </span></td>
                                </tr>
                                <tr>
                                    <td class="label">{ts}Preferred Method(s){/ts}</td><td>{$preferred_communication_method_display}</td>
                                </tr>
                                {if $preferred_language}
                                <tr>
                                    <td class="label">{ts}Preferred Language{/ts}</td><td>{$preferred_language}</td>
                                </tr>
                                {/if}
                                <tr>
                                    <td class="label">{ts}Email Format{/ts}</td><td>{$preferred_mail_format}</td>
                                </tr>
                            </table>
                        </div>

                        {include file="CRM/Contact/Page/View/Demographics.tpl"}
						
                        <div class="clear"></div>
                        <div class="separator"></div>
						
						<div class="contactCardLeft">
						{if $contact_type neq 'Organization'}
						 <table>
							<tr>
								<td class="label">{ts}Email Greeting{/ts}{if $email_greeting_custom}<br/><span style="font-size:8px;">({ts}Customized{/ts})</span>{/if}</td>
								<td>{$email_greeting_display}</td>
							</tr>
							<tr>
								<td class="label">{ts}Postal Greeting{/ts}{if $postal_greeting_custom}<br/><span style="font-size:8px;">({ts}Customized{/ts})</span>{/if}</td>
								<td>{$postal_greeting_display}</td>
							</tr>
						 </table>
						 {/if}
						</div>
						<div class="contactCardRight">
						 <table>
							<tr>
								<td class="label">{ts}Addressee{/ts}{if $addressee_custom}<br/><span style="font-size:8px;">({ts}Customized{/ts})</span>{/if}</td>
								<td>{$addressee_display}</td>
							</tr>
						 </table>
						</div>
						
                        <div class="clear"></div>
                    </div>
                </div><!--contact_details-->

                <div id="customFields">
                    <div class="contact_panel">
                        <div class="contactCardLeft">
                            {include file="CRM/Contact/Page/View/CustomDataView.tpl" side='1'}
                        </div><!--contactCardLeft-->

                        <div class="contactCardRight">
                            {include file="CRM/Contact/Page/View/CustomDataView.tpl" side='0'}
                        </div>

                        <div class="clear"></div>
                    </div>
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
                {if $hookContent and $hookContentPlacement eq 1}
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

{if $isAddressCustomPresent}
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
