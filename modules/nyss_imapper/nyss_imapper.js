if (!String.prototype.capitalize) {
  Object.defineProperty(String.prototype,'capitalize',
      {
        writable:true,
        value:function(first_only,undefined){
          if (first_only===undefined) {first_only = false;}
          r = first_only ? /(?!^\/)\b([a-z])/ : /(?!^\/)\b([a-z])/g;
          return this.replace(r,function(m){return m.toUpperCase()});
        }
      });
}

var messages = [];
var contacts = [];

function display_ajax_result(data, status) {
  var result = cj.parseJSON(data),
      msgtype = result.is_error ? 'error' : 'success';
  CRM.alert(result.message, msgtype.capitalize(), msgtype);
}

cj(document).ready(function()
{
  cj(".range option[value='30']").attr('selected', 'selected');

  // use highlighted text to populate search areas
  cj(".found.email_address").live('click', function() {
    cj('.highlightTarget:visible .email_address').val(cj(this).data('search'));
  });

  cj(".found.phone").live('click', function() {
    cj('.highlightTarget:visible .phone').val(cj(this).data('search'));
  });

  cj(".found.zip").live('click', function() {
    var data = cj(this).data('json');
    cj('.highlightTarget:visible .city').val(data.city);
    cj('.highlightTarget:visible .state').val(data.state);
    cj('.highlightTarget:visible .zip').val(data.zip);
  });

  cj(".found.name").live('click', function() {
    var data = cj(this).data('json');
    cj('.highlightTarget:visible .prefix').val(data.prefix);
    cj('.highlightTarget:visible .first_name').val(data.first);
    cj('.highlightTarget:visible .middle_name').val(data.middle);
    cj('.highlightTarget:visible .last_name').val(data.last);
    cj('.highlightTarget:visible .suffix').val(data.suffix);
  });

  // prevent the scroll to top issue
  cj(".imap_actions_column a").live('click', function( event ) {
    event.preventDefault();
  });

  // create the multi-tab interface
  cj("#tabs, #tabs_tag, #tabs_edit").tabs({
    // hide open autocomplete messages when switching
    activate: function( event, ui ){
      cj('.token-input-dropdown-facebook').hide();
    }
  });

  // Date range hack - a little debug trick
  if (location.hash.replace("#","").length) {
    var range = location.hash.replace("#","");
  }
  else {
    var range = 30;
  };

  // onpageload
  if (cj("#Activities").length) {
    getMatched(range);
  } else if(cj("#Unmatched").length) {
    getUnmatched(range);
  } else if(cj("#Reports").length) {
    getReports(range);
  }

  // add a loading icon popup
  cj("#loading-popup").dialog({
    modal: true,
    width: 200,
    autoOpen: false,
    resizable: false,
    title: 'Please Wait'
  });

  // assign additional emails to new contacts
  var AdditionalEmail = cj( "#AdditionalEmail-popup" ).dialog({
    modal: true,
    width: 500,
    autoOpen: false,
    resizable: false,
    open: function() {
      if (cj('#add_email').text().length < 10) {
        cj( this ).dialog("close");
      };
      cj(this).siblings('.ui-dialog-buttonpane').find('button:eq(0)').focus();
      cj(this).siblings('.ui-dialog-buttonpane').find('button:eq(0)').addClass('primary_button');
    },
    buttons: {
      "Yes": function() {
        var add_emails = [];
        cj('#add_email input:checked').each(function() {
          add_emails.push(cj(this).attr('value'));
        });
        if (cj('#add_email #cb_static').val()) {
          add_emails.push(cj('#add_email #cb_static').val());
        };
        cj.each(add_emails, function(index, value) {
          var contacts = cj('#contact').val();
          cj.ajax({
            url: '/civicrm/imap/ajax/contact/addEmail',
            async: false,
            data: {
              email: value,
              contacts: contacts
            },
            success: function(data, status) {
              result = cj.parseJSON(data);
              if (result.is_error) {
                CRM.alert('Unable to add email', '', 'error');
              }
              else {
                CRM.alert('Email added', '', 'success');
              }
            },
            error: function() {
              CRM.alert('Unable to add email', '', 'error');
            }
          });
        });
        cj('#add_email').empty();
        cj( this ).dialog( "close" );
      },
      No: function() {
        cj('#add_email').empty();
        cj( this ).dialog( "close" );
      }
    }
  });

  // ***********************************************************
  // Search -
  // Searching for contacts to assign message to
  cj("#search").live('click', function() {
    var first_name = cj('#tab1 .first_name').val();
    var last_name = cj('#tab1 .last_name').val();
    var city = cj('#tab1 .city').val();
    var phone = cj('#tab1 .phone').val();
    var street_address = cj('#tab1 .street_address').val();
    var email_address = cj('#tab1 .email_address').val();
    var dob = cj('#tab1 .form-text.dob').val();
    var state = cj('#tab1 .state').val();

    if (first_name || last_name || city || phone || street_address || email_address || dob) {
      cj('.search .right').html('').append('Searching...');
      cj.ajax({
        url: '/civicrm/imap/ajax/contact/search',
        async: false,
        data: {
          state: state,
          city: city,
          phone: phone,
          email_address: email_address,
          dob: dob,
          street_address: street_address,
          first_name: first_name,
          last_name: last_name
        },
        success: function(data, status) {
          if (data != null || data != '') {
            result = cj.parseJSON(data);
            if (result.is_error) {
              CRM.alert('Unable to search: '+result.message, '', 'error');
            }
            else {
              contacts = result.data;
              if (contacts.length == 0) {
                cj('.search .right').html('<strong>No contacts found</strong>');
                cj('#imapper-contacts-list').html('');
              }
              else {
                nContacts = contacts.length;
                if (nContacts == 1) pl = '';
                else pl = 's';
                cj('.search .right').html('<strong>'+nContacts+' contact'+pl+' found</strong>');
                buildContactList(0);
                cj("#reassign").show();
              }
            }
          }
        }
      });
    }
    else {
      cj('.search .right').html('');
      CRM.alert('Please enter a search query', '', 'warn');
    }
    return false;
  });

  // *******************************************************
  // Delete -
  // When you really didn't mean to forward that message in
  // ask permission first, with bright red buttons

  // Delete Single
  cj(".imap_actions_column .delete").live('click', function() {
    var messageId = cj(this).parent().parent().find('.checkbox').data('delete');
    cj("#delete-confirm #message").val(messageId);
    cj("#delete-confirm").dialog('open');
  });

  // Delete Multiple ( from checkbox )
  cj(".page_actions .multi_delete").live('click', function() {
    // grab the rows to delete
    var delete_ids = $("#imapper-messages-list input:checkbox:checked").map(function(){
      return $(this).data('delete');
    }).get();
    if (!delete_ids.length) {
      CRM.alert('Use the checkbox to select one or more messages to delete', '', 'warn');
      return false;
    }
    cj("#loading-popup").dialog('open');
    cj("#delete-confirm").dialog({ title:  "Delete "+delete_ids.length+" messages from Bluebird?"});
    cj("#delete-confirm #message").val(delete_ids);
    cj("#loading-popup").dialog('close');
    cj("#delete-confirm").dialog('open');
    // return false;
  });

  // Delete modal dialog
  cj("#delete-confirm").dialog({
    modal: true,
    dialogClass: 'delete_popup_class',
    width: 370,
    title: "Remove this message from Bluebird?",
    open: function() {
      cj(this).closest(".ui-dialog").find(".ui-button:first").addClass("primary_button");
    },
    buttons: {
      "Delete": function() {
        var messageId = cj("#delete-confirm #message").val();
        cj(this).dialog("close");
        if (cj("#Activities").length) {
          url = '/civicrm/imap/ajax/matched/delete';
          entity = 'activity';
        }
        else {
          url = '/civicrm/imap/ajax/unmatched/delete';
          entity = 'message';
        }

        cj.ajax({
          url: url,
          async: false,
          data: { id: messageId },
          success: function(data, status) {
            result = cj.parseJSON(data);
            if (result.is_error) {
              CRM.alert('Unable to delete '+entity+': '+result.message, '', 'error');
            }
            else {
              removeRow(result.data);
              CRM.alert('Deleted '+entity, '', 'success');
            }
          },
          error: function() {
            CRM.alert('Unable to delete '+entity, '', 'error');
          }
        });
      },
      Cancel: function() {
        cj(this).dialog("close");
      }
    },
    autoOpen: false,
    resizable: false
  });

  // *******************************************************
  // Clear -
  // When you don't want to see the message on the "matched" screen any longer
  // because whats it good for?
  // but ask permission first

  // Clear Single
  cj(".imap_actions_column .clear").live('click', function() {
    // console.log('clear');
    cj("#loading-popup").dialog('open');
    var messageId = cj(this).parent().parent().find('.checkbox').data('delete');
    cj("#clear-confirm #message").val(messageId);
    cj("#clear-confirm").dialog({ title:  "Clear message from list?"});
    cj("#loading-popup").dialog('close');
    cj("#clear-confirm").dialog('open');
    return false;
  });

  // Clear multiple ( from checkbox )
  cj(".page_actions .multi_clear").live('click', function() {
    // console.log('multi_clear');
    var clear_ids = $("#imapper-messages-list input:checkbox:checked").map(function(){
      return $(this).data('delete');
    }).get();
    if (!clear_ids.length) {
      CRM.alert('Use the checkbox to select one or more messages to clear', '', 'warn');
      return false;
    }
    cj("#loading-popup").dialog('open');
    cj("#clear-confirm").dialog({ title:  "Clear "+clear_ids.length+" messages from this list?"});
    cj("#clear-confirm #message").val(clear_ids);
    cj("#loading-popup").dialog('close');
    cj("#clear-confirm").dialog('open');
  });

  // Clear modal dialog
  cj("#clear-confirm").dialog({
    modal: true,
    width: 370,
    buttons: {
      "Clear": function() {
        ClearActivity(cj("#clear-confirm #message").val());
        cj( this ).dialog( "close" );
      },
      Cancel: function() {
        cj("#clear-confirm").dialog('close');
      }
    },
    autoOpen: false,
    resizable: false
  });

  // Clear activities
  function ClearActivity(activityId) {
    if (cj.isArray(activityId)) {
      activityId = activityId.toString();
    }
    cj.ajax({
      url: '/civicrm/imap/ajax/matched/clear',
      data: { id: activityId },
      async: false,
      success: function(data, status) {
        result = cj.parseJSON(data);
        if (result.is_error) {
          CRM.alert('Unable to clear activity: '+result.message, '', 'error');
        }
        else {
          CRM.alert('Activity cleared', '', 'success');
        }
        activityId = activityId.split(',');
        removeRow(activityId);
        cj("#clear-confirm").dialog('close');
      },
      error: function() {
        CRM.alert('Unable to clear activity', '', 'error');
      }
    });
  }


  // ****************************************************************
  // Assign -
  // A message is received, but we cannot find an exact match (via email)
  // It shows up on the Unmatched screen to be assigned to a contact

  // Assigning modal dialog
  cj("#assign-popup").dialog({
    modal: true,
    height: 580,
    width: 960,
    autoOpen: false,
    resizable: false,
    title: 'Loading Data',
    close: function(){
      cj('.token-input-dropdown-facebook').remove();
      cj('.token-input-list-facebook').remove();
    },
    open: function() {
      cj(this).siblings('.ui-dialog-buttonpane').find('button:eq(0)').focus();
      cj(this).siblings('.ui-dialog-buttonpane').find('button:eq(0)').addClass('primary_button');
      cj("#loading-popup").dialog('close');

      // here's the render
      var messageId = cj('#message').val();
      cj.ajax({
        url: '/civicrm/imap/ajax/unmatched/details',
        data: { id: messageId },
        success: function(data, status) {
          result = cj.parseJSON(data);
          if (result.is_error) {
            removeRow(messageId);
            CRM.alert('Unable to load message: '+ result.message, '', 'error');
          }
          else {
            message = result.data;
            var icon = '';
            if (message.attachmentfilename || message.attachmentname || message.attachment) {
              if (message.attachmentname) {
                var name = message.attachmentname;
              }
              else {
                var name = message.attachmentfilename;
              }
              icon = '<div class="ui-icon ui-icon-link attachment" title="'+name+'"></div>';
            }
            cj('#message_left_header').html('');
            cj('#message_left_header').append("<span class='popup_def'>From: </span>");
            if (message.sender_name) {
              cj('#message_left_header').append(shortenString(message.sender_name,50));
            }
            if (message.sender_email) {
              cj('#message_left_header').append("<span class='emailbubble marginL5'>"+shortenString(message.sender_email)+"</span>");
            }
            cj('#message_left_header').append("<br/><span class='popup_def'>Subject: </span>"+shortenString(message.subject,55)+" "+ icon+"<br/><span class='popup_def'>Date Forwarded: </span>"+message.email_date_long+"<br/>");
            if (message.forwarder != message.sender_email) {
              cj('#message_left_header').append("<span class='popup_def'>Forwarded by: </span><span class='emailbubble'>"+ message.forwarder+"</span> @ "+ message.updated_date_long+ "<br/>");
            }
            else {
              cj('#message_left_header').append("<span class='popup_def'>&nbsp;</span>No forwarded content found<br/>");
            }
            cj('#message_left_email').html(message.body+"<hr/>");
            cj.each(message.attachments, function(key, value) {
              if (!value.rejection || value.rejection == '') {
                cj('#message_left_email').append(value.fileName+" ("+((value.size / 1024) / 1024).toFixed(2)+" MB)<br/>");
              }
              else {
                cj('#message_left_email').append("<span class='rejected'>"+value.fileName+" was rejected ("+value.rejection+")</span><br/>");
              }
            });
            cj('.first_name, .last_name, .phone, .street_address, .street_address_2, .city, .email_address').val('');

            // add found emails to additional email popup
            cj('#AdditionalEmail-popup #add_email').empty();
            cj.each(message.found_emails, function(idx, val) {
              cj('#AdditionalEmail-popup #add_email').append('<fieldset id="fs_'+idx+'"></fieldset>');
              cj('<input />', { type: 'checkbox', id: 'cb_'+idx, value: val }).appendTo('#fs_'+idx);
              cj('<label />', { 'for': 'cb_'+idx, text: val }).appendTo('#fs_'+idx);
              cj('#cb'+idx).click();
            });
            cj('#AdditionalEmail-popup  #add_email').append('<fieldset id="fs_static"></fieldset>');
            cj('<input />', { type: 'input', id: 'cb_static',placeholder: 'Additional Email Address' }).appendTo('#fs_static');

            cj("#assign-popup").dialog({
              title:  "Assigning: "+shortenString(message.subject,55),
            });
            cj('#tabs').tabs({
              selected: 0,
              activate: function(event, ui) {
                var button = cj(event.currentTarget).data('button');
                cj('.match_popup_class .ui-dialog-buttonpane').find('button:eq(0) span').text(button);
              }
            });
            cj('#tab1 .email_address').val(message.sender_email);
            if (message.sender_email) {
              cj('#search').click();
            }
          }
        },
        error: function() {
          CRM.alert('Unable to load message', '', 'error');
        }
      });
    },
    dialogClass: 'match_popup_class',
    buttons: {
      "Assign": function() {
        Assign(cj('#message').val());
      },
      Cancel: function() {
        cj(this).dialog("close");
      }
    }
  });
  // what triggers the popup
  cj(".imap_actions_column .assign").live('click', function() {
    cj("#loading-popup").dialog('open');
    var messageId = cj(this).parent().parent().attr('id');
    cj('#message').val(messageId);
    cj("#tabs :input[type='text']").val("");
    cj(".dob .month,.dob .day,.dob .year,.state").val([]);
    cj("#status_id option[value='']").attr('selected', 'selected');

    cj('#imapper-contacts-list, #message_left_email').html('');
    cj("#message_left_email").animate({
      scrollTop: 0
    }, 'fast');
    cj("#assign-popup").dialog('open');

    return false;
  });

  // assign a message to a contact Unmatched page
  function Assign(messageId) {
    // console.log('Assign # : '+messageId);

    // get array of checked ids
    var newContacts = $(".imapper-contact-box input:checkbox:checked").map(function(){
      return $(this).val();
    }).get();

    // fields for creating a contact
    var prefix = cj("#tab2 .prefix").val();
    var first_name = cj("#tab2 .first_name").val();
    var middle_name = cj("#tab2 .middle_name").val();
    var last_name = cj("#tab2 .last_name").val();
    var suffix = cj("#tab2 .suffix").val();
    var email_address = cj("#tab2 .email_address").val();
    var phone = cj("#tab2 .phone").val();
    var street_address = cj("#tab2 .street_address").val();
    var street_address_2 = cj("#tab2 .street_address_2").val();
    var zip = cj("#tab2 .zip").val();
    var city = cj("#tab2 .city").val();
    var dob = cj("#tab2 .form-text.dob").val();
    var state = cj("#tab2 .state").val();

    // if they've selected 1 or more contact, assign the message
    if (newContacts != '') {
      // console.log('Assigning message to existing contact');
      cj.ajax({
        url: '/civicrm/imap/ajax/unmatched/assign',
        data: {
          id: messageId,
          contactId: newContacts.toString()
        },
        success: function(data, status) {
          result = cj.parseJSON(data);
          if (result.is_error) {
            CRM.alert('Could not assign message: '+result.message, '', 'error');
            return false;
          }
          else {
            messages = result.data;
            cj.each(messages, function(id, value) {
              removeRow(messageId);
              CRM.alert(value.message, '', 'success');
            });
            AdditionalEmail.dialog('open');
            cj('#AdditionalEmail-popup #contact').val(newContacts.toString());
            cj("#assign-popup").dialog('close');
            // additional email popup
          }
        }
      });
    }
    else if (first_name || last_name || email_address) {
      // console.log('Assigning message to new contact');
      if ((cj.isNumeric(cj("#tab2 .dob .month").val()) || cj.isNumeric(cj("#tab2 .dob .day").val()) || cj.isNumeric(cj("#tab2 .dob .year").val())) && ( !cj.isNumeric(cj("#tab2 .dob .month").val()) || !cj.isNumeric(cj("#tab2 .dob .day").val()) || !cj.isNumeric(cj("#tab2 .dob .year").val()))) {
        CRM.alert('Please enter a full date of birth', 'Warning', 'warn');
        return false;
      };

      cj.ajax({
        url: '/civicrm/imap/ajax/contact/add',
        data: {
          prefix: prefix,
          first_name: first_name,
          middle_name: middle_name,
          last_name: last_name,
          suffix: suffix,
          email_address: email_address,
          phone: phone,
          street_address: street_address,
          street_address_2: street_address_2,
          postal_code: zip,
          city: city,
          state: state,
          dob: dob
        },
        success: function(data, status) {
          result = cj.parseJSON(data);
          if (result.is_error) {
            CRM.alert('Could not create contact: '+result.message, '', 'error');
            return false;
          }
          else {
            contactId = result.data;
            cj.ajax({
              url: '/civicrm/imap/ajax/unmatched/assign',
              data: {
                id: messageId,
                contactId: contactId
              },
              success: function(data, status) {
                result = cj.parseJSON(data);
                if (result.is_error) {
                  CRM.alert('Could not assign message: '+result.message, '', 'error');
                  return false;
                }
                else {
                  messages = result.data;
                  AdditionalEmail.dialog('open');
                  cj('#AdditionalEmail-popup #contact').val(contactId);
                  cj("#assign-popup").dialog('close');
                  cj.each(messages, function(id, value) {
                    removeRow(messageId);
                    CRM.alert('Contact created: '+value.message, '', 'success');
                    if (email_address.length > 0) {
                      checkForMatch(email_address, contactId);
                    }
                  });
                }
              }
            });
          }
        }
      });
      return false;
    }
    else {
      // console.log('Please choose a contact');
      CRM.alert('Select a contact to assign message to,<br/> OR create a contact with first name, last name, or email', '', 'warn');
    }
  };


  // ****************************************************************
  // Process -
  // A message is received and is automatically or manually matched.
  // It shows up on the Matched screen to be processed

  // Process modal dialog
  cj("#process-popup").dialog({
    modal: true,
    height: 580,
    width: 960,
    autoOpen: false,
    resizable: false,
    title: 'Loading Data',
    close: function() {
      cj('.token-input-dropdown-facebook').remove();
      cj('.token-input-list-facebook').remove();
    },
    open: function() {
      cj(this).siblings('.ui-dialog-buttonpane').find('button:eq(0)').focus();
      cj(this).siblings('.ui-dialog-buttonpane').find('button:eq(0)').addClass('primary_button');

      var messageId = cj('#message').val();
      var activityId = cj('#activity').val();
      var contactId = cj('#contact').val();

      // Reset inputs
      cj('#contact_tag, #contact_position, #activity_tag, #contact_name, #activity_date, #contact_position_name').val('');
      cj('.token-input-dropdown-facebook').remove();
      cj('.token-input-list-facebook').remove();
      cj('#contact-issue-codes,#message_left_header,#message_left_email,#imapper-contacts-list').html('');

      // load the message
      // build one list of singular processing
      messageIds = messageId.split(',');
      activityIds = activityId.split(',');
      newContacts = contactId.split(',');
      if (activityIds.length == 1) {
        cj('#message_left_header,.ReAssignTab').show();
        cj('#message_left_email').removeClass('multi');
        cj('#ui-id-1').click();
        cj.ajax({
          url: '/civicrm/imap/ajax/matched/details',
          data: { id: messageId, contact: contactId },
          success: function(data,status) {
            result = cj.parseJSON(data);
            if (result.is_error) {
              CRM.alert('Could not load message details: '+result.message, '', 'error');
              cj("#loading-popup").dialog('close');
              removeRow(messageId);
            }
            else {
              message = result.data;
              if (message.sender_name || message.sender_email) {
                cj('#message_left_header').html('').append("<span class='popup_def'>From: </span>");
              }
              if (message.sender_name) {
                cj('#message_left_header').append(message.sender_name +"  ");
              }
              if (message.sender_email) {
                cj('#message_left_header').append("<span class='emailbubble'>"+ message.sender_email+"</span>");
              }
              cj('#message_left_header').append("<br/><span class='popup_def'>Subject: </span>"+shortenString(message.subject,55) +"<br/><span class='popup_def'>Date Forwarded: </span>"+message.email_date_long+"<br/>");
              cj('.email_address').val(message.fromEmail);

              if (message.forwarder != message.sender_email) {
                cj('#message_left_header').append("<span class='popup_def'>Forwarded by: </span><span class='emailbubble'>"+ message.forwarder+"</span> @ "+ message.updated_long+ "<br/>");
              }
              else {
                cj('#message_left_header').append("<span class='popup_def'>&nbsp;</span>No forwarded content found<br/>");
              }
              cj('#message_left_email').html(message.body+"<hr/>");
              cj.each(message.attachments, function(key, value) {
                if (!value.rejection || value.rejection == '') {
                  cj('#message_left_email').append(value.fileName+" ("+((value.size / 1024) / 1024).toFixed(2)+" MB)<br/>");
                }
                else {
                  cj('#message_left_email').append("<span class='rejected'>"+value.fileName+" was rejected ("+value.rejection+")</span><br/>");
                }
              });
              cj("#loading-popup").dialog('close');
              cj("#process-popup").dialog({
                title:  "Processing: "+shortenString(message.subject,55),
              });
              cj('#imapper-contacts-list').html('').append("<strong>currently matched to : </strong><br/>           "+'<a href="/civicrm/contact/view?reset=1&cid='+message.matched_to+'" title="'+message.sender_name+'">'+shortenString(message.sender_name,35)+'</a>'+" <br/><i>&lt;"+ message.sender_email+"&gt;</i> <br/>"+ cj('.dob').val()+"<br/> "+ cj('.phone').val()+"<br/> "+ cj('.street_address').val()+"<br/> "+ cj('.city').val()+"<br/>");
            }
          },
          error: function() {
            CRM.alert('Unable to load message', '', 'error');
            cj("#process-popup").dialog('close');
          }
        });
      }
      else if (activityIds.length > 1) {
        // here is the view for multiple messages
        cj('#message_left_email').addClass('multi');
        cj('#message_left_header,.ReAssignTab').hide();
        cj('#ui-id-2').click();
        cj.each(messageIds, function(key, messageId) {
          // console.log("messageId : "+messageId+' - activityId :'+activityIds[key]+" - key : "+key+" - Contact : "+newContacts[key]);
          cj.ajax({
            url: '/civicrm/imap/ajax/matched/details',
            data: { id: messageId, contact: newContacts[key] },
            success: function(data, status) {
              cj("#loading-popup").dialog('close');
              result = cj.parseJSON(data);
              if (result.is_error) {
                removeRow(messageId);
                CRM.alert('Unable to load message: '+result.message, '', 'error');
                return false;
              }
              else {
                message = result.data;
                cj('#message_left_email').append("<div id='header_"+messageId+"' data-id='"+messageId+"' class='message_left_header_tags'><span class='popup_def'>From: </span>"+message.sender_name +"  <span class='emailbubble'>"+ message.sender_email+"</span><br/><span class='popup_def'>Subject: </span>"+shortenString(message.subject,55)+"<br/><span class='popup_def'>Date Forwarded: </span>"+message.email_date_long+"<br/></div><div id='email_"+messageId+"' class='hidden_email' data-id='"+messageId+"'></div>");
                if (message.forwarder != message.sender_email) {
                  cj('#header_'+messageId).append("<span class='popup_def'>Forwarded by: </span><span class='emailbubble'>"+ message.forwarder+"</span> @"+ message.updated_date_long+ "<br/>");
                }
                else {
                  cj('#header_'+messageId).append("<span class='popup_def'>&nbsp;</span>No forwarded content found<br/>");
                }
                cj('#email_'+messageId).html("<span class='info hidden_email_info' data-id='"+messageId+"'>Show Email</span><br/><span class='email'>"+message.body+"</span>");
              }
            },
            error: function() {
              CRM.alert('Unable to load message', '', 'error');
            }
          });
        });
        cj("#loading-popup").dialog('close');
        cj("#process-popup").dialog({
          title:  "Processing "+activityIds.length+" messages",
        });
      }


      // ----
      // Tagging
      // Contact Keywords
      cj('#contact_keyword_input').tokenInput('/civicrm/imap/ajax/tag/search', {
        theme: 'facebook',
        zindex: 9999,
        jsonContainer: 'data',
        onResult: function(result) {
          if (result.is_error) {
            CRM.alert('Unable to look up tag: '+result.message, '', 'error');
          }
          return result;
        },
        onAdd: function(item) {
          current_contact_tags = cj('#contact_tag').val();
          current_contact_tags = current_contact_tags.replace(/,,/g, ',');
          cj('#contact_tag').val(current_contact_tags+','+item.id);
        },
        onDelete: function(item) {
          current_contact_tags = cj('#contact_tag').val();
          result = string_replace(current_contact_tags, ','+item.id, ',');
          result = result.replace(/,,/g, ',');
          cj('#contact_tag').val(result);
        }
      });
      // Contact Issue Codes
      var tree = new TagTreeTag({
        tree_container: cj('#contact-issue-codes'),
        filter_bar: cj('#contact-issue-codes-search'),
        tag_trees: [291],
        default_tree: 291,
        auto_save: false,
        entity_id: cj('#contact_ids').val(),
        entity_counts: false,
        entity_type: 'civicrm_contact',
      });
      tree.load();
      // Activity Keywords
      cj('#activity_keyword_input').tokenInput('/civicrm/imap/ajax/tag/search', {
        theme: 'facebook',
        zindex: 9999,
        jsonContainer: 'data',
        onResult: function(result) {
          if (result.is_error) {
            CRM.alert('Unable to look up tag: '+result.message, '', 'error');
          }
          return result;
        },
        onAdd: function(item) {
          current_activity_tags = cj('#activity_tag').val();
          current_activity_tags = current_activity_tags.replace(/,,/g, ',');
          cj('#activity_tag').val(current_activity_tags+','+item.id);
        },
        onDelete: function(item) {
          current_activity_tags = cj('#activity_tag').val();
          result = string_replace(current_activity_tags, ','+item.id, ',');
          result = result.replace(/,,/g, ',');
          cj('#activity_tag').val(result);
        }
      });
      // Contact Positions
      cj('#contact_position_input').tokenInput('/civicrm/ajax/taglist?parentId=292', {
        theme: 'facebook',
        zindex: 9999,
        onAdd: function(item) {
          current_contact_positions = cj('#contact_position').val();
          current_contact_positions = current_contact_positions.replace(/,,/g, ',');
          cj('#contact_position').val(current_contact_positions+','+item.id);
        },
        onDelete: function(item) {
          current_contact_positions = cj('#contact_position').val();
          result = string_replace(current_contact_positions, ','+item.id, ',');
          result = result.replace(/,,/g, ',');
          cj('#contact_position').val(result);
        }
      });

      // ----
      // Activity Editing,
      // Assignee Contact Names
      cj('#contact_name_input').tokenInput('/civicrm/ajax/checkemail?noemail=1&context=activity_assignee', {
        theme: 'facebook',
        zindex: 9999,
        onAdd: function(item) {
          current_contact_name = cj('#contact_name').val();
          current_contact_name = current_contact_name.replace(/,,/g, ',');
          cj('#contact_name').val(current_contact_name+','+item.id);
          //console.log(item.id);
        },
        onDelete: function(item) {
          current_contact_name = cj('#contact_name').val();
          result = string_replace(current_contact_name, ','+item.id, ',');
          result = result.replace(/,,/g, ',');
          cj('#contact_name').val(result);
        }
      });

    },
    dialogClass: 'match_popup_class',
    buttons: {
      "Update & Clear": function() {
        Process('clear');
      },
      "Update": function() {
        Process();
      },
      "Clear": function() {
        ClearActivity(messageIds);
        cj("#process-popup").dialog('close');
      },
      Cancel: function() {
        cj(this).dialog("close");
      }
    }
  });

  // What triggers the popup
  cj(".imap_actions_column .process").live('click', function() {
    cj("#loading-popup").dialog('open');

    var messageId = cj(this).parent().parent().attr('data-imap-id');
    var activityId = cj(this).parent().parent().attr('data-activity-id');
    var contactId = cj(this).parent().parent().attr('data-contact-id');

    cj('#message').val(messageId);
    cj('#activity').val(activityId);
    cj('#contact').val(contactId);
    cj("#tabs :input[type='text']").val("");
    cj(".dob .month,.dob .day,.dob .year,.state").val([]);
    cj('#imapper-contacts-list, #message_left_email').html('');
    cj("#status_id option[value='']").attr('selected', 'selected');
    cj("#message_left_email").animate({
      scrollTop: 0
    }, 'fast');
    cj("#process-popup").dialog('open');
    return false;
  });

  // What triggers the popup
  cj(".page_actions .multi_process").live('click', function() {
    cj("#loading-popup").dialog('open');

    var newContacts = new Array();
    var activityIds = new Array();
    var messageIds = new Array();

    cj('#imapper-messages-list input:checked').each(function() {
      messageIds.push(cj(this).attr('data-imap-id'));
      activityIds.push(cj(this).attr('data-activity-id'));
      newContacts.push(cj(this).attr('data-contact-id'));
    });

    cj('#contact').val('').val(newContacts);
    cj('#message').val('').val(messageIds);
    cj('#activity').val('').val(activityIds);

    // console.log('newContacts',newContacts)
    // console.log('messageIds',messageIds)
    // console.log('activityIds',activityIds)

    if (!activityIds.length) {
      cj("#loading-popup").dialog('close');
      CRM.alert('Use the checkbox to select one or more messages to process', '', 'warn');
      return false;
    }
    cj("#process-popup").dialog('open');

  });

  // Step through the process
  function Process(clear) {
    // assume we have errors
    var error = true;

    // Inputs
    var messageId = cj('#message').val();
    var activityId = cj('#activity').val();
    var contactId = cj('#contact').val();

    // get array of checked ids for contact reassign
    var newContacts = new Array();
    var newContacts = $(".imapper-contact-box input:checkbox:checked").map(function(){
      return $(this).val();
    }).get();
    // console.log("contactId",contactId);
    // console.log("newContacts",newContacts);

    // create new contact info
    var prefix = cj("#tab2_edit .prefix").val();
    var first_name = cj("#tab2_edit .first_name").val();
    var middle_name = cj("#tab2_edit .middle_name").val();
    var last_name = cj("#tab2_edit .last_name").val();
    var suffix = cj("#tab2_edit .suffix").val();
    var email_address = cj("#tab2_edit .email_address").val();
    var phone = cj("#tab2_edit .phone").val();
    var street_address = cj("#tab2_edit .street_address").val();
    var street_address_2 = cj("#tab2_edit .street_address_2").val();
    var zip = cj("#tab2_edit .zip").val();
    var city = cj("#tab2_edit .city").val();
    var dob = cj("#tab2_edit .form-text.dob").val();
    var state = cj("#tab2_edit .state").val();

    // array of previously selected issue codes in tree
    var existingIssueCodes = new Array();
    cj.each(cj('#contact-issue-codes dt.existing'), function(key, id) {
      existingIssueCodes.push(cj(this).attr('tid'));
    });

    // array of new issue codes in tree
    var newIssueCodes = new Array();
    cj.each(cj('#contact-issue-codes dt.checked'), function(key, id) {
      newIssueCodes.push(cj(this).attr('tid'));
    });

    // do a two way diff to check to see if the current list
    // matches the previous list
    var removedIssueCodes = cj(existingIssueCodes).not(newIssueCodes).get();
    var addedIssueCodes = cj(newIssueCodes).not(existingIssueCodes).get();

    // clean out weird ,'s  from facebook autocomplete
    var contact_tag = cj("#process-popup #contact_tag").val().replace(/,,/g, ",").replace(/^,/g, "");
    var activity_tag = cj("#process-popup #activity_tag").val().replace(/,,/g, ",").replace(/^,/g, "");
    var contact_position = cj("#process-popup #contact_position").val().replace(/,,/g, ",").replace(/^,/g, "");

    var activity_contact = cj("#process-popup #contact_name").val().replace(/,,/g, ",").replace(/^,/g, "");
    var activity_status_id = cj("#tab3 #status_id").val();
    // var activity_date = cj("#tab3 #activity_date").val();
    //

    // ----
    // Logic ( or as close as I get to it )
    // did we reassign ?
    // if they've selected 1 or more contact, reassign the message
    if (newContacts != '') {
      // console.log('Reassigning to: ', newContacts);
      cj.ajax({
        url: '/civicrm/imap/ajax/matched/reassign',
        async: false,
        data: {
          id: messageId,
          contactId: newContacts.toString()
        },
        success: function(data, status) {
          var result = cj.parseJSON(data);
          if (result.is_error) {
            CRM.alert('Could not reassign message: '+result.message, '', 'error');
          }
          else {
            data = result.data;
            cj("#process-popup").dialog('close');
            // reset activity to new data
            cj('#'+messageId).attr("data-contact_id", data.contact_id);
            cj('#'+messageId+" .name").attr("data-firstname", data.first_name);
            cj('#'+messageId+" .name").attr("data-lastname", data.last_name);
            cj('#'+messageId+" .match").html("ManuallyMatched");
            contact = '<a href="/civicrm/profile/view?reset=1&amp;gid=13&amp;id='+data.contact_id+'&amp;snippet=4" class="crm-summary-link"><div class="icon crm-icon '+data.contact_type+'-icon" title="'+data.contact_type+'"></div></a><a title="'+data.display_name+'" href="/civicrm/contact/view?reset=1&amp;cid='+data.contact_id+'">'+data.display_name+'</a><span class="emailbubble marginL5">'+shortenString(data.email,13)+'</span> <span class="matchbubble marginL5  M" title="This email was Manually matched">M</span>';

            CRM.alert(result.message, '', 'success');

            // redraw the table
            var row_index = oTable.fnGetPosition(document.getElementById(messageId));
            oTable.fnUpdate('ManuallyMatched', row_index, 4);
            oTable.fnUpdate(contact, row_index, 1);
            oTable.fnDraw();
          }
        },
        error: function() {
          CRM.alert('failure', '', 'error');
        }
      });
      error = false;
    }
    else if (first_name || last_name || email_address) {
      // console.log('Assigning message to new contact');
      if ((cj.isNumeric(cj("#tab2 .dob .month").val()) || cj.isNumeric(cj("#tab2 .dob .day").val()) || cj.isNumeric(cj("#tab2 .dob .year").val())) && ( !cj.isNumeric(cj("#tab2 .dob .month").val()) || !cj.isNumeric(cj("#tab2 .dob .day").val()) || !cj.isNumeric(cj("#tab2 .dob .year").val()))) {
        CRM.alert('Please Enter a full date of birth', 'Warning', 'warn');
        return false;
      };
      cj.ajax({
        url: '/civicrm/imap/ajax/contact/add',
        async: false,
        data: {
          prefix: prefix,
          first_name: first_name,
          middle_name: middle_name,
          last_name: last_name,
          suffix: suffix,
          email_address: email_address,
          phone: phone,
          street_address: street_address,
          street_address_2: street_address_2,
          postal_code: zip,
          city: city,
          state: state,
          dob: dob
        },
        success: function(data, status) {
          result = cj.parseJSON(data);

          // update the contact id
          if (result.is_error) {
            CRM.alert('Could not create contact: '+result.message, '', 'error');
            return false;
          }
          else {
            contactId = result.data;
            newContacts=[];
            newContacts.push(contactId);
            cj.ajax({
              url: '/civicrm/imap/ajax/matched/reassign',
              data: {
                id: messageId,
                contactId: contactId
              },
              success: function(data, status) {
                result = cj.parseJSON(data);
                if (result.is_error) {
                  CRM.alert('Could not assign message: '+result.message, '', 'error');
                  return false;
                }
                else {
                // cj.each(assign.messages, function(id, value) {
                  removeRow(messageId);
                  CRM.alert('Contact created: '+result.message, '', 'success');
                  if (email_address.length > 0) {
                    checkForMatch(email_address, contactId);
                  }
                  // AdditionalEmail.dialog('open');
                  // cj('#AdditionalEmail-popup #contacts').val(newContacts.toString());
                }
              },
              error: function() {
                CRM.alert('Failure', '', 'error');
              }
            });
          }
        }
      });
      error = false;
    }

    if (newContacts.length == 0) {
      newContacts.push(contactId);
    };

    // did we add any tags ?
    if (activity_tag.length != 0 || contact_tag.length != 0 || removedIssueCodes.length != 0 || addedIssueCodes.length != 0 || contact_position.length != 0) {

      // CONTACT POSITIONS
      if (contact_position.length) {
        cj.ajax({
          url: '/civicrm/imap/ajax/tag/add',
          async: false,
          data: {
            parentId: '292',
            contactId: newContacts.toString(),
            tags: contact_position
          },
          success: display_ajax_result,
          error: function() {
            var result = cj.parseJSON(data);
            CRM.alert(result.message, 'Error', 'error');
          }
        });
      }

      // CONTACT ISSUE CODES
      // if any of the data has changed in the issue codes tree we need to
      // submit the new data to be processed on the ajax side
      if (addedIssueCodes.length) {
        cj.ajax({
          url: '/civicrm/imap/ajax/issuecode',
          async: false,
          data: {
            contacts: newContacts.toString(),
            issuecodes: addedIssueCodes.toString(),
            action: 'create'
          },
          success: display_ajax_result,
          error: function() {
            var result = cj.parseJSON(data);
            CRM.alert(result.message, 'Error', 'error');
          }
        });
      }
      if (removedIssueCodes.length) {
        cj.ajax({
          url: '/civicrm/imap/ajax/issuecode',
          async: false,
          data: {
            contacts: newContacts.toString(),
            issuecodes: removedIssueCodes.toString(),
            action: 'delete'
          },
          success: display_ajax_result,
          error: function() {
            var result = cj.parseJSON(data);
            CRM.alert(result.message, 'Error', 'error');
          }
        });
      }

      // CONTACT KEYWORDS
      if (contact_tag) {
        cj.ajax({
          url: '/civicrm/imap/ajax/tag/add',
          async: false,
          data: {
            contactId: newContacts.toString(),
            tags: contact_tag
          },
          success: display_ajax_result,
          error: function() {
            var result = cj.parseJSON(data);
            CRM.alert(result.message, 'Error', 'error');
          }
        });
      }

      // ACTIVITY KEYWORDS
      if (activity_tag) {
        var activity_ids_array = activityId.split(',');
        cj.ajax({
          url: '/civicrm/imap/ajax/tag/add',
          async: false,
          data: {
            activityId: activityId,
            tags: activity_tag
          },
          success: display_ajax_result,
          error: function() {
            var result = cj.parseJSON(data);
            CRM.alert(result.message, 'Error', 'error');
          }
        });
      }
      error = false;
    }

    // console.log((activity_contact.length > 0) && (activity_status_id.length === 0));
    if (activity_contact.length > 0 && activity_status_id.length === 0) {
      CRM.alert('You\'ve picked a contact to Assign this message to, but also need to select a status to continue', 'Edit Activity Warning', 'warn');
      return false;
    }

    // did we edit the activity ?
    if (activity_contact.length != 0 || activity_status_id.length != 0) {
     cj.ajax({
        url: '/civicrm/imap/ajax/matched/edit',
        async: false,
        data: {
          activity_id: activityId,
          activity_contact: activity_contact,
          activity_status_id: activity_status_id,
        },
        success: display_ajax_result,
        error: function() {
          CRM.alert('Failed to Edit Activity', 'Error', 'error');
          return false;
        }
      });
      error = false;
    }

    // we didn't do anything... why did you click the thing?
    if (error) {
      CRM.alert('You have not chosen anything to update', '', 'warn');
      return false;
    }
    else {
      cj("#process-popup").dialog('close');
    }
    // do we clear it
    if (clear) {
      ClearActivity(messageId);
    }
  };

// general functions
  // paginated contact search
  cj(".seeMore").live('click', function() {
    var position = cj(this).attr('id');
    var update = parseInt(position,10)+200;
    buildContactList(update);
    cj(this).remove();
  });

  cj(".FixedHeader_Cloned th").live('click', function() {
    var clickclass = cj(this).attr('class').split(' ')[0];
    cj('.imapperbox th.'+clickclass).click();
  });

  // add highlight to selected rows in table view
  cj(".checkbox").live('click', function() {
    cj(this).parent().parent().toggleClass("highlight");
  });

  // toggle hidden email info in multi_process popup
  cj(".hidden_email_info").live('click', function() {
    var id = cj(this).data('id');
    cj("#email_"+id+" .info").removeClass('hidden_email_info').addClass('shown_email_info').html('Hide Email');
    cj("#email_"+id).removeClass('hidden_email').addClass('shown_email');
  });

  cj(".shown_email_info").live('click', function() {
    var id = cj(this).data('id');
    cj("#email_"+id+" .info").removeClass('shown_email_info').addClass('hidden_email_info').html('Show Email');
    cj("#email_"+id).removeClass('shown_email').addClass('hidden_email');
  });

  // smart date picker
  cj("#tab1 .dob .month,#tab1 .dob .day,#tab1 .dob .year").change(function() {
    if (cj.isNumeric(cj("#tab1 .dob .month").val()) && cj.isNumeric(cj("#tab1 .dob .day").val()) && cj.isNumeric(cj("#tab1 .dob .year").val())) {
      var date_string = cj("#tab1 .dob .month").val()+"/"+cj("#tab1 .dob .day").val()+"/"+cj("#tab1 .dob .year").val();
      cj('#tab1 input.form-text.dob').val(date_string);
    }
    else {
      cj('#tab1 input.form-text.dob').val('');
      return false;
    }
  });

  cj("#tab2 .dob .month,#tab2 .dob .day,#tab2 .dob .year").change(function() {
    if (cj.isNumeric(cj("#tab2 .dob .month").val()) && cj.isNumeric(cj("#tab2 .dob .day").val())  && cj.isNumeric(cj("#tab2 .dob .year").val())) {
      var date_string = cj("#tab2 .dob .month").val()+"/"+cj("#tab2 .dob .day").val()+"/"+cj("#tab2 .dob .year").val();
      cj('#tab2 input.form-text.dob').val(date_string);
    }
    else {
      cj('#tab2 input.form-text.dob').val('');
      return false;
    }
  });
});


