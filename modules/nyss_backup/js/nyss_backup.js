jQuery(document).ready(function() {
  var base_url = "/backupdata?function=";
  var cur_action = null;

  function write_files(data) {
    assign_undelegates();

    $('.nyss_backup_action').remove();
    $('.nyss_backup_confirm').remove();

    var tmpl_data = {"data":data, "util":util};
    $('.main-container')
      .html(tmpl("tmpl_instance_contents", tmpl_data));

    $('#blocker, #progress_container').hide();

    assign_delegates();
  }

  function init(data) {
    if (data) {
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
    $('input').css('vertical-align','baseline');

    $('body').delegate('.nyss_backup_action', 'click', function() {
      var action = $(this).attr('action');
      if (action == "null") {
        return;
      }

      var file = $(this)
        .parent()
        .prev()
        .children('.instance_file_name')
        .html();

      cur_action = {'action':action,'file':file};

      $('#progress_container')
        .html(tmpl('tmpl_' + action, {'file':file, "util":util}))
        .append(tmpl('tmpl_confirmation'))
        .fadeIn();

      $('#blocker').fadeIn();
    });

    $('body').delegate('.nyss_backup_confirm', 'click', function() {
      var action = $(this).attr('action');
      if (action == "null") {
        return;
      }

      if (action == "confirm" && cur_action.action) {
        var file_time_elem = $('#file_time');
        var file_time = file_time_elem.size() == 0 ? null : file_time_elem.val();
        var file_name_elem = $('#file_name');
        var file_name = file_name_elem.size() == 0 ? null : file_name_elem.val();

        $('#progress_container').html(tmpl("tmpl_progress"));

        util.getJSON(base_url
            + cur_action.action
            + (cur_action.file ? "&file=" + cur_action.file : "")
            + (file_name ? "&file_name=" + file_name : "")
            + (file_time ? "&file_time=" + file_time : ""),
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
  function browser() {
    var ie = (function() {
      if (navigator.appVersion.indexOf("MSIE") != -1) {
        return true;
      }
      return false;
    })();
    this.getIe = function() {
      return ie
    }
    this.getLeftWidth = function() {
      return (ie ? '64%' : '72%');
    }
    this.getRightWidth = function() {
      return (ie ? '36%' : '28%');
    }
  }
  this.util = {
    'getFileNameDate': function() {
      var date = new Date();
      var ret = "";
      ret += date.getFullYear();
      ret += (date.getMonth() < 9 ? "0" : "") + (date.getMonth() + 1);
      ret += (date.getDate() < 10 ? "0" : "") + date.getDate();
      ret +=  "-" + (date.getHours() < 9 ? "0" : "") + date.getHours();
      ret += (date.getMinutes() < 9 ? "0" : "") + date.getMinutes();
      ret += (date.getSeconds() < 9 ? "0" : "") + date.getSeconds();
      return {'str':ret, 'int':Math.floor(date.getTime() / 1000)};
    },
    'getDate': function(epochTime) {
      if (epochTime < 10000000000) {
        epochTime *= 1000;
      }

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
          for (i in callbacks) {
            callbacks[i](data, params);
          }
      });
    },
    'browser': new browser()
  }

  //set timeout to an hour
  jQuery.ajaxSetup({timeout: 3600000});
})();
