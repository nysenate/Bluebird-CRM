<div id="inline-privacy-notes">
  <div class="crm-label">
    <label>Privacy Note</label>
  </div>
  <div class="crm-content">
    {$form.custom_64.html}
  </div>
</div>

{literal}
<script type="text/javascript">
  //privacy note
  cj('input#is_opt_out').parent('.crm-content').after(cj('#inline-privacy-notes'));
</script>
{/literal}
