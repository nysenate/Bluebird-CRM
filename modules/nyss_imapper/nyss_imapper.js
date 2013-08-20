var messages = [];
var contacts = [];

cj(document).ready(function(){

  var first_name = cj('#tab1 .first_name').val();
  var last_name = cj('#tab1 .last_name').val();
  var city = cj('#tab1 .city').val();
  var phone = cj('#tab1 .phone').val();
  var street_address = cj('#tab1 .street_address').val();
  var email_address = cj('#tab1 .email_address').val();

  var reset = cj('#reset');
  var filter = cj('#filter');
  var assign = cj('#assign');
  var reassign = cj('#reassign');
  var create = cj('#add-contact');
  var createReassign = cj('#add-contact-reassign');

  // onpageload
  if(cj("#Activities").length){
    getMatchedMessages();
  }else if(cj("#Unmatched").length){
    getUnmatchedMessages();
  }else if(cj("#Reports").length){
    getReports();
    // console.log('reports');
  }
  cj('#search_help').live('click', function() {
    cj("#help-popup").dialog('open');
  });

  // Dialogs
  cj( "#help-popup" ).dialog({
    modal: true,
    width: 600,
    autoOpen: false,
    resizable: false,
    draggable: false
  });

  // After we've already matched something
  cj( "#no_find_match" ).dialog({
    modal: true,
    dialogClass: 'no_find_match',
    width: 370,
    autoOpen: false,
    resizable: false,
    draggable: false
  });

  // add a delete conform popup thats alarmingly red
  cj( "#delete-confirm" ).dialog({
    modal: true,
    dialogClass: 'delete_popup_class',
    width: 370,
    autoOpen: false,
    resizable: false,
    draggable: false
  });

  // add a clear conform popup
  cj( "#clear-confirm" ).dialog({
    modal: true,
    width: 370,
    autoOpen: false,
    resizable: false,
    draggable: false
  });



  // add a find match popup
  cj( "#find-match-popup" ).dialog({
    modal: true,
    height: 580,
    width: 960, // in ie the popup was overflowing
    autoOpen: false,
    resizable: false,
    title: 'Loading Data',
    draggable: false,
    buttons: {
      Cancel: function() {
        cj( this ).dialog( "close" );
      }
    }
  });

  // add a loading icon popup
  cj( "#loading-popup" ).dialog({
    modal: true,
    width: 200,
    autoOpen: false,
    resizable: false,
    title: 'Please Wait',
    draggable: false
  });

  // add a reloading icon popup
  cj( "#reloading-popup" ).dialog({
    modal: true,
    width: 200,
    autoOpen: false,
    resizable: false,
    title: 'Please Wait',
    draggable: false
  });

  // add a tagging popup
  cj( "#tagging-popup" ).dialog({
    modal: true,
    height: 565,
    width: 960,
    autoOpen: false,
    resizable: false,
    title: 'Loading Data',
    draggable: false
  });

  cj( "#matchCheck-popup" ).dialog({
    modal: true,
    width: 400,
    autoOpen: false,
    resizable: false,
    draggable: false
  });


  cj( "#fileBug-popup" ).dialog({
    modal: true,
    width: 500,
    autoOpen: false,
    resizable: false,
    draggable: false
  });


  cj( "#AdditionalEmail-popup" ).dialog({
    modal: true,
    width: 500,
    autoOpen: false,
    resizable: false,
    draggable: false,
    open:function () {
      cj(this).closest(".ui-dialog").find(".ui-button:first").addClass("primary_button");
    },
    buttons: {
      "Yes": function() {
        var add_email = cj('#add_email').val();
        var contacts = cj('#contacts').val();
        cj.ajax({
          url: '/civicrm/imap/ajax/addEmail',
          data: {
            email: add_email,
            contacts: contacts
          },
          success: function(data,status) {
            if(data != null || data != ''){
              helpMessage('Email Added.');
            }
          }
        });
        cj('#assign').click();

      },
      No: function() {
        cj('#add_email').val('');
        cj('#assign').click();
      }
    }
  });
  // BOTH MATCHED & UNMATCHED
  // file a bug
  cj(".fileBug").live('click', function() {
    cj("#fileBug-popup").dialog('open');
    cj('#description').val('');
  });

  cj( "#fileBug-popup" ).dialog({
    open:function () {
      cj(this).closest(".ui-dialog").find(".ui-button:first").addClass("primary_button");
    },
    buttons: {
      "Report Problem": function() {
        cj.each(jQuery.browser, function(i, val) {
          if(cj.browser.msie){
            browsertype = "IE";
          }else if(cj.browser.webkit){
            browsertype = "Webkit";
          }else if(cj.browser.opera){
            browsertype = "Opera";
          }else if(cj.browser.mozilla){
            browsertype = "Mozilla";
          }
        });
        var description = cj('#description').val();
        var browser =  browsertype+" v."+(parseInt(cj.browser.version, 10) );
        var id = cj('#id').val();
        cj( this ).dialog( "close" );
        cj.ajax({
          url: '/civicrm/imap/ajax/fileBug',
          data: {
            browser: browser,
            id: id,
            description: description
          },
          success: function(data,status) {
            if(data != null || data != ''){
              helpMessage('Report Filed.');
            }
          }
        });
      },
      Cancel: function() {
        cj( this ).dialog( "close" );
      }
    }
  });


  // search function in find_match and edit_match
  filter.live('click', function() {
    cj('#imapper-contacts-list').html('Searching...');
    // checks for deault data
    if(cj('#tab1 .first_name').val() != "First Name"){ var first_name = cj('#tab1 .first_name').val();}
    if(cj('#tab1 .last_name').val() != "Last Name"){ var last_name = cj('#tab1 .last_name').val();}
    if(cj('#tab1 .city').val() != "City"){var city = cj('#tab1 .city').val();}
    if(cj('#tab1 .phone').val() != "Phone Number"){var phone = cj('#tab1 .phone').val();}
    if(cj('#tab1 .street_address').val() != "Street Address"){var street_address = cj('#tab1 .street_address').val();}
    if(cj('#tab1 .email_address').val() != "Email Address"){var email_address = cj('#tab1 .email_address').val();}
    if(cj('#tab1 .dob').val() != "yyyy-mm-dd"){var dob = cj('#tab1 .dob').val();}
    if(cj('#tab1 .state').val() != ""){var state = cj('#tab1 .state').val();}
/*    console.log(state);*/

    if((first_name) || (last_name) || (city) || (phone) || (street_address) || (email_address) || (dob)){
      cj.ajax({
        url: '/civicrm/imap/ajax/searchContacts',
        async:false,
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
        success: function(data,status) {
          if(data != null || data != ''){
            contacts = cj.parseJSON(data);
            if(contacts.code == 'ERROR'){
              cj('#imapper-contacts-list').html(contacts.message);
            }else{
              cj('.contacts-list').html('').append("<strong>"+(contacts.length )+' Found</strong>');
              buildContactList(0);
              cj("#reassign").show();
            }
          }
        }
      });
    }else{
      alert('enter a search query');
    }
    return false;
  });

  // delete confirm & processing both pages
  cj(".delete").live('click', function() {
    var messageId = cj(this).parent().parent().attr('id');
    var contactId = cj(this).parent().parent().attr('data-contact_id');
    var row = cj(this).parent().parent();

    // reset the headers
    if(cj("#Activities").length){
      cj("#delete-confirm").dialog({ title:  "Delete this message from Matched Messages ?"});
    }else{
      cj("#delete-confirm").dialog({ title:  "Delete this message from Unmatched Messages ?"});
    }

    cj( "#delete-confirm" ).dialog({
      open:function () {
        cj(this).closest(".ui-dialog").find(".ui-button:first").addClass("primary_button");
      },
      buttons: {
        "Delete": function() {
          cj( this ).dialog( "close" );
          if(cj("#Activities").length){
            DeleteActivity(messageId);
          }else{
            DeleteMessage(messageId);
          }
        },
        Cancel: function() {
          cj( this ).dialog( "close" );
        }
      }
    });
    cj("#delete-confirm").dialog('open');
    return false;
  });


  // multi_delete confirm & processing both pages
  cj(".multi_delete").live('click', function() {
    cj("#loading-popup").dialog('open');

    // delete_ids = message id / activity id
    var delete_ids = new Array();
    // delete_secondary = imap id / contact id
    var delete_secondary = new Array();
    var rows = new Array();

    cj('#imapper-messages-list input:checked').each(function() {
      delete_ids.push(cj(this).attr('name'));
      delete_secondary.push(cj(this).attr('id'));
      rows.push(cj(this).parent().parent().attr('id')); // not awesome but ok
    });
    if(!rows.length){
      cj("#loading-popup").dialog('close');
      alert('Use the checkbox to select one or more messages to delete');
      return false;
    }

    // reset the headers
    if(cj("#Activities").length){
      cj("#delete-confirm").dialog({ title:  "Delete "+delete_ids.length+" messages from Matched Messages?"});
    }else{
      cj("#delete-confirm").dialog({ title:  "Delete "+delete_ids.length+" messages from Unmatched Messages?"});
    }

    cj( "#delete-confirm" ).dialog({
      open:function () {
        cj(this).closest(".ui-dialog").find(".ui-button:first").addClass("primary_button");
      },
      buttons: {
        "Delete": function() {
          cj( this ).dialog( "close" );
          if(cj("#Activities").length){
            cj("#reloading-popup").dialog('open');
            cj.each(delete_ids, function(key, value) {
              DeleteActivity(value);
            });
            cj("#reloading-popup").dialog('close');
          }else{
            cj("#reloading-popup").dialog('open');
            cj.each(delete_ids, function(key, value) {
              DeleteMessage(value,delete_secondary[key]);
            });
            cj("#reloading-popup").dialog('close');

          }
        },
        Cancel: function() {
          cj( this ).dialog("close");
        }
      }
    });
    cj("#loading-popup").dialog('close');
    cj("#delete-confirm").dialog('open');
    return false;
  });

  // dirty toggles
  // toggle hidden email info in multi_tag popup
  cj(".hidden_email_info").live('click', function(){
    var id = cj(this).data('id');
    cj("#email_"+id+" .info").removeClass('hidden_email_info').addClass('shown_email_info').html('Hide Email');
    cj("#email_"+id).removeClass('hidden_email').addClass('shown_email');
  });

  cj(".shown_email_info").live('click', function(){
    var id = cj(this).data('id');
    cj("#email_"+id+" .info").removeClass('shown_email_info').addClass('hidden_email_info').html('Show Email');
    cj("#email_"+id).removeClass('shown_email').addClass('hidden_email');
  });

  // toggle Debug info for find match message popup
  cj(".debug_on").live('click', function() {
    var debug_info = cj(".debug_info").html();
    cj("#message_left_email").prepend(debug_info);
    cj(this).removeClass('debug_on').addClass('debug_off').html('Hide Debug info');
  });

  cj(".debug_off").live('click', function() {
    cj("#message_left_email .debug_remove").remove();
    cj(this).removeClass('debug_off').addClass('debug_on').html('Show Debug info');
  });

// UNMATCHED
  // assign a message to a contact Unmatched page
  cj('#preAssign').click(function() {
    var contactRadios = cj('input[name=contact_id]');
    var contactIds = '';
    cj.each(contactRadios, function(idx, val) {
      if(cj(val).attr('checked')) {
        if(contactIds != '')
          contactIds = contactIds+',';
        contactIds = contactIds + cj(val).val();
      }
    });
    if(contactIds !='' ){
      cj("#AdditionalEmail-popup").dialog('open');
      cj('#AdditionalEmail-popup #contacts').val(contactIds);
      cj("#find-match-popup").dialog('close');
    }else{
      alert("Please Choose a contact");
    };

  });

  // assign a message to a contact Unmatched page
  assign.click(function() {
    var messageId = cj('#id').val();
    var contactRadios = cj('input[name=contact_id]');
    var contactIds = '';
    var add_email = cj('#add_email').val();
    cj("#AdditionalEmail-popup").dialog( "close" );

    cj.each(contactRadios, function(idx, val) {
      if(cj(val).attr('checked')) {
        if(contactIds != '')
          contactIds = contactIds+',';
        contactIds = contactIds + cj(val).val();
      }
    });
    if(contactIds !='' ){
      cj.ajax({
        url: '/civicrm/imap/ajax/assignMessage',
        data: {
          messageId: messageId,
          contactId: contactIds
        },
        success: function(data, status) {
          data = cj.parseJSON(data);
          if (data.code == 'ERROR'){
            alert('Could Not Assign message : '+data.message);
          }else{
            cj.each(data.assigned, function(key, value) {
              removeRow(messageId);
              helpMessage(value.message);
              checkForMatch(add_email,contactIds);
            });
            cj("#find-match-popup").dialog('close');
          }
        }
      });
      return false;
    }else{
      alert("Please Choose a contact");
    };
  });

  // create a new contact unmatched page
  create.click(function() {
    var create_messageId = cj('#id').val();
    var create_first_name = cj("#tab2 .first_name").val();
    var create_last_name = cj("#tab2 .last_name").val();
    var create_email_address = cj("#tab2 .email_address").val();
    var create_phone = cj("#tab2 .phone").val();
    var create_street_address = cj("#tab2 .street_address").val();
    var create_street_address_2 = cj("#tab2 .street_address_2").val();
    var create_zip = cj("#tab2 .zip").val();
    var create_city = cj("#tab2 .city").val();
    var create_dob = cj("#tab2 .dob").val();
    var create_state = cj("#tab2 .state").val();

    if((create_first_name)||(create_last_name)||(create_email_address)){
      cj.ajax({
        url: '/civicrm/imap/ajax/createNewContact',
        data: {
          messageId: create_messageId,
          first_name: create_first_name,
          last_name: create_last_name,
          email_address: create_email_address,
          phone: create_phone,
          street_address: create_street_address,
          street_address_2: create_street_address_2,
          postal_code: create_zip,
          city: create_city,
          state: create_state,
          dob: create_dob
        },
        success: function(data, status) {
          contactData = cj.parseJSON(data);
          if (contactData.code == 'ERROR' || contactData.code == '' || contactData == null ){
            alert('Could Not Create Contact : '+contactData.message);
            return false;
          }else{
            cj.ajax({
              url: '/civicrm/imap/ajax/assignMessage',
              data: {
                messageId: create_messageId,
                contactId: contactData.contact
              },
              success: function(data, status) {
                assign = cj.parseJSON(data);
                if (assign.code == 'ERROR' || assign.code == '' || assign == null ){
                  alert('Could Not Assign Message : '+assign.message);
                  return false;
                }else{
                  cj.each(assign.assigned, function(key, value) {
                    removeRow(create_messageId);
                    helpMessage('Contact created and '+value.message);
                    if(create_email_address.length > 0){
                      checkForMatch(create_email_address,contactData.contact);
                    }
                  });
                  cj("#find-match-popup").dialog('close');
                }
              },
              error: function(){
                alert('failure');
              }
            });
          }
        }
      });
      return false;
    }else{
      alert("Required: First Name or Last Name or Email");
    }
  });

// create a new contact and Reassign message to them
  createReassign.click(function() {
    var create_messageId = cj('#id').val();
    var create_first_name = cj("#tab2 .first_name").val();
    var create_last_name = cj("#tab2 .last_name").val();
    var create_email_address = cj("#tab2 .email_address").val();
    var create_phone = cj("#tab2 .phone").val();
    var create_street_address = cj("#tab2 .street_address").val();
    var create_street_address_2 = cj("#tab2 .street_address_2").val();
    var create_zip = cj("#tab2 .zip").val();
    var create_city = cj("#tab2 .city").val();
    var create_dob = cj("#tab2 .dob").val();
    var create_state = cj("#tab2 .state").val();

    if((create_first_name)||(create_last_name)||(create_email_address)){
      cj.ajax({
	url: '/civicrm/imap/ajax/createNewContact',
	data: {
	  messageId: create_messageId,
	  first_name: create_first_name,
	  last_name: create_last_name,
	  email_address: create_email_address,
	  phone: create_phone,
	  street_address: create_street_address,
	  street_address_2: create_street_address_2,
	  postal_code: create_zip,
	  city: create_city,
	  state: create_state,
	  dob: create_dob
	},
	success: function(data, status) {
	  contactData = cj.parseJSON(data);
	  if (contactData.code == 'ERROR' || contactData.code === '' || contactData === null ){
	    alert('Could Not Create Contact : '+contactData.message);
	    return false;
	  }else{
	    cj.ajax({
		url: '/civicrm/imap/ajax/reassignActivity',
		data: {
		  id: create_messageId,
		  change: contactData.contact
		},
		success: function(data, status) {
		  var data = cj.parseJSON(data);
		  if (data.code =='ERROR'){
		    alert('Could not reassign Message : '+data.message);
		  }else{
		    cj("#find-match-popup").dialog('close');
		    // reset activity to new data
		    cj('#'+create_messageId).attr("data-contact_id",data.contact_id); // contact_id
		    cj('#'+create_messageId+" .name").attr("data-firstname",data.first_name); // first_name
		    cj('#'+create_messageId+" .name").attr("data-lastname",data.last_name); // last_name
		    cj('#'+create_messageId+" .match").html("ManuallyMatched");
		    contact = '<a href="/civicrm/profile/view?reset=1&amp;gid=13&amp;id='+data.contact_id+'&amp;snippet=4" class="crm-summary-link"><div class="icon crm-icon '+data.contact_type+'-icon" title="'+data.contact_type+'"></div></a><a title="'+data.display_name+'" href="/civicrm/contact/view?reset=1&amp;cid='+data.contact_id+'">'+data.display_name+'</a><span class="emailbubble marginL5">'+shortenString(data.email,13)+'</span> <span class="matchbubble marginL5  M" title="This email was Manually matched">M</span>';

		    helpMessage(data.message);
		    // redraw the table
		    var oTable = cj('#sortable_results').dataTable();
		    var row_index = oTable.fnGetPosition(document.getElementById(create_messageId));
		    oTable.fnUpdate('ManuallyMatched', row_index, 4 );
		    oTable.fnUpdate(contact, row_index, 1 );
		    oTable.fnDraw();
		  }
		},
		error: function(){
		  alert('failure');
		}
	    });
	  }
      return false;
    }});
    }else{
      alert("Required: First Name or Last Name or Email");
    }
  });
  // opening find match window Unmatched
  cj(".find_match").live('click', function() {
    cj("#loading-popup").dialog('open');

    var messageId = cj(this).parent().parent().attr('id');
    var imapId = cj(this).parent().parent().attr('data-imap_id');
    var firstName = cj(this).parent().parent().children('.name').attr('data-firstName');
    var lastName = cj(this).parent().parent().children('.name').attr('data-lastName');

    cj("#tabs :input[type='text']").val("");


    cj('#imapper-contacts-list').html('');
    cj.ajax({
      url: '/civicrm/imap/ajax/getMessageDetails',
      data: {id: messageId },
      success: function(data,status) {
        message = cj.parseJSON(data);
        cj("#loading-popup").dialog('close');
        if(message.code == 'ERROR'){
          if(message.clear =='true')  removeRow(messageId);
          alert('Unable to load Message : '+ message.message);
        }else{
          var icon ='';
          if( message.attachmentfilename ||  message.attachmentname ||  message.attachment){
            if(message.attachmentname ){var name = message.attachmentname}else{var name = message.attachmentfilename};
            icon = '<div class="ui-icon ui-icon-link attachment" title="'+name+'"></div>'
          }
          cj('#message_left_header').html('');
          cj('#message_left_header').append("<span class='popup_def'>From: </span>");
          if(message.sender_name) cj('#message_left_header').append(shortenString(message.sender_name,50));
          if(message.sender_email) cj('#message_left_header').append("<span class='emailbubble marginL5'>"+shortenString(message.sender_email)+"</span>");

          cj('#message_left_header').append("<br/><span class='popup_def'>Subject: </span>"+shortenString(message.subject,55)+" "+ icon+"<br/><span class='popup_def'>Date Forwarded: </span>"+message.date_long+"<br/>");

          if ((message.forwarder != message.sender_email)){
            cj('#message_left_header').append("<span class='popup_def'>Forwarded by: </span><span class='emailbubble'>"+ message.forwarder+"</span> @ "+ message.updated_long+ "<br/>");
          }else{
            cj('#message_left_header').append("<span class='popup_def'>&nbsp;</span>No forwarded content found<br/>");
          }

          if ((message.filebug == true)){
          	cj('#message_left_header').append("<span class='popup_def'>&nbsp;</span><a class='fileBug'>Report Bug</a><br/>");
          }
          cj('#message_left_email').html(message.body+"<hr/>");
          cj.each(message.attachments, function(key, value) {
            if((!value.rejection) || (value.rejection == '')){
              cj('#message_left_email').append(value.fileName+" ("+((value.size / 1024) / 1024).toFixed(2)+" MB)<br/>");
            }else{
              cj('#message_left_email').append("<span class='rejected'>"+value.fileName+" was rejected ("+value.rejection+")</span><br/>");
            }
          });
          cj('.first_name, .last_name, .phone, .street_address, .street_address_2, .city, .email_address').val('');
          cj('#id').val(messageId);
          cj("#find-match-popup").dialog({ title:  "Reading: "+shortenString(message.subject,100) });
          cj("#find-match-popup").dialog('open');
          cj("#tabs").tabs();
          cj('#tabs').tabs({ selected: 0 });
          cj('.email_address').val(message.sender_email);
          if(message.sender_email) cj('#filter').click();
          cj('.first_name').val(firstName);
          cj('.last_name').val(lastName);
          cj('#AdditionalEmail-popup #add_email').val('');
          cj('#AdditionalEmail-popup #add_email').val(message.sender_email);
        }
      },
      error: function(){
        alert('Unable to load Message');
      }
    });
    return false;
  });

// MATCHED
// MATCHED singular Functions

  // reassign activity to contact on the matched page
  reassign.click(function() {
    var activityId = cj('#id').val();
    // only grabs the 1st one

    var contactRadios = cj('input[name=contact_id]');
    var contactIds = '';
    cj.each(contactRadios, function(idx, val) {
      if(cj(val).attr('checked')) {
        if(contactIds != '')
          contactIds = contactIds+',';
        contactIds = contactIds + cj(val).val();
      }
    });

    if (contactIds =='' ){
      alert("Please select a contact");
      return false;
    }else{

    cj.ajax({
      url: '/civicrm/imap/ajax/reassignActivity',
      data: {
        id: activityId,
        change: contactIds
      },
      success: function(data, status) {
        var data = cj.parseJSON(data);
        if (data.code =='ERROR'){
          alert('Could not reassign Message : '+data.message);
        }else{
          cj("#find-match-popup").dialog('close');
          // reset activity to new data
          cj('#'+activityId).attr("data-contact_id",data.contact_id); // contact_id
          cj('#'+activityId+" .name").attr("data-firstname",data.first_name); // first_name
          cj('#'+activityId+" .name").attr("data-lastname",data.last_name); // last_name
          cj('#'+activityId+" .match").html("ManuallyMatched");
          contact = '<a href="/civicrm/profile/view?reset=1&amp;gid=13&amp;id='+data.contact_id+'&amp;snippet=4" class="crm-summary-link"><div class="icon crm-icon '+data.contact_type+'-icon" title="'+data.contact_type+'"></div></a><a title="'+data.display_name+'" href="/civicrm/contact/view?reset=1&amp;cid='+data.contact_id+'">'+data.display_name+'</a><span class="emailbubble marginL5">'+shortenString(data.email,13)+'</span> <span class="matchbubble marginL5  M" title="This email was Manually matched">M</span>';

          helpMessage(data.message);
          // redraw the table
          var oTable = cj('#sortable_results').dataTable();
          var row_index = oTable.fnGetPosition(document.getElementById(activityId));
          oTable.fnUpdate('ManuallyMatched', row_index, 4 );
          oTable.fnUpdate(contact, row_index, 1 );
          oTable.fnDraw();
        }
      },
      error: function(){
        alert('failure');
      }
    });
    };
    return false;
    cj("#reassign").hide();
  });
  /// remove activity from the activities screen, but don't delete it Matched
  cj(".clear_activity").live('click', function() {
    cj("#loading-popup").dialog('open');
    // var activityId = cj(this).parent().parent().attr('data-id');
    var Id = cj(this).parent().parent().attr('id');

    cj( "#clear-confirm" ).dialog({
      buttons: {
        "Clear": function() {
          ClearActivity(Id);
        },
        Cancel: function() {
          cj("#clear-confirm").dialog('close');
        }
      }
    });
    cj("#clear-confirm").dialog({ title:  "Remove Message From List?"});
    cj("#loading-popup").dialog('close');
    cj("#clear-confirm").dialog('open');
    return false;
  });

  // Edit a match allready assigned to an Activity Matched Screen
  cj(".edit_match").live('click', function() {
    cj("#loading-popup").dialog('open');
    cj("#reassign").hide();

    var activityId = cj(this).parent().parent().attr('id');
    var contactId = cj(this).parent().parent().attr('data-contact_id');
    cj("#tabs :input[type='text']").val("");

    cj('#imapper-contacts-list').html('');

    cj.ajax({
      url: '/civicrm/imap/ajax/getActivityDetails',
      data: {id: activityId, contact: contactId },
      success: function(data,status) {
        message = cj.parseJSON(data);
        if (message.code == 'ERROR'){
          alert('Could not load message Details: '+message.message);
          cj("#loading-popup").dialog('close');
          if(message.clear =='true')   removeRow(activityId);
        }else{
          cj('#message_left_header').html('');

          if(message.sender_name || message.sender_email) cj('#message_left_header').html('').append("<span class='popup_def'>From: </span>");
          if(message.sender_name) cj('#message_left_header').append(message.sender_name +"  ");
          if(message.sender_email) cj('#message_left_header').append("<span class='emailbubble'>"+ message.sender_email+"</span>");
          cj('#message_left_header').append("<br/><span class='popup_def'>Subject: </span>"+shortenString(message.subject,55) +"<br/><span class='popup_def'>Date Forwarded: </span>"+message.date_long+"<br/>");
          cj('.email_address').val(message.fromEmail);

          if ((message.forwarder != message.sender_email)){
            cj('#message_left_header').append("<span class='popup_def'>Forwarded by: </span><span class='emailbubble'>"+ message.forwarder+"</span> @ "+ message.updated_long+ "<br/>");
          }else{
            cj('#message_left_header').append("<span class='popup_def'>&nbsp;</span>No forwarded content found<br/>");
          }
          // if we are on crmdev or crmtest show a debug window
          if ((message.filebug == true)){
          	cj('#message_left_header').append("<span class='popup_def'>&nbsp;</span><a class='fileBug'>Report Bug</a><br/>");
          }
          cj('#message_left_email').html(message.body+"<hr/>");

          cj.each(message.attachments, function(key, value) {
            if((!value.rejection) || (value.rejection == '')){
              cj('#message_left_email').append(value.fileName+" ("+((value.size / 1024) / 1024).toFixed(2)+" MB)<br/>");
            }else{
              cj('#message_left_email').append("<span class='rejected'>"+value.fileName+" was rejected ("+value.rejection+")</span><br/>");
            }
          });

          cj('#id').val(activityId);
          cj("#loading-popup").dialog('close');
          cj("#find-match-popup").dialog({ title:  "Reading: "+shortenString(message.subject,100)  });
          cj("#find-match-popup").dialog('open');
          cj("#tabs").tabs();
          cj('#imapper-contacts-list').html('').append("<strong>currently matched to : </strong><br/>           "+'<a href="/civicrm/contact/view?reset=1&cid='+message.matched_to+'" title="'+message.sender_name+'">'+shortenString(message.sender_name,35)+'</a>'+" <br/><i>&lt;"+ message.sender_email+"&gt;</i> <br/>"+ cj('.dob').val()+"<br/> "+ cj('.phone').val()+"<br/> "+  cj('.street_address').val()+"<br/> "+  cj('.city').val()+"<br/>");
        }
      },
      error: function(){
        alert('unable to Load Message');
      }
    });
    return false;
  });

  // add tag modal Matched screen
  cj(".add_tag").live('click', function(){
    cj("#loading-popup").dialog('open');

    var activityId = cj(this).parent().parent().attr('id');
    var contactId = cj(this).parent().parent().attr('data-contact_id');
    cj('#message_left_tag').html('').removeClass('tag_over_ride');
    cj('#message_left_header_tag').html('');
    cj('#message_left_tag').html('').html('<div id="message_left_header_tag"></div><div id="message_left_email_tag"></div>');
    cj('#contact_ids').val('').val(contactId);
    cj('#activity_ids').val('').val(activityId);
    cj('#contact_tag_ids').val('');
    cj('#activity_tag_ids').val('');
    cj('.token-input-dropdown-facebook').html('').remove();
    cj('.token-input-list-facebook').html('').remove();

    cj.ajax({
      url: '/civicrm/imap/ajax/getActivityDetails',
      data: {id: activityId, contact: contactId },
      success: function(data,status) {

        cj("#loading-popup").dialog('close');
        messages = cj.parseJSON(data);

        if(messages.code == 'ERROR'){
          if(messages.clear =='true') removeRow(activityId);
          alert('Unable to load Message : '+ messages.message);
          return false;
        }else{

          // autocomplete
          cj('#contact_tag_name')
            .tokenInput( '/civicrm/imap/ajax/searchTags', {
            theme: 'facebook',
            zindex: 9999,
            onAdd: function ( item ) {
              current_contact_tags = cj('#contact_tag_ids').val();
              current_contact_tags = current_contact_tags.replace(/,,/g, ",");
              cj('#contact_tag_ids').val(current_contact_tags+','+item.id);
            },
            onDelete: function ( item ) {
              current_contact_tags = cj('#contact_tag_ids').val();
              result = string_replace(current_contact_tags, ','+item.id,',');
              result = result.replace(/,,/g, ",");
              cj('#contact_tag_ids').val(result);
            }
          });
          cj('#activity_tag_name')
            .tokenInput( '/civicrm/imap/ajax/searchTags', {
            theme: 'facebook',
            zindex: 9999,
            onAdd: function ( item ) {
              current_activity_tags = cj('#activity_tag_ids').val();
              current_activity_tags = current_activity_tags.replace(/,,/g, ",");
              cj('#activity_tag_ids').val(current_activity_tags+','+item.id);
            },
            onDelete: function ( item ) {
              current_activity_tags = cj('#activity_tag_ids').val();
              result = string_replace(current_activity_tags, ','+item.id,',');
              result = result.replace(/,,/g, ",");
              cj('#activity_tag_ids').val(result);
            }
          });
          cj('#message_left_header_tag').html('').append("<span class='popup_def'>From: </span>"+messages.sender_name +"  <span class='emailbubble'>"+ messages.sender_email+"</span><br/><span class='popup_def'>Subject: </span>"+shortenString(messages.subject,55)+"<br/><span class='popup_def'>Date Forwarded: </span>"+messages.date_long+"<br/>");
          cj('#message_left_header_tag').append("<input class='hidden' type='hidden' id='activityId' value='"+activityId+"'><input class='hidden' type='hidden' id='contactId' value='"+contactId+"'>");

          if ((messages.forwarder != messages.sender_email)){
            cj('#message_left_header').append("<span class='popup_def'>Forwarded by: </span><span class='emailbubble'>"+ messages.forwarder+"</span> @"+ messages.updated_long+ "<br/>");
          }else{
            cj('#message_left_header').append("<span class='popup_def'>&nbsp;</span>No forwarded content found<br/>");
          }
          cj('#message_left_email_tag').html(messages.body+"<hr/>");
          cj.each(messages.attachments, function(key, value) {
             if((!value.rejection) || (value.rejection == '')){
              cj('#message_left_email').append(value.fileName+" ("+((value.size / 1024) / 1024).toFixed(2)+" MB)<br/>");
            }else{
              cj('#message_left_email').append("<span class='rejected'>"+value.fileName+" was rejected ("+value.rejection+")</span><br/>");
            }
          });

          cj("#tagging-popup").dialog({ title:  "Tagging: "+ shortenString(messages.subject,50) });
          cj( "#tagging-popup" ).dialog({
            buttons: {
              "Tag": function() {
                pushtag();
              },
              "Tag and Clear": function() {
                pushtag('clear');
              },
              Cancel: function() {
                cj("#tagging-popup").dialog('close');
                cj('.token-input-list-facebook').html('').remove();
                cj('.token-input-dropdown-facebook').html('').remove();

              }
            }
          });
          cj("#tagging-popup").dialog('open');
          cj("#tabs_tag").tabs();
          cj('#tabs_tag').tabs({ selected: 0 });
        }
      },
      error: function(){
        alert('Unable to load Message');
        cj('.token-input-dropdown-facebook').html('').remove();
        cj('.token-input-list-facebook').html('').remove();

      }
    });
    return false;
  });

// MATCHED Multiple Functions

  // modal for tagging multiple contacts, different header info is shown
  // opens the add_tag popup
  cj(".multi_tag").live('click', function() {
    cj("#loading-popup").dialog('open');
    var contactIds = new Array();
    var activityIds = new Array();

    cj('#imapper-messages-list input:checked').each(function() {
      activityIds.push(cj(this).attr('name'));
      contactIds.push(cj(this).attr('data-id'));
    });

    if(!activityIds.length){
      cj("#loading-popup").dialog('close');
      alert('Use the checkbox to select one or more messages to tag');
      return false;
    }
    // render the multi message view
    cj('#contact_ids').val('').val(contactIds);
    cj('#activity_ids').val('').val(activityIds);
    cj('#contact_tag_ids').val('');
    cj('#activity_tag_ids').val('');
    cj('.token-input-dropdown-facebook').html('').remove();
    cj('.token-input-list-facebook').html('').remove();

    cj('#message_left_header_tag').html('');
    cj('#message_left_tag').html('').addClass('tag_over_ride');

    // autocomplete
    cj('#contact_tag_name')
      .tokenInput( '/civicrm/imap/ajax/searchTags', {
      theme: 'facebook',
      zindex: 9999,
      onAdd: function ( item ) {
        current_contact_tags = cj('#contact_tag_ids').val();
        current_contact_tags = current_contact_tags.replace(/,,/g, ",");
        cj('#contact_tag_ids').val(current_contact_tags+','+item.id);
      },
      onDelete: function ( item ) {
        current_contact_tags = cj('#contact_tag_ids').val();
        result = string_replace(current_contact_tags, ','+item.id,',');
        result = result.replace(/,,/g, ",");
        cj('#contact_tag_ids').val(result);
      }
    });
    cj('#activity_tag_name')
      .tokenInput( '/civicrm/imap/ajax/searchTags', {
      theme: 'facebook',
      zindex: 9999,
      onAdd: function ( item ) {
        current_activity_tags = cj('#activity_tag_ids').val();
        current_activity_tags = current_activity_tags.replace(/,,/g, ",");
        cj('#activity_tag_ids').val(current_activity_tags+','+item.id);
      },
      onDelete: function ( item ) {
        current_activity_tags = cj('#activity_tag_ids').val();
        result = string_replace(current_activity_tags, ','+item.id,',');
        result = result.replace(/,,/g, ",");
        cj('#activity_tag_ids').val(result);
      }
    });

    cj.each(activityIds, function(key, activityId) {
      // console.log('activity :'+activityId+" - key : "+key+" - Contact : "+contactIds[key]);
      cj.ajax({
        url: '/civicrm/imap/ajax/getActivityDetails',
        data: {id: activityId, contact: contactIds[key] },
        success: function(data,status) {

          cj("#loading-popup").dialog('close');
          message = cj.parseJSON(data);

          if(message.code == 'ERROR'){
            if(message.clear =='true') removeRow(activityId);
            alert('Unable to load Message : '+ message.message);
            return false;
          }else{

            cj('#message_left_tag').append("<div id='header_"+activityId+"' data-id='"+activityId+"' class='message_left_header_tags'><span class='popup_def'>From: </span>"+message.sender_name +"  <span class='emailbubble'>"+ message.sender_email+"</span><br/><span class='popup_def'>Subject: </span>"+shortenString(message.subject,55)+"<br/><span class='popup_def'>Date Forwarded: </span>"+message.date_long+"<br/></div><div id='email_"+activityId+"' class='hidden_email' data-id='"+activityId+"'></div>");

            if ((message.forwarder != message.sender_email)){
              cj('#message_left_header').append("<span class='popup_def'>Forwarded by: </span><span class='emailbubble'>"+ message.forwarder+"</span> @"+ message.updated_long+ "<br/>");
            }else{
              cj('#message_left_header').append("<span class='popup_def'>&nbsp;</span>No forwarded content found<br/>");
            }

            cj('#email_'+activityId).html("<span class='info hidden_email_info' data-id='"+activityId+"'>Show Email</span><br/><span class='email'>"+message.body+"</span>");

          }
        },
        error: function(){
          alert('Unable to load Message');
        }
      });
    });
    cj( "#tagging-popup" ).dialog({
      buttons: {
        "Tag": function() {
          pushtag();
        },
        "Tag and Clear": function() {
          pushtag('clear');
        },
        Cancel: function() {
          cj("#tagging-popup").dialog('close');
          cj('.token-input-list-facebook').html('').remove();
          cj('.token-input-dropdown-facebook').html('').remove();
        }
      }
    });

    cj("#tabs_tag").tabs();
    cj('#tabs_tag').tabs({ selected: 0 });
    cj("#tagging-popup").dialog({ title: "Tagging "+contactIds.length+" Matched messages"});
    cj("#tagging-popup").dialog('open');
    cj("#loading-popup").dialog('close');
    return false;
  });

  // remove multiple activities
  cj(".multi_clear").live('click', function() {
    cj("#loading-popup").dialog('open');
    var delete_ids = new Array();

    cj('#imapper-messages-list input:checked').each(function() {
      delete_ids.push(cj(this).attr('name'));
    });
    if(!delete_ids.length){
      cj("#loading-popup").dialog('close');
      alert('Use the checkbox to select one or more messages to clear');
      return false;
    }
    cj( "#clear-confirm" ).dialog({
      buttons: {
        "Clear": function() {
          cj("#reloading-popup").dialog('open');
          cj.each(delete_ids, function(key, value) {
            ClearActivity(value);
          });
          cj("#reloading-popup").dialog('close');

        },
        Cancel: function() {
          cj( this ).dialog( "close" );
        }
      }
    });
    cj("#clear-confirm").dialog({ title:  "Remove "+delete_ids.length+" Messages From List?"});
    cj("#loading-popup").dialog('close');
    cj( "#clear-confirm" ).dialog('open');
    return false;
  });
  // paginated contact search
  cj(".seeMore").live('click', function() {
    var position = cj(this).attr('id');
    var update = parseInt(position,10)+200;
    buildContactList(update);
    cj(this).remove();
  });

});

