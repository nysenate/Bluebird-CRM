CRM.$(function($) {
  $(document).ready(function() {
    //when a leg position is selected, we may need to add it to the tag table
    $('#contact_taglist_292').on('select2-selecting', function(e) {
      //console.log('e: ', e);

      var contactId = null;
      if (typeof CRM.vars.NYSS !== 'undefined') {contactId = CRM.vars.NYSS.contactId;}
      //console.log('contactId: ', contactId);

      CRM.api3('nyss_tags', 'savePosition', {value:e.val, contactId:contactId}, false)
        .done(function(result) {
        });
    });
  });
});
