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
