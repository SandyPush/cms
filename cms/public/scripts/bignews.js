var dObj = new  Date();
var startTime = dObj.getTime();

/* 获取所有的头条新闻列表 */
function getBigNews() {
    var bignews = [];
    // 遍历每个列表
    for (var i = 0; document.getElementById('bignews_' + i); i++) {
        bignews[i] = {
            'tag'  : document.getElementById('bignews[' + i + '][tag]').value
        }
        bignews[i]['items'] = [];
        // 遍历每个列表项
        for (var j = 0; document.getElementById('bignews_' + i + '_' + j); j++) {
            bignews[i]['items'][j] = {
                'tag'  : document.getElementById('bignews[' + i + '][items][' + j + '][tag]').value
            }
            bignews[i]['items'][j]['items'] = [];			
            // 遍历每个链接项
			try{
				for (var k = 0; document.getElementById('bignews_' + i + '_' + j + '_' + k); k++) {	
					//if(document.getElementById('bignews_' + i + '_' + j + '_' + k)){
						bignews[i]['items'][j]['items'][k] = {
							'title' : document.getElementById('bignews[' + i + '][items][' + j + '][items][' + k + '][title]').value,
							'href'  : document.getElementById('bignews[' + i + '][items][' + j + '][items][' + k + '][href]').value,
							'class' : document.getElementById('bignews[' + i + '][items][' + j + '][items][' + k + '][class]').value
						}
					//}
				}
			}catch(e){};
        }
    }
    return bignews;
}

/* 构建链接项
   i 表示第几个列表
   j 表示列表中的第几个列表项
   k 表示列表项中的第几个链接
   anchoritem 表示链接项的数据，其结构为：
   {
     'title' : title_value,    // 标题名称
     'href'  : href_value,     // 链接地址
     'class' : class_value,    // 链接的 css 类名
   }
*/
function buildAnchorItem(i, j, k, anchoritem) {
    var result = document.createElement('a');
    result.href = anchoritem['href'];
    result.title = anchoritem['title'];
    result.target = '_blank';
    result.id = 'bignews_' + i + '_' + j + '_' + k;
    var key, input;
    for (key in anchoritem) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'bignews[' + i + '][items][' + j + '][items][' + k + '][' + key + ']';
        input.id = 'bignews[' + i + '][items][' + j + '][items][' + k + '][' + key + ']';
        input.value = anchoritem[key];
        result.appendChild(input);
    }
    result.appendChild(document.createTextNode(anchoritem['title'] + ' '));
    return result;
}

/* 构建列表项
   i 表示第几个列表
   j 表示列表中的第几个列表项
   bignewslistitem 表示列表项的数据，其结构为：
   {
     'tag'  : tag_value,      // 列表项标签名，取值为 dt，dd 和 li
     'items': anchoritems     // anchoritem 结构的数组
   }
*/
function buildBigNewsListItem(i, j, bignewslistitem) {	
    var result = document.createElement(bignewslistitem['tag']);	
    switch (bignewslistitem['tag']) {
        case 'dt': result.style.color = "#cc0000"; break;
        case 'dd': result.style.color = "#ff6600"; break;
        case 'li': result.style.color = "#006600"; break;
    }
    result.id = 'bignews_' + i + '_' + j;
    var key = 'tag'
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'bignews[' + i + '][items][' + j + '][' + key + ']';
    input.id = 'bignews[' + i + '][items][' + j + '][' + key + ']';
    input.value = bignewslistitem[key];
    result.appendChild(input);
    var btns = [
        {src: '/images/up.gif', name: 'up_item_btn', alt: '向上', onclick: function () { listItemUp(i, j); } },
        {src: '/images/down.gif', name: 'down_item_btn', alt: '向下', onclick: function () { listItemDown(i, j); } },
        {src: '/images/edit.gif', name: 'edit_item_btn', alt: '编辑', onclick: function () { editBigNewsListItem(i, j); } },
        {src: '/images/del.gif', name: 'del_item_btn', alt: '删除', onclick: function () { delBigNewsListItem(i, j); } }
    ];
    var img, btn;
    for (btn in btns) {
        btn = btns[btn];
        img = document.createElement('img');
        img.src = btn.src;
        img.name = btn.name;
        img.alt = btn.alt;
        img.title = btn.alt;
        img.onclick = btn.onclick;
        img.align = "absmiddle";
        img.style.width = "16px";
        img.style.height = "16px";
        img.style.cursor = 'pointer';
        result.appendChild(img);
        result.appendChild(document.createTextNode(' '));
    }
    if (bignewslistitem['items']) {
        for (var k = 0; k < bignewslistitem['items'].length; k++) {
            var a = buildAnchorItem(i, j, k, bignewslistitem['items'][k]);
            result.appendChild(a);
            // 将颜色样式应用于每个链接上
            a.style.color = result.style.color;
        }
    }
    return result;
}

