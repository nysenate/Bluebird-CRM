var messages = [];
var contacts = [];

$(document).ready(function(){
	var first_name = cj('#first_name');
	var last_name = cj('#last_name');
	var city = cj('#city');
	var phone = cj('#phone');
	var state = cj('#state');
	var street_address = cj('#street_address');
	var reset = cj('#reset');
	var filter = cj('#filter');
	var assign = cj('#assign');
	
placeholderSupport = ("placeholder" in document.createElement("input"));

if(!placeholderSupport ){
	console.log('no placeholder Support');
	$('[placeholder]').focus(function() {
		var input = $(this);
		if (input.val() == input.attr('placeholder')) {
			input.val('');
		    input.removeClass('placeholder');
		}
	}).blur(function() {
		var input = $(this);
		if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		}
	}).blur().parents('form').submit(function() {
		$(this).find('[placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
				input.val('');
			}
		})
	});
}else{
	console.log('placeholder Support');
}

	filter.live('click', function() {
		cj('#imapper-contacts-list').html('Searching...');
		cj.ajax({
			url: '/civicrm/imap/ajax/contacts',
			data: {
				state: '1031', //always use nystate for now
				city: city.val(),
				phone: phone.val(),
				street_address: street_address.val(),
				first_name: first_name.val(),
				last_name: last_name.val()
			},
			success: function(data,status) {
				if(data != null || data != ''){
					contacts = cj.parseJSON(data);
					if(contacts.length < 1){
						cj('#imapper-contacts-list').html('No Results Found');
					}else{
						cj('.contacts-list').html('').append("<strong>"+(contacts.length )+' Found</strong>');
						buildContactList();
					}
				}
			}
		});
		return false;
	});

	assign.click(function() {
		var messageId = cj('#email_id').val();
		var imapId = cj('#imap_id').val();
		var contactRadios = cj('input[name=contact_id]');
		var contactIds = '';

		cj.each(contactRadios, function(idx, val) {
			if(cj(val).attr('checked')) {
				if(contactIds != '')
					contactIds = contactIds+',';
				contactIds = contactIds + cj(val).val();
			}
		});

		cj.ajax({
			url: '/civicrm/imap/ajax/assignMessage',
			data: {
				messageId: messageId,
				imapId: imapId,
				contactId: contactIds
			},
			success: function(data, status) {
				cj.each(messages, function (idx, val) {
					if(val.uid == messageId && val.imap_id == imapId) {
                		delete messages[idx];
                		buildMessageList();
                	}
                });
				alert("Assigned email (UID: " + messageId + ") to contact (ID: " + contactIds + ").");
			}
		});
		return false;
	});

	pullMessageHeaders();

	// add a delete conform popup
	cj( "#delete-confirm" ).dialog({
		modal: true,
		width: 350,
		autoOpen: false,
		resizable: false,
		draggable: false	
	});
	
	//
	cj(".delete").live('click', function() {
		var messageId = cj(this).parent().parent().attr('data-id');
		var imapId = cj(this).parent().parent().attr('data-imap_id');

		cj( "#delete-confirm" ).dialog({
			buttons: {
				"Delete": function() {
					cj( this ).dialog( "close" );
					cj.ajax({
						url: '/civicrm/imap/ajax/deleteMessage',
						data: {id: messageId,
					    imapId: imapId },
						success: function(data,status) {
							cj("#"+messageId+'_'+imapId).remove();
							// update count on top
							var old_total = parseInt(cj("#total_number").html(),10);
							cj("#total_number").html(old_total-1);
								makeListSortable();
						} 
					});
				},
				Cancel: function() {
					cj( this ).dialog( "close" );
				}
			}
		});
		cj( "#delete-confirm" ).dialog('open');
	});

	// add a find match popup
	cj( "#find-match-popup" ).dialog({
		modal: true,
		height: 500,
		width: 950,
		autoOpen: false,
		resizable: false,
		title: 'Loading Data',
		draggable: false
	});

	// what happens when we click find match
	cj(".find_match").live('click', function() {
		var messageId = cj(this).parent().parent().attr('data-id');
		var imapId = cj(this).parent().parent().attr('data-imap_id');
		cj('#imapper-contacts-list').html('');
		cj.ajax({
			url: '/civicrm/imap/ajax/message',
			data: {id: messageId,
				   imapId: imapId },
			success: function(data,status) {
			//	console.log(data);
				messages = cj.parseJSON(data);
				switchName(messages.fromName);
				cj('#message_left_header').html('').append("<strong>From: </strong>"+messages.fromName +"  <i>&lt;"+ messages.fromEmail+"&gt;</i><br/><strong>Subject: </strong>"+messages.subject+"<br/><strong>Date: </strong>"+messages.date+"<br/>");
				cj('#message_left_email').html(messages.details);
				cj('#email_id').val(messageId);
				cj('#imap_id').val(imapId);

				cj("#find-match-popup").dialog({
					title:  "Reading: "+messages.subject
				});
				cj( "#find-match-popup" ).dialog('open');
 				cj( "#tabs" ).tabs();
			}
		});
	});

	cj(".imapper-contact-box").live('click', function() {
		var radioButton = cj(this).find(".imapper-contact-select-button");
		radioButton.attr('checked', 'checked');
	});

});

