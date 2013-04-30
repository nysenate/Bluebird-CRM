/*
* BBTree JS 1
* For Bluebird Version 1.4.1
* Now with infinite looping!
* And modular abilities!
* Last Updated: 1-23-2013
* Coded: Dan Pozzie (dpozzie@gmail.com)
*/

// Psuedocode:

// Pathing Func.: 
//   Start Instance:
//     Pull Configuration
//     Run Data Pull
//     Put Data in containers after data is run.

// Tree Creation Control Func.:    
//   Get Configuration- f
//   Get Data from Configurations
//   
//   Create Containers
//   Delete Containers
//   Write Data to specific containers
//   Write Autocomplete list

// Tagging Specific Controls.:
//   Get contact tags
//   Add Contact tags
//   Remove Contact tags
//   Clear contact tags

// Modal Control Func.:
//   Create Modal Container
//   Get Modal Information

// Tree Editing Control Func.:
//   Add New Tag to Tree, Refresh & List
//   Remove Tag from Tree, Refresh & List
//   Update Tag from Tree, Refresh & List
//   (thats it. move is a remove and an add, merge is a remove, convert is a move and an add)


// Error Message Library:

var BBTree = function() {


//data structures
var treeSetting = {
  //each particular tree instance will have it's own 'setting'
  defaultSetting: {
    //page CSS defaults
    page: {
      wrapper: '.BBTreeContainer',
      tagHolder: '.BBTree',
      messageHandler: '.BBMessages',
      tabLocation: '.BBTree-Tags',
      location: ''
    },
    //sets data for what sets to pull
    data: {
      //Set [one], or [other] to show only one, use [291,296] for both (when you want to show KW & IC)
      pullSets: [291, 296, 292],
      //IssueCodes = 291, KW = 296. Sets default tree to show first.
      defaultTree: 291,
      //contact is set to 0 and SHOULD appear on most contact inits
      contact: 0
    },
    display: {
      //Sets default type to appear: edit/tagging
      mode: 'edit',
      //Size? full includes tag tree
      fullSize: true,
      //autocomplete box is turned on
      autocomplete: true,
      //print tags option
      print: true,
      //show all active tags
      showActive: true
    },
    //ajax related settings
    call: {
      //interrupts ajax tag save process to not happen for edit type pages
      onSave: false,
      //where to grab the tree
      ajaxUrl: '/civicrm/ajax/tag/tree',
      ajaxSettings:{
        entity_type: 'civicrm_contact',
        entity_id: 0,
        call_uri: window.location.href,
        entity_counts: 0
      }
    }
  }
}
var currentTree = {
  autocomplete: {
    //acTagID[0] = 'tag name'
  },
  rawData: {
    //291 : { json_data }

  },
  html: {
    //291 : '<html>data</html>'

  }
}

var configMethods = {
  createNew: function() {

  },
  getCallSettings: function(obj,instance) {
    instance = ((typeof instance !== 'undefined') ? instance : 'defaultSetting');
    obj['callSettings'] = {};
    cj.extend(obj['callSettings'], treeSetting[instance].call);
    return true;
  },
  getDataSettings: function(obj,instance) {
    instance = ((typeof instance !== 'undefined') ? instance : 'defaultSetting');
    obj['dataSettings'] = {};
    cj.extend(obj.dataSettings, treeSetting[instance].data);
    return true;
  },
  removeSettings: function(obj,type,instance) {
    instance = ((typeof instance !== 'undefined') ? instance : 'defaultSetting');
    obj[type] = {};
    return true;
  }

}

var treeData = {
  //data
  tagLvl: 0,
  treeTop: null,
  output: '',

  //pulls just the bare bone tree stucture
  getRawJSON: function(){
    //get: call object
    configMethods.getCallSettings(this);
    configMethods.getDataSettings(this);
    
    cj.ajax({
      url: this.callSettings.ajaxUrl,
      data: {
        entity_type: this.callSettings.ajaxSettings.entity_type,
        call_uri: this.callSettings.ajaxSettings.call_uri,
        entity_counts: this.callSettings.ajaxSettings.entity_counts
      },
      dataType: 'json',
      success: function(data, status, XMLHttpRequest) {
        //starts the parsing methods
        treeData.parseRawJSON(data.message);
      } 
    });
  },//getRawJSON
  parseRawJSON: function(data) {
    //add new data properties
    var rawJSON = {}; 
    var parsedHTML = {};
    var that = this;

    //parse each allowed data type set
    cj.each(data, function(i,tID){
      if(cj.inArray(parseFloat(tID.id), treeData.dataSettings.pullSets)>-1) 
      {
        rawJSON[tID.id] = {'name':tID.name, 'children':tID.children};
        cj.extend(currentTree.rawData, rawJSON);
        that.writeTreeInit(tID.id);
        //callTree.defaultSettings.displaySettings.currentTree = tID.id;
        that.parseTreeAjax(tID);
      }
    });
  }, //parseRawJSON()
  writeTreeInit: function(treeToParseID){
    treeData.clearDisplayObj();
    this.tagLvl = 0;
    this.treeTop = treeToParseID;
    var treeName = currentTree.rawData[treeToParseID].name;
    var treeChildren = currentTree.rawData[treeToParseID].children;
    var treeLabel = BBTagLabel.set(treeToParseID);

    //writes the identifying tag label
    this.output += '<dl class="lv-'+this.tagLvl+'" id="'+ treeLabel.addDD+'"">';
      this.output += '<dt class="lv-'+this.tagLvl+' issueCode-'+this.treeTop;
      this.output += '" id="'+treeLabel.add+'"" ';
      this.output += '" data-tagid="'+this.treeTop+'">';
        this.output += '<div class="tag"><span class="name">'+treeName+'</span></div>'; // <!-- /tag name -->
      this.output += '</dt>'; // <!-- /.dt -->
      //if there's children, open up a new tag holder.
      if(treeChildren.length > 0 ) {
        this.output += '<dl class="lv-'+this.tagLvl+' '+treeLabel+'" id=""';
        this.tagLvl++; //start the tree at lv-1
      } else {
        //throw error of no children to display.
      }
  }, //writeTreeInit()
  parseTreeAjax: function(tID){
    console.log(currentTree.rawData[this.treeTop].children);
    var treeData = this.parseJsonInsides(currentTree.rawData[this.treeTop]);
    currentTree.html[tID.id] = {'name':tID.name, 'data':treeData};
    console.log(currentTree.html);
  },
  parseJsonInsides: function(tID){
    var that = this;
    cj.each(tID.children, function(i, cID){//runs all first level
      that.writeTagLabel(cID, tID.id);
      if(cID.children.length > 0)
      {
        callTree.writeJsonTag(cID);
      }
    });
    return true;
  },
  writeJsonTag: function(tID){//in second level & beyond
    var that = this;
    this.openChildJsonTag(tID, displayObj);
    cj.each(tID.children, function(i, cID){
      that.writeTagLabel(cID, tID.id);
      if(cID.children.length > 0)
      {
        that.writeJsonTag(cID);
      }
    });
    this.closeChildJsonTag(tID);
  },
  writeTagLabel: function(cID, parentTag){
    if(typeof parentTag === 'undefined')
    {
      parentTag = this.treeTop;
    }
    var tagLabel = BBTagLabel.set(cID.id);
    this.output += '<dt class="lv-'+this.tagLvl+' ';
      this.output += isItemMarked(cID.is_reserved,'isReserved')+'" ';
      this.output += 'id="'+tagLabel.add+'" data-description="'+cID.description+'" ';
      this.output += 'tLvl="'+this.tagLvl+'" data-tagid="'+cID.id+'" ';
      this.output += 'data-parentid="'+parentTag+' data-tagname="'+cID.name+'">';
      this.output += '<div class="ddControl '+ isItemChildless(cID.children.length) + '"></div>';
      this.output += '<div class="tag"><span class="name">'+cID.name+'</span></div>';
      this.output += this.addEntityCount(cID.entity_count);
      this.output += this.addControlBox(tagLabel, this.treeTop, isItemMarked(cID.is_checked,'checked'));
    this.output += '</dt>'; // <!-- /dt -->
    //redesigning what tags look like & do
// <dl class="lv-1" id="tagDropdown_1717">
//   <dt class="lv-2 " id="tagLabel_1723" data-description="null" data-tagid="1723" data-parentid="1717" data-tagname="ababa">
//     <div class="ddControl treeButton"></div>
//     <div class="tagName">ababa</div>
//     <span class="entityCount" style="display:none">Unknown</span>
//     <span class="controlDirectives">
//       <ul>
//         <li class="addTag" title="Add New Tag" data-action="add" data-tagid="1723" onclick="BBTreeModal.makeModal(this)"></li>
//         <li class="removeTag" title="Remove Tag" data-action="remove" data-tagid="1723" onclick="BBTreeModal.makeModal(this)"></li>
//         <li class="moveTag" title="Move Tag" data-action="move" data-tagid="1723" onclick="BBTreeModal.makeModal(this)"></li>
//         <li class="updateTag" title="Update Tag" data-action="update" data-tagid="1723" onclick="BBTreeModal.makeModal(this)"></li>
//         <li class="mergeTag" title="Merge Tag" data-action="merge"data-tagid="1723" onclick="BBTreeModal.makeModal(this)"></li>
//       </ul>
//     </span>
//   </dt>
// </dl>

    //'/*+isItemChildless(cID.children.length)+*/
  },
  writeTagContainer: function(tID){
    var tagLabel = addTagLabel(tID.id);
    this.output += '<dl class="lv-'+this.tagLvl+'" id="'+tagLabel.add+'">';
  },
  openChildJsonTag: function(tID){
    this.writeTagContainer(tID);
    this.tagLvl++;
  },
  closeChildJsonTag: function(tID){
    this.tagLvl--;
    this.output += '</dl>';
  },
  //helpers
  clearDisplayObj: function(treeID) {
    cj.extend(this.treeDef, {tagLvl: 0,treeTop: null,output: ''});
    return true;
  },
  isItemChildless: function(childLength) {
    return (childLength > 0) ? 'treeButton' :  '' ;
  },
  //add Entity Span
  addEntityCount: function(count) {
    (this.callSettings.ajaxSettings.entity_counts != 0) ? 
      var add = '<span class="entityCount">('+count+')</span>' :
      var add = '<span class="entityCount" style="display:none">Unknown</span>';
    return add;
  },
  //adds Control Box
  addControlBox: function(tagLabel,isChecked) { //should break this up 
    var floatControlBox;
    if(callTree.currentSettings.displaySettings.buttonType == 'edit')
    {
      floatControlBox = '<span class="fCB">';
      floatControlBox += '<ul>';
      if(291 == treeTop)
      {
        floatControlBox += '<li class="addTag" title="Add New Tag" do="add" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
        floatControlBox += '<li class="removeTag" title="Remove Tag" do="remove" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
        floatControlBox += '<li class="moveTag" title="Move Tag" do="move" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
        floatControlBox += '<li class="updateTag" title="Update Tag" do="update" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
        floatControlBox += '<li class="mergeTag" title="Merge Tag" do="merge" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
      }
      if(296 == treeTop)
      {
        floatControlBox += '<li class="removeTag" title="Remove Keyword" do="remove" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
        floatControlBox += '<li class="updateTag" title="Update Keyword" do="update"  onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
        floatControlBox += '<li class="mergeTag" title="Merge Keyword" do="mergeKW" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
        floatControlBox += '<li class="convertTag" title="Convert Keyword" do="convert" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li>';
      }
      floatControlBox += '</span>';
      
    }
    if(callTree.currentSettings.displaySettings.buttonType == 'tagging')
    {
      var displayChecked = '';
      floatControlBox = '<span class="fCB">';
      floatControlBox += '<ul>';
      floatControlBox += '<li>';
      if(isChecked == ' checked'){
        //NOTE: HAVE TO HAVE name="tag[###]" in order for edit tags to work
        floatControlBox += '<input type="checkbox" name="tag['+removeTagLabel(tagLabel)+']" class="checkbox checked"  checked onclick="BBTreeTag.checkRemoveAdd(this, \''+tagLabel+'\')"></input></li></ul>';
      } else {
        floatControlBox += '<input type="checkbox" name="tag['+removeTagLabel(tagLabel)+']" class="checkbox" onclick="BBTreeTag.checkRemoveAdd(this, \''+tagLabel+'\')"></input></li></ul>';
      }
      floatControlBox += '</span>';
      if(tagLabel != 'tagLabel_291' && tagLabel != 'tagLabel_296')
      {
        return(floatControlBox);
      } else { 
        return ''; 
      }
    }
    if((tagLabel == 'tagLabel_291' || tagLabel == 'tagLabel_296') && callTree.currentSettings.displaySettings.buttonType != 'modal')
    {
      return '<span class="fCB" ><ul><li class="printTag"  onClick="printTags()"> </li><li class="addTag" title="Add New Tag" do="add" onclick="BBTreeModal.makeModal(this,\''+ tagLabel +'\')"></li></ul></span>'; 
    } 
    else 
    { 
      return(floatControlBox); 
    }
  }
}

var BBTagLabel = {
  add: function(tagID) {
    return 'tagLabel_' + tagID;
  },
  remove: function(tagID) {
    return tagID.replace('tagLabel_', '');
  },
  addDD: function(tagID) {
    return 'tagDropdown_' + tagID;
  },
  removeDD: function(tagID) {
    return tagID.replace('tagDropdown_', '');
  },
  set: function(tagID) {
    var tagLabel = {
      add: BBTagLabel.add(tagID),
      remove: BBTagLabel.remove(tagID),
      addDD: BBTagLabel.addDD(tagID),
      removeDD: BBTagLabel.removeDD(tagID)
    }
    return tagLabel;
  }
}


//public functions
return {
  startInstance: function(){
    treeData.getRawJSON();
  }
}





}();
//marks item as checked or reserved
function isItemMarked(value, type)
{
  if(value == true)
  {
    return(type);
  }
  else {
    return '';
  }
}
//if has children, return either arrow or nothing
function isItemChildless(childLength)
{
  if(childLength > 0)
  {
    return('treeButton');
  }
  else
  {
    return '';
  }
}



function returnTime(note)
{
  if(!note)
  {
    note = '';
  }
  var time = new Date();
  var rTime = time.getMinutes() + ':' + time.getSeconds() + ':' + time.getMilliseconds();
  console.log(note);
  console.log(rTime);
}