function getUnmatched(range)
{
  if (typeof oTable != "undefined") {
    oTable.fnDestroy();
    cj('.FixedHeader_Cloned.fixedHeader.FixedHeader_Header').remove();
  }
  cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty"><span class="loading_row"><span class="loading_message">Loading message data <img src="/sites/default/themes/Bluebird/images/loading.gif"/></span></span></td>');
  cj.ajax({
    url: '/civicrm/imap/ajax/unmatched/list?range='+range,
    success: function(data, status) {
      result = cj.parseJSON(data);
      messages = result.data;
      if (result.is_error == true || messages == null || messages.stats.overview.successes == 0) {
        Table();
      }
      else {
        var html = '';
        var total_results = messages.stats.overview.successes;
        cj.each(messages.Unprocessed, function(key, value) {
          var icon ='';
          html += '<tr id="'+value.id+'" data-key="'+value.sender_email+'" class="imapper-message-box"> <td class="imap_checkbox_column" ><input class="checkbox" type="checkbox" data-delete="'+value.id+'" data-id="'+value.id+'"/></td>';

          // build a match count bubble
          countWarn = (value.email_count == 1) ? 'warn' : '';
          countMessage = (value.email_count == 1) ? 'This address should have matched automatically' : 'This email address matches '+value.email_count+' records in bluebird';
          countStatus = (value.email_count == 0) ? 'empty' : 'multi';
          countIcon = '<span class="matchbubble marginL5 '+countWarn+' '+countStatus+'" title="'+countMessage+'">'+value.email_count+'</span></td>';

          // build the name box
          if (value.sender_name != '' && value.sender_name != null) {
            html += '<td class="imap_name_column unmatched">'+shortenString(value.sender_name,20);
            if (value.sender_email != '' && value.sender_email != null) {
              html += '<span class="emailbubble marginL5">'+shortenString(value.sender_email,15)+'</span>';
              html += countIcon;
            }
            else {
              html += '<span class="emailbubble warn marginL5" title="We could not find the email address of this record">No email found!</span>';
            }
            html += '</td>';
          }
          else if (value.sender_email != '' && value.sender_email != null) {
            html += '<td class="imap_name_column unmatched"><span class="emailbubble">'+shortenString(value.sender_email,25)+'</span>';
            html += countIcon;
          }
          else {
            html += '<td class="imap_name_column unmatched"><span class="matchbubble warn" title="There was no info found in regard to the source of this message">No source info found</span></td>';
            html += countIcon;
          }

          // dealing with attachments
          if (value.attachments != 0) {
            icon = '<div class="icon attachment-icon attachment" title="'+value.attachments+' Attachments" ></div>';
          }
          html += '<td class="imap_subject_column unmatched">'+shortenString(value.subject,40) +' '+icon+'</td>';
          html += '<td class="imap_date_column unmatched"><span data-sort="'+value.updated_date_unix+'" title="'+value.updated_date_long+'">'+value.updated_date_short +'</span></td>';

          // hidden column to sort by
          if (value.match_count != 1) {
            var match_short = (value.match_count == 0) ? "NoMatch" : "MultiMatch" ;
            html += '<td class="imap_match_column hidden"><span data="'+match_short+'">'+match_short +'</span></td>';
          }
          else {
            html += '<td class="imap_match_column hidden"><span data="Error">ProcessError</span></td>';
          }

          // check for direct messages & not empty forwarded messages
          if (value.forwarder === value.sender_email) {
            html += '<td class="imap_forwarder_column"><span data-sort="'+value.forwarder.replace("@","_")+'">Direct '+shortenString(value.forwarder,10)+'</span></td>';
          }
          else if (value.forwarder != '') {
            html += '<td class="imap_forwarder_column"><span data-sort="'+value.forwarder.replace("@","_")+'">'+shortenString(value.forwarder,14)+'</span></td>';
          }
          else {
            html += '<td class="imap_forwarder_column"> N/A </td>';
          }
          html += '<td class="imap_actions_column "><span class="assign"><a href="#">Assign Contact</a></span><span class="delete"><a href="#">Delete</a></span></td> </tr>';
        });
        cj('#imapper-messages-list').html(html);
        Table();
      }
    },
    error: function() {
      CRM.alert('Unable to load messages', '', 'error');
    }
  });
}


