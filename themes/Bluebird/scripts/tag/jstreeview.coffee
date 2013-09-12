window.jstree["views"] =
  exec: (instance) ->
    console.log "exec"
    @view = new View(instance)
  done: (instance) ->
    trees = {}
    for a in instance.treeNames
      b = _treeUtils.selectByTree(instance.autocomplete, a)
      trees[a] = new Tree(b,a,instance)
    instance.trees = trees
    console.log "done"
    
  view: {}

class View
  constructor: (@instance) ->
    # starts the chain to write the page structure
    @writeContainers()
  writeContainers: () ->
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

# tree creates new trees
class Tree
  domList: {}
  currentDepth: []
  hierarchedList: {}
  nodeList: {}   
  constructor: (@tagList, @tagId, instance) ->
    @buildTree()
    displaySettings = instance.get("displaySettings")
    cj(".JSTree .top-#{displaySettings.defaultTree}").addClass("active")
  buildTree: () ->
    @domList = cj()
    @domList = @domList.add("<div class='top-#{@tagId} tagContainer'></div>")
    @iterate(@tagList)
  iterate: (ary) ->
    cjTagList = cj(@domList)
    console.log cjTagList
    for node in ary
      @nodeList[node.id] = kNode = new Node(node)
      # does parent exist already?
      
      if node.parent == @tagId
        cjTagList.append(kNode.html)
      else
        # console.log kNode.html
        cjTagList.find("dl#tagDropdown_#{kNode.parent}").append(kNode.html)
      # if parent exists attach to parent
      # if parent doesn't exist, attach to list
    cjTagList.appendTo(".JSTree")
  
  

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
    if @parent > 0 then treeButton = "treeButton" else treeButton = ""
    if parseFloat(node.is_reserved) != 0 then @reserved = true  else @reserved = false
    # dt first
    html = "<dt class='lv-#{node.level}' id='tagLabel_#{node.id}' data-tagid='#{node.id}' data-name='#{node.name}' data-parentid='#{node.parent}'>"
    html += "
              <div class='tag'>
                <div class='ddControl #{treeButton}'></div>
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
