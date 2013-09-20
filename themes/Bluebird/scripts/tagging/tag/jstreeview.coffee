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
    if @view.settings.tall
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
  entity_id: 0
  defaultPrefix: "JSTree"
  prefixes: []
  defaultTree: 0
  descWidths:
    normal: 80
    long: 160
  constructor: (@instance) ->
    # starts the chain to write the page structure
    @writeContainers()
  writeContainers: () ->
    @formatPageElements()
    @createSelectors()
    tagBox = new Resize
    @setDescWidths()
    console.log tagBox
    console.log @settings.tall
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
        _descWidths.normal = 80
        _descWidths.long = 160
      else
        _descWidths.normal = 40
        _descWidths.long = 40
    else
      if @settings.wide
        _descWidths.normal = 70
        _descWidths.long = 150
      else
        _descWidths.normal = 40
        _descWidths.long = 40
  buildDropdown: () ->
    @cj_selectors.initHolder.html "<div class='#{@selectors.tagBox} dropdown'></div>"
    @cj_selectors.initHolder.prepend(@menuHtml(@menuSelectors))
    @cj_selectors.initHolder.append(@dataHolderHtml())
    @cj_selectors.initHolder.append(@tokenHolderHtml(@tokenHolder))
    @cj_selectors.initHolder.removeClass(@selectors.initHolder).attr("id", @selectors.container).addClass(@selectors.containerClass)
  addClassesToElement: (height) ->
    @cj_selectors.initHolder.html "<div class='#{@selectors.tagBox}' #{height}></div>"
    @cj_selectors.initHolder.prepend(@menuHtml(@menuSelectors))
    @cj_selectors.initHolder.append(@dataHolderHtml())
    @cj_selectors.initHolder.append(@tokenHolderHtml(@tokenHolder))
    @cj_selectors.initHolder.removeClass(@selectors.initHolder).attr("id", @selectors.container).addClass(@selectors.containerClass)
  formatPageElements: () ->
    pageElements = @instance.get 'pageElements'
    displaySettings = @instance.get 'displaySettings'

    # could reorginize to allow best flexibility for tags
    @selectors.container = pageElements.wrapper.shift()
    @selectors.containerClass = pageElements.wrapper.join(" ")
    @selectors.tagBox = pageElements.tagHolder.join(" ")
    @menuSelectors.tabs = pageElements.tabLocation
    @menuSelectors.autocomplete = pageElements.autocomplete
    @selectors.dropdown = pageElements.tagDropdown
    @selectors.initHolder = pageElements.init
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
  createTabClick: (tabName, tabTree) ->
    @cj_menuSelectors.tabs.find(".#{tabName}").off "click"
    @cj_menuSelectors.tabs.find(".#{tabName}").on "click", =>
      @showTags tabTree,tabName
  showTags: (currentTree, tabName, noPrev) ->
    if currentTree != _treeVisibility.currentTree
      @cj_menuSelectors.tabs.find(".tab-#{@getTabNameFromId(_treeVisibility.currentTree,true)}").removeClass("active")
      @cj_selectors.tagBox.find(".top-#{_treeVisibility.currentTree}").toggle().removeClass("active") 
      _treeVisibility.previousTree = _treeVisibility.currentTree
      _treeVisibility.currentTree = currentTree
      @cj_menuSelectors.tabs.find(".tab-#{@getTabNameFromId(currentTree,true)}").addClass("active")
      @cj_selectors.tagBox.find(".top-#{currentTree}").toggle().addClass("active")
  setActiveTree: (id) ->
    tabName = @getTabNameFromId(id,true)
    @cj_menuSelectors.tabs.find("div").removeClass("active")
    @cj_selectors.tagBox.find(".tagContainer").removeClass("active").css("display","none")
    @cj_menuSelectors.tabs.find(".tab-#{tabName}").addClass("active")
    @cj_selectors.tagBox.find(".top-#{id}").addClass("active").css("display","block")
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
  writeFilteredList: (list,term,hits = {}) ->
    return false if !@shouldBeFiltered
    if @cj_selectors.tagBox.hasClass("filtered")
      return false if @cleanTree == true
      currentBoxes = @cj_selectors.tagBox.find(".tagContainer")
      cj.each(currentBoxes, (i,tree) =>
        currentTerm = cj(tree).data("term")
        if !currentTerm?
          currentTerm = ""
          cj(tree).data("term","")
        incomingTerm = term
        if currentTerm != incomingTerm
          cj(tree).remove()
        )
    else
      # this is the initial clear
      @cj_selectors.tagBox.addClass("filtered")
      currentBoxes = @cj_selectors.tagBox.find(".tagContainer")
      cj.each(currentBoxes, (i,tree) =>
        currentTerm = cj(tree).data("term")
        if !currentTerm?
          currentTerm = ""
          cj(tree).data("term","")
        incomingTerm = term
        if currentTerm != incomingTerm
          cj(tree).remove()
        ) 
      @cj_selectors.tagBox.empty()
      @cleanTree = false
    @setTabResults(hits)
    activeTree = @cj_menuSelectors.tabs.find(".active").attr("class").replace("active","")
    for k,v of list
      new Tree(v,k,true)
      @cj_selectors.tagBox.find(".top-#{k}").data("term",term)
    @setActiveTree(@getIdFromTabName(activeTree))
    for k,v of hits
      @removeUnnecessaryDropdowns(k)
    # send to tree to make list
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

  removeUnnecessaryDropdowns: (treeId) ->
    dropdowned = @cj_selectors.tagBox.find(".top-#{treeId} .treeButton").parent().parent()
    cj.each(dropdowned, (i,item) ->
        cjItem = cj(item)
        tagid = cjItem.data("tagid")
        sibLength = cjItem.siblings("dl#tagDropdown_#{tagid}").length
        if cjItem.siblings("dl#tagDropdown_#{tagid}").children().length == 0
          cjItem.find(".treeButton").removeClass("treeButton")
      )
  
  rebuildInitialTree: () ->
    if @cj_selectors.tagBox.hasClass("filtered")
      @cj_selectors.tagBox.removeClass("filtered")
      @cj_selectors.tagBox.find(".filtered").remove()
      activeTree = @cj_menuSelectors.tabs.find(".active").attr("class").replace("active","")
      for k,v of @trees
        new Tree(v.tagList, k)
        if parseInt(k) == 292
          @addPositionReminderText(@cj_selectors.tagBox.find(".top-#{k}"))
      @setActiveTree(@getIdFromTabName(activeTree))
  setTabResults: (hits) ->
    for k,v of hits
      cjTab = @cj_menuSelectors.tabs.find(".tab-#{@getTabNameFromId(k, true)}")
      count = cjTab.find("span").html()
      if count?
        count = count.replace(/\(|\)/g,"")
      # doesn't exist yet
      if !count? && parseInt(v) > 0
        result = cjTab.html()
        cjTab.html("#{result}<span>(#{v})</span>")
      else   
        if count > 0 and parseInt(v) == 0
          cjTab.find("span").remove()
          result = cjTab.html()
          cjTab.html("#{result}<span>(#{count})</span>")
        if count == 0 && parseInt(v) > 0
          cjTab.find("span").remove()
          result = cjTab.html()
          cjTab.html("#{result}<span>(#{v})</span>")
        else
          cjTab.find("span").remove()
          result = cjTab.html()
          cjTab.html("#{result}<span>(#{v})</span>")
        

  removeTabCounts: () ->
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
      console.log boxHeight
      @cj_selectors.container.css("position","relative")
      @cj_selectors.tagBox.removeClass("open").css("overflow-y","scroll")
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
      console.log "know height"
      lsheight = bbUtils.localStorage("tagBoxHeight")
      console.log bbUtils.localStorage("tagBoxHeight")
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
    cjac.on "keydown",((event) =>
      @filterKeydownEvents(event,searchmonger,cjac)
    )
    cjac.on "keyup", ((event) =>
      keyCode = bbUtils.keyCode(event)
      if keyCode.type == "delete" && cjac.val().length <= 3
        @view.removeTabCounts()
        @view.shouldBeFiltered = false
        @view.rebuildInitialTree()
        if @view.cj_selectors.tagBox.hasClass("dropdown")
          @view.toggleDropdown()
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
  filterKeydownEvents: (event, searchmonger, cjac) ->
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
        return @execSearch(event,searchmonger,cjac,name)
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
  execSearch: (event,searchmonger,cjac,lastLetter) ->
    term = cjac.val() + lastLetter
    if term.length >= 3
      @view.shouldBeFiltered = true
      searchmonger.exec(event, (terms) =>
        if terms? && !cj.isEmptyObject(terms)
          openLeg = new OpenLeg
          openLeg.query({"term":term}, (results) =>
            poses = @addPositionsToTags(results.results)
            filteredList = {292: poses}
            @getNextPositionRound(results)
            @view.writeFilteredList(filteredList,term,{292: (results.seeXmore)})
            @buildPositions()
            @openLegQueryDone = true
            if @view.cj_selectors.tagBox.hasClass("dropdown")
              @view.toggleDropdown(true)
          )
          tags = @sortSearchedTags(terms.tags)
          hits = @separateHits(tags)
          hcounts = 0
          foundTags = []
          # where trees the tags are in
          for k,v of hits
            hcounts += v
            foundTags.push(parseFloat(k))
          console.log hits
          filteredList = @view.buildFilteredList(tags)
          console.log cj.isEmptyObject(terms)
          # if !cj.isEmptyObject(terms)
          #   for k in [291,296]
              # @view.noResultsBox(cj(".JSTree .top-#{k}"),k)
          @view.writeFilteredList(filteredList, terms.term.toLowerCase(), hits)
          console.log Object.keys(hits).length
          if Object.keys(hits).length < 2
            for k,v of hits
              console.log k,v
              console.log [291,296].indexOf(k)
              # if [291,296].indexOf(k) < 0
                # @view.noResultsBox(cj(".JSTree .top-#{k}"),k)

          @localQueryDone = true
        if terms? && cj.isEmptyObject(terms)
          tags = {}
      )
      

  separateHits: (terms, results) ->
    hits = {}
    for k, v of terms
      if v.length > 0
        hits[k] = v.length
    hits

  positionIdNumber: 292000

  getNextPositionRound:(results) ->
    @positionPage = results.page + 1
    @positionPagesLeft = results.pagesLeft
    @positionSearchTerm = results.term

  addPositionsToTags: (positions) ->
    format = []
    for k,o of positions
      # check if position has id, if not. arbitrarily assign one?
      forpos =
        name: o.forname
        id: "#{@positionIdNumber+1}"
      agipos=
        name: o.againstname
        id: "#{@positionIdNumber+2}"
      neupos=
        name: o.noname
        id: "#{@positionIdNumber+3}"
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
    # @cjLocation = 
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
  normal: 80
  long: 160


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
    html = "<dt class='lv-#{node.level} #{@hasDesc} tag-#{node.id}' id='tagLabel_#{node.id}' data-tagid='#{node.id}' data-name='#{node.name}' data-parentid='#{node.parent}'>"
    html += "
              <div class='tag'>
            "
    html += "
                <div class='ddControl #{treeButton}'></div>
              "
    html += "
                <span class='name'>#{node.name}</span>
            "
    if @hasDesc.length > 0
      html += "
                <div class='description'>#{@description}</div>
            "
    html += "
              </div>
              <div class='transparancyBox type-#{node.type}'></div>
            " 
    html += "</dt>"
    # dl second
    html += "
              <dl class='lv-#{node.level}' id='tagDropdown_#{node.id}' data-name='#{node.name}'></dl>
            "
    return html

  # nodes have processes
  # nodes have data
  # nodes have name
  # nodes don't have html in the tree
