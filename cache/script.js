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
$.ajax = function(id,url,fn) {
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.async = true;
	script.src = url;
	script.setAttribute('id',id);
	var newElement = document.getElementsByTagName('script')[0];
	newElement.parentNode.insertBefore(script,newElement);
	$('#'+id).on('load',fn);
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
		var el = document.createElement(param.element||'div');
		param.hasOwnProperty('element') && delete param.element;
		if(param.hasOwnProperty('text')) el.innerHTML = param.text; delete param.text;
		this[0].appendChild(el);
		for(var i in param) el.setAttribute(i,param[i]);
	},
	appendSVGText: function(param) {
		var el = document.createElementNS('http://www.w3.org/2000/svg','text');
		el.appendChild(document.createTextNode(param['text']));
		if(param.hasOwnProperty('text')) delete param.text;
		for(var i in param) el.setAttributeNS(null,i,param[i]);
		this[0].appendChild(el);
		
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

var selList = [], app, listCount, prevStream = 0, shifted = !!0, isPortrait = !0, isStacked = !!0, noRibbon = !!0, prevList = {}, prevFlag = null, screenWidth = parseInt(window.innerWidth);
isLight = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)?!!0:true, gap = [50,30,5], animSec = 40, hideDelete = !!0, chartCreated = !!0;

