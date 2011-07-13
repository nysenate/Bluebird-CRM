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
				var dataSet = JSON.parse(data);
				cj('.contactList').empty();
				cj('.showContactNumResult span').html('');
				cj('.contactsContainer .linkContact').slideDown('fast');
				cj('.showContactNumResult').slideDown('fast');
				cj('.showContactNumResult span').append(dataSet.length);
				var contactResult = [];
				var a = 1;
				cj.each(dataSet, function(i,contacts){
					if(contacts.postal_code == null) {contacts.postal_code = ''};
					if(contacts.state_province_id == '1031') {contacts.state_province_id = 'NY'};
					contactResult += '<div class="contactItem" id="contact-' + a +'">';
					contactResult += '<input type="radio" name="contactLinkList" value="' + contacts.id + '" class="radioButton">';
					contactResult += '<div class="left">';
					contactResult += '<span>' + contacts.display_name + '</span> ';
					if(contacts.nick_name !=null) {
						contactResult += ' (' + contacts.nick_name + ')';
					};
					contactResult += '<div>';
					if(contacts.street_address != null) {
						contactResult += contacts.street_address + ', ';
					}
					if(contacts.city != null) {
						contactResult += contacts.city + ', ';
					}
					contactResult += contacts.state_province_id;
					if(contacts.postal_code == null) {
						contactResult += ', ' + contacts.postal_code ;
					}
					contactResult += '</div>';
					contactResult += '</div>';
					contactResult += '<div class="right">';
					contactResult += '<a href="/civicrm/contact/view?reset=1&cid=' + contacts.id + '" target="_NEW">More Info</a>';
					contactResult += '</div>';
					contactResult += '</div>';
					a++;
				});
				cj('.contactList').append(contactResult);
				cj('.contactItem').hover(function()	{
					cj(this).addClass('hover');}, function() 	{
					cj(this).removeClass('hover');
				});
				cj('.contactItem').click(function()	{
					cj(this).children('input[@name="contactLinkList"]').attr('checked',true);
					goLinkButton('#toUnlink');
					cj(this).parent().children('.contactItem').removeClass('active');
					cj(this).addClass('active');
				});
			}
		});
		return false;
	});
	
	cj(".emailWrapper").each(function() {
		var button = cj(this);
		var id = cj(this).attr('id');
		button.click(
			function() {
				cj(".emailWrapper").removeClass('active');
				button.addClass('active');
				loading('.messageBody');
				cj.ajax({
					url: '/civicrm/imap/ajax/message',
					data: {id: id},
					success: function(data,status) {
						loaded('.messageBody');
						cj(".messageBody").html(data);
					}
				});
			}
		);
	});
});
function loading(box) {
	cj(".messageBody").html('');
	cj(box).prepend('<div class="loadingBox">Loading<span>...</span></div>');
	cj(box + ' .loadingBox span').fadeIn('slow').fadeOut('slow').fadeIn('slow').fadeOut('slow').fadeIn('slow').fadeOut('slow').fadeIn('slow');

	
}
function loaded(box) {
	cj(box).remove(box + ' .loadingBox');
}
function goUnlinkButton(GLB) {
cj(GLB).removeClass('visible'); cj('.linkContactButton.unlink').addClass('visible'); cj('.linkContact .button').addClass('linked');
}
function goLinkButton(GLB) {
cj(GLB).removeClass('visible'); cj('.linkContactButton.link').addClass('visible'); cj('.linkContact .button').removeClass('linked');
}