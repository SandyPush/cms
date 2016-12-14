var others_height = 132;

function initCmsFunctions()
{
    for (var i = 0; i < cms_functions.func.length; i ++) {		
        var html = '<option value="' + cms_functions.func[i].name + '">' + cms_functions.func[i].label + '</option>';
        $('#sel_funcs').append(html);
    }

    $('#sel_funcs').bind('change', function () {
        var index = $(this).attr('selectedIndex');
        if (index == 0) {
            $('#func_params').html('');
            return true;
        }
        
        var func = cms_functions.func[index - 1];
		if(!func.param.length){
			var params= new Array();
			params.push(func.param);
		}else{		
			var params = func.param;
		}
        var html = '';	
		
        for (var i = 0; i < params.length; i ++) {
            var param = params[i];
            html += param.label + ': ';
           
            if (typeof param.type == 'undefined') {
                param.input == 'string';
            }

            if (typeof param.input == 'undefined') {
                param.input == 'text';
            }
            
            var default_value = typeof(param['default']) !== 'string' ? '' : param['default'];

            if (param.input == 'text') {
                var size = param.type == 'int' ? 2 : 10;
                html += '<input type="text" id="func_param_' + param.name + '" value="' + default_value + '" size="' + size + '"/>';
            } else if (param.input == 'select') {
                html += '<select id="func_param_' + param.name + '">';
                for (var j = 0; j < param.options.option.length; j ++) {
                    var op = param.options.option[j];
                    html += '<option value="' + op.value + '">' + op.label + '</option>';
                }
                html += '</select>';
            }

            html += ' &nbsp;';
        }

        html += '<input type="button" value=" 插入 " onclick="insertCmsFunction(' + (index - 1) + ')"/>';        
        $('#func_params').html(html);
    });
}

function insertCmsFunction(index)
{
    var func = cms_functions.func[index];
    var args = ["'" + func.name + "'"];
	if(!func.param.length){
		var params= new Array();
		params.push(func.param);
	}else{		
		var params = func.param;
	}
    //var params = func.param;
    
    for (var i = 0; i < params.length; i ++) {
        var param = params[i];
        var value = $('#func_param_' + param.name).val();
        value = param.type == 'string' ? value.replace("'", '\\\'') : value;
        value = param.type == 'string' ? "'"  + value + "'" : value;
        args.push(value);
    };

    var code = 'CMS_FUNCTION(' + args.join(', ') + ')';

    //$('#txt_content').val($('#txt_content').val() + code);
    insertText(document.getElementById('txt_content'), code);
}

function insertText(obj, text)
{
    obj.focus();

    if(document.selection) {
        if (document.selection.type == "Text") {
            var range = document.selection.createRange();

            range.text = text;
            range.moveStart("character", -text.length);
            range.select();
        } else {
            obj.document.selection.createRange().text += text;
        }
    } else if(window.getSelection && obj.selectionStart > -1) {
        var start = obj.selectionStart; 
        var end   = obj.selectionEnd;
        
        var value  = obj.value;
        
        obj.value = value.substring(0, start) + text +  value.slice(end);

        if (start != end) {
            obj.selectionStart = start;
            obj.selectionEnd = start + text.length;
        } else {
            obj.selectionStart = obj.selectionEnd = start + text.length;
        }
    } else { 
        obj.value += text;
    }

    obj.focus();
}

function adjustTxtArea()
{
    $('#txt_content').css('height', $(window).height() - others_height);
    $('#txt_content').css('width', $(window).width() - 26);
}

$(document).ready(function () {
    initCmsFunctions();
    window.focus();
    adjustTxtArea();	
});

$(window).bind('resize', function () {
    adjustTxtArea();
});


/* Added by yxw 09.03.30 */
if(window.HTMLElement) {
	HTMLElement.prototype.__defineSetter__("outerHTML",function(sHTML) {
        var r=this.ownerDocument.createRange();
        r.setStartBefore(this);
        var df=r.createContextualFragment(sHTML);
        this.parentNode.replaceChild(df,this);
        return sHTML;
    });
    HTMLElement.prototype.__defineGetter__("outerHTML",function() {
        var attr;
        var attrs=this.attributes;
        var str="<"+this.tagName;
        for(var i=0;i<attrs.length;i++) {
            attr=attrs[i];
            if(attr.specified)
                str+=" "+attr.name+'="'+attr.value+'"';
        }
        if(!this.canHaveChildren)
            return str+">";
        return str+">"+this.innerHTML+"</"+this.tagName+">";
    });
}

var D=function(A,B){A[A.length]=B;};
var cmsModuleRevision = {
	oid : window.location.href.match(/\/?oid=(\d+)&/)[1],	
		
	init:function(){
		var self=this;
		$.ajax({
			type: "GET",
			url: window.location.href.replace('form','revise'),	
			error:function(){
				return;
			},
			success: function(res){					
				obj = res?eval('(' + res + ')'):'';								
				var html=[];
				D(html,"<select id=\"sel_revision\" style=\"width: 100px\" onchange=\"cmsModuleRevision.get(this.value);\">");
				D(html,"<option value=\"\">历史记录</option>");
				if(obj)for(k in obj) D(html, "<option value=\""+obj[k].uid+'|'+obj[k].updatetime+"\">"+obj[k].username+'|'+obj[k].updatetime+"</option>");	
				D(html,"</select>");
				html= html.join('');			
				//$('#sel_revision')[0].innerHTML= html;
				$('#sel_revision')[0].outerHTML= html;				
			}
		});		
	},

	get:function(v){
		if(!v)return;	
		var self=this;
		$.ajax({
			type: "GET",
			url: '/publish/modules/revise/?oid='+ self.oid+ '&param='+v,			 
			error:function(){
				alert("error!");
			},
			success: function(code){ 
				if(!confirm('真的要用历史纪录替换当前正在编辑的内容吗?'))return;
				if(code)$('#txt_content').val(code);
			}
		});
	}
};

function upload(obj)
{
	//var dialog_url="/contents/article/upload/type/0/name/"+encodeURI('文章')+"/cid/"+$("#cid").val();
	var dialog_url="/resource/index/index/";
    var imgurl = window.showModalDialog(dialog_url, "", "dialogHeight:250px; dialogWidth:400px; resizable:0; scroll:0; status:0; unadorned:0;");
    if(imgurl == undefined) return false;
    $(obj).prev().val(imgurl);
    $('#img_preview').html('<img src="'+imgurl+'"/>');
}
