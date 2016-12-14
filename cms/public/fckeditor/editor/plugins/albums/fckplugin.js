/*uthor: yxw
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


var oalbumsItem = new FCKToolbarButton('albums', FCKLang.albumsBtn,null);
//var oalbumsItem = new FCKToolbarButton('albums', FCKLang.albumsBtn);
oalbumsItem.IconPath = FCKPlugins.Items['albums'].Path + 'albums.gif';
FCKToolbarItems.RegisterItem('albums', oalbumsItem);

// The object used for all albums operations.
var FCKalbums = new Object();

FCKalbums = function(name){
	this.Name = name;
}

FCKalbums.prototype.GetState = function() {
	
	return FCK_TRISTATE_OFF;
}

FCKalbums.prototype.Execute = function(){
	/*
	parent.tb_show('插入组图','/fckeditor/editor/plugins/albums/form.html?TB_iframe=true&height=500&width=750&modal=false',false);
	try{
		parent.$("#TB_window").easydrag();
		parent.$("#TB_window").setHandler('TB_title'); 
	}catch(e){}  
	*/
	parent.$(function() {
		parent.$.ui.dialog.defaults.bgiframe = true;
		var b = navigator.userAgent.toLowerCase();
		var browser = {
			version: b.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)[1],
			safari: /webkit/.test(b),
			opera: /opera/.test(b),
			chrome: /chrome/.test(b),
			msie: /msie/.test(b) && !/opera/.test(b),
			mozilla: /mozilla/.test(b) && !/(compatible|webkit)/.test(b)
		};		
		if(browser.chrome){
			parent.$('#dialogFrame').attr("scrolling", 'auto');
			parent.$('#dialogFrame').width('99.5%').height('99.3%');	
			var config={title:'插入组图',autoOpen: false,modal:true,height:500,width:800,resizable: false,autoResize: true};
		}else{		
			var config={title:'插入组图',autoOpen: false,modal:true,height:500,width:750,resizable: false,autoResize: true};
		}
		parent.$('#dialog').dialog(config);
		parent.$('#dialog').dialog('open');
	});
	return true;
}

//执行命令
function ExecuteCommand(commandName){
	var oEditor = FCKeditorAPI.GetInstance(FCK.Name);
	oEditor.Commands.GetCommand(commandName).Execute();
	FCKStyles.RemoveAll() ;
}


String.prototype.trim = function(){
  return this.replace(/(^[\s　]*)|([\s　]*$)/g, "");
};

String.prototype.leftTrim = function(){
  return this.replace(/(^\s*)/g, "");
};

String.prototype.rightTrim = function(){
  return this.replace(/(\s*$)/g, "");
};


// 注册命令
FCKCommands.RegisterCommand('albums', new FCKalbums('albums'));

