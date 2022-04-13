{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{* tpl for building Organization related fields *}
<table class="form-layout-compressed">
  <tr>
    <td>{
      $form.organization_name.label}<br/>
      {$form.organization_name.html}
    </td>
    <td>
      {$form.legal_name.label}<br/>
      {$form.legal_name.html}
    </td>
    <td>
      {$form.nick_name.label}<br/>
      {$form.nick_name.html}
    </td>

       {*NYSS*}{*
       <td>{$form.sic_code.label}<br/>
       {$form.sic_code.html}</td>
       *}

      {*NYSS*}
       {*<td>{if $action == 1 and $contactSubType}&nbsp;{else}
              {$form.contact_sub_type.label}<br />
              {$form.contact_sub_type.html}
           {/if}
       </td>*}
    
    <td>
      {assign var='custom_41' value=$groupTree.3.fields.41.element_name}
      {$form.$custom_41.label}<br/>
      {$form.$custom_41.html}
    </td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
      {assign var='custom_26' value=$groupTree.3.fields.26.element_name}
      {$form.$custom_26.label}<br/>
      {$form.$custom_26.html|crmReplace:class:"big crm-form-text"}
    </td>
    <td>
      {assign var='custom_25' value=$groupTree.3.fields.25.element_name}
      {$form.$custom_25.label}<br/>
      {$form.$custom_25.html|crmReplace:class:"big crm-form-text"}
    </td>
    <td>
      {$form.sic_code.label}<br/>
      {$form.sic_code.html|crmReplace:class:"big crm-form-text"}
    </td>
    <td>
      {$form.contact_source.label}<br />
      {$form.contact_source.html|crmReplace:class:"big crm-form-text"}
     </td>
    <td>
      {$form.external_identifier.label}<br />
      {$form.external_identifier.value}
    </td>
    <td>
      <label for="internal_identifier">{ts}Internal Id{/ts}</label><br />
      {$contactId}
    </td>
  </tr>
</table>

{literal}
<script type="text/javascript">
  //NYSS 7303
  cj('label[for=organization_name]').append(' <span class="crm-marker" title="This field is required.">*</span>');
</script>
{/literal}
