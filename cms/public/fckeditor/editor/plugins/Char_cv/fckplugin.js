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


var ochar_cvItem = new FCKToolbarButton('char_cv', FCKLang.char_cvBtn,'',FCK_TOOLBARITEM_ICONTEXT);
//var ochar_cvItem = new FCKToolbarButton('char_cv', FCKLang.char_cvBtn);
ochar_cvItem.IconPath = FCKPlugins.Items['Char_cv'].Path + 'char_cv.gif';
FCKToolbarItems.RegisterItem('char_cv', ochar_cvItem);

// The object used for all char_cv operations.
var FCKchar_cv = new Object();

FCKchar_cv = function(name){
	this.Name = name;
}

FCKchar_cv.prototype.GetState = function() {
	
	return FCK_TRISTATE_OFF;
}

FCKchar_cv.prototype.Execute = function(){
	FormatText();
}

//格式化操作
function FormatText() {	
	var oEditor = FCKeditorAPI.GetInstance(FCK.Name) ;
	if (oEditor.EditMode == FCK_EDITMODE_WYSIWYG ){ 
		//var html = oEditor.EditorDocument.body.innerHTML; 
		var html = oEditor.GetXHTML(true);
		//var html = processFormatText(oEditor.EditorDocument.selection.createRange().text);
		//ExecuteCommand("RemoveFormat"); 
		//ExecuteCommand("PasteText");	
		
		//html.replace("/<br>/g", "~~~");
		//html=html.replace("/<br \/>/g", "~~~"); 
		//html=html.replace("/<P>/g", "~~~"); 
		//html=html.replace(/<[^>]+>/g,"");
		//html=html.replace("/\n/g", "<br />"); 
		//html=html.replace("/\r/g", "<br />");		
		//copy_clip(html);
		//oEditor.EditorDocument.body.execCommand("Copy");
		//ExecuteCommand("Paste");		
		//shtml=clipboardData.getData("Text"); 
		//alert(shtml);
		//oEditor.EditorDocument.body.innerHTML='';
		//ExecuteCommand("PasteText");				
		var word=false;
		var re = /<\w[^>]*(( class="?MsoNormal"?)|(="mso-))/gi ;
	    if (re.test(html )){
			word=true;
			html=CleanWord(html,1,1);			
	    }
		//alert(html);
		html=replace_html(html,word);				
		oEditor.SetHTML(html);	    
	}  
}


//过滤html标记,包括style和script,保留p,br和img,保留表格
function replace_html(html,flag){	  
	  //过滤背景色
	  html=html.replace(/ bgcolor=[\"]*#[\w\"]*(?=>|\w)+/ig,"");
	  var re18=/<p class=\"img[1]?\" flag=\"1\">|<p flag=\"1\" class=\"img[1]?\">/i;
	  var re17=/<p class=\"img[1]?\">/i;

	  //保留分页符号
	  var re19=/<div style=\"page-break-after: always\">/i;
	  var re20=/<div style=\"page-break-after: always\"><span style=\"display: none\">&nbsp;<\/span><\/div>/i;
	  //新分页符
	  var re21=/<hr style=\"page-break-after: always;?[ ]?\" \/>/i;
	  
	  //过滤class(不完善)
	  if(!html.match(re17) && !html.match(re18))html=html.replace(/class=\"[^\"]*\"/ig,"");	  
	  //过滤style(不完善)
	  if(!html.match(re20)&& !html.match(re21))html=html.replace(/\s*style=\"[^\"]*\"/gi,"");
	
	  //过滤内部样式表
	  html=html.replace(/<style[\s\S]*?>[\s\S]*?<\/style>/ig,"");
	  //过滤内部javascript
	  html=html.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/ig,"");
	  
	  var re=/<(?:(?:\/?[A-Za-z][^>=\s]*(?:[=\s](?:(?!['"])[\s\S]*?|'[^']*'|"[^"]*"))*)|(?:!--[\s\S]*?--))>/g;
        
      var re1=/<img[\s\S]*>/i;

      var re2=/<p( [\s\S])*>/i;
      var re3=/<\/p>/i;

	  var re4=/<br[\s\S]*>/i;

      var re5=/<table[\s\S]*>/i;
	  var re6=/<tr[\s\S]*>/i;
	  var re7=/<td[\s\S]*>/i;
	  var re8=/<tbody[\s\S]*>/i;

	  var re9=/<\/table>/i;
	  var re10=/<\/tr>/i;
	  var re11=/<\/td>/i;
	  var re12=/<\/tbody>/i;	
	  var re13=/<center>/i;
	  var re14=/<\/center>/i;
	  var re15=/<embed[^>]*>/i;
	  var re16=/<\/embed>/i;
	  

      var arr; 	
	  arr=html.match(re);	
	  if(arr){
		  for(var i=0; i<arr.length; i++){			  
			  if(re18.exec(arr[i])){
				  i=i+8;
				  continue;
			  }

			  if(re17.exec(arr[i])){
				  i=i+4;
				  continue;
			  }
			  if(re19.exec(arr[i])){				  
				  i=i+3;
				  continue;
			  }			 
			  if(!(re1.exec(arr[i])) && !(re2.exec(arr[i])) && !(re3.exec(arr[i])) && !(re4.exec(arr[i]))&& !(re5.exec(arr[i]))&& !(re6.exec(arr[i]))&& !(re7.exec(arr[i]))&& !(re8.exec(arr[i]))&& !(re9.exec(arr[i]))&& !(re10.exec(arr[i]))&& !(re11.exec(arr[i])) && !(re12.exec(arr[i]))&& !(re13.exec(arr[i])) && !(re14.exec(arr[i])) && !(re15.exec(arr[i])) && !(re16.exec(arr[i])) && !(re21.exec(arr[i]))){
				   html=html.replace(arr[i],'');			   
			  }else{
				  //alert(arr[i]);
			  }
		  }
	  }
	  //全角转半角
	  html=processFormatText(html);

	  //转换软回车为硬回车	 
	  html=html.replace(/(<br[^>]*>)(?:&nbsp;|　|\s| )*([\s])*(<br[^>]*>)*/gi,"</p><p>　　");
	 
	  //首行缩进
	  html=html.replace(/<p>(?:&nbsp;|　|\s| )*(?!<p[^>]*)(.*?)<\/p>/gi,"<p>　　$1</p>");
	  html=html.replace(/<p([^>]*)(?=class=\"img\"|class=\"img1\")>(?:&nbsp;|　|\s| )*(.*?)<\/p>/gi,"<p$1>　　$2</p>");

	  //过滤空行
	  html=html.replace(/<p[^>]*>(nbsp;)*(　)*(\s)*( )*<\/p>/gi,"");

	  //过滤多余的center
	  html=html.replace(/<center>(?!<embed[^>]*><\/embed>)([\s\S]*)<\/center>/gi,"<p>　　$1<\/p>"); 
	  
      //过滤多余的class
	  html=html.replace(/<p[^>]* class=\"img[1]?\"[^>]*>(?!<img[^>]*>|<a[^>]*>)([\s\S]*)<\/p>/gi,"<p>　　$1<\/p>"); 

      return html;
}



//过滤粘贴过来的word的垃圾代码
function CleanWord(html, bIgnoreFont, bRemoveStyles ){
	html = html.replace(/<o:p>\s*<\/o:p>/g, '') ;
	html = html.replace(/<o:p>.*?<\/o:p>/g, '&nbsp;');

	// Remove mso-xxx styles.
	html = html.replace( /\s*mso-[^:]+:[^;"]+;?/gi, '' );

	// Remove margin styles.
	html = html.replace( /\s*MARGIN: 0cm 0cm 0pt\s*;/gi, '' ) ;
	html = html.replace( /\s*MARGIN: 0cm 0cm 0pt\s*"/gi,  "\"" ) ;
	html = html.replace( /\s*TEXT-INDENT: 0cm\s*;/gi, '' ) ;
	html = html.replace( /\s*TEXT-INDENT: 0cm\s*"/gi, "\"" ) ;
	html = html.replace( /\s*TEXT-ALIGN: [^\s;]+;?"/gi, "\"" ) ;
	html = html.replace( /\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"" ) ;
	html = html.replace( /\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"" ) ;

	html = html.replace( /\s*tab-stops:[^;"]*;?/gi, '' ) ;
	html = html.replace( /\s*tab-stops:[^"]*/gi, '' ) ;

	// Remove FONT face attributes.
	if ( bIgnoreFont ){
		html = html.replace( /\s*face="[^"]*"/gi, '' ) ;
		html = html.replace( /\s*face=[^ >]*/gi, '' ) ;
		html = html.replace( /\s*FONT-FAMILY:[^;"]*;?/gi, '' ) ;
	}

	// Remove Class attributes
	html = html.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3") ;

	// Remove styles.
	if ( bRemoveStyles )
		html = html.replace( /<(\w[^>]*) style="([^\"]*)"([^>]*)/gi, "<$1$3" ) ;

	// Remove empty styles.
	html = html.replace( /\s*style="\s*"/gi, '' ) ;
	html = html.replace( /<SPAN\s*[^>]*>\s*&nbsp;\s*<\/SPAN>/gi, '&nbsp;' ) ;
	html = html.replace( /<SPAN\s*[^>]*><\/SPAN>/gi, '' ) ;

	// Remove Lang attributes
	html = html.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3") ;
	html = html.replace( /<SPAN\s*>(.*?)<\/SPAN>/gi, '$1' ) ;
	html = html.replace( /<FONT\s*>(.*?)<\/FONT>/gi, '$1' ) ;

	// Remove XML elements and declarations
	html = html.replace(/<\\?\?xml[^>]*>/gi, '' ) ;

	// Remove Tags with XML namespace declarations: <o:p><\/o:p>
	html = html.replace(/<\/?\w+:[^>]*>/gi, '' ) ;

	// Remove comments [SF BUG-1481861].
	html = html.replace(/<\!--.*?-->/g, '' ) ;
	html = html.replace( /<(U|I|STRIKE)>&nbsp;<\/\1>/g, '&nbsp;' ) ;
	html = html.replace( /<H\d>\s*<\/H\d>/gi, '' ) ;

	// Remove "display:none" tags.
	html = html.replace( /<(\w+)[^>]*\sstyle="[^"]*DISPLAY\s?:\s?none(.*?)<\/\1>/ig, '' ) ;

	// Remove language tags
	html = html.replace( /<(\w[^>]*) language=([^ |>]*)([^>]*)/gi, "<$1$3") ;

	// Remove onmouseover and onmouseout events (from MS Word comments effect)
	html = html.replace( /<(\w[^>]*) onmouseover="([^\"]*)"([^>]*)/gi, "<$1$3") ;
	html = html.replace( /<(\w[^>]*) onmouseout="([^\"]*)"([^>]*)/gi, "<$1$3") ;

	if ( FCKConfig.CleanWordKeepsStructure ){
		// The original <Hn> tag send from Word is something like this: <Hn style="margin-top:0px;margin-bottom:0px">
		html = html.replace( /<H(\d)([^>]*)>/gi, '<h$1>' ) ;
		// Word likes to insert extra <font> tags, when using MSIE. (Wierd).
		html = html.replace( /<(H\d)><FONT[^>]*>(.*?)<\/FONT><\/\1>/gi, '<$1>$2<\/$1>' );
		html = html.replace( /<(H\d)><EM>(.*?)<\/EM><\/\1>/gi, '<$1>$2<\/$1>' );
	}else{
		html = html.replace( /<H1([^>]*)>/gi, '<div$1><b><font size="6">' ) ;
		html = html.replace( /<H2([^>]*)>/gi, '<div$1><b><font size="5">' ) ;
		html = html.replace( /<H3([^>]*)>/gi, '<div$1><b><font size="4">' ) ;
		html = html.replace( /<H4([^>]*)>/gi, '<div$1><b><font size="3">' ) ;
		html = html.replace( /<H5([^>]*)>/gi, '<div$1><b><font size="2">' ) ;
		html = html.replace( /<H6([^>]*)>/gi, '<div$1><b><font size="1">' ) ;
		html = html.replace( /<\/H\d>/gi, '<\/font><\/b><\/div>' ) ;

		//Transform <P> to <DIV>
		//var re = new RegExp( '(<P)([^>]*>.*?)(<\/P>)', 'gi' ) ;	// Different because of a IE 5.0 error
		//html = html.replace( re, '<div$2<\/div>' ) ;

		// Remove empty tags (three times, just to be sure).
		// This also removes any empty anchor
		html = html.replace( /<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '' ) ;
		html = html.replace( /<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '' ) ;
		html = html.replace( /<([^\s>]+)(\s[^>]*)?>\s*<\/\1>/g, '' ) ;		
	}
	return html;
}


//全角转半角
function processFormatText(text){
	var str=text;
	var result="";
    /*
	str=str.replace(/‘/g,'\'');
	str=str.replace(/’/g,'\'');
	
	str=str.replace(/\．/g,'。');

	str=str.replace(/·/gi,'<span style="font-family: 宋体;">&middot;</span>'); 
	str=str.replace(/&lt;&lt;/gi,'《');
	str=str.replace(/&gt;&gt;/gi,'》');   	
	
	
	for (var i = 0; i < str.length; i++){
		
		if (str.charCodeAt(i)==12288){
			result+= String.fromCharCode(str.charCodeAt(i)-12256);
			continue;
		}
		if (str.charCodeAt(i)>65280 && str.charCodeAt(i)<65375)
			result+= String.fromCharCode(str.charCodeAt(i)-65248);
			else result+= String.fromCharCode(str.charCodeAt(i));
		} 
		  
	}
	*/	 
	str=str.replace(/１/g,'1');
	str=str.replace(/２/g,'2');
	str=str.replace(/３/g,'3');
	str=str.replace(/４/g,'4');
	str=str.replace(/５/g,'5');
	str=str.replace(/６/g,'6');
	str=str.replace(/７/g,'7');
	str=str.replace(/８/g,'8');
	str=str.replace(/９/g,'9');
	str=str.replace(/０/g,'0');
	return str;
}

//兼容方式复制到粘贴板
function copy_clip(meintext){
	if (window.clipboardData){  
		// the IE-manier
		window.clipboardData.setData("Text", meintext);
		// waarschijnlijk niet de beste manier om Moz/NS te detecteren;
		// het is mij echter onbekend vanaf welke versie dit precies werkt:
	}else if (window.netscape){  
		// dit is belangrijk maar staat nergens duidelijk vermeld:
		// you have to sign the code to enable this, or see notes below 
		netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');

		// maak een interface naar het clipboard
		var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
		if (!clip) return;

		// maak een transferable
		var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
		if (!trans) return;  
		// specificeer wat voor soort data we op willen halen; text in dit geval
		trans.addDataFlavor('text/unicode');

		// om de data uit de transferable te halen hebben we 2 nieuwe objecten 
		// nodig om het in op te slaan
		var str = new Object();
		var len = new Object();

		var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);  
		var copytext=meintext;  
		str.data=copytext;  
		trans.setTransferData("text/unicode",str,copytext.length*2);  
		var clipid=Components.interfaces.nsIClipboard;  
		if (!clip) return false;  
		clip.setData(trans,null,clipid.kGlobalClipboard);  
	}	
	alert("转换成功！\n\n");
	//return true;
}


//自动排版
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
FCKCommands.RegisterCommand('char_cv', new FCKchar_cv('char_cv'));