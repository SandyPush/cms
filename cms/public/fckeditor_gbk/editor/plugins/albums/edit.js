var $$= function(id){return document.getElementById(id)};

//增加图片
var addattachfrom=function () {
	$(".cell:first").clone(true).insertAfter($(".cell:last"));
	$(".cell:last").find(":input,textarea,span").each(function(){	
			$(this).val('');
			if($(this).attr("id")){
				var num=$(".cell").length;				
				var id=$(this).attr("id").replace(/[0-9]+/,'');					
				$(this).attr("id",id+num);					
			}				
		});  
	$(".cell:last").find("span").each(function(){	
			$(this)[0].innerHTML='';					
		});
	$(".cell:last").find("img").each(function(){	
			$(this)[0].src='/images/nopic.jpg';	
			if($(this).attr("id")){
				var num=$(".cell").length;				
				var id=$(this).attr("id").replace(/[0-9]+/,'');					
				$(this).attr("id",id+num);					
			}
		});
	listenEvent();
};

//swfupload增加图片
var addswffrom= function (alt, image) {
	$(".cell:first").clone(true).insertAfter($(".cell:last"));	
	$(".cell:last").show();
	$("#submit").show();	
	$(".cell:last").find(":input[name='upfile[]']").hide();
	$(".cell:last").find(":input,textarea,span").each(function(){	
			$(this).val('');
			if($(this).attr("id")){
				var num= $(".cell").length;				
				var id= $(this).attr("id").replace(/[0-9]+/,'');					
				$(this).attr("id",id+num);					
			}				
		});  
	$(".cell:last").find("span").each(function(){	
			$(this)[0].innerHTML='';					
		});
	$(".cell:last").find("textarea[name^='alt']").val(alt);
	$(".cell:last").find(".hide").val(image);
	$(".cell:last").find(".del").mouseover(function(){$(this).parent().parent().parent().css("background", '#ddddde' )}).mouseout(function(){$(this).parent().parent().parent().css("background", '#EBEBEB' )});
	$(".cell:last").find("img:first").each(function(){	
			$(this)[0].src= image;	
			$(this).parent().attr("href", image);
			if($(this).attr("id")){
				var num=$(".cell").length;				
				var id=$(this).attr("id").replace(/[0-9]+/,'');					
				$(this).attr("id",id+num);					
			}
		});
	listenEvent();
};

//减少图片
var removeattachfrom= function () {
	if($(".cell").length==1)return;
	$(".cell:last").remove(); 
};

var testLength= function(id,name,max){ 	
		var max= max!=''?max:'无';
		var len= $$(name+id).value.length;
		$$('len'+name+id).innerHTML='建议长度:'+max+',已输入:'+len;	
		if(len> max){
			$$('len'+name+id).style.color="red";
		}else{
			$$('len'+name+id).style.color="#000000";
		}
}; 

var previewImage= function(obj,type){
	if(!obj.value)return;
	
	switch(type){
		case 1: {
			var num= $(obj).attr("id").replace(/upfile/,'');	
			$('#preview'+num)[0].src= obj.value;	
			break;
		}
		case 2:	{
			var num= $(obj).attr("id").replace(/fileurl/,'');	
			$('#preview'+num)[0].src= obj.value;  	
			break;
		}
		case 3: {
			var id= obj.value;					
			var dir=(Math.floor(id/1000))*1000;	
			var num= $(obj).attr("id").replace(/titanpic/,'');					
			$('#preview'+num)[0].src= 'http://img2008.titan24.com/imgwater/'+dir+'/'+id+'.jpg'; 
			break;	
		}
					
	}
};

var handle= function(id,name,max){return function(){testLength(id,name,max);}};

var listenEvent= function(){
	if($.browser.msie!= true){ 		
		$(".alt").each(function(){			
				var id=$(this).attr("id").replace(/alt/,''); 				
				$(this)[0].addEventListener("input",handle(id,'alt',40),false);
			});  
		$(".info").each(function(){
				var id=$(this).attr("id").replace(/info/,''); 				
				$(this)[0].addEventListener("input",handle(id,'info',500),false);
		});	
	}else{		
		$(".alt").each(function(){				
				var id=$(this).attr("id").replace(/alt/,''); 				
				$(this)[0].attachEvent("onpropertychange",handle(id,'alt',40));
			});  
		$(".info").each(function(){
				var id=$(this).attr("id").replace(/info/,''); 				
				$(this)[0].attachEvent("onpropertychange",handle(id,'info',500));
		});	
	}
};

$(document).ready(function () { 
	listenEvent(); 
});