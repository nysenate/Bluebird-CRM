window.jstree["views"] =
  exec: (instance) ->
    @view = new View(instance)
    @menuSettings = new Settings(instance,@view)
  done: (instance) ->
    trees = {}
    displaySettings = instance.get 'displaySettings'
    for a,v of instance.treeNames
      if a != "292"
        b = _treeUtils.selectByTree(instance.autocomplete, a)
        trees[a] = new Tree(b,a)
      else
        b = instance.positionList
        if displaySettings.edit
          trees[a] = new Tree(b,a)
        else
          trees[a] = new Tree(b,a,false,"init")
    @view.trees = trees
    @view.init()
    if @view.settings.tall && !@view.settings.lock
      resize = new Resize
      resize.addResize(instance,@view)
    else
      # TODO - instead of remove make sure that tagHolers don't
      # delete on dropdown
      @view.cj_tokenHolder.resize.remove()
  changeEntity: (entity_id) ->
    # get entity id from instance
    @view = jstree.view
    @view.entity_id = entity_id
    @view.applyTagged()

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
    dropdown:false
  entity_id: 0
  entityList: []
  defaultPrefix: "JSTree"
  prefixes: []
  defaultTree: 0
  descWidths:
    normal: 75
    long: 150
  # starts the chain to write the page structure
  constructor: (@instance) ->
    @writeContainers()
  # applies tags to entity, by procuring them from instance.getEntity,
  applyTagged:() ->
    @instance.getEntity(@entity_id, (tags) =>
      if @entityList.length > 0
        @removeAllTagsFromEntity()
        @cj_tokenHolder.body.empty()
      @entityList = tags
      if @entityList.length > 0
        @applyTaggedKWIC()
        @applyTaggedPositions()
        for id in @entityList
          @addTagsToHolder(id)
        @cj_tokenHolder.options.find(".showToggle").off "click"
        @cj_tokenHolder.options.find(".showToggle").on "click", =>
          @slideTokenHolder()
        if @cj_tokenHolder.box.hasClass("closed")
          curHeight = @cj_tokenHolder.box.height()
          @cj_tokenHolder.box.css('height','auto')
          autoHeight = @cj_tokenHolder.box.height()
          @cj_tokenHolder.box.height(curHeight)
          @cj_tokenHolder.box.animate({height:autoHeight},100, =>
            @cj_tokenHolder.box.css('height','auto')
            @cj_tokenHolder.box.removeClass("closed") 
            @slideTokenHolder() if @settings.dropdown
          )
      )
  slideTokenHolder: () ->
    if @cj_tokenHolder.body.height() <= 20 
      @cj_tokenHolder.options.find(".showToggle").toggleClass("slideUp")
      curHeight = @cj_tokenHolder.body.height()
      @cj_tokenHolder.body.css('height','auto')
      autoHeight = @cj_tokenHolder.body.height()
      @cj_tokenHolder.body.height(curHeight)
      @cj_tokenHolder.body.animate({height:autoHeight},500, =>
      )
    else
      @cj_tokenHolder.options.find(".showToggle").toggleClass("slideUp")
      @cj_tokenHolder.body.animate({height:"16px"},500, =>
      )
    # @cj_tokenHolder.body.slideToggle(400)
  addTagsToHolder:(id) ->
    cjDT = @cj_selectors.tagBox.find("#tagLabel_#{id}")
    killToken = =>
      cjDT.find("input.checkbox").prop("checked",false).trigger("change")
    name = cjDT.data("name")
    type = cjDT.data("tree")
    # @cj_tokenHolder.body
    token = new Token(@cj_tokenHolder.body)
    token.create(@cj_tokenHolder.body,name,type,id,killToken)
  removeAllTagsFromEntity:() ->
    cjDTs = @cj_selectors.tagBox.find("dt")
    cjDTs.find("dt").removeClass("shaded").removeClass("shadedChildren")
    cjDTs.find("dt input.checkbox").prop("checked",false)
  # uses current view.entityList and applies that to the view
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
  # turns the arbitrarily ID's incoming positions into their real, local DB value
  # based on procuring the values from the DB and applying them
  # based on their name, i.e. "S2953-2013", and by removing the position from them
  # via regex
  findPositionLocalMatch:(cjDT) ->
    name = cjDT.find(".tag .name").text
    for a,b of @instance.positionList
      name = _utils.removePositionTextFromBill(cjDT.name)
      position = cjDT.data("position")
  # tags the positions, as opposed to applyTaggedKWIC on initial load
  # to show all currently tagged positions on an entity
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
    else
      @addPositionReminderText(@cj_selectors.tagBox.find(".top-292"))

  # controls the writers for the initial HTML to create the box
  writeContainers: () ->
    @formatPageElements()
    @createSelectors()
    tagBox = new Resize
    if tagBox?
      if tagBox.height == 0
        @setDescWidths(false,undefined)
      else
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
  # checks on if the settings describe the box as tall or wide
  # and sets the widths for the text processer (_utils.textWrap)
  # to follow when it writes tags
  setDescWidths: (tall,wide) ->
    if !tall?
      tall = @settings.tall
    if !wide?
      wide = @settings.wide
    # changes the maximum text with in menus
    if tall
      if wide
        _descWidths.normal = 75
        _descWidths.long = 150
      else
        _descWidths.normal = 38
        _descWidths.long = 38
    else
      if wide
        _descWidths.normal = 70
        _descWidths.long = 140
      else
        _descWidths.normal = 38
        _descWidths.long = 38
  # writes the html for a not tall dropdown box
  buildDropdown: () ->
    @cj_selectors.initHolder.html "<div class='#{@selectors.tagBox} dropdown'></div><div class='JSTree-overlay'></div>"
    @cj_selectors.initHolder.prepend(@menuHtml(@menuSelectors))
    @cj_selectors.initHolder.append(@dataHolderHtml())
    @cj_selectors.initHolder.append(@tokenHolderHtml(@tokenHolder))
    @cj_selectors.initHolder.removeClass(@selectors.initHolder).attr("id", @selectors.container).addClass(@selectors.containerClass)
  # writes the html for a tall scrollbox
  addClassesToElement: (height) ->
    @cj_selectors.initHolder.html "<div class='#{@selectors.tagBox}' #{height}></div><div class='JSTree-overlay'></div>"
    @cj_selectors.initHolder.prepend(@menuHtml(@menuSelectors))
    @cj_selectors.initHolder.append(@dataHolderHtml())
    @cj_selectors.initHolder.append(@tokenHolderHtml(@tokenHolder))
    @cj_selectors.initHolder.removeClass(@selectors.initHolder).attr("id", @selectors.container).addClass(@selectors.containerClass)
  # initializes and sets up variables for the view to use
  # along with @cj_xyz shortcuts for cached jquery variables
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
  # util function that should be moved to _utils
  # writes out the prefix 'BBTree' on all CSS classes
  # used for custom theming based on location (via display settings)
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
  # iterator for createCJfrom Obj
  createSelectors: () ->
    for v in @settingCollection
      @createCJfromObj(@[v],v)
  # smaller function of createSelctors, useful for only
  # creating cj updates for a single object branch
  createCJfromObj: (obj, name) ->
    cjed = {}
    for k,v of obj
      continue if typeof(v) != "string" or v.length == 0
      selectorType = "."
      if obj.idedKeys?
        selectorType = "#" if obj["idedKeys"].indexOf(k) >= 0
      cjed[k] = cj("#{selectorType}#{cj.trim(v).replace(/\ /g, ".")}")
    @["cj_#{name}"] = cjed
  # sets the page css for narrow/short
  setByHeightWidth: () ->
    ret = ""
    ret += "narrow " unless @settings.wide
    ret += "short" unless @settings.tall
    ret

  # html for menu. should be moved to its own class
  # you can use __super__ to overwrite the html if you have to
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
  # html for token. should be moved to its own class
  # you can use __super__ to overwrite the html if you have to
  tokenHolderHtml: (name) ->
    # <div class='#{name.left}'></div>
    html = "
        <div class='#{name.box} closed'>
         <div class='#{name.options}' >
          <div class='showToggle' title='Toggle Tokens'></div>
         </div>
         <div class='#{name.body}'></div>
        </div>
        <div class='#{name.resize}'></div>
      "
  # html for where the 'tags' live. should be moved to its own class
  # you can use __super__ to overwrite the html if you have to
  dataHolderHtml: () ->
    return "<div id='JSTree-data' style='display:none'></div>"
  
  # init is called on execution of the inital response of tag data from the server
  # once it's been processed, it re-executes the selectors and updates them
  # based on changes in the dom
  init:() ->
    @createSelectors()
    _treeVisibility.currentTree = _treeVisibility.defaultTree = _treeVisibility.previousTree = @settings.defaultTree
    for k,v of @instance.treeNames
      tabName = @createTreeTabs(v)
    @setActiveTree(@settings.defaultTree)
    ac = new Autocomplete(@instance, @)
    @createTabClick()
    buttons = new Buttons(@)
    @setTaggingOrEdit()
  # sets tagging functions or edit functions
  # depending on if the settings call for tagging or edit
  # you can write accessors (like in jstree["views"] to, post load, change this data)
  # or your own accessor class
  setTaggingOrEdit: () ->
    if @cj_selectors.tagBox.hasClass("tagging,edit")
      @cj_selectors.tagBox.removeClass("tagging").removeClass("edit")
    if @settings.edit && @settings.tagging
      @settings.tagging = false
    if @settings.edit
      @initPositionList(@cj_selectors.tagBox.find(".top-292"))
      @cj_selectors.tagBox.addClass("edit")
    if @settings.tagging
      @cj_selectors.tagBox.addClass("tagging")
      @applyTagged()
  initPositionList:(cjlocation) ->
    positionText = "
              <div class='position-box-text-reminder'>
                These positions are in your instance.
              </div>
          "
    cjlocation.prepend(positionText)
  unbindTabClick: () ->
    for k,v of @instance.treeNames
      @cj_menuSelectors.tabs.find(".tab-#{@getTabNameFromId(k,true)}").off "click"
  # jquery for functionality when you click on a menu tab
  createTabClick: () ->
    @unbindTabClick()
    onTabClick = (currentTree,tabName) ->
      @cj_menuSelectors.tabs.find(".#{tabName}").on "click", =>
        @showTags currentTree,tabName
    cj.each(@cj_menuSelectors.tabs.find("div"), (i,div) ->
      cj(div).off "click"
    )
    for k,v of @instance.treeNames
      tabName = "tab-#{@getTabNameFromId(k,true)}"
      onTabClick.call(@,k,"#{tabName}")
      if parseInt(k) == 292 && !@settings.tagging
        @addPositionReminderText(@cj_selectors.tagBox.find(".top-#{k}"))
    
  # changes shown tags based on current tree and previous tree.
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
      @setOverlay()
  # overlay is a 'color' box which provides the coloring for the backgrounds
  # and the dropdown white background-height for variable height dropdowns
  # it's important because you won't have a white background all the time
  # and the dropdown floats over text, and is transparent
  setOverlay:() ->
    if @cj_selectors.tagBox.hasClass("dropdown")
      cjOverlay = @cj_selectors.container.find(".JSTree-overlay")
      cjOverlay.height(@cj_selectors.tagBox.height())
      cjOverlay.width(@cj_selectors.tagBox.width())
    else
      cjOverlay = @cj_selectors.container.find(".JSTree-overlay")
      cjOverlay.css("height","100%")
      cjOverlay.css("width","100%")
  # sets the "active" tree, visible, and the tab to be declared "active
  setActiveTree: (id) ->
    tabName = @getTabNameFromId(id,true)
    @cj_menuSelectors.tabs.find("div").removeClass("active")
    @cj_selectors.tagBox.find(".tagContainer").removeClass("active").css("display","none")
    @cj_menuSelectors.tabs.find(".tab-#{tabName}").addClass("active")
    @cj_selectors.tagBox.find(".top-#{id}").addClass("active").css("display","block")
    @cj_selectors.tagBox.addClass("top-#{id}-active")
  # writes the tabs HTML based on # of tabs.
  createTreeTabs: (tabName, isHidden = false) ->
    if isHidden then style = "style='display:none'" else style = ""
    tabClass = (_utils.hyphenize(tabName)).toLowerCase()
    output = "<div class='tab-#{tabClass}' #{style}>#{tabName}</div>"
    @cj_menuSelectors.tabs.append(output)
  # utility class. gets the tab name (issue-codes) from tree id (291)
  getTabNameFromId: (id, hyphenize = false) ->
    treeNames = @instance.treeNames
    return treeNames[id] unless hyphenize
    return _utils.hyphenize(treeNames[id]).toLowerCase()
  # utility class. gets the tab id (291) from tab-name ("tab-issue-codes")
  getIdFromTabName: (tabName) ->
    tabName = cj.trim(tabName)
    return 291 if tabName == "tab-issue-codes" or tabName == "issue-codes"
    return 296 if tabName == "tab-keywords" or tabName == "keywords"
    return 292 if tabName == "tab-positions" or tabName == "positions"
  # builds a filtered list of the tags that are to be written
  # and makes sure there's no duplication
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
  # might be unnecessary, should deprecate
  shouldBeFiltered: false
  currentWrittenTerm: ""
  queryLog:
    "291": []
    "296": []
    "292": []

  # i thing this is incorrectly implemented
  # should memoize the query results and return them
  createQueryLog: (term,tree) ->
    if @queryLog[tree].lastIndexOf(term) < 0
      @queryLog[tree].push term
    for k,v of @queryLog
      if v.length > @queryLog[tree].length
        return false
    return true

  # writes the list of tags to be added (291:array_of_objs,296:array_of_objs)
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
        # @writeEmptyList(term,k)
        @cj_selectors.tagBox.find(".top-#{k}").data("term",term)
      else
        @setTabResults(k,v)
        t = new Tree(list[k],k,true)
        @cj_selectors.tagBox.find(".top-#{k}").data("term",term)
      if @settings.tagging
        if parseInt(k) == 292
          if v > 0
            # not entirely sure why this exists
            # i suppose that adding shading to positions indicates it's a local
            # one?
            delay = (ms, func) -> setTimeout func, ms
            delay(100, =>
              for a,b of @instance.positionList
                iO = b.id.indexOf(@entityList)
                if iO > -1
                  cjDTs = @cj_selectors.tagBox.find("#tagLabel_#{b.id}")
                  cjDTs.addClass("shaded")
                  # cjDTs.find(".fCB input.checkbox").prop("checked",true)
            )

    new Buttons(@)
    if @settings.tagging
      if @entityList?
        @applyTaggedKWIC()
      else
        # should really do a full check, but i'm lazy
        delay = (ms, func) -> setTimeout func, ms
        delay(500, => @applyTaggedKWIC())

    @setActiveTree(@getIdFromTabName(activeTree))

  # what it says, but i don't think it's implemented
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

  # when you go to zero length of the text input, you should go back to 
  # the initial state of the tree, which uses memoized trees to
  # quickly shift back and forth
  rebuildInitialTree: () ->
    if @cj_selectors.tagBox.hasClass("filtered")
      @cj_selectors.tagBox.removeClass("filtered")
      @cj_selectors.tagBox.find(".filtered").remove()
      activeTree = @cj_menuSelectors.tabs.find(".active").attr("class").replace("active","")
      for k,v of @trees
        if parseInt(k) != 292
          t = new Tree(v.tagList, k)
        if parseInt(k) == 292 && !@settings.tagging
          t = new Tree(v.tagList, k)
          # @cj_selectors.tagBox.find(".top-#{k}").empty()
          @initPositionList(@cj_selectors.tagBox.find(".top-#{k}"))
          # @addPositionReminderText(@cj_selectors.tagBox.find(".top-#{k}"))
        if parseInt(k) == 292 && @settings.tagging
          # fix this here to write entity settings
          t = new Tree(v.tagList, k,false,"init")
          @cj_selectors.tagBox.find(".top-#{k}").empty()
          if @entityList.length == 0
            @addPositionReminderText(@cj_selectors.tagBox.find(".top-#{k}"))
          else
            @applyTaggedPositions()
      new Buttons(@)
      if @settings.tagging
        @applyTaggedKWIC()
      @setActiveTree(@getIdFromTabName(activeTree))
  # sets the 'hits' result that a query returns to the tabs
  setTabResults: (tree,val) ->
    cjTab = @cj_menuSelectors.tabs.find(".tab-#{@getTabNameFromId(tree, true)}")
    if cjTab.find("span").length > 0
      cjTab.find("span").html("(#{val})")
    else
      result = cjTab.html()
      cjTab.html("#{result}<span>(#{val})</span>")
  # removes the tab count for rebuild tree situations
  removeTabCounts: (id) ->
    if id?
      @cj_menuSelectors.tabs.find(".#{} span").remove()
    else
      @cj_menuSelectors.tabs.find("span").remove()

  # this is useful on 
  addPositionReminderText: (cjlocation) ->
    positionText = "
              <div class='position-box-text-reminder'>
                Type in a Bill Number or Name for Results
              </div>
          "
    cjlocation.html(positionText)

  # shortcut for the toggleClass on a tagbox
  # TODO: this is probably what breaks the page on weird queries
  toggleTagBox: () ->
    @cj_selectors.tagBox.toggle().toggleClass("dropdown")
  # this turns on/off the dropdown based on the tagging/edit functionality
  toggleDropdown: (hits) ->
    if @cj_selectors.tagBox.hasClass("dropdown")
      if hits?
        for k,v of hits
          @getTagHeight(@cj_selectors.tagBox.find(".top-#{k}"))
        @cj_selectors.container.css("position","static").css("border-bottom-width","0px").css("border-right-width","0px")
        @cj_selectors.tagBox.css("height","auto").addClass("open").css("overflow-y","auto")
        @cj_selectors.tagBox.css("border-right","1px solid #ccc")
        @cj_menuSelectors.bottom.find(".JSTree-settings").css("border-bottom","1px solid #bbb")
        @setOverlay()
      else
        boxHeight = new Resize()
        @cj_selectors.container.css("position","relative").css("border-bottom-width","1px").css("border-right-width","1px")
        @cj_selectors.tagBox.removeClass("open").css("overflow-y","scroll").height(boxHeight.height)
        @cj_selectors.tagBox.css("border-right","0px")
        @cj_menuSelectors.bottom.find(".JSTree-settings").css("border-bottom","0px")
        @setOverlay()
    @setOverlay()
  # queries the height of the tag box so that it can inform the selector
  # how large the tags are in the box, based on their DOM height
  getTagHeight:(tagBox,maxHeight = 180) ->
    checkDTs = []
    heightTotal = @getRecTagHeight(tagBox)
    propHeight = 0
    for v in heightTotal
      propHeight += parseInt(v)
    if propHeight > maxHeight
      closestTo = 0
      for v in heightTotal
        if closestTo > maxHeight
          break
        closestTo += parseInt(v)
      return cj(tagBox).height(closestTo)
    else
      return cj(tagBox).height(propHeight)

  # if there's more than 8 elements, show only those 8
  # i think this is primarily to not cut off individual
  # tags?
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
  
  # this is view's action wrapper
  createAction: (tagId="",action,cb) ->
    new Action(@,@instance,tagId,action,cb)

  # creates the event bindings for tag checkboxes
  # when it's checked, then it calls the internal actions
  # relating to the status
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
        a.addTagsToHolder(cjDT.data("tagid"))
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
              # console.log "response false"
            else
              action.tagId = response["message"]["id"]
              toggleClass.call(newDT.find("input.checkbox")[0],newDT)
          )
      else
        tagId = cjDT.data("tagid")
        action.tagId = tagId
        toggleClass.call(@,cjDT)
    )
  # checks to see if a given tag has descendants that are checked as well
  # so we can provide inheritance tracking
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


