CRM.$(function($) {
  $("#mainTabContainer").tabs( {active: 0} );
  var contact_id = CRM.vars.NYSS.matched_to;
  $('#contact_positions').on('select2-selecting', function(e) {
    CRM.api3('nyss_tags', 'savePosition', {value:e.val, contactId:contact_id}, false);
  });
});