function getMatched(range)
{
  if (typeof oTable != "undefined") {
    oTable.fnDestroy();
    cj('.FixedHeader_Cloned.fixedHeader.FixedHeader_Header').remove();
  }
  cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty"><span class="loading_row"><span class="loading_message">Loading message data <img src="/sites/default/themes/Bluebird/images/loading.gif"/></span></span></td>');
  cj.ajax({
    url: '/civicrm/imap/ajax/matched/list?range='+range,
    success: function(data, status) {
      result = cj.parseJSON(data);
      messages = result.data;
      if (result.is_error == true || messages == null || messages.stats.overview.successes == 0) {
        cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty">No records found</td>');
        Table();
      }
      else {
        var html = '';
        var total_results = messages.stats.overview.successes;
        // console.log(messages);
        cj.each(messages.Processed, function(key, value) {
          if (value.updated_date_short != null) {
            html += '<tr id="'+value.id+'" data-imap-id="'+value.id+'" data-activity-id="'+value.activity_id+'" data-contact-id="'+value.matched_to+'" class="imapper-message-box"><td class="imap_checkbox_column" > <input class="checkbox" type="checkbox" data-imap-id="'+value.id+'" data-delete="'+value.id+'" data-activity-id="'+value.activity_id+'" data-contact-id="'+value.matched_to+'"/></td>';
            if (value.contactType != '') {
              html += '<td class="imap_name_column" data-firstName="'+value.firstName +'" data-lastName="'+value.lastName +'">';
              html += '<a class="crm-summary-link" href="/civicrm/profile/view?reset=1&gid=13&id='+value.matched_to+'&snippet=4">';
              html += '<div class="icon crm-icon '+value.contactType+'-icon"></div>';
              html += '</a>';
              html += '<a href="/civicrm/contact/view?reset=1&cid='+value.matched_to+'" title="'+value.fromName+'">'+shortenString(value.fromName,19)+'</a>';
              html += ' ';
            }
            else {
              html += '<td class="imap_name_column">';
            }
            if (value.matcher) {
              // previously we called bluebird admin 0, its actually 1
              if (value.matcher == 0) {
                value.matcher = 1;
              }
              var match_string = (value.matcher != 1) ? "Manually matched by "+value.matcher_name : "Automatically Matched";
              var match_short = (value.matcher != 1) ? "M" : "A";
              match_sort = (value.matcher != 1) ? "ManuallyMatched" : "AutomaticallyMatched" ;
              html += '<span class="matchbubble marginL5 '+match_short+'" title="This email was '+match_string+'">'+match_short+'</span>';
            }
            else {
              match_sort = 'ProcessError';
            }
            html += '</td>';
            html += '<td class="imap_subject_column">'+shortenString(value.subject,40);
            if (value.attachments != 0) {
              html += '<div class="icon attachment-icon attachment" title="'+value.attachments+' Attachments" ></div>';
            }
            html += '</td>';
            html += '<td class="imap_date_column"><span data-sort="'+value.updated_date_unix+'"  title="'+value.updated_date_long+'">'+value.updated_date_short +'</span></td>';
            html += '<td class="imap_match_column  hidden">'+match_sort +'</td>';
            html += '<td class="imap_forwarder_column"><span data-sort="'+value.forwarder.replace("@","_")+'">'+shortenString(value.forwarder,14)+'</span> </td>';
            html += '<td class="imap_actions_column"><span class="process"><a href="#">Process</a></span><span class="clear"><a href="#">Clear</a></span><span class="delete"><a href="#">Delete</a></span></td> </tr>';
          }
        });
        cj('#imapper-messages-list').html(html);
        Table();
      }
    },
    error: function() {
      CRM.alert('Unable to load messages', '', 'error');
    }
  });
}


