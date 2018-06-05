CRM.$(function($) {
  var inboxType = CRM.vars.NYSS.inboxType,
      refreshTimer = null;

  //redraw table when range filter changes
  $('#range_filter').change(function(){
    refreshList(inboxType);
  });

  //redraw table when search filter triggered
  $('#search_filter').keyup(function (e) {
    if (e.key.length == 1 || e.key == 'Backspace') {
      if (refreshTimer) {
        window.clearTimeout(refreshTimer);
      }
      refreshTimer = window.setTimeout(function () {
        refreshList(inboxType);
      }, 250);
    }
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
      var handle_msg = function(d) { return d.msg; },
        handlers = {success: handle_msg, error: handle_msg},
        opts = {
          url: CRM.url('civicrm/nyss/inbox/deletemsgs', {ids: delete_ids}),
          dataType: 'json',
          complete: function (xhr, status) {
            refreshList(inboxType);
          }
        };
      CRM.status(handlers, $.post(opts));
    });
  });

  $('body')
    .on('crmPopupFormSuccess', '.inbox-delete, .inbox-assign-contact, .inbox-clear-contact, .inbox-process-contact', function() {
      refreshList(inboxType);
    });

  /**
   *
   * @param inboxType
   *
   * refresh the listing, retaining filter options;
   */
  function refreshList(inboxType) {
    var range = $('#range_filter').val();
    var term = $('#search_filter').val();
    //console.log('refreshList:: inboxType: ', inboxType, ' range: ', range, ' term: ', term);

    CRM.$('table.inbox-messages-selector').DataTable().ajax.url(CRM.url('civicrm/nyss/inbox/ajax/' + inboxType, {
      snippet: 4,
      range: range,
      term: JSON.stringify(term)
    })).load();
  }
});
