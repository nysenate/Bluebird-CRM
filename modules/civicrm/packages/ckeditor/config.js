/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	//config.uiColor = '#AADC6E';
    
    // NYSS vvvvvvvv -  begin modifications to make IMCE module work with civicrm - kyle
    config.filebrowserBrowseUrl = '/index.php?q=imce&app=ckeditor|url@txtUrl|width@txtWidth|height@txtHeight';
    config.filebrowserImageBrowseUrl = '/index.php?q=imce&app=ckeditor|url@txtUrl|width@txtWidth|height@txtHeight';
    config.filebrowserFlashBrowseUrl = '/index.php?q=imce&app=ckeditor|url@txtUrl|width@txtWidth|height@txtHeight';
    // NYSS ^^^^^^^  -  end modifications to make IMCE woork with civicrm - kyle
    
    // disable auto spell check
    config.scayt_autoStartup = false;
    
    // This is actually the default value.
    config.toolbar_Full =
    [
    // NYSS vvvvvvvv -  begin modifications: fix editor options buttons - kyle
        ['Bold','Italic','Underline'],
        ['Font','FontSize'],
        //['TextColor','BGColor'],   
        ['Link','Unlink'],
        ['Image'],
        ['NumberedList','BulletedList'],     
        ['PasteText','PasteFromWord'],
        ['RemoveFormat'],
        ['Source'],
    // NYSS ^^^^^^^  -  end modifications: fix editor options buttons - kyle
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
 
        // Get a reference to the 'Link Info' tab.
        var infoTab = dialogDefinition.getContents( 'info' );
 
        // Remove unnecessary widgets from the 'Link Info' tab.         
        infoTab.remove( 'browse');
        infoTab.remove( 'protocol');
		
    } else if ( dialogName == 'image' ) {
		// Remove the 'Advanced' tabs from the 'Image Properties' dialog.
        dialogDefinition.removeContents( 'advanced' );
	}
	  
});
