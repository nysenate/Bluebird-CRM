$(document).ready(function() {
	$('.messages').appendTo('#status .messages-container');
	if($('#status .messages-container').children().length > 0) {
		$('#status').append('<div id="status-handle"><span class="ui-icon ui-icon-arrowthickstop-1-n"></span></div>');
	}
	
	$('#status-handle').click(function(){
		$('.messages-container').toggle('fast');
		$('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-n');
		$('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-s');
	});
	
});

