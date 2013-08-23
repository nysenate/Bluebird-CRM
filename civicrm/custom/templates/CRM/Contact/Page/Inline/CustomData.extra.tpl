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

  //5183 move ethnicity fields
  var ethnicity, ethnicityOther;
  cj('#custom-set-block-1 div.bb-row-wrap').each(function(){
    //console.log(this);
    //console.log(cj(this).children('div.crm-label'));
    if ( cj(this).children('div.crm-label').text() == 'Ethnicity' ) {
      ethnicity = cj(this).children('div.crm-custom-data').text();
      cj(this).remove();
    }
    if ( cj(this).children('div.crm-label').text() == 'Other Ethnicity' ) {
      ethnicityOther = cj(this).children('div.crm-custom-data').text();
      cj(this).remove();
    }
  });
  if ( ethnicity || ethnicityOther ) {
    var ethVal = ethnicity;
    if ( ethnicity && ethnicityOther ) {
      ethVal = ethnicity + '<br />';
    }
    ethVal = ethVal + ethnicityOther;
    //console.log('e: ', ethVal);

    //now move above
    if ( cj('div#contactTopBar tr.row-ethnicity').length ) {
      cj('div#contactTopBar tr.row-ethnicity td.content').html(ethVal);
    }
    else {
      var ethHtml = '<tr class="row-ethnicity"><td class="label">Ethnicity</td><td class="content">' +
        ethVal + '</td></tr>';
      cj('div#contactTopBar div.contactCardRight table').prepend(ethHtml);
    }
  }
  else {
    cj('div#contactTopBar tr.row-ethnicity').remove();
  }

</script>
{/literal}