function firstName(nameVal){
  if(nameVal){
    var nameLength = nameVal.length;
    var nameSplit = nameVal.split(" ");
    return nameSplit[0];
  }else{
    return 'N/A';
  }
}

function lastName(nameVal){
  if(nameVal){
    var nameLength = nameVal.length;
    var nameSplit = nameVal.split(" ");
    var lastLength = nameLength - nameSplit[0].length;
    var lastNameLength = nameSplit[0].length + 1;
    var lastName = nameVal.slice(lastNameLength);
    return lastName;
  }else{
    return 'N/A';
  }

}


function getUnmatchedMessages() {
  cj.ajax({
    url: '/civicrm/imap/ajax/listUnmatchedMessages',
    success: function(data,status) {
      messages = cj.parseJSON(data);
      buildMessageList();
    },
    error: function(){
      alert('unable to Load Messages');
    }
  });
}

function getMatchedMessages() {
  cj.ajax({
    url: '/civicrm/imap/ajax/listMatchedMessages',
    success: function(data,status) {
      messages = cj.parseJSON(data);
      buildActivitiesList();
    },
    error: function(){
      alert('unable to Load Messages');
    }
  });
}
function getReports() {
  cj.ajax({
    url: '/civicrm/imap/ajax/reports',
    success: function(data,status) {
      reports = cj.parseJSON(data);
      buildReports();
    },
    error: function(){
      alert('unable to Load Messages');
    }
  });
}
// needed to format timestamps to allow sorting:
// make a hidden data attribute with the non-readable date (date(U)) and sort on that
cj.extend( cj.fn.dataTableExt.oSort, {
  "title-string-pre": function ( a ) {
    return a.match(/id="(.*?)"/)[1].toLowerCase();
  },
  "title-string-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
  },
  "title-string-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
  }
});

