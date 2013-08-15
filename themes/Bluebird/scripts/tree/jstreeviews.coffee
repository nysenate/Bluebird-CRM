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
      @writeTreeFromSource()
  setUpdateInterval: (timeSet) ->
    callback = => @getData()
    setInterval( callback, timeSet )
  killUpdateInterval: (clearInt) ->
    clearInterval(clearInt)
  writeContainers: () ->
    # cj.extend(_viewSettings, viewSettings, true)
    # console.log _viewSettings
    @formatPageElements()
    # console.log cj(".#{@pageElements.init}")
    @addClassesToElement()
  addClassesToElement: () ->
    @cjInitHolderId.html "<div class='#{@addClassHolderString}'></div>"
    @addMenuToElement()
    @addTokenHolderToElement()
    @addDataHolderToElement()
    @cjInitHolderId.removeClass(@initHolderId).attr("id", @addIdWrapperString)
  addMenuToElement: ()->
    menu = "
      <div class='#{@menuName.menu}'>
       <div class='#{@menuName.top}'>
        <div class='#{@menuName.tabs}'></div>
        <div class='#{@menuName.settings}'></div>
       </div>
       <div class='#{@menuName.bottom}'>
        <div class='#{@menuName.autocomplete}'>
         <input type='text' id='JSTree-ac'>
        </div>
        <div class='#{@menuName.settings}'></div>
       </div>
      </div>
    "
    @cjInitHolderId.prepend(menu)
  addDataHolderToElement: ()->
    dataHolder = "<div id='JSTree-data' style='display:none'></div>"
    @cjInitHolderId.append(dataHolder)
  addTokenHolderToElement: ()->
    tokenHolder = "
      <div class='#{@tokenHolder.tokenHolder}'>
       <div class='#{@tokenHolder.resize}'></div>
       <div class='#{@tokenHolder.body}'>
        <div class='#{@tokenHolder.left}'></div>
        <div class='#{@tokenHolder.options}'></div>
       </div>
      </div>
    "
    @cjInitHolderId.append(tokenHolder)
  addSearchBoxToElement: () ->

  formatPageElements: () ->
    pageElements = @instance.get 'pageElements'
    [@tagHolderSelector,@tagWrapperSelector] = ["",""]
    @menuName =
      menu: ""
      top: ""
      tabs: ""
      bottom: ""
      autocomplete: ""
      settings: ""
    @tokenHolder = 
      tokenHolder: ""
      options: ""
      body: ""
      resize: ""
      left: ""
    @addIdWrapperString = pageElements.wrapper
    @addClassHolderString = pageElements.tagHolder
    @initHolderId = pageElements.init
    @cjInitHolderId = cj(".#{@initHolderId}")
    
    @addClassHolderString = @ifisarrayjoin(@addClassHolderString)
    for selector, i in pageElements.tagHolder
      selector = selector.replace(" ","-")
      @menuName = @concatOnObj(@menuName, selector)
      @tokenHolder = @concatOnObj(@tokenHolder, selector)
      @tagHolderSelector = @tagHolderSelector.concat(".#{selector}")
    @tagWrapperSelector = @tagWrapperSelector.concat("##{pageElements.wrapper}")
  ifisarrayjoin: (toJoin)->
    if cj.isArray(toJoin)
      toJoin = toJoin.join(" ")
  concatOnObj: (obj, selector, classOrId = ".") ->
    for k,v of obj
      if k.substr(0,3) == "cj_"
        break
      if typeof obj["cj_#{k}"] == "undefined" then obj["cj_#{k}"] = ""
      obj["cj_#{k}"] = obj["cj_#{k}"].concat "#{classOrId}#{selector}-#{k}"
      obj[k] = obj[k].concat "#{selector}-#{k} "
    obj


  getCJQsaves: () ->
    @cjTagWrapperSelector = cj(@tagWrapperSelector)
    @cjTagHolderSelector = cj(@tagHolderSelector)
    @cjInstanceSelector = cj(@tagWrapperSelector.concat(" #{@tagHolderSelector}"))
    @cjTabs = cj(@menuName.cj_tabs)
  # what we're going to do here is
  # allow for options
  writeTreeFromSource: () ->
    @getCJQsaves()
    @displaySettings = @instance.get 'displaySettings'
    @dataSettings = @instance.get 'dataSettings'
    locals = {"menu":@menuName.cj_tabs,"top":@displaySettings.defaultTree}
    treeBehavior.setLocals locals
    # actions
    @writeTabs()
    @cjInstanceSelector.html(_treeData.html[@displaySettings.defaultTree])

    for k,v of @dataSettings.pullSets
      if v isnt @displaySettings.defaultTree
        @cjInstanceSelector.append(_treeData.html[v])
      treeBehavior.createOpacityFaker(".top-#{v}","dt","type-#{v}")
    @cjInstanceSelector.find(".top-#{@displaySettings.defaultTree}").addClass("active")
    treeBehavior.setCurrentTab _treeData.treeTabs[@displaySettings.defaultTree]
    cj(@tagHolderSelector).append("<div class='search tagContainer'></div>")
    treeBehavior.autoCompleteStart(@instance)
    treeBehavior.readDropdownsFromLocal()
    treeBehavior.enableDropdowns()
  writeTabs: () ->
    output = ""
    _treeData.treeTabs = {}
    for k,v of _treeData.treeNames
      b = v.replace(" ","-")
      b = b.toLowerCase()
      treeBehavior.appendTab(b,v)
      _treeData.treeTabs[k] = "tab-#{b}"
      treeBehavior.createTabClick("tab-#{b}", "top-#{k}")