# action creates the events required to interact with tag manipulation
# not entity manipulation
class Action
  ajax:
    addTag:
      url: '/civicrm/ajax/tag/create'
      data:
        name: ""
        description: ""
        parent_id: ""
        is_reserved: true
    removeTag:
      url: '/civicrm/ajax/tag/delete'
      data:
        id: ""
    updateTag:
      url: '/civicrm/ajax/tag/update'
      data:
        name: ""
        description: ""
        id: ""
        is_reserved: true
    moveTag:
      url: '/civicrm/ajax/tag/update'
      data:
        id: ""
        parent_id: ""
  fields:
    addTag: ["Tag Name","Description","Is Reserved"]
    removeTag: []
    updateTag: ["Tag Name","Description","Is Reserved"]
    moveTag: []
  requiredFields:
    addTag: ["Tag Name"]
    removeTag: []
    updateTag: ["Tag Name"]
    moveTag: []
  requiredValidation:
    addTag: ["isRequired","appliesNullToText"]
    removeTag: ["noChildren"]
    updateTag: ["isRequired","appliesNullToText"]
    moveTag: ["noChildren"]
  # constructor uses name based applications to call functions
  # so you only have to remember to call new Action
  constructor: (@view, @instance, @tagId, action, @cb) ->
    for k,v of @ajax
      v.data["call_uri"] = window.location.href
      v["dataType"] = "json"
    @cjDT = @view.cj_selectors.tagBox.find("dt[data-tagid='#{@tagId}']")
    @tagName = @cjDT.data("name")
    @[action].apply(@,[action])
  # createSlide pulls a slider from right side to provide a platform for editing tags
  # if it's tall enough, if not, uses the bottom.
  createSlide: (cb) ->
    # remove the ability to swap tabs
    resize = new Resize
    if resize.height > 190
      @view.cj_selectors.tagBox.addClass("hasSlideBox")
      @view.cj_selectors.tagBox.prepend("<div class='slideBox'></div>")
      @bottom = false
      # memoize this
      @cj_slideBox = @view.cj_selectors.tagBox.find(".slideBox")
      @cj_slideBox.css("right","#{@findGutterSpace()}px")
      if @view.settings.wide then slideWidth = '40%' else sideWidth = '60%'

      @cj_slideBox.animate({width:slideWidth}, 500, =>
        @cj_slideBox.append(@slideHtml)
        @setCancel()
        cb()
      )
    else
      @bottom = true
      containerHeight = @view.cj_selectors.container.height()
      menuHeight = @view.cj_menuSelectors.menu.height()
      activeTreeId = @view.cj_selectors.tagBox.find(".tagContainer.active").data("treeid")
      offset = -(containerHeight - menuHeight + 15);
      @view.cj_selectors.container.after("<div class='JSTree-slideBox'><div class='slideBox top-#{activeTreeId}'></div></div>")
      @cj_slideBoxContainer = cj(".JSTree-slideBox")
      @cj_slideBoxContainer.css("top","#{offset}px")
      @cj_slideBox = @cj_slideBoxContainer.find(".slideBox")
      @cj_slideBox.animate({height:"210px"}, 500, =>
        @cj_slideBox.append(@slideHtml)
        @setCancel()
        cb()
      )
      # it adds a dropdown
  setCancel:() ->
    @cj_slideBox.find(".label.cancel").off "click"
    @cj_slideBox.find(".label.cancel").on "click", =>
      @destroySlideBox()

  destroySlideBox:() ->
    @cj_slideBox.empty()
    if @bottom
      animate = {height:"0px"}
    else
      animate = {width:"0%"}
    @cj_slideBox.animate(animate, 500, =>
      @cj_slideBox.find(".label.cancel").off "click"
      @cj_slideBox.remove()
      @view.cj_selectors.tagBox.removeClass("hasSlideBox")
      @view.createTabClick()
      if @cj_slideBoxContainer?
        @cj_slideBoxContainer.remove()
    )
  # creates a tag from thin air, for positions.
  # this should be broken up into separate non-private functions
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
      # need to figure out a better way to implement this?
      if _utils.removePositionTextFromBill(@ajax.addTag.data.name) == v.name
        if _utils.checkPositionFromBill(@ajax.addTag.data.name) == v.pos
          manipBox.call(@,cjDT.data("tagid"),v.id)
          message =
            id: v.id
          response = {"cjDT": cjDT,"message":message}
          @cb(response)
    @tagAjax(tagId,"addTag",action, (message) =>
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
  # finds the gutter space for active tag containers
  # so that create slide doesn't overlap the scroll bar 
  findGutterSpace: () ->
    outerWidth = @view.cj_selectors.tagBox.width()
    innerWidth = @view.cj_selectors.tagBox.find(".tagContainer.active").width()
    return outerWidth-innerWidth
  # destructive method
  setRequiredFields: (type) ->
    reqFields = @requiredFields[type]
    @requiredFields = []
    for i in reqFields
      @requiredFields.push _utils.camelCase(i)
    @requiredValidation = @requiredValidation[type]
  # values provides a shell for updateTag to use same field
  # but add in values
  addTag: (values="") ->
    @slideHtml = @gatherLabelHTML()
    @setRequiredFields("addTag")
    @createSlide(=>
      @view.unbindTabClick()
      doSubmit = =>
        @submitButton(true,(data) =>
          @removeErrors(data)
          # if there's errors
          if bbUtils.objSize(data.errors) > 0
            @markErrors(data.errors)
          else
            @ajax.addTag.data.name = data.fields.tagName
            @ajax.addTag.data.description = data.fields.description
            @ajax.addTag.data.is_reserved = data.fields.isReserved
            @ajax.addTag.data.parent_id = data.tagId
            @convertSubmitToLoading()
            # undefined middle callback for some reason.
            @tagAjax(data.tagId, "addTag", undefined, (message) =>
              if message == "DB Error: already exists"
                @revertSubmitFromLoading()
                @markErrors({tagName:"Tag #{data.fields.tagName} already exists."})
                doSubmit.call(@)
              else
                @addEntityToTree(data.tagId,message)
                @revertSubmitFromLoading()
                @destroySlideBox()
            )
          )
      doSubmit.call(@)
    )
  removeTag: () ->
    @slideHtml = @gatherRemoveLabelHTML()
    @setRequiredFields("removeTag")
    @createSlide(=>
      @view.unbindTabClick()
      doSubmit = =>
        @submitButton(true,(data) =>
          @removeErrors(data)
          # if there's errors
          if bbUtils.objSize(data.errors) > 0
            @markErrors(data.errors)
          else
            @ajax.removeTag.data.id = data.tagId
            @convertSubmitToLoading()
            # undefined middle callback for some reason.
            @tagAjax(data.tagId,"removeTag", undefined, (message) =>
              if message == "DB Error: already exists"
                @revertSubmitFromLoading()
                @markErrors({tagName:"Tag #{data.fields.tagName} cannot be removed."})
                doSubmit.call(@)
              else
                @removeEntityFromTree(data.id)
                @revertSubmitFromLoading()
                @destroySlideBox()
            )
        )
      doSubmit.call(@)
    )

  moveTag: () ->
    @slideHtml = @gatherMoveLabelHTML()
    @setRequiredFields("moveTag")
    @createSlide(=>
      @view.unbindTabClick()
      doSubmit = =>
        @submitButton(true,(data) =>
          @removeErrors(data)
          # if there's errors
          if bbUtils.objSize(data.errors) > 0
            @markErrors(data.errors)
          else
            @ajax.moveTag.data.parent_id = data.parent_id
            @ajax.moveTag.data.id = data.id
            @convertSubmitToLoading()
            # undefined middle callback for some reason.
            # @tagAjax(data.tagId,"moveTag", undefined, (message) =>
            #   if message == "DB Error: already exists"
            #     @revertSubmitFromLoading()
            #     @markErrors({tagName:"Tag #{data.fields.tagName} cannot be removed."})
            #     doSubmit.call(@)
            #   else
            #     @removeEntityFromTree(data.id)
            #     @revertSubmitFromLoading()
            #     @destroySlideBox()
            # )
        )
      doSubmit.call(@)
    )
  mergeTag: () ->
    @createSlide()
  updateTag: () ->
    checked = ""
    checked = "checked" if @cjDT.data("isreserved") == 1
    values =
      tagName: @cjDT.data("name")
      description: @cjDT.find(".tag .description").text()  || ""
      isReserved: checked
    @slideHtml = @gatherUpdateLabelHTML(values)
    @setRequiredFields("updateTag")
    @createSlide(=>
      @view.unbindTabClick()
      doSubmit = =>
        @submitButton(true,(data) =>
          @removeErrors(data)
          # if there's errors
          if bbUtils.objSize(data.errors) > 0
            @markErrors(data.errors)
          else
            @ajax.updateTag.data.name = data.fields.tagName
            @ajax.updateTag.data.description = data.fields.description
            @ajax.updateTag.data.is_reserved = data.fields.isReserved
            @ajax.updateTag.data.id = data.tagId
            @convertSubmitToLoading()
            # undefined middle callback for some reason.
            @tagAjax(data.tagId, "updateTag", undefined, (message) =>
              if message == "DB Error: already exists"
                @revertSubmitFromLoading()
                @markErrors({tagName:"Tag #{data.fields.tagName} already exists."})
                doSubmit.call(@)
              else
                @updateEntity(message)
                @revertSubmitFromLoading()
                @destroySlideBox()
            )
          )
      doSubmit.call(@)
    )
  addEntityToTree: (parent,message) ->
    node = {}
    if message.created_date?
      node.created_date = _manipTags.createDate(message.created_date)
    node.name = message.name if message.name?
    if message.description?
      if message.description == null
        node.description = ""
      else
        node.description = message.description 
    node.parent = node.parent_id = message.parent_id if message.parent_id?
    node.id = message.id if message.id?
    node.children = false
    cjParent = @view.cj_selectors.tagBox.find("dt#tagLabel_#{parent}")
    node.level = cjParent.data("level")+1
    node.type = "#{cjParent.data("tree")}"
    if message.is_reserved = "false" then node.is_reserved = "0" else node.is_reserved = "1"
    # get node
    node_parsed = new Node(node)
    # append the node to the autocomplete & tree
    @instance.appendToAC(node)
    backout = @view.trees[parseInt(node.type)].appendNode(node,node.parent_id,node_parsed.html)
    if !backout
      console.log "bad things happened"
      return false
    new Buttons(@view,"#tagLabel_#{node.id}")
  removeEntityFromTree: () ->
    id = @cjDT.data("tagid")
    cjDL = @view.cj_selectors.tagBox.find("dl#tagDropdown_#{id}")
    nodeType = @cjDT.data("tree")
    @instance.removeFromAC(id)
    @view.trees[parseInt(nodeType)].removeNode(id)
  updateEntity: (message) ->
    data = {}
    id = message.id
    data.id = "#{id}"
    data.is_reserved = "0"
    if message.is_reserved == "true" or message.is_reserved == true
      data.is_reserved = "1"
    cjDT = @view.cj_selectors.tagBox.find("dt#tagLabel_#{id}")
    cjDL = @view.cj_selectors.tagBox.find("dl#tagDropdown_#{id}")
    data.parent_id = "#{cjDT.data('parentid')}"
    data.children = false
    if cjDL.children().length > 0
      data.children = true
    data.level = parseInt(cjDT.data("level"))
    data.type = "#{cjDT.data("tree")}"
    data.name = message.name
    data.description = message.description
    
    # major hack. nodes should be centrally accessable instead as an easy 'get' from the tree
    node = Object.getPrototypeOf(@view.trees[cjDT.data("tree")]).nodeList[id]
    settings = cj.extend({},node.data,data)
    node.setValues(settings)
    
    backout = @view.trees[parseInt(data.type)].appendNode(settings,settings.parent_id,node.html,true)
    @instance.appendToAC(settings)
    
    # now we remodify it
    cjNode = cj("<div>#{node.html}</div>")
    cjNode.find("#tagLabel_#{id}").addClass("open") if cjDT.hasClass("open")
    if node.children
      nodeDL = cjNode.find("#tagDropdown_#{id}")
      nodeDL.replaceWith(cjDL[0].outerHTML)
    cjDL.remove()
    cjDT.replaceWith(cjNode.html())
    _treeUtils.makeDropdown(@view.cj_selectors.tagBox.find(".top-#{data.type}"))
    new Buttons(@view,"#tagDropdown_#{id}")
    new Buttons(@view,"#tagLabel_#{id}")
  removeErrors: (data)->
    notErrored = []
    for k,v of data.fields
      unless data.errors[k]?
        notErrored.push k
    for nowGood in notErrored
      cjEl = @cj_slideBox.find(".label.#{nowGood}")
      oldName = cjEl.data("oldname")
      cjEl.removeClass("errorLabel").text(oldName)
      @cj_slideBox.find("input[name='#{nowGood}']").removeClass("errorBox")
  markErrors: (errors) ->
    for k,v of errors
      cjEl = @cj_slideBox.find(".label.#{k}")
      unless cjEl.hasClass("errorLabel")
        cjEl.data("oldname",cjEl.text())
        cjEl.addClass("errorLabel").text(v)
      @cj_slideBox.find("input[name='#{k}']").addClass("errorBox")
  # split this up into data elswhere so i don't have to call this 
  # for the data values
  gatherValuesFromSlideBox: () ->
    cjFields = @cj_slideBox.find("input")
    data =
      tagId: @cjDT.data("tagid")
      fields: {}
      errors: {}
    cj.each(cjFields, (i,el) =>
      cjEl = cj(el)
      if cjEl.attr("type").toLowerCase() == "checkbox"
        data.fields[cjEl.attr("name")] = cjEl.prop("checked")
      else
        data.fields[cjEl.attr("name")] = cjEl.val()
    )
    @validateValues(data)
    
  revertSubmitFromLoading:() ->
    cjSubmitButton = @cj_slideBox.find(".label.submit")
    cjSubmitButton.removeClass("loadingGif")
    text = cjSubmitButton.data("text")
    cjSubmitButton.text(text)
  convertSubmitToLoading:() ->
    cjSubmitButton = @cj_slideBox.find(".label.submit")
    cjSubmitButton.addClass("loadingGif")
    cjSubmitButton.data("text",cjSubmitButton.text())
    cjSubmitButton.text("")

  validateValues:(data) ->
    validations =
      createError: (name,val) =>
        # niceName = @cj_slideBox.find(".label.#{name}").text()
        niceName = @cjDT.data("name")
        val = "#{niceName} #{val}"
        data.errors[name] = val
      isString: (name,val) ->
        validations.createError.call(@,name,"cannot contain numbers or symbols.")
      appliesNullToText:(name,val) ->
        passing = true
        if val.length > 0
          noSpaces = val.replace(/\ /g,"")
          passing = false if noSpaces.length == 0
        else
          passing = false
        unless passing
          data.fields[name] = null
      noSpaces: (name,val) ->
        validations.createError.call(@,name,"cannot be contain spaces.")

      isRequired: (name,val) ->
        if @requiredFields.indexOf(name) != -1
          passing = true
          if val.length > 0
            noSpaces = val.replace(/\ /g,"")
            passing = false if noSpaces.length == 0
          else
            passing = false
          unless passing
            validations.createError.call(@,name,"is required.")
      # checking the process as a whole, separate from text validation above
      noChildren: (tagId) ->
        # TODO
        cjDL = cj(".JSTree #tagDropdown_#{tagId}")
        passing = true
        passing = false if cjDL.children().length > 0
        name = cjDL.data("name")
        unless passing
          validations.createError.call(@,"headerdescription","cannot be deleted because it has children.")

    for k,v of data.fields
      cjEl = @cj_slideBox.find("input[name='#{k}']")
      if cjEl.attr("name").toLowerCase() == "checkbox"
        continue
      for test in @requiredValidation
        validations[test].call(@,k,v)
    # test object
    if bbUtils.objSize(data.fields) == 0
      for test in @requiredValidation
        validations[test].call(@,data.tagId)
    data

  submitButton: (gather,cb) ->
    @cj_slideBox.find(".label.submit").on "click", =>
      if gather
        cb(@gatherValuesFromSlideBox())
      else
        true

  gatherLabelHTML: (values="") ->
    label = new Label
    html = ""
    if @tagName?
      html += label.buildLabel("header","Add Tag","Add Tag Under:")
      html += label.buildLabel("headerdescription","headerdescription","#{@tagName}")
    else
      html += label.buildLabel("header","Add Tag","Add Tag")
    for field in @fields.addTag
      html += "<div class='elementGroup'>"
      # creates label
      html += label.buildLabel("label",field,field)
      # in update tag, this is important
      if field is "Is Reserved"
        html += label.buildLabel("checkBox",field,"")
      else
        html += label.buildLabel("textBox",field,"")
      html += "</div>"
    html += "<div class='actionButtons'>"
    html += label.buildLabel("submit","","submit")
    html += label.buildLabel("cancel","","cancel")
    html += "</div>"
    return html
  gatherUpdateLabelHTML: (values="") ->
    label = new Label
    html = ""
    if @tagName?
      html += label.buildLabel("header","Update Tag","Update Tag:")
      html += label.buildLabel("headerdescription","headerdescription","#{@tagName}")
    else
      html += label.buildLabel("header","Update Tag","Update Tag")
    for field in @fields.addTag
      html += "<div class='elementGroup'>"
      # creates label
      html += label.buildLabel("label",field,field)
      # in update tag, this is important
      if field is "Is Reserved"
        html += label.buildLabel("checkBox",field,values[_utils.camelCase(field)])
      else
        html += label.buildLabel("textBox",field,values[_utils.camelCase(field)])
      html += "</div>"
    html += "<div class='actionButtons'>"
    html += label.buildLabel("submit","","submit")
    html += label.buildLabel("cancel","","cancel")
    html += "</div>"
    return html
  gatherRemoveLabelHTML: (values="") ->
    label = new Label
    html = ""
    if @tagName?
      html += label.buildLabel("header","Remove Tag","Remove Tag:")
      html += label.buildLabel("headerdescription","headerdescription","#{@tagName}")
    else
      html += label.buildLabel("error","error","Cannot Find Tag to Remove")
      return html
    html += "<div class='actionButtons'>"
    html += label.buildLabel("submit","","submit")
    html += label.buildLabel("cancel","","cancel")
    html += "</div>"
    return html
  gatherMoveLabelHTML: (values="") ->
    label = new Label
    html = ""
    html += "<div class='openArrow'></div>"
    if @tagName?
      html += label.buildLabel("header","Move Tag","Move Tag:")
      html += label.buildLabel("headerdescription","headerdescription","#{@tagName}")
      html += label.buildLabel("header","header3","to")
      html += label.buildLabel("headerdescription","To Tag","")
    else
      html += label.buildLabel("error","error","Cannot Find Tag to Move")
      return html
    html += "<div class='actionButtons'>"
    html += label.buildLabel("submit","","submit")
    html += label.buildLabel("cancel","","cancel")
    html += "</div>"
    return html

  # addTag is merely a validation and request wrapper. should be explicitly called
  # and provide methods based on tagId properties which are discernable from 
  # the tagId (via a jQuery search)
  tagAjax: (tagId,type,action,locCb) ->
    request = cj.when(cj.ajax(@ajax[type]))
    request.done((data) =>
        if locCb?
          locCb(data.message)
        else if @cb?
          @cb(data.message)
      )
    return @

_manipTags =
  createDate:(mDate)->
    date = ""
    date += "#{mDate.substring(0,3)}-"
    date += "#{mDate.substring(4,5)}-"
    date += "#{mDate.substring(6,7)} "
    date += "#{mDate.substring(8,9)}:"
    date += "#{mDate.substring(10,11)}:"
    date += "#{mDate.substring(12,13)}"
    date

class Label
  defaults:
    header:
      className: "label header"
      value: "Header"
    label:
      className: "label"
      value: "Label"
    textBox:
      className: "textBox"
      value: ""
      name: ""
    submit:
      className: "label submit"
      value: "Submit"
    checkBox:
      className: "checkBox"
      value: ""
    cancel:
      className: "label cancel"
      value: "Cancel"
    headerdescription:
      className: "label headerDescription"
      value: ""
    error:
      className: "label error"
      value: ""
  buildLabel:(type,className,value) ->
    @passed =
      className: _utils.camelCase(className)
      value: value
    if ["header","label","submit","headerdescription","cancel","error"].indexOf(type) > -1
      @label(type)
    else
      @[type].call(@,null)
  label:(type) ->
    @passed.value ?= @.defaults[type].value
    return "<div class='#{@.defaults[type].className} #{@passed.className}'>#{@passed.value}</div>"
  textBox:() ->
    @passed.className ?= @.defaults.textBox.className
    @passed.value ?= @.defaults.textBox.value
    return "<input type='text' class='#{@.defaults.textBox.className} #{@passed.className}' name='#{@passed.className}' value='#{@passed.value}'>"
  checkBox:() ->
    @passed.className ?= @.defaults.textBox.className
    return "<input type='checkbox' class='#{@.defaults.checkBox.className} #{@passed.className}' name='#{@passed.className}' #{@passed.value}>"
  

# buttons handles creating and removing checkboxes, and fCB tags
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
  # finder is a prefix to buttons that allows for
  # specifically targeting the tree or div of tagboxes
  # the div is used in positions for the ajax loaded content
  createTaggingCheckboxes: (finder) ->
    a = @
    @view.cj_selectors.tagBox.find("#{finder} dt .tag .name").before( ->
      if cj(@).siblings(".fCB").length == 0
        a.createButtons(cj(@).parent().parent().data("tagid"))
    )
    @view.toggleCheckInBox()

  removeTaggingCheckboxes: () ->
    @view.cj_selectors.tagBox.find("dt .tag .name .fCB").remove()
  # fcb = floating control box
  # as opposed to previous iterations, now just appends and deletes
  # instead of adding extra page weight with 1400 pieces of html
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
  # kills FCB for each tree
  removeFCB: () ->
    for k,v of @view._trees
      cjTreeTop = @view.cj_selectors.tagBox.find(".top-#{k}").find("dt")
      cjTreeTop.off("mouseenter")
      cjTreeTop.off("mouseleave")
  # function for creating buttons using predefined lists
  # of what appears for each tree
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
      # if parseInt(treeTop) == 292
      #   for tag in @keywords
      #     html += @[tag]
    else
      html += "<li>"
      html += _utils.createCheckBox("tag[#{treeTop}]","","checkbox")
      html += "</li>"
    html += "</ul>"
    html += "</div>"
  # radio buttons are used during selecting move candidates
  addRadios: (treeTop) ->
    # "<input type="radio" class="selectRadio" name="selectTag">"

  # execute creates an action in the view, for logging
  executeButton: (cjDT) ->
    cjDT.off("click")
    if @view.settings.edit
      cjDT.on("click", "li", (button) =>
        action = "#{cj(button.target).data("do")}Tag"
        tagid =  cjDT.data("tagid")
        @view.createAction(tagid,action)
      )
    else
      # i think there's another on li somewhere else
      cjDT.on("click", "li", (button) =>
        # cj(button.target).data("do")
      )
# activity log powers the errors
# not currently implemented
class ActivityLog
  constructor: (jsonObj,action) ->
    # console.log jsonObj,action

# settings is the 'settings box' in the upper right.
# add tag lives there, along with print, settings (which creates)
# a dropdown, "clear", options, etc. that kind of stuff.
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

# resize specifically relates to the height of the tagbox
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
  # resize handler for resizing the box
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
        @view.settings.tall = false
      if !@tagBox.hasClass("dropdown")
        bbUtils.localStorage("tagBoxHeight", {height:@tagBox.height()})
        @view.settings.tall = true
      else
        bbUtils.localStorage("tagBoxHeight", {height:0})
        @view.settings.tall = false
      @view.setDescWidths()
    )
    @view.cj_tokenHolder.resize.on("mousedown", (ev,tagBox) =>
      if @tagBox.hasClass("dropdown")
        @tagBox.height(0)
        @tagBox.show()
        @tagBox.removeClass("dropdown")
      ev.preventDefault()
      cj(document).on("mousemove", (ev,tagBox) =>
          @view.toggleDropdown()
          tagBoxHeight = (ev.pageY-(cj(".JSTree").offset().top+cj(".JSTree-tokenHolder").height()))
          if tagBoxHeight < maxHeight
            @tagBox.css("height",tagBoxHeight)
        )
    )

