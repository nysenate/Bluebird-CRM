cj(document).ready(function(){
  cj('a.crm-reset-builder-row').after('<span class="nyss-builder-op">(AND)</span>');
  cj('div.crm-search-block h3:not(:first)').append('&nbsp;<span class="nyss-builder-op">(OR)</span>');

  //TODO shouldn't really need to do this... not sure where it's broken by our customizations
  cj('select[id^=operator_]').change(function(){
    if (cj(this).val()) {
      cj(this).next('span.crm-search-value').show();
    }
  });
});
