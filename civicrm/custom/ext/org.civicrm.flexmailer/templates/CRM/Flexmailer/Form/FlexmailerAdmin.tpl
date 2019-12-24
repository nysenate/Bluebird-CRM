{crmScope extensionKey='org.civicrm.flexmailer'}
  <div class="crm-block crm-form-block crm-flexmailer-form-block">
    {*<div class="help">*}
    {*{ts}...{/ts} {docURL page="Debugging for developers" resource="wiki"}*}
    {*</div>*}
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <table class="form-layout">
      <tr class="crm-flexmailer-form-block-flexmailer_traditional">
        <td class="label">{$form.flexmailer_traditional.label}</td>
        <td>{$form.flexmailer_traditional.html}<br />
          <span class="description">
            {ts}For greater backward-compatibility, process "<code>traditional</code>" mailings with the CiviMail's hard-coded BAO.{/ts}<br/>
            {ts}For greater forward-compatibility, process "<code>traditional</code>" mailings with Flexmailer's extensible pipeline.{/ts}<br/>
          </span>
        </td>
      </tr>
    </table>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
  </div>
{/crmScope}