function getReports(range)
{
  if (typeof oTable != "undefined") {
    oTable.fnDestroy();
    cj('.FixedHeader_Cloned.fixedHeader.FixedHeader_Header').remove();
  }
  cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty"><span class="loading_row"><span class="loading_message">Loading message data <img src="/sites/default/themes/Bluebird/images/loading.gif"/></span></span></td>');
  cj.ajax({
    url: '/civicrm/imap/ajax/reports/list?range='+range,
    success: function(data, status) {
      result = cj.parseJSON(data);
      reports = result.data;console.log(reports);
      var html = '',
          status_val=null;
      if (reports.total == 0 || reports.Messages == null) {
        ReportTable();
      }
      else {
        cj.each(reports.Messages, function(key, value) {
          html += '<tr id="'+value.id+'" data-id="'+value.activity_id+'" data-contact_id="'+value.matched_to+'" class="imapper-message-box '+value.status_string+'"> ';
          html += '<td class="imap_column">'+shortenString(value.fromName,40)+'</td>';
          if (!value.contactType) {
            html += '<td class="imap_name_column"> </td>';
          }
          else {
            html += '<td class="imap_name_column" data-firstName="'+value.firstName +'" data-lastName="'+value.lastName +'"> <a class="crm-summary-link" href="/civicrm/profile/view?reset=1&gid=13&id='+value.matched_to+'&snippet=4"> <div class="icon crm-icon '+value.contactType+'-icon"></div> </a> <a href="/civicrm/contact/view?reset=1&cid='+value.matched_to+'" title="'+value.fromName+'">'+shortenString(value.fromName,19)+'</a> </td>';
          }
          html += '<td class="imap_subject_column">'+shortenString(value.subject,40)+'</td>';
          html += '<td class="imap_date_column"><span data-sort="'+value.updated_date_unix+'"  title="'+value.updated_date_long+'">'+value.updated_date_short +'</span></td>';
          html += '<td class="imap_date_column"><span data-sort="'+value.email_date_unix+'"  title="'+value.email_date_long+'">'+value.email_date_short +'</span></td>';

          /* #8396 SBB duplicating Civi's hover HTML to avoid an unnecessary AJAX call */
          html += '<td class="imap_date_column"><a class="crm-summary-link mail-merge-hover" href="#"><span class="mail-merge-filter-data">'+value.status_icon_class+'</span><div class="icon crm-icon mail-merge-icon mail-merge-'+value.status_icon_class+'"></div><div class="crm-tooltip-wrapper"><div class="crm-tooltip">'+value.status_string+'</div></div></a>&nbsp;</td>';
          /* #8396 SBB Adding the new tag column */
          html += '<td class="imap_date_column">';
          if (Number(value.tagCount) > 0) {
            html += '<a class="crm-summary-link mail-merge-hover" href="/civicrm/imap/ajax/reports/getTags?id=' + value.id + '"><div class="mail-merge-tags mail-merge-icon icon crm-icon"></div></a>&nbsp;';
          }
          html += '</td>';

          html += '<td class="imap_forwarder_column"><span data-sort="'+value.forwarder.replace("@","_")+'">'+shortenString(value.forwarder,14)+'</span></td></tr>';
        });

        cj('#imapper-messages-list').html(html);
        ReportTable();
        cj('#total').html(reports.Total);
        cj('#total_unmatched').html(reports.Unmatched);
        cj('#total_Matched').html(reports.Matched);
        cj('#total_Cleared').html(reports.Cleared);
        cj('#total_Errors').html(reports.Errors);
        cj('#total_Deleted').html(reports.Deleted);
      };
    },
    error: function() {
      CRM.alert('Unable to load messages', '', 'error');
    }
  });
}


