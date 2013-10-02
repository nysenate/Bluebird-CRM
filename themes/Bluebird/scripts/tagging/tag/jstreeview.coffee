window.jstree["views"] =
  exec: (instance) ->
    @view = new View(instance)
    @menuSettings = new Settings(instance,@view)
  done: (instance) ->
    trees = {}
    for a,v of instance.treeNames
      b = _treeUtils.selectByTree(instance.autocomplete, a)
      trees[a] = new Tree(b,a)
    @view.trees = trees
    @view.init()
    if @view.settings.tall && !@view.settings.lock
      resize = new Resize
      resize.addResize(instance,@view)
    else
      @view.cj_tokenHolder.resize.remove()
    
  view: {}

class View
  @property "trees",
    get: -> @_trees
    set: (a) -> @_trees = a
  selectors:
    tagBox: ""
    container: ""
    containerClass: ""
    initHolder: ""
    byHeightWidth: ""
    dropdown: ""
    defaultTree: ""
    activeTree: ""
    isFiltered: false
    data: "data"
    idedKeys: [
     "container", "data"
    ]
    addPrefix: [
      "dropdown","data"
    ]
  menuSelectors:
    menu: "menu"
    top: "top"
    tabs: "tabs"
    bottom: "bottom"
    autocomplete: "autocomplete"
    settings: "settings"
    addPrefix: [
      "menu","tabs","top","bottom","autocomplete","settings"
    ]
  tokenHolder:
    box: "tokenHolder"
    options: "options"
    body: "tokenBody"
    resize: "resize"
    left: "left"
    addPrefix: [
      "box","options","body","resize","left"
    ]
  settings:
    tall: true
    wide: true
    edit: false
    tagging: false
    print: true
    lock: false
  entity_id: 0
  defaultPrefix: "JSTree"
  prefixes: []
  defaultTree: 0
  descWidths:
    normal: 75
    long: 150
  constructor: (@instance) ->
    # starts the chain to write the page structure
    @writeContainers()
  applyTagged:() ->
    @instance.getEntity(@entity_id, (tags) =>
        @entityList = tags
        @applyTaggedKWIC()
        @applyTaggedPositions()
      )
  applyTaggedKWIC:(filter="") ->
    findList = []
    for i in @entityList
      findList.push "#{filter} #tagLabel_#{i}"
    cjDTs = @cj_selectors.tagBox.find(findList.join(","))
    cjDTs.addClass("shaded")
    cj.each(cjDTs, (i,DT) =>
      cj(DT).find(".fCB input.checkbox").prop("checked",true)
      @hasTaggedChildren(cj(DT))  
    )
  findPositionLocalMatch:(cjDT) ->
    name = cjDT.find(".tag .name").text
    for a,b of @instance.positionList
      name = _utils.removePositionTextFromBill(cjDT.name)
      position = cjDT.data("position")
  applyTaggedPositions:() ->
    posList = []
    trees = @trees
    for a,b of @instance.positionList
      iO = @entityList.indexOf("#{b.id}")
      if iO > -1
        posList.push b
    if posList.length > 0
      @cj_selectors.tagBox.find(".top-292").remove()
      trees[292] = new Tree(posList, 292)
      if @cj_menuSelectors.tabs.find(".tab-positions").hasClass("active")

      else
        @cj_selectors.tagBox.find(".top-292").css("display","none")
      new Buttons(@,".top-292")
      cjDTs = @cj_selectors.tagBox.find(".top-292 dt")
      cjDTs.addClass("shaded")
      cjDTs.find(".fCB input.checkbox").prop("checked",true)
      @trees = trees
  writeContainers: () ->
    @formatPageElements()
    @createSelectors()
    tagBox = new Resize
    @setDescWidths()
    if @settings.tall
      if tagBox?
        if tagBox.height > 0  
          height = " style='height:#{tagBox.height}px'"
          @addClassesToElement(height)
        else
          @buildDropdown()
      else
        height = ""
        @addClassesToElement(height)
    else
      @buildDropdown()
  setDescWidths: () ->
    if @settings.tall
      if @settings.wide
        _descWidths.normal = 75
        _descWidths.long = 150
      else
        _descWidths.normal = 38
        _descWidths.long = 38
    else
      if @settings.wide
        _descWidths.normal = 73
        _descWidths.long = 145
      else
        _descWidths.normal = 38
        _descWidths.long = 38
  buildDropdown: () ->
    @cj_selectors.initHolder.html "<div class='#{@selectors.tagBox} dropdown'></div>"
    @cj_selectors.initHolder.prepend(@menuHtml(@menuSelectors))
    @cj_selectors.initHolder.append(@dataHolderHtml())
    @cj_selectors.initHolder.append(@tokenHolderHtml(@tokenHolder))
    @cj_selectors.initHolder.removeClass(@selectors.initHolder).attr("id", @selectors.container).addClass(@selectors.containerClass)
  addClassesToElement: (height) ->
    @cj_selectors.initHolder.html "<div class='#{@selectors.tagBox}' #{height}></div><div class='JSTree-overlay'></div>"
    @cj_selectors.initHolder.prepend(@menuHtml(@menuSelectors))
    @cj_selectors.initHolder.append(@dataHolderHtml())
    @cj_selectors.initHolder.append(@tokenHolderHtml(@tokenHolder))
    @cj_selectors.initHolder.removeClass(@selectors.initHolder).attr("id", @selectors.container).addClass(@selectors.containerClass)
  formatPageElements: () ->
    pageElements = @instance.get 'pageElements'
    displaySettings = @instance.get 'displaySettings'
    dataSettings = @instance.get 'dataSettings'
    # could reorginize to allow best flexibility for tags
    @selectors.container = pageElements.wrapper.shift()
    @selectors.containerClass = pageElements.wrapper.join(" ")
    @selectors.tagBox = pageElements.tagHolder.join(" ")
    @menuSelectors.tabs = pageElements.tabLocation
    @menuSelectors.autocomplete = pageElements.autocomplete
    @selectors.dropdown = pageElements.tagDropdown
    @selectors.initHolder = pageElements.init
    @entity_id = dataSettings.entity_id
    @settings = displaySettings
    @settingCollection = ["settings","menuSelectors","tokenHolder","selectors"]
    for v in pageElements.tagHolder
      @prefixes.push(v)
    @joinPrefix()
    @selectors.byHeightWidth = @setByHeightWidth()
    if !@settings.wide
      @selectors.containerClass += " narrow"
  joinPrefix: () ->
    for v in @settingCollection
      for k,o of @["#{v}"]
        continue if typeof(o) != "string" or o.length == 0
        if @["#{v}"].idedKeys?
          if @["#{v}"].idedKeys.indexOf(k) >= 0
            if @["#{v}"].addPrefix?  
              if @["#{v}"].addPrefix.indexOf(k) >= 0
                @["#{v}"][k] = "#{@prefixes[0]}-#{o}"
                @["#{v}"].addPrefix.splice(@["#{v}"].addPrefix.indexOf(k),1)
        if @["#{v}"].addPrefix?  
          if @["#{v}"].addPrefix.indexOf(k) >= 0
            name = ""
            for a,i in @prefixes
              name += "#{a}-#{o}"

              name += " " if @prefixes.length - 1 > i
            @["#{v}"][k] = name

  createSelectors: () ->
    for v in @settingCollection
      @createCJfromObj(@[v],v)

  createCJfromObj: (obj, name) ->
    cjed = {}
    for k,v of obj
      continue if typeof(v) != "string" or v.length == 0
      selectorType = "."
      if obj.idedKeys?
        selectorType = "#" if obj["idedKeys"].indexOf(k) >= 0
      cjed[k] = cj("#{selectorType}#{cj.trim(v).replace(/\ /g, ".")}")
    @["cj_#{name}"] = cjed
  
  setByHeightWidth: () ->
    ret = ""
    ret += "narrow " unless @settings.wide
    ret += "short" unless @settings.tall
    ret

  menuHtml: (name) -> 
    return "
      <div class='#{name.menu}'>
       <div class='#{name.top}'>
        <div class='#{name.tabs}'></div>
        <div class='#{name.settings}'></div>
       </div>
       <div class='#{name.bottom}'>
        <div class='#{name.autocomplete}'>
         <input type='text' id='JSTree-ac'>
        </div>
        <div class='#{name.settings}'></div>
       </div>
      </div>
    "
  tokenHolderHtml: (name) ->
    return "
        <div class='#{name.box}'>
         <div class='#{name.resize}'></div>
         <div class='#{name.body}'>
          <div class='#{name.left}'></div>
          <div class='#{name.options}'></div>
         </div>
        </div>
      "
  dataHolderHtml: () ->
    return "<div id='JSTree-data' style='display:none'></div>"
  init:() ->
    @createSelectors()
    _treeVisibility.currentTree = _treeVisibility.defaultTree = _treeVisibility.previousTree = @settings.defaultTree
    for k,v of @instance.treeNames
      tabName = @createTreeTabs(v)
    @setActiveTree(@settings.defaultTree)
    ac = new Autocomplete(@instance, @)
    for k,v of @instance.treeNames
      @createTabClick("tab-#{@getTabNameFromId(k,true)}", k)
      if parseInt(k) == 292
        @addPositionReminderText(@cj_selectors.tagBox.find(".top-#{k}"))
    buttons = new Buttons(@)
    @setTaggingOrEdit()
  setTaggingOrEdit: () ->
    if @cj_selectors.tagBox.hasClass("tagging,edit")
      @cj_selectors.tagBox.removeClass("tagging").removeClass("edit")
    if @settings.edit && @settings.tagging
      @settings.tagging = false
    if @settings.edit
      @cj_selectors.tagBox.addClass("edit")
    if @settings.tagging
      @cj_selectors.tagBox.addClass("tagging")
      @applyTagged(@entity_id)
  createTabClick: (tabName, tabTree) ->
    @cj_menuSelectors.tabs.find(".#{tabName}").off "click"
    @cj_menuSelectors.tabs.find(".#{tabName}").on "click", =>
      @showTags tabTree,tabName
  showTags: (currentTree, tabName, noPrev) ->
    if currentTree != _treeVisibility.currentTree
      @cj_menuSelectors.tabs.find(".tab-#{@getTabNameFromId(_treeVisibility.currentTree,true)}").removeClass("active")
      @cj_selectors.tagBox.removeClass("top-#{_treeVisibility.currentTree}-active")
      @cj_selectors.tagBox.find(".top-#{_treeVisibility.currentTree}").toggle().removeClass("active") 
      _treeVisibility.previousTree = _treeVisibility.currentTree
      _treeVisibility.currentTree = currentTree
      @cj_menuSelectors.tabs.find(".tab-#{@getTabNameFromId(currentTree,true)}").addClass("active")
      @cj_selectors.tagBox.find(".top-#{currentTree}").toggle().addClass("active")
      @cj_selectors.tagBox.addClass("top-#{currentTree}-active")
  setActiveTree: (id) ->
    tabName = @getTabNameFromId(id,true)
    @cj_menuSelectors.tabs.find("div").removeClass("active")
    @cj_selectors.tagBox.find(".tagContainer").removeClass("active").css("display","none")
    @cj_menuSelectors.tabs.find(".tab-#{tabName}").addClass("active")
    @cj_selectors.tagBox.find(".top-#{id}").addClass("active").css("display","block")
    @cj_selectors.tagBox.addClass("top-#{id}-active")
  createTreeTabs: (tabName, isHidden = false) ->
    if isHidden then style = "style='display:none'" else style = ""
    tabClass = (_utils.hyphenize(tabName)).toLowerCase()
    output = "<div class='tab-#{tabClass}' #{style}>#{tabName}</div>"
    @cj_menuSelectors.tabs.append(output)
  getTabNameFromId: (id, hyphenize = false) ->
    treeNames = @instance.treeNames
    return treeNames[id] unless hyphenize
    return _utils.hyphenize(treeNames[id]).toLowerCase()
  getIdFromTabName: (tabName) ->
    tabName = cj.trim(tabName)
    return 291 if tabName == "tab-issue-codes" or tabName == "issue-codes"
    return 296 if tabName == "tab-keywords" or tabName == "keywords"
    return 292 if tabName == "tab-positions" or tabName == "positions"
      
  buildFilteredList: (tags) ->
    checkAgainst = {}
    for m,n of tags
      checkAgainst[m] = []
      for x,y of n
        checkAgainst[m].push(parseFloat(y.id))
    buildList = {}
    for d,e of checkAgainst
      buildList[d] = []
      for k,o of @instance.autocomplete
        if e.indexOf(parseFloat(o.id)) >= 0
          buildList[d].push o
    buildList

  # instance variables
  shouldBeFiltered: false
  currentWrittenTerm: ""
  queryLog:
    "291": []
    "296": []
    "292": []

  createQueryLog: (term,tree) ->
    if @queryLog[tree].lastIndexOf(term) < 0
      @queryLog[tree].push term
    for k,v of @queryLog
      if v.length > @queryLog[tree].length
        return false
    return true

  writeEmptyList: (term,tree) ->

  writeFilteredList: (list,term,hits = {}) ->
    if !@shouldBeFiltered
      return false

    for k,v of hits
      latestQuery = @createQueryLog(term,"#{k}")
      unless latestQuery
        return false

    if !@cj_selectors.tagBox.hasClass("filtered")
      @cj_selectors.tagBox.addClass("filtered")

    cj.each(@cj_selectors.tagBox.find(".tagContainer"), (i,tree)=>
      cjTree = cj(tree)
      unless cjTree.hasClass("filtered")
        cjTree.remove()
      if cjTree.data("term") != term
        cjTree.remove()
    )
    for k,v of hits
      # if if it's a hit, delete current box and write new box
      activeTree = @cj_menuSelectors.tabs.find(".active").attr("class").replace("active","")
      if v == 0
        @setTabResults(k,"0")
        @writeEmptyList(term,k)
        @cj_selectors.tagBox.find(".top-#{k}").data("term",term)
      else
        @setTabResults(k,v)
        t = new Tree(list[k],k,true)
        @cj_selectors.tagBox.find(".top-#{k}").data("term",term)
      if parseInt(k) == 292
        if v > 0
          for a,b of @instance.positionList
            iO = b.id.indexOf(@entityList)
            if iO > -1
              cjDTs = @cj_selectors.tagBox.find("#tagLabel_#{b.id}")
              cjDTs.addClass("shaded")
              cjDTs.find(".fCB input.checkbox").prop("checked",true)
    new Buttons(@)
    if @settings.tagging
      @applyTaggedKWIC()
    @setActiveTree(@getIdFromTabName(activeTree))

    
  noResultsBox: (treeId,k) ->
    activeTree = @getIdFromTabName(cj.trim(cj(".JSTree-tabs .active").attr("class").replace(/active/g,"")))
    if parseInt(k) == parseInt(activeTree) then isActive = "active" else isActive = ""
    noResults = "
            <div class='top-#{k} tagContainer filtered #{isActive} no-results'>
              <div class='no-results'>
                No Results Found
              </div>
            </div>
          "
    cj(".JSTree").append(noResults)

  rebuildInitialTree: () ->
    if @cj_selectors.tagBox.hasClass("filtered")
      @cj_selectors.tagBox.removeClass("filtered")
      @cj_selectors.tagBox.find(".filtered").remove()
      activeTree = @cj_menuSelectors.tabs.find(".active").attr("class").replace("active","")
      for k,v of @trees
        if parseInt(k) != 292
          t = new Tree(v.tagList, k)
        if parseInt(k) == 292 && !@settings.tagging
          @cj_selectors.tagBox.find(".top-#{k}").empty()
          @addPositionReminderText(@cj_selectors.tagBox.find(".top-#{k}"))
        if parseInt(k) == 292 && @settings.tagging
          # fix this here to write entity settings
          @cj_selectors.tagBox.find(".top-#{k}").empty()
          # @addPositionReminderText(@cj_selectors.tagBox.find(".top-#{k}"))
          @applyTaggedPositions()
      new Buttons(@)
      if @settings.tagging
        @applyTaggedKWIC()
      @setActiveTree(@getIdFromTabName(activeTree))

  setTabResults: (tree,val) ->
    cjTab = @cj_menuSelectors.tabs.find(".tab-#{@getTabNameFromId(tree, true)}")
    if cjTab.find("span").length > 0
      cjTab.find("span").html("(#{val})")
    else
      result = cjTab.html()
      cjTab.html("#{result}<span>(#{val})</span>")
        

  removeTabCounts: (id) ->
    if id?
      @cj_menuSelectors.tabs.find(".#{} span").remove()
    else
      @cj_menuSelectors.tabs.find("span").remove()

  addPositionReminderText: (cjlocation) ->
    positionText = "
              <div class='position-box-text-reminder'>
                Type in a Bill Number or Name for Results
              </div>
          "
    cjlocation.html(positionText)

  toggleTagBox: () ->
    @cj_selectors.tagBox.toggle().toggleClass("dropdown")

  toggleDropdown: (oc = false) ->
    # debugger
    if oc
      if @cj_selectors.tagBox.hasClass("filtered")
        if @cj_selectors.tagBox.find(".top-291,.top-296").length > 0
          cj.each(@cj_selectors.tagBox.find(".tagContainer:not('.top-292')"), (i,container) =>
            @getTagHeight(cj(container))
          )
        if @cj_selectors.tagBox.find(".top-292").length == 1
          cj.each(@cj_selectors.tagBox.find(".tagContainer.top-292"), (i,container) =>
            @getTagHeight(cj(container))
          )
        @cj_selectors.container.css("position","static")
        @cj_selectors.tagBox.css("height","auto").addClass("open").css("overflow-y","auto")
      else
        boxHeight = new Resize()
        @cj_selectors.container.css("position","relative")
        @cj_selectors.tagBox.removeClass("open").css("overflow-y","scroll").height(boxHeight)  
    else
      boxHeight = new Resize()
      @cj_selectors.container.css("position","relative")
      @cj_selectors.tagBox.removeClass("open").css("overflow-y","scroll").height(boxHeight)
  
  getTagHeight:(cjTagContainer,maxHeight = 180) ->
    # get all dl's
    cj.each(cjTagContainer, (a,container) =>
      checkDTs = []
      heightTotal = @getRecTagHeight(container)
      propHeight = 0
      for v in heightTotal
        propHeight += parseInt(v)
      if propHeight > maxHeight
        closestTo = 0
        for v in heightTotal
          if closestTo > maxHeight
            break
          closestTo += parseInt(v)
        cj(container).height(closestTo)
      else
        cj(container).height(propHeight)
    )
  
  getRecTagHeight:(container,heightTotal = [],already) ->
    if heightTotal.length > 8
      return heightTotal
    cj.each(cj(container).find("dt"), (i,el) =>
      cjEl = cj(el)
      heightTotal.push cjEl.height()
      if heightTotal.length > 8
        return false
    )
    return heightTotal
  createAction: (tagId="",action,cb) ->
    # this is where you save previous history
    new Action(@,@instance,tagId,action,cb)
  toggleCheckInBox: () ->
    a = @
    @cj_selectors.tagBox.find("dt input.checkbox").off("change")
    @cj_selectors.tagBox.find("dt input.checkbox").on("change", ->
      action =
        type: "checkbox"
      removeTag = () ->
        _removeTag = entity.removeTag(tagId)
        _removeTag.done((i) =>
          doAction.apply(null,[i,"remove"])
        )
      addTag = () ->
        _addTag = entity.addTag(tagId)
        _addTag.done((i) =>
          doAction.apply(null,[i,"add"])
        )
      doAction = (res, typeOfAction) ->
        action["action"] = typeOfAction
        if res.code != 1
          removeTag.call(null,null) if typeOfAction == "add"
          addTag.call(null,null) if typeOfAction == "remove"
        new ActivityLog(res,action)
      toggleClass = (cjDT) ->
        # requires addTag (if doesn't already exist)
        cjDT.toggleClass("shaded")
        a.hasTaggedChildren(cjDT)
        if cj(@).prop("checked")
          addTag.call(@,null)
        else
          removeTag.call(@,null)
      entity = a.instance.entity
      cjDT = cj(@).parents("dt").first()
      # if position!
      if cjDT.data("tree") == 292 && parseInt(cjDT.data("tagid")) >= 292000
        # create new position
        o = @
        a.createAction(cjDT.data("tagid"),"addTagFromPosition", (response)->
            newDT = response.cjDT
            if response == false
              console.log "response false"
            else
              action.tagId = response["message"]["id"]
              toggleClass.call(newDT.find("input.checkbox")[0],newDT)
          )
      else
        tagId = cjDT.data("tagid")
        action.tagId = tagId
        toggleClass.call(@,cjDT)
    )
  hasTaggedChildren: (cjDT) ->
    tagId = cjDT.data("tagid")
    if cjDT.siblings("#tagDropdown_#{tagId}").find("dt.shaded").length > 0
      cjDT.addClass("shadedChildren")
    parents = cjDT.parentsUntil(".JSTree","dl")
    # checks up and down the chain for children/parents that aren't
    # correctly labeled
    for dl,i in parents
      parentTagId = cj(dl).data("tagid")
      cjSiblingDT = @cj_selectors.tagBox.find("#tagLabel_#{parentTagId}")
      if cj(dl).find("dt.shaded").length > 0
        cjSiblingDT.addClass("shadedChildren")
      else
        cjSiblingDT.removeClass("shadedChildren") 


