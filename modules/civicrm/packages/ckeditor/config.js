/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

  //NYSS set skin
  config.skin = 'moonocolor';
    
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

  config.extraPlugins = 'font,aspell,justify';

  // The toolbar groups arrangement, optimized for two toolbar rows.
  config.toolbarGroups = [
    { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
    { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
    { name: 'links' },
    { name: 'insert' },
    { name: 'forms' },
    { name: 'tools' },
    { name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
    { name: 'others' },
    '/',
    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'justify' ] },//LCD
    { name: 'styles' },
    { name: 'colors' },
    { name: 'about' }
  ];

  // Remove some buttons, provided by the standard plugins, which we don't
  // need to have in the Standard(s) toolbar.
  config.removeButtons = 'Subscript,Superscript,Anchor,Table,Styles,Format,Symbols,Strike,About';

  //NYSS old config retained for reference
  /*config.toolbar_Full =
  [
      ['Bold','Italic','Underline'],
      ['Font','FontSize'],
      //['TextColor','BGColor'], //NYSS
      ['Link','Unlink'],
      ['Image'], //NYSS ,'HorizontalRule','Smiley'],
      ['NumberedList','BulletedList'], //NYSS ,'Outdent','Indent','Blockquote'],
      ['PasteText','PasteFromWord'], //NYSS ,'SpellChecker'],
      ['SpellCheck'],
      ['RemoveFormat'],
      //['Undo','Redo'], //NYSS
      ['Source'] //NYSS ,'-','Preview','-','About'],
  ];*/

  //allows the browser (non-ie) to take out the context menues
  config.disableNativeSpellChecker = true;
  config.removePlugins = 'scayt,menubutton,wsj'//,contextmenu';
};

//NYSS 3878 remove some unnecessary elements
CKEDITOR.on( 'dialogDefinition', function( ev )
{
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
