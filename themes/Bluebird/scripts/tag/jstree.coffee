Function::property = (prop, desc) ->
  Object.defineProperty @prototype, prop, desc

jstree = 
  init: (settings,view) ->
    # build the instance of data
    instance = new Instance()
    setProp(settings, instance)
    pageElements = instance.get("pageElements")
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
      wrapper: 'JSTreeContainer'
      tagHolder: ['JSTree']
      messageHandler: ['JSMessages']
      # tabLocation: ['JST']
      location: ''
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
      mode: 'edit'
      # default full, options: short, narrow, short narrow
      size: 'full'
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
      # temporarily killing
      # url: '/civicrm/ajax/tag/tree'
      # data:
      #   entity_table: 'civicrm_contact'
      #   entity_id: 0
      #   call_uri: window.location.href
      #   entity_counts: 0
      # local function
      url: 'localtagdata.json'
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
  @property "treeNames",
    get: -> @_treeNames
    set: (a) -> @_treeNames = a
  @property "trees",
    get: -> @_trees
    set: (a) -> @_trees = a

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
    toRet = ""
    shouldRet = false
    rx = /\s|-| |\u00A0|\u8209|\r|\n/g
    for a in [0..numberOfSegs] 
      seg = text.slice(length*a,length*(a+1))
      if !seg.match(rx) and seg.length >= length
        shouldRet = true
        toRet += "#{seg} "
      else 
        toRet += "#{seg}"
    return toRet if shouldRet
    text
  hyphenize: (text) ->
    text.replace(" ","-")

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
    instance.autocomplete =  @ac
    instance.treeNames = @treeNames
  ac: []
  treeNames: {}

_parseAutocomplete =  
  pre: (obj) ->
    _parseAutocomplete.level++
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


window.jstree = jstree