# autocomplete. you instantiate the autocomplete, and it uses
# the data provided by the @view and @instance to create the
# autocomplete environment
class Autocomplete
  # jqDataReference doesn't do anything. I think.
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
    debounced = bbUtils.debounce(@execSearch,500)
    cjac.on "keydown", ((event) =>
      @filterKeydownEvents(debounced,event,searchmonger,cjac)
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
  # inithint is the hint text "type in a partial or complete name"
  # and cheks that if it exists
  initHint = true
  
  # because it's not always there, the hint text needs to be styled as it comes in
  # and unstyled as it leaves
  hintText: (cjac,params) ->
    cjac.val(params.hintText)
    cjac.css("color","#999")

  # meat and potatoes of determining events, directs traffic
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
        return obj(@,event,searchmonger,cjac)
      else
        return false
    
  # builds the recursive positions list once the inital call's been made
  # and there's more than one page left.
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
          openLeg.query(nextPage, (results) =>
              poses = @addPositionsToTags(results.results)
              filteredList = {292: poses}
              @getNextPositionRound(results)
              new Tree(poses,"292",false,cj(".JSTree .top-292"),nextPage)
              addButtonsTo = ""
              for k,v of nextPage
                addButtonsTo += ".#{k}-#{v}"
              new Buttons(@view,addButtonsTo)
              @openLegQueryDone = true
              @buildPositions()
            )
      )
  # html fixture for position loder
  # i don't think it uses nextPage for antyhing
  addPositionLoader: (nextPage = {}) ->
    "<dt class='loadingGif' data-parentid='292'>
      <div class='tag'>
        <div class='ddControl'></div>
        <div class='loadingText'>Loading...</div>
      </div>
    </dt>"
  # execSearch is the 'main' search query, once it validates that it should
  # perform a query, uses searchmonger to query the lists, write the lists to the view
  # and executes them. because of some nuances, i'm not referring to the this object
  # aka @, as @, but instead obj in this function because of some silly things
  # with the debonce function making that difficult to pass (as it's inside a
  # closure from the debounce function, and i didn't bother to fix it yet)
  # openLeg is queries independently, and thus, enter at different times
  execSearch: (obj,event,searchmonger,cjac) ->
    term = cjac.val()
    if term.length >= 3
      obj.view.shouldBeFiltered = true
      obj.doOpenLegQuery()
      searchmonger.nExec(event, (terms) =>
        if terms? && !cj.isEmptyObject(terms)
          tags = obj.sortSearchedTags(terms.tags)
          hits = obj.separateHits(tags)
          hcounts = 0
          foundTags = []
          # where trees the tags are in
          for k,v of hits
            hcounts += v
            foundTags.push(parseFloat(k))
          filteredList = obj.view.buildFilteredList(tags)
          obj.view.writeFilteredList(filteredList, terms.term.toLowerCase(), hits)
          obj.localQueryDone = true
          if obj.view.cj_selectors.tagBox.hasClass("dropdown")
            obj.view.toggleDropdown(hits)
      )
  # executes the openLeg query
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
        @view.toggleDropdown({292:(hitCount)})
        @openLegQueryDone = true
      )
  # writes the 'hits', which is an obj with array counts of
  # the returned, formatted, json objects
  separateHits: (terms, results) ->
    hits = {}
    for k, v of terms
      # if v.length > 0
      hits[k] = v.length
    hits[296] = 0 unless hits[296]?
    hits[291] = 0 unless hits[291]?
    hits

  # arbitrary seed number for positions without an id
  positionIdNumber: 292000

  # changes definitions for each new page
  getNextPositionRound:(results) ->
    @positionPage = results.page + 1
    @positionPagesLeft = results.pagesLeft
    @positionSearchTerm = results.term
  # creates the 3 variations of a position for a bill
  # for, against, neutral, and duplicates it 3 times
  # to use in a tree
  addPositionsToTags: (positions) ->
    format = []
    positionList = @instance.positionList
    checkName = (name,v) =>
      return true if _utils.removePositionTextFromBill(name) == v.name and _utils.checkPositionFromBill(name) == v.pos
      return false
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
        # ideally should use checkname, but not tested yet
        # but a lot of this is totes inefficient.
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
  # sorts an unorganized list of tags into
  # categories based upon it's type
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
# closured variable for determine what tags have been opened
# and closed during the session for reload
_openTags = {}

