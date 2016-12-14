/* FCK编辑器字数提示*/ 

var b = navigator.userAgent.toLowerCase();
var browser = {
	version: b.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)[1],
	safari: /webkit/.test(b),
	opera: /opera/.test(b),
	msie: /msie/.test(b) && !/opera/.test(b),
	mozilla: /mozilla/.test(b) && !/(compatible|webkit)/.test(b)
};

if(window.HTMLElement) {
    HTMLElement.prototype.__defineSetter__("innerText",function(sText) {
        this.innerHTML='';
        this.appendChild(document.createTextNode(sText));
        return sText;
    });
    HTMLElement.prototype.__defineGetter__("innerText",function() {
        var r=this.ownerDocument.createRange();
        r.selectNodeContents(this);
        return r.toString();
    });
}

var $$=function(id){return document.getElementById(id)};
function getContentLength()
{
	var fckObj = FCKeditorAPI.GetInstance('contents') ;
	if (fckObj.EditMode == FCK_EDITMODE_WYSIWYG ){ 
		//IE
		if(browser.msie==true){	
			var span=document.createElement( 'span' );	
			span.innerHTML=fckObj.EditorDocument.body.innerHTML;

			var obj=document.createElement('textarea' );
			obj.appendChild(span);

			var len=obj.value.length;
			if(obj.value==' ')len=0;
		}else{
			var span=document.createElement( 'span' );		
			span.innerHTML=fckObj.EditorDocument.body.innerHTML;	
			var len=span.innerText.length;
		}
	}
	else
	{
		var len= -1;
	}
	return len;
}
function getFckContent(flag){
	var flag=flag?flag:0;
	var max=600;
	var fckObj = FCKeditorAPI.GetInstance('contents') ;
	if (fckObj.EditMode == FCK_EDITMODE_WYSIWYG ){ 
		//IE
		if(browser.msie==true){	
			var span=document.createElement( 'span' );	
			span.innerHTML=fckObj.EditorDocument.body.innerHTML;

			var obj=document.createElement('textarea' );
			obj.appendChild(span);

			var len=obj.value.length;
			if(obj.value==' ')len=0;
		}else{
			var span=document.createElement( 'span' );		
			span.innerHTML=fckObj.EditorDocument.body.innerHTML;	
			var len=span.innerText.length;
		}

		if(!flag){		
			if(!len){
				alert('无输入');
			}else{
				alert("当前已输入:"+len+"\n");
			}
		}
	}
}

/*
function FCKeditor_OnComplete(editorInstance){  
	if(browser.msie!=true){	
		editorInstance.EditorDocument.body.addEventListener("keyup",getFckContent,false);
	}else{
		editorInstance.EditorDocument.body.attachEvent("onkeyup",getFckContent);		

	}
}
*/