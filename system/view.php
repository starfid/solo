<?php
	class View {
		private $auth, $apps, $cacheFolder, $meta, $current, $data;

		function __construct($setting, $current, $data){
			$this->apps = $current['apps'];
			$this->auth = isset($_SESSION[$setting['token']]['auth'])?
				$_SESSION[$setting['token']]['auth']:
				array('type'=>'guest','username'=>'guest','ip'=>$_SERVER['REMOTE_ADDR']);

			$this->meta = $setting;
			$this->cacheFolder = $setting['cache'];
			$this->current = $current;
			$this->data = $data;
			$format = isset($_GET['format'])?$_GET['format']:NULL;
			method_exists($this,$format)?$this->$format():$this->html();
		}

		function html(){
			$keyword = isset($_GET['keyword']) && strlen(trim($_GET['keyword'])) > 2?stripslashes(stripslashes(stripslashes(trim($_GET['keyword'])))):"";
			$predefined = array('itemTitle','itemInfo','itemKey','itemRank','itemChartLabel','itemChartNumber');
			$settings = Array();
			$selIndex =	isset($_POST['selIndex']) &&
						is_numeric($_POST['selIndex']) &&
						$this->notEmpty &&
						$_POST['selIndex'] < $this->data['count']?
						$_POST['selIndex']:0;
			$services = array();
			$scheme = isset($_COOKIE['scheme'])?$_COOKIE['scheme']:'dark';
			$stream = isset($_COOKIE['stream'])?$_COOKIE['stream']:'off';

			$s = "<html>";
			$s .= "\n\t<head>";
			$s .= "\n\t\t<title>".ucwords(str_replace('_',' ',$this->current['app']))." ".ucwords($this->current['group']).", ".strtoupper($this->meta['label'])."</title>";
			$s .= "\n\t\t<link rel=\"apple-touch-icon\" href=\"".$this->cacheFolder."/normal.png\" />";
			$s .= "\n\t\t<link rel=\"apple-touch-icon\" sizes=\"76x76\" href=\"".$this->cacheFolder."/76.png\" />";
			$s .= "\n\t\t<link rel=\"apple-touch-icon\" sizes=\"120x120\" href=\"".$this->cacheFolder."/120.png\" />";
			$s .= "\n\t\t<link rel=\"apple-touch-icon\" sizes=\"152x152\" href=\"".$this->cacheFolder."/152.png\" />";
			$s .= "\n\t\t<link rel=\"apple-touch-startup-image\" href=\"".$this->cacheFolder."/normal.png\" />";
			$s .= "\n\t\t<link href=\"".$this->cacheFolder."/favc.ico\" rel=\"icon\" type=\"image/x-icon\" />";
			$s .= "\n\t\t<link href=\"".$this->cacheFolder."/style.css\" rel=\"stylesheet\" type=\"text/css\" />";
			$s .= "\n\t\t<link href=\"".$this->cacheFolder."/".$scheme.".css\" rel=\"stylesheet\" type=\"text/css\" />";
			$s .= "\n\t\t<meta name=\"description\" content=\"".$this->meta['desc']."\" />";
			$s .= "\n\t\t<meta name=\"keyword\" content=\"".$this->meta['keyword']."\" />";
			$s .= "\n\t\t<meta name=\"apple-mobile-web-app-capable\" content=\"yes\" />";
			$s .= "\n\t\t<meta name=\"viewport\" content=\"user-scalable=no, width=device-width\" />";
			$s .= "\n\t\t<meta name=\"viewport\" content=\"minimum-scale=1.0,width=device-width,maximum-scale=1,user-scalable=no\" />";
			$s .= "\n\t\t<meta name=\"google\" content=\"notranslate\" />";
			$s .= "\n\t\t<meta name=\"google\" value=\"notranslate\" />";
			$s .= "\n\t\t<meta name=\"format-detection\" content=\"telephone=no\">";
			$s .= "\n\t</head>";
			$s .= "\n\t<body>";
			$s .= "\n\t\t<div id=\"ribbon\" class=\"both\">";
			$s .= "\n\t\t\t<div class=\"right act\"><div class=\"actw both\">";
			$s .= "\n\t\t\t\t<div class=\"left\" id=\"actions\">";
			$s .= "\n\t\t\t\t\t<img id=\"backButton\" class=\"left\" src=\"".$this->cacheFolder."/back.png\" />";
			$s .= "\n\t\t\t\t</div>";
			$s .= "\n\t\t\t\t<div class=\"right\" id=\"userid\" onmousedown=\"document.location='login/?log=out'\">";

			$s .= "\n\t\t\t\t\t<strong class=\"left\">".ucwords($this->auth['username'])."</strong>";

			$s .= "\n\t\t\t\t</div>";
			$s .= "\n\t\t\t</div></div>";
			$s .= "\n\t\t\t<div class=\"left nav\">";
			$s .= "\n\t\t\t\t<strong class=\"pad\">".strtoupper($this->meta['label'])."</strong>";
			$s .= "\n\t\t\t</div>";
			$s .= "\n\t\t\t<div class=\"right lis\">";
			$s .= "\n\t\t\t\t<form method=\"search\" action=\"?group=".$this->current['group']."&app=".$this->current['app']."\" class=\"both\">";
			$s .= "\n\t\t\t\t\t<img id=\"magnify\" class=\"left\" src=\"".$this->cacheFolder."/magnify.png\" />";

			$s .= "\n\t\t\t\t\t<input value=\"".$this->current['group']."\" name=\"group\" id=\"group\" type=\"hidden\" />";
			$s .= "\n\t\t\t\t\t<input value=\"".$this->current['app']."\" name=\"app\" id=\"app\" type=\"hidden\" />";
			$s .= "\n\t\t\t\t\t<input value=\"".$keyword."\" name=\"keyword\" id=\"search\" class=\"left\" placeholder=\"Search Here\" ondblclick=\"this.value=''\" autocomplete=\"off\" autocorrect=\"off\" spellcheck=\"false\" autocapitalize=\"off\" />";
			$s .= "\n\t\t\t\t\t<img class='right' src=\"".$this->cacheFolder."/menu.png\" id=\"menuButton\" />";
			$s .= "\n\t\t\t\t</form>";
			$s .= "\n\t\t\t</div>";
			$s .= "\n\t\t</div>";
			$s .= "\n\t\t<div id=\"view\" class=\"both\">";
			$s .= "\n\t\t\t<div class=\"right act\"><div class=\"actw both\"><div id=\"multiple\"><div id=\"shell\">";

			$s .= "\n\t\t\t\t<form method=\"post\" action=\"?group=".$this->current['group']."&app=".$this->current['app']."\" id=\"actForm\" class=\"scroll\">";

			if(isset($this->data['match']) && $this->data['count'] > 0) {

				foreach(array_keys($this->data['match'][0]) as $columns => $column){
					if(substr($column,-3)=='_id') $services[] = $column;
				}
				foreach($this->data['match'][$selIndex] as $column => $value){
					if(!in_array($column,$predefined)){
						$s .= "\n\t\t\t\t\t<div class=\"row\">";
						$s .= "\n\t\t\t\t\t\t<div class=\"label left\">".ucwords(str_replace(array('_id','_'),array('',' '),$column))."</div>";
						$s .= "\n\t\t\t\t\t\t<div class=\"wrapper\">";

						if(substr($column,-3)=='_id'){
							$s .= "\n\t\t\t\t\t\t\t<select id=\"".$column."\" name=\"".$column."\"></select>";
						}
						else{
							$s .= "\n\t\t\t\t\t\t\t<input type=\"text\" class=\"actInput\" id=\"".$column."\" name=\"".$column."\" value=\"".$value."\" autocomplete=\"off\" autocorrect=\"off\" spellcheck=\"false\" />";
						}

						$s .= "\n\t\t\t\t\t\t</div>";
						$s .= "\n\t\t\t\t\t</div>";
					}
				}

//				$s .= "\n\t\t\t\t\t<input type=\"hidden\" name=\"itemKey[]\" value=\"".$this->data['match'][0]['itemKey']."\" id=\"itemKey\" />";
				$s .= "\n\t\t\t\t\t<input type=\"hidden\" name=\"itemAction\" value=\"\" id=\"listAction\" />";
				$s .= "\n\t\t\t\t\t<input type=\"hidden\" name=\"selIndex\" value=\"0\" id=\"selIndex\" />";
			}

			$s .= "\n\t\t\t\t\t<div class=\"waste\"></div>";
			$s .= "\n\t\t\t\t</form>";
			$s .= "\n\t\t\t</div></div></div></div>";
			$s .= "\n\t\t\t<div class=\"left nav\">";
			$s .= "\n\t\t\t\t<ul class=\"scroll\" id=\"wnav\">";

			foreach($this->apps as $types => $type){
				if($types == 'services') continue;
				foreach($type as $groups => $group){
					$s .= "\n\t\t\t\t<li class=\"group\">".ucwords($groups)."</li>";
					foreach($group as $apps => $app){
						$selected = ($groups == $this->current['group'] && $app == $this->current['app'])?' id="navSelected"':'';
						$s .= "\n\t\t\t\t\t<li".$selected."><a href=\"?group=".$groups."&amp;app=".$app."\">".ucwords(str_replace("_"," ",$app))."</a></li>";
					}
				}
			}

			$scheme = $scheme=='dark'?'light':'dark';
			$stream = $stream=='off'?'on':'off';
			$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$s .= "\n\t\t\t\t<li class='group'>Options</li>";
			$s .= "\n\t\t\t\t\t<li><a title='Set background dimmer or lighter' onmousedown='setScheme()' href='".$url."'>Turn ".ucwords($scheme)."</a></li>";
			$s .= "\n\t\t\t\t\t<li><a title='Set data refresh automatically on-off' onmousedown='setStream()' href='".$url."'>Set Stream ".ucwords($stream)."</a></li>";
			$s .= "\n\t\t\t\t\t<li id='navlogin'><a href='login'>".($this->auth['username']=='guest'?'Login':'Log Out')."</a></li>";


			$s .= "\n\t\t\t\t\t<li class=\"waste\"></li>";
			$s .= "\n\t\t\t\t</ul>";
			$s .= "\n\t\t\t</div>";
			$s .= "\n\t\t\t<div class=\"right lis\">";
			$s .= "\n\t\t\t\t<div id=\"wlis\" class=\"scroll noSelectText\">";


			$report = "<br /><br /><a id='report' href='?'>Report</a></div>";
			$problem = "\n\t\t\t\t<div id='problem'>";
			if(isset($this->data['error']) || $this->data['count'] < 1) {
				$error = isset($this->data['error'])?$this->data['error']:'No data available';
				$s .= $problem.$error.$report;
			}
			elseif(!isset($this->data['match'][0]['itemTitle'])) {
				$error = 'Data does not contain itemTitle';
				$s .= $problem.$error.$report;
			}
			elseif(!isset($this->data['match'][0]['itemInfo'])) {
				$error = 'Data does not contain itemInfo';
				$s .= $problem.$error.$report;
			}
			elseif(!isset($this->data['match'][0]['itemKey'])) {
				$error = 'Data does not contain itemKey';
				$s .= $problem.$error.$report;
			}
			else {
				for($i=0;$i<$this->data['count'];$i++) {
					$selParent = $i==$selIndex?" class=\"selParent\"":"";
					$s .= "\n\t\t\t\t\t<dl index=\"".$i."\"".$selParent.">";
					$s .= "\n\t\t\t\t\t\t<dt>".$this->highlight($this->data['match'][$i]['itemTitle'])."</dt>";
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

			$s .= "<script type='text/javascript' src='".$this->cacheFolder."/script.js'></script>";
			//echo preg_replace('/[\r\n|\n|\t]+/', '', $s);
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
			//echo(preg_replace('/[\r\n|\n|\t]+/', '', $tmp));
			echo($s);
		}
		function json() {
			header('Content-Type: application/javascript');
			//echo('var '.$this->current['app'].' = '.json_encode($this->data['match']));

			echo "response['".$this->current['app']."'] = ".json_encode($this->data['match']).";";
		}
		function serial() {
			echo(serialize($this->data['match']));
		}
		function raw() {
			echo("<pre>".print_r($this->data['match'],true)."</pre>");
		}

		function highlight($string) {
			if(isset($this->current['arg'])) {
				$words = array_unique(preg_split("/[-,:'. ]/", $this->current['arg'], null, PREG_SPLIT_NO_EMPTY));
				foreach($words as $word) {
					if(strlen($word)<3) continue;
					$word = str_replace("/","",$word);
					$string = preg_replace("/(".preg_quote($word).")/is","<span>$1</span>",$string);
				}
			}
			return stripslashes($string);
		}

	}