# change data sets, not multipe implementations
_treeVisibility =
  currentTree: ""
  defaultTree: ""
  previousTree: ""


treeBehavior =
  setLocals: (locals) ->
    @tabsLoc = locals.menu if locals.menu?
    if locals.top?
      _treeVisibility.currentTree = "top-#{locals.top}" if _treeVisibility.currentTree is ""

  autoCompleteStart: (@instance) ->
    @pageElements = @instance.get 'pageElements'
    @dataSettings = @instance.get 'dataSettings'
    @appendTab("search","Search",true)
    @createTabClick("tab-search", "search")
    @cjTagBox = cj(".#{@pageElements.tagHolder.join(".")}") unless @cjTagBox?
    cj("#JSTree-data").data("autocomplete" : @instance.getAutocomplete())
    params =
      jqDataReference: "#JSTree-data"
      hintText: "Type in a partial or complete name of an tag or keyword."
      theme: "JSTree"
    cjac = cj("#JSTree-ac")
    searchmonger = cjac.tagACInput("init",params)
    # REALLY NEED TO START SEPARATING THIS OUT
    # and that definitely should be debounce
    cjac.on "keydown", bbUtils.throttle((event) =>
      searchmonger.exec(event, (terms) =>
        openLeg = new OpenLeg
        console.log terms
        if terms? && terms.tags?
          openLeg.query({"term":terms.term}, (results) =>
            console.log results
            if terms.tags.length > 0
              @buildSearchList(terms.tags, terms.term.toLowerCase())
            else if terms.tags.length == 0 and terms.term.length >= 3
              @buildSearchList(null, "No Results Found")
          )
        if cjac.val().length < 3
          if _treeVisibility.currentTree == "search"
            @showTags _treeVisibility.previousTree
          cj("#{@tabsLoc} .tab-search").hide() 
      )
    300)

  grabParents: (cjParentId) ->
    return [] if @dataSettings.pullSets.indexOf(cjParentId) != -1
    go = true
    parentid = [cjParentId]
    while go
      newid = @cjTagBox.find("dt[data-tagid=#{parentid[parentid.length-1]}]").data("parentid")
      if @dataSettings.pullSets.indexOf(newid) < 0
        parentid.push(newid)
      else
        go = false
    parentid   

  buildParents: (parentArray) ->
    output = ""
    # you're going from top, down, not bottom up
    parentArray.reverse();
    for parentid, index in parentArray
      clonedTag = @cjTagBox.find("dt[data-tagid=#{parentid}]").clone()
      clonedTagLvl = treeManipulation.parseLvl(clonedTag.attr("class"))
      clonedName = clonedTag.data('name')
      if index == 0
        if @alreadyPlaced.indexOf(parentid) < 0
          clonedTag.appendTo(@cjSearchBox).addClass("open")
          @alreadyPlaced.push parentid
          @cjSearchBox.append(treeManipulation.createDL(clonedTagLvl, parentid, clonedName))
          # cj(".search #tagDropdown_#{parentid}").append(clonedTag).addClass("open")
      else
        if @alreadyPlaced.indexOf(parentid) < 0
          clonedTag.appendTo(".search #tagDropdown_#{parentArray[index-1]}")
          cj(".search #tagDropdown_#{parentArray[index-1]}").append(treeManipulation.createDL(clonedTagLvl, parentid, clonedName))
    cj(".search #tagDropdown_#{parentArray[index-1]}")

      
      # if it has children...


  buildSearchList: (tagList, term) ->
    @alreadyPlaced = []
    @cjSearchBox = @cjTagBox.find(".search") unless @cjSearchBox?
    @cjSearchBox.empty()
    if tagList != null
      tagListLength = tagList.length
      @toShade = []
      foundId = []
      # pre-populate a list
      for key,tag of tagList
        # parentArray.push(@cjTagBox.find("dt[data-tagid=#{tag.id}]").data("parentid"))
        foundId.push(parseInt tag.id)
      for key,tag of tagList
        cjCloneTag = @cjTagBox.find("dt[data-tagid=#{tag.id}]")
        cjParentId = cjCloneTag.data("parentid")
        if @cloneChildren(cjCloneTag,tagList)
          # checking to see if orphaned children?
          if foundId.indexOf(cjParentId) < 0 
            if @dataSettings.pullSets.indexOf(cjParentId) < 0
              toAppendTo = @buildParents(@grabParents(cjParentId))
            else
              toAppendTo = @cjSearchBox
          else
            toAppendTo = @cjSearchBox
          cjCloneChildren = @cjTagBox.find("#tagDropdown_#{tag.id}")
          @toShade.push(parseInt tag.id)
          cjCloneTag.clone().appendTo(toAppendTo).addClass("shaded")
          cjCloneChildren.clone().appendTo(toAppendTo)
        else
          @toShade.push(parseInt tag.id)
      # search for all DL's in search
      allDropdowns = cj(".search dt .tag .ddControl.treeButton").parent().parent()
      # 3 tenths of a second from here to end
      @processSearchChildren @toShade
      cj.each allDropdowns, (key,value) =>
        tagid = cj(value).data('tagid')
        if tagid?
          @enableDropdowns(".search dt[data-tagid='#{tagid}']", true)
    else
      tagListLength = 0
      @cjSearchBox.append("<div class='noResultsFound'>No Results Found</div>")
    
    for value in @toShade
      @makeShade value, term
    @switchToSearch(tagListLength)
  
  switchToSearch: (tagListLength) ->
    cj("#{@tabsLoc} .tab-search").show()
    @setTabResults(tagListLength,"tab-search")
    @showTags("search")

  makeShade: (tagid, term) ->
    cjItems = cj(".search dt[data-tagid='#{tagid}']")
    cjItems.addClass("shaded")
    cj.each cjItems, (i,arr) =>
      toLc = cj(arr).find(".tag .name").text().toLowerCase()
      initIndex = toLc.indexOf(term.toLowerCase())
      strBegin = cj(arr).text().slice(0,initIndex)
      strEnd = cj(arr).text().slice(term.length + initIndex)
      strTerm = "<span>#{cj(arr).text().slice(initIndex,term.length + initIndex)}</span>"
      tagName = cj(arr).find(".tag .name")
      tagName.html("#{strBegin}#{strTerm}#{strEnd}")


  cloneChildren: (cjTag, tagList) ->
    setReturn = true
    for key,tag of tagList
      hasRelevantPs = cjTag.parents("dl#tagDropdown_#{tag.id}")
      if hasRelevantPs.length > 0
        setReturn = false
    setReturn

  setTabResults: (number,tabName) ->
    tab = cj("#{@tabsLoc} .#{tabName}")
    tab.find("span").remove()
    result = tab.html()
    tab.html("#{result}<span>(#{number})</span>")

  setCurrentTab: (treeTag) ->
    cj("#{@tabsLoc}").find(".active").toggleClass("active")
    cj("#{@tabsLoc}").find(".#{treeTag}").toggleClass("active")

  showTags: (currentTree, noPrev) ->
    if currentTree != _treeVisibility.currentTree
      @cjTagBox.find(".#{_treeVisibility.currentTree}").toggle() 
      _treeVisibility.previousTree = _treeVisibility.currentTree
      _treeVisibility.currentTree = currentTree
      @cjTagBox.find(".#{currentTree}").toggle()
      @setCurrentTab @convertTreeNameToTab(currentTree)

  convertTreeNameToTab: (treeName) ->
    splitted = treeName.split("-")
    parsed = parseInt(splitted[splitted.length-1])
    if !isNaN(parsed)
      return "#{_treeData.treeTabs[parsed]}"
    else
      return "tab-#{treeName}" if treeName == "search"

  appendTab: (a,c,hidden = false) ->
    style = ""
    style = "style='display:none'" if hidden
    cjtabloc = cj("#{@tabsLoc}")
    output = "<div class='tab-#{a}' #{style}>#{c}</div>"
    cjtabloc.append(output)


  autoCompleteEnd: (@instance) ->
    cj("#JSTree-ac").off "keydown"

  processSearchChildren: (tagArray) ->
    # start with opening up children
    # and ideally, we'll take all of the shaded tags and move them to the top.
    alreadyProcessed = []
    for tag in tagArray
      parents = @grabParents(tag)
      for parent in parents
        if alreadyProcessed.indexOf(parent) < 0 && parent != tag
          cj(".search dt[data-tagid='#{parent}']").addClass "open"
          cj(".search dl#tagDropdown_#{parent}").show()
          alreadyProcessed.push(parent)

  # onclick method for tabs
  createTabClick: (tabName, tabTree) ->
    cj(".JSTree-tabs .#{tabName}").off "click"
    cj(".JSTree-tabs .#{tabName}").on "click", =>
      @showTags tabTree

  # onclick method for tag dropdowns
  enableDropdowns: (tag = "", search = false) ->
    cj(".JSTree #{tag} .treeButton").off "click" 
    cj(".JSTree #{tag} .treeButton").on "click", ->
      treeBehavior.dropdownItem(cj(this).parent().parent(), search)
  
  # creates an arbitrary 'transparancy box'
  # in order to provide 100% opaque text hovered
  # over a semi-transparant background
  createOpacityFaker: (container, parent, cssClass = "") ->
    cjItems = cj("#{container} #{parent}")
    cjItems.append("<div class='transparancyBox #{cssClass}'></div>")

  # dropdown item opens up the dl's that are associated 
  # with the attributed dt's. 
  dropdownItem: (tagLabel, search = false) ->
    tagid = tagLabel.data('tagid')
    tagLabel.siblings("dl#tagDropdown_#{tagid}").slideToggle "200", =>
      if tagLabel.is(".open")
        _viewSettings["openTags"][tagid] = false
      else
        _viewSettings["openTags"][tagid] = true
      tagLabel.toggleClass "open"
    if !search
      bbUtils.localStorage("tagViewSettings", _viewSettings["openTags"])

  readDropdownsFromLocal: () ->
    if bbUtils.localStorage("tagViewSettings")    
      _viewSettings["openTags"] = bbUtils.localStorage("tagViewSettings")
      for tag, bool of bbUtils.localStorage("tagViewSettings")
        if bool
          toPass = cj("dt.tag-#{tag}")
          @dropdownItem toPass
        else
          delete _viewSettings["openTags"][tag]
    else
    _viewSettings["openTags"]

  

_viewSettings =
  openTags: {}

utils = 
  loadingGif:()->
    cj(".#{@pageElements.tagHolder.join(".")}").toggleClass("loadingGif")

treeManipulation =
  parseLvl: (tags) ->
    tagArr = tags.split(" ")
    for tag in tagArr
      if tag.indexOf("lv-") != -1
        return tag.slice(3)

  createDL: (lvl, id, name) ->
    return "<dl class='lv-#{lvl}' id='tagDropdown_#{id}' data-name='#{name}'></dl>"

  createDT: (lvl, id, name, parent) ->


###
neat
<script>
$("div").attr("id", function (arr) {
  return "div-id" + arr;
})
.each(function () {
  $("span", this).html("(ID = '<b>" + this.id + "</b>')");
});
</script>
###