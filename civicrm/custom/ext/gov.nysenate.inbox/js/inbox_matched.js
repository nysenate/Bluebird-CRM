CRM.$(function($) {
  //clear multiple
  $('.multi_clear').click(function() {
    // grab the rows to clear

    var checked_boxes = $("input.message-select:checked").map(function () {
      return $(this).prop('id').replace('select-', '');
    }).get();

    // The checkbox values are now in the form <msg_id>-<contact_id>.
    // Split the values so we pass the right info to the handler.  We use
    // "new Set()" to get a list of unique values for each.
    var clear_ids = Array.from(new Set(checked_boxes.map(function(k){return k.split('-')[0];}))),
      clear_cids = Array.from(new Set(checked_boxes.map(function(k){return k.split('-')[1];})));

    if (!clear_ids.length) {
      CRM.alert('Use the checkbox to select one or more messages to clear.', 'Clear Messages', 'warn');
      return false;
    }

    CRM.confirm({
      title: 'Clear Messages?',
      message: 'Are you sure you want to clear ' + clear_ids.length +
      ' messages (affecting ' + clear_cids.length + ' contacts)?'
    }).on('crmConfirm:yes', function () {
      var url = CRM.url('civicrm/nyss/inbox/clearmsgs', {ids: clear_ids});
      var request = $.post(url);
      CRM.status({success: 'Messages were successfully cleared.'}, request);

      refreshList('matched');
    });
  });

  //process multiple
  $('.multi_process').click(function() {
    // grab the rows to delete
    var process_ids = $("input.message-select:checked").map(function(){
      return $(this).prop('id').replace('select-', '');
    }).get();

    if (!process_ids.length) {
      CRM.alert('Use the checkbox to select one or more messages to process.', 'Process Messages', 'warn');
      return false;
    }

    //TODO after processing multiple rows, if you change the filter it throws a datatables warning
    //this prevents the popup; functionality still works; but it is preferred if we determine the root cause
    $.fn.dataTableExt.sErrMode = "console";

    var url = CRM.url('civicrm/nyss/inbox/process', {ids: process_ids, multi: 1});
    CRM.loadForm(url)
      .on('crmFormSuccess', function(event, data) {
        //console.log('onFormSuccess event: ', event, ' data: ', data, 'this: ', this);

        if (data.isError) {
          CRM.status({success: data.message});
        }

        //TODO this works, but throws console errors
        if (data.status === 'success') {
          $(this).dialog('close');
        }

        refreshList('matched');
      })
      .on('crmFormCancel', function(event, data) {
        //console.log('crmFormCancel event: ', event);
        //TODO this works, but throws console errors
        $(this).dialog('close');
        refreshList('matched');
      });
  });

  //TODO we should NOT have to duplicate this from inbox.js, but can't properly reference
  //it without getting errors
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
