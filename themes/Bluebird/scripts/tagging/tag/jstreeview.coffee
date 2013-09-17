window.jstree["views"] =
  exec: (instance) ->
    @view = new View(instance)
  done: (instance) ->
    trees = {}
    for a,v of instance.treeNames
      b = _treeUtils.selectByTree(instance.autocomplete, a)
      trees[a] = new Tree(b,a)
    @view.trees = trees
    @view.init()
    
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
  constructor: (@instance) ->
    # starts the chain to write the page structure
    @writeContainers()
  writeContainers: () ->
    @formatPageElements()
    @createSelectors()
    @addClassesToElement()
  addClassesToElement: () ->
    @cj_selectors.initHolder.html "<div class='#{@selectors.tagBox}'></div>"
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
    # now you remove trees
    # buildList
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
          # if currentTerm == ""
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
          # if currentTerm == ""
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
    # @tagList, @tagId, @filter = false
    # send to tree to make list
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
        # @cj_selectors.tagBox.append(v.html)
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
    cjac = cj("#JSTree-ac")
    searchmonger = cjac.tagACInput("init",params)
    # cjac.on "keydown", bbUtils.debounce((event) =>
    #   @filterKeydownEvents(event,searchmonger,cjac)
    # 1000)
    cjac.on "keydown",((event) =>
      @filterKeydownEvents(event,searchmonger,cjac)
    )
    cjac.on "keyup", ((event) =>
      keyCode = bbUtils.keyCode(event)
      if keyCode.type == "delete" && cjac.val().length <= 3
        @view.removeTabCounts()
        @view.shouldBeFiltered = false
        @view.rebuildInitialTree()
    )
  filterKeydownEvents: (event, searchmonger, cjac) ->
    keyCode = bbUtils.keyCode(event)
    # look at context first.
    # space & enter add tags to list
    # tab and down, shift tab and up are the same
    # end and home and page up/page down work as you'd
    # expect in the dropdown context
    switch keyCode.type
      when "directional"
        return @moveDropdown(keyCode.type)
      when "letters","delete","math","punctuation","number"
        if keyCode.type != "delete" then name = keyCode.name  else name = ""
        return @execSearch(event,searchmonger,cjac,name)
      else
        return false


  moveDropdown: () ->

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
            @view.writeFilteredList(filteredList,term,{292: (results.seeXmore + 10)})
            @buildPositions()
            @openLegQueryDone = true
          )
          tags = @sortSearchedTags(terms.tags)
          hits = @separateHits(tags)
          hcounts = 0
          foundTags = []
          # where trees the tags are in
          for k,v of hits
            hcounts += v
            foundTags.push(parseFloat(k))
          # for set of @view.trees
            # if foundTags.indexOf(parseFloat(set)) < 0
            #   hits[set] = 0
            #   tags[set] = []
          filteredList = @view.buildFilteredList(tags)
          @view.writeFilteredList(filteredList, terms.term.toLowerCase(), hits)
          @localQueryDone = true
        if terms? && cj.isEmptyObject(terms)
          tags = {}
          # filteredList = @view.buildFilteredList(tags)
          # @view.writeFilteredList(filteredList, term)
      )
      

  separateHits: (terms, results) ->
    hits = {}
    for k, v of terms
      if v.length > 0
        hits[k] = v.length
    # hits[292] = results.seeXmore + results.results.length
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




class Node
  constructor: (node) ->
    @data = node
    @parent = node.parent
    @hasDesc = ""
    @description = node.description
    @descLength(node.description)
    @id = node.id
    @children = node.children
    @name = node.name
    @html = @html(node)
    return @
  descLength: (@description) ->
    if @description?
      if description.length > 0
        @hasDesc = "description"
      if @description.length > 0 and @description.length <= 80
        @hasDesc += " shortdescription"
      if @description.length > 160
        @hasDesc = "longdescription"
      if @description.length > 80
        @description = _utils.textWrap(@description, 80)
  html: (node) ->
    # if @parent > 0 then treeButton = "treeButton" else treeButton = ""
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
