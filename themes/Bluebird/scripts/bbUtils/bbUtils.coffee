#
# Bluebird Browser utility Belt
# Standard Methods to Do Common Tasks
# Created by: Dan Pozzie, NY Senate
# Last Update: 2013-06-13
# Language: Coffeescript
# Requires: jquery.1.7.2 via a "cj" jquery namespace
# Version: 0.0.1
#
#
bbUtils= 
  # LocalStorage
  # Usage:
  # Key = uniqueIdentifier for locally stored data
  # Value = JSON Object, mostly. LocalStorage stores KV pairs, but not
  # nested KV pairs. Meaning, you have to JSON parse and flatten the data
  # and parse it out to get it back. 
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
  isJsonString: (str) ->
    try
      JSON.parse str
    catch e
      return false
    return true
  version: ->
    return _settings.version

_settings=
  version: "0.0.1"

# opening the namespace
window.bbUtils ?= bbUtils