# provides global knowledge of what trees are visible
_treeVisibility =
  currentTree: ""
  defaultTree: ""
  previousTree: ""

# tree creates new trees
class Tree
  domList: {}
  nodeList: {}
  tabName: ""
  # taglist is an ordered list of tags with data
  # tagId is which type of tag it is (numeric)
  # filter is if you're building the list via filter methods
  # location is when you're specifically targeting specific blocks of position responses
  # i.e. nextPage =
  #        term: @positionSearchTerm
  #        page: @positionPage
  # list classes allows you to add classes to the location that you target
  # so you can target it in the future
  constructor: (@tagList, @tagId, @filter = false, @location, @listClasses) ->
    @buildTree()
    return @
  # build tree creates a blank tree, with dom elements to be inserted, and then
  # appended to the tree required
  buildTree: () ->
    if @filter then filter = "filtered" else filter = "" 
    if @location?
      if @location == "init"
        @append = "292"
      else
        @append = true
      @domList = cj()
      if @listClasses?
        dataNames = ""
        for k,v of @listClasses
          dataNames += " #{k}-#{v} "
        @domList = @domList.add("<div class='#{dataNames}'></div>")
      else
        @domList = @domList.add("<div></div>")
    else
      @domList = cj()
      @domList = @domList.add("<div class='top-#{@tagId} #{filter} tagContainer' data-treeid='#{@tagId}'></div>")
    @iterate(@tagList)
  # because we know how to attach each tag to a parent, it's not a DAG...
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
      if @append == "292"
        cj("<div class='top-292 tagContainer'></div>").appendTo(".JSTree")
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
  # really, we're prepending, but, it's still an 'appending'
  appendNode:(node,parent,html,noAdd=false) ->
    console.log node
    topLevel = false
    if parseInt(parent) == 291 or parseInt(parent) == 292 or parseInt(parent) == 296
      topLevel = true
      if noAdd
        parentDT = "#tagLabel_#{node.id}"
        parentDL = "#tagDropdown_#{node.id}"
        for obj,i in @tagList
          console.log node.id == obj.id
          if obj.id == node.id
            @tagList.splice(i,1)
            @tagList.splice(i,0,node)
            break
      else
        parentDT = ""
        parentDL = ".JSTree .top-#{node.type}"
        cjParentDT = cj(parentDT)
        @tagList.push node
    else
      parentDT = ".JSTree #tagLabel_#{parent}"
      parentDL = ".JSTree #tagDropdown_#{parent}"
      cjParentDT = cj(parentDT)
      return false if cjParentDT.length == 0
      @tagList.push node
    if noAdd
      cj(@domList).find(parentDT).replaceWith(html)
      cj(@html).find(parentDT).replaceWith(html)
    else
      cj(@domList).find(parentDT).prepend(html)
      cj(@html).find(parentDT).prepend(html)
    return true if noAdd
    cjParentDL = cj(parentDL)
    cjParentDL.show().prepend(html)
    return true if topLevel
    cjParentDT.addClass("open")
    cjParentDT.find(".ddControl").addClass("treeButton")
    # make sure to add buttons after calling AppendNode
    return true

  removeNode:(nodeId,noDel) ->
    realTree = "#JSTree-container .JSTree"
    return false if @hasChildren(cj(realTree),nodeId)
    for obj,i in @tagList
      if parseInt(obj.id) == nodeId
        @tagList.splice(i,1)
        break
    removeFrom = [@domList,@html,realTree]
    for i in removeFrom
      cjDT = cj(i).find("#tagLabel_#{nodeId}")
      cjDL = cj(i).find("#tagDropdown_#{nodeId}")
      parentId = cjDT.data('parentid')
      cjDT.remove()
      cjDL.remove()
      if !@hasChildren(i,parentId)
        cjParentDT = cj(i).find("#tagLabel_#{parentId}")
        cjParentDT.removeClass("open")
        cjParentDT.find(".ddControl").removeClass("treeButton")
  hasChildren:(tree,nodeId) ->
    cjDL = cj(tree).find("#tagDropdown_#{nodeId}")
    # are there siblings
    return true if cjDL.children().length > 0
    return false 