function makeListSortable(){
  cj("#sortable_results").dataTable({
    "aaSorting": [[ 3, "desc" ]],
    "aoColumnDefs": [ { "sType": "title-string", "aTargets": [ 3 ] }],
    'aTargets': [ 1 ],
    "iDisplayLength": 50,
    "aLengthMenu": [[10, 50, 100, -1], [10, 50, 100, 'All']],
    "sPaginationType": "bootstrap",
    "bAutoWidth": false,
    "bInfo": false,
  });
  checks();
}
// http://moorberry.net/blog/datatables-twitter-bootstrap-pagination/
  cj.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
  {
      return {
          "iStart":         oSettings._iDisplayStart,
          "iEnd":           oSettings.fnDisplayEnd(),
          "iLength":        oSettings._iDisplayLength,
          "iTotal":         oSettings.fnRecordsTotal(),
          "iFilteredTotal": oSettings.fnRecordsDisplay(),
          "iPage":          oSettings._iDisplayLength === -1 ?
              0 : Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
          "iTotalPages":    oSettings._iDisplayLength === -1 ?
              0 : Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
      };
  }

  /* Bootstrap style pagination control */
  cj.extend( cj.fn.dataTableExt.oPagination, {
      "bootstrap": {
          "fnInit": function( oSettings, nPaging, fnDraw ) {
              var oLang = oSettings.oLanguage.oPaginate;
              var fnClickHandler = function ( e ) {
                  e.preventDefault();
                  if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
                      fnDraw( oSettings );
                  }
              };

              cj(nPaging).addClass('pagination').append(
                  '<ul>'+
                      '<li class="first disabled"><a href="#">&larr; &larr; First</a></li>'+
                      '<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
                      '<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
                      '<li class="last disabled"><a href="#"> Last &rarr; &rarr; </a></li>'+

                  '</ul>'
              );
              var els = cj('a', nPaging);
              cj(els[0]).bind( 'click.DT', { action: "first" }, fnClickHandler );
              cj(els[1]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
              cj(els[2]).bind( 'click.DT', { action: "next" }, fnClickHandler );
              cj(els[3]).bind( 'click.DT', { action: "last" }, fnClickHandler );

          },

          "fnUpdate": function ( oSettings, fnDraw ) {
              var iListLength = 5;
              var oPaging = oSettings.oInstance.fnPagingInfo();
              var an = oSettings.aanFeatures.p;
              var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

              if ( oPaging.iTotalPages < iListLength) {
                  iStart = 1;
                  iEnd = oPaging.iTotalPages;
              }
              else if ( oPaging.iPage <= iHalf ) {
                  iStart = 1;
                  iEnd = iListLength;
              } else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
                  iStart = oPaging.iTotalPages - iListLength + 1;
                  iEnd = oPaging.iTotalPages;
              } else {
                  iStart = oPaging.iPage - iHalf + 1;
                  iEnd = iStart + iListLength - 1;
              }

              for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
                  // Remove the middle elements
                  cj('li:gt(1)', an[i]).filter(':not(.next):not(.last)').remove();

                  // Add the new list items and their event handlers
                  for ( j=iStart ; j<=iEnd ; j++ ) {
                      sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
                      cj('<li '+sClass+'><a href="#">'+j+'</a></li>')
                          .insertBefore( $('li.next', an[i])[0] )
                          .bind('click', function (e) {
                              e.preventDefault();
                              oSettings._iDisplayStart = (parseInt(cj('a', this).text(),10)-1) * oPaging.iLength;
                              fnDraw( oSettings );
                          } );
                  }

                  // Pages
                  //console.log("Page "+(oPaging.iPage+1) +" Of "+oPaging.iTotalPages);
                  //console.log("Numbers "+(oPaging.iLength*(oPaging.iPage))+" THRU "+(oPaging.iLength*(oPaging.iPage+1)));
                  totals = cj("#total_number").html();

                  if((oPaging.iLength*(oPaging.iPage+1)) < 1 ){
                    cj("#total_results").html('All Results 1 - <span id="total_number">'+totals+'</span>');
                  }else if ((oPaging.iLength*(oPaging.iPage+1)) < cj("#total_number").html()){
                    viewing = "Results "+(oPaging.iLength*(oPaging.iPage)+1)+" - "+(oPaging.iLength*(oPaging.iPage+1));
                    cj("#total_results").html(viewing+' of <span id="total_number">'+totals+'</span>');
                  }else{
                     cj("#total_results").html("Results "+(oPaging.iLength*(oPaging.iPage)+1)+' - <span id="total_number">'+totals+'</span>');
                  }


                  // Add / remove disabled classes from the static elements
                  if ( oPaging.iPage === 0 ) {
                      cj('.first', an[i]).addClass('disabled');
                      cj('.prev', an[i]).addClass('disabled');

                  } else {
                      cj('.first', an[i]).removeClass('disabled');
                      cj('.prev', an[i]).removeClass('disabled');

                  }

                  if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
                      cj('.last', an[i]).addClass('disabled');
                      cj('.next', an[i]).addClass('disabled');

                  } else {
                      cj('.last', an[i]).removeClass('disabled');
                      cj('.next', an[i]).removeClass('disabled');

                  }
              }
          }
      }
  } );


