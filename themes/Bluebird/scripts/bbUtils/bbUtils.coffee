#
# Bluebird Browser utility Belt
# Standard Methods to Do Common Tasks
# Created by: Dan Pozzie, NY Senate
# Last Update: 2013-06-13
# Language: Coffeescript
# Requires: jquery.1.7.2
# Is a: specialized jQuery plugin suite that doesn't have the same plugins & dependencies that CJ does
# Version: 0.0.1
#
#

# init of the namespace & cloning jQuery's bare object
window["bbUtils"] = jQuery.noConflict()

(($, window, document) ->
  # for chaining
  $this = undefined

  # data objects

  _settings=
    version: "0.0.1"

  # public methods - must return $this for chaining
  methods =
    init: () ->
      console.log "jQuery: #{$.fn.jquery}, bbUtils: #{_settings.version}"
    localStorage: (options) ->

    isJsonString: (options) ->

    rc4: (options) ->

    version: ->
      
    # init: (options) ->
    #   $this = $(@)
    #   $.extend _settings, (options or {})
    #   _internals.enableAC()

    # # This method is often overlooked.
    # kill: (note) ->
    #   console.log "Killed with: #{note}"
    #   # Do anything to clean it up (nullify references, unbind events…).
    #   return $this
    # exec: (search, event) ->
    #   search.exec(event)  

  # core object
  class bbUtils
    # LocalStorage
    # Usage:
    # Key = uniqueIdentifier for locally stored data
    # Value = JSON Object, mostly. LocalStorage stores KV pairs, but not
    # nested KV pairs. Meaning, you have to JSON parse and flatten the data
    # and parse it out to get it back. 
    # Returns: false if unable to use local storage, true if set, value if get
    # 
    # 
    localStorage: (key, value)->
      # your easy modernizr check if it's supported, we don't save to cookie
      # because it's not important to for unsupported browsers to have
      # this kind of functionality
      return false if !localStorage
      # setter
      if value?
        if @isJsonString(value)
          value = JSON.parse(value)
        else
        unparsedKV = localStorage.getItem(key)
        if !unparsedKV?
          unparsedKV = {}
        if @isJsonString(unparsedKV)
          parsedKV = JSON.parse(unparsedKV)
        else
          parsedKV = unparsedKV

        extended = cj.extend({}, parsedKV, value )
        localStorage.setItem(key, JSON.stringify(extended))
        return true
      # getter
      else
        return JSON.parse(localStorage.getItem(key))
    # isJsonString
    # returns bool
    isJsonString: (str) ->
      try
        JSON.parse str
      catch e
        return false
      return true
    rc4: (k,v) ->
      s = []
      j = 0
      res = ''
      s[i] = i for i in [0..256]
      for i in [0..256]
        j = (j + s[i + k.charCodeAt(i % k.length)]) % 256
        x = s[i]
        s[i] = s[j]
        s[j] = x
      i = 0
      j = 0
      for y in v.length
        i = (i+1) % 256
        j = (j + s[i]) % 256
        x = s[i]
        s[i] = s[j]
        s[j] = x
        res += String.fromCharCode(v.charCodeAt(y) ^ s[(s[i] + s[j]) % 256])
      res
    # returns version number.
  

  # Namespacing
  # $.fn = (method) -> 
  $.each methods, (method) ->
    console.log method
    $.fn["#{method}"] = ->
      if methods[method]
        methods[method].apply this, Array::slice.call(arguments, 1)
      else if typeof method is "object" or not method
        methods.init.apply this, arguments
      else
        $.error "Method #{method} doesn't" 

  ###$.fn.tagACInput = (method) ->
    if methods[method]
      methods[method].apply this, Array::slice.call(arguments, 1)
    else if typeof method is "object" or not method
      methods.init.apply this, arguments
    else
      $.error "Method " + method + " does not exist on jquery.tagACInput"###
) bbUtils, window, document

console.log bbUtils.version