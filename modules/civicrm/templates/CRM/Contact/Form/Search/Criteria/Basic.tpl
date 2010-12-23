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
	<table class="form-layout">
		<tr>
            <td><label>{ts}Complete OR Partial Name{/ts}</label>&nbsp;{help id='id-advanced-intro'}<br />
                {$form.sort_name.html|crmReplace:class:big}
            </td>
            <td>
                <label>{ts}Complete OR Partial Email{/ts}</label><br />
                {$form.email.html|crmReplace:class:medium}
            </td>
            <td>
                {if $form.component_mode}  
                    {$form.component_mode.label} {help id="id-display-results"}<br />{$form.component_mode.html}
                {else}
                    &nbsp;
                {/if}
            </td>
            <td>
                {$form.uf_group_id.label} {help id="id-search-views"}<br />{$form.uf_group_id.html}
            </td>
            <td class="label"><div class="crm-submit-buttons" style="margin-top:1em;">{$form.buttons.html}</div></td>       
        </tr>
		<tr>
{if $form.contact_type}
            <td><label>{ts}Contact Type(s){/ts}</label><br />
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
            </td>
{else}
            <td>&nbsp;</td>
{/if}
{if $form.group}
            <td><label>{ts}Group(s){/ts}</label>
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
            </td>
{else}
            <td>&nbsp;</td>
{/if}

{if $form.contact_tags}
            <td colspan="3"><label>{ts}Tag(s){/ts}</label>
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
            </td>
{else}
            <td colspan="3">&nbsp;</td>
{/if}
	    </tr>
        <tr>
            <td>
            {if $form.uf_user}
                {$form.uf_user.label} {$form.uf_user.html} <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('uf_user', 'Advanced'); return false;" >{ts}clear{/ts}</a>)</span>
                <div class="description font-italic">
                    {ts 1=$config->userFramework}Does the contact have a %1 Account?{/ts}
                </div>
            {else}
                &nbsp;
            {/if}
            </td>
            <td>
                {$form.preferred_language.label}<br />
                {$form.preferred_language.html|crmReplace:class:medium}
            </td> 
        </tr>
        <tr>
            <td colspan="2">
                {$form.privacy.label}<br />
                {$form.privacy.html} {help id="id-privacy"}
            </td>
            <td colspan="3">
                {$form.preferred_communication_method.label}<br />
                {$form.preferred_communication_method.html}<br />
                <div class="spacer"></div>
                {$form.email_on_hold.html} {$form.email_on_hold.label}
            </td>
        </tr>
        <tr>
            <td>
                {$form.contact_source.label}<br />
                {$form.contact_source.html|crmReplace:class:medium}
            </td>
            <td>
                {$form.job_title.label}<br />
                {$form.job_title.html|crmReplace:class:medium}
            </td>
            <td colspan="3">
                {if $form.deleted_contacts}<br />{$form.deleted_contacts.html} {$form.deleted_contacts.label}{else}&nbsp;{/if}
            </td>
        </tr>
        <tr><td colspan="5">{include file="CRM/common/Tag.tpl"}</td></tr>
    </table>