// a complicated checkbox method,
function checks(){
  cj('.checkbox_switch').click(function() {
    cj('th.checkbox').click();
  });
  cj('th.checkbox').click(function() {
    if(cj('.checkbox_switch').is(':checked')){
      cj('input.checkbox_switch').removeAttr('checked');
      cj('#imapper-messages-list input:checkbox').removeAttr('checked');
    }else{
      cj('#imapper-messages-list input:checkbox').attr('checked', true);
      cj('.checkbox_switch').attr("checked", true);
    }
  });
}

function buildMessageList() {
  if(messages.stats.overview.Unprocessed == '0' || messages == null){
    cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty">No records found</td>');
    cj("#total_number").html('0');
  }else{
    var messagesHtml = '';
    var total_results = messages.stats.overview.Unprocessed;
    cj.each(messages.Unprocessed, function(key, value) {
      var icon ='';

        // wrap the row
	messagesHtml += '<tr id="'+value.id+'" data-key="'+value.key+'" class="imapper-message-box"> <td class="imap_checkbox_column" ><input class="checkbox" type="checkbox" name="'+value.id+'"  data-id="'+value.id+'"/></td>';

        // build a match count bubble
        countWarn = (value.matches_count == 1) ? 'warn' :  '';
        countMessage = (value.matches_count == 1) ? 'This address should have matched automatically' : 'This email address matches '+value.matches_count+' records in bluebird';
        countStatus = (value.matches_count == 0) ? 'empty' :  'multi';
        countIcon = '<span class="matchbubble marginL5 '+countWarn+' '+countStatus+'" title="'+countMessage+'">'+value.matches_count+'</span></td>';


        // build the name box
        if( value.sender_name != ''  && value.sender_name != null){
	  messagesHtml += '<td class="imap_name_column unmatched" data-firstName="'+firstName(value.sender_name)+'" data-lastName="'+lastName(value.sender_name)+'">'+shortenString(value.sender_name,20);

          if( value.sender_email != '' && value.sender_email != null){
            messagesHtml += '<span class="emailbubble marginL5">'+shortenString(value.sender_email,15)+'</span>';
            messagesHtml +=  countIcon;
          }else{
            messagesHtml += '<span class="emailbubble warn marginL5" title="We could not find the email address of this record">No email found!</span>';
          }
          messagesHtml +='</td>';

        }else if( value.sender_email != '' && value.sender_email != null ){
	  messagesHtml += '<td class="imap_name_column unmatched"><span class="emailbubble">'+shortenString(value.sender_email,25)+'</span>';
          messagesHtml +=  countIcon;
        }else {
	  messagesHtml += '<td class="imap_name_column unmatched"><span class="matchbubble warn" title="There was no info found in regard to the source of this message">No source info found</span></td>';
          messagesHtml +=  countIcon;
        }

        // dealing with attachments
        if(value.attachments){
          cj.each(value.attachments, function(key, attachment) {
            icon = '<div class="icon attachment-icon attachment" title="'+value.attachments.length+' Attachments" ></div>'
          });
        }
	messagesHtml += '<td class="imap_subject_column unmatched">'+shortenString(value.subject,40) +' '+icon+'</td>';
	messagesHtml += '<td class="imap_date_column unmatched"><span id="'+value.date_u+'" title="'+value.date_long+'">'+value.date_short +'</span></td>';

        // hidden column to sort by
        if(value.match_count != 1){
          var match_short = (value.match_count == 0) ? "NoMatch" : "MultiMatch" ;
	  messagesHtml += '<td class="imap_match_column hidden"><span data="'+match_short+'">'+match_short +'</span></td>';
        }else{
	  messagesHtml += '<td class="imap_match_column hidden"><span data="Error">ProcessError</span></td>';
        }

        // check for direct messages & not empty forwarded messages
        if((value.status == 'direct' ) && (value.forwarder != '')){
	  messagesHtml += '<td class="imap_forwarder_column">Direct '+shortenString(value.from_email,10)+'</td>';
        }else if(value.forwarder != ''){
	  messagesHtml += '<td class="imap_forwarder_column">'+shortenString(value.forwarder,14)+'</td>';
        }else{
	  messagesHtml += '<td class="imap_forwarder_column"> N/A </td>';
        }

	messagesHtml += '<td class="imap_actions_column "><span class="find_match"><a href="#">Find match</a></span><span class="delete"><a href="#">Delete</a></span></td> </tr>';

    });
    cj('#imapper-messages-list').html(messagesHtml);
    cj("#total_number").html(total_results);
    makeListSortable();
    cj('.checkbox').removeClass('sorting');
    cj('.Actions').removeClass('sorting');

  }
}

