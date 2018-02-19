CRM.$(function($) {
  //11623 hackish fix for hiding our dummy text field for misdirecting autofocus
  $('input[name=trick_autofocus]').parents('div.crm-section').css('visibility', 'hidden').css('height', 0);
  $('input[name=matches]').parents('div.crm-section').css('clear', 'both');

  displayEmails();
  $('#matches').change(function(){
    displayEmails();
    $('.click-message').show();
  });

  function displayEmails() {
    var ids = $('#matches').val().split(',');
    var summaryOverlayProfileId = CRM.vars.NYSS.summaryOverlayProfileId;
    //console.log(ids);

    for (var i = 0; i < ids.length; i++) {
      var id = ids[i];
      if (id.length > 0 && $('div#cid-' + id).length === 0) {
        var contact = CRM.api3('contact', 'getsingle', {id: id})
          .done(function (result) {
            //console.log(result);
            var value = '';
            if (result.email.length > 0) {
              value = 'value="' + result.email + '" ';
            }
            var url = CRM.url("civicrm/contact/view", {reset: 1, cid: result.contact_id});
            var summaryUrl = CRM.url("civicrm/profile/view", {reset: 1, gid: summaryOverlayProfileId, id: result.contact_id, snippet: 4});
            $('div#match-emails').append('<div class="match-details-email-row" id="cid-' + result.contact_id +
              '"><div class="label match-sort_name match-details"><a href="' + summaryUrl +
              '" class="crm-summary-link"><i class="crm-i fa-list-alt"></i></a>&nbsp;<a href="' +
              url + '" target="_blank">' +
              result.sort_name + '</a></div>' +
              '<div class="match-details content"><input type="text" name="email-' +
              result.contact_id + '" ' + value +
              'placeholder="email"><input type="hidden" name="emailorig-' + result.contact_id + '" value="' +
              result.email + '"></div>' +
              '<div class="clear"></div></div>'
            );
          });
      }
    }

    //account for remove of specific values
    $('div.match-details-email-row').each(function(){
      var id = $(this).prop('id').replace('cid-', '');
      if ($.inArray(id, ids) === -1) {
        $('div#cid-' + id).remove();
      }
    });

    //account for removal of all matched contacts (cleanup edge case)
    if (ids.length === 0 || (ids.length === 1 && ids[0] === '')) {
      $('div.match-details-email-row').remove();
    }
  }

  $('span.email_address').click(function(){
    if ($('#match-emails div.match-details-email-row').length === 1) {
      var email = $(this).text();
      $('.match-details input[type=text]').val(email);
    }
    else {
      //TODO if we have more than one match, how do we pass selection?...
    }
  });
});
