#sets the jstree namespace
# window.jstree ?= {}
  # instance is the collection of data as specified in the init settings
  # should hypothetically only need 1 instance at a time per page
tree =
  startInstance: (properties) ->
    initInstance = new instance(properties)
    request = cj.when(getTrees.getRawJSON(initInstance))
    request.done((data) ->
      getTrees.putRawJSON(data.message, initInstance)
      parseTree.init(initInstance)
      initInstance.set('ready',true))
    initInstance

window.jstree ?= tree   
    
  #creates a new treeDataRepository
  #getTrees
  #parseTree
  #parseautocomplete
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
        @autocompleteID = []
        @autocompleteName = []
        @treeTop = id
        tagName = new BBTagLabel(id)
        # write the top of the tree without adding a lvl++
        # because the first tag (tag header stays @ 0)
        @addDLtop tagName,cID.name, true
        @addDTtag tagName,cID.name
        # loops through each child. 
        cj.each cID.children, (id, tID) =>
          childTagName = new BBTagLabel(tID.id)
          @addDLtop childTagName,tID.name
          @writeOutputData tID 
        @addDLbottom()
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
    @addDTtag tagName,tID.name,parentTag
    if tID.children.length > 0
      cj.each tID.children, (id, cID) =>
        # if !/lcd/i.test(cID.name)
        childTagName = new BBTagLabel(cID.id)
        @addDLtop childTagName,cID.name
        @writeOutputData cID, tID.id
      @addDLbottom() 
    else
      @addDLbottom()
  # helper functions for writing tag names
  addDLtop: (tagName,name,except) ->
    if !except 
      @tagLvl++
    @output += "<dl class='lv-#{@tagLvl}' id='#{tagName.addDD()}' data-name='#{name}'>"
  addDTtag: (tagName,name,parentTag) ->
    if !parentTag?
      parentTag = @treeTop
    @output += "<dt class='lv-#{@tagLvl} #{@tagType}-#{tagName.passThru()}' id='#{tagName.add()}' data-tagid='#{tagName.passThru()}' data-name='#{name}' data-parentid='#{parentTag}'>"
    @output += "<div class='tag'><span class='name'>#{name}</span></div>"
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
    @autocompleteID.push id
    @autocompleteName.push name
  # writes data to treeData
  writeData: () ->
    _treeData.autocomplete[@treeTop] =
      'name' : @autocompleteName
      'id' : @autocompleteID
    _treeData.html[@treeTop] = @output

#/Get Trees
#should be private
_treeData =
  autocomplete: {}
    # acTagName[0] = tag name
    # acTagID[0] = same key
    # acTagName[10] = Energy
    # acTagID[10] = 29
  rawData: {}
    # 291 :json_data 
  html: {}
    # 291 : <html>data</html>

#creates new instances
class instance
  #sets the instance variables for that particular instance 
  constructor: (properties...) ->
    #this is what makes the page-data tick.
    pageElements =
      wrapper: '.JSTreeContainer'
      tagHolder: '.JSTree'
      messageHandler: '.JSMessages'
      tabLocation: '.JSTree-Tags'
      location: ''
    #interrupts ajax tag save process to not happen for edit type pages
    onSave = false
    #sets data for what sets to pull
    ready = false
    dataSettings =
      #Set [one] or [other] to show only one use [291,296] for both (when you want to show KW & IC)
      pullSets: [291, 296, 292]
      #contact is set to 0 and SHOULD appear on most contact inits
      contact: 0
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
    #ajax related settings
    callAjax =
      #where to grab the tree
      url: '/civicrm/ajax/tag/tree'
      data:
        entity_type: 'civicrm_contact'
        entity_id: 0
        call_uri: window.location.href
        entity_counts: 0
      dataType: 'json'
    #set instance properties on init
    for k, v of properties[0]
      switch k
        when "pageElements" then pageElements = v
        when "onSave" then onSave = v
        when "dataSettings" then dataSettings = v
        when "displaySettings" then displaySettings = v
        when "callAjax" then callAjax = v
        when "ready" then ready = v
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
      if 'pageElements' is name then cj.extend true,pageElements,obj
      if 'onSave' is name then onSave = obj
      if 'dataSettings' is name then cj.extend true,dataSettings,obj
      if 'displaySettings' is name then cj.extend true,displaySettings,obj
      if 'callAjax' is name then cj.extend true,callAjax,obj
      if 'ready' is name then ready = obj
  #getter 

  # get: (name...) =>
  #   getRet = {}
  #   if 'pageElements' in name then cj.extend(true, getRet, {pageElements}) 
  #   if 'dataSettings' in name then cj.extend(true, getRet, {dataSettings})
  #   if 'displaySettings' in name then cj.extend(true, getRet, {displaySettings})
  #   if 'callAjax' in name then cj.extend(true, getRet, {callAjax})
  #   getRet

  # #setter
  # set: (obj)  =>
    # if 'pageElements' is name then cj.extend(true, pageElements, obj)
    # if 'dataSettings' is name then cj.extend(true, dataSettings, obj)
    # if 'displaySettings' is name then cj.extend(true, displaySettings, obj)
    # if 'callAjax' is name then cj.extend(true, callAjax, obj)
    # cj.extend(@, obj)
#helpers
typeIsArray = Array.isArray || ( value ) -> return {}.toString.call( value ) is '[object Array]'

#builds tagLabels
class BBTagLabel
  constructor: (@tagID) ->
  add: -> "tagLabel_" + @tagID
  remove: -> @tagID.replace "tagLabel_", ""
  addDD: -> "tagDropdown_" + @tagID
  removeDD: -> @tagID.replace "tagDropdown_", ""
  passThru: -> @tagID
    