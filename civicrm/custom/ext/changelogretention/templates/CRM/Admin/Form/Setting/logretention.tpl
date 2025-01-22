<div class="crm-block crm-form-block crm-logretention-form-block">
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
 
<fieldset>
    <table class="form-layout">
        <tr class="crm-logretention-form-retention_period">
          <td class="label">{$form.retention_period.label}</td>
          <td>
            {$form.retention_period.html}
          </td>
        </tr>
        
         <tr class="crm-logretention-form-tables_excluded">
          <td class="label">{$form.tables_excluded.label}</td>
          <td>
            {$form.tables_excluded.html}
          </td>
        </tr>
   </table>
 
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</fieldset>
 
</div>