CRM.$(function($) {
  function refreshList() {
    var range = $('#range_filter').val();
    var term = $('#search_filter').val();
    CRM.$('table.inbox-unmatchedmessages-selector').DataTable().ajax.
    url(CRM.url('civicrm/nyss/inbox/ajax/unmatched', {snippet: 4, range: range, term: JSON.stringify(term)})).load();
  }
});
