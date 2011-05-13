
$(document).ready(function(){
	var base_url = "/backupData?function=";
	var cur_action = null;

	function write_files(data) {
		assign_undelegates()
		
		$('.nyss_backup_action').remove();
		$('.nyss_backup_confirm').remove();
		
		var tmpl_data = {"data":data, "util":util};
		$('.main-container')
			.html(tmpl("tmpl_instance_contents", tmpl_data))
			.css('min-height','200px');
		
		$('#blocker, #progress_container').hide();
		
		assign_delegates();
	}
	
	function init(data) {
		if(data) {
			write_files(data);
		}
		else {
			util.getJSON(base_url + "instance_file_list", [init]);
		}
	}
	
	function finishRequest(data) {
		$('#progress_container').html(tmpl("tmpl_success_" + data.success));
		
		window.setTimeout(function() {
			init();
		}, 2000);
	}
	
	function assign_undelegates() {
		$('body').undelegate('.nyss_backup_action', 'click');
		$('body').undelegate('.nyss_backup_confirm', 'click');
	}
	
	function assign_delegates() {
		$('body').delegate('.nyss_backup_action', 'click', function() {
			var action = $(this).attr('action');
			if(action == "null")
	  			return;
			
			var file = $(this).siblings('.instance_file_name').html();
			
			cur_action = {'action':action,'file':file};
			
			$('#progress_container')
				.html(tmpl('tmpl_' + action, {'file':file}))
				.append(tmpl('tmpl_confirmation'))
				.fadeIn();
			
			$('#blocker').fadeIn();
		});
		
		$('body').delegate('.nyss_backup_confirm', 'click', function() {
			var action = $(this).attr('action');
			if(action == "null")
	  			return;
			
			if(action == "confirm" && cur_action.action) {
				$('#progress_container').html(tmpl("tmpl_progress"));
				util.getJSON(base_url 
						+ cur_action.action 
						+ (cur_action.file ? "&file=" + cur_action.file : ""),
					[finishRequest]);
			}
			else {
				cur_action = null;
				$('#blocker').fadeOut();
				$('#progress_container').fadeOut();
			}
		});
	}
		
	init();
});

(function() {
	this.util = { 
		'getDate': function (epochTime) {
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
		},
		'getJSON': function(url, callbacks, params) {
			$.getJSON(url, function(data) {
  				for(i in callbacks) {
  					callbacks[i](data, params);
  				}
			});
		}
	}
	//set timeout to an hour
	$.ajaxSetup({
		timeout: 3600000
	});
})();