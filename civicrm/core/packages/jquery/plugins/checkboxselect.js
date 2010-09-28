/*
 * select / unselect checkboxes plugin for jQuery
 *########################################
 *# Logic to select/unselect checkboxes  #
 *########################################
 * Last Action   Current Status	     Result
 *	         ( of the checkbox 
 *		   you selected )
 *
 * Checked       Checked             Uncheck the all checkboxes between the two
 * Checked       UnChecked	     Check the all checkboxes between the two
 * UnChecked     Checked	     Uncheck the all checkboxes between the two
 * UnChecked     UnChecked 	     Check the all checkboxes between the two
 */

(function($){

var lastChecked = null;

$(document).ready(function() {
    $('.form-checkbox').click(function(event) {
	//class selector is present?
	var isSelector = $(this).parent().parent().parent().parent().attr('class');
	//if not donot allow
	if (isSelector != 'selector' ) {
	    return;
	}
	if ( !lastChecked ) {
	    lastChecked = this;
	    return;
	}
	if ( event.shiftKey ) {
	    var start = $('.form-checkbox').index(this);
	    var end   = $('.form-checkbox').index(lastChecked);
	    if ( start == end ) {
		return;
	    }
	    var validLastcheck = $(lastChecked).parent().parent().attr('class');
	    var validthischeck = $(this).parent().parent().attr('class');
	    //don't allow the select/unslect other than search result e.g.(search form)
	    //TODO: once all headers are sticky( under development), we can remove the columnheader check	    
	    var params = new Array( "listing-box", "columnheader", "sticky", "" );
	    for ( i = 0; i < params.length; i++ ) {		
		if ( params[i] == validLastcheck || params[i] == validthischeck ) {
		    return;
		}
	    }
	    var min   = Math.min( start, end );
	    var max   = Math.max( start, end );
	    if ( lastChecked.checked && this.checked ) {
		lastChecked.checked = true;
	    } else if ( lastChecked.checked  && !this.checked ) {
	    	lastChecked.checked = false;
	    } else if ( !lastChecked.checked && this.checked  ) {
	    	lastChecked.checked = true;
	    } else if (! lastChecked.checked && !this.checked ) {
		lastChecked.checked = false;
	    } 
	    for ( i = min; i <= max; i++ ) {
		//check the checkboxes between the two chech boxes
		$('.form-checkbox')[i].checked = lastChecked.checked;
	    }
	    //add class for tr and remove if it unchecked
	    $('.selector tbody tr td:first-child input:checkbox').each( function() {
		var oldClass = $(this).parent().parent().attr('class');
		if ( this.checked ) {
		    $(this).parent().parent().removeClass().addClass('row-selected '+ oldClass);
		} else {
		    var lastClass = $(this).parent().parent().attr('class');
		    var str       = lastClass.toString().substring(12);
		    if ( lastClass.substring(0,12) == "row-selected" ) {
			$(this).parent().parent().removeClass().addClass(str);
		    }
		}	
	    });	    
	}
	lastChecked = this;
    });
});

})(jQuery);
