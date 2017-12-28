CRM.$(function($) {
  $('#matches').change(function(){
    var ids = $(this).val().split(',');
    //console.log(ids);

    for (var i = 0; i < ids.length; i++) {
      var id = ids[i];
      if (id.length > 0 && $('div#cid-' + id).length === 0) {
        var oddeven = (i % 2 === 0) ? 'odd' : '';
        var contact = CRM.api3('contact', 'getsingle', {id: id})
          .done(function (result) {
            //console.log(result);
            $('div#match-emails .content').append('<div id="cid-' + id + '" class="match-details ' +
              oddeven + '"><span class="match-sort_name">' + result.sort_name +
              '</span><input type="text" name="email-' + id + '" value="' + result.email +
              '"><input type="hidden" name="emailorig-' + id + '" value="' + result.email + '"></div>');
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
  });

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
