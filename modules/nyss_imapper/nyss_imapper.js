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
	
	reset.click(function() {
		city.val("");
		last_name.val("");
		first_name.val("");
		phone.val("");
		street_address.val("");
		cj('.contactList').hide();
		cj('.contactList .contactItem').remove();
		cj('.showContactNumResult span').empty();
		cj('.contactsContainer .linkContact').slideUp('fast');
		cj('.showContactNumResult').slideUp('fast');
		return false;
	});
	
	filter.click(function() {
		cj('.contactList').show();
		loading('.contactList');
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
				contacts = cj.parseJSON(data);
				buildContactList();
			}
		});
		return false;
	});

	assign.click(function() {
		var messageIds = cj('input[name=message_uid]');
		var messageId = -1;
		cj.each(messageIds, function(idx, val) {
			if(cj(val).attr('checked')) {
				messageId = cj(val).val();
			}
		});
		if(messageId == -1) {
			alert("Please select a message.");
			return false;
		}
		var contactIds = cj('input[name=contact_id]');
		var contactId = -1;
		cj.each(contactIds, function(idx, val) {
			if(cj(val).attr('checked')) {
				contactId = cj(val).val();
			}
		});
		if(contactId == -1) {
			alert("Please select a contact.");
			return false;
		}

		cj.ajax({
			url: '/civicrm/imap/ajax/assignMessage',
			data: {
				messageId: messageId,
				contactId: contactId
			},
			success: function(data, status) {
				cj.each(messages,	function (idx, val) { 
                	if(val.uid == messageId) {
                		delete messages[idx];
                		buildMessageList();
                	}
                });
				alert("Assigned email (UID: " + messageId + ") to contact (ID: " + contactId + ").");
			}
		});
		return false;
	});

	pullMessageHeaders();

	cj(".imapper-message-box").live('click', function() {
		var radioButton = cj(this).find(".imapper-select-button");
		radioButton.attr('checked', 'checked');
		var messageId = cj(this).attr('data-id');
		cj.ajax({
			url: '/civicrm/imap/ajax/message',
			data: {id: messageId},
			success: function(data,status) {
				$.prompt(data, []);
			}
		});
	});

	cj(".imapper-contact-box").live('click', function() {
		var radioButton = cj(this).find(".imapper-contact-select-button");
		radioButton.attr('checked', 'checked');
	});

});

function pullMessageHeaders() {
	cj.ajax({
		url: '/civicrm/imap/ajax/messageHeaders',
		success: function(data,status) {
			messages = cj.parseJSON(data);
			buildMessageList(messages);
		}
	});
}

function buildMessageList() {
	var messagesHtml = '';
	$.each(messages, function(key, value) {
		messagesHtml += '<div class="imapper-message-box" data-id="'+value.uid+'">';
		messagesHtml += '<input type="radio" class="imapper-select-button" name="message_uid" value="'+value.uid+'" />';
		messagesHtml += value.from_name + ' &lt;' + value.from_email + '&gt; (' + value.subject + ')';
		messagesHtml += '</div>';
	});
	cj('#imapper-messages-list').html(messagesHtml);
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
	cj('#imapper-contacts-list').html(contactsHtml);
}