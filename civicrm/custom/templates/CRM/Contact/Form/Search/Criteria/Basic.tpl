{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
<table class="form-layout">
<tr>
    <td valign="top">
        Search Contacts<br />
        {$form.sort_name.html}
            <div class="description font-italic">
                {ts}Complete OR partial Contact Name.{/ts}
            </div>
            {$form.email.html}
            <div class="description font-italic">
                {ts}Complete OR partial Email Address.{/ts}
            </div>
            
    </td>
    <td valign="top">
    {if $form.group}
            <label>{ts}Group(s){/ts}</label>
                {$form.group.html}
                {literal}
                <script type="text/javascript">
                cj("select#group").crmasmSelect({
                    addItemTarget: 'bottom',
                    animate: false,
                    highlight: true,
                    sortable: true,
                    respectParents: true
                });

                </script>
                {/literal}
    {/if}
    
    </td>
    <td valign="top">
    {if $form.contact_tags}
    <label>{ts}Issue Codes{/ts}</label>
                {$form.contact_tags.html}
                {literal}
                <script type="text/javascript">

                cj("select#contact_tags").crmasmSelect({
                    addItemTarget: 'bottom',
                    animate: false,
                    highlight: true,
                    sortable: true,
                    respectParents: true
                });

                </script>
                {/literal}   
    {/if}
    
    </td>
    <td>
    <div style="display:block;">{$form.buttons.html}{help id='id-advanced-intro'}</div>
    <a href="/civicrm/contact/search/advanced&reset=1" class="resetbutton" style="float:left;margin-left:0;"><span>Reset Form</span></a>
    </td>
</tr>
<tr>
    <td>
    <div id="locationSection"></div>
    <div id="locationSectionHidden"></div>
              
    </td> 
    <td>
    {if $form.contact_type}
            <label>{ts}Contact Type(s){/ts}</label><br />
                {$form.contact_type.html}
                 {literal}
					<script type="text/javascript">

								cj("select#contact_type").crmasmSelect({
									addItemTarget: 'bottom',
									animate: false,
									highlight: true,
									sortable: true,
									respectParents: true
								});

						</script>
					{/literal}
    {/if}
    {$form.contact_source.label}<br />
    {$form.contact_source.html}<br />
    {$form.job_title.label}<br />
    {$form.job_title.html}
    <br />
    {$form.preferred_communication_method.label}<br />
    {$form.preferred_communication_method.html}<br />
    {$form.email_on_hold.html} {$form.email_on_hold.label}
    </td>
    <td colspan="2">
        {include file="CRM/common/Tag.tpl"}
        <br />
        {$form.privacy.label}<br />
        {$form.privacy.html} {help id="id-privacy"}
    </td>
</tr>
<tr>       
    <td></td>
    <td>
    </td>
    </tr>
</table>
<div style="display:none">
  {$form.uf_group_id.label} {$form.uf_group_id.html}
                <br /><br />
                <div class="form-item">
                    {if $form.uf_user}{$form.uf_user.label} {$form.uf_user.html}
                    <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('uf_user', 'Advanced'); return false;" >{ts}clear{/ts}</a>)</span>

                    <div class="description font-italic">
                        {ts 1=$config->userFramework}Does the contact have a %1 Account?{/ts}
                    </div>
                    {/if}
                </div>
</div>
