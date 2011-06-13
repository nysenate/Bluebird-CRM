  		$(document).ready(function() {
  			var loading_page = false;
  	  		var paging_cache = {};
  	  		var body_cache = {};
			var app_data = {
				'data_url' : '/crm/imap/imap.php?function=',
				'page_idx' : 0,
				'page_size': 5
			};
			var tmpl_tools = {
				'get_name' : 
					function(record) {
						return record.personal ? record.personal : record.mailbox;
					},
				'get_long_name' :
					function(record) { 
						return (record.personal ? record.personal : "(no name)") 
							+ " &lt;" + record.mailbox + "@" + record.host + "&gt; ";
					},
				'date' : getDate
			};
			
			function writePage() {
				var tmpl_data = {"data":paging_cache[app_data.page_idx], 'tools' : tmpl_tools};
				$('#left_container').html(tmpl("tmpl_email_headers", tmpl_data));
				
				if(!paging_cache[app_data.page_idx+1]) {
					getPage(app_data.page_idx+1);
				}
			}

			function writeEmailBody(cached_body, params) {
  				var tmpl_data = {
  					'data' : paging_cache[app_data.page_idx][params.uid],
  					'body' : cached_body,
  					'tools' : tmpl_tools
  				};
  	  	  			
  				$(tmpl("tmpl_email_long",tmpl_data)).prependTo('#main').hide();
  				
  				doPlaceHolding();
  				
  				$('#email_long').slideDown(function() {
  	  				 $('#email_long_html_body').css(
  	  		  				 'height',$('#email_long_close').position().top - $('#email_long_html_body').position().top - 7);
  				});
  			}
			
			function getPage(page_idx) {
				var params = {'page_idx':page_idx};
				loading_page = true;
				getJSON(
						app_data.data_url + "get_emails" 
							+ "&page_idx=" + page_idx
							+ "&page_size=" + app_data.page_size,
						[cachePage], params);
			}

			function cachePage(data, params) {
				paging_cache[params.page_idx] = {};
				
				for(i in data) {
					paging_cache[params.page_idx][data[i].uid] = data[i];
				}
				loading_page = false;
			}

			function cacheBody(data, params) {
				body_cache[params.uid] = data;
				writeEmailBody(data, params);
  			}
			
			function init() {
				if(paging_cache[app_data.page_idx]) {
					writePage();
				}
				else {
					var params = {'page_idx':app_data.page_idx};
					getJSON(
							app_data.data_url + "get_emails" 
								+ "&page_idx=" + app_data.page_idx
								+ "&page_size=" + app_data.page_size,
							[cachePage, writePage], params);
				}
			};
			
			function doPlaceHolding() {
  				copyDimensions($('#left_container'), $('#email_long'),
						{'top':20, 'height':-30, 'width':-2});
  				copyDimensions($('#left_container'),$('#expand_left_container'),
  	  					{'top':5, 'height':-10,'width':12});
  				if($('#email_long').length != 0) {
	  					$('#email_long_html_body').css(
		  		  				 'height',
		  		  				 $('#email_long_close').position().top 
		  		  				 	- $('#email_long_html_body').position().top - 7);
				}
  			}
  	  		
  			function getJSON(url, callbacks, params) {
  				$.getJSON(url, function(data) {
  	  				for(i in callbacks) {
						callbacks[i](data, params);
  	  				}
  				});
  			};

  			function copyDimensions(copyFrom, copyTo, params) {
				var position = copyFrom.position();
				copyTo.css({
					'top' 		: position.top + (params.top ? params.top : 0),
					'left' 		: position.left + (params.left ? params.left : 0),
					'height'	: copyFrom.height() + (params.height ? params.height : 0),
					'width'		: copyFrom.width() + (params.width ? params.width : 0)
				});
  			};

  			function getDate(epochTime) {
  				if(epochTime<10000000000) epochTime *= 1000;
  				
  				var time = parseInt(epochTime);
  				var date = new Date();
  				date.setTime(time);

  				var month = date.getMonth() + 1;
  				var day = date.getDate();
  				var year = date.getFullYear();
  				var hours = date.getHours();
  				var minutes = date.getMinutes();
  				var seconds = date.getSeconds();
  				
  				minutes = minutes < 10 ? "0" + minutes:minutes;
  				seconds = seconds < 10 ? "0" + seconds:seconds;
  				return month + "/" + day + "/" + year + " at " + hours + ":" + minutes + ":" + seconds;
  			}

  			$("body").delegate("#page_right", "click", function(){
  	  			//TODO must halt if last page
  				if(!loading_page || paging_cache[app_data.page_idx+1]) {
  					app_data.page_idx++;
  	  	  			init();
  				}
  			});
  			
  			$("body").delegate("#page_left", "click", function(){
  	  			if(app_data.page_idx > 0) {
  	  				app_data.page_idx--;
  	  				init();
  	  			}
  			});
  			
  			$("body").delegate(".email_summary_container", "click", function() {
  	  			var uid = $(this).attr('id');
  	  			var params = {"uid" : uid};
  	  			if(body_cache[uid]) {
  	  				writeEmailBody(body_cache[uid], params);
  	  			}
  	  			else {
  	  				getJSON(
						app_data.data_url + "get_body" 
							+ "&uid=" + uid,
						[cacheBody], params);
  	  			}
  			});

  			$('body').delegate('#email_long_close', 'click', function() {
  				$('#email_long').slideUp();
  				$('#main').remove('#email_long');
  			});
  			
  			$('body').delegate('#expand_left_container', 'click', function() {
  				if($('#left_container').attr("big")) {
  					$('#left_container').removeAttr("big");
  					$('#left_container').animate({ 'width':'42%' },
  							500, function() { doPlaceHolding(); });
  				}
  				else {
  					$('#left_container').attr("big","true");
  					$('#left_container').animate({ 'width':'70%' },
  							500, function() { doPlaceHolding(); });
  				}
  			});

  			window.onresize = function() {
  				doPlaceHolding();
  			};
  			
  			doPlaceHolding();
  			init();
  		});