class Action
  ajax:
    addTag:
      url: "/civicrm/ajax/tag/create"
      data:
        name: ""
        description: ""
        parent_id: ""
        is_reserved: true
  constructor: (@view, @instance, tagId, action,@cb) ->
    # @createSlide()
    for k,v of @ajax
      v.data["call_uri"] = window.location.href
      v["dataType"] = "json"
    @[action].apply(@,[tagId,action])
  createSlide: () ->
    resize = new Resize
    @view.cj_selectors.tagBox.addClass("hasSlideBox")
    if resize.height > 200
      @view.cj_selectors.tagBox.prepend("<div class='slideBox'></div>")
      @view.cj_selectors.tagBox.find(".slideBox").css("right","#{@findGutterSpace()}px")
      @view.cj_selectors.tagBox.find(".slideBox").animate({width:'40%'}, 500, =>
        # console.log "time to populate"
      )
    else
  addTagFromPosition:(tagId,action) ->
    manipBox = (tagId,messageId) =>
      cjDL = @view.cj_selectors.tagBox.find("#tagDropdown_#{tagId}")
      cjDL.attr("id","tagDropdown_#{messageId}")
      cjDL.data("tagid",messageId)
      cjDT.data("tagid",messageId)
      cjDT.attr("id","tagLabel_#{messageId}")
      cjDT.removeClass("tag-#{tagId}").addClass("tag-#{messageId}")
      cjDT.find("input.checkbox").attr("name","tag[#{messageId}]")
    cjDT = @view.cj_selectors.tagBox.find("#tagLabel_#{tagId}")
    @ajax.addTag.data.name = cjDT.find(".tag .name").text()
    @ajax.addTag.data.description = cjDT.find(".tag .description").text()
    @ajax.addTag.data.parent_id = "292"
    @ajax.addTag.data.is_reserved = true
    for k,v of @instance.positionList
      if _utils.removePositionTextFromBill(@ajax.addTag.data.name) == v.name
        if _utils.checkPositionFromBill(@ajax.addTag.data.name) == v.pos
          manipBox.call(@,cjDT.data("tagid"),v.id)
          message =
            id: v.id
          response = {"cjDT": cjDT,"message":message}
          @cb(response)
    @addTag(tagId,action, (message) =>
      # change tag onces completed
      # if message == "ERROR: `tag_id` parameter is required to identify the tag to apply."
      #   cjDT.prop("checked",false)
      #   console.log message
      #   return false
      if message == "DB Error: already exists"
        cjDT.prop("checked",false)
        if @cb?
          response = {"cjDT": cjDT,"message":message}
          @cb(response)
      manipBox.call(@,tagId,message.id)
      if @cb?
        response = {"cjDT": cjDT,"message":message}
        @cb(response)
    )

  findGutterSpace: () ->
    outerWidth = @view.cj_selectors.tagBox.width()
    innerWidth = @view.cj_selectors.tagBox.find(".tagContainer.active").width()
    return outerWidth-innerWidth

  moveTag: () ->
  addTag: (tagId,action,locCb) ->
    if @ajax.addTag.data.name == ""
      @cb(false) if @cb?
      return false
    request = cj.when(cj.ajax(@ajax.addTag))
    request.done((data) =>
        if locCb?
          locCb(data.message)
        else if @cb?
          @cb(data.message)
      )
    return @
  removeTag: () ->
  mergeTag: () ->
  updateTag: () ->

