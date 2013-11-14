{literal}
<script type="text/javascript">
  //5779 truncate file name
  cj('.crm-fileURL a').each(function(){
    var title = cj(this).text();
    if ( title.length > 30 ) {
      var short = cj.trim(title).substring(0, 30).slice(0, -1) + "...";
      cj(this).text(short);
    }
  });
</script>
{/literal}

{include file="CRM/Contact/Page/nyssInlineCommon.tpl"}
