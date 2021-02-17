CRM.$(function ($) {
  if (CRM.vars.NYSS.cid) {
    $('input#file_on_case_unclosed_case_id').after('<a class="nyss-create-case button crm-popup" href="' + CRM.vars.NYSS.url +'"><span>Create</span></a>');

    $('body')
      .on('crmPopupFormSuccess', '.nyss-create-case', function() {
        //get last created case
        CRM.api3('Case', 'get', {
          "sequential": 1,
          "contact_id": 1,
          "options": {"limit":1,"sort":"id DESC"}
        }).then(function(result) {
          //console.log('id: ', result.id);
          $('#file_on_case_unclosed_case_id')
            .val(result.id)
            .attr('data-entity-value', '[{"id":' + result.id + ',"label":"' + result.subject + '"}]')
            .trigger('change');
        }, function(error) {
        });
      });
  }
});