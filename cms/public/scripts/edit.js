//检测移除重复链接的jquery文件 让其他js重新执行
(function(){
	var hdr=document.getElementsByTagName("head")[0];
	var scripts=document.getElementsByTagName("script");
	var scrlen=scripts.length;
	var jqlen=0;
	var jqlib=[];
	var otherjs=[];
    
	for(i=0; i<scrlen; i++){
		var src=scripts[i].src;
		if(src.match("jquery")){
			jqlib.push(scripts[i]);
			jqlen++;
			}else if(!src.match("edit") && src.length>3){
				otherjs.push(scripts[i]);
				}
		//alert(src);
		}
	if(jqlen>1){
		for(i=1;i<jqlen;i++){			
			hdr.removeChild(jqlib[i]);
			}
		}
	
	if(otherjs.length>=1){
		for(i=0; i<otherjs.length; i++){
			hdr.removeChild(otherjs[i]);
			}		
		for(i=0; i<otherjs.length; i++){
			var nsc=document.createElement("script");
			nsc.src=otherjs[i].src;
			hdr.appendChild(nsc);
			}
		}	
		
})()

// object types
var OBJ_TYPE_TEMPLATE = 0;
var OBJ_TYPE_ARTICLE = 1;
var OBJ_TYPE_FOCUS   = 2;
var OBJ_TYPE_PAGE    = 3;

// object OWNEDs
var OBJ_OWNED_CHANNEL = 0;
var OBJ_OWNED_TEMPLATE = 1;
var OBJ_OWNED_PAGE    = 2;

jQuery(document).ready(function () {
    cmsInitModules();
});

function cmsInitModules()
{
    var anchors = jQuery('.module_anchor');
    for (var i = 0; i < anchors.length; i ++) {
        var oid = anchors[i].id.match(/cms_module_(\d+)/)[1];
        var menu = new FlyoutMenu('menu_' + anchors[i].id, cms_modules[oid].name, anchors[i]);
        
        menu.addOption('本页面', cms_modules[oid][OBJ_OWNED_PAGE], cmsEditModule, {oid: oid, owned: OBJ_OWNED_PAGE});
        menu.addOption('本模板', cms_modules[oid][OBJ_OWNED_TEMPLATE], cmsEditModule, {oid: oid, owned: OBJ_OWNED_TEMPLATE});
        menu.addOption('本频道', cms_modules[oid][OBJ_OWNED_CHANNEL], cmsEditModule, {oid: oid, owned: OBJ_OWNED_CHANNEL});
        
        for (var j = 0; j < EXTRA_MENUS.length; j ++) {
            var m = EXTRA_MENUS[j];
            if (m.target == oid) {
                menu.addOption(m.text, false, m.handle, m.params);
            }
        }
    }
}

function cmsEditModule(params)
{
    var data = {
        oid : params.oid,
        pageid: cms_pageid,
        type: cms_type,
        owned: params.owned
    };
    
    var params = [];
    for (var key in data) {
        params.push(key + '=' + data[key]); 
    }

    var url = 'http://cms.v1h5.com/publish/modules/form/?' + params.join('&');

    showEditWindow(url);
}

function cmsEditManualModules(params)
{
    var oid = params.oid;
    var otype = params.otype;

    var prefix = 'p';
    if (cms_type == OBJ_TYPE_ARTICLE) {
        prefix = 'a';
    } else if (cms_type == OBJ_TYPE_FOCUS) {
        prefix = 'f';
    }

//    if (otype == 'manual') {
//        var url = '/contentarea/index/show/oid/' + oid + '/pid/' + prefix + cms_pageid;
//    } else {
//        var url = '/customlist/index/show/oid/' + oid + '/pid/' + prefix + cms_pageid;
//    }
    if (otype == 'manual') {
        var url = 'http://cms.v1h5.com/contentarea/index/show/oid/' + oid + '/pid/' + params.pid;
    } else {
        var url = 'http://cms.v1h5.com/customlist/index/show/oid/' + oid + '/pid/' + params.pid + '/cid/' + params.cid;
    }

    showEditWindow(url);
}

function cmsEditFlashpptModules(params)
{
    var oid = params.oid;
	var url = 'http://cms.v1h5.com/flashppt/index/index/oid/' + params.oid + '/pid/' + params.pid;
    showEditWindow(url);
}

function showEditWindow(url)
{
    var win_features = "width=800,height=400,menubar=no,location=no,resizable=yes,scrollbars=yes,status=yes";
	var win_name= 'module_edit'+ new Date().getTime();
    window.open(url, win_name, win_features);
}

/* flyout menu */
FlyoutMenu = function (id, name, position)
{
    var self = this;
    
    this.handle = null;
    this.menu = null;
    this.id = '';
    this.name = '编辑选项';

    this.init = function ()
    {
        var handle = document.createElement('div');
        handle.className = 'flyout_menu_handle';
	handle.title = name;
        handle.onclick = function () {
            self.show();
        };

        jQuery(position).after(handle);

        var menu = document.createElement('div');
        menu.id = id;
        menu.className = 'flyout_menu';
        menu.style.zIndex = FlyoutMenu.zIndex --;
        //jQuery(position).after(menu); 
        jQuery('body').append(menu);
        
        this.handle = jQuery(handle);
        this.id = id;
        this.name = name;
        this.menu = jQuery(menu);

        var html = '<div class="flyout_menu_header"><div class="flyout_menu_icon"></div><div class="flyout_menu_title">' + this.name + '</div><div class="flyout_menu_clearfix"></div></div><div class="flyout_menu_options"><ul></ul></div>';
        this.menu.html(html);
        
        var pos = this.handle.position();
		//屏蔽掉这个判断 2009年9月8日
       // if (FlyoutMenu.menus.length) {
           // if (pos.top - FlyoutMenu.lastPosition.top < 20) {
                //this.handle.css('top', FlyoutMenu.lastPosition.top + 22);
				
            //}
       // }
        
        FlyoutMenu.lastPosition = this.handle.position();
        //this.setOptions(options);
    }
    
    this.addOption = function (text, highlight, handle, params)
    {
        var ul = jQuery('#' + self.id + ' .flyout_menu_options ul');

        var li = document.createElement('li');
        var option = document.createElement('a');
        option.href='javascript:void(0);';
        option.innerHTML = text;
        option.onclick = function () {
            handle(params);
        }

        if (highlight) {
            option.className = 'flyout_menu_option_highlight';
        }

        li.appendChild(option); 
        ul.append(li);
    }

    this.show = function ()
    {
        var offset = self.handle.offset();

        self.menu.show();
        self.menu.css('left', offset.left - 3);
        self.menu.css('top', offset.top - 3);

        FlyoutMenu.hideAll(self);
    }
    
    this.hide = function ()
    {
        self.menu.hide();
    }

    this.init();
    FlyoutMenu.menus.push(this);
}

FlyoutMenu.zIndex = 99999;
FlyoutMenu.lastPosition = {top: 0, left: 0};
FlyoutMenu.menus = [];
FlyoutMenu.hideAll = function (except)
{
    jQuery.map(FlyoutMenu.menus, function (m) {
        if (typeof except == 'undefined' || except != m) {
            m.hide();
        }
    });
}

FlyoutMenu.init = function ()
{
    jQuery(document).bind('click', function (e) {
        e = e || window.event;

        var target   = e.target || e.srcElement;

        if (!target.className.match(/^flyout_menu_/)) {
            FlyoutMenu.hideAll();
        }
    });
}

FlyoutMenu.init();