class Buttons
  checkbox: "<input type='checkbox' class='checkbox'>"
  addTag: "<li class='addTag' title='Add New Tag' data-do='add'></li>"
  removeTag: "<li class='removeTag' title='Remove Tag' data-do='remove'></li>"
  moveTag: "<li class='moveTag' title='Move Tag' data-do='move'></li>"
  updateTag: "<li class='updateTag' title='Update Tag' data-do='update'></li>"
  mergeTag: "<li class='mergeTag' title='Merge Tag' data-do='merge'></li>"
  convertTag: "<li class='convertTag' title='Convert Keyword' data-do='convert'></li>"
  keywords: ["removeTag","updateTag","mergeTag","convertTag"]
  issuecodes: ["addTag","removeTag","updateTag","moveTag","mergeTag"]
  constructor: (@view,finder="") ->
    if @view.settings.tagging
      @removeFCB()
      @createTaggingCheckboxes(finder)
    if @view.settings.edit
      @removeTaggingCheckboxes()
      @createFCB()

  createTaggingCheckboxes: (finder) ->
    a = @
    @view.cj_selectors.tagBox.find("#{finder} dt .tag .name").before( ->
      if cj(@).siblings(".fCB").length == 0
        a.createButtons(cj(@).parent().parent().data("tagid"))
    )
    @view.toggleCheckInBox()
  removeTaggingCheckboxes: () ->
    @view.cj_selectors.tagBox.find("dt .tag .name .fCB").remove()

  createFCB: () ->
    if !@nodeList?
      @nodeList = @view._trees[291].nodeList
    for k,v of @view._trees
      cjTreeTop = @view.cj_selectors.tagBox.find(".top-#{k}").find("dt")
      cjTreeTop.off("mouseenter")
      cjTreeTop.off("mouseleave")
      cjTreeTop.on("mouseenter", (tag) =>
        cjDT = cj(tag.currentTarget)
        cjDT.find(".tag").append(@createButtons(cjDT.data("tree")))
        @executeButton(cjDT)
      )
      cjTreeTop.on("mouseleave", (tag) =>
        cjDT = cj(tag.currentTarget).find(".tag .fCB")
        cjDT.remove()
      )
  removeFCB: () ->
    for k,v of @view._trees
      cjTreeTop = @view.cj_selectors.tagBox.find(".top-#{k}").find("dt")
      cjTreeTop.off("mouseenter")
      cjTreeTop.off("mouseleave")

  createButtons: (treeTop) ->
    html = "<div class='fCB'>"
    html += "<ul>"
    if @view.settings.edit
      if parseInt(treeTop) == 291
        for tag in @issuecodes
          html += @[tag]
      if parseInt(treeTop) == 296
        for tag in @keywords
          html += @[tag]
    else
      html += "<li>"
      html += _utils.createCheckBox("tag[#{treeTop}]","","checkbox")
      html += "</li>"
    html += "</ul>"
    html += "</div>"
  addRadios: (treeTop) ->
    # "<input type="radio" class="selectRadio" name="selectTag">"
  executeButton: (cjDT) ->
    cjDT.off("click")
    if @view.settings.edit
      cjDT.on("click", "li", (button) =>
        action = cj(button.target).data("do")
        tagid =  cjDT.data("tagid")
        @view.createAction(tagid,action)
      )
    else
      # tagging
      cjDT.on("click", "li", (button) =>
        # cj(button.target).data("do")
      )

