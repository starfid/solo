<?php
	class View {
		private $auth, $apps, $cacheFolder, $meta, $minimizeHTML, $developing, $current, $data;

		function __construct($setting, $current, $data){
			$this->apps = $current['apps'];
			$this->meta = $setting['personal'];
			$this->auth = isset($_SESSION[$this->meta['token']]['auth'])?
				$_SESSION[$this->meta['token']]['auth']:
				array('type'=>'guest','username'=>'guest','ip'=>$_SERVER['REMOTE_ADDR']);

			$this->cacheFolder = $setting['folder']['cache'];
			$this->minimizeHTML = $setting['minimizeHTML'];
			$this->developing = $setting['developing'];
			$this->current = $current;
			$this->data = $data;
			$this->isNotError = isset($data['error'])?false:true;
			$this->isEmpty = isset($this->data['error']) || $this->data['count'] < 1?true:false;
			$format = isset($_GET['format'])?$_GET['format']:NULL;
			method_exists($this,$format)?$this->$format():$this->html();
		}

		function single() {
			return $this->html('single');
		}
		function dual() {
			return $this->html('dual');
		}
		

		function html($specialView = 'normal'){
			$random = $this->developing?"?r=".rand():"";
			$keyword = in_array('search',$this->current['methods']) && isset($_GET['keyword']) && strlen(trim($_GET['keyword'])) > 2?stripslashes(stripslashes(stripslashes(trim($_GET['keyword'])))):"";
			$keywordURL = empty($keyword)?'':'&keyword='.$keyword;
			$predefined = array('itemTitle','itemInfo','itemKey','itemRank','itemChartLabel','itemChartNumber','itemFlag');
			$settings = Array();
			$services = array();
			$scheme = isset($_COOKIE['scheme'])?$_COOKIE['scheme']:'dark';
			$stream = isset($_COOKIE['stream'])?$_COOKIE['stream']:'off';

			if(!$this->isEmpty){
				$noTitle = !isset($this->data['match'][0]['itemTitle'])?true:false;
				$noInfo = !isset($this->data['match'][0]['itemInfo'])?true:false;
				$noKey = !isset($this->data['match'][0]['itemKey'])?true:false;
			}
			$selIndex =	0;

			$title = $this->nameFormat($this->current['app'])." ".$this->nameFormat($this->current['group']).", ".ucwords($this->meta['label']);
			$currentURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$searchAble = in_array('search',$this->current['methods']);
			$disableSearch = $searchAble?'':'disabled ';

			$s = "";
			$s .= "<html lang='en'>";
			$s .= "\n\t<head>";
			$s .= "\n\t\t<title>".$title."</title>";
			$s .= "\n\t\t<link rel=\"apple-touch-icon\" href=\"".$this->cacheFolder."/normal.png\" />";
			$s .= "\n\t\t<link rel=\"apple-touch-icon\" sizes=\"76x76\" href=\"".$this->cacheFolder."/76.png\" />";
			$s .= "\n\t\t<link rel=\"apple-touch-icon\" sizes=\"120x120\" href=\"".$this->cacheFolder."/120.png\" />";
			$s .= "\n\t\t<link rel=\"apple-touch-icon\" sizes=\"152x152\" href=\"".$this->cacheFolder."/152.png\" />";
			$s .= "\n\t\t<link rel=\"apple-touch-startup-image\" href=\"".$this->cacheFolder."/normal.png\" />";
			$s .= "\n\t\t<link href=\"".$this->cacheFolder."/favc.ico\" rel=\"icon\" type=\"image/x-icon\" />";
			$s .= "\n\t\t<link href=\"".$this->cacheFolder."/style.css".$random."\" rel=\"stylesheet\" type=\"text/css\" />";

			$s .= "\n\t\t<meta name=\"description\" content=\"".$this->meta['desc']."\" />";
			$s .= "\n\t\t<meta name=\"keyword\" content=\"".$this->meta['keyword']."\" />";
			$s .= "\n\t\t<meta name=\"apple-mobile-web-app-capable\" content=\"yes\" />";

			$s .= "\n\t\t<meta name=\"apple-mobile-web-app-status-bar-style\" content=\"default\" />";

			$s .= "\n\t\t<meta name=\"viewport\" content=\"user-scalable=no, width=device-width\" />";
			$s .= "\n\t\t<meta name=\"viewport\" content=\"minimum-scale=1.0,width=device-width,maximum-scale=1,user-scalable=no\" />";
			$s .= "\n\t\t<meta name=\"google\" content=\"notranslate\" />";
			$s .= "\n\t\t<meta name=\"google\" value=\"notranslate\" />";

			$s .= "\n\t\t<meta name=\"theme-color\" content=\"#CECCC7\" id=\"systrayCol\" />";
			$s .= "\n\t\t<meta name=\"format-detection\" content=\"telephone=no\">";
			$s .= "\n\t\t<meta content=\"text/html;charset=UTF-8\" http-equiv=\"Content-Type\" />";
			$s .= "\n\t\t<meta property=\"og:url\" content=\"".$currentURL."/\" />";
			$s .= "\n\t\t<meta property=\"og:type\" content=\"article\" />";
			$s .= "\n\t\t<meta property=\"og:title\" content=\"".$title."\" />";
			$s .= "\n\t\t<meta property=\"og:description\" content=\"".$this->meta['desc']."\" />";
			$s .= "\n\t\t<meta property=\"og:image\" content=\"preview.jpg\" itemprop=\"image\" />";
			$s .= "\n\t</head>";
			$s .= "\n\t<body>";

			$showRibbon = $specialView!='normal'?" style='display:none'":"";

			$s .= "\n\t\t<div id=\"ribbon\" class=\"landing\"".$showRibbon.">";
			$s .= "\n\t\t\t<div class=\"right act\"><div class=\"actw both\">";
			$s .= "\n\t\t\t\t<div class=\"left\" id=\"actions\">";

			$s .= "\n\t\t\t\t\t<img id=\"backButton\" class=\"left\" src=\"".$this->cacheFolder."/back.png\" />";

			if($this->isNotError){
				
				$s .= "\n\t\t\t\t<span id=\"charCount\"></span>";

				asort($this->current['methods']);
				foreach(array_diff($this->current['methods'],array('primary','search')) as $methods => $method){
					if(!($this->isEmpty && $method != 'update')){
						$s .= "\n\t\t\t\t\t<img title=\"".ucfirst(str_replace('update','save',$method))."\" id=\"".$method."\" class=\"left\" src=\"".$this->cacheFolder."/".$method.".png\" />";
					}
				}
				if(in_array('compose',$this->current['methods']) && !in_array('update',$this->current['methods'])){
					$s .= "\n\t\t\t\t\t<img title=\"save\" id=\"save\" class=\"left\" src=\"".$this->cacheFolder."/save.png\" />";
				}
				if(!$this->isEmpty && count(array_diff($this->data['columns'],$predefined))==1){
					$s .= '';
				}
			}


			$s .= "\n\t\t\t\t</div>";

			if($this->auth['type']=='guest'){
				$s .= "\n\t\t\t\t<div class=\"right\" id=\"userid\" onclick=\"logForm()\">";
			}
			else {
				$s .= "\n\t\t\t\t<div class=\"right\" id=\"userid\" onclick=\"document.location='?log=out'\">";
			}

			$label = $this->auth['username']=="guest"?"Sign In":"Sign Out";
			$s .= "\n\t\t\t\t\t<strong class=\"left\">".$label."</strong>";

			$s .= "\n\t\t\t\t</div>";
			$s .= "\n\t\t\t</div></div>";
			$s .= "\n\t\t\t<div class=\"left nav\">";

			$s .= "\n\t\t\t<div id='header'>";
			$s .= "\n\t\t\t\t<div class='left pad'>".strtoupper($this->meta['label'])."</div>";
			$s .= "\n\t\t\t\t<img class='right' src=\"".$this->cacheFolder."/back.png\" />";
			$s .= "\n\t\t\t</div>";
			
//			$s .= "\n\t\t\t\t<strong class=\"pad\">".strtoupper($this->meta['label'])."</strong>";
			
			$s .= "\n\t\t\t</div>";
			$s .= "\n\t\t\t<div class=\"right lis\">";
			$s .= "\n\t\t\t\t<form method=\"search\" action=\"?group=".$this->current['group']."&app=".$this->current['app']."".$keywordURL."\" class=\"both\">";
			
			if(in_array('search',$this->current['methods'])){
				$s .= "\n\t\t\t\t\t<img id=\"magnify\" class=\"left\" src=\"".$this->cacheFolder."/magnify.png\" />";
			}

			$s .= "\n\t\t\t\t\t<input value=\"".$this->current['group']."\" name=\"group\" id=\"group\" type=\"hidden\" />";
			$s .= "\n\t\t\t\t\t<input value=\"".$this->current['app']."\" name=\"app\" id=\"app\" type=\"hidden\" />";

			if(in_array('compose',$this->current['methods'])){
				$s .= "\n\t\t\t\t\t<img title=\"Compose\" id=\"mobileCompose\" class=\"right\" src=\"".$this->cacheFolder."/mobilecompose.png\" />";
			}

			$s .= "\n\t\t\t\t\t<img class='right' src=\"".$this->cacheFolder."/menu.png\" id=\"menuButton\" />";
			if(in_array('search',$this->current['methods'])){
				$s .= "\n\t\t\t\t\t<input value=\"".$keyword."\" name=\"keyword\" id=\"search\" class=\"left\" placeholder=\"Search Here\" ondblclick=\"this.value=''\" autocomplete=\"off\" autocorrect=\"off\" spellcheck=\"false\" autocapitalize=\"off\" ".$disableSearch."/>";
			}
			
			$s .= "\n\t\t\t\t</form>";
			$s .= "\n\t\t\t</div>";
			$s .= "\n\t\t</div>";

			//ribbon end here

			$singleView = $specialView=='single'?" tv":"";

			$s .= "\n\t\t<div id=\"view\" class=\"both".$singleView."\">";
			$s .= "\n\t\t\t<div class=\"right act\"><div class=\"actw both\"><div id=\"multiple\"><div id=\"shell\">";

			$s .= "\n\t\t\t\t<form method=\"post\" action=\"?group=".$this->current['group']."&app=".$this->current['app']."".$keywordURL."\" id=\"actForm\" class=\"scroll\">";

			if(isset($this->data['columns'])) {
				foreach($this->data['columns'] as $column){
					if(substr($column,-3)=='_id') $services[] = $column;
					if(!in_array($column,$predefined)){
						$s .= "\n\t\t\t\t\t<div class=\"row\">";
						$s .= "\n\t\t\t\t\t\t<div class=\"label left\">".ucwords(str_replace(array('_id','_bool','_'),array('','',' '),$column))."</div>";
						$s .= "\n\t\t\t\t\t\t<div class=\"wrapper\">";

						if(substr($column,-3)=='_id'){
							$s .= "\n\t\t\t\t\t\t\t<select id=\"".$column."\" name=\"".$column."\"></select>";
						}
						elseif(substr($column,-5)=='_bool'){
							$s .= "\n\t\t\t\t\t\t\t<label class=\"switch\"><input onclick=\"checker(this)\" type=\"checkbox\" id=\"".$column."\" name=\"".$column."\" value=\"1\" checked /><span class=\"slider round\"></span></label>";
						}
						else{
							$type = substr($column,-5)=='_date'?'date':'text';
							$s .= "\n\t\t\t\t\t\t\t<input type=\"".$type."\" class=\"actInput\" id=\"".$column."\" name=\"".$column."\" value=\"\" autocomplete=\"off\" autocorrect=\"off\" spellcheck=\"false\" />";
						}

						$s .= "\n\t\t\t\t\t\t</div>";
						$s .= "\n\t\t\t\t\t</div>";						
					}
				}

				if(in_array('itemChartLabel',$this->data['columns']) && in_array('itemChartNumber',$this->data['columns']) ){
					$s .= "\n\t\t\t\t\t<svg width=\"100%\" height=\"100%\">\n\t\t\t\t\t\t<polyline points=\"\" />\n\t\t\t\t\t</svg>";
				}
			
				$s .= "\n\t\t\t\t\t<input type=\"hidden\" name=\"itemAction\" value=\"\" id=\"itemAction\" />";
				$s .= "\n\t\t\t\t\t<input type=\"hidden\" name=\"selIndex\" value=\"".$this->current['selIndex']."\" id=\"selIndex\" />";
				if(in_array('search',$this->current['methods'])){
					$s .= "\n\t\t\t\t\t<input type=\"hidden\" name=\"itemSearch\" id=\"itemSearch\" value=\"".$keyword."\" />";
				}

			}

			$s .= "\n\t\t\t\t\t<div class=\"waste\"></div>";
			$s .= "\n\t\t\t\t</form>";
			$s .= "\n\t\t\t</div></div>";

			$s .= "\n\t\t\t\t<div id='plis'>";
			$s .= "\n\t\t\t\t\t<div id='pinAct'><span>Close</span></div>";
			$s .= "\n\t\t\t\t\t<div id='pinned' class='scroll'></div>";
			$s .= "\n\t\t\t\t</div>";


			$s .= "\n\t\t\t</div></div>";
			$s .= "\n\t\t\t<div class=\"left nav\">";
			$s .= "\n\t\t\t\t<ul class=\"scroll\" id=\"wnav\">";

			foreach($this->apps as $types => $type){
				if($types == 'services') continue;
				ksort($type);
				foreach($type as $groups => $group){
					$s .= "\n\t\t\t\t<li class=\"group\">".$this->nameFormat($groups)."</li>";
					asort($group);
					foreach($group as $app){
						$selected = ($groups == $this->current['group'] && $app == $this->current['app'])?' id="navSelected"':'';
						$s .= "\n\t\t\t\t\t<li".$selected."><a class='links' href=\"?group=".$groups."&amp;app=".$app."\">".$this->nameFormat($app)."</a></li>";
					}
				}
			}


			$stream = $stream=='off'?'on':'off';
			$s .= "\n\t\t\t\t<li class='group'>Options</li>";
			$s .= "\n\t\t\t\t\t<li><a class='options' title='Set data refresh automatically on-off' onmousedown='setStream()' href='".$currentURL."'>Stream ".ucwords($stream)."</a></li>";

			if($this->auth['type']=='guest'){
				$s .= "\n\t\t\t\t\t<li class='options' id='navlogin' onmousedown=\"logForm()\"><a href=\"#\">Login</a></li>";
			}
			else {
				$s .= "\n\t\t\t\t\t<li class='options' id='navlogin'><a href=\"?log=out\">Logout</a></li>";
			}
						
			$s .= "\n\t\t\t\t\t<li class=\"waste\"></li>";
			$s .= "\n\t\t\t\t</ul>";
			$s .= "\n\t\t\t</div>";
			$s .= "\n\t\t\t<div class=\"right lis\">";
			$s .= "\n\t\t\t\t<div id=\"wlis\" class=\"scroll noSelectText\">";

			$problem = "\n\t\t\t\t<div id='problem'>";
			$report = "</div>";

			if($this->isEmpty && isset($_GET['keyword']) && in_array('search',$this->current['methods'])) {
				$error = isset($this->data['error'])?$this->data['error']:'No results were found.<br />Try different keyword';
				$s .= $problem.$error.$report;
			}
			elseif($this->isEmpty) {
				$error = isset($this->data['error'])?$this->data['error']:'No data available';
				$s .= $problem.$error.$report;
			}
			elseif($noTitle) {
				$error = 'Data does not contain itemTitle';
				$s .= $problem.$error.$report;
			}
			elseif($noKey) {
				$error = 'Data does not contain itemKey';
				$s .= $problem.$error.$report;
			}
			else {
				for($i=0;$i<$this->data['count'];$i++) {

					$flag = array_key_exists('itemFlag',$this->data['match'][$i]) && $this->data['match'][$i]['itemFlag'] == 'true'?"class='flag'":"";

					$s .= "\n\t\t\t\t\t<dl index=\"".$i."\" ".$flag." data-key=\"".$this->data['match'][$i]['itemKey']."\">";

					if(empty(trim($this->data['match'][$i]['itemTitle']))) $this->data['match'][$i]['itemTitle'] = "Title is not provided";
					$s .= "\n\t\t\t\t\t\t<dt>".$this->highlight($this->data['match'][$i]['itemTitle'])."</dt>";
					
					if(!array_key_exists('itemInfo',$this->data['match'][$i]) || empty(trim($this->data['match'][$i]['itemInfo']))) $this->data['match'][$i]['itemInfo'] = "No description";
					$s .= "\n\t\t\t\t\t\t<dd>".$this->highlight($this->data['match'][$i]['itemInfo'])."</dd>";
					
					$s .= "\n\t\t\t\t\t</dl>";
				}
			}

			$s .= "\n\t\t\t\t\t<div class=\"waste\"></div>";
			$s .= "\n\t\t\t\t</div>";
			$s .= "\n\t\t\t</div>";
			$s .= "\n\t\t</div>";
			//$s .= "\n\t</body></html>";

			if(isset($this->data['match'])) {
				$s .= "<script type='text/javascript' id='response'>";
				$s .= "var response=[[]];response['".$this->current['app']."']=".json_encode($this->data['match']).";";
				$s .= "</script>";
				
				if(count($services)>0 && isset($this->apps['services']['services'])){
					foreach($services as $service => $app){
						if(in_array(substr($app,0,-3),$this->apps['services']['services'])){
							$s .= "<script type='text/javascript' src=\"?group=services&app=".substr($app,0,-3)."&format=json\"></script>";
						}
					}
				}

			}

			$s .= "<script type='text/javascript' src='".$this->cacheFolder."/script.js".$random."'></script>";
			$s = $this->minimizeHTML?preg_replace('/[\r\n|\n|\t]+/', '', $s):$s;
			echo $s;
		}
		function xml() {
			header("Content-type: text/xml");
			$s = "<?xml version=\"1.0\" encoding=\"UTF-8\"?".">";
			$s .= "\n<rows>";
			foreach($this->data as $rows => $row){
				$s .= "\n\t<row>";
				foreach($row as $column => $data) {
					$s .= "\n\t\t<".$column.">".htmlentities($data)."</".$column.">";
				}
				$s .= "\n\t</row>";
			}
			$s .= "\n</rows>";
			echo preg_replace('/[\r\n|\n|\t]+/', '', $tmp);
			//echo $s;
		}
		function json() {
			header('Content-Type: application/javascript');
			$s = "response['".$this->current['app']."'] = ";
			$s .= $this->isEmpty?"[];":json_encode($this->data['match']).";";
			echo $s;
		}
		function serial() {
			echo(serialize($this->data['match']));
		}
		function raw() {
			echo("<pre>".print_r($this->data['match'],true)."</pre>");
		}

		function highlight($string) {			
			if(isset($_GET['keyword']) && strlen($_GET['keyword'])>2){
				$words = array_unique(preg_split("/[-,:'. ]/", $_GET['keyword'], null, PREG_SPLIT_NO_EMPTY));
				foreach($words as $word) {
					if(strlen($word)<3) continue;
					$word = str_replace("/","",$word);
					$string = preg_replace("/(".preg_quote($word).")/is","<span>$1</span>",$string);
				}
			}
			return stripslashes($string);
		}

		function link() {
			$group = "group=".$this->data[0]['groupLink'];
			$app = "&app=".$this->data[0]['appLink'];
			$search = isset($this->data[0]['searchLink'])?"&search=".urlencode($this->data[0]['searchLink']):"";
			header("Location: ?".$group.$app.$search);		
		}

		function nameFormat($str){
			$str = preg_replace("/^\d{1}_/is","",$str);
			$str = str_replace("_"," ",$str);
			$str = strtolower($str);
			$str = ucwords($str);
			return $str;
		}

	}