function switchName(nameVal){
    var nameLength = nameVal.length;
    var nameSplit = nameVal.split(" ");
    var lastLength = nameLength - nameSplit[0].length;
    var lastNameLength = nameSplit[0].length + 1;
    var lastName = nameVal.slice(lastNameLength);
    cj('#tabs-1 #first_name,#tabs-2 #first_name').val(nameSplit[0]);
    cj('#tabs-1 #last_name, #tabs-2 #last_name').val(lastName);

//	cj('.imapper-submit').click();
}



function pullMessageHeaders() {
	cj.ajax({
		url: '/civicrm/imap/ajax/unmatchedMessages',
		success: function(data,status) {
			messages = cj.parseJSON(data);
			buildMessageList();
		}
	});
}
function makeListSortable(){
	//cj("#sortable_results").fnDestroy();
	cj("#sortable_results").dataTable({
		"aaSorting": [[ 4, "desc" ]]
	}); 
	console.log('makeListSortable called ');
}

function buildMessageList() {
	if(messages == '' || messages == null)
		return;
	var messagesHtml = '';
	var total_results =0;
	$.each(messages, function(key, value) {
		total_results++;
		messagesHtml += '<tr id="'+value.uid+'_'+value.imap_id+'" data-id="'+value.uid+'" data-imap_id="'+value.imap_id+'" class="imapper-message-box"> <td class="checkboxieout" ><input type="checkbox" name="" value="" /></td>';
		if( value.from_name != ''){
			messagesHtml += '<td class="name">'+value.from_name +'</td>';
		}else {
			messagesHtml += '<td class="name"> N/A </td>';
		}
		messagesHtml += '<td class="email">'+value.from_email +'</td>';
		messagesHtml += '<td class="subject">'+value.subject +'</td>';
		messagesHtml += '<td class="date">'+value.date +'</td>';
		messagesHtml += '<td class="Actions"><span class="find_match"><a href="#">Find match</a></span> | <span class="delete"><a href="#">Delete</a></span></td> </tr>';
	});
	cj('#imapper-messages-list').html(messagesHtml);
	cj("#total_number").html(total_results);
	makeListSortable();
}

function buildContactList() {
	var contactsHtml = '';
	$.each(contacts, function(key, value) {
		contactsHtml += '<div class="imapper-contact-box" data-id="'+value.contact_id+'">';
		contactsHtml += '<div class="imapper-address-select-box">';
		contactsHtml += '<input type="radio" class="imapper-contact-select-button" name="contact_id" value="'+value.contact_id+'" />';
		contactsHtml += '</div>';
		contactsHtml += '<div class="imapper-address-box">';
		contactsHtml += value.display_name + '<br />';
		contactsHtml += value.street_address + '<br />';
		contactsHtml += value.city + ', ' + value.abbreviation + ' ' + value.postal_code;
		contactsHtml += '</div></div>';
		contactsHtml += '<div class="clear"></div>';
	});
	cj('#imapper-contacts-list').append(contactsHtml);
}