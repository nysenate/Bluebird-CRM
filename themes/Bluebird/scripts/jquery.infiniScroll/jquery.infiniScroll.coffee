# Name:    jquery.infiniScroll.js
# By:      Dan Pozzie
# Updated: 18/5/2013
# Version: 0.1.0
# Purpose: provide an ajax event, via callback, to a scroll down event
#          

(($, window, document) ->
  # Prepare your internal $this reference.
  $this = undefined
 
  _defaults =
    scrollBox: ".infiniScrollBox"
    box:
      height: 0
      percent: 99
      pixels: 0

  _cj = {}

  _options= {}

  _cb = ->

  # public methods - must return $this for chaining
  methods =
    init: (options, cb) ->
      _options = $.extend(_defaults, options)
      return console.log "Callback isn't a function" if typeof cb != "function"
      _cb = cb
      _internals.setCJ(@)
      _internals.matchBox()
    unbind: (ob) ->
      ob.off("scroll")

  _internals = 
    setCJ: (target) ->
      _cj.sb = cj(_options.scrollBox)
      _cj.loc = target

    matchBox: () ->
      @box = _options.box
      @box.height = _cj.loc.height()
      @box.viewHeight = _cj.sb.height()
      @box.limit = @findBottomLimit()
      @execScrollerHandler()

    execScrollerHandler: () ->
      dc = 0
      _cj.sb.scroll =>
        @box.currentPos = _cj.sb.scrollTop()
        if @box.limit <= @box.viewHeight + @box.currentPos
          if dc != 1
            dc = 1
            _cb()
            methods.unbind(_cj.sb)

    findBottomLimit: () ->
      if parseInt(@box.pixels) > 0
        return @box.height - parseInt(@box.pixels)
      Math.floor(@box.height*parseInt(@box.percent)/100)

  # Namespacing
  $.fn.infiniscroll = (method) ->
    if methods[method]
      methods[method].apply this, Array::slice.call(arguments, 1)
    else if typeof method is "object" or not method
      methods.init.apply this, arguments
    else
      $.error "Method " + method + " does not exist on jquery.infiniscroll"
) cj, window, document
