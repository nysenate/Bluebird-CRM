CRM.$(function($) {
  //select all action
  $('.select-all').click(function(){
    if ($(this).is(":checked")) {
      $('.message-select').each(function(){
        $(this).prop('checked', true);
      });
    }
    else {
      $('.message-select').each(function(){
        $(this).prop('checked', false);
      });
    }
  });

  //delete multiple
  $('.multi_delete').click(function() {
    // grab the rows to delete
    var delete_ids = $("input.message-select:checked").map(function(){
      return $(this).prop('id').replace('select-', '');
    }).get();
    //console.log('delete_ids: ', delete_ids);

    if (!delete_ids.length) {
      CRM.alert('Use the checkbox to select one or more messages to delete', '', 'warn');
      return false;
    }

    //cj("#loading-popup").dialog('open');
    //cj("#delete-confirm").dialog({ title:  "Delete "+delete_ids.length+" messages from Bluebird?"});
    //cj("#delete-confirm #message").val(delete_ids);
    //cj("#loading-popup").dialog('close');
    //cj("#delete-confirm").dialog('open');
  });

  $('.inbox-delete, .inbox-assign-contact')
    .on('crmPopupFormSuccess.crmLivePage', CRM.refreshParent);

});
