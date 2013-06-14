(function() {
  jqfunc = jQuery.extend({},jQuery);
  utils = function Utils(){
    this.version = "0.0.1";
  };
  jQuery.each(jqfunc, function(k,v){
    utils.prototype[k] = v;
  });
  window["bbUtils"] = new utils();
}).apply(this, jQuery);
