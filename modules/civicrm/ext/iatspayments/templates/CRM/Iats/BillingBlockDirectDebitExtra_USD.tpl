{*
 Extra fields for iats direct debit, template for USD
*}

<div id="iats-direct-debit-extra">
  <div class="crm-section usd-instructions-section">
    <div class="label"><em>{ts domain='com.iatspayments.civicrm'}You can find your Bank Routing Number and Bank Account number by inspecting a check.{/ts}</em></div>
    {capture assign="CheckImage"}{crmResURL ext="com.iatspayments.civicrm" file="templates/CRM/Iats/USD_check_500x.jpg"}{/capture}
    <div class="content"><img width="500" height="303" src="{$CheckImage}"></div>
    <div class="clear"></div>
  </div>
</div>
{capture assign="acheftjs"}{crmResURL ext="com.iatspayments.civicrm" file="js/dd_acheft.js"}{/capture}
<script type="text/javascript">
  {literal}
  CRM.$(function ($) {
    var ddAcheftJs = {/literal}"{$acheftjs}"{literal};
    $.getScript(ddAcheftJs);
  });
  {/literal}
</script>