class ActivityLog
  constructor: (jsonObj,action) ->
    # console.log jsonObj,action

class Settings
  constructor: (@instance, @view) ->
    @createButtons()
  createButtons: () ->
    @cj_top_settings = cj(".#{@view.menuSelectors.top.split(" ").join(".")} .#{@view.menuSelectors.settings.split(" ").join(".")}")
    @cj_bottom_settings = cj(".#{@view.menuSelectors.bottom.split(" ").join(".")} .#{@view.menuSelectors.settings.split(" ").join(".")}")
    for a in icons.top 
      @cj_top_settings.append(@addButton(a))
    for b in icons.bottom 
      @cj_bottom_settings.append(@addButton(b))
    # onclicks
  icons =
    top: ['setting','add','print']
    bottom: ['slide']

  addButton: (name) ->
    return "<div class='#{name}'></div>"

class Resize
  constructor: (boxHeight) ->
    if boxHeight?
      bbUtils.localStorage("tagBoxHeight",boxheight)
      return boxHeight
    if bbUtils.localStorage("tagBoxHeight")?
      lsheight = bbUtils.localStorage("tagBoxHeight")
      if lsheight.height > 600
       bbUtils.localStorage("tagBoxHeight", 600)
       lsheight.height = 600
      @height = lsheight.height
    else
      @height = 400
  addResize: (@instance,@view) ->
    displaySettings = @instance.get("displaySettings")
    maxHeight = 500
    if displaySettings.maxHeight?
      maxHeight = displaySettings.maxHeight
    @tagBox = @view.cj_selectors.tagBox
    cj(document).on("mouseup", (event,tagBox) =>
      cj(document).off("mousemove")
      if @tagBox.height() < 15
        @tagBox.height(0)
        @tagBox.addClass("dropdown")
      if !@tagBox.hasClass("dropdown")
        bbUtils.localStorage("tagBoxHeight", {height:@tagBox.height()})
      else
        bbUtils.localStorage("tagBoxHeight", {height:0})
    )
    @view.cj_tokenHolder.resize.on("mousedown", (ev,tagBox) =>
      if @tagBox.hasClass("dropdown")
        @tagBox.height(0)
        @tagBox.show()
        @tagBox.removeClass("dropdown")
      ev.preventDefault()
      cj(document).on("mousemove", (ev,tagBox) =>
          if ev.pageY-cj(".JSTree").offset().top < maxHeight
            @tagBox.css("height",ev.pageY-cj(".JSTree").offset().top)
        )
    )


  
