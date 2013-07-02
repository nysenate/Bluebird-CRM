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


treeBehavior =
  autoCompleteStart: (@instance) ->
    @pageElements = @instance.get 'pageElements'
    cj("#JSTree-data").data("autocomplete" : @instance.getAutocomplete())
    params =
      jqDataReference: "#JSTree-data"
      hintText: "Type in a partial or complete name of an tag or keyword."
      theme: "JSTree"
    searchmonger = cj("#JSTree-ac").tagACInput("init",params)
    cj("#JSTree-ac").on "keydown", (event) =>
      searchmonger.exec(event, (terms) =>
        if terms.tags?
          # console.log terms.tags.length
          # console.log terms
          if terms.tags.length > 0
            @hideTags(@pageElements, terms.tags, terms.term.toLowerCase())
        #   # hide tags
        # if terms.tags.length == 0 and term.length >= 3
        #   # @noResultsTags()
        # if term.length <= 3
        #   # @showTags(@instance)
       )
  hideTags: (@instance, tagList, term) ->
    # children = cj(".#{@pageElements.tagHolder.join(".")} dt")
    @cjTagBox = cj(".#{@pageElements.tagHolder.join(".")}")
    # @cjTagBox.addClass("NV")
    termBox = "<dl class='search'></div>"
    @cjTagBox.append(termBox)
    for key,tag of tagList
      # console.log tag.id
      cjkids = @cjTagBox.find("dt[data-tagid=#{tag.id}]")
      console.log cjkids
      cjkids.clone().appendTo(termBox)
      # console.log cjkids.parentsUntil(".JSTree","dt")
      # .addClass("aNV")
    # cj("top-291").toggle()
    console.log cjkids

    # cj.each(children, (key, tag) =>
    #   console.log cj()
      # if not cj.inArray(,tagList)
        # cj(tag).addClass("NV")
      # else
        # cj(tag).addClass("aNV")
        

    # cj(".#{@pageElements.tagHolder.join(".")} dt.tag-#{tag.id}").addClass("aNV") for index,tag of tagList
      # console.log tag.id
    # cj.each(tagList, (key, tag) =>
    # )
    # notANV = cj(".#{@pageElements.tagHolder.join(".")} dt").not("aNV")
    # notANV.addClass("NV")
    # cj(".#{@pageElements.tagHolder.join(".")} dt.aNV.NV").removeClass("NV")
    # @loadingGif()
    
    # @loadingGif()
  showTags: (@instance) ->

  autoCompleteEnd: (@instance) ->
    cj("#JSTree-ac").off "keydown"

  enableDropdowns: () ->
    cj(".JSTree .treeButton").off "click" 
    cj(".JSTree .treeButton").on "click", ->
      treeBehavior.dropdownItem(cj(this).parent().parent())

  dropdownItem: (tagLabel) ->
    console.log tagLabel
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