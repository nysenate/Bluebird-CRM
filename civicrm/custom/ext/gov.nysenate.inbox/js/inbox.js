CRM.$(function($) {
  //redraw table when range filter changes
  CRM.$('#range_filter').change(function(){
    refreshList();
  });

  //redraw table when search filter triggered
  CRM.$('#search_filter').keypress(function(){
    refreshList();
  });

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

    if (!delete_ids.length) {
      CRM.alert('Use the checkbox to select one or more messages to delete.', 'Delete Messages', 'warn');
      return false;
    }

    CRM.confirm({
      title: 'Permanently Delete Messages?',
      message: 'Are you sure you want to permanently remove ' + delete_ids.length + ' messages?'
    }).on('crmConfirm:yes', function() {
      var url = CRM.url('civicrm/nyss/inbox/deletemsgs', {ids: delete_ids});
      var request = $.post(url);
      CRM.status({success: 'Messages were successfully deleted.'}, request);

      refreshList();
    });
  });

  $('.inbox-delete, .inbox-assign-contact, .inbox-clear-contact, .inbox-process-contact')
    .on('crmPopupFormSuccess.crmLivePage', CRM.refreshParent);
});
