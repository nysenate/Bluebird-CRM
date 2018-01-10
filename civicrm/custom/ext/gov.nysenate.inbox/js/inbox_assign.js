CRM.$(function($) {
  //11623 hackish fix for hiding our dummy text field for misdirecting autofocus
  $('input[name=trick_autofocus]').parents('div.crm-section').css('visibility', 'hidden').css('height', 0);
  $('input[name=matches]').parents('div.crm-section').css('clear', 'both');

  displayEmails();
  $('#matches').change(function(){
    displayEmails();
  });

  function displayEmails() {
    var ids = $('#matches').val().split(',');
    //console.log(ids);

    for (var i = 0; i < ids.length; i++) {
      var id = ids[i];
      if (id.length > 0 && $('div#cid-' + id).length === 0) {
        var oddeven = (i % 2 === 0) ? 'odd' : '';
        var contact = CRM.api3('contact', 'getsingle', {id: id})
          .done(function (result) {
            //console.log(result);
            var value = '';
            if (result.email.length > 0) {
              value = 'value="' + result.email + '" ';
            }
            $('div#match-emails .content').append('<div id="cid-' + id + '" class="match-details ' +
              oddeven + '"><span class="match-sort_name">' + result.sort_name +
              '</span><input type="text" name="email-' + id + '" ' + value +
              'placeholder="email"><input type="hidden" name="emailorig-' + id + '" value="' + result.email + '"></div>');
          });
      }
    }

    //account for remove of specific values
    $('div.match-details').each(function(){
      var id = $(this).prop('id').replace('cid-', '');
      if ($.inArray(id, ids) === -1) {
        $('div#cid-' + id).remove();
      }
    });

    //account for removal of all matched contacts (cleanup edge case)
    if (ids.length === 0 || (ids.length === 1 && ids[0] === '')) {
      $('div.match-details').remove();
    }
  }

  $('span.email_address').click(function(){
    if ($('#match-emails div.match-details').length === 1) {
      var email = $(this).text();
      $('.match-details input[type=text]').val(email);
    }
    else {
      //TODO if we have more than one match, how do we pass selection?...
    }
  });
});
