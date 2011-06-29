$(document).ready(function(){
	var first_name = cj('#first_name');
	var last_name = cj('#last_name');
	var city = cj('#city');
	var phone = cj('#phone');
	var state = cj('#state');
	var street_address = cj('#street_address');
	var reset = cj('#reset');
	var filter = cj('#filter');
	
	reset.click(function() {
		city.val("");
		last_name.val("");
		first_name.val("");
		phone.val("");
		street_address.val("");
		return false;
	});
	
	filter.click(function() {
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
				//Write somethign here!!!
				//Data is an array of contact json objects
			}
		});
		return false;
	});
	
	cj(".imapper-message-toggle").each(function() {
		var button = cj(this);
		var container = button.parent().parent();
		var body = cj(".imapper-body",container);
		var id = container.attr('id').split('_').pop();
		
		button.toggle(
			function() {
				button.html("+");
				body.hide();
			},
			function() {
				button.html("-");
				if(!body.html()) {
					cj.ajax({
						url: '/civicrm/imap/ajax/message',
						data: {id: id},
						success: function(data,status) {
							body.html(data);
						}
					});
				}
				body.show();
			}
		).click();
	});
});