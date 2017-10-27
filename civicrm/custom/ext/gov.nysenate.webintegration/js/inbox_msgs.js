CRM.$(function($) {
  CRM.$('table.contact-unmatchedmessages-selector').data({
    "ajax": {
      "url": CRM.url('civicrm/nyss/ajax/unmatchedmessages', {snippet: 4})
    }
  });
  $(function($) {
    //retain in case we are asked to implement filters
    /*$('.unmatchedmessages-search-options :input').change(function(){
      CRM.$('table.contact-unmatchedmessages-selector').DataTable().draw();
    });*/
  });

  $('table.contact-unmatchedmessages-selector').
  on('click', '.view-msg', function(){
    var msgId = $(this).prop('id').replace('view-msg-', '');

    $('#msg-' + msgId).dialog({
      width: 500
    }).dialog('open');

    return false;
  }).
  on('click', '.create-activity', function(){
    var msgId = $(this).prop('id').replace('create-activity-', '');
    var contactId = $(this).attr('contact_id');

    //determine the activity type ID
    var msgType = $('table.contact-unmatchedmessages-selector tr#' + msgId + ' span.msg-type').text();
    var msgTypeId = 0;
    switch (msgType) {
      case 'Direct':
        msgTypeId = CRM.vars.NYSS.unmatched_activity_direct;
        break;
      case 'Contextual':
        msgTypeId = CRM.vars.NYSS.unmatched_activity_contextual;
        break;
      default:
    }

    var url = '/civicrm/activity/add?reset=1&action=add&atype=' + msgTypeId + '&cid=' + contactId + '&msgId=' + msgId;

    CRM.loadForm(url).
    on('crmFormSuccess', function(event, data){
      //redraw so list is reloaded
      CRM.$('table.contact-unmatchedmessages-selector').DataTable().draw();
    });

    return false;
  });
});
