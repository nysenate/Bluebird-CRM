{literal}
<script type="text/javascript">
  //NYSS 8995 - revert in 4.6
  cj(document).ready(function(){
    var contactId = '{/literal}{$contactId}{literal}';
    var allCasesUrl = '{/literal}{crmURL p='civicrm/case/search' q='reset=1&force=1&context=caselist&cid='}{literal}' + contactId;
    cj('div.action-link').append('<a href="' + allCasesUrl + '" style="float:right;">View all cases for this contact</a>')});
</script>
{/literal}
