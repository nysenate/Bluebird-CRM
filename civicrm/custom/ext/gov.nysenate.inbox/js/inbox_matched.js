CRM.$(function($) {
  //clear multiple
  $('.multi_clear').click(function() {
    // grab the rows to clear
    var clear_ids = $("input.message-select:checked").map(function(){
      return $(this).prop('id').replace('select-', '');
    }).get();

    if (!clear_ids.length) {
      CRM.alert('Use the checkbox to select one or more messages to clear.', 'Clear Messages', 'warn');
      return false;
    }

    CRM.confirm({
      title: 'Clear Messages?',
      message: 'Are you sure you want to clear ' + clear_ids.length + ' messages?'
    }).on('crmConfirm:yes', function() {
      var url = CRM.url('civicrm/nyss/inbox/clearmsgs', {ids: clear_ids});
      var request = $.post(url);
      CRM.status({success: 'Messages were successfully cleared.'}, request);

      refreshList();
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

    CRM.confirm({
      title: 'Process Messages?',
      message: 'Are you sure you want to process ' + process_ids.length + ' messages?'
    }).on('crmConfirm:yes', function() {
      var url = CRM.url('civicrm/nyss/inbox/processmsgs', {ids: process_ids});
      var request = $.post(url);
      CRM.status({success: 'Messages were successfully processed.'}, request);

      refreshList();
    });
  });

  function refreshList() {
    var range = $('#range_filter').val();
    var term = $('#search_filter').val();
    CRM.$('table.inbox-matchedmessages-selector').DataTable().ajax.
    url(CRM.url('civicrm/nyss/inbox/ajax/matched', {snippet: 4, range: range, term: JSON.stringify(term)})).load();
  }
});
