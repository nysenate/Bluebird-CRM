#
# Bluebird Browser utility Belt
# Standard Methods to Do Common Tasks
# Created by: Dan Pozzie, NY Senate
# Last Update: 2013-06-13
# Language: Coffeescript
# Requires: jquery.1.7.2
# Version: 0.0.1
#
#


# (($, window, document) ->
# moduleKeywords = ['extended', 'included'] 


# # core object
# class window.bb
#   @extend: (obj) ->
#     for key, value of obj when key not in moduleKeywords
#       @[key] = value

#     obj.extended?.apply(@)
#     this

#   @include: (obj) ->
#     for key, value of obj when key not in moduleKeywords
#       # Assign properties to the prototype
#       @::[key] = value

#     obj.included?.apply(@)
#     this



class window.bb
  # LocalStorage
  # Usage:
  # Key = uniqueIdentifier for locally stored data
  # Value = JSON Object, mostly. LocalStorage stores KV pairs, but not
  # nested KV pairs. Meaning, you have to JSON parse and flatten the data
  # and parse it out to get it back. 
  # Returns: false if unable to use local storage, true if set, value if get
  # 
  #

  _settings=
    version: "0.0.3" 
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
  debounce: (func, threshold, execAsap) ->
    timeout = null
    (args...) ->
      obj = this
      delayed = ->
        func.apply(obj, args) unless execAsap
        timeout = null
      if timeout
        clearTimeout(timeout)
      else if (execAsap)
        func.apply(obj, args)
      timeout = setTimeout delayed, threshold || 100
  throttle: (fn, delay) ->
    return fn if delay is 0
    timer = false
    return ->
      return if timer
      timer = true
      setTimeout (-> timer = false), delay unless delay is -1
      fn arguments...
  returnTime: (note = "") ->
    time = new Date()
    rTime = "#{time.getMinutes()}:#{time.getSeconds()}:#{time.getMilliseconds()}"
    if note.length > 0
      console.log(note)
    console.log(rTime)

  spaceTo: (type, str) ->
    return str.replace /\ /g,"_" if type == "underscore"
    return str.replace /\ /g,"-" if type == "dash"
    return str.replace /\ /g,"" 
  
  # returns a random string
  randomString: (length) ->
    b = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"
    a = ""
    for i in [1..length]
      a = "#{a}#{b.charAt(Math.floor(Math.random() * b.length))}"
    a


  keyCode: (evt) ->
    charCode = if evt.which? then evt.which else event.keyCode
    keyCode =
      "modifier": [16,17,18,19,20,91,92,93,144,145]
      "number": [96..105].concat [48..57]
      "directional": [9,13,27,33,34,35,36,37,38,39,40]
      "delete": [8,32,46]
      "insert": [45]
      "math": [106..111]
      "function": [112..123]
      "punctuation": [219..222].concat [186..192]
      "letters": [65..90]
    notHtmlSafe: []
    rData = {}
    for ktype,arr of keyCode
      if arr.indexOf(charCode) >= 0
        rData.type = ktype
    # console.log rData.type
    switch rData.type
      # non-directional, non-character
      when "modifier"
        switch charCode
          when 16 then rData.name = "shift"
          when 17 then rData.name = "ctrl"
          when 18 then rData.name = "alt"
          when 19 then rData.name = "pause/break"
          when 20 then rData.name = "caps lock"
          when 93 then rData.name = "select key"
          when 91 then rData.name = "left window"
          when 92 then rData.name = "right window"
          when 144 then rData.name = "num lock"
          when 145 then rData.name = "scroll lock"
        rData.char = false
      when "number"
        switch charCode
          when 48,96 then rData.name = "0"
          when 49,97 then rData.name = "1"
          when 50,98 then rData.name = "2"
          when 51,99 then rData.name = "3"
          when 52,100 then rData.name = "4"
          when 53,101 then rData.name = "5"
          when 54,102 then rData.name = "6"
          when 55,103 then rData.name = "7"
          when 56,104 then rData.name = "8"
          when 57,105 then rData.name = "9"
        rData.char = true
      when "directional"
        switch charCode
          when 9 then rData.name = "tab"
          when 13 then rData.name = "enter"
          when 27 then rData.name = "escape"
          when 33 then rData.name = "page up"
          when 34 then rData.name = "page down"
          when 35 then rData.name = "end"
          when 36 then rData.name = "home"
          when 37 then rData.name = "left arrow"
          when 38 then rData.name = "up arrow"
          when 39 then rData.name = "right arrow"
          when 40 then rData.name = "down arrow"
        rData.char = false
      when "delete"
        switch charCode
          when 8 then rData.name = "backspace"
          when 46 then rData.name = "delete"
          when 32 then rData.name = "space" 
        rData.char = false
      when "insert"
        rData.name = "insert"
        rData.char = false
      when "math"
        switch charCode
          when 106 then rData.name = "multiply"
          when 107 then rData.name = "add"
          when 109 then rData.name = "subtract"
          when 110 then rData.name = "decimal point"
          when 111 then rData.name = "divide"
        rData.char = false
      when "function"
        switch charCode
          when 112 then rData.name = "F1"
          when 113 then rData.name = "F2"
          when 114 then rData.name = "F3"
          when 115 then rData.name = "F4"
          when 116 then rData.name = "F5"
          when 117 then rData.name = "F6"
          when 118 then rData.name = "F7"
          when 119 then rData.name = "F8"
          when 120 then rData.name = "F9"
          when 121 then rData.name = "F10"
          when 122 then rData.name = "F11"
          when 123 then rData.name = "F12"
        rData.char = false
      when "punctuation"
        if evt.shiftKey
          switch charCode
            when 186 then rData.name = ";"
            when 187 then rData.name = "="
            when 188 then rData.name = ","
            when 189 then rData.name = "-"
            when 190 then rData.name = "."
            when 191 then rData.name = "/"
            when 192 then rData.name = "`"
            when 219 then rData.name = "["
            when 220 then rData.name = "\\"
            when 221 then rData.name = "]"
            when 222 then rData.name = "'"
        else
          switch charCode
            when 186 then rData.name = ":"
            when 187 then rData.name = "+"
            when 188 then rData.name = "<"
            when 189 then rData.name = "_"
            when 190 then rData.name = ">"
            when 191 then rData.name = "?"
            when 192 then rData.name = "~"
            when 219 then rData.name = "{"
            when 220 then rData.name = "|"
            when 221 then rData.name = "}"
            when 222 then rData.name = "\""
        rData.char = true
      when "letters"
        switch charCode
          when 65 then rData.name = "a" 
          when 66 then rData.name = "b" 
          when 67 then rData.name = "c" 
          when 68 then rData.name = "d" 
          when 69 then rData.name = "e" 
          when 70 then rData.name = "f" 
          when 71 then rData.name = "g" 
          when 72 then rData.name = "h" 
          when 73 then rData.name = "i" 
          when 74 then rData.name = "j" 
          when 75 then rData.name = "k" 
          when 76 then rData.name = "l" 
          when 77 then rData.name = "m" 
          when 78 then rData.name = "n" 
          when 79 then rData.name = "o" 
          when 80 then rData.name = "p" 
          when 81 then rData.name = "q" 
          when 82 then rData.name = "r" 
          when 83 then rData.name = "s" 
          when 84 then rData.name = "t" 
          when 85 then rData.name = "u" 
          when 86 then rData.name = "v" 
          when 87 then rData.name = "w" 
          when 88 then rData.name = "x" 
          when 89 then rData.name = "y" 
          when 90 then rData.name = "z"
        rData.name = rData.name.toUpperCase() if evt.shiftKey
    rData
  uniqueAry: (ary) ->
    uniqAry = []
    cj.each ary, (i, el) ->
      uniqAry.push(el) if cj.inArray(el, uniqAry) == -1
    uniqAry
  insertIntoArray: (ary, index, item) ->
    ary.splice(index, 0, item)
  compact: (ary) ->
    arr = cj.grep(ary, (n) ->
        return(n) 
      )
    arr
window.bbUtils = new bb
# when you go new bb... none you're not extending ONTO bb, you're extending BB. which doesn't work, so...
# you need to merge everything into BB and then call it.