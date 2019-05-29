{crmScope extensionKey='uk.co.vedaconsulting.mosaico'}
<div class="crm-block crm-form-block crm-mosaico-form-block">
  {*<div class="help">*}
  {*{ts}...{/ts} {docURL page="Debugging for developers" resource="wiki"}*}
  {*</div>*}
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
  <table class="form-layout">
    <tr class="crm-mosaico-form-block-mosaico_layout">
      <td class="label">{$form.mosaico_layout.label}</td>
      <td>{$form.mosaico_layout.html}<br />
        <span class="description">{ts}How should the CiviMail composition screen look?{/ts}</span>
      </td>
    </tr>
    <tr class="crm-mosaico-form-block-mosaico_graphics">
      <td class="label">{$form.mosaico_graphics.label}</td>
      <td>
        {$form.mosaico_graphics.html}<br/>
        <span class="description">{ts}Which backend should process images?{/ts}</span>
      </td>
    </tr>
    <tr class="crm-mosaico-form-block-mosaico_custom_templates_dir">
      <td class="label">
        {$form.mosaico_custom_templates_dir.label}
      </td>
      <td>
        {$form.mosaico_custom_templates_dir.html|crmAddClass:'huge40'}
      </td>
    </tr>
    <tr class="crm-mosaico-form-block-mosaico_custom_templates_url">
      <td class="label">
        {$form.mosaico_custom_templates_url.label}
      </td>
      <td>
        {$form.mosaico_custom_templates_url.html|crmAddClass:'huge40'}
      </td>
    </tr>
  </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
{/crmScope}