// needed to format timestamps to allow sorting: make a hidden date attribute
// with the non-readable date (date(U)) and sort on that
cj.extend(cj.fn.dataTableExt.oSort, {
  "title-string-pre": function(a) {
    return a.match(/data-sort="(.*?)"/)[1].toLowerCase();
  },
  "title-string-asc": function(a, b) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
  },
  "title-string-desc": function(a, b) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
  }
});


function Table()
{
  oTable = cj("#sortable_results").dataTable({
    "sDom":'<p><"controlls"lif><"clear">rt <p>',//add i here this is the number of records
    // "iDisplayLength": 1,
    "sPaginationType": "full_numbers",
    "aaSorting": [[ 3, "desc" ]],
    "aoColumnDefs": [
      { 'bSortable': false, 'aTargets': [ 0 ] },
      { 'bSortable': false, 'aTargets': [ 6 ] },
      { "sType": "title-string", "aTargets": [ 3,5 ] },
    ],
    "oColVis": { "activate": "mouseover" },
    'aTargets': [ 1 ],
    "iDisplayLength": 50,
    "aLengthMenu": [[10, 50, 100, -1], [10, 50, 100, 'All']],
    "bAutoWidth": false,
    "oLanguage": {
      "sEmptyTable": "No records found"
    }
  });
  oHeader = new FixedHeader(oTable, {zTop:'auto'});
  oHeader.fnUpdate();
}


