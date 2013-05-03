#----------
#views
#----------
#createNewView
#writeTreeFromSource
#writeAutocomplete
#writeTaggedList
#writeTagControls
#writeAddTag
#writeRemoveTag
#writeConfirmDisplay

window.jstree.views = 
  createNewView: (instance) ->
    aView = new View(instance)


class View
  constructor: (@instance) ->
    @setUpdateInterval(1000)


  getData: ->
    # DOESNT UNBIND WTH
    # console.log "get Data by Ajax"
    # console.log @instance.get('ready')
    if @instance.get('ready')
      @killUpdateInterval()
  setUpdateInterval: (timeSet) ->
    callback = => @getData()
    setInterval( callback, timeSet )
  killUpdateInterval: () ->
    console.log('ttk')
    callback = => @getData()
    clearInterval(callback)