class Autocomplete
  constructor: (@instance, @view) ->
    @pageElements = @instance.get 'pageElements'
    @dataSettings = @instance.get 'dataSettings'
    @cjTagBox = cj(".#{@pageElements.tagHolder.join(".")}") unless @cjTagBox?
    cj("#JSTree-data").data("autocomplete" : @instance.autocomplete)
    params =
      jqDataReference: "#JSTree-data"
      hintText: "Type in a partial or complete name of an tag or keyword."
      theme: "JSTree"
    if !@view.settings.wide
      params.hintText = "Search..."
    cjac = cj("#JSTree-ac")
    @hintText(cjac,params)
    searchmonger = cjac.tagACInput("init",params)
    cjac.on "click",((event) =>
      if cjac.val() == params.hintText
        cjac.val("")
        cjac.css("color","#000")
        @initHint = false
    )
    debounced = bbUtils.debounce(@filterKeydownEvents,500)
    a = @
    cjac.on "keydown",((event) ->
      debounced(a,event,searchmonger,cjac)
    )
    cjac.on "keyup", ((event) =>
      keyCode = bbUtils.keyCode(event)
      if keyCode.type == "delete" && cjac.val().length < 3
        @view.removeTabCounts()
        @view.shouldBeFiltered = false
        @view.currentWrittenTerm = ""
        @view.cj_selectors.tagBox.find(".top-292.tagContainer").infiniscroll("unbind", cj(".JSTree"))
        @view.cj_selectors.tagBox.find(".top-292.tagContainer").remove("dt.loadingGif")
        if @view.cj_selectors.tagBox.hasClass("dropdown")
          @view.toggleDropdown()
          @view.rebuildInitialTree()
        else
          @view.rebuildInitialTree()
        if @initHint
          @hintText(cjac,params)
          @initHint = false
        else
          cjac.css("color","#000")
    )
  initHint = true
    
  hintText: (cjac,params) ->
    cjac.val(params.hintText)
    cjac.css("color","#999")

  filterKeydownEvents: (obj, event, searchmonger, cjac) ->
    keyCode = bbUtils.keyCode(event)
    # look at context first.
    # space & enter add tags to list
    # tab and down, shift tab and up are the same
    # end and home and page up/page down work as you'd
    # expect in the dropdown context
    switch keyCode.type
      when "directional"
        return true
        # return @moveDropdown(keyCode.type)
      when "letters","delete","math","punctuation","number"
        if keyCode.type != "delete" then name = keyCode.name  else name = ""
        return obj.execSearch(event,searchmonger,cjac)
      else
        return false
    

  buildPositions: (list,term,hits) ->
    if @positionPagesLeft > 1 
      openLeg = new OpenLeg
      options =
        scrollBox: ".JSTree"
      @cjTagBox.find(".top-292.tagContainer").infiniscroll(options, =>
          @openLegQueryDone = false
          nextPage =
            term: @positionSearchTerm
            page: @positionPage
          @cjTagBox.find(".top-292.tagContainer").append(@addPositionLoader())
          if @cjTagBox.find(".top-292.tagContainer").hasClass('active')
            openLeg.query(nextPage, (results) =>
                poses = @addPositionsToTags(results.results)
                filteredList = {292: poses}
                @getNextPositionRound(results)
                new Tree(poses,"292",false,cj(".JSTree .top-292"))
                @openLegQueryDone = true
                @buildPositions()
            )
      )

  addPositionLoader: () ->
    "<dt class='loadingGif' data-parentid='292'><div class='tag'><div class='ddControl'></div><div class='loadingText'>Loading...</div></div><div class='transparancyBox type-292'></div></dt>"
  execSearch: (event,searchmonger,cjac) ->
    term = cjac.val()
    if term.length >= 3
      @view.shouldBeFiltered = true
      @doOpenLegQuery()
      searchmonger.nExec(event, (terms) =>
        if terms? && !cj.isEmptyObject(terms)
          tags = @sortSearchedTags(terms.tags)
          hits = @separateHits(tags)
          hcounts = 0
          foundTags = []
          # where trees the tags are in
          for k,v of hits
            hcounts += v
            foundTags.push(parseFloat(k))
          filteredList = @view.buildFilteredList(tags)
          @view.writeFilteredList(filteredList, terms.term.toLowerCase(), hits)
          @localQueryDone = true
      )
  doOpenLegQuery:() ->
    openLeg = new OpenLeg
    terms = cj("#JSTree-ac").val()
    openLeg.query({"term":terms}, (results) =>
        # console.log "exec: term: #{terms} #{new Date().getSeconds()}.#{new Date().getMilliseconds()}"
        poses = @addPositionsToTags(results.results)
        filteredList = {292: poses}
        @getNextPositionRound(results)
        if results.seeXmore == 0
          hitCount = (results.results.length*3)
        else
          hitCount = results.seeXmore
        @view.writeFilteredList(filteredList,terms.toLowerCase(),{292: (hitCount)})
        @buildPositions()
        @openLegQueryDone = true
        if @view.cj_selectors.tagBox.hasClass("dropdown")
          @view.toggleDropdown(true)
        )
    # console.log "call: term: #{terms} #{new Date().getSeconds()}.#{new Date().getMilliseconds()}"
    
    # @queryPending.push "#{terms}"
    # if @queryPending.length == 1
    #   bbUtils.throttle.call(@,a,1000)
    # else
    #   bbUtils.throttle.call(@,a,1000*@queryPending.length)
    # console.log @queryPending
  queryPending: []
  separateHits: (terms, results) ->
    hits = {}
    for k, v of terms
      # if v.length > 0
      hits[k] = v.length
    hits[296] = 0 unless hits[296]?
    hits[291] = 0 unless hits[291]?
    hits


  positionIdNumber: 292000

  getNextPositionRound:(results) ->
    @positionPage = results.page + 1
    @positionPagesLeft = results.pagesLeft
    @positionSearchTerm = results.term

  addPositionsToTags: (positions) ->
    format = []
    positionList = @instance.positionList
        # for storedPos in a.instance.positionList
        #   console.log storedPos
        #   if storedPos.name == billno && storedPos.pos == position
        #     action.tagId = storedPos.id
        #     toggleClass.call(@,cjDT)
    for k,o of positions
      # check if position has id, if not. arbitrarily assign one?
      forpos =
        name: o.forname
        id: "#{@positionIdNumber+1}"
        position: "for"
      agipos=
        name: o.againstname
        id: "#{@positionIdNumber+2}"
        position: "against"
      neupos=
        name: o.noname
        id: "#{@positionIdNumber+3}"
        position: "neutral"
      for k,v of @instance.positionList
        if _utils.removePositionTextFromBill(forpos.name) == v.name
          if _utils.checkPositionFromBill(forpos.name) == v.pos
              forpos.id = v.id
        if _utils.removePositionTextFromBill(agipos.name) == v.name
          if _utils.checkPositionFromBill(agipos.name) == v.pos
              agipos.id = v.id
        if _utils.removePositionTextFromBill(neupos.name) == v.name
          if _utils.checkPositionFromBill(neupos.name) == v.pos
              neupos.id = v.id
      forpos.billNo = agipos.billNo = neupos.billNo = o.billNo
      forpos.type = agipos.type = neupos.type = "292"
      forpos.description = agipos.description = neupos.description = o.description
      forpos.children = agipos.children = neupos.children = false
      forpos.created_date = agipos.created_date = neupos.created_date = ""
      forpos.created_id = agipos.created_id = neupos.created_id = ""
      forpos.created_name = agipos.created_name = neupos.created_name = ""
      forpos.parent = agipos.parent = neupos.parent = "292"
      forpos.level = agipos.level = neupos.level = 1
      forpos.url = agipos.url = neupos.url = o.url
      format.push(forpos)
      format.push(agipos)
      format.push(neupos)
      @positionIdNumber = @positionIdNumber + 10
    @positionListing = format 

  sortSearchedTags: (tags) ->
    list = {}
    cj.each tags, (i,el) ->
      if !list[el.type]?
        list[el.type] = []
      obj =
        id: el.id
        name: el.name
      list[el.type].push(obj)
    list

