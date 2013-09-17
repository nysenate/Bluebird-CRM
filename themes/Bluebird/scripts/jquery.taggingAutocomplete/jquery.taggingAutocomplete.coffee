# Name:    jquery.taggingAutocomplete.js
# By:      Dan Pozzie
# Updated: 18/5/2013
# Version: 0.1.0
# Purpose: Hook JQuery UI Autocomplete for tagging usages in a
#          coffeescript environment, while moving the token-input
#          aspect from civicrm's core jquery.tokeninput.js. Instead
#          of hooking and modifying it out a lot of additional
#          functionality that isn't in tokeninput's scope, leverage
#          jquery ui's AC to do the following:
#          

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
    menuElement: ".JSTree-menu"
    source: ""
    minLength: 3
    # disabled: true
    delay: 300

  _jqcache =
    samplequery: ""

  _tags =
    input: "autocomplete-input"

  # public methods - must return $this for chaining
  methods =
    init: (options) ->
      $this = $(@)
      $.extend _settings, (options or {})
      _internals.enableAC()

    # This method is often overlooked.
    kill: (note) ->
      console.log "Killed with: #{note}"
      # Do anything to clean it up (nullify references, unbind events…).
      return $this
    exec: (search, event) ->
      search.exec(event)  
    
  # private
  _internals =
    enableAC: () ->
      @turnDataLocation()
      @formatInput()
      @acSearch = new Search

    turnDataLocation: ()->
      if _settings.jqDataReference?
        _settings.source = $(_settings.jqDataReference).data("autocomplete")
      # else if _settings.ajaxLocation?
        # return _settings.ajaxLocation
      else
        methods.kill "No Data Location"
    formatInput: ()->
      _jqcache["input"] = $("#{_settings.textBoxLocation}")
      _jqcache.input.addClass "#{_settings.theme}-#{_tags.input}"
      _jqcache["menu"] = $("#{_settings.menuElement}")


  class Search
    constructor: () ->
      @element = _jqcache["input"]
      @menu = _jqcache["menu"]
      @searchIndex = 0
      @source = [{term: "", tags:_settings.source}]
      @rebuildswitch = true
      # @start()
      # @source = (req,res) ->
        # console.log "filter:", req, res
        # res (@_filter @array, req.term)
    exec: (event, cb) ->
      @query = @element.val()
      @delay event , ((toret) =>
        cb(toret)
      )
    delay: (event, cb) ->
      clearTimeout @searching
      @searching = setTimeout ( =>
        @toret = @validate event if @query != @element.val()
        cb(@toret)
      ), _settings.delay
      
    validate: (event) ->
      if value? 
        value = value 
      else
        value = @element.val()
      if value.length < _settings.minLength
        return {}
      return @search(value)

    search: (term) ->
      if term.indexOf(@source[@searchIndex].term) != -1
        if term.length <= @source[@searchIndex].term.length
          @rebuildTag @searchIndex
      else
        @rebuildTag @searchIndex
      currentArray = @ifNullArray @source[@searchIndex].tags,@searchIndex
      @searchIndex++
      arrayToFill =
        tags: []
        term: ""
      @source[@searchIndex] = {}
      cachedQuery = @checkCache term
      
      if cachedQuery.length
        arrayToFill["term"] = cachedQuery[0].term
        arrayToFill["tags"] = cachedQuery[0].tags
        arrayToFill["cached"] = true
      else
        arrayToFill["term"] = term
        arrayToFill["tags"] = @filter currentArray, term
      return @source[@searchIndex] = arrayToFill
    filter: (array, term) ->
      matcher = new RegExp( @escapeRegex(term), "i" );
      return $.grep array, (value) =>
        return matcher.test value.name or value.id or value
    escapeRegex: (value) ->
      return value.replace /[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&"
    checkCache: (term) ->
      return $.grep @source, (query) =>
        return term == query.term
    rebuildTag: (index) ->
      @source[index] = {}
      @source[index].tags = @source[0].tags
    # don't entirely know if these are necessary?
    ifNullArray: (array, index) ->
      if array?
        return array if array.length > 0
      return @checkPrevArray index
    checkPrevArray: (index) ->
      if index > 0
        index--
        return @ifNullArray @source[index].tags, index
      else
        return @rebuildTag @searchIndex



 
  # Namespacing
  $.fn.tagACInput = (method) ->
    if methods[method]
      methods[method].apply this, Array::slice.call(arguments, 1)
    else if typeof method is "object" or not method
      methods.init.apply this, arguments
    else
      $.error "Method " + method + " does not exist on jquery.tagACInput"
) cj, window, document