/* 构建列表
   i 表示第几个列表
   bignewslist 表示列表的数据，其结构为：
   {
     'tag'  : tag_value,         // 列表标签名，取值为 dl 和 ul
     'items': bignewslistitems   // bignewslistitem 结构的数组
   }
*/
function buildBigNewsList(i, bignewslist) {
    var result = document.createElement('tr');
    result.className = "line";

    var td = document.createElement('td');
    td.style.width = "180px";
    td.appendChild(document.createTextNode((bignewslist['tag'] == 'dl') ? '即时新闻 ': '新闻列表 '));

    var btns = [
        {src: '/images/up.gif', name: 'up_btn', alt: '向上', onclick: function() { listUp(i); } },
        {src: '/images/down.gif', name: 'down_btn', alt: '向下', onclick: function() { listDown(i); } }
    ].concat(
        ((bignewslist['tag'] == 'dl') ? [
        {src: '/images/add1.gif', name: 'add_bigitem_btn', alt: '添加大列表项', onclick: function() { addBigNewsListItem(i, 'dt'); } },
        {src: '/images/add2.gif', name: 'add_smallitem_btn', alt: '添加小列表项', onclick: function() { addBigNewsListItem(i, 'dd'); } }
    ] : [
        {src: '/images/add.gif', name: 'add_item_btn', alt: '添加列表项', onclick: function() { addBigNewsListItem(i, 'li'); } }
    ])).concat([ {src: '/images/del.gif', name: 'del_btn', alt: '删除列表', onclick: function() { delBigNewsList(i); } } ]);

    var img, btn;
    for (btn in btns) {
        btn = btns[btn];
        img = document.createElement('img');
        img.src = btn.src;
        img.name = btn.name;
        img.alt = btn.alt;
        img.title = btn.alt;
        img.onclick = btn.onclick;
        img.align = "absmiddle";
        img.style.width = "16px";
        img.style.height = "16px";
        img.style.cursor = 'pointer';
        td.appendChild(img);
        td.appendChild(document.createTextNode(' '));
    }

    var bignews_tag = document.createElement('input');
    bignews_tag.type = 'hidden';
    bignews_tag.name = 'bignews[' + i + '][tag]';
    bignews_tag.id = 'bignews[' + i + '][tag]';
    bignews_tag.value = bignewslist['tag'];
    td.appendChild(bignews_tag);

    result.appendChild(td);

    td = document.createElement('td');

    var list = document.createElement(bignewslist['tag']);
    list.id = 'bignews_' + i;
    if (bignewslist['items']) {
        for (var j = 0; j < bignewslist['items'].length; j++) {
            list.appendChild(buildBigNewsListItem(i, j, bignewslist['items'][j]));
        }
    }
    td.appendChild(list);
    result.appendChild(td);

    return result;
}

/* 添加列表
   tag 表示列表类型，其取值为 dl 和 ul
*/
function addBigNewsList(tag) {
    var bignews = getBigNews();
    var bignewslist = {
        'tag': tag,
        'items': []
    }
    var big_news_body = document.getElementById('big_news_body');
    big_news_body.appendChild(buildBigNewsList(bignews.length, bignewslist));
}

/* 将列表 i 上移 */
function listUp(i) {
    if (i <= 0) return;
    var bignews = getBigNews();
    var big_news_body = document.getElementById('big_news_body');
    var lists = big_news_body.getElementsByTagName('tr');
    var a = lists[i];
    var b = lists[i - 1];
    big_news_body.insertBefore(buildBigNewsList(i - 1, bignews[i]), a);
    big_news_body.insertBefore(buildBigNewsList(i, bignews[i - 1]), a);
    big_news_body.removeChild(a);
    big_news_body.removeChild(b);
    cancelEditBigNewsListItem();
}

