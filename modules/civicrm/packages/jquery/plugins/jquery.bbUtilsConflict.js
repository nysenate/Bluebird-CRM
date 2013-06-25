(function() {
  Function.prototype.bbclone = function() {
    var that = this;
    var temp = function temporary() { return that.apply(this, arguments); };
    for( key in this ) {
        temp[key] = this[key];
    }
    return temp;
  };
  var bbUtils = $.bbclone();
  window["bbUtils"] = bbUtils;
  delete Function.prototype.bbclone;
})(window, jQuery);
