/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

//NYSS add paths to additional resources
CKEDITOR.plugins.addExternal('aspell', '/sites/all/ext/gov.nysenate.resources/js/aspell/');
CKEDITOR.plugins.addExternal('lineheight', '/sites/all/ext/gov.nysenate.resources/js/lineheight/');

CKEDITOR.editorConfig = function( config ) {
// Define changes to default configuration here.
// For complete reference see:
// http://docs.ckeditor.com/#!/api/CKEDITOR.config

  //NYSS set skin; load via extension folder
  config.skin = 'moonocolor,/sites/all/ext/gov.nysenate.resources/js/moonocolor/';

  // disable auto spell check
  config.scayt_autoStartup = false;

  //NYSS integrate kcfinder
  config.filebrowserBrowseUrl = '/sites/all/modules/kcfinder/browse.php?type=files';
  config.filebrowserImageBrowseUrl = '/sites/all/modules/kcfinder/browse.php?type=images';
  config.filebrowserFlashBrowseUrl = '/sites/all/modules/kcfinder/browse.php?type=flash';
  config.filebrowserUploadUrl = '/sites/all/modules/kcfinder/upload.php?type=files';
  config.filebrowserImageUploadUrl = '/sites/all/modules/kcfinder/upload.php?type=images';
  config.filebrowserFlashUploadUrl = '/sites/all/modules/kcfinder/upload.php?type=flash';

  //NYSS
  config.pasteFromWordPromptCleanup = false;
  config.pasteFromWordRemoveStyles = true;
  config.pasteFromWordRemoveFontStyles = false;
  config.pasteFromWordNumberedHeadingToList = true;
  config.tabSpaces = 5;

  //NYSS additional plugins
  config.extraPlugins = 'font,aspell,justify,colorbutton,image2,lineheight';

  //NYSS support anchors
  config.extraAllowedContent = 'a[name]';

  // The toolbar groups arrangement, optimized for two toolbar rows.
  config.toolbarGroups = [
    { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
    { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
    { name: 'links' },
    { name: 'insert' },
    { name: 'forms' },
    { name: 'tools' },
    { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
    { name: 'others' },
    '/',
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'justify' ] }, //NYSS
    { name: 'styles', groups: [ 'styles' ] },
    { name: 'colors', groups: [ 'colors' ] },
    { name: 'about', groups: [ 'about' ] }
  ];

  // Remove some buttons provided by the standard plugins, which are
  // not needed in the Standard(s) toolbar.
  config.removeButtons = 'Subscript,Superscript,Anchor,Format,Styles,Symbols,Strike,About,PasteFromWord';  // ,Format

  //NYSS allows the browser (non-ie) to take out the context menues
  config.disableNativeSpellChecker = true;
  config.removePlugins = 'scayt,menubutton,wsj'; //,contextmenu'

  // Set the most common block elements.
  config.format_tags = 'p;h1;h2;h3;pre';

  // Simplify the dialog windows.
  config.removeDialogTabs = 'image:advanced;link:advanced';
};

//NYSS 3878 remove some unnecessary elements
CKEDITOR.on('dialogDefinition', function(ev) {
  // Take the dialog name and its definition from the event data.
  var dialogName = ev.data.name;
  var dialogDefinition = ev.data.definition;

  // Check if the definition is from the dialog we're
  // interested in (the 'link' dialog).
  if ( dialogName == 'link' ) {
    // Remove the 'Advanced' tabs from the 'Link' dialog.
    dialogDefinition.removeContents( 'advanced' );

    // Get a reference to the Link Info tab. Remove unnecessary widgets.
    var infoTab = dialogDefinition.getContents( 'info' );
    infoTab.remove( 'browse');
    //infoTab.remove( 'protocol'); //NYSS restored 5003

    var linkTypeItems = infoTab.get( 'linkType' ).items;
    if ( linkTypeItems.length > 0 ) {
      var items_no_anchor = linkTypeItems.slice(0, 1).concat( linkTypeItems.slice(2, linkTypeItems.length) );
      infoTab.get( 'linkType' ).items = items_no_anchor;
    }

    // Get a reference to the Target tab. Remove items from target type list.
    /*var targetTab = dialogDefinition.getContents( 'target' );
     var targetTypeItems = targetTab.get( 'linkTargetType' ).items;
     if ( targetTypeItems.length > 0 ) {
     var items_popup = targetTypeItems.slice(0, 1).concat( targetTypeItems.slice(3, targetTypeItems.length) );
     targetTab.get( 'linkTargetType' ).items = items_popup;
     }*/

    // Remove the target tab altogether
    dialogDefinition.removeContents( 'target' );


  } else if ( dialogName == 'image' ) {
    // Remove the 'Advanced' tabs from the 'Image Properties' dialog.
    dialogDefinition.removeContents( 'advanced' );

    // Remove link target dropdown
    var linkTab = dialogDefinition.getContents( 'Link' );
    linkTab.remove( 'cmbTarget');
  }
});

CKEDITOR.on('instanceReady', function (ev) {
  //console.log('ev: ', ev);

  ev.editor.dataProcessor.htmlFilter.addRules({
    elements: {
      figure: function( el ) {
        //console.log('el: ', el);
        var style = el.attributes.style;
        el.attributes.style = 'margin-inline-start: 5px; margin-inline-end: 5px;' + style;
      }
    }
  });

  ev.editor.dataProcessor.dataFilter.addRules({
    elements: {
      figure: function( el ) {
        //console.log('el: ', el);
        var style = el.attributes.style;
        el.attributes.style = 'margin-inline-start: 5px; margin-inline-end: 5px;' + style;
      }
    }
  });
});
