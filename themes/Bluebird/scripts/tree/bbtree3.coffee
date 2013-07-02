#sets the jstree namespace
# window.jstree ?= {}
  # instance is the collection of data as specified in the init settings
  # should hypothetically only need 1 instance at a time per page
tree =
  startInstance: (submittedProperties) ->
    initInstance = new instance()
    @setProp(submittedProperties, initInstance)
    request = cj.when(getTrees.getRawJSON(initInstance))
    request.done((data) =>
      getTrees.putRawJSON(data.message, initInstance)
      parseTree.init(initInstance)
      initInstance.set('ready',true)
      # @startBehavior(initInstance)
      )
    initInstance
  setProp: (properties..., instance) ->
    for k, v of properties[0]
      instance.set k,v
  # startBehavior: (instance) ->
    # treeBehavior.autoCompleteStart()

treeBehavior =
  getEntityTags: () ->

  tagActions: () ->
    # this should ONLY be the ajax db calls to add/remove/move

window.jstree ?= tree
window.CRM ?= {}
    
  #getContactTags
  #tagEntity
  #tagActions

getTrees =
  # makes the JSON call, and then writes it.
  # with putRaw. allows timestamp checks/if applicable
  getRawJSON: (instance) ->
    cj.ajax(instance.get('callAjax'))
  putRawJSON: (data, instance) ->
    cj.each data, (i,tID) ->
      if parseFloat(tID.id) in instance.get('dataSettings').pullSets
        _treeData.rawData[tID.id] =
          'name':tID.name
          'children':tID.children
parseTree =
  init: (instance) ->
    # reads each tree
    cj.each _treeData.rawData, (id,cID) =>
      if parseFloat(id) in instance.get('dataSettings').pullSets
        # blanks all reused data
        @output = ''
        @tagLvl = 0
        @setDataType(cID.name)
        @autocompleteObj = []
        @treeTop = id
        tagName = new BBTagLabel(id)
        # write the top of the tree without adding a lvl++
        # because the first tag (tag header stays @ 0)
        # @addDLtop tagName,cID.name, true
        # @addDTtag tagName,cID.name
        # loops through each child.
        @addTabName (cID.name)
        @output += "<dl class='top-#{id}'>"
        cj.each cID.children, (id, tID) =>
          @writeOutputData tID 
        @output += "</dl>"
        @writeData()
    console.log "Loaded Data"
  isItemMarked: (value,type) ->
    if value true then type else ''
  isItemChildless: (childLength) ->
    if childLength > 0 then 'treeButton' else ''
  writeOutputData: (tID, parentTag) ->
    # writeOutputData calls itself if a tag has children
    # and snakes its way out
    tagName = new BBTagLabel(tID.id)
    @addAutocompleteEntry tID.id, tID.name
    if tID.children.length > 0 then hasChild = true else hasChild = false
    @addDTtag tagName,tID.name,parentTag,hasChild
    @addDLtop tagName,tID.name
    if hasChild
      cj.each tID.children, (id, cID) =>
        # if !/lcd/i.test(cID.name)
        @writeOutputData cID, tID.id
      @addDLbottom() 
    else
      @addDLbottom()
  addTabName: (name) ->
    _treeData.treeNames.push(name)
  # helper functions for writing tag names
  addDLtop: (tagName,name) ->
    @output += "<dl class='lv-#{@tagLvl}' id='#{tagName.addDD()}' data-name='#{name}'>"
  addDTtag: (tagName,name,parentTag,hasChild,except) ->
    if !except 
      @tagLvl++
    if hasChild then treeButton = "treeButton" else treeButton = ""
    # console.log "#{name} #{hasChild} #{treeButton} "
    if !parentTag?
      parentTag = @treeTop
    @output += "<dt class='lv-#{@tagLvl} #{@tagType}-#{tagName.passThru()}' id='#{tagName.add()}' data-tagid='#{tagName.passThru()}' data-name='#{name}' data-parentid='#{parentTag}'>"
    @output += "<div class='tag'>"

    @output += "<div class='ddControl #{treeButton}'></div>"
    @output += "<span class='name'>#{name}</span></div>"
    @output += "</dt>"
  addDLbottom: ->
    @tagLvl--
    @output += "</dl>"
  setDataType: (name) ->
    # with the names, you can add an array & property lookup to
    # the init settings
    switch name
      when "Issue Code" then @tagType = "issueCode"
      when "Positions" then @tagType = "position"
      when "Keywords" then @tagType = "keyword"
      else @tagType = "tag"
  # creates autocomplete array
  addAutocompleteEntry: (id,name) ->
    tempObj = 
      "name": name
      "id": id
      "type": @treeTop
      # don't know if type is necessary to filter out? probably a good idea though
    @autocompleteObj.push tempObj
  # writes data to treeData
  writeData: () ->
    _treeData.autocomplete = _treeData.autocomplete.concat @autocompleteObj
    _treeData.html[@treeTop] = @output