function buildReports() {
  cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty">Not Quite Ready yet</td>');
	// console.log(reports.Unprocessed.length);
	// console.log(reports.Matched.length);
	// console.log(reports.Cleared.length);
	// console.log(reports.Errors.length);
	// console.log(reports.Deleted.length);
}
function DeleteMessage(id,imapid){
  cj.ajax({
    url: '/civicrm/imap/ajax/deleteMessage',
    async:false,
    data: {id: id },
    success: function(data,status) {
      deleted = cj.parseJSON(data);
      if(deleted.code == 'ERROR' || deleted.code == '' || deleted.code == null){
        if(deleted.clear =='true')  removeRow(id);
        alert('Unable to Delete Message : '+deleted.message);
      }else{
        removeRow(id); ;
        helpMessage('Message Deleted');
      }
    },
    error: function(){
      alert('Unable to delete Message');
      }
  });
}

// Clear activities
// args : value = activity ID
// Result : A few things
function ClearActivity(value){
  cj.ajax({
    url: '/civicrm/imap/ajax/untagActivity',
    data: {id: value},
    async:false,
    success: function(data,status) {
      data = cj.parseJSON(data);
      if (data.code =='ERROR'){
        alert('Unable to Clear Activity : '+data.message);
        if(deleted.clear =='true')  removeRow(value);
      }else{
        helpMessage('Activity Cleared');
      }
      removeRow(value);
      cj("#clear-confirm").dialog('close');
    },
    error: function(){
      alert('Unable to Clear Activity');
    }
  });
}

