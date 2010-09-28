/* 
   jQuery TextAreaResizer plugin
*/
$(document).ready(function() {
    cj('textarea.huge:not(.processed)').TextAreaResizer();
    cj('textarea.form-textarea:not(.processed)').TextAreaResizer();
});

(function($) {
    /* private variable "oHover" used to determine if you're still hovering over the same element */
    var textarea, staticOffset; 
    // added the var declaration for 'staticOffset' thanks to issue logged by dec.
    var iLastMousePos = 0;
    //mininum text area height
    var iMin          = 32;
    
    var grip;
    /* TextAreaResizer plugin */
    $.fn.TextAreaResizer = function() {
	return this.each(function() {
	    textarea = $(this).addClass('processed'), staticOffset = null;
	    
	    // When wrapping the text area, work around an IE margin bug.  See:
	    // http://jaspan.com/ie-inherited-margin-bug-form-elements-and-haslayout
	    $(this).wrap('<div class="resizable-textarea"><span></span></div>')
	    .parent().append($('<div class="grippie"></div>').bind("mousedown",{el: this} , startDrag));

	    var grippie = $('div.grippie', $(this).parent())[0];
	    grippie.style.marginRight = (grippie.offsetWidth - $(this)[0].offsetWidth) +'px';
	});
    };
    
    /* private functions */
    function startDrag(e) {
	textarea = $(e.data.el);
	textarea.blur();
	iLastMousePos = mousePosition(e).y;
	staticOffset  = textarea.height() - iLastMousePos;
	textarea.css('opacity', 0.25);
	$(document).mousemove(performDrag).mouseup(endDrag);
	return false;
    }

    function performDrag(e) {
	var iThisMousePos = mousePosition(e).y;
	var iMousePos     = staticOffset + iThisMousePos;
	if ( iLastMousePos >= ( iThisMousePos ) ) {
	    iMousePos -= 5;
	}
	iLastMousePos = iThisMousePos;
	iMousePos     = Math.max(iMin, iMousePos);
	textarea.height( iMousePos + 'px' );
	if ( iMousePos < iMin ) {
	    endDrag(e);
	}
	return false;
    }
    
    function endDrag(e) {
	$(document).unbind('mousemove', performDrag).unbind('mouseup', endDrag);
	textarea.css('opacity', 1);
	textarea.focus();
	textarea      = null;
	staticOffset  = null;
	iLastMousePos = 0;
    }
    
    function mousePosition(e) {
	return { x: e.clientX + document.documentElement.scrollLeft, y: e.clientY + document.documentElement.scrollTop };
    };
})(jQuery);

