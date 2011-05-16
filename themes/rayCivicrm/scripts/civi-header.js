$(document).ready(function() {
	$('h1.title[innerHTML=CiviCRM Home]').html('Dashboard');

	$('.civi-advanced-search-button').click(function(){
		$('.crm-advanced_search_form-accordion').toggleClass('crm-accordion-open');
		$('.crm-advanced_search_form-accordion').toggleClass('crm-accordion-closed');
        if($('.crm-advanced_search_form-accordion').hasClass('crm-accordion-open')) {
        	    $(this).addClass('civi-adv-search-linkwrap-active');
            } else {
        	    $(this).removeClass('civi-adv-search-linkwrap-active');                
                }
		return false;
	});
    
    $('.civi-advanced-search-link').click(function(){
		$(this).toggleClass('civi-adv-search-linkwrap-active');
		$('.civi-adv-search-body').toggle();
		return false;
	});
    
    
	/*$("input[name=sort_name]").focus(function(){
		var defaultText = $(this).val();
		if(defaultText === 'enter name or email'){
			$(this).val('');
			$(this).addClass('input-active');			
			}
		});*/
	/*$("input[name=sort_name]").blur(function(){
		var defaultText = $(this).val();
		if(defaultText === ''){
			$(this).val('enter name or email');
			$(this).removeClass('input-active');
			}
		});	*/

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
	/*$("#address_1_state_province_id option[value='1031']").attr('selected', 'selected');*/
	
	$('.action-item[innerHTML=File On Case]').remove();
	
	var extID = $('#external_identifier').val();
    $('#external_identifier').after(extID).remove();
	
	//prevent duplicate form submission with enter key
	var submitted = false;
	$('form').submit(function(e){
		if( submitted && e.keyCode == 13 ) {
        	return false;
        } else if ( e.keyCode == 13 ) {
        	submitted = true;
        	return true;
        }
	});
    
});