searchFocus = function(){
	$('#search').length > 0 &&
	setTimeout(function(){
		var node = $('#search')[0];
		node.focus();
		"number"==typeof node.selectionStart?node.selectionStart=node.selectionEnd=node.value.length:"undefined"!=typeof node.createTenodetRange&&(r=node.createTenodetRange(),r.collapse(!1),r.select())
	},10);
},
setStream = function(){
	document.cookie=-1<document.cookie.indexOf('stream=')?
	-1<document.cookie.indexOf('stream=on')?
	'stream=off':'stream=on':'stream=on';
},
streaming = function(){
	var txt = '', i, pos = [5,10,80,82,84,84], prevIndex = $(selList[0]).attr('index'),
	keyword = $('#search').length==1?'&keyword='+$('#search').val():'';
	$('#response').destroy();
	$.ajax(
		'response',
		'?group='+$('#group').val()+'&app='+$('#app').val()+keyword+'&format=json&r='+Math.random(),
		function(){
			if(prevStream != 0 && response[app][0]['itemKey'] != prevStream){
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
						!noRibbon && listSelected($('#wlis > dl')[prevIndex]);
					}
				);
			}
		}
	);
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
	hideDelete && ($('#delete').length == 1 && $('#delete').css('display','block'));
	if(!response[app]) return !!0;
	if(!shifted){
		for(i in selList) $(selList[i]).remove('class');
		selList = [];
		if(isStacked){
			isStacked = !!0;
			$('#stack').length == 1 && $('#stack').destroy();
			$('#shell').css('visibility','visible');
			$('#shell').css({'padding-top':'0px','border':'0'});
			$('#multiple').css({'zoom':'100%','padding':'0','background-color':'transparent'});
			$('#actForm').css('overflow','auto');
			$('#actions > img').length > 0 && $('#actions > img').css('display','block');
		}
	}
	else !isStacked && (listCount > 1) && submitting(!!0);
	//check not double
	selList.indexOf(o)<0 && selList.push(o);
	createKey();

	prevFlag !== null && $(prevFlag[0]).attr('class','flag');
	prevFlag = $(o).attr('class')=='flag'?$(o):null;

	$(o).attr('class','selParent');

	var index = $(o).attr('index'), res = response[app][index];
	visibleList(index);

	for(key in res) {
		if(/(itemRank|itemInfo|itemTitle|itemAction)/.test(key)) continue;
		if(!!$('#'+key)[0]) {
			if($('#'+key)[0].type == 'checkbox') {$('#'+key).val(res[key]);$('#'+key)[0].checked = res[key]==1?!0:!!0; }
			else if($('#'+key)[0].nodeName=="INPUT") $('#'+key).val(res[key]);
			else {
				var i, tmp = ['<option></option>'], col = response[key.replace('_id','')];
				for(var i in col) tmp.push("<option value=\""+col[i]['itemKey']+"\" "+(res[key]==col[i]['itemKey']?'selected':'')+">"+col[i]['itemTitle']+"</option>");
				$('#'+key).text(tmp.join(''));
			}
		}
	}

	if($('svg').length==1 && 'itemChartLabel' in res && 'itemChartNumber' in res){
		!chartCreated && makingChart(response[app]);
		showChartLabel(index);
	}
	
	!isPortrait && $('#backButton').css('display','none');
},
mobileLink = function(){
	$('#wnav > li > a').each(function(o){
		if($(o).attr('class') != 'links') return;
		var href = $(o).attr('href');
		$(o).attr('href','#');
		$(o).attr('onmousedown',"showNav(!!0,'"+href+"')");
	});
},
submitting = function(submit){
	prepare = function() {
		$('#shell').css({
			'padding-top'	:'5px',
			'border'		:'solid 1px #'+(isLight?'B0B0B0':'17181C')
		});
		$('#actForm').css({
			'overflow':'hidden',
			'height':'100%'
		});
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
			'zoom'			:(98-i)+'%',
			'padding-top'		:(2+i)+'%',
			'padding-left'		:(2+i)+'%',
			'padding-right'		:(2+i)+'%'
		});
	},
	stack = function(){
		var top = $('#shell').css('top') - 10,
		left = $('#shell').css('left'),
		width = $('#shell').css('width') * 0.96;
		$('#shell').append({
			'element'	:'div',
			'id'		:'stack',
			'style'		:'top:'+top+'px;left:'+left+'px;width:'+width+'px;margin:0 1% 2% 1%;position:absolute;z-index:3;height:9px;border:solid 1px #'+(isLight?'B0B0B0':'17181C')+';background-color:#'+(isLight?'E7E9EB':'17181C')
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
					'style'		:'top:'+top+'px;left:'+left+'px;position:absolute;width:'+width+'px;height:100px;border:solid 1px #'+(isLight?'B0B0B0':'17181C')+';background-color:#'+(isLight?'E7E9EB':'17181C')
				});
			},
			function(){$('#tail').css('height','10px');},
			function(){$('#tail').destroy();},
			function(){
				$('#actForm')[0].submit();
			}
		];
		var itr = 5, itv = 40;
		boxchecking();
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
			else if(submit) sending(!!0);
		}
	);
},
firstCompose = function(){
	$('#actions > img').each(function(o){
		if(['update'].indexOf($(o).attr('id'))<0 && ['save'].indexOf($(o).attr('id'))<0){
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
},
deleteConfirm = function(){
	return confirm('Are you sure to delete '+selList.length+' item'+((selList.length>1)?'s':'')+'?')?true:!!0;
},
visibleList = function(index){
	var target = $('#wlis > dl')[index];
	if(target.getBoundingClientRect()['top'] < 0) {
		target.scrollIntoView(true);
	}
	else if(target.getBoundingClientRect()['bottom'] > (window.innerHeight+50)){
		target.scrollIntoView(!!0);
	}
	window.scrollTo(0,0);
},
pinning = function(){
	var id, copyVal = [];
	$('#actForm > div > div[class="wrapper"] > *').each(function(o){
		id = $(o).attr('id');
		copyVal[id] = $('#'+id).val();
	});
	$('#pinned').text('');
	$('#actForm > .row').each(function(o){
		$('#pinned').append({
			'element':'div',
			'class':'row',
			'text':o.innerHTML
		});
	});
	$('#pinned > div > div[class="wrapper"] > *').each(function(o){
		$(o).val(
			copyVal[$(o).attr('id')]
		);
	});
},
composing = function(){
	if($('#delete').length==1) $('#delete').css('display','none'); hideDelete = !0;
	$('.selParent').each(function(o){
		$(o).remove('class');
	});
	$('.row input,.row select').each(function(obj){
		$(obj).val(
			$(obj).attr('type')=='date'?new Date().toISOString().slice(0, 10):''
		);
	});
	if($('.switch').length>0){
		$('.switch input').each(function(o){
			$(o)[0].checked = false;
			$(o).val('0');
		});
	}
	setTimeout(function(){
		$('#actForm input')[0].focus();
	},10);
	$('#itemAction').val('compose');
},
logOpening = function(opening,launch){
	!opening && gap.reverse();
	var marginTop = ['Android','iPhone','iPad'].indexOf(navigator.platform)<0?190:30;
	opening && marginTop == 190 && $('#loginPop').css('top','300px');
	$.timer(gap.length,animSec,
		function(i){
			$('#loginPop').css('top',(opening?gap[i]:marginTop+gap[i])+'px');
			opening && $('#loginForm').css('margin-top',(gap[i]+marginTop)+'px');
		},
		function(){
			if(opening){
				$('#loginPop').css('top','0');
				$('#loginForm').css('margin-top',marginTop+'px');
				setTimeout(function(){
					if($('#loginPop').length == 1) $('#loginPop').destroy();
				},60000);
			}
			else {
				window.scrollTo(0,0);
				launch && showNav(!!0) && $('#loginForm')[0].submit();
				if($('#loginPop').length == 1) $('#loginPop').destroy();
				gap.reverse();
			}
		}
	);
},
logForm = function(){
	$('body').append({
		'element':'div',
		'id':'loginPop',
		'text':"<form id='loginForm' action='?' method='post'><h1>Sign in to Application</h1><div><input style='font-size:20px' name='username' autocapitalize='off' type='text' placeholder='Username' autofocus /><br /><input style='font-size:20px' name='password' type='password' onkeydown='logFormEnter(event)' placeholder='Password'/><input type='hidden' name='type' value='admin' /><br /><br /><input style='font-size:20px' value='Login' type='button' onmousedown='logOpening(!!0,!0)' /><br /><br /><span style='cursor:pointer' onclick='logOpening(!!0,!!0)'>Cancel</cancel></div></form>"
	});
	logOpening(!0,!!0);
},
showDetail = function(opening){
	$('.act').css({
		'display':'block',
		'position':'absolute',
		'z-index':1,
		'left':(opening?'100':0)+'px',
	});
	$('#ribbon > .act').css('top','0px');
	$('#view > .act').css('top','45px');
	isLight && $('.act').css('border-left','solid 1px #EEEEEE');
	
	var wlisGap = [2,6,10];
	!opening && gap.reverse();
	!opening && wlisGap.reverse();

	$.timer(gap.length,animSec,
		function(i){
			$('.act').css('left',gap[i]+'px');
			$('#wlis').css('margin-left','-'+wlisGap[i]+'px');
		},
		function(){
			$('.act').css({
				'left':(opening?'0':screenWidth)+'px',
				'border-left':'none'
			});
			$('#wlis').css('margin-left',(opening?'-15px':0));
			!opening && gap.reverse() && $('#side').destroy();
			!opening && wlisGap.reverse();
			opening && sideButton('left');
		}
	);
},
showNav = function(opening,href=''){
	$('.nav').css({
		'display':'block',
		'position':'absolute',
		'z-index':1,
		'left':(opening?'-100':0)+'px',
		'width':$('.lis').css('width')
	});
	$('#ribbon > .nav').css('top','0px');
	$('#view > .nav').css('top','45px');

	href != '' && $('.selParent').length == 1 && $('.selParent').remove('class');
	
	!opening && gap.reverse();
	$.timer(gap.length,animSec,
		function(i){
			$('.nav').css({'left':'-'+gap[i]+'px'});
		},
		function(){
			$('.nav').css({
				'left':'0px',
				'display':opening?'block':'none'
			});
			opening && sideButton('right');
			if(!opening){
				gap.reverse();
				$('#side').destroy();
				if(href != '') {
					$('#wlis').css('visibility','hidden');
					document.location = href;
				}
			}
		}
	);
	return true;
},
sideButton = function(side){
	$('body').append({
		'element':'div',
		'id':'side'
	});
	if(screenWidth > 768 || ($('#itemAction').length == 1 && $('#itemAction').val() == 'compose')) return;
	$('#side').css({
		'position':'absolute',
		'z-index':2,
		'left':side=='left'?0:(parseInt(window.innerWidth)-28)+'px',
		'top':'45px',
		'width':'14px',
		'height':(parseInt(window.innerHeight)-45)+'px'
	});
	$('#side').on('mousedown',function(){
		side=='left'?showDetail(!!0):showNav(!!0);
	});
},
shortIndicator = function(obj){
	$(obj).css('background-color',isLight?'#CBC8C9':'#3F4149');
	setTimeout(function(){
		$(obj).css('background-color','transparent');
	},300);
	return true;
},
makingChart = function(res){
	var svgWidth = 	$('#wlis').css('width')==screenWidth?screenWidth:$('#actForm').css('width');
	var source = res.map(function(arr){
		return {'label':arr.itemChartLabel,'number':arr.itemChartNumber}
	}),
	max = Math.max.apply(Math,source.map(function(o){return o.number;})),
	gap = Math.floor(svgWidth/source.length),
	x = 0, points = [], text = [];
	for(i in source){
		var y = 220-(Math.ceil((source[i]['number']*198)/max));
		points.push(x+','+y);
		text.push({
			'x':x,
			'y':y,
			'label':source[i]['label']
		});
		x = x + gap;
	}
	
	$('polyline').attr('points',points.join(' '));
	for(i in text){
		$('svg').appendSVGText({
			'x':text[i]['x']-1,
			'y':text[i]['y']-4,
			'text':text[i]['label']
		});

	}
	showChartLabel(0);
	chartCreated = true;
},
showChartLabel = function(n){
	$('text').css('visibility','hidden');
	$('text')[n].style.visibility = 'visible';
},
logFormEnter = function(e){
	if(e.key == 'Enter') logOpening(!!0,!0);
},
checker = function(o){
	$(o).val(o.checked?1:0);
},
boxchecking = function(){
	if($('.switch').length>0){
		$('.switch').css('display','none');
		$('.switch input').each(function(o){
			$(o)[0].checked = true;
		});
	}
}

document.body.onload = function(){
	app = $("#app").val();
	listCount = $('#wlis > dl').length;
	notEmpty = listCount<1?!!0:true;
	isPortrait = $('.act').css('width')<1;
	noRibbon = $('#ribbon').css('display')=='none'?true:!!0;
	searchFocus();

	if(notEmpty){
		selList[0] = $('#wlis > dl')[$('#selIndex').val()];
		selList[0] = selList[0] || $('#wlis > dl')[0];
		($('dl').length && response[app]) && !noRibbon && listSelected(selList[0]);
		if(document.cookie.indexOf('stream=on')>-1) setTimeout('streaming()',3000);
	}
	else if(!notEmpty && $('.row').length>0 && $('#wlis > dl').length<1){
		firstCompose();
	}	
	if(noRibbon){
		streaming();
		$('#view > .lis,.act').css('height','100%');
		$('.portrait').length < 1 && listSelected(selList[0]);
	}
	if(screenWidth < 1280){
		$('title').text($('.pad').text());
		mobileLink();
	}
};

document.onkeyup = function(e){
	shifted = (e||window.event).shiftKey?!0:!!0;
}
document.onkeydown = function(e){
	var is = (e||window.event);
	if(is.keyCode == 16) {
		shifted = true;
	}
	else if(is.keyCode == 27) { /* escape button */
		$('#loginPop').length > 0 && logOpening(!!0,!!0);
		$('#search').val('');
		searchFocus();
	}
	else if(is.keyCode == 46){ /* delete button */
		var searchText = $('#search').val();
		if(
			$(document.activeElement).attr('id') == 'search' &&
			$('#delete').length > 0 &&
			listCount > 0 &&
			deleteConfirm()
		){
			$('#itemAction').val('delete');
			submitting(true);
		}
		else{
			setTimeout(function(){
				$('#search').val(searchText);
				searchFocus();
			},100);
		}
	}
}
$('#search').on('keydown',function(e){
	if(selList.length < 1){ return; }
	var keyCode = (e.which||window.event.keyCode ), target, top;
	if(keyCode==40 || keyCode==38){
		var index = parseInt($(selList.slice(-1)[0]).attr('index'));
		if(keyCode==40 && listCount-1 > index) index++;
		else if(keyCode==38 && index > 0) index--;
		listSelected($('#wlis > dl')[index]);
		$('#selIndex').val(index);
	}
});
$('#search').on('keyup',function(e){
	window.scrollTo(0,0);
});
$('#wlis > dl').on('mousedown',function(){
	if(noRibbon) return !!0;
	$('#selIndex').val($(this).attr('index'));
	listSelected(this);
	
	screenWidth > 1024 && searchFocus();
	isPortrait && shortIndicator(this) && showDetail(true);
});
$('#wlis > dl').on('dblclick',function(){
	$('#actForm').css({'height':'50%'});
	pinning();
});
$('#pinAct > span').on('mousedown',function(){
	$('#actForm').css({'height':'100%'});
});

$('#menuButton').on('mousedown',function(){showNav(true)});

$('#header > img').on('mousedown',function(){showNav(!!0)});

$('#actions img').on('mousedown',function(){
	var action = $(this).attr('id');
	if(action=='backButton'){
		showDetail(!!0);
	}
	else if(action=='compose'){
		composing();
	}
	else{
		if((action=='update'||'compose')){
			var selCount = $('#actForm select').length, check = 0; 
			if(selCount > 0){
				$('#actForm select').each(function(sel){
					($(sel).val().length > 0) && check++;
				});
				if(check != selCount) return !!0;
			}
			check = 0; 
			if($('#actForm input').length > 0){
				$('#actForm input').each(function(inp){
					($(inp).val().length > 0) && check++;
				});
				if(check < 2) return !!0;
			}
		}
		if($('#itemAction').val() =='compose') {
			action = 'compose';
		}
		$('#itemAction').val(action);
		if(action=='delete' && !deleteConfirm()){
			searchFocus();
			return !!0;
		}
		$('#actions img').each(function(o){
			$(o).css('display','none');
		});
		submitting(true);
	}
});
$('.wrapper > input,select').on('blur',function(){
	window.scrollTo(0,0);
});
$('.wrapper > input').on('keydown',function(e){
	var id = $(this).attr('id'), tmp = [], found = 0;
	if((e.which||window.event.keyCode)!=13) return !!0;
	for(i in response[app][0]){
		if(i.substr(-3)!='_id') tmp.push(i);
	}
	found = tmp.indexOf(id)+1;
	if(found<tmp.length){
		$('#'+tmp[found])[0].focus();
	}
	else if($('#update').length>0){
		if($('#itemAction').val()=='') $('#itemAction').val('update');
		submitting(true);
	}
});
$('#mobileCompose').length == 1 && $('#mobileCompose').on('click',function(e){
	composing();
	showDetail(true);
});
