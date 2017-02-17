// ----------------------------------------- PERMANENTES -----------------------------------------

function download(file_name, path, download_name){
	path = PHP.trim(path, '/');
	if (false) {//browserInfo.name == "IE"
		window.open(path + '/' + file_name, "_blank");
	} else {
		var link = document.createElement("a");
		link.setAttribute("download", download_name);
		link.setAttribute("href", path + '/' + file_name);
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}
}

function moveScroll(px, onfinish){
	onfinish = onfinish || null;
	$("html, body").stop().animate({scrollTop: px}, '500', 'swing', onfinish);
}

function parseBoolean(value){
	if(value instanceof Array)
		if(value.length == 0)
			return false;
		else
			return true;

	if(typeof value != 'undefined')
		switch(value.toString().toLowerCase().trim()){
			case "true": case "yes": case "1":
				return true;
			case "false": case "no": case "0": case null:
				return false;
			default:
				return Boolean(value);
		}
	else
		return false;
}

function removeHtmlCode(html){
	var tmp = document.createElement("DIV");
	tmp.innerHTML = html;
	return tmp.textContent || tmp.innerText || "";
}

function getObjHTML(html){
	var nodes = $.parseHTML(html).filter(function(node){
		return node.nodeType == Node.ELEMENT_NODE;
	});

	return nodes.length == 1? nodes[0]: (nodes.length == 0? false: nodes);
}

function getJsHTML(html){
	return (typeof html == 'string')? getObjHTML(html) : ((html instanceof jQuery)? html[0] : html);
}

function getJQueryHTML(html){
	return (typeof html == 'string')? $(getObjHTML(html)) : ((html instanceof jQuery)? html : $(html));
}

function getTableColumns(table, hiddens, visibles){
	var columns = {};
	var array_columns = [];
	var $table = getJQueryObj(table);

	visibles = typeof visibles == 'undefined'? true: visibles;
	hiddens = typeof hiddens == 'undefined'? true: hiddens;

	if(visibles)
		array_columns = array_columns.concat($table.bootstrapTable('getVisibleColumns'));

	if(hiddens)
		array_columns = array_columns.concat($table.bootstrapTable('getHiddenColumns'));

	array_columns.filter(function(col){
		return !col.checkbox && !col.radio;
	}).forEach(function(col){
		columns[col.field] = removeHtmlCode(col.title);
	});

	return columns;
}

function getJsObj(param){
	return (typeof param == 'string')? document.getElementById(param) : ((param instanceof jQuery)? param[0] : param);
}

function getJQueryObj(param){
	return (typeof param == 'string')? $("#"+param) : ((param instanceof jQuery)? param : $(param));
}

function objectToArray(obj){
	return Object.keys(obj).map(function (key) {return obj[key]});
}

function clone(obj) {
    var copy;

    // Handle the 3 simple types, and null or undefined
    if (null == obj || "object" != typeof obj) return obj;

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (var i = 0, len = obj.length; i < len; i++) {
            copy[i] = this.clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = this.clone(obj[attr]);
        }
        return copy;
    }

    throw new Error("Unable to copy obj! Its type isn't supported.");
}

function getHiddenHtml(html, remove){
	var hiddens = {},
		$html = html? getJQueryObj(html) : $(document),
		$hiddens = $(".hidden-html", $html);
	remove = typeof remove != 'undefined'? remove: true;

	$hiddens.each(function(){
		hiddens[$(this).attr("id")] = $(this).html();
	});

	if(remove)
		$hiddens.remove();

	return hiddens;
}

function requireTableSelect(table, error_message){
	return requireTableSelects(table, error_message).pop();
}

function requireTableSelects(table, error_message){
	var $table = getJQueryObj(table);
	var selections = $table.bootstrapTable('getSelections');

	if(selections.length > 0)
		return selections;

	Dialog.alert(error_message);
	throw new Error(error_message);
}