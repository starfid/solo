if(window.navigator.userAgent.indexOf('MSIE ')>1){
	document.title = 'Sorry';
	document.body.innerHTML = '<font color=white><h1>Support for older<br />versions of Internet<br />Explorer ended</h1>Microsoft no longer provides security updates or<br />technical support for old versions of Internet Explorer.<br />Please use another browser.</font>';
}
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
$.timer = function(loopTime,milSec,loopFn,endFn){
	var i = -1, t = setInterval(function(){
		if(i++ >= loopTime-1) return endFn(),clearInterval(t);
		else return(loopFn(i));
	},milSec);
};
$.submitting = function(submit){
	prepare = function() {
		$('#shell').css({
			'padding-top'	:'5px',
			'border'		:'solid 1px #'+(isLight?'B0B0B0':'17181C')
		});
		$('#actForm').css('overflow','hidden');
		$('#multiple').css('background-color',(isLight?'#CACACA':'#000000'));
		$('#actions > img').each(function(o){
			if(['delete'].indexOf($(o).attr('id'))<0 && !submit){
				$(o).css('display','none');
			}
		});
		isStacked = true;
	},
	loop = function(i) {
		$('#multiple').css({
			'zoom'				:(98-i)+'%',
			'padding'			:(2+i)+'%'
		});	
	},
	stack = function(){
		var top = $('#shell').css('top') - 10,
		left = $('#shell').css('left'),
		width = $('#shell').css('width') * 0.96;
		$('#shell').append({
			'element'	:'div',
			'id'		:'stack',
			'style'		:'top:'+top+'px;left:'+left+'px;width:'+width+'px;margin:0 1% 2% 1%;position:absolute;z-index:3;height:9px;border:solid 1px #'+(isLight?'B0B0B0':'17181C')+';background-color:#'+(isLight?'F5F5F5':'17181C')
		});
	},
	sending = function(cut){
		var steps = [
			function() {
				$('#actions > img').css('display','none');
				$('#shell').css({
					'padding-top'	:'0px',
					'border'		:'solid 1px #'+(isLight?'B0B0B0':'17181C')
				});
			},
			function() {
				var top = $('#multiple').css('top'),
				left = $('#shell').css('left'),
				width = $('#shell').css('width')-1;

				$('#shell').css('visibility','hidden');
				$('#multiple').append({
					'element'	:'div',
					'id'		:'tail',
					'style'		:'top:'+top+'px;left:'+left+'px;position:absolute;width:'+width+'px;height:100px;border:solid 1px #'+(isLight?'B0B0B0':'17181C')+';background-color:#'+(isLight?'F5F5F5':'17181C')
				});
			},
			function(){$('#tail').css('height','10px');},
			function(){$('#tail').destroy();},
			function(){
				$('#actForm')[0].submit();
			}
		];
		
		var itr = 5, itv = 40;
		if(cut) {
		 	$('#stack').destroy();
		 	steps = steps.slice(1);
		 	itr = 4;
		 	itv = 60;
		}
		$.timer(itr,itv,
			function(i){
				steps[i]();
			},
			function(){}
		);
		
	};
	
	$('#actForm').css('overflow') != 'hidden' && prepare();
	
	var cont = !!$('#stack')[0] && submit;
	
	$.timer(3,60,
		function(i){
			cont || loop(i);
		},
		function(){
			if(cont) sending(true);
			else if(selList.length > 1) stack();
			else if(submit) sending(false);
		}
	);
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
					document.initView.getComputedStyle(el, null).getPropertyValue(prop)
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
	destroy: function() {
		this[0].parentNode.removeChild(this[0]);
	},
	remove: function(attr){
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
	}
};

var selList = [], app, listCount, prevStream = 0, shifted = !!0, isPortrait = !0,
isLight = document.cookie.indexOf('scheme=light')>-1?true:false,
isStacked = false;

