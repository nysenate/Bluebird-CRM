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
    newView = new View(instance)


class View
  constructor: (@instance) ->
    # first, write all boxes
    @writeContainers()
    @interval = @setUpdateInterval(1000)
  getData: ->
    if @instance.get('ready') is true
      @killUpdateInterval(@interval)
      # console.log @instance.get('ready')
      # make view now
  setUpdateInterval: (timeSet) ->
    callback = => @getData()
    setInterval( callback, timeSet )
  killUpdateInterval: (clearInt) ->
    clearInterval(clearInt)
  writeContainers: () ->
    @pageElements = @instance.get('pageElements')
<<<<<<< HEAD
    # console.log @pageElements
    # find each jstreeinit
    initInstances = cj(@pageElements.init)
    # for
=======
    console.log @pageElements
    cj(".jstreeinit").add
>>>>>>> 99a55eedd50985cbfd444e6226baffea360278ce
  # writeTreeFromSource: () ->