/* 将列表 i 下移 */
function listDown(i) {
    var bignews = getBigNews();
    if (i >= bignews.length - 1) return;
    var big_news_body = document.getElementById('big_news_body');
    var lists = big_news_body.getElementsByTagName('tr');
    var a = lists[i];
    var b = lists[i + 1];
    big_news_body.insertBefore(buildBigNewsList(i, bignews[i + 1]), a);
    big_news_body.insertBefore(buildBigNewsList(i + 1, bignews[i]), a);
    big_news_body.removeChild(a);
    big_news_body.removeChild(b);
    cancelEditBigNewsListItem();
}

/* 添加列表项
   i 表示列表项所在的列表编号
   tag 表示列表项类型，其取值为 dt，dd 和 li
*/
function addBigNewsListItem(i, tag) {
    var bignews = getBigNews();
    var big_news_list = document.getElementById('bignews_' + i);
    var bignewslistitem = {
        'tag': tag,
        'items': []
    };   
	var j = bignews[i]['items'].length;	
    //big_news_list.appendChild(buildBigNewsListItem(i, bignews[i]['items'].length, bignewslistitem));	
	/*	
	var get_b= function (i, j) {
		alert(j);
		var bignews = [];		
		bignews[j] = {
			'tag'  : document.getElementById('bignews[' + i + '][items][' + j + '][tag]').value
		}
		bignews[j]['items'] = [];
		// 遍历每个链接项
		for (var k = 0; document.getElementById('bignews_' + i + '_' + j + '_' + k); k++) {
			bignews[j]['items'][k] = {
				'title' : document.getElementById('bignews[' + i + '][items][' + j + '][items][' + k + '][title]').value,
				'href'  : document.getElementById('bignews[' + i + '][items][' + j + '][items][' + k + '][href]').value,
				'class' : document.getElementById('bignews[' + i + '][items][' + j + '][items][' + k + '][class]').value
			}
		};		
		return bignews[j];
	};
	*/

	//base on jquery
	if(bignewslistitem.tag!='li'){
		big_news_list.appendChild(buildBigNewsListItem(i, j, bignewslistitem));
		editBigNewsListItem(i, j);
		addAnchorItem();
	}else{
		jQuery.fn.extend({outerHTML:function(){
			return $("<div></div>").append(this.clone()).html();
			}
		});	
		$('#bignews_' + i).prepend($(buildBigNewsListItem(i, j, bignewslistitem)).outerHTML());	
		resetBigNews(i, tag);
		editBigNewsListItem(i, 0);  
		addAnchorItem();
	}
}

function resetBigNews(i, tag){
	var re= tag=='li'? 'li': 'dt,dd';
	$('#bignews_' + i).find(re).each(function(o){	
		$(this).find("IMG").attr("onclick", "");		
		$(this).find("IMG[alt='向上']").bind("click", function(){			
			listItemUp(i, o);
		}); 
		$(this).find("IMG[alt='向下']").bind("click", function(){ 
			listItemDown(i, o);
		}); 
		$(this).find("IMG[alt='编辑']").bind("click", function(){ 
			editBigNewsListItem(i, o);
		}); 	
		$(this).find("IMG[alt='删除']").bind("click", function(){			
			delBigNewsListItem(i, o);
		});	
		$(this).attr("id", 'bignews_'+ i+ '_'+ o); 
		$(this).find("a").each(function(){		 
			$(this).attr("id", $(this).attr("id").replace(/(bignews_[0-9]*_)([0-9]+_)(.*)/i, "$1"+o+"_$3")); 						
		}); 
		$(this).find("input:hidden").each(function(){		 
			$(this).attr("id", $(this).attr("id").replace(/(bignews\[[0-9]*\])(\[items\])(\[[0-9]+\])(.*)/i, "$1$2["+o+"]$4")); 
			$(this).attr("name", $(this).attr("name").replace(/(bignews\[[0-9]*\])(\[items\])(\[[0-9]+\])(.*)/i, "$1$2["+o+"]$4")); 			
		});	
	});	
	try{
		listItemDown(i, 0);
		listItemUp(i, 1);
	}catch(e){};
}

/* 清除子元素项
   parent 表示父元素
   items 是 parent 的子元素列表数组
*/
function clearItems(parent, items) {
    // 只能逆序删除！！！
    for (var i = items.length - 1; i >= 0; i--) {
        parent.removeChild(items[i]);
    }
}

/* 清除 tbody 中的所有行 */
function clearTbody(tbody) {
    clearItems(tbody, tbody.getElementsByTagName('tr'));
}

