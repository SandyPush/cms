/* 手动区管理相关函数(yxw) */

var $$=function(id){return document.getElementById(id);};

var checkadd=function (){
	var caption=$$('i_caption').value;
	var name=$$('i_name').value;
	var max_length=$$('i_max_length').value;
	var type=$$('i_type').value;
	var re=/^[a-zA-Z][a-zA-Z_0-9]*$/;		
	var re1=/^[0-9]*$/;

	if(!caption){
		alert('属性名不能为空');
		$$('i_caption').focus;
		return false;
	}

	if(!name){
		alert('ID不能为空');
		$$('i_name').focus;
		return false;
	}else{
		var re=/^[a-zA-Z][a-zA-Z_0-9]*$/;		
		if(!(re.exec(name))){
			alert('ID必须为英文,数字,或下划线组成,\n\n 并且以只能以英文字母开头!');
			$$('i_name').focus;
			return false;
		}
	}
	var pid=$('#i_pid').val();
	if(pid && !checkPid(pid)){
		alert('Pid必须为英文字母p,f或者a开头加数字组成!');	
		window.location.href=window.location.href;
		return false;
	}

	/*
	if(type==2 && $$('i_pic').value==''){
		alert('请选择一张图片!');
		return false;
	}
	*/

	if((!re1.exec(max_length) || type==2))$$('i_max_length').value='';	
	
	if($$('i_area').style.display!='none'){
		$$('ahtml').value=$$('i_area').value;
	}else if($$('i_put').style.display!='none'){
		$$('ahtml').value=$$('i_put').value;
	}else if($$('i_pic').style.display!='none'){
		$$('ahtml').value=$$('i_pic').value;
	}
};

var select_type=function (id){
	switch(id){
		case '1':
			$('#i_put')[0].style.display='none';
			$('#i_area')[0].style.display='';
			$('#i_apic')[0].style.display='none';
			$('#i_max_l')[0].style.display='';break;
		case '2':
			$('#i_put')[0].style.display='none';
			$('#i_area')[0].style.display='none';
			$('#i_apic')[0].style.display='';
			$('#i_max_l')[0].style.display='none';break;
		default:
			$('#i_put')[0].style.display='';
			$('#i_area')[0].style.display='none';
			$('#i_apic')[0].style.display='none';
			$('#i_max_l')[0].style.display='';
	}
};

var dialogFirst=true;
var dialog=function(title,content,width,height,cssName){
	if(dialogFirst==true){
		var temp_float=new String;
		temp_float="<div id=\"floatBoxBg\" style=\"height:"+$(document).height()+"px;filter:alpha(opacity=0);opacity:0;\"></div>";
		temp_float+="<div id=\"floatBox\" class=\"floatBox\">";
		temp_float+="<div class=\"title\"><h4></h4><span>关闭</span></div>";
		temp_float+="<div class=\"content\"></div>";
		temp_float+="</div>";
		$("body").append(temp_float);
		dialogFirst=false;
	}

	$("#floatBox .title span").click(function(){
		$("#floatBoxBg").animate({opacity:"0"},"normal",function(){$(this).hide();});
		$("#floatBox").animate({top:($(document).scrollTop()-(height=="auto"?300:parseInt(height)))+"px"},"normal",function(){$(this).hide();});
	});

	$("#floatBox .title h4").html(title);
	contentType=content.substring(0,content.indexOf(":"));
	content=content.substring(content.indexOf(":")+1,content.length);
	switch(contentType){
		case "url":
			var content_array=content.split("?");
			$("#floatBox .content").ajaxStart(function(){
				$(this).html("loading...");
			});
			$.ajax({
				type:content_array[0],
				url:content_array[1],
				data:content_array[2],
				error:function(){
					$("#floatBox .content").html("error...");
				},
				success:function(html){
					$("#floatBox .content").html(html);
				}
			});
		break;
		case "text":
		$("#floatBox .content").html(content);
		break;
		case "id":
		 $("#floatBox .content").html($("#"+content+"").html());
		break;
		case "iframe":
		$("#floatBox .content").html("<iframe src=\""+content+"\" width=\"100%\" height=\""+"100%"+"\" scrolling=\"auto\" frameborder=\"0\" marginheight=\"0\" marginwidth=\"0\"></iframe>");
	}

	$("#floatBoxBg").show();
	$("#floatBoxBg").animate({opacity:"0.3"},"normal");
	$("#floatBox").attr("class","floatBox "+cssName);
	$("#floatBox").css({display:"block",left:(($(document).width())/2-(parseInt(width)/2))+"px",top:($(document).scrollTop()-(height=="auto"?300:parseInt(height)))+"px",width:width,height:height});
	$("#floatBox").animate({top:($(document).scrollTop()+150)+"px"},"normal");
}

