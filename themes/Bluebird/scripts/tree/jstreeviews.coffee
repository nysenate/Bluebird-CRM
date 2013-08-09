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
      treeBehavior.createOpacityFaker(".top-#{v}","dt","type-#{v}")
    treeBehavior.setCurrentTab _treeData.treeTabs[@displaySettings.defaultTree]
    cj(@tagHolderSelector).append("<div class='search'></div>")
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
    @appendTab("search","search",true)
    @cjTagBox = cj(".#{@pageElements.tagHolder.join(".")}") unless @cjTagBox?
    cj("#JSTree-data").data("autocomplete" : @instance.getAutocomplete())
    params =
      jqDataReference: "#JSTree-data"
      hintText: "Type in a partial or complete name of an tag or keyword."
      theme: "JSTree"
    cjac = cj("#JSTree-ac")
    searchmonger = cjac.tagACInput("init",params)
    cjac.on "keydown", (event) =>
      searchmonger.exec(event, (terms) =>
        console.log terms
        if terms? && terms.tags?
          if terms.tags.length > 0
            @buildSearchList(terms.tags, terms.term.toLowerCase())
          else if terms.tags.length == 0 and terms.term.length >= 3
            @buildSearchList(null, "No Results Found")
        if cjac.val().length < 3
          if _treeVisibility.currentTree == "search"
            @showTags _treeVisibility.previousTree
            cj("#{@tabsLoc} .tab-search").hide()
       )
  buildSearchList: (tagList, term) ->
    @cjSearchBox = @cjTagBox.find(".search") unless @cjSearchBox?
    @cjSearchBox.empty()
    if tagList != null
      tagListLength = tagList.length
      toShade = []
      for key,tag of tagList
        cjCloneTag = @cjTagBox.find("dt[data-tagid=#{tag.id}]")
        if @cloneChildren(cjCloneTag,tagList)
          cjCloneTag.clone().appendTo(@cjSearchBox)
        else
          toShade.push(tag.id)
        # console.log @cjTagBox.find("#tagDropdown_#{tag.id}")
        # cjCloneChildren = @cjTagBox.find("#tagDropdown_#{tag.id}")
        # clone = cjCloneChildren.clone()
        # console.log clone
        # console.log @cjSearchBox.html()
        # @cjSearchBox.append(clone)
        @enableDropdowns(".search dt[data-tagid=#{tag.id}]", true)
    else
      tagListLength = 0
      @cjSearchBox.append("<div class='noResultsFound'>No Results Found</div>")
    cj("#{@tabsLoc} .tab-search").show()
    @setTabResults(tagListLength,"tab-search")
    @showTags("search")

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
    console.log a,c
    style = ""
    style = "style='display:none'" if hidden
    cjtabloc = cj("#{@tabsLoc}")
    output = "<div class='tab-#{a}' #{style}>#{c}</div>"
    cjtabloc.append(output)


  autoCompleteEnd: (@instance) ->
    cj("#JSTree-ac").off "keydown"

  processSearchChildren: (tag) ->
    searchTag = cj(".search dl#tagDropdown_#{tag}")
    searchTag.toggle()
    cj(".search dt.tag-#{tag} .ddContol").toggleClass "open"
    searchTag.find("dl").toggle()
    cj(".search dt.tag-#{tag}").find("dt .ddControl").parent().parent().toggleClass "open"

  enableDropdowns: (tag = "", search = false) ->
    if search
      @processSearchChildren
    cj(".JSTree #{tag} .treeButton").off "click" 
    cj(".JSTree #{tag} .treeButton").on "click", ->
      treeBehavior.dropdownItem(cj(this).parent().parent(), search)
  
  createOpacityFaker: (container, parent, cssClass = "") ->
    cjItems = cj("#{container} #{parent}")
    cjItems.append("<div class='transparancyBox #{cssClass}'></div>")

  dropdownItem: (tagLabel, search = false) ->
    tagid = tagLabel.data('tagid')
    # console.log tagLabel.siblings("dl#tagDropdown_#{tagLabel.data('tagid')}")
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
  loadingGif:()->
    cj(".#{@pageElements.tagHolder.join(".")}").toggleClass("loadingGif")


_viewSettings=
  openTags: {}


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