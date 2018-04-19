CRM.$(function ($) {
  // DOM element creation function for match details.
  function addDetailRow(r) {
    var cid = r.contact_id,
      profile = CRM.vars.NYSS.summaryOverlayProfileId,
      url = CRM.url("civicrm/contact/view", {reset: 1, cid: cid}),
      summopt = {reset: 1, gid: profile, id: cid, snippet: 4},
      summurl = CRM.url("civicrm/profile/view", summopt),
      row = $('<div class="match-details-email-row"></div>')
        .attr('id', "cid-" + cid),
      iconlink = $('<a class="crm-summary-link"></a>')
        .attr('href', summurl)
        .html('<i class="crm-i fa-list-alt"></i>'),
      namelink = $('<a target="_blank"></a>')
        .attr('href', url)
        .html(r.sort_name),
      label = $('<div class="label match-sort_name match-details"></div>')
        .append(iconlink)
        .append(namelink),
      ctnr = $('<div class="match-details content"></div>'),
      clear = $('<div class="clear"></div>'),
      ctrl = null,
      d = null;

    row.append(label);

    ['email', 'phone'].forEach(function (v) {
      ctrl = $('<input type="text" />')
        .addClass('text-input-' + v)
        .attr('name', v + '-' + cid)
        .attr('placeholder', v);
      if (r[v]) {
        ctrl.attr('value', r[v]);
      }
      d = ctnr.clone().append(ctrl);
      d.append(ctrl.clone()
        .attr('name', v + 'orig-' + cid)
        .attr('type', 'hidden'));
      row.append(d);
    });

    row.append(clear);
    return row;

  }

  // Primary display function.
  function displayEmails() {
    var ids = $('#matches').val().split(',');

    for (var i = 0; i < ids.length; i++) {
      var id = ids[i];
      if (id.length > 0 && $('div#cid-' + id).length === 0) {
        var contact = CRM.api3('contact', 'getsingle', {id: id})
          .done(function (result) {
            $('div#match-emails').append(addDetailRow(result));
            var fn = $('.match-details-email-row').length ? 'show' : 'hide';
            $('.click-message')[fn]();
          });
      }
    }

    //account for remove of specific values
    $('div.match-details-email-row').each(function () {
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

  // Click handler for phone numbers, populates the selected contacts.
  $('span.found.phone').click(function () {
    var pn = $(this).text();
    $('.match-details input.text-input-phone[type=text]').val(pn);
  });

  // Click handler for normal email addresses, populates the selected contacts.
  $('span.email_address').click(function () {
    var email = $(this).text();
    $('.match-details input.text-input-email[type=text]').val(email);
  });

  // Click handler for aggregator emails, populates only after confirmation.
  $('span.aggregator_email').click(function () {
    var email = $(this).text(),
      msg = "The email address " + email + " has been flagged as belonging " +
        "to a public action entity.  This is not a constituent's email " +
        "address.<br />Are you sure you want to add this email address to the " +
        "consitutent record?",
      options = {message: msg};
    CRM.confirm(options)
      .on('crmConfirm:yes', function () {
        $('.match-details input.text-input-email[type=text]').val(email);
      });
  });

  // Attach the display function to the contact selection box.
  $('#matches').change(function () {
    displayEmails();
  });

  // Trigger the display function on load.
  displayEmails();
});