function ReportTable()
{
  oTable = cj("#sortable_results").dataTable({
    "sDom":'<p><"controlls"lif><"clear">rt <p>',//add i here this is the number of records
    // "iDisplayLength": 1,
    "sPaginationType": "full_numbers",
    "aaSorting": [[ 3, "desc" ]],
    "aoColumnDefs": [ { "sType": "title-string", "aTargets": [ 3,4 ] },
                    ],
    "aoColumns": [ { "sWidth":"12%" },
                   { "sWidth":"18%" },
                   { "sWidth":"14%" },
                   { "sWidth":"12%" },
                   { "sWidth":"10%" },
                   { "sWidth":"1%" },
                   { "sWidth":"1%" },
                   { "sWidth":"22%" },
                 ],
    'aTargets': [ 1 ],
    "iDisplayLength": 50,
    "aLengthMenu": [[10, 50, 100, -1], [10, 50, 100, 'All']],
    "bAutoWidth": false,
    "oLanguage": {
      "sEmptyTable": "No records found"
    },
  });
  new FixedHeader(oTable, {zTop:'auto'});
}


cj(".range").live('change', function() {
  if (cj("#Activities").length) {
    getMatched(cj('#range').attr("value"));
  }
  else if(cj("#Unmatched").length) {
    getUnmatched(cj('#range').attr("value"));
  }
  else if(cj("#Reports").length) {
    getReports(cj('#range').attr("value"));
  }
});