/* 删除列表 i */
function delBigNewsList(i) {
    var bignews = getBigNews();
    if (i < 0 || i >= bignews.length) return;
    var big_news_body = document.getElementById('big_news_body');
    clearTbody(big_news_body);
    cancelEditBigNewsListItem();
    if (bignews.length == 1) return;
    bignews = bignews.slice(0, i).concat(bignews.slice(i + 1));
    for (var i = 0; i < bignews.length; i++) {
        big_news_body.appendChild(buildBigNewsList(i, bignews[i]));
    }
}

/* 上移列表 i 的列表项 j */
function listItemUp(i, j) {
    if (j <= 0) return;
    var bignews = getBigNews();
    var bignews_list = document.getElementById('bignews_' + i);
    var a = document.getElementById('bignews_' + i + '_' + j);
    var b = document.getElementById('bignews_' + i + '_' + (j - 1));
    bignews_list.insertBefore(buildBigNewsListItem(i, j - 1, bignews[i]['items'][j]), a);
    bignews_list.insertBefore(buildBigNewsListItem(i, j, bignews[i]['items'][j - 1]), a);
    bignews_list.removeChild(a);
    bignews_list.removeChild(b);
    cancelEditBigNewsListItem();
}

/* 下移列表 i 的列表项 j */
function listItemDown(i, j) {
    var bignews = getBigNews();
    if (j >= bignews[i]['items'].length - 1) return;
    var bignews_list = document.getElementById('bignews_' + i);
    var a = document.getElementById('bignews_' + i + '_' + j);
    var b = document.getElementById('bignews_' + i + '_' + (j + 1));
    bignews_list.insertBefore(buildBigNewsListItem(i, j, bignews[i]['items'][j + 1]), a);
    bignews_list.insertBefore(buildBigNewsListItem(i, j + 1, bignews[i]['items'][j]), a);
    bignews_list.removeChild(a);
    bignews_list.removeChild(b);
    cancelEditBigNewsListItem();
}

/* 删除列表 i 的列表项 j */
function delBigNewsListItem(i, j) {	
    var bignews = getBigNews();
    var bignews_items = bignews[i]['items'];
    if (j < 0 || j >= bignews_items.length) return;
    var bignews_list = document.getElementById('bignews_' + i);
    clearItems(bignews_list, bignews_list.childNodes);
    cancelEditBigNewsListItem();
    if (bignews_items.length == 1) return;
    bignews_items = bignews_items.slice(0, j).concat(bignews_items.slice(j + 1));	
    for (var j = 0; j < bignews_items.length; j++) {		
        bignews_list.appendChild(buildBigNewsListItem(i, j, bignews_items[j]));
    }
}

