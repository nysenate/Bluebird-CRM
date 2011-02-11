/*(function() {
	
	var BrowserDetect = {
		init: function () {
			this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
			this.version = this.searchVersion(navigator.userAgent)
				|| this.searchVersion(navigator.appVersion)
				|| "an unknown version";
			this.OS = this.searchString(this.dataOS) || "an unknown OS";
		},
		searchString: function (data) {
			for (var i=0;i<data.length;i++)	{
				var dataString = data[i].string;
				var dataProp = data[i].prop;
				this.versionSearchString = data[i].versionSearch || data[i].identity;
				if (dataString) {
					if (dataString.indexOf(data[i].subString) != -1)
						return data[i].identity;
				}
				else if (dataProp)
					return data[i].identity;
			}
		},
		searchVersion: function (dataString) {
			var index = dataString.indexOf(this.versionSearchString);
			if (index == -1) return;
			return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
		},
		dataBrowser: [
			{
				string: navigator.userAgent,
				subString: "Chrome",
				identity: "Chrome"
			},
			{ 	string: navigator.userAgent,
				subString: "OmniWeb",
				versionSearch: "OmniWeb/",
				identity: "OmniWeb"
			},
			{
				string: navigator.vendor,
				subString: "Apple",
				identity: "Safari",
				versionSearch: "Version"
			},
			{
				prop: window.opera,
				identity: "Opera"
			},
			{
				string: navigator.vendor,
				subString: "iCab",
				identity: "iCab"
			},
			{
				string: navigator.vendor,
				subString: "KDE",
				identity: "Konqueror"
			},
			{
				string: navigator.userAgent,
				subString: "Firefox",
				identity: "Firefox"
			},
			{
				string: navigator.vendor,
				subString: "Camino",
				identity: "Camino"
			},
			{		// for newer Netscapes (6+)
				string: navigator.userAgent,
				subString: "Netscape",
				identity: "Netscape"
			},
			{
				string: navigator.userAgent,
				subString: "MSIE",
				identity: "Explorer",
				versionSearch: "MSIE"
			},
			{
				string: navigator.userAgent,
				subString: "Gecko",
				identity: "Mozilla",
				versionSearch: "rv"
			},
			{ 		// for older Netscapes (4-)
				string: navigator.userAgent,
				subString: "Mozilla",
				identity: "Netscape",
				versionSearch: "Mozilla"
			}
		],
		dataOS : [
			{
				string: navigator.platform,
				subString: "Win",
				identity: "Windows"
			},
			{
				string: navigator.platform,
				subString: "Mac",
				identity: "Mac"
			},
			{
				string: navigator.userAgent,
				subString: "iPhone",
				identity: "iPhone/iPod"
		    },
			{
				string: navigator.platform,
				subString: "Linux",
				identity: "Linux"
			}
		]
	
	};
	
	BrowserDetect.init();
	
	window.$.client = { os : BrowserDetect.OS, browser : BrowserDetect.browser };
	
})();*/



/**
 * jQuery.FormNavigate.js
 * jQuery Form onChange Navigate Confirmation plugin
 * Browser Compatibility : IE 6.0, 7.0, 8.0; Firefox 2.0+;  Safari 3+; Opera 9+; Chrome 1+;
 *
 * Copyright (c) 2009 Law Ding Yong
 * 
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * See the file license.txt for copying permission.
 */
 
 /**
  * Documentation :
  * ==================
  *
  * How to Use:
  * $("YourForm").FormNavigate("YourMessage");
  *  -- "YourForm" as Your Form ID $("#form") or any method to grab your form
  *  -- "YourMessage" as Your onBeforeUnload Prompt Message Here
  *
  * This plugin handles onchange of input type of text, textarea, password, radio, checkbox, select and file to toggle on and off of window.onbeforeunload event.
  * Users are able to configure the custom onBeforeUnload message.
  */
var global_formNavigate = true;		// Js Global Variable for onChange Flag
(function($){
    $.fn.FormNavigate = function(message) {
        
        
        // NYSS kj - if windows XP & ie8, don't use onbeforeunload: too buggy 
        /*var ua = navigator.userAgent;
        if (!ua.match(/Win(dows )?(NT 5\.1|XP)/) || ($.browser.version != '8.0')) {*/
	       window.onbeforeunload = confirmExit;
        //}
        
        function confirmExit( event ) {  
            if (global_formNavigate == true) {  event.cancelBubble = true;  }  else  { return message;  }
        }
        $(this+ ":input[type=text], :input[type='textarea'], :input[type='password'], :input[type='radio'], :input[type='checkbox'], :input[type='file'], select").change(function(){
            global_formNavigate = false;
        });
		//to handle back button
		$(this+ ":input[type='textarea']").keyup(function(){ 
			global_formNavigate = false; 
		}); 
        $(this+ ":submit").click(function(){
            global_formNavigate = true;
        });
        $(".token-input-list-facebook").bind( "DOMNodeRemoved DOMNodeInserted", function(){ 
            global_formNavigate = false; 
        });
    }
})(jQuery);