{literal}
<script type="text/javascript">
  //7367 update display name after inline edit
  var displayName = cj('div.crm-summary-display_name').text();
  var titleIcon = cj('div.crm-title h1.title a').html();
  cj('div.crm-title h1.title').html(titleIcon + displayName);
</script>
{/literal}
