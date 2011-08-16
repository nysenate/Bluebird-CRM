<tr>
  <td colspan="2">
    {$form.mailing_name.label}
    <br />
    {$form.mailing_name.html}
  </td>
</tr>
<tr>
  <td>
    {$form.mailing_date_low.label} 
    <br />
    {include file="CRM/common/jcalendar.tpl" elementName=mailing_date_low}
  </td>
  <td>
    {$form.mailing_date_high.label}
    <br />
    {include file="CRM/common/jcalendar.tpl" elementName=mailing_date_high}
  </td>
</tr>
<tr>
  <td>
    {$form.mailing_delivery_status.label}
    <br />
    {$form.mailing_delivery_status.html}
    <span class="crm-clear-link">(<a href="javascript:unselectRadio('mailing_delivery_status','{$form.formName}')">{ts}clear{/ts}</a>)</span>
  </td>
  <td>
    {$form.mailing_open_status.label}
    <br />
    {$form.mailing_open_status.html}
    <span class="crm-clear-link">(<a href="javascript:unselectRadio('mailing_open_status','{$form.formName}')">{ts}clear{/ts}</a>)</span>
  </td>
</tr>
<tr>
  <td>
    {$form.mailing_click_status.label}
    <br />
    {$form.mailing_click_status.html}
    <span class="crm-clear-link">(<a href="javascript:unselectRadio('mailing_click_status','{$form.formName}')">{ts}clear{/ts}</a>)</span>
  </td>
  <td>
    {$form.mailing_reply_status.label}
    <br />
    {$form.mailing_reply_status.html}
    <span class="crm-clear-link">(<a href="javascript:unselectRadio('mailing_reply_status','{$form.formName}')">{ts}clear{/ts}</a>)</span>
  </td>
</tr>