cj(".checkbox_switch").live('click', function(e) {
  if (this.checked) {
    cj('.checkbox').prop('checked', this.checked)
    cj('.checkbox').parent().parent().addClass('highlight');
  }
  else {
    cj('.checkbox').prop('checked', this.checked);
    cj('tr').removeClass('highlight');
  }
});

cj(".stats_overview").live('click', function() {
  cj(".stats_overview").removeClass('active');
  cj(this).addClass('active');
});

cj(".Total").live('click', function() {
  oTable.fnFilter("", 5, false, false);
});
cj(".Unmatched").live('click', function() {
  oTable.fnFilter('unmatched', 5);
});
cj(".Matched").live('click', function() {
  oTable.fnFilter('^matched', 5, true);
});
cj(".Cleared").live('click', function() {
  oTable.fnFilter('cleared', 5);
});
/** removed per NYSS #8396
cj(".Errors").live('click', function() {
  oTable.fnFilter('error', 5);
});
**/
cj(".Deleted").live('click', function() {
  oTable.fnFilter('deleted', 5);
});



function buildContactList(loop)
{
  var contactsHtml = '';
  for (var i = loop; i < contacts.length && i < loop + 200; i++) {
    // calculate the aprox age
    if (contacts[i].birth_date) {
      var date = new Date();
      var year  = date.getFullYear();
      var birth_year = contacts[i].birth_date.substring(0,4);
      var age = year - birth_year;
    }
    contactsHtml += '<div class="imapper-contact-box" data-contact-id="'+contacts[i].id+'">';
    contactsHtml += '<div class="imapper-address-select-box">';
    contactsHtml += '<input type="checkbox" class="imapper-contact-select-button" name="contact_id" value="'+contacts[i].id+'" />';
    contactsHtml += '</div>';
    contactsHtml += '<div class="imapper-address-box">';
    if (contacts[i].display_name) {
      contactsHtml += shortenString(contacts[i].display_name,30) + '<br/>';
    }
    if (contacts[i].birth_date) {
      contactsHtml += '<strong>'+age+'</strong> - '+contacts[i].birth_date + '<br/>';
    }
    if (contacts[i].email) {
      contactsHtml += shortenString(contacts[i].email,30) + '<br/>';
    }
    if (contacts[i].phone) {
      contactsHtml += shortenString(contacts[i].phone,30) + '<br/>';
    }
    if (contacts[i].street_address) {
      contactsHtml += shortenString(contacts[i].street_address,30) + '<br/>';
    }
    if (contacts[i].city) {
      contactsHtml += contacts[i].city + ', ' + contacts[i].name +" "+ contacts[i].postal_code + '<br/>';
    }
    contactsHtml += '</div></div>';
    contactsHtml += '<div class="clear"></div>';
  }
  if (contacts.length > loop + 200) {
    contactsHtml += '<span class="seeMore" id="'+loop+'">see more</span>';
  };

  if (loop == 0) {
    cj('#imapper-contacts-list').html(contactsHtml);
  }
  else {
    cj('#imapper-contacts-list').append(contactsHtml);
  }
}


