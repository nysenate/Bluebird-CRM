Function::property = (prop, desc) ->
  Object.defineProperty @prototype, prop, desc

jstree = 
  init: (settings,view) ->
    # build the instance of data
    instance = new Instance()
    setProp(settings, instance)
    pageElements = instance.get("pageElements")
    dataSettings = instance.get("dataSettings")
    # call the view, and we're using call
    # so that you can change the view...
    # or use super to change it
    view["exec"].call(@, instance)
    request = cj.when(_getTrees.getRawJSON(instance))
    request.done((data) =>
      _getTrees.putRawJSON(data.message,instance)
      _parseTree.init(instance)
      view["done"].call(@, instance)
      )
    instance
  

setProp = (properties..., instance) ->
  for k, v of properties[0]
    instance.set k,v


class Instance
  constructor: (@_rawData,@_autocomplete,@_treeNames,@_trees) ->
    # if the definitions are an array, it's because they can have
    # multiple values
    #this is what makes the page-data tick.
    pageElements =
      init: 'JSTreeInit'
      wrapper: ['JSTree-container']
      tagHolder: ['JSTree']
      messageHandler: 'messages'
      tabLocation: 'tabs'
      tagDropdown: 'dropdown'
      location: ''
      autocomplete: 'autocomplete'
      tokenHolder: 'tokens'
    #interrupts ajax tag save process to not happen for edit type pages
    onSave = false
    dataSettings =
      #Set [one] or [other] to show only one use [291,296] for both (when you want to show KW & IC)
      # pullSets: [291, 296]
      pullSets: [291, 296, 292]
      #contact is set to 0 and SHOULD appear on most contact inits
      entity_id: 0
      # activity_id
      
    displaySettings =
      #IssueCodes = 291 KW = 296. Sets default tree to show first.
      defaultTree: 291
      #Sets default type to appear: edit/tagging
      edit: false
      tagging: true
      # tall or wide or some combination
      tall: true
      wide: true
      #autocomplete box is turned on
      autocomplete: true
      #print tags option
      print: true
      maxHeight: 600
    #ajax related settings
    callAjax =
      # if it's an activity, entity_type is different
      #where to grab the tree
      # temporarily killing
      url: '/civicrm/ajax/tag/tree'
      data:
        entity_table: "civicrm_contact,civicrm_activity,civicrm_case"
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
        obj = _utils.checkForArray(pageElements, obj)
        cj.extend true,pageElements,obj
      if 'onSave' is name 
        onSave = obj
      if 'dataSettings' is name
        obj = _utils.checkForArray(dataSettings, obj) 
        cj.extend true,dataSettings,obj
      if 'displaySettings' is name 
        obj = _utils.checkForArray(displaySettings, obj) 
        cj.extend true,displaySettings,obj
      if 'callAjax' is name
        obj = _utils.checkForArray(callAjax, obj) 
        cj.extend true,callAjax,obj
      if 'ready' is name 
        ready = obj
  # accessors
  @property "rawData",
    get: -> @_rawData
    set: (a) -> @_rawData = a
  @property "autocomplete",
    get: -> @_autocomplete
    set: (a) -> @_autocomplete = a
  @property "positionList",
    get: -> @_positionList
    set: (a) -> @_positionList = a
  @property "treeNames",
    get: -> @_treeNames
    set: (a) -> @_treeNames = a
  @property "trees",
    get: -> @_trees
    set: (a) -> @_trees = a
  @property "entity",
    get: -> @_entity
    set: (a) -> @_entity = a
  getEntity: (entityId, cb) ->
    if entityId == 0
      dataSettings = @get 'dataSettings'
      entityId = dataSettings.entity_id
    @entity = new Entity(entityId, (tags) =>
      cb.call(@,tags)
    )
  appendToAC:(node) ->
    @_autocomplete.push node
    true
  removeFromAC:(node) ->
    for v,i in @_autocomplete
      if parseInt(node.id) == parseInt(v.id)
        console.log "removeAC #{i}"
        @_autocomplete.splice(i,1)

