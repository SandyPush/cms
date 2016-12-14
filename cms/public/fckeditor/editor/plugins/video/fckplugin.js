/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2006 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * "Support Open Source software. What about a donation today?"
 */

// Register the related commands.
FCKCommands.RegisterCommand( 'video', new FCKDialogCommand( FCKLang['DlgdMediaTitle']	, FCKLang['DlgdMediaTitle'], FCKConfig.PluginsPath + 'video/video.html'	, 800, 600 ) ) ;

// Create the "wmv" toolbar button.
var oFindItem		= new FCKToolbarButton('video', FCKLang['InsertMedia'], null) ;
oFindItem.IconPath	= FCKConfig.PluginsPath + 'video/video.png' ;

// 'wmv' is the name used in the Toolbar config.
FCKToolbarItems.RegisterItem( 'video', oFindItem ) ;			
