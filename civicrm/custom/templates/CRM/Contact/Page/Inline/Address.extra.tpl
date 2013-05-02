{literal}
<script type="text/javascript">

  //5637 reduce block width after returning from inline form
  cj('div[id=^address-block] #crm-container-snippet').css('width','auto');
  cj('div[id=^address-block] .crm-accordion-wrapper').removeClass('crm-accordion-open').addClass('crm-accordion-closed');

  //5785 make sure append add link only once
  if (cj('.appendAddLink').length > 1) {
    cj('.appendAddLink:last').remove();
  }
</script>
{/literal}
