;(function($,document,window,undefined) {
  $(document).ready(function(){
    var AH = AJAXHelper, logcount=0, DataHandlers = {},
        body_types = ['text','multipart','message','application','audio','image','video','other'],
        encodings = ['7BIT','8BIT','BINARY','BASE64','QUOTED-PRINTABLE','OTHER']
        ;


    /* ***********************************
       Work functions
    *********************************** */
    function debug(m) {
      var ln = $('<span />').addClass('debug-ln').html(++logcount+':'),
          t  = $('<div />').addClass('debug-msg').html(ln).append(m);
      $('#debug-output').append(t);
    }

    /* Build the common values object for AJAX calls */
    function buildCommonValues(opts) {
      var o = {};
      $('select,input,checkbox').each(function(k,v){
        var $v = $(v),
            n=(v.tagName + ($v.attr('type') || '')).toUpperCase(),
            vv = null;
        switch(n){
          case 'SELECT':
          case 'INPUT':
          case 'INPUTTEXT':
            vv = $v.val(); break;
          case 'INPUTCHECKBOX':
            vv = $v.is(':checked'); break;
          default: vv=null; break;
        }
        if (vv) { o[$v.attr('name')] = vv; }
      });
      return $.extend(o, opts);
    }

    /* Execute a selected command (AJAX) */
    function doCommand(opts) {
      AH.doAjax('doCommand', { data:$.param(buildCommonValues(opts)), complete: [hnd_doCommand] });
    }

    /* handle the AJAX response for a selected command */
    function hnd_doCommand(resp) {
      var r = resp.responseJSON;
      if (r.resultcode) {
        debug("AJAX result="+r.result+"("+r.resultcode+") "+r.message);
      } else {
        if (typeof DataHandlers[r.req] == 'function') {
          DataHandlers[r.req](r);
        }
      }
    }


    /* ***********************************
       AJAX Handler functions
    *********************************** */
    /* handle the AJAX return for folder status */
    DataHandlers.getFolderStatus = function(r) {
      $('#folder-overview').empty()
                           .append($('<div/>').html('Message Count: '+r.data.messages))
                           .append($('<div/>').html('Unseen Count: '+r.data.unseen))
    }

    /* handle the AJAX return for list folders */
    DataHandlers.listFolders = function(r) {
      var $list = $('#folder-request').empty();
      debug('Found folders: ' + r.data.length);
      if (r.data.length) {
        $list.append( $('<option />').attr('value','').text('Select a folder') );
        r.data.forEach(function(v,k){
          $list.append( $('<option />').attr('value',v).text(v) );
        });
      } else {
        $list.append( $('<option />').attr('value','').text('No folders found  ') );
      }
    }

    /* handle the AJAX response for loading known instances */
    DataHandlers.loadInstances = function(r) {
      var $e = $('#instance-account');
      if (r.errorcount) {
        debug("AJAX result="+r.result+"("+r.errorcount+" errors)");
        r.errors.forEach(function(v,k){
          debug("Err"+(++k)+":"+v);
        });
      } else {
        $e.empty().append( $("<option />").attr("value",'').text('Select one . . .') );
        debug("loadInstanceConfigs returned " + r.data.length + " instances");
        r.data.forEach(function(v,k){
          $e.append( $('<option />').attr("value",v).text(v) );
          debug("&nbsp;&nbsp;--> Added instance " + v);
        });
      }
    }

    /* handle the AJAX response for fetching a message */
    DataHandlers.showMsg = function(r) {
      var d = r.data,
          bld = $('<div/>');
      bld.append($('<div/>').attr('id','message-byte-length').text('Length: '+d.overview.bytes))
         .append($('<div/>').attr('id','message-type').text('MIME Type: '+body_types[d.overview.type] + ' '+(d.overview.subtype || '')))
         .append($('<div/>').attr('id','message-encoding').text('Encoding: '+encodings[d.overview.encoding]));
      if (d.overview.ifparameters) {
        var params = $('<div/>').attr('id','message-parameters');
        d.overview.parameters.forEach(function(v,k){
          params.append($('<div/>').addClass('message-param').text(v.attribute+' = '+v.value));
        });
        bld.append(params);
      }
      $('#results-message-rfc822-content').text(d.rfc822.replace(/\n/g,"\\n\n"));
      $('#results-message-overview-content').empty().append(bld);
      $('#results-message-headers-content').text(JSON.stringify(d.headers, null, 2));
      $('#results-message-parsed-content').text(JSON.stringify(d.meta, null, 2));
      $('#results-possible-senders-content').text(JSON.stringify(d.senders, null, 2));
      $('#column-right .fillable').show();
    }

    /* ***********************************
       Hooks
    *********************************** */
    /* hook to refresh instance config list */
    $('#instance-account-refresh').click(function(e){
      doCommand({req:'loadInstances'});
    });

    /* hook to show/hide custom server properties */
    $('#use-custom-server').click(function(e){
      $('#account-information').toggle(this.checked);
    });

    /* hook to clear debug output */
    $('#debug-box-flush').click(function(e){
      $('#debug-output').empty();
      logcount=0;
    });

    /* hook to open/close debug box */
    $('#debug-box-close-handle,#debug-box-open-handle').click(function(e){
      $("#debug-box").toggle();
    });

    /* hook to execute selected command */
    $('#execute-command').click(function(e){
      var v = $('#command-request').val();
      if (v) {
        doCommand({req:v});
      }
    });

    /* hook to show/hide raw IMAP return */
    $('.collapsible .toggle-button').click(function(e){
      $(this).closest('.collapsible').children('pre').toggle();
    });

    /* hook to show/hide folder selector */
    $('#instance-account').change(function(e) {
      var v = $(this).val();
      $('#folder-selection').toggle(Boolean(v));
      if (v) {
        doCommand({req:'listFolders'});
      }
    });

    /* hook to populate & show/hide folder selector */
    $('#folder-request').change(function(e){
      var v = $(this).val();
      $('#command-selection').toggle(Boolean(v));
      if (v) {
        doCommand({req:'getFolderStatus'});
      }
    });

    /* hook to show/hide command groups */
    $('#command-request').change(function(e){
      var v = $(this).val();
      $('.command-group').toggle(Boolean(v));
      $('#execute-command').toggle(Boolean(v));
      if (v) {
        $('#command-group-'+v).show();
      }
    });

    /* hook to increment/decrement message counter */
    $('.showMsg-counter').click(function(e){
      var c = parseInt($('#showMsg-number').val()) || 0;
      if (this.id == "showMsg-next") {
        c++;
      } else {
        c--;
      }
      if (c<1) { c = 1; }
      $('#showMsg-number').val(c);
      $('#execute-command').click();
    })


    /* ***********************************
       Page initialization necessities
    *********************************** */
    /* load the instance config */
    $('#instance-account-refresh').click();

    /* make debug box draggable */
    $('#debug-box').draggable();

  });
})(jQuery,document,window);
