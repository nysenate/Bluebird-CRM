<div class="help"><span>Enabling this setting will whitelist all emails configured at "Administer > Communications > FROM Email Addresses" so that they can be used as valid FROM email addresses across various forms on the website. Please ensure your server's SPF policy is updated to allow sending emails using these email addresses.</span></div>
<div class="crm-container crm-block crm-form-block crm-twilio-form-block">
<table class="form-layout">
  <tbody>
    <tr>
     <td class="label">
       {$form.ode_from_allowed.label}
     </td>
     <td>
       {$form.ode_from_allowed.html}
     </td>
    </tr>
  </tbody>
</table> 
   <div class="crm-submit-buttons">
     {include file="CRM/common/formButtons.tpl" location="bottom"}
   </div>
</div>
