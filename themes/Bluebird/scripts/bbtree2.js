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

var BBTree = {
//puts everyting in the BBTree namespace.

treeSetting: {
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
},
//BBTree.currentTree
//sets the hard information for the current tree
//the autocomplete list
//the raw json data, split by tree id, 291, 296, 292
//and the html
currentTree: {
  autocomplete: {
    //acTagID[0] = 'tag name'
  },
  rawData: {
    //291 : { json_data }

  },
  html: {
    //291 : '<html>data</html>'

  }
},
startInstance: function() {
  this.treeData.getRawJSON();
  
},
configMethods: {
  createNew: function() {

  },
  getCallSettings: function() {
    return BBTree.treeSetting.defaultSetting.call;
  },
  getDataSettings: function(instance) {
    instance = ((typeof instance !== 'undefined') ? instance : 'defaultSetting');
    return BBTree.treeSetting[instance].data;
  }
},
treeData: {
  tagLvl: 0,
  treeTop: null,
  output: '',
  clearDisplayObj: function() {
    this.tagLvl = 0;
    this.treeTop = null;
    this.output = '';
    return true;
  },
  getCallSettings: function() {
    this['callSettings'] = {};
    cj.extend(this.callSettings, BBTree.configMethods.getCallSettings());
    return true;
  },
  getDataSettings: function(instance) {
    this['dataSettings'] = {};
    cj.extend(this.dataSettings, BBTree.configMethods.getDataSettings(instance));
    return true;
  },
  removeSettings: function(type) {
    this[type] = {};
    return true;
  },
  //pulls just the bare bone tree stucture
  getRawJSON: function(){
    //get: call object
    this.getCallSettings();
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
        BBTree.treeData.parseRawJSON(data.message);
      } 
    });
  },//getRawJSON
  parseRawJSON: function(data) {
    //get: data object
    this.getDataSettings();
    var dataSettings = this.dataSettings;
    //add new data properties
    var rawJSON = {}; 
    var parsedHTML = {};

    //parse each allowed data type set
    cj.each(data, function(i,tID){
      if(cj.inArray(parseFloat(tID.id), dataSettings.pullSets)>-1) 
      {
        rawJSON[tID.id] = {'name':tID.name, 'children':tID.children};
        cj.extend(BBTree.currentTree.rawData, rawJSON);
        BBTree.treeData.writeTreeInit(tID);
        //callTree.defaultSettings.displaySettings.currentTree = tID.id;
        //callTree.parseTreeAjax(tID, displayObj);
        
      }
    });
  }, //parseRawJSON()
  writeTreeInit: function(tID){
    this.tagLvl = 0;
    this.treeTop = tID.id;
    var tagLabel = BBTree.BBTagLabel.set(tID.id);
    //writes the identifying tag label
    this.output += '<dl class="lv-'+this.tagLvl+'" id="'+ tagLabel.addDD+'"">';
    this.output += '<dt class="lv-'+this.tagLvl+' issueCode-'+tID.id;

    // if(cj.inArray(parseFloat(tID.id), callTree.currentSettings.displaySettings.pullSets)>-1) //only writes the 
    // {
    //   if(callTree.currentSettings.callSettings.ajaxSettings.entity_id != 0)
    //   {
    //     displayObj.output += isItemMarked(tID.is_checked,'checked');
    //   }
    //   displayObj.output += ' ' + isItemMarked(tID.is_reserved,'isReserved');
    // }
    this.output += '" id="'+tagLabel.add+'" data-description="'+tID.description+'" ';
    this.output += 'data-parent="'+tID.id+'" data-tagid="'+tID.id+'">';
    // displayObj.output += '<div class="ddControl '+isItemChildless(tID.children.length)+'"></div><div class="tag"><span class="name">'+tID.name+'</span></div>';
    // displayObj.output += addControlBox(tagLabel, displayObj.treeTop, isItemMarked(tID.is_checked,'checked')) + '</dt>';
    // displayObj.output += '<dl class="lv-'+displayObj.tLvl+' '+tagLabel+'" id="" tLvl="'+displayObj.tLvl+'">';
    // displayObj.tLvl++; //start the tree at lv-1
    // return displayObj;
  } //writeTreeInit()
}, // obj treeData
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

//returns array of possible tagLabels
BBTagLabel: {
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
      add: BBTree.BBTagLabel.add(tagID),
      remove: BBTree.BBTagLabel.remove(tagID),
      addDD: BBTree.BBTagLabel.addDD(tagID),
      removeDD: BBTree.BBTagLabel.removeDD(tagID)
    }
    return tagLabel;
  }
}



} //close BBTree


// var log = {
//   tagAction: {
//     0 : {
//       action: 'add',
//       contact: null,
//       tagId: '1799',
//       attributes: '',
//       time: '',
//       error: false
//     },
//     1 : {
//       action: 'remove',
//       contact: null,
//       tagId: '1799',
//       attributes: '',
//       time: '',
//       error: false
//     },
//     2 : {
//       action: 'tag',
//       contact: 87307,
//       tagId: '1799',
//       attributes: null,
//       time: '',
//       error: false
//     },
//     3 : {
//       action: 'untag',
//       contact: 87307,
//       tagId: '1799',
//       attributes: null,
//       time: '',
//       error: false
//     }
//   }
// }






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