// Delete activities
// args : value = activity ID
// Result : A few things
function DeleteActivity(value){
    // setTimeout(this.resolve, (1500));
  // console.log(value);
  cj.ajax({
    url: '/civicrm/imap/ajax/deleteActivity',
    data: {id: value},
    success: function(data,status) {
      deleted = cj.parseJSON(data);
      if(deleted.code == 'ERROR' || deleted.code == '' || deleted.code == null){
        if(deleted.clear =='true')  removeRow(value);
        alert('Unable to Delete Activity : '+deleted.message);
      }else{
        removeRow(value);
        helpMessage('Activity Deleted');
      }
    },
    error: function(){
      alert('Unable to Delete Activity ');
    }
  });
}

// adding (single / multiple) tags to (single / multiple) contacts,
// function works for multi contact tagging and single
// cj(".push_tag").live('click', function(){
function pushtag(clear){

  var contact_ids = cj("#contact_ids").attr('value');
  var activity_ids = cj("#activity_ids").attr('value');

  var contact_tag_ids ='';
  var activity_tag_ids ='';

  var contact_input = cj("#contact_tag_ids").val().replace(/,,/g, ",").replace(/^,/g, "");
  if(contact_input.length){
    contact_tag_ids = contact_input;
  }

  var activity_input = cj("#activity_tag_ids").val().replace(/,,/g, ",").replace(/^,/g, "");
  if(activity_input.length){
    activity_tag_ids = activity_input;
  }

  if (activity_tag_ids =='' && contact_tag_ids == ''){
    alert("please select a tag");
    return false;
  }else{
    cj("#tagging-popup").dialog('close');
    cj('.token-input-list-facebook .token-input-token-facebook').remove();
    cj('.token-input-dropdown-facebook').html('');
    cj('.token-input-dropdown-facebook').html('').remove();

  }

  if(contact_tag_ids){
    var contact_ids_array = contact_ids.split(',');
    cj.each(contact_ids_array, function(key, id) {
      helpMessage('Contact Tagged');
    });

    cj.ajax({
      url: '/civicrm/imap/ajax/addTags',
      async:false,
      data: { contactId: contact_ids, tags: contact_tag_ids},
      success: function(data,status) {
      },error: function(){
        alert('failure');
      }
    });
  }
  if(activity_tag_ids){
    var activity_ids_array = activity_ids.split(',');
    cj.each(activity_ids_array, function(key, id) {
      helpMessage('Message Tagged');
    });

    cj.ajax({
      url: '/civicrm/imap/ajax/addTags',
      async:false,
      data: { activityId: activity_ids, tags: activity_tag_ids},
      success: function(data,status) {
      },error: function(){
        alert('failure');
      }
    });
  }

  if(clear){
    cj("#clear-confirm").dialog('open');
    cj("#clear-confirm").dialog({
      buttons: {
        "Clear": function() {
          cj("#clear-confirm").dialog('close');
          activity_ids_array = activity_ids.split(',');
          cj.each(activity_ids_array, function(key, id) {
            ClearActivity(id);
          });
        },
        Cancel: function() {
          cj("#clear-confirm").dialog('close');
        }
      }
    });
  }

};

