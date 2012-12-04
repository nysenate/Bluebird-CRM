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
