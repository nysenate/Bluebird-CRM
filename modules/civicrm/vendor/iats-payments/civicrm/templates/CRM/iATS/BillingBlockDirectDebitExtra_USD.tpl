{*
 Extra fields for iats direct debit, template for USD
*}
<div id="iats-direct-debit-extra">
  <div class="crm-section usd-instructions-section">
    <div class="label"><em>{ts domain='com.iatspayments.civicrm'}You can find your Bank Routing Number and Bank Account number by inspecting a check.{/ts}</em></div>
    <div class="content"><img width=500 height=303 src="{crmResURL ext=com.iatspayments.civicrm file=templates/CRM/iATS/USD_check_500x.jpg}"></div>
    <div class="clear"></div>
  </div>
  <div class="crm-section bank-account-type-section">
    <div class="label">{$form.bank_account_type.label}</div>
    <div class="content">{$form.bank_account_type.html}</div>
    <div class="clear"></div>
  </div>
</div>
{literal}<script type="text/javascript">iatsACHEFTRefresh()</script>{/literal}
