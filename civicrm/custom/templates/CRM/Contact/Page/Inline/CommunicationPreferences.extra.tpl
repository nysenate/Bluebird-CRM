{if !empty($inCommPref.custom_64)}
<div class="bb-row-wrap" id="wrap-privacynote">
  <div class="crm-label">{ts}Privacy Note{/ts}</div>
  <div class="crm-content crm-contact-privacynote">{$inCommPref.custom_64}</div>
</div>
{/if}

{literal}
<script type="text/javascript">
  cj('#crm-communication-pref-content .crm-config-option').next('.bb-row-wrap').after(cj('#wrap-privacynote'));
  cj('#crm-communication-pref-content div').removeClass('upper');
</script>
{/literal}

{*5412*}
{if $privacy.do_not_mail || $privacy.do_not_trade}
  {literal}
  <script type="text/javascript">
    if ( cj('span.do-not-mail').length == 0 ) {
      if (  cj('div.crm-address-block div.crm-content span.adr').length ) {
        cj('div.crm-address-block div.crm-content span.adr').wrapInner('<span class="do-not-mail" />');
      }
      else {
        cj('div.crm-address-block div.crm-content').wrapInner('<span class="do-not-mail" />');
      }
    }
  </script>
  {/literal}
{else}
  {literal}
  <script type="text/javascript">
    cj('span.do-not-mail').removeClass('do-not-mail');
  </script>
  {/literal}
{/if}
