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
      # console.log @instance.get('ready')
      @writeTreeFromSource()
  setUpdateInterval: (timeSet) ->
    callback = => @getData()
    setInterval( callback, timeSet )
  killUpdateInterval: (clearInt) ->
    clearInterval(clearInt)
  writeContainers: () ->
    # cj.extend(_viewSettings, viewSettings, true)
    console.log _viewSettings
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
    @cjTagMenu = cj(@menuSelector)
  # what we're going to do here is
  # allow for options
  writeTreeFromSource: () ->
    @getCJQsaves()
    @displaySettings = @instance.get 'displaySettings'
    @writeTabs()
    @cjInstanceSelector.html(_treeData.html[@displaySettings.defaultTree])
    for key,val of _treeVisibility
      _treeVisibility[key] = "top-#{@displaySettings.defaultTree}"
    cj(@tagHolderSelector).append("<div class='search'></div>")
    treeBehavior.autoCompleteStart(@instance)
    treeBehavior.readDropdownsFromLocal()
    treeBehavior.enableDropdowns()
  writeTabs: () ->
    output = ""
    for a in _treeData.treeNames
      b = a.replace(" ","-")
      b = b.toLowerCase()
      output += "<div class='tab-#{b}'>#{a}</div>"
    @cjTagMenu.find(".tabs").html(output)

# change data sets, not multipe implementations
_treeVisibility =
  currentTree: ""
  defaultTree: ""
  previousTree: ""


treeBehavior =
  autoCompleteStart: (@instance) ->
    @pageElements = @instance.get 'pageElements'
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
        console.log _treeVisibility
        if terms? && terms.tags?
          if terms.tags.length > 0
            @buildSearchList(terms.tags, terms.term.toLowerCase())
          else if terms.tags.length == 0 and terms.term.length >= 3
            @buildSearchList(null, "No Results Found")
        if cjac.val().length < 3
          if _treeVisibility.currentTree == "search"
            @showTags _treeVisibility.previousTree
       )
  buildSearchList: (tagList, term) ->
    @cjSearchBox = @cjTagBox.find(".search") unless @cjSearchBox?
    @cjSearchBox.empty()
    if tagList != null
      for key,tag of tagList
        cjCloneTag = @cjTagBox.find("dt[data-tagid=#{tag.id}]")
        cjCloneTag.clone().appendTo(@cjSearchBox)
        console.log @cjTagBox
        console.log @cjTagBox.find("#tagDropdown_#{tag.id}")
        cjCloneChildren = @cjTagBox.find("#tagDropdown_#{tag.id}]")
        cjCloneChildren.clone().appendTo(@cjSearchBox)
        @enableDropdowns(".search dt[data-tagid=#{tag.id}]")

    else
      @cjSearchBox.append("<div class='noResultsFound'>No Results Found</div>")
    @showTags("search")
  
  showTags: (currentTree, noPrev) ->
    if currentTree != _treeVisibility.currentTree
      @cjTagBox.find(".#{_treeVisibility.currentTree}").toggle() 
      _treeVisibility.previousTree = _treeVisibility.currentTree
      _treeVisibility.currentTree = currentTree
      @cjTagBox.find(".#{currentTree}").toggle()

  autoCompleteEnd: (@instance) ->
    cj("#JSTree-ac").off "keydown"

  enableDropdowns: (tag = "") ->
    cj(".JSTree #{tag} .treeButton").off "click" 
    cj(".JSTree #{tag} .treeButton").on "click", ->
      treeBehavior.dropdownItem(cj(this).parent().parent())

  dropdownItem: (tagLabel) ->
    tagid = tagLabel.data('tagid')
    # console.log tagLabel.siblings("dl#tagDropdown_#{tagLabel.data('tagid')}")
    tagLabel.siblings("dl#tagDropdown_#{tagid}").slideToggle "200", =>
      if tagLabel.is(".open")
        _viewSettings["openTags"][tagid] = false
      else
        _viewSettings["openTags"][tagid] = true
      tagLabel.toggleClass "open"
      bbUtils.localStorage("tagViewSettings", _viewSettings["openTags"])
  readDropdownsFromLocal: () ->
    if bbUtils.localStorage("tagViewSettings")    
      _viewSettings["openTags"] = bbUtils.localStorage("tagViewSettings")
      for tag, bool of bbUtils.localStorage("tagViewSettings")
        if bool
          toPass = cj("dt.tag-#{tag}")
          console.log toPass
          @dropdownItem toPass
        else
          delete _viewSettings["openTags"][tag]
    else
    console.log _viewSettings["openTags"]
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