{*
 Extra fields for iATS secure SWIPE
*}

<div id="iats-swipe">
      <div class="crm-section cad-instructions-section">
        <div class="label"><em>{ts domain='com.iatspayments.civicrm'}Get ready to SWIPE! Place your cursor in the Encrypted field below and swipe card.{/ts}</em></div>
        {capture assign="reader_image"}{crmResURL ext="com.iatspayments.civicrm" file="templates/CRM/Iats/usb_reader.jpg"}{/capture}
        <div class="content"><img width=220 height=220 src="{$reader_image}"></div>
        <div class="clear"></div>
      </div>
</div>
{capture assign="swipeJsFile"}{crmResURL ext="com.iatspayments.civicrm" file="js/swipe.js"}{/capture}
<script type="text/javascript">
  var swipeJs = "{$swipeJsFile}";
{literal}
  CRM.$(function ($) {
    $.getScript(swipeJs);
  });
{/literal}
</script>
