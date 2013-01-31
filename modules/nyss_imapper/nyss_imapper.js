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

	// onpageload 
	if(cj("#Activities").length){
		getMatchedMessages();
 	}else if(cj("#Unmatched").length){
		getUnmatchedMessages();
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
		height: 510,
		width: 960, // in ie the popup was overflowing
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

// BOTH MATCHED & UNMATCHED

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
		if((first_name) || (last_name) || (city) || (phone) || (street_address) || (email_address) || (dob)){
			cj.ajax({
				url: '/civicrm/imap/ajax/contacts',
				async:false,
				data: {
					state: '1031',
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
							buildContactList();
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
		var messageId = cj(this).parent().parent().attr('data-id');
		var imapId = cj(this).parent().parent().attr('data-imap_id');
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
						DeleteMessage(messageId,imapId);
					}
				},
				Cancel: function() {
					cj( this ).dialog( "close" );
				}
			},
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
			delete_secondary.push(cj(this).attr('data-id'));
			rows.push(cj(this).parent().parent().attr('id')); // not awesome but ok
		});
		if(!rows.length){
			cj("#loading-popup").dialog('close');
			alert('Use the checkbox to select a Message');
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
		id = cj(this).data('id');
		cj("#email_"+id+" .info").removeClass('hidden_email_info').addClass('shown_email_info').html('Hide Email');
		cj("#email_"+id).removeClass('hidden_email').addClass('shown_email');
	});

	cj(".shown_email_info").live('click', function(){
		id = cj(this).data('id');
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
		if(contactIds !='' ){
			cj.ajax({
				url: '/civicrm/imap/ajax/assignMessage',
				data: {
					messageId: messageId,
					imapId: imapId,
					contactId: contactIds
				},
				success: function(data, status) {
					data = cj.parseJSON(data);
					if (data.code == 'ERROR'){
						alert('Could Not Assign message : '+data.message);
					}else{
						// cj(".imapper-message-box[data-id='"+messageId+"']").remove();
						removeRow(messageId+'_'+imapId);
						cj("#find-match-popup").dialog('close');
						helpMessage(data.message);
						checkForMatch(data.key);
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
		var create_messageId = cj('#email_id').val();
		var create_imap_id = cj('#imap_id').val();
		var create_first_name = cj("#tab2 .first_name").val();
		var create_last_name = cj("#tab2 .last_name").val();
		var create_email_address = cj("#tab2 .email_address").val();
		var create_phone = cj("#tab2 .phone").val();
		var create_street_address = cj("#tab2 .street_address").val();
		var create_street_address_2 = cj("#tab2 .street_address_2").val();
		var create_zip = cj("#tab2 .zip").val();
		var create_city = cj("#tab2 .city").val();

		if((!!create_first_name) && (!!create_last_name)){
			cj.ajax({
				url: '/civicrm/imap/ajax/createNewContact',
				data: {
					messageId: create_messageId,
					imap_id: create_imap_id,
					first_name: create_first_name,
					last_name: create_last_name,
					email_address: create_email_address,
					phone: create_phone,
					street_address: create_street_address,
					street_address_2: create_street_address_2,
					postal_code: create_zip,
					city: create_city
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
								imapId: create_imap_id,
								contactId: contactData.contact
							},
							success: function(data, status) {
								contactData = cj.parseJSON(data);
								if (contactData.code == 'ERROR' || contactData.code == '' || contactData == null ){
									alert('Could Not Assign Message : '+contactData.message);
									return false;
								}else{
									cj("#find-match-popup").dialog('close'); 
									removeRow(create_messageId+'_'+create_imap_id);
									helpMessage('Contact created and '+contactData.message);
									checkForMatch(contactData.key);
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
			alert("Please Enter a first & last name");
		};
	});

	// opening find match window Unmatched
	cj(".find_match").live('click', function() {
		cj("#loading-popup").dialog('open');

		var messageId = cj(this).parent().parent().attr('data-id');
		var imapId = cj(this).parent().parent().attr('data-imap_id');
		var firstName = cj(this).parent().parent().children('.name').attr('data-firstName');
		var lastName = cj(this).parent().parent().children('.name').attr('data-lastName');

		cj('#imapper-contacts-list').html('');
		cj.ajax({
			url: '/civicrm/imap/ajax/message',
			data: {id: messageId,
				   imapId: imapId },
			success: function(data,status) {
				messages = cj.parseJSON(data);
				cj("#loading-popup").dialog('close');
				if(messages.code == 'ERROR'){
					if(messages.clear =='true')  removeRow(messageId+'_'+imapId);
					alert('Unable to load Message : '+ messages.message);
				}else{ 
					var icon ='';
					if( messages.attachmentfilename ||  messages.attachmentname ||  messages.attachment){ 
						if(messages.attachmentname ){var name = messages.attachmentname}else{var name = messages.attachmentfilename};
						icon = '<div class="ui-icon ui-icon-link attachment" title="'+name+'"></div>'
					}
					cj('#message_left_header').addClass(messages.email_user);
					cj('#message_left_email').addClass(messages.email_user);

					cj('#message_left_header').html('');
					cj('#message_left_header').append("<span class='popup_def'>From: </span>");
					if(messages.fromName) cj('#message_left_header').append(messages.fromName);
					if(messages.fromEmail) cj('#message_left_header').append("<span class='emailbubble marginL5'>"+shortenString(messages.fromEmail)+"</span>");

					cj('#message_left_header').append("<br/><span class='popup_def'>Subject: </span>"+shortenString(messages.subject,70)+" "+ icon+"<br/><span class='popup_def'>Date: </span>"+messages.forwarder_date_long+"<br/>");
					
					if ((messages.forwardedEmail != '')){
						cj('#message_left_header').append("<span class='popup_def'>"+messages.status+" from: </span>"+messages.forwardedName+" <span class='emailbubble marginL5'>"+ messages.forwardedEmail+"</span> "+ messages.date_short+ "<br/>");
					}
					// add some debug info to the message body on toggle
					if(messages.email_user == 'crmdev' || messages.email_user == 'crmtest' ){
						var debugHTML ="<span class='popup_def'>Dev & Test only</span><div class='debug_on'>Show Debug info</div><div class='debug_info'><div class='debug_remove'><i>UnMatched Message Header ("+messages.status+"):</i><br/><strong>Forwarder: </strong>"+messages.forwardedFull+"<br/><strong>Subject: </strong>"+messages.header_subject+"<br/><strong>Date: </strong>"+messages.date_long+"<br/><strong>Id: </strong>"+messages.uid+"<br/><strong>ImapId: </strong>"+messages.imapId+"<br/><strong>Format: </strong>"+messages.format+"<br/><strong>Mailbox: </strong>"+messages.email_user+"<br/><strong>Attachment Count: </strong>"+messages.attachment+"<br/>";
						if(messages.status !== 'direct'){
							debugHTML +="<br/><i>Parsed email body (origin):</i><br/><strong>Subject: </strong>"+messages.subject+"<br/><strong>Fristname: </strong>"+firstName+"<br/><strong>Lastname: </strong>"+lastName+"<br/><strong>Email: </strong>"+messages.fromEmail+"<br/><strong>Address lookup: </strong>"+messages.origin_lookup+"<br/><strong>Date: </strong>"+messages.forwarder_date_long+"";

						}
						debugHTML +="<span class='search_info'></span></div></div>";
						cj('#message_left_header').append(debugHTML);

						// we can create redmine issues with message details and assign to stefan from a url!
						submitHTML = cj('.debug_remove').html().replace(/'|"/ig,"%22").replace(/(<i>[*]<\/i>)/ig,"").replace(/(<br>)/ig,"%0d").replace(/(<([^>]+)>)/ig,"");
						bugHTML ="<div class='debug_sumit'><a href='http://dev.nysenate.gov/projects/bluebird/issues/new?issue[description]="+submitHTML+"&issue[category_id]=40&issue[assigned_to_id]=184' target='blank'> Create Redmine issue from this message</a></div><hr/>";
						cj('.debug_remove').append(bugHTML);
					}

					cj('#message_left_email').html(messages.details);
					cj('.first_name, .last_name, .phone, .street_address, .street_address_2, .city, .email_address').val('');
					cj('#email_id').val(messageId);
					cj('#imap_id').val(imapId);
					cj("#find-match-popup").dialog({ title:  "Reading: "+shortenString(messages.subject,100) });
					cj("#find-match-popup").dialog('open');
					cj("#tabs").tabs();
					cj('.email_address').val(messages.fromEmail);

					if(messages.fromEmail) cj('#filter').click();
					cj('.first_name').val(firstName);
					cj('.last_name').val(lastName);
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
		var activityId = cj('#email_id').val();
		var contact = cj('#imap_id').val();
		// only grabs the 1st one
		var contactRadios = cj('input[name=contact_id]').val();
		cj.ajax({
			url: '/civicrm/imap/ajax/reassignActivity',
			data: {
				id: activityId,
				contact: contact,
				change: contactRadios
			},
			success: function(data, status) {
				var data = cj.parseJSON(data);
				if (data.code =='ERROR'){
					alert('Could not reassign Message : '+data.message);
				}else{
					cj("#find-match-popup").dialog('close');
					// reset activity to new data 
					cj('#'+activityId).attr("data-contact_id",data.contact_id);	// contact_id
					cj('#'+activityId+" .name").attr("data-firstname",data.first_name);	// first_name
					cj('#'+activityId+" .name").attr("data-lastname",data.last_name);	// last_name
					cj('#'+activityId+" .match").html("ManuallyMatched");
					contact = '<a href="/civicrm/profile/view?reset=1&amp;gid=13&amp;id='+data.contact_id+'&amp;snippet=4" class="crm-summary-link"><div class="icon crm-icon '+data.contact_type+'-icon" title="'+data.contact_type+'"></div></a><a title="'+data.display_name+'" href="/civicrm/contact/view?reset=1&amp;cid='+data.contact_id+'">'+data.display_name+'</a><span class="emailbubble marginL5">'+shortenString(data.email,13)+'</span> <span class="matchbubble marginL5  H" title="This email was Manually matched">H</span>';

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
		return false;
	});
	/// remove activity from the activities screen, but don't delete it Matched
	cj(".clear_activity").live('click', function() {
		cj("#loading-popup").dialog('open');
		var activityId = cj(this).parent().parent().attr('data-id');

		cj( "#clear-confirm" ).dialog({
			buttons: {
				"Clear": function() {
					ClearActivity(activityId);
				},
				Cancel: function() {
					cj("#clear-confirm").dialog('close');
				}
			}
		});
		cj("#clear-confirm").dialog({ title:  "Clear Message from Matched Messages?"});
 		cj("#loading-popup").dialog('close');
		cj("#clear-confirm").dialog('open');
		return false;
	});

	// Edit a match allready assigned to an Activity Matched Screen
	cj(".edit_match").live('click', function() {
		cj("#loading-popup").dialog('open');

		var activityId = cj(this).parent().parent().attr('data-id');
		var contactId = cj(this).parent().parent().attr('data-contact_id');
		var firstName = cj(this).parent().parent().children('.name').attr('data-firstName');
		var lastName = cj(this).parent().parent().children('.name').attr('data-lastName');

		if(firstName && firstName !='null') cj('.first_name').val(firstName);
		if(lastName && lastName !='null') cj('.last_name').val(lastName);

		cj('#imapper-contacts-list').html('');

		cj.ajax({
			url: '/civicrm/imap/ajax/activityDetails',
			data: {id: activityId, contact: contactId },
			success: function(data,status) {
				messages = cj.parseJSON(data);
				if (messages.code == 'ERROR'){
					alert('Could not load message Details: '+messages.message);
					cj("#loading-popup").dialog('close');
					if(messages.clear =='true')   removeRow(activityId);
				}else{
					cj('#message_left_header').html('');
					if(messages.fromName) cj('#message_left_header').html('').append("<span class='popup_def'>From: </span>"+messages.fromName +"  ");
					if(messages.fromEmail) cj('#message_left_header').append("<span class='emailbubble '>"+ messages.fromEmail+"</span>");
	 		 		cj('#message_left_header').append("<br/><span class='popup_def'>Subject: </span>"+shortenString(messages.subject,70) +"<br/><span class='popup_def'>Date: </span>"+messages.date_long+"<br/>");
			 		cj('.email_address').val(messages.fromEmail);

					if ((messages.forwardedEmail != '')){
						cj('#message_left_header').append("<span class='popup_def'>Forwarded by: </span>"+messages.forwardedName+" <span class='emailbubble marginL5'>"+ messages.fromEmail+"</span><br/>");
					}
					// if we are on crmdev or crmtest show a debug window 
					cj('#message_left_header').addClass(messages.email_user);
					cj('#message_left_email').addClass(messages.email_user);
					if( messages.email_user == 'crmdev' || messages.email_user == 'crmtest' ){
							var match_type = (messages.match_type == 0) ? "Manually matched by user" : "Process Mailbox Script " ;
							var debugHTML ="<span class='popup_def'>Dev & Test only</span><div class='debug_on'>Show Debug info</div><div class='debug_info'><div class='debug_remove'><i>Matched Message Info:</i><br/><strong>Match Type: </strong>"+match_type+" ("+messages.match_type+")<br/><strong>Activty id: </strong>"+messages.uid+"<br/><strong>Assigned by: </strong>"+messages.forwardedName+"<br/><strong>Assigned To: </strong>"+messages.fromId+"<br/><strong>Created from message Id: </strong>"+messages.original_id+"<br/>";
							debugHTML +="<span class='search_info'></span></div></div>";
							cj('#message_left_header').append(debugHTML);
							// we can create redmine issues with message details and assign to stefan from a url ! 
							submitHTML = cj('.debug_remove').html().replace(/'|"/ig,"%22").replace(/(<i>[*]<\/i>)/ig,"").replace(/(<br>)/ig,"%0d").replace(/(<([^>]+)>)/ig,"");
							bugHTML ="<div class='debug_sumit'><a href='http://dev.nysenate.gov/projects/bluebird/issues/new?issue[description]="+submitHTML+"&issue[category_id]=40&issue[assigned_to_id]=184' target='blank'> Create Redmine issue from this message</a></div><hr/>";
							cj('.debug_remove').append(bugHTML);

						}

					cj('#message_left_email').html(messages.details);
					cj('#email_id').val(activityId);
					cj('#imap_id').val(contactId);
					cj("#loading-popup").dialog('close');
					cj("#find-match-popup").dialog({ title:  "Reading: "+shortenString(messages.subject,100)  });
					cj("#find-match-popup").dialog('open');
					cj("#tabs").tabs();
					cj('#imapper-contacts-list').html('').append("<strong>currently matched to : </strong><br/>"+messages.fromName +"  <i>&lt;"+ messages.fromEmail+"&gt;</i> <br/> ");
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

		var activityId = cj(this).parent().parent().attr('data-id');
		var contactId = cj(this).parent().parent().attr('data-contact_id');
		cj('#message_left_tag').html('').removeClass('tag_over_ride');
		cj(".autocomplete_tag").val('');
		cj("#contact_tag_name").val('');
		cj("#activity_tag_name").val('');
		cj(".autocomplete-tags-bank").html('');
		cj('#message_left_header_tag').html('');
		cj('#message_left_tag').html('').html('<div id="message_left_header_tag"></div><div id="message_left_email_tag"></div>');

		cj.ajax({
			url: '/civicrm/imap/ajax/activityDetails',
			data: {id: activityId, contact: contactId },
			success: function(data,status) {

				cj("#loading-popup").dialog('close');
				messages = cj.parseJSON(data);

				if(messages.code == 'ERROR'){
					if(messages.clear =='true') removeRow(activityId);
					alert('Unable to load Message : '+ messages.message);
					return false;
				}else{

					cj('#contact_tag_name').autocomplete( "/civicrm/imap/ajax/getTags", { width : 220, selectFirst : true, hintText: 'Type in a partial or complete name of an existing tag.', matchContains: true, minChars: 3
					}).result( function(event, data, json) {
						// console.log('Results : '+data);  // when you click on the results
					}).bind( 'click', function( ) {
						// console.log('Click : '+data); ? wtf 
					});

					cj('#activity_tag_name').autocomplete( "/civicrm/imap/ajax/getTags", { width : 220, selectFirst : true, hintText: 'Type in a partial or complete name of an existing tag.', matchContains: true, minChars: 3
					}).result( function(event, data, json) {
						// console.log('Results : '+data);  // when you click on the results
					}).bind( 'click', function( ) {
						// console.log('Click : '+data); ? wtf 
					});
					// http://skelos/civicrm/ajax/taglist?parentId=296&key=7371d1bba6c7758e9f8a570874c6c547&name=brown

					cj('#message_left_header_tag').html('').append("<span class='popup_def'>From: </span>"+messages.fromName +"  <span class='emailbubble'>"+ messages.fromEmail+"</span><br/><span class='popup_def'>Subject: </span>"+shortenString(messages.subject,70)+"<br/><span class='popup_def'>Date: </span>"+messages.date_long+"<br/>");
					cj('#message_left_header_tag').append("<input class='hidden' type='hidden' id='activityId' value='"+activityId+"'><input class='hidden' type='hidden' id='contactId' value='"+contactId+"'>");

					if ((messages.forwardedEmail != '')){
						cj('#message_left_header_tag').append("<span class='popup_def'>Forwarded by: </span>"+messages.forwardedName+" <span class='emailbubble'>"+ messages.forwardedEmail+"</span><br/>");
					}
					cj('#message_left_email_tag').html(messages.details);
					cj("#tagging-popup").dialog({ title:  "Tagging: "+ shortenString(messages.subject,50) });
					cj( "#tagging-popup" ).dialog({
						buttons: {
							"Tag": function() {
								pushtag();
							},
							"Tag and Clear": function() {
								// pushtag();
								cj("#clear-confirm").dialog('open');
								cj("#clear-confirm").dialog({
									buttons: {
										"Clear": function() {
											// ClearActivity(activityId);
											cj("#clear-confirm").dialog('close');
										},
										Cancel: function() {
											cj("#clear-confirm").dialog('close');
										}
									}
								});
								cj("#tagging-popup").dialog('close');
							},
							Cancel: function() {
								cj("#tagging-popup").dialog('close');
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
			alert('Use the checkbox to select a Message');
			return false;
		}
		// render the multi message view 

		cj("#contact_tag_name").val('');
		cj("#activity_tag_name").val('');
		cj(".autocomplete_tag").val('');

		cj(".autocomplete-tags-bank").html('');
		cj('#message_left_header_tag').html('');
		cj('.autocomplete-dropdown').html('');
		cj('#message_left_tag').html('').addClass('tag_over_ride');
		cj('#message_right_tag').append("<input class='hidden' type='hidden' id='activityId' value='"+activityIds+"'><input class='hidden' type='hidden' id='contactId' value='"+contactIds+"'>");

		cj.each(activityIds, function(key, activityId) {
			// console.log('activity :'+activityId+" - key : "+key+" - Contact : "+contactIds[key]);
			cj.ajax({
				url: '/civicrm/imap/ajax/activityDetails',
				data: {id: activityId, contact: contactIds[key] },
				success: function(data,status) {

					cj("#loading-popup").dialog('close');
					messages = cj.parseJSON(data);

					if(messages.code == 'ERROR'){
						if(messages.clear =='true') removeRow(activityId);
						alert('Unable to load Message : '+ messages.message);
						return false;
					}else{

						// .append("<span class='popup_def'>From: </span>"+messages.fromName +"  <span class='emailbubble'>"+ messages.fromEmail+"</span><br/><span class='popup_def'>Subject: </span>"+messages.subject+"<br/><span class='popup_def'>Date: </span>"+messages.date_long+"<br/>");

						// cj('#contact_tag_name').autocomplete( "/civicrm/imap/ajax/getTags", { width : 220, selectFirst : true, hintText: 'Type in a partial or complete name of an existing tag.', matchContains: true, minChars: 3
						// }).result( function(event, data, json) {
						// 	// console.log('Results : '+data);  // when you click on the results
						// }).bind( 'click', function( ) {
						// 	// console.log('Click : '+data); ? wtf 
						// });

						// cj('#activity_tag_name').autocomplete( "/civicrm/imap/ajax/getTags", { width : 220, selectFirst : true, hintText: 'Type in a partial or complete name of an existing tag.', matchContains: true, minChars: 3
						// }).result( function(event, data, json) {
						// 	// console.log('Results : '+data);  // when you click on the results
						// }).bind( 'click', function( ) {
						// 	// console.log('Click : '+data); ? wtf 
						// });
						// // http://skelos/civicrm/ajax/taglist?parentId=296&key=7371d1bba6c7758e9f8a570874c6c547&name=brown

				 		cj('#message_left_tag').append("<div id='header_"+activityId+"' data-id='"+activityId+"' class='message_left_header_tags'><span class='popup_def'>From: </span>"+messages.fromName +"  <span class='emailbubble'>"+ messages.fromEmail+"</span><br/><span class='popup_def'>Subject: </span>"+shortenString(messages.subject,70)+"<br/><span class='popup_def'>Date: </span>"+messages.date_long+"<br/></div><div id='email_"+activityId+"' class='hidden_email' data-id='"+activityId+"'></div>");

						if ((messages.forwardedEmail != '')){
							cj('#header_'+activityId).append("<span class='popup_def' >Forwarded by: </span>"+messages.forwardedName+" <span class='emailbubble'>"+ messages.forwardedEmail+"</span><br/>");
						}
						
						cj('#email_'+activityId).html("<span class='info hidden_email_info' data-id='"+activityId+"'>Show Email</span><br/><span class='email'>"+messages.details+"</span>");

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
					// pushtag();
					cj("#clear-confirm").dialog('open');
					cj("#clear-confirm").dialog({
						buttons: {
							"Clear": function() {
								// ClearActivity(activityId);
								cj("#clear-confirm").dialog('close');
							},
							Cancel: function() {
								cj("#clear-confirm").dialog('close');
							}
						}
					});
					cj("#tagging-popup").dialog('close');
				},
				Cancel: function() {
					cj("#tagging-popup").dialog('close');
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
			alert('Use the checkbox to select a Message');
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
		cj("#clear-confirm").dialog({ title:  "Clear "+delete_ids.length+" Messages ?"});
		cj("#loading-popup").dialog('close');
		cj( "#clear-confirm" ).dialog('open');
		return false;
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
		url: '/civicrm/imap/ajax/unmatchedMessages',
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
		url: '/civicrm/imap/ajax/getMatchedMessages',
		success: function(data,status) {
			messages = cj.parseJSON(data);
			buildActivitiesList();
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
		return a.match(/data="(.*?)"/)[1].toLowerCase();
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
		"bPaginate": false,
		"bAutoWidth": false,
		"bInfo": false,
	});
	cj("#sortable_results_filter").append('<a id="search_help" href="#">help</a>')
	checks();
}

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
	if(messages.count == '0' || messages == null){
		cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty">No records found</td>');
		cj("#total_number").html('0');
	}else{
		var messagesHtml = '';
		var total_results = messages.stats.overview.successes;
		cj.each(messages.successes, function(key, value) {
			var icon ='';
			if(value.date_short != null){

				// wrap the row
				messagesHtml += '<tr id="'+value.uid+'_'+value.imap_id+'" data-key="'+value.key+'" data-id="'+value.uid+'" data-imap_id="'+value.imap_id+'" class="imapper-message-box"> <td class="" ><input class="checkboxieout" type="checkbox" name="'+value.uid+'"  data-id="'+value.imap_id+'"/></td>';

				// build a match count bubble
				countWarn = (value.match_count == 1) ? 'warn' :  '';
				countMessage = (value.match_count == 1) ? 'This address should have matched automatically' : 'This email address matches '+value.match_count+' records in bluebird';
				countStatus = (value.match_count == 0) ? 'empty' :  'multi';
 				countIcon = '<span class="matchbubble marginL5 '+countWarn+' '+countStatus+'" title="'+countMessage+'">'+value.match_count+'</span></td>';


 				// build the name box
				if( value.from_name != ''  && value.from_name != null){
					messagesHtml += '<td class="name" data-firstName="'+firstName(value.from_name)+'" data-lastName="'+lastName(value.from_name)+'">'+shortenString(value.from_name,20);

					if( value.from_email != '' && value.from_email != null){
						messagesHtml += '<span class="emailbubble marginL5">'+shortenString(value.from_email,15)+'</span>';
						messagesHtml +=  countIcon;
					}else{
						messagesHtml += '<span class="emailbubble warn marginL5" title="We could not find the email address of this record">No email found!</span>';
					}
					messagesHtml +='</td>';

				}else if( value.from_email != '' && value.from_email != null ){
					messagesHtml += '<td class="name"><span class="emailbubble">'+shortenString(value.from_email,25)+'</span>';
					messagesHtml +=  countIcon;
				}else {
					messagesHtml += '<td class="name"><span class="matchbubble warn" title="There was no info found in regard to the source of this message">No source info found</span></td>';
					messagesHtml +=  countIcon;
				}

				// dealing with attachments
				if( value.attachmentfilename ||  value.attachmentname ||  value.attachment){ 
					if(value.attachmentname ){var name = value.attachmentname}else{var name = value.attachmentfilename};
					icon = '<div class="ui-icon inform-icon attachment" title="Currently attachments are not allowed" ></div><div class="ui-icon ui-icon-link attachment" title="'+name+'">'+value.attachment+'</div>'
				}

				messagesHtml += '<td class="subject">'+shortenString(value.subject,40) +' '+icon+'</td>';
				messagesHtml += '<td class="date"><span data="'+value.date_u+'">'+value.date_short +'</span></td>';

				// hidden column to sort by
				if(value.match_count != 1){
					var match_short = (value.match_count == 0) ? "NoMatch" : "MultiMatch" ;
					messagesHtml += '<td class="match hidden"><span data="'+match_short+'">'+match_short +'</span></td>';
				}else{
					messagesHtml += '<td class="match hidden"><span data="Error">ProcessError</span></td>';
				}

				// check for direct messages & not empty forwarded messages
				if((value.status == 'direct' ) && (value.forwarder_email != '')){
					messagesHtml += '<td class="forwarder">Direct '+shortenString(value.from_email,10)+'</td>';
				}else if(value.forwarder_email != ''){
					messagesHtml += '<td class="forwarder">'+shortenString(value.forwarder_email,14)+'</td>';
				}else{
					messagesHtml += '<td class="forwarder"> N/A </td>';
				}
				
				messagesHtml += '<td class="actions"><span class="find_match"><a href="#">Find match</a></span><span class="delete"><a href="#">Delete</a></span></td> </tr>';
			}
		});
		cj('#imapper-messages-list').html(messagesHtml);
		cj("#total_number").html(total_results);
		makeListSortable();
		cj('.checkbox').removeClass('sorting');
		cj('.Actions').removeClass('sorting');

	}
}
function DeleteMessage(id,imapid){
	cj.ajax({
		url: '/civicrm/imap/ajax/deleteMessage',
		data: {id: id,
		imapId: imapid },
		async:false,
		success: function(data,status) {
			deleted = cj.parseJSON(data);
			if(deleted.code == 'ERROR' || deleted.code == '' || deleted.code == null){
				if(deleted.clear =='true')  removeRow(id+'_'+imapid);
				alert('Unable to Delete Message : '+deleted.message);
			}else{
				removeRow(id+'_'+imapid); ;
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
		url: '/civicrm/imap/ajax/unproccessedActivity',
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
		async:false,
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
function pushtag(){
	var tags = new Array();

	// delete_ids = message id / activity id 
	// delete_secondary = imap id / contact id 
	var delete_ids = new Array();
	var delete_secondary = new Array();
	var rows = new Array();

	cj('#imapper-messages-list input:checked').each(function() {
		delete_ids.push(cj(this).attr('name'));
		delete_secondary.push(cj(this).attr('data-id'));
		rows.push(cj(this).parent().parent().attr('id')); // not awesome but ok
	});

	// console.log(delete_ids);
	// console.log(delete_secondary);
	// console.log(rows);

	// // if there are tags selected
	// // we can either have multiple rows, or single rows selected 
	if(cj(".autocomplete-tags-bank").html().length){
		cj('.autocomplete-tags-bank a').each(function(index) {
			var tagId = cj(this).attr('data-id');
			console.log(tagId);
		});
	}else{
		alert('Please Enter a tag or 2');
	}
	// 		cj.ajax({
	// 			url: '/civicrm/imap/ajax/addTags',
	// 			data: {activityId: activityId, contactId: contactId, tags: tagId},
	// 			success: function(data,status) {
	// 				if(delete_ids.length > 0 ){
	// 					cj.each(delete_ids, function(key, value) { 
	// 						cj.ajax({
	// 							url: '/civicrm/imap/ajax/unproccessedActivity',
	// 							data: {id: value},
	// 							success: function(data,status) { 
	// 								cj("#tagging-popup").dialog('close');
	// 								helpMessage('Tag Added');
	// 									removeRow(value);
	// 							},
	// 							error: function(){
	// 								alert('unable to add tag');
	// 							}
	// 						});
	// 					});
	// 				}else{
	// 					var activityId = cj("#activityId").val();
	// 					cj.ajax({
	// 						url: '/civicrm/imap/ajax/unproccessedActivity',
	// 						data: {id: activityId},
	// 						success: function(data,status) { 
	// 							cj("#tagging-popup").dialog('close');
	// 							removeRow(activityId);
	// 							helpMessage('Tag Added');
	// 						},
	// 						error: function(){
	// 							alert('unable to add tag');
	// 						}
	// 					});
	// 				}
					
	// 			}
	// 	 	});
	// 	});
	// }else{
	// 	alert("please select a tag");
	// 	return;
	// }
	return false;
};

// matched messages screen 
function buildActivitiesList() {
	if(messages.count == '0' || messages == null){
		cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty">No records found</td>');
		cj("#total_number").html('0');
	}else{
		var messagesHtml = '';
		var total_results = messages.stats.overview.successes;
		// console.log(messages);
		cj.each(messages.successes, function(key, value) {
			if(value.date_short != null){
				messagesHtml += '<tr id="'+value.activitId+'" data-id="'+value.activitId+'" data-contact_id="'+value.contactId+'" class="imapper-message-box"> <td class="" ><input class="checkboxieout" type="checkbox" name="'+value.activitId+'" data-id="'+value.contactId+'"/></td>';

				if( value.fromName != ''){
					messagesHtml += '<td class="name" data-firstName="'+value.firstName +'" data-lastName="'+value.lastName +'">';
					messagesHtml += '<a class="crm-summary-link" href="/civicrm/profile/view?reset=1&gid=13&id='+value.contactId+'&snippet=4">';
					messagesHtml += '<div class="icon crm-icon '+value.contactType+'-icon"></div>';
					messagesHtml += '</a>';
					messagesHtml += '<a href="/civicrm/contact/view?reset=1&cid='+value.contactId+'" title="'+value.fromName+'">'+shortenString(value.fromName,19)+'</a>';
					messagesHtml += ' ';
				}else {
					messagesHtml += '<td class="name">N/A ';
				}

				messagesHtml += '<span class="emailbubble marginL5">'+shortenString(value.fromEmail,13)+'</span>';

				match_sort = 'ProcessError';
				if(value.match_type){
					var match_string = (value.match_type == 0) ? "Manually matched" : "Automatically Matched" ;
					var match_short = (value.match_type == 0) ? "H" : "A" ;
					match_sort = (value.match_type == 0) ? "ManuallyMatched" : "AutomaticallyMatched" ;
					messagesHtml += '<span class="matchbubble marginL5 '+match_short+'" title="This email was '+match_string+'">'+match_short+'</span>';
				}
					messagesHtml +='</td>';
				messagesHtml += '<td class="subject">'+shortenString(value.subject,40) +'</td>';
				messagesHtml += '<td class="date"><span data="'+value.date_u+'">'+value.date_short +'</span></td>';
				messagesHtml += '<td class="match hidden">'+match_sort +'</td>';

				messagesHtml += '<td class="forwarder">'+shortenString(value.forwarder,14)+'</td>';
				// messagesHtml += '<td class="actions"><span class="edit_match"><a href="#">Edit</a></span><span class="disabled no_add_tag"><a href="#" title="Tagging is not ready yet">Tag</a></span><span class="clear_activity"><a href="#">Clear</a></span><span class="delete"><a href="#">Delete</a></span></td> </tr>';
				messagesHtml += '<td class="actions"><span class="edit_match"><a href="#">Edit</a></span><span class="add_tag"><a href="#" title="Tagging is not ready yet">Tag</a></span><span class="clear_activity"><a href="#">Clear</a></span><span class="delete"><a href="#">Delete</a></span></td> </tr>';

			}
		});
		cj('#imapper-messages-list').html(messagesHtml);
		cj("#total_number").html(total_results);
		makeListSortable();
	}
}


function buildContactList() {
	var contactsHtml = '';
	html = "<br/><br/><i>Contact Search results:</i><br/><strong>Number of matches: </strong>"+contacts.length+' ';
	if(contacts.length < 1){
		html += "(No Matches)";
	}else if(contacts.length == 1){
		html += "(Should have auto Matched)";
	}else if(contacts.length > 1){
		html += "(MultiMatch)";	
	}
	cj('.search_info').html(html);
	cj.each(contacts, function(key, value) {
		// calculate the aprox age
		if(value.birth_date){
			var date = new Date();
			var year  = date.getFullYear();
			var birth_year = value.birth_date.substring(0,4);
			var age = year - birth_year;
		}
		contactsHtml += '<div class="imapper-contact-box" data-id="'+value.id+'">';
		contactsHtml += '<div class="imapper-address-select-box">';
		contactsHtml += '<input type="checkbox" class="imapper-contact-select-button" name="contact_id" value="'+value.id+'" />';
		contactsHtml += '</div>';
		contactsHtml += '<div class="imapper-address-box">';
		if(value.display_name){ contactsHtml += value.display_name + '<br/>'; };
		if(value.birth_date){ contactsHtml += '<strong>'+age+'</strong> - '+value.birth_date + '<br/>';}
		if(value.email){ contactsHtml += value.email + '<br/>'; }
		if(value.phone){ contactsHtml += value.phone + '<br/>'; }
		if(value.street_address){ contactsHtml += value.street_address + '<br/>'; }
		if(value.city){ contactsHtml += value.city + ', NY ' + value.postal_code + '<br/>'; }
		contactsHtml += '</div></div>';
		contactsHtml += '<div class="clear"></div>';
	});
	cj('#imapper-contacts-list').append(contactsHtml);

}


// displays a help window + current date time
// if same message and hasn't disappared yet, update
function helpMessage(message){
	var d = new Date();
	var h = d.getHours();
	var m = d.getMinutes();
	if(m < 10){ m = '0'+m;}
	var s = d.getSeconds();
	if(s < 10){ s = '0'+s;}
	var rm = h+"_"+m;
	var messageclass = message.replace(/[^a-z0-9]/gi,'');;

	var updateCheck = cj("#top").find("."+messageclass).html();

	if(updateCheck){
		var oldCount = cj("#top ."+messageclass).find(".count").html();
		var oldMessage = cj("#top ."+messageclass).find(".message").html();
		oldCount = parseInt(oldCount,10);
		count = oldCount+1;
		cj("#top ."+messageclass).html("<p><span class='count'>"+count+"</span> <span class='message'>"+message+"</span> <small>"+h+":"+m+":"+s+"</small></p>");
	}else{
		cj("#top").append("<div class='"+rm+" "+messageclass+"' id='help' ><p><span class='count'>1</span> <span class='message "+messageclass+"'>"+message+"</span> <small>"+h+":"+m+":"+s+"</small></p></div>");
	}
	// fade out and remove
	setTimeout(function(){
		cj("."+messageclass).fadeOut(1000, function(){
			$(this).remove();
		});
	}, 60000);
}

// Create shortended String with yitle tag for hover 
function shortenString(subject, length){
	if(subject){
		if (subject.length > length ){
		var safe_subject = '<span title="'+subject+'">'+subject.substring(0,length)+"...</span>";
		return safe_subject;
		}else{
			return subject;
		}
	}else{
		return "N/A";
	}
 }
			
// Look for rows that match the KEY of a matched row
// Remove them from the view so the user doesn't re-add / create duplicates
// key = md5 ( shortened to 8 ) of user_email + user_name	
function checkForMatch(key){
	cj('.imapper-message-box').each(function(i, item) {
		check = cj(this).data('key'); 		
		id = cj(this).attr('id'); 
		remove = 0;
		if (key == check) {
			if($('.matchbubble.empty',this).length){
				remove++;
				removeRow(id);
			}
		};
	});
}

// updates the count at the top of the page 
function updateTotalCount(){
	// count = cj('.imapper-message-box').length;
	// cj("#total_number").html(count);
	// if(count < 1){
	// 	cj("#total_number").html('0');
	// 	cj('#imapper-messages-list').html('<td valign="top" colspan="7" class="dataTables_empty">No records left, Please Reload the page</td>');
	// }
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
		alert('could not delete row');
	}
}

// unbind the sort on the checkbox and actions
cj("th.checkbox").removeClass('sorting').unbind('click');
cj("th.Actions").removeClass('sorting').unbind('click');
