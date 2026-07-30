CRM.$(function($) {
    var ts = CRM.ts('gov.nysenate.inbox');
    // NYSS #18205 - Improve UI handling when duplicate contacts are found by the New Individual form.
    $(document).on('crmFormError.nyssInbox', function(e, data) {
        var $dialog = $(e.target);
        if (!$dialog.find('form.CRM_Profile_Form_Edit').length) return;
        var ids = data.nyssInboxDuplicateIds;// short-circuit this logic if there are no duplicates
        if (! (ids && ids.length)) return;

        // fetch matching contact names and append links to the notice
        CRM.api4('Contact', 'get', {
            select: ['id', 'display_name', 'email_primary.email', 'phone_primary.phone'],
            where: [['id', 'IN', ids]]
        }).then(function(contacts) {
            // remove the warning, otherwise the message can be duplicated.
            $(".nyss-duplicate-warning").remove();

            if (contacts.length) {
                var $dup_contact_btn_label = $(".nyss-inbox-duplicate-contact-btn").first().text() || ts('Create Duplicate Contact');
                var $notice = $('<div class="nyss-duplicate-warning messages messages status crm-warning">').html(
                    '<h4><i class="crm-i fa-exclamation-triangle" role="img" aria-hidden="true"></i> '+ ts('Similar Constituent(s) Found') + '</h4>'
                );
                $notice.append('<p>' + ts('Choose an existing contact:') + '</p>');
                $table = $('<table><thead><tr><th>&nbsp;</th><th>Name</th><th>Email</th><th>Phone</th></tr></thead></table>');
                $tbody = $('<tbody>');
                $table.append($tbody);
                // Add a row for each contact
                var $trs = contacts.map(function(c) {
                    var $selectLink = $("<button>", {
                        id: "select-c-" + String(c.id),
                        class: "ui-btn",
                        href: "#",
                        text: "Select",
                        click: function (e) {
                            e.preventDefault();
                            // This is the magic that adds the contact to the Assign dialog.
                            var $field = $('#matches');
                            var $existing = $field.select2('data') || [];
                            var alreadyAdded = $existing.some(function(d) { return String(d.id) === String(c.id); });
                            if (!alreadyAdded) {
                                $existing.push({id: String(c.id), text: c.display_name});
                                $field.select2('data', $existing).trigger('change');
                            }
                            $dialog.dialog('close');
                        }
                    });
                    var $tr = $("<tr>").append($selectLink);
                    $tr.append('<td><a href="' + CRM.url('civicrm/contact/view', {
                        reset: 1,
                        cid: c.id
                    }) + '" target="_blank">' + CRM._.escape(c['display_name']) + '</a></td>');

                    $tr.append($("<td>", {text: CRM._.escape(c['email_primary.email']) || '\u00A0'}));
                    $tr.append($("<td>", {text: CRM._.escape(c['phone_primary.phone']) || '\u00A0'}));
                    return $tr;
                });

                if ($trs.length > 0) {
                    $tbody.append($trs);
                    $notice.append($table);
                }
            }
            $notice.append(
                '<p>' +
                ts('If you really want to create a new constituent, click %1.', {
                    1: '<strong>'+$dup_contact_btn_label+'</strong>'
                }) +
                '</p>');
            $dialog.find('form.CRM_Profile_Form_Edit').prepend($notice);
        });
    });
    // End NYSS #18205

  var message = $('div.message-details-wrapper').html();
  if (message.length) {
    $(document).ready(function() {
      $('input#street_address-Primary').removeClass('huge').addClass('big');
      $('div.crm-profile-name-new_individual').after('<div class="crm-accordion-wrapper crm-accordion message-details">' + message + '</div>');

      $('form.CRM_Profile_Form_Edit div.click-message').text('Click the colored areas below to populate the corresponding fields.');
      $('form.CRM_Profile_Form_Edit div.click-message').show();

      //insert values from body on click
      $('span.phone').click(function(){
        $('input#phone-1-1').val($(this).text());
      });
      $('span.email_address').click(function(){
        $('input#email-Primary').val($(this).text());
      });
      $('span.street_address').click(function(){
        $('input#street_address-Primary').val($(this).text());
      });
      $('span.city').click(function(){
        $('input#city-Primary').val($(this).text());
      });
      $('span.justzip').click(function(){
        $('input#postal_code-Primary').val($(this).text());
      });
      $('span.name').click(function(){
        var $e = $(this),
          j = $e.data('json'),
          check = ['first', 'middle', 'last'];
        for (v in j) {
          var select = '.CRM_Profile_Form_Edit #' + v + '_name';
          if ($(select).length) {
            $(select).val(j[v]);
          }
        }
      });

      //address is a single element with parsed values in data
      $('span.zip').click(function(){
        var json = $(this).data('json');
        $('input#city-Primary').val(json.city);
        $('input#postal_code-Primary').val(json.zip);

        //state select: determine id from label
        $('select#state_province-Primary option').each(function(){
          if ($(this).text() == json.state) {
            var stateId = $(this).val();
            $('select#state_province-Primary').val(stateId).trigger('change');
            return false;
          }
        });
      });
    });
  }
});
