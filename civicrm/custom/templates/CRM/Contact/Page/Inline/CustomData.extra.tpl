{literal}
<script type="text/javascript">
  //5638 custom data
  if ( cj('#custom-set-block-1').length ) {
    var custLink1 = cj('#custom-set-block-1 .crm-config-option a').html().replace('add or edit custom set', 'add or edit constituent information');
    cj('#custom-set-block-1 .crm-config-option a').html(custLink1);
  }

  if ( cj('#custom-set-block-3').length ) {
    var custLink3 = cj('#custom-set-block-3 .crm-config-option a').html().replace('add or edit custom set', 'add or edit constituent information');
    cj('#custom-set-block-3 .crm-config-option a').html(custLink3);
  }

  var custLink2 = cj('#custom-set-block-5 .crm-config-option a').html().replace('add or edit custom set', 'add or edit attachments');
  cj('#custom-set-block-5 .crm-config-option a').html(custLink2);

  //5637 reduce block width after returning from inline form
  cj('#custom-set-block-1 #crm-container-snippet').css('width','auto');
  cj('#custom-set-block-3 #crm-container-snippet').css('width','auto');
  cj('#custom-set-block-5 #crm-container-snippet').css('width','auto');

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
