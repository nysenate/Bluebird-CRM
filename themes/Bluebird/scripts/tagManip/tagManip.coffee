class TagManip
  constructor: () ->
    
  findTag: () ->
    # little library to determine which tag it is

  createDT: () ->
    output = "<dt class='lv-#{lvl} tag-#{id}' id='tagLabel_#{id}' data-tagid='#{id}' data-name='#{name}' data-parentid='#{parent}'>"
    output += "<div class='tag'>"
    output += "<div class='ddControl #{treeButton}'></div>"
    output += "<span class='name'>#{name}</span></div>"
    output += "</dt>"

  createDL: () ->
    output = "<dl class='lv-#{lvl}' id='tagDropdown_#{id}' data-name='#{name}'></dl>"
      
  closeDT: () ->

  closeDL: () ->

  removeTag: () ->

  cloneTag: () ->

  parseLvl: () ->

  _currentTag=
    tagName: ""
    cjName: ""
    cj: {}
    tagId: 0
    parentId: 0
    tagLvl: 0
    hasDT: false
    hasDL: false