_openTags = {}

_treeVisibility =
  currentTree: ""
  defaultTree: ""
  previousTree: ""

# tree creates new trees
class Tree
  domList: {}
  nodeList: {}
  tabName: ""
  constructor: (@tagList, @tagId, @filter = false, @location) ->
    @buildTree()
    return @
  buildTree: () ->
    if @filter then filter = "filtered" else filter = "" 
    if @location?
      @append = true
      @domList = cj()
      @domList = @domList.add("<div></div>")
    else
      @domList = cj()
      @domList = @domList.add("<div class='top-#{@tagId} #{filter} tagContainer'></div>")
    @iterate(@tagList)
  # setHover: () ->
    # console.log @nodeList
    
  iterate: (ary) ->
    cjTagList = cj(@domList)
    for node in ary
      @nodeList[node.id] = kNode = new Node(node)
      if node.parent == @tagId
        cjTagList.append(kNode.html)
      else
        cjToAppendTo = cjTagList.find("dl#tagDropdown_#{kNode.parent}")
        if cjToAppendTo.length == 0
          cjTagList.append(kNode.html)
        else
          cjToAppendTo.append(kNode.html)
      # if parent exists attach to parent
      # if parent doesn't exist, attach to list
    if !@append
      cjTagList.appendTo(".JSTree")
    else
      @location.find(".loadingGif").replaceWith(cjTagList)
    @html = cjTagList
    _treeUtils.makeDropdown(cj(".JSTree .top-#{@tagId}"))
    if @filter
      buttons = cj(".JSTree .top-#{@tagId} .treeButton").parent().parent()
      cj.each(buttons, (i,button) =>
        _treeUtils.dropdownItem(cj(button),true)
      )
    else
      _treeUtils.readDropdownsFromLocal(@tagId,@tagList)

