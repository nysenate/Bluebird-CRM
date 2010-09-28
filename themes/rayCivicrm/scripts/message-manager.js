$(document).ready(function() {
    $('.messages br').remove();
    $('.messages').each(function(index){
        if($(this).html() == '') { $(this).remove();}
        });
	$('.messages').appendTo('#status .messages-container');
	if($('#status .messages-container').children().length > 0) {
		$('#status').append('<div id="status-handle"><span class="ui-icon ui-icon-arrowthickstop-1-n"></span></div>');
	}
	$('#status-handle').click(function(){
		$('.messages-container').slideToggle('fast');
		$('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-n');
		$('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-s');
	});
	
});

