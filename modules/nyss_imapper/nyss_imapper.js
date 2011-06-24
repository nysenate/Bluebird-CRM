$(document).ready(function(){
	var first_name = cj('#first_name');
	var last_name = cj('#last_name');
	var city = cj('#city');
	var phone = cj('#phone');
	var state = cj('#state');
	var street_address = cj('#street_address');
	
	cj("#reset").click(function() {
		city.val("");
		last_name.val("");
		first_name.val("");
		phone.val("");
		state.val(1031);
		street_address.val("");
		return false;
	});
	
	cj("#filter").click(function() {
		return false;
	});
	
	
	cj(".imapper-toggle").each(function() {
		var button = cj(this);
		var container = button.parent().parent();
		var body = cj(".imapper-body",container);
		var parts = container.attr('id').split('_');
		var id = parts.pop();
		var type = parts.pop();
		
		button.toggle(
			function() {
				button.html("+");
				body.hide();
			},
			function() {
				button.html("-");
				if(!body.html()) {
					cj.ajax({
						url: '/civicrm/imap/ajax/first',
						data: {
							type: type,
							id: id,
						},
						success: function(data,status) {
							body.html(data);
						}
					});
				}
				body.show();
			}
		).click();
	});
		
	imapper_options = {
		'matchContains': false,
		'matchCase': false,
		'matchSubset': true,
		'minChars': 1,
		'scroll': true,
	};
	
	/*
	cj('#city').autocomplete(nyss_imapper_city_names,imapper_options);
	cj('#first_name').autocomplete(nyss_imapper_first_names,imapper_options);
	cj('#last_name').autocomplete(nyss_imapper_last_names,imapper_options);
	cj('#street_address').autocomplete(nyss_imapper_street_addresses,imapper_options);
	*/
	/*
	cj('#city').autocomplete('/civicrm/imap/ajax/city',{
		minChars:0,
		extraParams: {
			state: function() { return cj("#state").val(); },
			first_name: function() { return cj('#first_name').val(); },
			last_name: function() { return cj('#last_name').val(); },
			city: function() { return cj('#city').val(); },
			street_address: function() { return cj('#street_address').val(); },
			type: 'city',
		},
		'cacheLength': 1,
		'matchContains':false,
		'scroll': true,
	});
	
	cj('#first_name').autocomplete('/civicrm/imap/ajax/city',{
		minChars:0,
		extraParams: {
			state: function() { return cj("#state").val(); },
			first_name: function() { return cj('#first_name').val(); },
			last_name: function() { return cj('#last_name').val(); },
			city: function() { return cj('#city').val(); },
			street_address: function() { return cj('#street_address').val(); },
			type: 'first_name',
		},
		'cacheLength': 1,
		'matchContains':false,
		'scroll': true,
	});
	
	cj('#last_name').autocomplete('/civicrm/imap/ajax/city',{
		minChars:0,
		extraParams: {
			state: function() { return cj("#state").val(); },
			first_name: function() { return cj('#first_name').val(); },
			last_name: function() { return cj('#last_name').val(); },
			city: function() { return cj('#city').val(); },
			street_address: function() { return cj('#street_address').val(); },
			type: 'last_name',
		},
		'cacheLength': 1,
		'matchContains':false,
		'scroll': true,
	});
	
	cj('#street_address').autocomplete('/civicrm/imap/ajax/city',{
		minChars:0,
		extraParams: {
			state: function() { return cj("#state").val(); },
			first_name: function() { return cj('#first_name').val(); },
			last_name: function() { return cj('#last_name').val(); },
			city: function() { return cj('#city').val(); },
			street_address: function() { return cj('#street_address').val(); },
			type: 'street_address',
		},
		'cacheLength': 1,
		'matchContains':false,
		'scroll': true,
	});
	*/
});