_treeUtils =
  selectByParent: (list, parent) ->
    childList = [] 
    for b in list
      if b.parent == parent
        childList.push b
    childList
  selectByTree: (list, tree) ->
    treeList = [] 
    for b in list
      if b.type == tree
        treeList.push b
    treeList
  makeDropdown: (cjTree) ->
    cjTree.find(".treeButton").off "click"
    cjTree.find(".treeButton").on "click", ->
      _treeUtils.dropdownItem(cj(@).parent().parent())
  dropdownItem: (tagLabel, search = false) ->
    tagid = tagLabel.data('tagid')
    if tagLabel.length > 0
      if tagLabel.is(".open")
        _openTags[tagid] = false
      else
        _openTags[tagid] = true
    tagLabel.siblings("dl#tagDropdown_#{tagid}").slideToggle "200", =>
      tagLabel.toggleClass "open"
    if !search
      bbUtils.localStorage("tagViewSettings", _openTags)

  readDropdownsFromLocal: (cjTree) ->
    if parseInt(cjTree) == 291
      if bbUtils.localStorage("tagViewSettings")    
        _openTags = bbUtils.localStorage("tagViewSettings")
        for tag, bool of bbUtils.localStorage("tagViewSettings")
          if bool
            toPass = cj("dt.tag-#{tag}")
            @dropdownItem toPass
          else
            delete _openTags[tag]
      else
      _openTags



