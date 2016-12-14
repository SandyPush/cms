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

var oEditor		= window.parent.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;


// Set the dialog tabs.
window.parent.AddTab( 'Info', oEditor.FCKLang.DlgInfoTab ) ;
/*
if ( FCKConfig.FlashUpload )
	window.parent.AddTab( 'Upload', FCKLang.DlgLnkUpload ) ;
*/

// Function called when a dialog tag is selected.
function OnDialogTabChange( tabCode )
{
	ShowE('divInfo'		, ( tabCode == 'Info' ) ) ;
}

// Get the selected media embed (if available).
var oEmbed = FCK.Selection.GetSelectedElement() ;
// Get the active link.
var oLink = FCK.Selection.MoveToAncestorNode( 'A' ) ;
var oImageOriginal ;

function UpdateOriginal( resetSize )
{
	if ( !eImgPreview )
		return ;

	if (!GetE1('picid')||GetE1('picid').length == 0 )
	{
		oImageOriginal = null ;
		return ;
	}

	oImageOriginal = document.createElement( 'IMG' ) ;	// new Image() ;

	if ( resetSize )
	{
		oImageOriginal.onload = function()
		{
			this.onload = null ;
			ResetSizes() ;
		}
	}

	oImageOriginal.src = eImgPreview.src ;
}

var bPreviewInitialized ;
window.onload = function()
{
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	// Load the selected element information (if any).
	LoadSelection() ;

	// Show/Hide the "Browse Server" button.
	//GetE('tdBrowse').style.display = FCKConfig.FlashBrowser	? '' : 'none' ;

	// Set the actual uploader URL.
	//if ( FCKConfig.FlashUpload )
	//GetE('frmUpload').action = FCKConfig.FlashUploadURL ;

	window.parent.SetAutoSize( true ) ;

	// Activate the "OK" button.
	window.parent.SetOkButton( true ) ;
}

function LoadSelection()
{
	if ( ! oEmbed ) {
		return ;
	} else {
		//var sUrl = GetAttribute( oEmbed, 'src', '' ) ;
	
	GetE('txtUrl').value    = GetAttribute( oEmbed, 'src', '' ) ;

	//Get Advances Attributes
	GetE('txtAttId').value		= oEmbed.id ;
	GetE('txtAlt').value = GetAttribute( oEmbed, 'title', '') ;

	GetE('txtUrl').value    = sUrl ;
	//GetE('txtAlt').value    = GetAttribute( oImage, 'alt', '' ) ;
	GetE('txtVSpace').value	= GetAttribute( oImage, 'vspace', '' ) ;
	GetE('txtHSpace').value	= GetAttribute( oImage, 'hspace', '' ) ;
	GetE('txtBorder').value	= GetAttribute( oImage, 'border', '' ) ;
	GetE('cmbAlign').value	= GetAttribute( oImage, 'align', '' ) ;

	var iWidth, iHeight ;

	var regexSize = /^\s*(\d+)px\s*$/i ;

	if ( oImage.style.width )
	{
		var aMatchW  = oImage.style.width.match( regexSize ) ;
		if ( aMatchW )
		{
			iWidth = aMatchW[1] ;
			oImage.style.width = '' ;
			SetAttribute( oImage, 'width' , iWidth ) ;
		}
	}

	if ( oImage.style.height )
	{
		var aMatchH  = oImage.style.height.match( regexSize ) ;
		if ( aMatchH )
		{
			iHeight = aMatchH[1] ;
			oImage.style.height = '' ;
			SetAttribute( oImage, 'height', iHeight ) ;
		}
	}

	GetE('txtWidth').value	= iWidth ? iWidth : GetAttribute( oImage, "width", '' ) ;
	GetE('txtHeight').value	= iHeight ? iHeight : GetAttribute( oImage, "height", '' ) ;

	// Get Advances Attributes
	GetE('txtAttId').value			= oImage.id ;
	GetE('cmbAttLangDir').value		= oImage.dir ;
	GetE('txtAttLangCode').value	= oImage.lang ;
	GetE('txtAttTitle').value		= oImage.title ;
	GetE('txtLongDesc').value		= oImage.longDesc ;

	if ( oEditor.FCKBrowserInfo.IsIE )
	{
		GetE('txtAttClasses').value = oImage.className || '' ;
		GetE('txtAttStyle').value = oImage.style.cssText ;
	}
	else
	{
		GetE('txtAttClasses').value = oImage.getAttribute('class',2) || '' ;
		GetE('txtAttStyle').value = oImage.getAttribute('style',2) ;
	}

	if ( oLink )
	{
		var sLinkUrl = oLink.getAttribute( '_fcksavedurl' ) ;
		if ( sLinkUrl == null )
			sLinkUrl = oLink.getAttribute('href',2) ;

		GetE('txtLnkUrl').value		= sLinkUrl ;
		GetE('cmbLnkTarget').value	= oLink.target ;
	}

	UpdatePreview() ;
	}
}

