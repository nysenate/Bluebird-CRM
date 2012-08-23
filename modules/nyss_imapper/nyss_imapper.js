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
	var email_address = cj('#email_address');

	// Checking to see if we are in a browser that the placeholder tag is not yet supported in. We regressively add it here.
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

    cj('.checkbox_switch').toggle(function(){
        cj('input:checkbox').attr('checked','checked');
        cj('.checkbox_switch').attr('checked','checked');
       // if(cj('.checkbox_switch').is(':checked')){}
    },function(){
        cj('input:checkbox').removeAttr('checked');
    	
    	if(cj('.checkbox_switch').is(':checked')){
    		cj('.checkbox_switch').removeAttr('checked');
    	};
    
    })



	filter.live('click', function() {
		cj('#imapper-contacts-list').html('Searching...');
		cj.ajax({
			url: '/civicrm/imap/ajax/contacts',
			data: {
				state: '1031', //always use nystate for now
				city: city.val(),
				phone: phone.val(),
				email_address: email_address.val(),
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

	// // add a assign worked popup
	// cj( "#assign-confirm").dialog({
	// 	modal: true,
	// 	width: 350,
	// 	autoOpen: false,
	// 	resizable: false,
	// 	draggable: false	
	// });

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

            cj("#find-match-popup").dialog('close');  
			//	alert("Assigned email (UID: " + messageId + ") to contact (ID: " + contactIds + ").");
			}
		});
		return false;
	});


	// 
	
	if(cj("#Activities").length){
		pullActivitiesHeaders();
	}else{
		pullMessageHeaders();
	}

	

	// add a delete conform popup
	cj( "#delete-confirm" ).dialog({
		modal: true,
		width: 350,
		autoOpen: false,
		resizable: false,
		draggable: false	
	});
	
	// delete confirm & processing 
	cj(".delete").live('click', function() {
		var messageId = cj(this).parent().parent().attr('data-id');
		var imapId = cj(this).parent().parent().attr('data-imap_id');
		var contactId = cj(this).parent().parent().attr('data-contact_id');
		var row = cj(this).parent().parent();

		cj( "#delete-confirm" ).dialog({
			buttons: {
				"Delete": function() {
					cj( this ).dialog( "close" );
					row.remove();
					if(cj("#Activities").length){
						

						// cj.ajax({
						// 	url: '/civicrm/imap/ajax/deleteActivity',
						// 	data: {id: messageId},
						// 	success: function(data,status) {
						// 		console.log(data);
								
						// 		//console.log("#"+messageId+'_'+contactId);
						// 		// update count on top
						// 		var old_total = parseInt(cj("#total_number").html(),10);
						// 		cj("#total_number").html(old_total-1);
						// 		//destroyReSortable();
						// 	} 
						//});
					}else{
						cj.ajax({
							url: '/civicrm/imap/ajax/deleteMessage',
							data: {id: messageId,
						    imapId: imapId },
							success: function(data,status) {
								cj("#"+messageId+'_'+imapId).remove();
								// update count on top
								var old_total = parseInt(cj("#total_number").html(),10);
								cj("#total_number").html(old_total-1);
								//destroyReSortable();
							} 
						});
					}
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

	// add a loading icon popup
	cj( "#loading-popup" ).dialog({
		modal: true,
		width: 200,
		autoOpen: false,
		resizable: false,
		title: 'Please Wait',
		draggable: false
	});

		// add a loading icon popup
	cj( "#tagging-popup" ).dialog({
		modal: true,
		height: 400,
		width: 500,
		autoOpen: false,
		resizable: false,
		title: 'Loading Data',
		draggable: false
	});


//
	cj(".add_tag").live('click', function() { 
		cj("#loading-popup").dialog('open');

			var activityId = cj(this).parent().parent().attr('data-id');
		var contactId = cj(this).parent().parent().attr('data-contact_id');
		cj('#tagging-popup').html('');
	//	console.log(activityId+" / "+contactId)

		cj.ajax({
			url: '/civicrm/imap/ajax/activityDetails',
			data: {id: activityId, contact: contactId },
			success: function(data,status) {
				console.log(data);
		 		cj("#loading-popup").dialog('close');
		 		messages = cj.parseJSON(data);
		 		cj('#tagging-popup').html('').append("<strong>From: </strong>"+messages.fromName +"  <i>&lt;"+ messages.fromEmail+"&gt;</i><br/><strong>Subject: </strong>"+messages.subject+"<br/><strong>Date: </strong>"+messages.date+"<br/>");
				if ((messages.forwardedEmail != '')){
					cj('#tagging-popup').append("<strong>Forwarded by: </strong>"+messages.forwardedName+" <i>&lt;"+ messages.forwardedEmail+"&gt;</i><br/>");
				}
				if ((messages.fromAddress)){
					cj('#tagging-popup').append("<strong>Address by: </strong>"+messages.fromAddress);
				}
 				cj('#tagging-popup').append("<hr/><input type='text'/><br/>");
 				cj('#tagging-popup').append('<hr/><strong>Add to: </strong> <br/> <input type="checkbox" name="group1" value="Contact">Contact<br/><input type="checkbox" name="group1" value="Activity"> Activity<br>');
 				cj('#tagging-popup').append('<input type="button" class="tagger-submit" id="add-tag" value="Add Tag" name="add-tag">');

				cj("#tagging-popup").dialog({ title:  "Reading: "+messages.subject });
				cj("#tagging-popup").dialog('open');
 				cj("#tabs").tabs();
 	// 			cj('#tabs-1 #email-address').val(messages.fromEmail);
 	// 			cj('#filter').click();
		// 		switchName(messages.fromName);
			}
		 });



	});


	cj(".multi_tag").live('click', function() { 
		cj("#loading-popup").dialog('open');
		var selected = new Array();
		$('input:checked').each(function() {
			selected.push($(this).attr('name'));
		});
		console.log(selected.length);
 		cj("#loading-popup").dialog('close');
 		cj('#tagging-popup').html('');
 		cj("#tagging-popup").dialog({ title: "Tagging "+selected.length+" Matched messages"});
 		cj('#tagging-popup').append("<hr/><input type='text'/><br/>");
 		cj('#tagging-popup').append('<hr/><strong>Add to: </strong> <br/> <input type="checkbox" name="group1" value="Contact">Contact<br/><input type="checkbox" name="group1" value="Activity"> Activity<br>');
 				cj('#tagging-popup').append('<input type="button" class="tagger-submit" id="add-tag" value="Add Tag" name="add-tag">');

 		cj("#tagging-popup").dialog('open');

	});

	cj(".multi_clear").live('click', function() { 
		cj("#loading-popup").dialog('open');
		var selected = new Array();
		$('input:checked').each(function() {
			selected.push($(this).attr('name'));
		});
		cj( "#delete-confirm" ).dialog({
			buttons: {
				"Delete": function() {
					cj.each(selected, function(key, value) { 
 						cj('#'+value).remove();
						var old_total = parseInt(cj("#total_number").html(),10);
						cj("#total_number").html(old_total-1);
					});
					cj( this ).dialog( "close" );
						// cj.ajax({
						// 	url: '/civicrm/imap/ajax/deleteMessage',
						// 	data: {id: messageId,
						//     imapId: imapId },
						// 	success: function(data,status) {
						// 		cj("#"+messageId+'_'+imapId).remove();
						// 		// update count on top
						// 		var old_total = parseInt(cj("#total_number").html(),10);
						// 		cj("#total_number").html(old_total-1);
						// 		//destroyReSortable();
						// 	} 
						// });
					
				},
				Cancel: function() {
					cj( this ).dialog( "close" );
				}
			}
		});
		cj("#delete-confirm").dialog({ title:  "Clear "+selected.length+" Messages ?"});
		cj("#loading-popup").dialog('close');
		cj( "#delete-confirm" ).dialog('open');

	});





	// opening find match window
	cj(".find_match").live('click', function() {
		cj("#loading-popup").dialog('open');

		var messageId = cj(this).parent().parent().attr('data-id');
		var imapId = cj(this).parent().parent().attr('data-imap_id');
		cj('#imapper-contacts-list').html('');
		cj.ajax({
			url: '/civicrm/imap/ajax/message',
			data: {id: messageId,
				   imapId: imapId },
			success: function(data,status) {
				cj("#loading-popup").dialog('close');
				messages = cj.parseJSON(data);
				cj('#message_left_header').html('').append("<strong>From: </strong>"+messages.fromName +"  <i>&lt;"+ messages.fromEmail+"&gt;</i><br/><strong>Subject: </strong>"+messages.subject+"<br/><strong>Date: </strong>"+messages.date+"<br/>");
				if ((messages.forwardedEmail != '')){
					cj('#message_left_header').append("<strong>Forwarded by: </strong>"+messages.forwardedName+" <i>&lt;"+ messages.forwardedEmail+"&gt;</i><br/>");
				}
				cj('#message_left_email').html(messages.details);
				cj('#tabs-1 #first_name, #tabs-1 #last_name, #tabs-1 #phone, #tabs-1 #street_address, #tabs-1 #city, ').val('');

				cj('#email_id').val(messageId);
				cj('#imap_id').val(imapId);
				cj("#find-match-popup").dialog({ title:  "Reading: "+messages.subject });
				cj("#find-match-popup").dialog('open');
 				cj("#tabs").tabs();
 				cj('#tabs-1 #email_address').val(messages.fromEmail);

 				cj('#filter').click();
				switchName(messages.fromName);
			}
		});
	});
	// if it was a already matches message 
cj(".pre_find_match").live('click', function() {
		cj("#loading-popup").dialog('open');

		var activityId = cj(this).parent().parent().attr('data-id');
		var contactId = cj(this).parent().parent().attr('data-contact_id');
		cj('#imapper-contacts-list').html('');
 

		cj.ajax({
			url: '/civicrm/imap/ajax/activityDetails',
			data: {id: activityId, contact: contactId },
			success: function(data,status) {
				console.log(data);
		 		cj("#loading-popup").dialog('close');
		 		messages = cj.parseJSON(data);
		 		cj('#message_left_header').html('').append("<strong>From: </strong>"+messages.fromName +"  <i>&lt;"+ messages.fromEmail+"&gt;</i><br/><strong>Subject: </strong>"+messages.subject+"<br/><strong>Date: </strong>"+messages.date+"<br/>");
				if ((messages.forwardedEmail != '')){
					cj('#message_left_header').append("<strong>Forwarded by: </strong>"+messages.forwardedName+" <i>&lt;"+ messages.forwardedEmail+"&gt;</i><br/>");
				}
				cj('#message_left_email').html(messages.details);
		// 		cj('#email_id').val(messageId);
		// 		cj('#imap_id').val(imapId);
				cj("#find-match-popup").dialog({ title:  "Reading: "+messages.subject });
				cj("#find-match-popup").dialog('open');
 				cj("#tabs").tabs();
 
  				cj('#imapper-contacts-list').html('').append("<strong>currently matched to : </strong><br/>"+messages.fromName +"  <i>&lt;"+ messages.fromEmail+"&gt;</i> <br/> "+messages.fromAddress);
  
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

function pullActivitiesHeaders() {
	cj.ajax({
		url: '/civicrm/imap/ajax/getMatchedMessages',
		success: function(data,status) {
			messages = cj.parseJSON(data);
			//console.log(data);
			buildActivitiesList();
		}
	});
}

function destroyReSortable(){ 
	 
	var oTable = cj("#sortable_results").dataTable();
  	oTable.fnDestroy();

	makeListSortable();
}

function makeListSortable(){
cj("#sortable_results").dataTable({
		"aaSorting": [[ 5, "desc" ]],
		"aoColumnDefs": [  { "bSearchable": true, "bVisible": false, "aTargets": [ 3 ] }  ],
		"iDisplayLength": 50,
	//	"bStateSave": true,
		'aTargets': [ 1 ] 
	}); 
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
		messagesHtml += '<td class="forwarder">'+value.forwarder +'</td>';
		messagesHtml += '<td class="subject">'+value.subject +'</td>';
		messagesHtml += '<td class="date">'+value.date +'</td>';
		messagesHtml += '<td class="Actions"><span class="find_match"><a href="#">Find match</a></span> | <span class="delete"><a href="#">Delete</a></span></td> </tr>';
	});
	cj('#imapper-messages-list').html(messagesHtml);
	cj("#total_number").html(total_results);
	makeListSortable();
}

function buildActivitiesList() {
	if(messages == '' || messages == null)
		return;
	var messagesHtml = '';
	var total_results =0;
	$.each(messages, function(key, value) {
		total_results++;
 		messagesHtml += '<tr id="'+value.activitId+'" data-id="'+value.activitId+'" data-contact_id="'+value.contactId+'" class="imapper-message-box"> <td class="" ><input class="checkboxieout" type="checkbox" name="'+value.activitId+'" value="" /></td>';
		if( value.fromName != ''){
			messagesHtml += '<td class="name">'+value.fromName +'</td>';
		}else {
			messagesHtml += '<td class="name"> N/A </td>';
		}
		messagesHtml += '<td class="email">'+value.fromEmail +'</td>';
		messagesHtml += '<td class="forwarder">'+value.forwarder +'</td>';
		messagesHtml += '<td class="subject">'+value.subject +'</td>';
		messagesHtml += '<td class="date">'+value.date +'</td>';
		messagesHtml += '<td class="Actions"><span class="pre_find_match"><a href="#">Edit</a></span> | <span class="add_tag"><a href="#">Tag</a></span> | <span class="delete"><a href="#">Delete</a></span></td> </tr>';
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
		contactsHtml += '<input type="checkbox" class="imapper-contact-select-button" name="contact_id" value="'+value.contact_id+'" />';
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