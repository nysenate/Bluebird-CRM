/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	//config.uiColor = '#AADC6E';
    
    // disable auto spell check
    config.scayt_autoStartup = false;
    
    // This is actually the default value.
    config.toolbar_Full =
    [
        ['Bold','Italic','Underline'],
        ['Font','FontSize'],
        ['TextColor','BGColor'],   
        ['Link','Unlink'],
        ['Image','HorizontalRule','Smiley'],
        ['NumberedList','BulletedList','Outdent','Indent','Blockquote'],     
        ['PasteText','PasteFromWord','SpellChecker'],
        ['RemoveFormat'],
        ['Source','-','Preview','-','About'],
    ];
};
