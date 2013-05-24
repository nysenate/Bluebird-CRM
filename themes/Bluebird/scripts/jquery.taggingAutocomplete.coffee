(($, window, document) ->
  # Prepare your internal $this reference.
  $this = undefined
 
  # Store your default settings in something "private".
  # The simplest way to do so is to abide by the convention that anything
  # named with a leading underscore is part of the private API (a well-known
  # interface contract in the JavaScript community).
  _settings =
    default: 'cool!'
    
  # You *may* rely on internal, private objects:
  _flag = false
  _anotherState = null
 
  # This is your public API (no leading underscore, see?)
  # All public methods must return $this so your plugin is chainable.
  methods =
    init: (options) ->
      $this = $(@)
      # The settings object is available under its name: _settings. Let's
      # expand it with any custom options the user provided.
      $.extend _settings, (options or {})
      # Do anything that actually inits your plugin, if needed, right now!
      # An important thing to keep in mind, is that jQuery plugins should be
      # built so that one can apply them to more than one element, like so:
      #
      #  $('.matching-elements, #another-one').tagACInput()
      #
      # It means the $this object we populated using @ (this) is to be
      # considered an array of selectors, and one must always perform
      # computations while iterating over them:
      #
      #  $this.each (index, el) ->
      #    # do something with el
      #
      return $this
 
    doSomething: (what) ->
      # Another public method that people can call and rely on to do "what".
      return $this
 
    # This method is often overlooked.
    destroy: ->
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
 
  # Here is another important part of a proper plugin implementation: the clean
  # namespacing preventing from cluttering the $.fn namespace. This explains why
  # we went the extra miles of providing a pair of public and private APIs.
  # This is also the place where you specify the name of your plugin in your code.
  $.fn.tagACInput = (method) ->
    if methods[method]
      methods[method].apply this, Array::slice.call(arguments, 1)
    else if typeof method is "object" or not method
      methods.init.apply this, arguments
    else
      $.error "Method " + method + " does not exist on jquery.tagACInput"
) cj, window, document