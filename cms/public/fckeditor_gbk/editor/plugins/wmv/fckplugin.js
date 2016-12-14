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
FCKCommands.RegisterCommand( 'wmv', new FCKDialogCommand( FCKLang['DlgdMediaTitle']	, FCKLang['DlgdMediaTitle'], FCKConfig.PluginsPath + 'wmv/media.html'	, 450, 350 ) ) ;

// Create the "wmv" toolbar button.
var oFindItem		= new FCKToolbarButton('wmv', FCKLang['InsertMedia'], null) ;
oFindItem.IconPath	= FCKConfig.PluginsPath + 'wmv/media.gif' ;

// 'wmv' is the name used in the Toolbar config.
FCKToolbarItems.RegisterItem( 'wmv', oFindItem ) ;			