// Create shortended String with title tag for hover
// If subject is null return N/A
function shortenString(subject, length)
{
  if (subject) {
    if (subject.length > length) {
      var safe_subject = '<span title="'+subject+'" data-sort="'+subject+'">'+subject.substring(0,length)+"...</span>";
      return safe_subject;
    }
    else {
      return '<span data-sort="'+subject+'">'+subject+'</span>';
    }
  }
  else {
    return '<span title="Not Available" data-sort="Not Available"> N/A </span>';
  }
}


// Look for empty rows that match the KEY of a matched row
// Remove them from the view so the user doesn't re-add / create duplicates
// key = user_email
function checkForMatch(key,newContacts)
{
  // console.log('checking',key,newContacts);
  cj(".this_address").html(key);
  cj('.imapper-message-box').each(function(i, item) {
    var check = cj(this).data('key');
    var messageId = cj(this).attr('id');
    if (key == check) {
      if (cj('.matchbubble.empty', this).length) {
        cj.ajax({
          url: '/civicrm/imap/ajax/unmatched/assign',
          async: false,
          data: {
            id: messageId,
            contactId: newContacts
          },
          success: function(data, status) {
            if (data != null || data != '') {
              var result = cj.parseJSON(data);
              if (result.is_error) {
                // CRM.alert(('Other Records not Matched'), ts(actionData.name), actionData['errorClass']);
              }
              else {
                removeRow(messageId);
                CRM.alert(('Other records automatically matched'), '', 'success');
              }
            }
          }
        });
      }
    };
  });
}


// removes row from the UI, forces table reload
// takes an array, or a single object
function removeRow(id)
{
  if (cj.isArray(id)) {
    cj.each(id, function(index, value) {
      var row_index = oTable.fnGetPosition(document.getElementById(value));
      oTable.fnDeleteRow(row_index);
    });
  }
  else if (cj("#"+id).length) {
    var row_index = oTable.fnGetPosition(document.getElementById(id));
    oTable.fnDeleteRow(row_index);
  }
}


function string_replace(haystack, find, sub)
{
  return haystack.split(find).join(sub);
}
