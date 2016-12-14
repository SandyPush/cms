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


var ocountItem = new FCKToolbarButton('count', FCKLang.countBtn,'',FCK_TOOLBARITEM_ICONTEXT);
//var ocountItem = new FCKToolbarButton('count', FCKLang.countBtn);
ocountItem.IconPath = FCKPlugins.Items['count'].Path + 'count.gif';
FCKToolbarItems.RegisterItem('count', ocountItem);

// The object used for all count operations.
var FCKcount = new Object();

FCKcount = function(name){
	this.Name = name;
}

FCKcount.prototype.GetState = function() {
	
	return FCK_TRISTATE_OFF;
}

FCKcount.prototype.Execute = function(){
	parent.getFckContent();
}


//÷¥––√¸¡Ó
function ExecuteCommand(commandName){
	var oEditor = FCKeditorAPI.GetInstance(FCK.Name);
	oEditor.Commands.GetCommand(commandName).Execute();
	FCKStyles.RemoveAll() ;
}


String.prototype.trim = function(){
  return this.replace(/(^[\s°°]*)|([\s°°]*$)/g, "");
};

String.prototype.leftTrim = function(){
  return this.replace(/(^\s*)/g, "");
};

String.prototype.rightTrim = function(){
  return this.replace(/(\s*$)/g, "");
};


// ◊¢≤·√¸¡Ó
FCKCommands.RegisterCommand('count', new FCKcount('count'));