// matched messages screen
function buildActivitiesList() {
  if(messages.stats.overview.successes == '0' || messages == null){
    cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty">No records found</td>');
    cj("#total_number").html('0');
  }else{
    var messagesHtml = '';
    var total_results = messages.stats.overview.successes;
    // console.log(messages);
    cj.each(messages.successes, function(key, value) {
      if(value.date_short != null){
	messagesHtml += '<tr id="'+value.id+'" data-id="'+value.activity_id+'" data-contact_id="'+value.matched_to+'" class="imapper-message-box"> <td class="imap_checkbox_column matched" ><input class="checkbox" type="checkbox" name="'+value.id+'" data-id="'+value.matched_to+'"/></td>';

        if( value.contactType != ''){
	  messagesHtml += '<td class="imap_name_column matched" data-firstName="'+value.firstName +'" data-lastName="'+value.lastName +'">';
          messagesHtml += '<a class="crm-summary-link" href="/civicrm/profile/view?reset=1&gid=13&id='+value.matched_to+'&snippet=4">';
          messagesHtml += '<div class="icon crm-icon '+value.contactType+'-icon"></div>';
          messagesHtml += '</a>';
          messagesHtml += '<a href="/civicrm/contact/view?reset=1&cid='+value.matched_to+'" title="'+value.fromName+'">'+shortenString(value.fromName,19)+'</a>';
          messagesHtml += ' ';
        }else {
	  messagesHtml += '<td class="imap_name_column matched">';
        }

        // messagesHtml += '<span class="emailbubble marginL5">'+shortenString(value.sender_email,13)+'</span>';

        match_sort = 'ProcessError';
        if(value.matcher){
          var match_string = (value.matcher != 0) ? "Manually matched by "+value.matcher_name : "Automatically Matched" ;
          var match_short = (value.matcher != 0) ? "M" : "A" ;
          match_sort = (value.matcher != 0) ? "ManuallyMatched" : "AutomaticallyMatched" ;
          messagesHtml += '<span class="matchbubble marginL5 '+match_short+'" title="This email was '+match_string+'">'+match_short+'</span>';
        }
        messagesHtml +='</td>';
	messagesHtml += '<td class="imap_subject_column matched">'+shortenString(value.subject,40);
        if(value.attachments.length > 0){
          messagesHtml += '<div class="icon attachment-icon attachment" title="'+value.attachments.length+' Attachments" ></div>';
        }
        messagesHtml +='</td>';
	messagesHtml += '<td class="imap_date_column matched"><span id="'+value.date_u+'"  title="'+value.date_long+'">'+value.date_short +'</span></td>';
	messagesHtml += '<td class="imap_match_column matched  hidden">'+match_sort +'</td>';

	messagesHtml += '<td class="imap_forwarder_column matched">'+shortenString(value.forwarder,14)+'</td>';
        // messagesHtml += '<td class="actions"><span class="edit_match"><a href="#">Edit</a></span><span class="add_tag"><a href="#">Tag</a></span><span class="clear_activity"><a href="#">Clear</a></span><span class="delete"><a href="#">Delete</a></span></td> </tr>';
	messagesHtml += '<td class="imap_actions_column matched "><span class="edit_match"><a href="#">Edit</a></span><span class="disabled"><a href="#">Tag</a></span><span class="delete"><a href="#">Delete</a></span></td> </tr>';

      }
    });
    cj('#imapper-messages-list').html(messagesHtml);
    cj("#total_number").html(total_results);
    makeListSortable();
  }
}

