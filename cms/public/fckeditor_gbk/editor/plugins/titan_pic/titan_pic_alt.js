/* ����޸�����ʱ����ժҪ�Լ��༭��ͼע��������ʾ*/

var b = navigator.userAgent.toLowerCase();
var browser = {
	version: b.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)[1],
	safari: /webkit/.test(b),
	opera: /opera/.test(b),
	msie: /msie/.test(b) && !/opera/.test(b),
	mozilla: /mozilla/.test(b) && !/(compatible|webkit)/.test(b)
};

var $$=function(id){return document.getElementById(id)};

var testLength=function(){ 
	if(!$$('txtAlt'))return;
	var span=document.createElement( 'span' );	
	$$('txtAlt').parentNode.insertBefore(span,$$('txtAlt'));
	span.setAttribute("id" , 'txtAltLength');	

	if($$('txtAltLength')){
		var max=40;
		var len=$$('txtAlt').value.length;
		$$('txtAltLength').innerHTML='���鳤��:'+max+',������:'+len+'<br />';
		
		if(len>max){
			$$('txtAltLength').style.color="red";
		}else{
			$$('txtAltLength').style.color="#000000";
		}
	}
} 

function listenEvent(){
	if(!$$('txtAlt'))return;
	if(browser.msie!=true){ 		
		$$('txtAlt').addEventListener("input",testLength,false);	
	}else{
		$$('txtAlt').attachEvent('onpropertychange', testLength);
	}
}
listenEvent();