$(function(){$("#checkAll").click(function(){  
		if ($(this).attr("checked") == true){ 
			$("input[@name='check[]']").each(function() {$(this).attr("checked", true);});  
		}else{
			$("input[@name='check[]']").each(function() {$(this).attr("checked", false);}); 
		} 
	});
});

var deleteOne=function(id){
	if(!id)return;
	if(!confirm("想一想真的要删除吗?"))	return false;   
    $.ajax({
	   type: "GET",
	   url: "/contentarea/index/delete/aid/"+id,	
	   success: function(data){
		   alert(data);
		   window.location.href=window.location.href;
	   }
	});
};

var showUpdateOne=function(id){
	var data = {
		oid:$('#oid'+id).val(),
		check:$('#check'+id).val(),
		mode:$('#mode'+id).val(),
		pid:$('#pid'+id).val(),
		channel:$('#channel').val(),
		value:$('#value'+id).val()
		};			
		
	$.ajax({
		type: "POST",
		url: "/contentarea/index/updateall/mode/ajax",
		data: data,	 
		success: function(data){ 
			alert(data);
			window.location.href=window.location.href;
		}
	});
};

var updateOne=function(id){
	var caption=$('#caption'+id).val();
	var name=$('#name'+id).val();
	if(!caption){
		alert('属性名不能为空');
		window.location.href=window.location.href;
		return false;
	}
	if(!name){
		alert('属性ID不能为空');		
		window.location.href=window.location.href;
		return false;
	}else{
		var re=/^[a-zA-Z][a-zA-Z_0-9]*$/;		
		if(!(re.exec(name))){
			alert('属性ID必须为英文,数字,或下划线组成,\n\n 并且以只能以英文字母开头!');			
			window.location.href=window.location.href;
			return false;
		}
	}
	var pid=$('#pid'+id).val();
	if(pid && !checkPid(pid)){
		alert('Pid必须为英文字母p,f或者a开头加数字组成!');	
		window.location.href=window.location.href;
		return false;
	}
	var data = {
		oid:$('#oid'+id).val(),
		pid:$('#pid'+id).val(),
		name:$('#name'+id).val(),
		caption:$('#caption'+id).val(),
		info:$('#info'+id).val(),
		type:$('#type'+id).val(),
		max_length:$('#max_length'+id).val()?$('#max_length'+id).val():'',
		value:$('#value'+id).val()
		};			
			
	$.ajax({
		type: "POST",
		url: "/contentarea/index/update/aid/"+id,
		data: data,	 
		success: function(data){ 
			alert(data);
			window.location.href=window.location.href;
		}
	});
};

var testLength=function(id){ 
	if($$('len'+id) && $$('max_length'+id)){
		var max=$$('max_length'+id).value>0?$$('max_length'+id).value:'无';
		var len=$$('value'+id).value.length;
		$$('len'+id).innerHTML='建议长度:'+max+',已输入:'+len;
	
		if(len>max){
			$$('len'+id).style.color="red";
		}else{
			$$('len'+id).style.color="#000000";
		}
	}
	$$('check'+id).checked=true;
} 

var handle=function(id){
	return function(){
		testLength(id);
	}
};
/*
var deleteAll=function(){
　　if(!confirm('想一想真的要删除吗?'))return;　　
　　if($("input[@name='check[]']").length>0){
		var ids=[];
　　　　$("input[@name='check[]']").each(function(){
			if($(this).attr("checked")==true){
				ids.push($(this).val());
			}
		}); 
	
		var data = {
			ids:ids.join(',')
		};
		$.ajax({
		   type: "POST",
		   url: "/contentarea/index/deleteall/",	
		   data: data,	
		   success: function(data){
			   alert(data);
			   window.location.href=window.location.href;
		   }
		});
　　}
};
*/
var checkPid=function (pid) {
	var re=/^[apf][0-9]+$/;		
	if(!(re.exec(pid)) && pid){	
		return false;
	}
	return true;
}

$(document).ready(function () {
	//$("#formBox table tr").not($(".tool")).hover(function(){$(this).addClass("over");},function(){$(this).removeClass("over");}); 
	if($.browser.msie!=true){ 
		$("input[name='value[]']").each(function(){
				var id=$(this).attr("id").replace(/value/,''); 				
				$(this)[0].addEventListener("input",handle(id),false);
			});  
		$("textarea[name='value[]']").each(function(){
				var id=$(this).attr("id").replace(/value/,''); 				
				$(this)[0].addEventListener("input",handle(id),false);
		});	
	}
});