_utils =
  removeDupFromExtend: (obj) =>
    cj.each obj, (k, v) =>
      @removeDupFromExtend(v) if cj.isPlainObject(v)
      v = bbUtils.uniqueAry(v)

  checkForArray: (propDefault, obj) ->
    cj.each obj, (k, def) ->
      # sort prop and obj
      if cj.isArray(def) && cj.isArray(propDefault[k])
        a = propDefault[k].sort()
        b = def.sort()
        for c, i in a 
          if c isnt b[i] 
            for ar in def
              if propDefault[k].indexOf(ar) < 0
                propDefault[k].push(ar)
        obj[k] = propDefault[k]
  textWrap: (text, length) ->
    numberOfSegs = Math.ceil(text.length/length)
    retObj =
      segs: numberOfSegs
      toRet: []
    shouldRet = false
    rx = /\s|-| |\u00A0|\u8209|\r|\n/g
    if numberOfSegs <= 1
      retObj.toRet = ["#{text}"]
      return retObj
    lastEnd = 0
    for a in [0..numberOfSegs] 
      seg = text.slice(length*a,length*(a+1))
      if !seg.match(rx) and seg.length >= length
        # if there's no spaces or any line breaks
        retObj.toRet.push "#{seg} "
        lastEnd = length*(a+1)
      else
        if seg.length > 0
          currentEnd = lastEnd+length
          # find all spaces
          nextSpace = 0
          for wordPiece in text.split(rx)
            currentLastSpace = nextSpace
            if wordPiece.length == 0
              nextSpace++
            nextSpace += wordPiece.length + 1
            if nextSpace >= currentEnd
              nextSpace = currentLastSpace
              break
          if nextSpace > text.length
            currentLastSpace = nextSpace
            killFutureSegs = true
          retObj.toRet.push text.slice(lastEnd,currentLastSpace)
          lastEnd = currentLastSpace
          if killFutureSegs? 
            retObj.segs = a+1
            break

    return retObj
    
  hyphenize: (text) ->
    text.replace(" ","-")
  camelCase: (text) ->
    a = text.split(" ")
    b = ""
    for word,i in a
      if i isnt 0
        word = word.toLowerCase()
        word = word.charAt(0).toUpperCase() + word.substring(1)
      else
        word = word.toLowerCase()
      b += word
    return b
  _createInputBox: (type,name,value = "",classNames...) ->
    checked = ""
    if type == "radio"
      for cName in classNames[0]
        checked = "checked" if cName.toLowerCase() == "checked"
    classes = classNames[0].join(" ")
    return "<input type='#{type}' class='#{classes}' name='#{name}' value='#{value}' #{checked}>"
  createTextBox: (name,value,classNames...) ->
    return _utils._createInputBox("text",name,value,classNames)
  createCheckBox: (name,value,classNames...) ->
    return _utils._createInputBox("checkbox",name,value,classNames)
  createRadioButton: (name,value,classNames...) ->
    return _utils._createInputBox("radio",name,value,classNames)
  removePositionTextFromBill: (positionName) ->
    return positionName.replace(/\ (\-|)(.*AGAINST|.*FOR|.*\(.*\))/g, "")
  checkPositionFromBill: (positionName) ->
    a = positionName.replace(/\ \(.*\)/g,"")
    a = a.replace(/(A|S|J|K)([0-9]*|[0-9]*.)\-20[0-9][0-9]/g,"")
    a = a.replace(/\ \-\ /g,"").toLowerCase()
    a = "neutral" if (a == "" or a == " -" or a == " ")
    return a

_getTrees =
  # makes the JSON call, and then writes it.
  # with putRaw. allows timestamp checks/if applicable
  getRawJSON: (instance) ->
    cj.ajax(instance.get('callAjax'))
  putRawJSON: (data, instance) ->
    rawData = {}
    cj.each data, (i,tID) ->
      if parseFloat(tID.id) in instance.get('dataSettings').pullSets
        rawData[tID.id] =
          'name':tID.name
          'children':tID.children
    instance.rawData = rawData

_tree =
  blacklist: (id) ->
    return true if id == 292
    return false


_parseTree =
  init: (instance) ->
    for k,o of instance.rawData
      _parseAutocomplete["type"] = "#{k}"
      @treeNames[k] = o.name
      if o.children.length > 0 && !(_tree.blacklist(parseFloat(k)))
        _parseAutocomplete.deepIterate o.children, _parseAutocomplete.pre, _parseAutocomplete.post
      if o.children.length > 0 && parseFloat(k) == 292
        @positionList(o.children)
    instance.autocomplete = @ac
    instance.positionList = @pl
    instance.treeNames = @treeNames
  positionList: (obj) ->
    for k,v of obj
      # console.log v
      a =
        "name": _utils.removePositionTextFromBill(v.name)
        "posName": v.name
        "id": v.id
        "pos": _utils.checkPositionFromBill(v.name)
        "parent": 292
        "type": 292
        "children": false
        "description": v.description
        "is_reserved": v.is_reserved
        "created_id": v.created_id
        "created_date": v.created_date
        "created_name": v.created_display_name
        "level": 1
      @pl.push a
  pl: []
  ac: []
  treeNames: {}

_parseAutocomplete =  
  pre: (obj) ->
    _parseAutocomplete.level++
    return true if obj.name == "Inbox Polling Unprocessed"
    hasChildren = false
    if obj.children.length > 0
      hasChildren = true 
    item =
      "name": obj.name
      "id": obj.id
      "parent": obj.parent_id
      "type": _parseAutocomplete.type
      "children": hasChildren
      "description": obj.description
      "is_reserved": obj.is_reserved
      "created_id": obj.created_id
      "created_date": obj.created_date
      "created_name": obj.created_display_name
      "level": _parseAutocomplete.level
    _parseTree.ac.push(item)
  post: (obj) ->
    _parseAutocomplete.level--
  level: 0


  deepIterate: (obj, before, after) ->
    for o in obj
      before(o)
      if o.children.length > 0
        @.deepIterate o.children, before, after
      after(o)

class Entity
  tags: []
  entity_id: 0 
  _get:
    url: '/civicrm/ajax/entity_tag/get'
    data:
      entity_type: "civicrm_contact,civicrm_activity,civicrm_case"
      entity_id: 0
      call_uri: window.location.href
    dataType: 'json'
  _create:
    url: '/civicrm/ajax/entity_tag/create'
    data:
      entity_type: "civicrm_contact,civicrm_activity,civicrm_case"
      entity_id: 0
      call_uri: window.location.href
    dataType: 'json'
  _del:
    url: '/civicrm/ajax/entity_tag/delete'
    data:
      entity_type: "civicrm_contact,civicrm_activity,civicrm_case"
      entity_id: 0
      call_uri: window.location.href
    dataType: 'json'
  constructor: (entity_id,cb) ->
    @entity_id = entity_id
    a = ["_get","_create","_del"]
    for i in a
      @[i].data.entity_id = @entity_id
    request = cj.when(cj.ajax(@_get))
    request.done((data) =>
        @tags = data["message"]
        cb.call(@, @tags)
      )
    return @
  addTag: (tagId)->
    @_create.data.tag_id = tagId
    return cj.when(cj.ajax(@_create))
  removeTag: (tagId)->
    @_del.data.tag_id = tagId
    index = @tags.indexOf(tagId)
    if index > -1
      @tags.splice(index,1)
    return cj.when(cj.ajax(@_del))

window.jstree = jstree