function buildContactList(loop) {
  var contactsHtml = '';
  html = "<br/><br/><i>Contact Search results:</i><br/><strong>Number of matches: </strong>"+contacts.length+' ';
  if(contacts.length < 1){
    html += "(No Matches)";
  }
  cj('.search_info').html(html);

  for (var i = loop; i < contacts.length && i < loop+200; i++) {
    // calculate the aprox age
    if(contacts[i].birth_date){
      var date = new Date();
      var year  = date.getFullYear();
      var birth_year = contacts[i].birth_date.substring(0,4);
      var age = year - birth_year;
    }
    contactsHtml += '<div class="imapper-contact-box" data-id="'+contacts[i].id+'">';
    contactsHtml += '<div class="imapper-address-select-box">';
    contactsHtml += '<input type="checkbox" class="imapper-contact-select-button" name="contact_id" value="'+contacts[i].id+'" />';
    contactsHtml += '</div>';
    contactsHtml += '<div class="imapper-address-box">';
    if(contacts[i].display_name){ contactsHtml += contacts[i].display_name + '<br/>'; };
    if(contacts[i].birth_date){ contactsHtml += '<strong>'+age+'</strong> - '+contacts[i].birth_date + '<br/>';}
    if(contacts[i].email){ contactsHtml += contacts[i].email + '<br/>'; }
    if(contacts[i].phone){ contactsHtml += contacts[i].phone + '<br/>'; }
    if(contacts[i].street_address){ contactsHtml += contacts[i].street_address + '<br/>'; }
    if(contacts[i].city){ contactsHtml += contacts[i].city + ', ' + contacts[i].name +" "+ contacts[i].postal_code + '<br/>'; }
    contactsHtml += '</div></div>';
    contactsHtml += '<div class="clear"></div>';
  }
  if (contacts.length > loop+200){
    contactsHtml += '<span class="seeMore" id="'+loop+'">see more</span>';
  };
  cj('#imapper-contacts-list').append(contactsHtml);

}


// displays a help window + current date time
// if same message and hasn't disappared yet, update
function helpMessage(message){
  // parse date
  var d = new Date();
  var h = d.getHours();
  var m = d.getMinutes();
  if(m < 10){ m = '0'+m;}
  var s = d.getSeconds();
  if(s < 10){ s = '0'+s;}

  // keep track of unique messages with a class based on the message
  // replace to eliminate things that would break a class
  var messageclass = message.replace(/[^a-z0-9]/gi,'');

  // check to see if it exists
  var updateCheck = cj("#top").find("."+messageclass).html();
  if(updateCheck){
    // update old count
    var oldCount = cj("#top ."+messageclass).find(".count").html();
    count = parseInt(oldCount,10)+1;
    cj("#top ."+messageclass).html("<p><span class='count'>"+count+"</span> <span class='message'>"+message+"</span> <small>"+h+":"+m+":"+s+"</small></p>");
  }else{
    cj("#top").append("<div class='"+h+"_"+m+" "+messageclass+"' id='help' ><p><span class='count'>1</span> <span class='message "+messageclass+"'>"+message+"</span> <small>"+h+":"+m+":"+s+"</small></p></div>");
  }
  // fade out and remove after 60 seconds
  setTimeout(function(){
    cj("."+messageclass).fadeOut(1000, function(){
      cj(this).remove();
    });
  }, 60000);
}

// Create shortended String with title tag for hover
// If subject is null return N/A
function shortenString(subject, length){
  if(subject){
    if (subject.length > length ){
    var safe_subject = '<span title="'+subject+'">'+subject.substring(0,length)+"...</span>";
    return safe_subject;
    }else{
      return '<span>'+subject+'</span>';
    }
  }else{
    return "N/A";
  }
 }

// Look for empty rows that match the KEY of a matched row
// Remove them from the view so the user doesn't re-add / create duplicates
// key = md5 ( shortened to 8 ) of user_email
function checkForMatch(key,contactIds){
  cj("#matchCheck-popup").dialog('open');
  // console.log('checking',key,contactIds);
  cj(".this_address").html(key);
  cj('.imapper-message-box').each(function(i, item) {
    var check = cj(this).data('key');
    var messageId = cj(this).attr('id');
    if (key == check) {
      if (cj('.matchbubble.empty',this).length){
        cj.ajax({
          url: '/civicrm/imap/ajax/assignMessage',
          async:false,
          data: {
            messageId: messageId,
            contactId: contactIds
          },
          success: function(data,status) {
            if(data != null || data != ''){
              var assign = cj.parseJSON(data);
              if(assign.code == 'ERROR'){
                // helpMessage('Other Records not Matched');
              }else{
                removeRow(messageId);
                helpMessage('Other Records Automatically Matched');
              }
            }
          }
        });
      }
    };
  });
  cj("#matchCheck-popup").dialog( "close" );

}

// updates the count at the top of the page
function updateTotalCount(){
  var count = cj("#total_number").html();
  count = parseInt(count,10);
  output = count -1;
  cj("#total_number").html(output);
  if(output < 1){
    cj("#total_number").html('0');
    cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty">No records left, Please Reload the page</td>');
  }
}

// removes row from the UI, forces table reload
function removeRow(id){
  if(cj("#"+id).length){
    var oTable = cj('#sortable_results').dataTable();
    var row_index = oTable.fnGetPosition( document.getElementById(id));
    oTable.fnDeleteRow(row_index);
    updateTotalCount();
  }else{
    // alert('could not delete row');
  }
}

function string_replace(haystack, find, sub) {
    return haystack.split(find).join(sub);
}
// unbind the sort on the checkbox and actions
cj("th.checkbox").removeClass('sorting').unbind('click');
cj("th.Actions").removeClass('sorting').unbind('click');