# util functions that the tree uses
_treeUtils =
  # don't remember what this does
  selectByParent: (list, parent) ->
    childList = [] 
    for b in list
      if b.parent == parent
        childList.push b
    childList
  # don't remember what this does
  selectByTree: (list, tree) ->
    treeList = [] 
    for b in list
      if b.type == tree
        treeList.push b
    treeList
  # event for what happens when you click on a slide button
  makeDropdown: (cjTree) ->
    cjTree.find(".treeButton").off "click"
    cjTree.find(".treeButton").on "click", ->
      _treeUtils.dropdownItem(cj(@).parent().parent())
  # executes a dropdown on a particular tag
  # useful for individuall picking and choosing tags
  dropdownItem: (tagLabel, filter = false) ->
    tagid = tagLabel.data('tagid')
    if tagLabel.length > 0
      if tagLabel.is(".open")
        _openTags[tagid] = false
      else
        _openTags[tagid] = true
    tagLabel.siblings("#tagDropdown_#{tagid}").slideToggle("200")
    tagLabel.toggleClass "open"
    if !filter
      bbUtils.localStorage("tagViewSettings", _openTags)
  # i don't know how cjTree is parseIntable (meaning it's probably named wrong)
  # but this reads the local storage variables for what tags are opened
  # and opens them again
  readDropdownsFromLocal: (cjTree) ->
    if parseInt(cjTree) == 291
      if bbUtils.localStorage("tagViewSettings")    
        _openTags = bbUtils.localStorage("tagViewSettings")
        for tag, bool of bbUtils.localStorage("tagViewSettings")
          if bool
            @dropdownItem cj("#tagLabel_#{tag}")
          else
            delete _openTags[tag]
      else
      _openTags

