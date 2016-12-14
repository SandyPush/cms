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
FCKCommands.RegisterCommand( 'titan_pic', new FCKDialogCommand( FCKLang['DlgdPicTitle']	, FCKLang['DlgdPicTitle'], FCKConfig.PluginsPath + 'titan_pic/titan_pic.html'	, 500, 460 ) ) ;

// Create the "wmv" toolbar button.
var oFindItem		= new FCKToolbarButton('titan_pic', FCKLang['DlgdPicTitle'],null) ;
oFindItem.IconPath	= FCKConfig.PluginsPath + 'titan_pic/titan_pic.gif' ;

// 'wmv' is the name used in the Toolbar config.
FCKToolbarItems.RegisterItem( 'titan_pic', oFindItem ) ;			