/* 构建链接项输入行
   n 表示列表项中的链接编号
   anchoritem 表示链接项的数据，其结构同函数 buildAnchorItem 的 anchoritem 参数结构一致。
*/
function buildAnchorItemInputLine(n, anchoritem) {
    var result = document.createElement('tr');
    var td = document.createElement('td');
    var btns = [
        {src: '/images/up.gif', name: 'up_anchor_item_btn', alt: '向上', onclick: function () { anchorItemUp(n); } },
        {src: '/images/down.gif', name: 'down_anchor_item_btn', alt: '向下', onclick: function () { anchorItemDown(n); } },
        {src: '/images/edit.gif', name: 'edit_anchor_item_btn', alt: '编辑', onclick: function () { editAnchorItem(n); } },
        {src: '/images/del.gif', name: 'del_anchor_item_btn', alt: '删除', onclick: function () { delAnchorItem(n); } }
    ];
    var img, btn;
    for (btn in btns) {
        btn = btns[btn];
        img = document.createElement('img');
        img.src = btn.src;
        img.name = btn.name;
        img.alt = btn.alt;
        img.title = btn.alt;
        img.onclick = btn.onclick;
        img.align = "absmiddle";
        img.style.width = "16px";
        img.style.height = "16px";
        img.style.cursor = 'pointer';
        td.appendChild(img);
        td.appendChild(document.createTextNode(' '));
    }
    var inputs = [
        { 'label': '标题', 'name': 'anchor_item_title', 'value': anchoritem['title'] },
        { 'label': '链接', 'name': 'anchor_item_href', 'value': anchoritem['href'] }
        //{ 'label': '类名', 'name': 'anchor_item_class', 'value': anchoritem['class'] }
    ];
    for (var i = 0; i < inputs.length; i++) {
        var label = document.createElement('label');
        label.appendChild(document.createTextNode(' ' + inputs[i].label + '：'));
        var input = document.createElement('input');
        input.className = 'input';
        input.type = 'text';
        input.value = inputs[i].value;
        input.id = inputs[i].name + '_' + n;
        label.htmlFor = input.id;
        label.appendChild(input);
        label.appendChild(document.createTextNode(' '));
        td.appendChild(label);
    }

    var label = document.createElement('label');
    label.appendChild(document.createTextNode(' 样式：'));
    var input = document.createElement('select');
    var option = document.createElement('option');
    option.value = "";
    option.appendChild(document.createTextNode("无"));
    input.appendChild(option);

    var option = document.createElement('option');
    option.value = "handyellow";
    option.appendChild(document.createTextNode("加黄"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "handgreen";
    option.appendChild(document.createTextNode("加绿"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "handbule";
    option.appendChild(document.createTextNode("加蓝"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "handblod";
    option.appendChild(document.createTextNode("加粗"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "handf14";
    option.appendChild(document.createTextNode("小字"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "handf16";
    option.appendChild(document.createTextNode("中字"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "handf18";
    option.appendChild(document.createTextNode("大字"));
    input.appendChild(option);


/* p.t update 2013-09-23
    var option = document.createElement('option');
    option.value = "handred";
    option.appendChild(document.createTextNode("加红"));
    input.appendChild(option);

    var option = document.createElement('option');
    option.value = "handblack";
    option.appendChild(document.createTextNode("加黑"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "handblod";
    option.appendChild(document.createTextNode("加粗"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "linez";
    option.appendChild(document.createTextNode("加线"));
    input.appendChild(option);
    var option = document.createElement('option');
    option.value = "v";
    option.appendChild(document.createTextNode("视频"));
    input.appendChild(option);
	var option = document.createElement('option');
    option.value = "f12";
    option.appendChild(document.createTextNode("小字"));
    input.appendChild(option);
*/

    input.className = 'input';
    input.value = anchoritem['class'];
    input.id = 'anchor_item_class_' + n;
    label.htmlFor = input.id;
    label.appendChild(input);
    label.appendChild(document.createTextNode(' '));
    td.appendChild(label);

    result.appendChild(td);
    return result;
}

/* 构建链接项输入列表
   anchorItems 为 anchoritem 链接项结构的数组
*/
function buildAnchorItemInput(anchorItems) {
    var anchor_items_body = document.getElementById('anchor_items_body');
    for (var i = 0; i < anchorItems.length; i++) {
        anchor_items_body.appendChild(buildAnchorItemInputLine(i, anchorItems[i]));
    }
}

/* 在链接项输入列表中添加一个新的链接项输入行 */
function addAnchorItem() {
    var anchor_items_body = document.getElementById('anchor_items_body');
    var i = anchor_items_body.getElementsByTagName('tr').length;
    var anchorItem = { 'title': '', 'href': '', 'class': '' };
    anchor_items_body.appendChild(buildAnchorItemInputLine(i, anchorItem));
    editAnchorItem(i);
}

/* 获取所有的链接项输入值
   其返回结果的结构为 anchoritem 链接项结构的数组
*/
function getAnchorItems() {
    var result = [];
    for (i = 0; document.getElementById('anchor_item_title_' + i); i++) {
        result[i] = {
            'title': document.getElementById('anchor_item_title_' + i).value,
            'href': document.getElementById('anchor_item_href_' + i).value,
            'class': document.getElementById('anchor_item_class_' + i).value
        }
    }
    return result;
}

/* 更新列表 i 的列表项 j 的内容 */
function updateBigNewsListItem(i, j) {
    var bignewslistitem = document.getElementById('bignews_' + i + '_' + j);
    if (bignewslistitem) {
        clearItems(bignewslistitem, bignewslistitem.getElementsByTagName('a'));
        var anchorItems = getAnchorItems();
        for (var k = 0; k < anchorItems.length; k++) {
            var a = buildAnchorItem(i, j, k, anchorItems[k]);
            bignewslistitem.appendChild(a);
            a.style.color = bignewslistitem.style.color;
        }
    }
}

/* 编辑列表 i 的列表项 j 的内容 */
function editBigNewsListItem(i, j) {
    var bignews = getBigNews();
    var update_anchor_btn = document.getElementById('update_anchor_btn');
    update_anchor_btn.onclick = function () {
        updateBigNewsListItem(i, j);
        cancelEditBigNewsListItem();
    }
    clearTbody(document.getElementById('anchor_items_body'));
    buildAnchorItemInput(bignews[i]['items'][j]['items']);
    document.getElementById('listitem_manager').style.display = 'block';
}

/* 取消编辑列表项 */
function cancelEditBigNewsListItem() {
    document.getElementById('listitem_manager').style.display = 'none';
    cancelEditAnchorItem();
}

/* 上移链接项 i */
function anchorItemUp(i) {
    if (i <= 0) return;
    var anchorItems = getAnchorItems();
    var anchor_items_body = document.getElementById('anchor_items_body');
    var lists = anchor_items_body.getElementsByTagName('tr');
    var a = lists[i];
    var b = lists[i - 1];
    anchor_items_body.insertBefore(buildAnchorItemInputLine(i - 1, anchorItems[i]), a);
    anchor_items_body.insertBefore(buildAnchorItemInputLine(i, anchorItems[i - 1]), a);
    anchor_items_body.removeChild(a);
    anchor_items_body.removeChild(b);
    cancelEditAnchorItem();
}

/* 下移链接项 i */
function anchorItemDown(i) {
    var anchorItems = getAnchorItems();
    if (i >= anchorItems.length - 1) return;
    var anchor_items_body = document.getElementById('anchor_items_body');
    var lists = anchor_items_body.getElementsByTagName('tr');
    var a = lists[i];
    var b = lists[i + 1];
    anchor_items_body.insertBefore(buildAnchorItemInputLine(i, anchorItems[i + 1]), a);
    anchor_items_body.insertBefore(buildAnchorItemInputLine(i + 1, anchorItems[i]), a);
    anchor_items_body.removeChild(a);
    anchor_items_body.removeChild(b);
    cancelEditAnchorItem();
}

/* 删除链接项 i */
function delAnchorItem(i) {
    var anchorItems = getAnchorItems();
    if (i < 0 || i >= anchorItems.length) return;
    var anchor_items_body = document.getElementById('anchor_items_body');
    clearTbody(anchor_items_body);
    cancelEditAnchorItem();
    if (anchorItems.length == 1) return;
    anchorItems = anchorItems.slice(0, i).concat(anchorItems.slice(i + 1));
    for (var i = 0; i < anchorItems.length; i++) {
        anchor_items_body.appendChild(buildAnchorItemInputLine(i, anchorItems[i]));
    }
}

/* 编辑链接项 i */
function editAnchorItem(i) {
    window.setAnchorItem = function (url, title) {
        document.getElementById('anchor_item_title_' + i).value = title;
        document.getElementById('anchor_item_href_' + i).value = url;
        return false;
    }
    document.getElementById('search_box').style.display = 'block';
}

/* 取消编辑链接项 */
function cancelEditAnchorItem() {
    document.getElementById('search_box').style.display = 'none';
}

/* 初始化页面头条管理页面 */
function initBigNews(bignews) {
    document.getElementById('add_online_news_btn').onclick = function () { addBigNewsList('dl'); };
    document.getElementById('add_news_list_btn').onclick = function () { addBigNewsList('ul'); };
    document.getElementById('cancel_listitem_btn').onclick = cancelEditBigNewsListItem;
    var add_anchor_btn = document.getElementById('add_anchor_btn');
    add_anchor_btn.align = "absmiddle";
    add_anchor_btn.style.width = "16px";
    add_anchor_btn.style.height = "16px";
    add_anchor_btn.style.cursor = "pointer";
    add_anchor_btn.onclick = addAnchorItem;
    document.getElementById('listitem_manager').style.display = "none";
    document.getElementById('search_box').style.display = 'none';
    var big_news_body = document.getElementById('big_news_body');
    for (var i = 0; i < bignews.length; i++) {
        big_news_body.appendChild(buildBigNewsList(i, bignews[i]));
    }
}

/* 验证pid */
function checkBigNews() {
	/*
	var dObj = new Date();
	var maxTime = document.getElementById("MaxTimeWait").value;
	if((dObj.getTime() - startTime) / 1000 > maxTime) {
		alert("您提交保存的时间已经超时，请重新打开此页。");
		return false;
	}
	*/
	var pid=document.getElementById('pid').value;
	var re=/^[apf][0-9]+$/;		
	if(!(re.exec(pid)) && pid){
		alert('ID必须为英文字母p,f或者a开头加数字组成!');		
		return false;
	}
	return true;
}