# closured settings for determining how many characters a node
# should be, in name and description
_descWidths = 
  normal: 75
  long: 150


class Node
  # attaches a lot of descriptions to the class
  # which is only good because it still shows the state
  # of unmodified vs modified nodes
  constructor: (node) ->
    @setValues(node)
    return @
  setValues: (node) ->
    @data = node
    @parent = node.parent
    @hasDesc = ""
    @description = node.description
    @descLength(node.description)
    @id = node.id
    @children = node.children
    @name = node.name
    @nameLength = ""
    @billNo = node.billNo
    @isreserved = node.is_reserved
    if node.type == 292
      @billNo ?= node.name
      @name = node.posName
    @billNo ?= ""
    @position = node.position
    @position ?= node.pos
    @position ?= ""
    if @name.length >= _descWidths.normal
      levelModifier = 0
      if node.level > 2
        levelModifier = node.level*5
      @name = _utils.textWrap(@name, (_descWidths.normal - levelModifier) )
      @name = @name.toRet.join('<br />')
      @nameLength = "longName"
    @name = cj.trim(@name)
    @html = @getHtml(node)
    return true
  # processes a description and attaches classes based on parameters
  descLength: (@description) ->
    if @description == null or @description == "null"
      @description = "" 
      return true
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
  # writes the html of a node
  # basically, send a node class the right parameters, and you'll
  # get an actionable node. and you don't have to worry about putting in dependencies
  # because you're using dom characteristics to create tags
  getHtml: (node) ->
    if node.children then treeButton = "treeButton" else treeButton = ""
    if parseFloat(node.is_reserved) != 0 then @reserved = true  else @reserved = false
    # dt first
    html = "<dt class='lv-#{node.level} #{@hasDesc} tag-#{node.id} #{@nameLength}' id='tagLabel_#{node.id}'
             data-tagid='#{node.id}' data-tree='#{node.type}' data-name='#{node.name}' 
             data-parentid='#{node.parent}' data-billno='#{@billNo}'
             data-position='#{@position}' data-level='#{node.level}'
             data-isreserved='#{@isreserved}'
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
class Token
  constructor: (cjLocation) ->
    
    # there's 2 children to start
    if cjLocation.children().length == 2
      # console.log "2 kids empty"
    else
      # console.log "plenty of kids"

  dropdown: (cjLocation) ->  

  create:(cjLocation,name,type,id,cb) ->
    # console.log "create"
    html = "<div class='token token-#{id}' data-name='#{name}' data-type='#{type}'>
                #{name}
             </div>
           "
    cjLocation.append(html)
    cjLocation.find(".token-#{id}").on "click", =>
      cb.call(@)
      cjLocation.find(".token-#{id}").remove()
    return @