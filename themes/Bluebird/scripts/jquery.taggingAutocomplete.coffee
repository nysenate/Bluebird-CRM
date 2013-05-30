###
# Name:    jquery.taggingAutocomplete.js
# By:      Dan Pozzie
# Updated: 18/5/2013
# Purpose: Hook JQuery UI Autocomplete for tagging usages in a
#          coffeescript environment, while moving the token-input
#          aspect from civicrm's core jquery.tokeninput.js. Instead
#          of hooking and modifying it out a lot of additional
#          functionality that isn't in tokeninput's scope, leverage
#          jquery ui's AC to do the following:
#          
###

(($, window, document) ->
  # Prepare your internal $this reference.
  $this = undefined
 
  _settings =
    # with dataReference pass #JSTree-data
    # cj("#JSTree-data").data("autocomplete" : @instance.getAutocomplete())
    jqDataReference: ""
    hintText: "Type in a partial or complete name of an tag or keyword."
    theme: "JSTree"
    ajaxLocation: ""
    textBoxLocation: "#JSTree-ac"
    menuElement: ".JSTree-me"
    source: ""
    minLength: 3
    # disabled: true
    delay: 100
    
  # You *may* rely on internal, private objects:
  _flag = false
  _cache = {} 
  acSettings =
    
  
  # This is your public API (no leading underscore, see?)
  # All public methods must return $this so your plugin is chainable.
  methods =
    init: (options) ->
      $this = $(@)
      $.extend _settings, (options or {})
      _internals.enableAC()
      return $this
    # This method is often overlooked.
    kill: (note) ->
      console.log "Killed with: #{note}"
      # Do anything to clean it up (nullify references, unbind events…).
      return $this
 
  # This is your private API. Most of your plugin code should go there.
  # The name "_internals" is by no mean mandatory: pick something you like, don't
  # forget the leading underscore so that the code is self-documented.
  # Those methods do not need to return $this. You may either have them working
  # by side-effects (modifying internal objects, see above) or, in a more
  # functionnal style, pass all required arguments and return a new object.
  # You can access the …settings, or other private methods using …internals.method,
  # as expected.
  _internals =
    enableAC: () ->
      @turnDataLocation()
      @selectedItem = null
      @element = $("#{_settings.textBoxLocation}")
      @menuelement = $("#{_settings.menuElement}")
      @checkValue()
    checkValue: () ->
      @element.on "keydown", (event) =>
        @query = @element.val()
        @checkKeycodes(event)


    checkKeycodes: (event) ->
      # switch event.keyCode
      #   when keyCode.PAGE_UP
      #     @._move "previousPage", event
      #   when keyCode.PAGE_DOWN
      #     @._move "nextPage", event
      #   when keyCode.UP
      #     @._keyEvent "previous", event
      #   when keyCode.DOWN
      #     @._keyEvent "next", event
      #   when keyCode.ENTER || keyCode.NUMPAD_ENTER
      #     if @menu.active
      #       @suppressKeyPress = true
      #     if !@.menu.active
      #       return
      #     @menu.select event
      #   when keyCode.TAB
      #     if !@.menu.active
      #       return
      #     @menu.select event
      #   when keyCode.ESCAPE
      #     @element.val @term
      #     @close event
      #   else
      clearTimeout @searching
      @searching = setTimeout ( =>
        if @query != @element.val()
          @search null,event

      ), _settings.delay
    search: (value,event) ->
      if value? 
        value = value 
      else
        value = @element.val()
      console.log value, @element.val()
      @term = @element.val();

      if value.length < _settings.minLength
        return event
      # do search here
      console.log "value: #{value.length}"
      console.log "minLength: #{_settings.minLength}"


    _search: (value) ->
      @pending++
      @source { term : value }


    turnDataLocation: ()->
      if _settings.jqDataReference?
        _settings.source = cj(_settings.jqDataReference).data("autocomplete")
      # else if _settings.ajaxLocation?
        # return _settings.ajaxLocation
      else
        methods.kill "No Data Location"

    # this toggles our "global" yet internal flag:
    toggleFlag: ->
      _flag = !_flag

    # This one does not alter anything: it requires parameters (to be documented)
    # and then it returns something based on those params. Use case (for instance):
    #
    #  state = _internals.computeSomething(_anotherState || false, _flag)
    #
    computeSomething: (state, flag) ->
      flag ? state : "No, that's not right."
 
  # Namespacing
  $.fn.tagACInput = (method) ->
    if methods[method]
      methods[method].apply this, Array::slice.call(arguments, 1)
    else if typeof method is "object" or not method
      methods.init.apply this, arguments
    else
      $.error "Method " + method + " does not exist on jquery.tagACInput"
) cj, window, document