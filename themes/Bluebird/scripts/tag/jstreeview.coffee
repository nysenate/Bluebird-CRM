window.jstree["views"] =
  exec: (instance) ->
    console.log "exec"
    @view = new View(instance)
  done: (instance) ->
    trees = {}
    for a,v of instance.treeNames
      b = _treeUtils.selectByTree(instance.autocomplete, a)
      trees[a] = new Tree(b,a)
    @view.trees = trees
    @view.init()
    console.log "done"
    
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
    # @displaySettings = @instance.get("displaySettings")
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

    for k,v of @instance.treeNames
      tabName = @createTreeTabs(v)
    @setActiveTree(@settings.defaultTree)
    ac = new Autocomplete(@instance, @)
  setActiveTree: (id) ->
    tabName = @getTabNameFromId(id,true)
    cj(".JSTree-tabs .tab-#{tabName}").addClass("active")
    cj(".JSTree .top-#{id}").addClass("active")
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
    # cj(".JSTree")
  buildFilteredList: (a,b,c) ->
    checkAgainst = {}
    for m,n of a
      checkAgainst[m] = []
      for x,y of n
        checkAgainst[m].push(parseFloat(y.id))
    console.log checkAgainst
    buildList = {}
    for d,e of checkAgainst
      buildList[d] = []
      for k,o of @instance.autocomplete
        if e.indexOf(parseFloat(o.id)) >= 0
          buildList[d].push o

    # now you remove trees
    # buildList
    console.log buildList


    # @tagList, @tagId, @filter = false
    # send to tree to make list

  
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
    cjac.on "keydown", ((event) =>
      @filterKeydownEvents event,searchmonger,cjac
    )
    cjac.on "keyup", ((event) =>
      keyCode = bbUtils.keyCode(event)
      # if keyCode.type == "delete" && cjac.val().length <= 3
        # @clearBoard()
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

  execSearch: (event,searchmonger,cjac,lastLetter) ->
    term = cjac.val() + lastLetter
    if term.length >= 3
      openLeg = new OpenLeg
      # addDebounce for openledge
      openLeg.query({"term":term}, (results) =>

      )
      searchmonger.exec(event, (terms) =>
        if terms? && !cj.isEmptyObject(terms)
          # console.log results
          # console.log @addPositionsToTags(results.results)
          # @getNextPositionRound(results)
          tags = @sortSearchedTags(terms.tags)
          hits = @separateHits(tags)
          hcounts = 0
          foundTags = []
          # where trees the tags are in
          for k,v of hits
            hcounts += v
            foundTags.push(parseFloat(k))
          for set of @view.trees
            console.log set
            if foundTags.indexOf(parseFloat(set)) < 0
              hits[set] = 0
              tags[set] = []
          @view.buildFilteredList(tags, terms.term.toLowerCase(), hits)
        
        if terms? && cj.isEmptyObject(terms)
          tags = {}
          @view.buildFilteredList(tags, terms.term.toLowerCase(), {291:0,296:0})
      )

  separateHits: (terms, results) ->
    hits = {}
    for k, v of terms
      hits[k] = v.length
    # hits[292] = results.seeXmore + results.results.length
    hits

  addPositionReminderText: (cjlocation) ->
    positionText = "
            <dl class='top-292 tagContainer' style='display:none'>
              <div class='position-box-text-reminder'>
                Type in a Bill Number or Name for Results
              </div>
            </dl>
          "
    cjlocation.append(positionText)

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

# tree creates new trees
class Tree
  domList: {}
  nodeList: {}
  tabName: ""
  constructor: (@tagList, @tagId, @filter = false) ->
    # console.log @tagList, @tagId
    @buildTree()
    # @cjLocation = 
  buildTree: () ->
    if @filter then filter = "filtered" else filter = "" 
    @domList = cj()
    @domList = @domList.add("<div class='top-#{@tagId} #{filter} tagContainer'></div>")
    @iterate(@tagList)
  iterate: (ary) ->
    cjTagList = cj(@domList)
    # console.log cjTagList
    for node in ary
      @nodeList[node.id] = kNode = new Node(node)
      # does parent exist already?
      if node.parent == @tagId
        cjTagList.append(kNode.html)
      else
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
      console.log 
  html: (node) ->
    if @parent > 0 then treeButton = "treeButton" else treeButton = ""
    if parseFloat(node.is_reserved) != 0 then @reserved = true  else @reserved = false
    # dt first
    html = "<dt class='lv-#{node.level} #{@hasDesc}' id='tagLabel_#{node.id}' data-tagid='#{node.id}' data-name='#{node.name}' data-parentid='#{node.parent}'>"
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