#/Get Trees
#should be private
_treeData =
  autocomplete: []
    # json array of objects
    # using tokenize autocomplete jquery
    # [{"id":"856","name":"Issue Code ABC"},
    # {"id":"1035","name":"Keyword"},
    # {"id":"1048","name":"Dollhouse"},
    # {"id":"1113","name":"Full House"}]
  rawData: {}
    # 291 :json_data 
  html: {}
    # 291 : <html>data</html>
  treeNames: []

#creates new instances
class instance
  #sets the instance variables for that particular instance 
  constructor: () ->
    # if the definitions are an array, it's because they can have
    # multiple values
    #this is what makes the page-data tick.
    pageElements =
      init: 'JSTreeInit'
      wrapper: 'JSTreeContainer'
      tagHolder: ['JSTree']
      messageHandler: ['JSMessages']
      # tabLocation: ['JST']
      location: ''
    #interrupts ajax tag save process to not happen for edit type pages
    onSave = false
    #sets data for what sets to pull
    ready = false
    dataSettings =
      #Set [one] or [other] to show only one use [291,296] for both (when you want to show KW & IC)
      pullSets: [291, 296]
      # pullSets: [291, 296, 292]
      #contact is set to 0 and SHOULD appear on most contact inits
      contact: 0
      # activity_id
      
    displaySettings =
      #IssueCodes = 291 KW = 296. Sets default tree to show first.
      defaultTree: 291
      #Sets default type to appear: edit/tagging
      mode: 'edit'
      #Size? full includes tag tree
      fullSize: true
      #autocomplete box is turned on
      autocomplete: true
      #print tags option
      print: true
      #show all active tags
      showActive: true
      #shows stubs on stub items
      showStubs: false
    #ajax related settings
    callAjax =
      # if it's an activity, entity_type is different
      #where to grab the tree
      url: '/civicrm/ajax/tag/tree'
      data:
        entity_table: 'civicrm_contact'
        entity_id: 0
        call_uri: window.location.href
        entity_counts: 0
      dataType: 'json'
    # getter/setter
    @get = (name) =>
      getRet = {}
      if 'pageElements' is name then cj.extend true,getRet,pageElements
      if 'onSave' is name then return onSave
      if 'dataSettings' is name then cj.extend true,getRet,dataSettings
      if 'displaySettings' is name then cj.extend true,getRet,displaySettings
      if 'callAjax' is name then cj.extend true,getRet,callAjax
      if 'ready' is name then return ready
      getRet
    @set = (name,obj)  =>
      if 'pageElements' is name 
        obj = @checkForArray(pageElements, obj)
        cj.extend true,pageElements,obj
      if 'onSave' is name 
        onSave = obj
      if 'dataSettings' is name
        obj = @checkForArray(dataSettings, obj) 
        cj.extend true,dataSettings,obj
      if 'displaySettings' is name 
        obj = @checkForArray(displaySettings, obj) 
        cj.extend true,displaySettings,obj
      if 'callAjax' is name
        obj = @checkForArray(callAjax, obj) 
        cj.extend true,callAjax,obj
      if 'ready' is name 
        ready = obj
    @getAutocomplete = =>
      _treeData.autocomplete
  checkForArray: (propDefault, obj) ->
    cj.each obj, (k, def) ->
      # sort prop and obj
      if cj.isArray(def) && cj.isArray(propDefault[k])
        a = propDefault[k].sort()
        b = def.sort()
        for c, i in a 
          if c isnt b[i] 
            for ar in def
              propDefault[k].push(ar)
        obj[k] = propDefault[k]

#helpers
# typeIsArray = Array.isArray || ( value ) -> return {}.toString.call( value ) is '[object Array]'

#builds tagLabels
class BBTagLabel
  constructor: (@tagID) ->
  add: -> "tagLabel_" + @tagID
  remove: -> @tagID.replace "tagLabel_", ""
  addDD: -> "tagDropdown_" + @tagID
  removeDD: -> @tagID.replace "tagDropdown_", ""
  passThru: -> @tagID
