{*5412*}
{if $privacy.do_not_trade}
  {literal}
  <script type="text/javascript">
    if ( cj('div.crm-address-block div.crm-label span.do-not-mail').length == 0 ) {
      cj('div.crm-address-block div.crm-label').
        append('<span class="icon privacy-flag do-not-mail" title="Privacy flag: Do Not Mail"></span>');
    }
  </script>
  {/literal}
{else}
  {literal}
  <script type="text/javascript">
    cj('span.do-not-mail').remove();
  </script>
  {/literal}
{/if}

{include file="CRM/Contact/Page/nyssInlineCommon.tpl"}
