{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{* tpl for building Individual related fields *}
{*NYSS*}
{assign var=formtextbig value='crm-form-text big'}
<table class="form-layout-compressed individual-contact-details">
  <tr>
    {if !empty($form.prefix_id)}
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
    {if !empty($form.first_name)}
    <td>
      {$form.first_name.label}<br />
      {$form.first_name.html}
    </td>
    {/if}
    {if !empty($form.middle_name)}
    <td>
      {$form.middle_name.label}<br />
      {$form.middle_name.html}
    </td>
    {/if}
    {if !empty($form.last_name)}
    <td>
      {$form.last_name.label}<br />
      {$form.last_name.html}
    </td>
    {/if}
    {if !empty($form.suffix_id)}
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
      <label for="internal_identifier">{ts}Internal Id{/ts}</label>/{$form.external_identifier.label}<br />
      {$contactId}{if $form.external_identifier.value}/{$form.external_identifier.value}{/if}
    </td>
  </tr>
</table>
