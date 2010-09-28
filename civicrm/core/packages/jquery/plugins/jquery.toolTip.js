
(function($){ $.fn.toolTip = function(){
  var clickedElement = null;
  return this.each(function() {
    var text = $(this).children().find('div.crm-help').html();
    if(text != undefined) {
      $(this).bind( 'click', function(e){
		$("#crm-toolTip").remove();
		if ( clickedElement == $(this).children().attr('id') ) { clickedElement = null; return; }
		 $("body").append('<div id="crm-toolTip" style="z-index: 100;"><div id="hide-tooltip" class="ui-icon ui-icon-close"></div>' + text + "</div>");
		  if ($.browser.msie && $.browser.version.substr(0,1)<7) {
		  	$("#crm-toolTip").css('position','absolute');
			$(window).bind('scroll', function() {
				var windowheight = $(window).height();
				var toolTipBottom = $(window).scrollTop() + 30;
				var posFromTop = windowheight+toolTipBottom;
				$("#crm-toolTip").css("top", toolTipBottom + "px");
				});
			};
		
		  $("#crm-toolTip").fadeIn("medium");
		  clickedElement = cj(this).children().attr('id');
	      })
	      .bind( 'mouseout', function() {
			$('#hide-tooltip').click( function() {
			  $("#crm-toolTip").hide();
			});
	     });
    	}
  	});
}})(jQuery);