//The OK button was hit.
function Ok()
{
	if(!GetE1('picid'))return;
	if ( GetE1('picid').length == 0 )
	{
		window.parent.SetSelectedTab( 'Info' ) ;
		GetE('picid').focus() ;
		alert( oEditor.FCKLang.DlgAlertUrl ) ;
		return false ;
	}

	if (!oEmbed) {
			var title=GetE('txtAlt').value;
			if(GetE('cmbAlign').value=='middle'){				
				oEmbed = FCK.InsertElement('ccimg',title);
			}else{				
				oEmbed = FCK.InsertElement('ccimg1',title);
			}			
	} else {
		oEditor.FCKUndo.SaveUndoStep() ;
	}
	UpdateEmbed( oEmbed );
	return true ;
}

function UpdateEmbed( e )
{
	//var e=obj[0];
	e.src = GetE1('picid');	
	// Advances Attributes
	SetAttribute( e, 'id' , GetE('picid').value ) ;
	SetAttribute( e, 'title', '点击查看视界大图');	

	SetAttribute( e, "alt"   , GetE('txtAlt').value ) ;
	SetAttribute( e, "width" , GetE('txtWidth').value ) ;
	SetAttribute( e, "height", GetE('txtHeight').value ) ;
	SetAttribute( e, "vspace", GetE('txtVSpace').value ) ;
	SetAttribute( e, "hspace", GetE('txtHSpace').value ) ;
	SetAttribute( e, "border", GetE('txtBorder').value ) ;
	SetAttribute( e, "align" , GetE('cmbAlign').value ) ;

	e.parentNode.href=GetE2();	
	e.parentNode.target='_blank';

	var obj=e.parentNode.nextSibling.getElementsByTagName('A')[0];	
	obj.href=GetE3();
	obj.target='_blank';
	
	/*
	var str=e.parentNode.nextSibling.outerHTML;
	var span = FCK.EditorDocument.createElement( 'span' ) ;
	span.innerHTMl=GetE('txtAlt').value+'  '+str;
	alert(span.innerHTMl);
	*/

	/*
	e.parentNode.parentNode.removeChild(e.parentNode.nextSibling);
	e.parentNode.parentNode.insertBefore(span,e.parentNode);	
	e.parentNode.parentNode.appendChild(span);
	*/

	/*
	e.parentNode.nextSibling.replaceNode(span);	
	*/
	
	var newElement=FCK.EditorDocument.createTextNode(GetE('txtAlt').value+'　'); 	
	e.parentNode.nextSibling.insertBefore(newElement,obj); 
}


function UpdateImage( e, skipId )
{
	//e.src = GetE('txtUrl').value ;
	e.src = GetE1('picid');		
	//SetAttribute( e, "_fcksavedurl", GetE('txtUrl').value ) ;
	SetAttribute( e, "alt"   , GetE('txtAlt').value ) ;
	SetAttribute( e, "width" , GetE('txtWidth').value ) ;
	SetAttribute( e, "height", GetE('txtHeight').value ) ;
	SetAttribute( e, "vspace", GetE('txtVSpace').value ) ;
	SetAttribute( e, "hspace", GetE('txtHSpace').value ) ;
	SetAttribute( e, "border", GetE('txtBorder').value ) ;
	SetAttribute( e, "align" , GetE('cmbAlign').value ) ;
	

	// Advances Attributes

	//if ( ! skipId )
	//SetAttribute( e, 'id', GetE('txtAttId').value ) ;
	//SetAttribute( e, 'dir'		, GetE('cmbAttLangDir').value ) ;
	//SetAttribute( e, 'lang'		, GetE('txtAttLangCode').value ) ;
	//SetAttribute( e, 'title'	, GetE('txtAttTitle').value ) ;
	//SetAttribute( e, 'longDesc'	, GetE('txtLongDesc').value ) ;
	/*
	if ( oEditor.FCKBrowserInfo.IsIE )
	{
		e.className = GetE('txtAttClasses').value ;
		e.style.cssText = GetE('txtAttStyle').value ;
	}
	else
	{
		SetAttribute( e, 'class'	, GetE('txtAttClasses').value ) ;
		SetAttribute( e, 'style', GetE('txtAttStyle').value ) ;
	}
	*/
	SetAttribute( e, 'style', '' ) ;
}

var eImgPreview ;
var eImgPreviewLink ;

function SetPreviewElements( imageElement, linkElement )
{
	eImgPreview = imageElement ;
	eImgPreviewLink = linkElement ;

	UpdatePreview() ;
	UpdateOriginal() ;

	bPreviewInitialized = true ;
}

function UpdatePreview()
{
	if ( !eImgPreview || !eImgPreviewLink )
		return ;

	if (!GetE1('picid') ||GetE1('picid').length == 0 )
		eImgPreviewLink.style.display = 'none' ;
		
	else
	{
		//alert(eImgPreview.id);
		UpdateImage(eImgPreview, true ) ;
		/*
		if ( GetE('txtLnkUrl').value.Trim().length > 0 )
			eImgPreviewLink.href = 'javascript:void(null);' ;
		else
			SetAttribute( eImgPreviewLink, 'href', '' ) ;
		*/
		eImgPreviewLink.style.display = '' ;
		
	}
}