_descWidths = 
  normal: 75
  long: 150


class Node
  constructor: (node) ->
    @data = node
    @parent = node.parent
    @hasDesc = ""
    @description = node.descriptf_ion
    @descLength(node.description)
    @id = node.id
    @children = node.children
    @name = node.name
    @nameLength = ""
    @billNo = node.billNo
    if node.type == 292
      @billNo ?= node.name
      @name = node.posName
    @billNo ?= ""
    @position = node.position
    @position ?= node.pos
    @position ?= ""
    if @name.length > _descWidths.normal
      levelModifier = 0
      if node.level > 2
        levelModifier = node.level*5
      @name = _utils.textWrap(@name, (_descWidths.normal - levelModifier) )
      @name = @name.toRet.join('<br />')
      @nameLength = "longName"
    @name = cj.trim(@name)
    @html = @html(node)
    return @
  descLength: (@description) ->
    if @description?
      if @description.length > 0
        desc = _utils.textWrap(@description, _descWidths.normal)
        if desc.segs == 1
          @hasDesc = "description shortdescription"
        if desc.segs == 2
          @hasDesc = "description"
        if desc.segs >= 3
          @hasDesc = "longdescription"
        if desc.segs > 3
          tempDesc = ""
          for text,i in desc.toRet
            tempDesc += "#{text}<br />"
            if i >= 2
              break
          @description = tempDesc
        else
          if desc.segs > 1
            @description = desc.toRet.join("<br />")
          else
            @description = desc.toRet[0]
  html: (node) ->
    if node.children then treeButton = "treeButton" else treeButton = ""
    if parseFloat(node.is_reserved) != 0 then @reserved = true  else @reserved = false
    # dt first
    html = "<dt class='lv-#{node.level} #{@hasDesc} tag-#{node.id} #{@nameLength}' id='tagLabel_#{node.id}'
             data-tagid='#{node.id}' data-tree='#{node.type}' data-name='#{node.name}' 
             data-parentid='#{node.parent}' data-billno='#{@billNo}'
             data-position='#{@position}'
            >"
    html += "
              <div class='tag'>
                <div class='ddControl #{treeButton}'></div>
                <div class='name'>#{@name}</div>
            "
    if @hasDesc.length > 0
      html += "
                <div class='description'>#{@description}</div>
            "
    html += "
              </div>
              </dt>
            " 
    # dl second
    html += "
              <dl class='lv-#{node.level}' id='tagDropdown_#{node.id}' data-tagid='#{node.id}' data-name='#{node.name}'></dl>
            "
    return html
  # add
  # remove
  # update
  # merge
  # move
  # convert

  # nodes have processes
  # nodes have data
  # nodes have name
  # nodes don't have html in the tree
