/* global namespace */
var AJAXHelper = AJAXHelper || {};

(function ($,window,document,undefined) {
  /* easy reference */
  var D = AJAXHelper;

  /* common variables */
  // cache of jqXHR objects
  D.jqxhr = {};
  // default AJAX method
  D.ajax_method = "POST";
  // default AJAX URL
  D.ajax_url = '/scripts/IMAP/imap_reader_ajax.php';

  /* For debugging only.  Remove, or set to false for production */
  D.debug_logger = true;

  /* a console logger */
  D.log = function() {
    if (D.debug_logger && arguments.length) {
      for (var i=0; i<arguments.length; i++) { console.log(arguments[i]); }
    }
  }

  /* Simple management of AJAX calls
     This handler utilizes the request cache D.jqxhr, and formats an
     AJAX request to include fields required by the server.  It also
     sets a method (default POST), a default dataType, and replaces the
     .complete callback with a custom handler.  Any current assignment
     of .complete is cached.  All AJAX parameters except .complete can
     be overridden by passing an options object.

     n = name of this AJAX call
     o = custom options to override defaults, see jQuery's .ajax() options
     cb = a callback to add to the end of the .complete chain
     */
  D.doAjax = function(n,o,cb) {
    n = n || 'default';
    o = o || { type:'POST' };
    var t = t || (o.type ? o.type : D.ajax_method),
        allcb = o.complete ? ($.isArray(o.complete) ? o.complete : [ o.complete ]) : []
        ;
    if (D.jqxhr[n]) {
      try {
        D.jqxhr.abort();
      } catch(e) { };
      D.jqxhr[n] = null;
    }
    ( ($.isArray(cb)) ? cb : [cb] ).forEach(function(v,k){
      if (v) { allcb.push(v); }
    });
    var oo = $.extend( { type:t, dataType:'json', url:D.ajax_url },
                       o,
                       { complete:D.handlerDoAjax }
                     );
    D.jqxhr[n] = $.ajax(oo);
    D.jqxhr[n].userHandler = allcb;
  }

  /* In case an AJAX call fails

     m = a message to use in the alert
     */
  D.failAlert = function(m) {
    alert(m);
  }

  /* handler for general AJAX
     A custom AJAX return handler.  When an AJAX call is executed
     through D.doAjax(), this handler is called before any other
     handlers in the .complete property.  After error checking the
     response, any other cached handlers (.complete, followed by
     the custom callback, see D.doAjax) are called in order.

     The function signature is as required for $.ajax.complete.
     */
  D.handlerDoAjax = function(r,s) {
    var $this = this;
    D.log('===AJAX response JSON', r.responseJSON);
    if ((!r.responseJSON) || r.responseJSON.result) {
      D.log('AJAX call failed! (http='+s+")",
            "result=",
            r.responseJSON.result,
            "msg=",r.responseJSON.msg
            );
    }
    if (r.userHandler && r.userHandler.length) {
      r.userHandler.forEach(function(v,i){
        if (v && typeof(v) == 'function') { v.call($this, r, s); }
      });
    } else {
      D.log('---AJAX call has no custom handlers');
    }
  }

})(jQuery,window,document);