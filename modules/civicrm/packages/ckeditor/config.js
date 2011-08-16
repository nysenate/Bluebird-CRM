/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	//config.uiColor = '#AADC6E';

    //NYSS integrate kcfinder
    config.filebrowserBrowseUrl = '/sites/all/modules/kcfinder/browse.php?type=files';
    config.filebrowserImageBrowseUrl = '/sites/all/modules/kcfinder/browse.php?type=images';
    config.filebrowserFlashBrowseUrl = '/sites/all/modules/kcfinder/browse.php?type=flash';
    config.filebrowserUploadUrl = '/sites/all/modules/kcfinder/upload.php?type=files';
    config.filebrowserImageUploadUrl = '/sites/all/modules/kcfinder/upload.php?type=images';
    config.filebrowserFlashUploadUrl = '/sites/all/modules/kcfinder/upload.php?type=flash';
 
    // disable auto spell check
    config.scayt_autoStartup = false;
    
    // This is actually the default value.
    config.toolbar_Full =
    [
        ['Bold','Italic','Underline'],
        ['Font','FontSize'],
        //['TextColor','BGColor'], //NYSS
        ['Link','Unlink'],
        ['Image'], //NYSS
        ['NumberedList','BulletedList'], //NYSS
        ['PasteText','PasteFromWord'], //NYSS
        ['RemoveFormat'],
        ['Source'], //NYSS
    ];
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
        infoTab.remove( 'protocol');
		
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
