$(document).ready(function() {
	adminNotLoaded = true;
	$('#civi-admin-link').click(function(){
		$('.primary-link').removeClass('primary-link-active');
		$(this).toggleClass('admin-link-active');
		$(this).toggleClass('active');
		var adminURL = $(this).attr('href');
		adminURL = adminURL;
		$('.civi-admin-block-wrapper').toggle();
		if(adminNotLoaded == true){
			$('.civi-admin-block').load(adminURL);
		}
		adminNotLoaded = false;
		
		return false;
	});

	$('.civi-advanced-search-link').click(function(){
		$(this).toggleClass('civi-adv-search-linkwrap-active');
		$('.civi-adv-search-body').toggle();
		return false;
	});

	$("input[name=sort_name]").focus(function(){
		var defaultText = $(this).val();
		if(defaultText === 'enter name or email'){
			$(this).val('');
			$(this).addClass('input-active');			
			}
		});
	$("input[name=sort_name]").blur(function(){
		var defaultText = $(this).val();
		if(defaultText === ''){
			$(this).val('enter name or email');
			$(this).removeClass('input-active');
			}
		});	

	$("#civi_text_search").focus(function(){
		var defaultText = $(this).val();
		if(defaultText === 'enter any text'){
			$(this).val('');
			$(this).addClass('input-active');			
			}
		});
	$("#civi_text_search").blur(function(){
		var defaultText = $(this).val();
		if(defaultText === ''){
			$(this).val('enter any text');
			$(this).removeClass('input-active');
			}
		});
		
	 $('body').click(function() {
	 	$('.primary-link').removeClass('primary-link-active');
	 });
	
	 $('.primary-link').click(function(event){
	     event.stopPropagation();
	 });
	
			
	$('.primary-link .main-menu-item').click( function(){
		$('.civi-admin-block-wrapper').hide();
		$('.admin-link a').removeClass('active'); 
		$('.primary-link').removeClass('primary-link-active');
		$(this).parent().toggleClass('primary-link-active')
		});
	
	$('#civicrm-menu').hide();
	if ($('#civicrm-menu').length >0){
		$('#civi-admin-menu').show();
		}
	$('#civi-admin-menu').click(function(){$('#civicrm-menu').toggle();});
	
	$('.civi-general-search').append('<div id="general-form-hack"></div>');
	$('#general-form-hack').hide()
		.load('/nyss/civicrm/contact/search/custom&csid=15&reset=1&snippet=1', 
			function(){
				$('#general-form-hack #Custom input[type=hidden]').appendTo('#gen-search-wrapper');
			});

	
	
});



DD_roundies.addRule('.create-link, .account-info-wrapper, .ac_results-inner, .civi-advanced-search-link, .civi-menu, .civi-admin-block, .civi-search-section .input-wrapper, .not-logged-in #block-user-0 #edit-submit, .standard-container', '4px');
DD_roundies.addRule('#crm-container .column-1 .widget-header, #footer #dashboard-link-wrapper', '4px 4px 0px 0px');
DD_roundies.addRule('#crm-container .column-1 .widget-content', '0px 0px 4px 4px');
