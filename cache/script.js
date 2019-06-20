var $ = function(expr, context) {
	return new $.init(expr, context);
};
$.init = function(expr,context) {
	if(expr.nodeName && !context) this[0] = expr;
	else {
		try { var el = (context||document)['querySelectorAll'](expr); }
		catch(err){ var el = expr; }
		[].push.apply(this,[].slice.call(el));
	}
};
$.ajax = function(id,url) {
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.async = true;
	script.src = url;
	script.setAttribute('id',id);
	var node = document.getElementsByTagName('script')[0];
	node.parentNode.insertBefore(script, node);
};
$.init.prototype = {
	each: function(el) {
		for (var l=0;l<this.length;l++) el(this[l]);
	},
	css: function(prop, val) {
		if ('object' == typeof prop) this.each(function(val) {
			for(var i in prop) val.style[i] = prop[i];
		});
		else {
			prop = prop.replace(/-([a-z])/g, function (g) { return g[1].toUpperCase(); });
			var el = this[0], lt = /(left||top)/.test(prop);
			if(arguments.length>1) return 1 < this.length ? this.each(function(o){o.style[prop] = val;}) : el.style[prop] = val,"";
			else return el.style[prop]==''?(
				el.currentStyle && !lt?
				el.currentStyle[prop]:(lt?
					Math.round(el.getBoundingClientRect()[prop]):
					document.defaultView.getComputedStyle(el, null).getPropertyValue(prop)
				)
			):el.style[prop];
		}
	},
	on: function(evt, fn) {
		for (var l = this.length; l--;) {
			var el = this[l];
			el.addEventListener ? el.addEventListener(evt, fn, !1) : el.attachEvent("on" + evt, function() {
				return fn.call(el, window.event);
			});
		}
	},
	append: function(param) {
		var el = document.createElement(param.element||'div'), ar = [];
		param.hasOwnProperty('element') && delete param.element;
		this[0].appendChild(el);
		for(var i in param) el.setAttribute(i,param[i]);
	},
	remove: function() {
		this[0].parentNode.removeChild(this[0]);
	},
	rem: function(attr){
		this[0].removeAttribute(attr);
	},
	attr: function(key,val) {
		var el = this[0];
		return val ? (el.setAttribute(key, val), '') : el.getAttribute(key);
	},
   	val: function(dat){
		var el = this[0];
		return arguments.length ? (el.value = dat, '') : el.value;
	},
	text: function(dat){
		var el = this[0];
		return arguments.length ? (el.innerHTML = dat, '') : el.innerHTML;
	},
};

var selList = [], app, isEmpty, listCount, streamTm, prevStream = 0;

searchFocus = function(){
	var node = $('#search')[0];
	setTimeout(function(){
		node.focus();
		"number"==typeof node.selectionStart?node.selectionStart=node.selectionEnd=node.value.length:"undefined"!=typeof node.createTenodetRange&&(r=node.createTenodetRange(),r.collapse(!1),r.select())
	},10);
},
setScheme = function(){
	document.cookie=-1<document.cookie.indexOf('scheme=')?
	-1<document.cookie.indexOf('scheme=light')?
	'scheme=dark':'scheme=light':'scheme=light';
},
setStream = function(){
	document.cookie=-1<document.cookie.indexOf('stream=')?
	-1<document.cookie.indexOf('stream=on')?
	'stream=off':'stream=on':'stream=on';
},
listSelected = function(o){
	if(!response[app]) return false;
	for(i in selList) $(selList[i]).rem('class');
	selList = [];
	selList.push(o);
	$(o).attr('class','selParent');
	var res = response[app][$(o).attr('index')];
	$('h2').text(res['itemTitle']);
	$('h5').text(res['itemInfo']);
	for(key in res) {
		if(/(itemRank|itemInfo|itemTitle|itemAction|selIndex)/.test(key)) continue;
		if(!!$('#'+key)[0]) {
			if($('#'+key)[0].nodeName=="INPUT") $('#'+key).val(res[key]);
			else {
				var i, tmp = [], col = response[key.replace('_id','')];
				for(var i in col) tmp.push("<option "+(res[key]==col[i]['itemKey']?'selected':'')+">"+col[i]['itemTitle']+"</option>");
				$('#'+key).text(tmp.join(''));
			}
		}
	}
	streaming = function(){
		var txt = '', i, prevIndex = $(selList[0]).attr('index');
		$('#response').remove();
		$.ajax('response','?group='+$('#group').val()+'&app='+$('#app').val()+'&keyword='+$('#search').val()+'&format=json&r='+Math.random());
		if(response[app][0] !== undefined && response[app][0]['itemKey'] != prevStream){
			listCount = response[app].length;
			for(i in response[app]){
				txt = txt + "<dl onmousedown='listSelected(this);searchFocus()' index='"+i+"'>";
				txt = txt + "<dt>"+response[app][i]['itemTitle']+"</dt>";
				txt = txt + "<dd>"+response[app][i]['itemInfo']+"</dd>";
				txt = txt + "</dl>";
			}
			$('#wlis').text(txt);
			listSelected($('#wlis > dl')[prevIndex]);
		}
		prevStream = response[app][0]['itemKey'];
		setTimeout('streaming()',3000);
	}	
};

window.onload = function(){
	app = $("#app").val();
	listCount = $('#wlis > dl').length;
	notEmpty = listCount<1?false:true;
	searchFocus();
	if(notEmpty){
		selList[0] = $('#wlis > dl')[$('#selIndex').val()];
		($('dl').length && response[app]) && listSelected(selList[0]);

		if(document.cookie.indexOf('stream=on')>-1){
			setTimeout('streaming()',3000);
		}
	}
};
document.onkeydown = function(e){
	if((window.event.keyCode || e.which) == 27){
		$('#search').val(''); searchFocus();
	}
}
$('#search').on('keydown',function(e){
	if(selList.length < 1){ return; }
	var keyCode = (window.event.keyCode || e.which), target, top;

	if(keyCode==40 || keyCode==38){
		var index = parseInt($(selList[0]).attr('index'));

		if(keyCode==40 && listCount-1 > index) index++;
		else if(keyCode==38 && index > 0) index--;
		target = $('#wlis > dl')[index];
		listSelected(target);
		if(target.getBoundingClientRect()['top'] < 0) {
			$('#wlis')[0].scrollTop = parseInt($('#wlis')[0].scrollTop) - 150;
			if(index == 0) $('#wlis')[0].scrollTop = 0;
		}
		else if(target.getBoundingClientRect()['bottom'] > (window.innerHeight+50)){
			$('#wlis')[0].scrollTop = parseInt($('#wlis')[0].scrollTop) + 150;
			if(index == listCount) $('#wlis')[0].scrollTop + 210;
		}
	}
});
$('#wlis > dl').on('mousedown',function(){
	listSelected(this);
	searchFocus();
});