var bLockRatio = true ;

function SwitchLock( lockButton )
{
	bLockRatio = !bLockRatio ;
	lockButton.className = bLockRatio ? 'BtnLocked' : 'BtnUnlocked' ;
	lockButton.title = bLockRatio ? 'Lock sizes' : 'Unlock sizes' ;

	if ( bLockRatio )
	{
		if ( GetE('txtWidth').value.length > 0 )
			OnSizeChanged( 'Width', GetE('txtWidth').value ) ;
		else
			OnSizeChanged( 'Height', GetE('txtHeight').value ) ;
	}
}

// Fired when the width or height input texts change
function OnSizeChanged( dimension, value )
{
	// Verifies if the aspect ration has to be maintained
	if ( oImageOriginal && bLockRatio )
	{
		var e = dimension == 'Width' ? GetE('txtHeight') : GetE('txtWidth') ;

		if ( value.length == 0 || isNaN( value ) )
		{
			e.value = '' ;
			return ;
		}

		if ( dimension == 'Width' )
			value = value == 0 ? 0 : Math.round( oImageOriginal.height * ( value  / oImageOriginal.width ) ) ;
		else
			value = value == 0 ? 0 : Math.round( oImageOriginal.width  * ( value / oImageOriginal.height ) ) ;

		if ( !isNaN( value ) )
			e.value = value ;
	}

	UpdatePreview() ;
}

// Fired when the Reset Size button is clicked
function ResetSizes()
{
	if ( ! oImageOriginal ) return ;

	GetE('txtWidth').value  = oImageOriginal.width ;
	GetE('txtHeight').value = oImageOriginal.height ;

	UpdatePreview() ;
}

/*
function BrowseServer()
{
	OpenFileBrowser( FCKConfig.FlashBrowserURL, FCKConfig.FlashBrowserWindowWidth, FCKConfig.FlashBrowserWindowHeight ) ;
}
*/


function GetE1(){
	var id=GetE('picid').value;
	if(!id)return;
	var dir=(Math.floor(id/1000))*1000;	
	return 'http://img2008.titan24.com/imgwater/'+dir+'/'+id+'.jpg'; 
}

function GetE2(){
	var id=GetE('picid').value;
	if(!id)return;
	return 'http://pic.titan24.com/photo?id='+id; 
}

function GetE3(){
	var id=GetE('picid').value;
	if(!id)return;
	return 'http://pic.titan24.com/photo?id='+id+'&album'; 
}

function SetUrl( url )
{
	GetE('txtUrl').value = url ;
	window.parent.SetSelectedTab( 'Info' ) ;
}


//兼容方式的取图片宽度和高度
function loadImage() {
    var img = new Image(); //创建一个Image对象，实现图片的预下载
    img.src = eImgPreview.src;
   
    if (img.complete) { // 如果图片已经存在于浏览器缓存，直接调用回调函数
        ResetSizes();
        return; // 直接返回，不用再处理onload事件
    }

    img.onload = function () { //图片下载完毕时异步调用ResetSizes函数。
        ResetSizes();
    };
};



/*
function OnUploadCompleted( errorNumber, fileUrl, fileName, customMsg )
{
	switch ( errorNumber )
	{
		case 0 :	// No errors
			alert( '上传成功' ) ;
			break ;
		case 1 :	// Custom error
			alert( customMsg ) ;
			return ;
		case 101 :	// Custom warninga
			alert( customMsg ) ;
			break ;
		case 201 :
			alert( '服务器上存在一个同名文件. 上传的文件被自动更名为 "' + fileName + '"' ) ;
			break ;
		case 202 :
			alert( '文件类型非法' ) ;
			return ;
		case 203 :
			alert( "权限不足，无法上传。请检查您的服务器设置" ) ;
			return ;
		default :
			alert( '上传错误，错误号为: ' + errorNumber ) ;
			return ;
	}

	SetUrl( fileUrl ) ;
	GetE('frmUpload').reset() ;
}

var oUploadAllowedExtRegex	= new RegExp( FCKConfig.FlashUploadAllowedExtensions, 'i' ) ;
var oUploadDeniedExtRegex	= new RegExp( FCKConfig.FlashUploadDeniedExtensions, 'i' ) ;

function CheckUpload()
{
	var sFile = GetE('txtUploadFile').value ;
	
	if ( sFile.length == 0 )
	{
		alert( '上传文件不能为空' ) ;
		return false ;
	}
	
	if ( ( FCKConfig.FlashUploadAllowedExtensions.length > 0 && !oUploadAllowedExtRegex.test( sFile ) ) ||
		( FCKConfig.FlashUploadDeniedExtensions.length > 0 && oUploadDeniedExtRegex.test( sFile ) ) )
	{
		OnUploadCompleted( 202 ) ;
		return false ;
	}
	
	return true ;
}
*/