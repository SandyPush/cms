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
window.parent.AddTab( 'Info', FCKLang['Info'] ) ;

// Function called when a dialog tag is selected.
function OnDialogTabChange( tabCode )
{
	ShowE('divInfo'		, ( tabCode == 'Info' ) ) ;
}


window.onload = function()
{
    uri=location.search;
    if(uri==undefined){
        uri="";
    }
    $.get("/video/video/ajax"+uri,function(data){
       $("#divInfo").html(data);
       $("#showData tr").each(function(i){
            $(this).click(function(){
                $(this).find('input[type="radio"]').attr("checked", true);
            });
       });
    });
    
    window.parent.SetOkButton( true ) ;
}
var FCKDomRange=function(A){this.Window=A;this._Cache={};};
//The OK button was hit.
function Ok()
{
    var radioContent=$("#showData").find('input:radio:checked').val();
    var radioObj = $.parseJSON(radioContent);
    if(radioObj.videoLink==undefined || radioObj.videoLink==null){
        alert('请选择视频！');
        return false;
    }else{
        videoHTML='<object width="630" height="534" id="play1" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0">'
                    +'<param value="http://www.v1.cn/player/cloud/cloud_player.swf" name="movie" />'
                    +'<param value="#000000" name="bgcolor" />'
                    +'<param value="id='+radioObj.vid+'&amp;startSwfUrl=http://www.v1.cn/player/cloud/loading.swf&amp;videoUrl='+radioObj.videoLink+'" name="FlashVars" />'
                    +'<param value="always" name="allowScriptAccess" />'
                    +'<param value="true" name="allowFullScreen" />'
                    +'<param value="transparent" name="wmode" />'
                    +'<embed width="630" height="534" type="application/x-shockwave-flash" cover="'+radioObj.picLink+'" wmode="opaque" allowfullscreen="true" allowscriptaccess="always" bgcolor="#000000" name="play1" flashvars="id='+radioObj.vid+'&amp;startSwfUrl=http://www.v1.cn/player/cloud/loading.swf&amp;videoUrl='+radioObj.videoLink+'" src="http://www.v1.cn/player/cloud/cloud_player.swf"></embed>'
                    +'</object>';
        oEmbed=FCK.EditorDocument.createElement('DIV');
        SetAttribute( oEmbed, 'id', 'playDiv');
        SetAttribute( oEmbed, 'class', 'video_play');
        $(oEmbed).html(videoHTML);
        vids=$("#vids",window.parent.parent.document).val();
        $("#vids",window.parent.parent.document).val(vids+radioObj.vid+'|');
        $("input[name='image']",window.parent.parent.document).val(radioObj.picLink);
        $("#img_preview",window.parent.parent.document).html('<img src="'+radioObj.picLink+'">');
        $("#title",window.parent.parent.document).val(radioObj.title);
        $("#short_title",window.parent.parent.document).val(radioObj.title);
        $("#intro",window.parent.parent.document).val(radioObj.title);
        $("input[name='publisher']",window.parent.parent.document).val(radioObj.source);
        $("input[name='tags']",window.parent.parent.document).val(radioObj.keyword);
        
        
        oFakeImage	= oEditor.FCKDocumentProcessor_CreateFakeImage( 'FCK__UnknownObject', oEmbed ) ;
        oFakeImage.setAttribute( 'style', 'width: 630px; height: 534px', 0 ) ;
        oFakeImage.setAttribute( '_moz_resizing', 'true', 0 ) ;
    	oFakeImage	= FCK.InsertElement( oFakeImage ) ;
        oEditor.FCKEmbedAndObjectProcessor.RefreshView(oFakeImage,oEmbed);
    	return true ;
    }
}
