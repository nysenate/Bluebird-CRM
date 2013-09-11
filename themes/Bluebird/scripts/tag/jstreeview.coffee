class Tree
  # trees have nodes

class Node
  # nodes have processes
  # nodes have data
  # nodes have name
  # nodes don't have html in the tree

window.jstree["views"] =
  exec: (params) ->
    console.log "exec"
    @view = new View(params)
  done: (params) ->
    console.log "done"
  # newView = new View(instance)




class View
  constructor: (@instance) ->
    # first, write all boxes
    @writeContainers()
    # @interval = @setUpdateInterval(1000)
  writeContainers: () ->
    console.log @instance
    @formatPageElements()
    @addClassesToElement()
  addClassesToElement: () ->
    @cjInitHolderId.html "<div class='#{@addClassHolderString}'></div>"
    @cjInitHolderId.prepend(@menuHtml(@menuName))
    @cjInitHolderId.append(@dataHolderHtml)
    @cjInitHolderId.append(@tokenHolderHtml(@tokenHolder))
    @cjInitHolderId.removeClass(@initHolderId).attr("id", @addIdWrapperString)

  formatPageElements: () ->
    pageElements = @instance.get 'pageElements'
    displayElements = @instance.get 'displaySettings'
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
    @addBoxSizing = pageElements.size
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
  
  separateSizeElements: (el) ->
    el.replace /\./, ""
    el.replace /#/, ""
    classNames = el.split " "
    for a,b in classNames
      el += ".#{b}"
    return el
    
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
        <div class='#{name.tokenHolder}'>
         <div class='#{name.resize}'></div>
         <div class='#{name.body}'>
          <div class='#{name.left}'></div>
          <div class='#{name.options}'></div>
         </div>
        </div>
      "
  dataHolderHtml: () ->
    "<div id='JSTree-data' style='display:none'></div>"