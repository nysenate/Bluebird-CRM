{if !empty($inDemo.current_employer_id)}
<div class="bb-row-wrap" id="wrap-currentemployer">
  <div class="crm-label">{ts}Employer{/ts}</div>
  <div class="crm-content crm-contact-current_employer"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$inDemo.current_employer_id`"}" title="{ts}view current employer{/ts}">{$inDemo.current_employer}</a></div>
</div>
{/if}

{if !empty($inDemo.job_title)}
<div class="bb-row-wrap" id="wrap-jobtitle">
  <div class="crm-label">{ts}Job Title{/ts}</div>
  <div class="crm-content crm-contact-job_title">{$inDemo.job_title}</div>
</div>
{/if}

{if !empty($inDemo.custom_63)}
<div class="bb-row-wrap" id="wrap-religion">
  <div class="crm-label">{ts}Religion{/ts}</div>
  <div class="crm-content crm-contact-religion">{$inDemo.custom_63}</div>
</div>
{/if}

{literal}
<script type="text/javascript">
cj('#crm-demographic-content .bb-row-wrap').each(function(){
  if ( cj(this).children('.crm-label').text() == 'Gender' ) {
    cj(this).remove();
  }
});
cj('#crm-demographic-content .crm-config-option').after(cj('#wrap-jobtitle')).after(cj('#wrap-currentemployer'));
cj('#crm-demographic-content .crm-clear').append(cj('#wrap-religion'));
</script>
{/literal}