searchFocus = function(){
	setTimeout(function(){
		var node = $('#search')[0];
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
streaming = function(){
	var txt = '', i, prevIndex = $(selList[0]).attr('index'), pos = [5,10,80,82,84,84];
	$('#response').destroy();
	$.ajax('response','?group='+$('#group').val()+'&app='+$('#app').val()+'&keyword='+$('#search').val()+'&format=json&r='+Math.random());
	if(response[app][0] !== undefined && prevStream != 0 && response[app][0]['itemKey'] != prevStream){
		listCount = response[app].length;
		for(i in response[app]){
			txt = txt + "<dl onmousedown='listSelected(this);searchFocus()' index='"+i+"'>";
			txt = txt + "<dt>"+response[app][i]['itemTitle']+"</dt>";
			txt = txt + "<dd>"+response[app][i]['itemInfo']+"</dd>";
			txt = txt + "</dl>";
		}
		$.timer(6,70,
			function(i){
				$('#wlis').css('margin-top',pos[i]+'px');
			},
			function(){
				$('#wlis').css('margin-top','0');
				$('#wlis').text(txt);
				listSelected($('#wlis > dl')[prevIndex]);
			}
		);
	}
	prevStream = response[app][0]['itemKey'];
	setTimeout('streaming()',3000);
},
createKey = function(){
	$('.itemKey').length>0 && 
	$('.itemKey').each(function(o){
		$(o).destroy();
	});
	
	for(i in selList){
		var key = $(selList[i]).attr('data-key');
		$('#actForm').append({
			'element'	:'input',
			'type'		:'hidden',
			'class'		:'itemKey',
			'name'		:'itemKey[]',
			'value'		: key
		});
	}
},
listSelected = function(o){
	$('#itemAction').val('');
	if(!response[app]) return false;
	if(!shifted){
		for(i in selList) $(selList[i]).remove('class');
		selList = [];
		if(isStacked){
			isStacked = false;
			$('#stack').destroy();
			$('#shell').css({'padding-top':'0px','border':'0'});
			$('#multiple').css({'zoom':'100%','padding':'0','background-color':'transparent'});
			$('#actForm').css('overflow','auto');
			$('#actions > img').length > 0 && $('#actions > img').css('display','block');
			$('')
		}
	}
	else !isStacked && (listCount > 1) && $.submitting(false);
	//check not double
	selList.indexOf(o)<0 && selList.push(o);
	createKey();

	$(o).attr('class','selParent');
	var res = response[app][$(o).attr('index')];
	for(key in res) {
		if(/(itemRank|itemInfo|itemTitle|itemAction)/.test(key)) continue;
		if(!!$('#'+key)[0]) {
			if($('#'+key)[0].nodeName=="INPUT") $('#'+key).val(res[key]);
			else {
				var i, tmp = [], col = response[key.replace('_id','')];
				for(var i in col) tmp.push("<option value=\""+col[i]['itemKey']+"\" "+(res[key]==col[i]['itemKey']?'selected':'')+">"+col[i]['itemTitle']+"</option>");
				$('#'+key).text(tmp.join(''));
			}
		}
	}
},
firstCompose = function(){
	$('#actions > img').each(function(o){
		if(['update'].indexOf($(o).attr('id'))<0){
			$(o).css('display','none');
		}
	});
	$('.wrapper > *').each(function(o){
		if($(o)[0].nodeName=='SELECT'){
			var col = response[$(o).attr('id').replace('_id','')], i, tmp = [];
			for(var i in col) tmp.push("<option value=\""+col[i]['itemKey']+"\">"+col[i]['itemTitle']+"</option>");
			$(o).text(tmp.join(''));
		}
	});
	$('#itemAction').val('compose');
};



window.onload = function(){
	app = $("#app").val();
	listCount = $('#wlis > dl').length;
	notEmpty = listCount<1?false:true;
	isPortrait = $('.act').css('width')<1;
	searchFocus();

	if(notEmpty){
		selList[0] = $('#wlis > dl')[0];
		($('dl').length && response[app]) && listSelected(selList[0]);
		if(document.cookie.indexOf('stream=on')>-1) setTimeout('streaming()',3000);
	}
	else if(!notEmpty && $('.row').length>0 && $('#wlis > dl').length<1){
		firstCompose();
	}
};
document.onkeyup = function(e){
	shifted = (e||window.event).shiftKey?!0:!!0;
}
document.onkeydown = function(e){
	shifted = (e||window.event).shiftKey?!0:!!0;
	if((e||window.event).keyCode == 27) {
		$('#search').val('');
		searchFocus();
	}
}
$('#search').on('keydown',function(e){
	if(selList.length < 1){ return; }
	var keyCode = (e.which||window.event.keyCode ), target, top;

	if(keyCode==40 || keyCode==38){
		var index = parseInt($(selList.slice(-1)[0]).attr('index'));

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
	isPortrait && setTimeout(function(){
		$('.lis').css('display','none');
		$('.act').css('display','block');
	},100);
});
$('#menuButton').on('mousedown',function(){
	$('.lis').css('display','none');
	$('.nav,#navlogin').css('display','block');
});
$('#actions img').on('mousedown',function(){
	var action = $(this).attr('id');
	if(action=='backButton'){
		$('.act').css('display','none');
		$('.lis').css('display','block');
	}
	else if(action=='compose'){
		$('#actForm input').each(function(obj){
			$(obj).val('');
		});
		setTimeout(function(){
			$('#actForm input')[0].focus();
		},10);
		$('#itemAction').val('compose');
	}
	else{
		if(action=='update' && $('#itemAction').val() =='compose') action = 'compose';
		$('#itemAction').val(action);
		if(action=='delete' && !confirm('Are you sure to delete '+selList.length+' item'+((selList.length>1)?'s':'')+'?')){
			return false;
		}
		$.submitting(true);
	}
});
$('.wrapper > input').on('keydown',function(e){
	var id = $(this).attr('id'), tmp = [], found = 0;
	if((e.which||window.event.keyCode)!=13) return false;
	for(i in response[app][0]){
		if(i.substr(-3)!='_id') tmp.push(i);
	}
	found = tmp.indexOf(id)+1;
	if(found<tmp.length){
		$('#'+tmp[found])[0].focus();
	}
	else if($('#update').length>0){
		if($('#itemAction').val()=='') $('#itemAction').val('update');
		$.submitting(true);
	}
});