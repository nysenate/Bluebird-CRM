Function::property = (prop, desc) ->
  Object.defineProperty @prototype, prop, desc

jstree = 
  init: (settings,view) ->
    instance = new Instance()
    setProp(settings, instance)
    pageElements = instance.get("pageElements")
    view["exec"].call(@, instance)
    cj(pageElements.wrapper).data("isLoading",true)
    request = cj.when(getTrees.getRawJSON(instance))
    request.done((data) =>
      getTrees.putRawJSON(data.message,instance)
      cj(pageElements.wrapper).data("isLoading",false)
      view["done"].call(@, instance)
      )
    instance
setProp = (properties..., instance) ->
  for k, v of properties[0]
    instance.set k,v

getTrees =
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

class Instance
  #sets the instance variables for that particular instance 
  # _data = (prop) ->
  #   autocomplete: []
  #     # json array of objects
  #     # using tokenize autocomplete jquery
  #     # [{"id":"856","name":"Issue Code ABC"},
  #     # {"id":"1035","name":"Keyword"},
  #     # {"id":"1048","name":"Dollhouse"},
  #     # {"id":"1113","name":"Full House"}]
  #   rawData: {}
  #     # 291 :json_data 
  #   treeNames: []
  #   trees: {}
  #   return =>
  #     @[prop]

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


window.jstree = jstree