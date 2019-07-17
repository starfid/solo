<?php	

	class Controller {
		private $token, $appFolder, $priviledge;
		public $loginSubmitted, $current = array();

		function __construct($setting){
			$this->appFolder = $setting['folder']['apps'];
			$this->token = $setting['personal']['token'];
			$this->priviledge = $setting['priv'];
			$this->expiration($setting['personal']['expired']);
			!isset($_SESSION[$this->token]['apps']) && $this->getApps();
			$this->pathCheck();
			$this->loginSubmitted = 
				isset($_POST['username']) && !empty($_POST['username']) && strlen($_POST['username']) > 2 &&
				isset($_POST['password']) && !empty($_POST['password']) && strlen($_POST['username']) > 2 &&
				isset($_POST['type']) && !empty($_POST['type']) && in_array($_POST['type'],array_keys($this->priviledge))
				?True:False;
		}

		function authentication($result){
			if($result['count'] == 1){
				$_SESSION[$this->token]['auth'] = array(
					'type' => $_POST['type'],
					'username' => $_POST['username'],
					'ip' => $_SERVER['REMOTE_ADDR'],
					'start' => time()
				);
			}
			header('Location: ./');
		}

		function setApps($session){
			$user = isset($session['auth'])?$session['auth']['type']:'guest';
			$temp = array();
			foreach($this->priviledge[$user] as $type => $types){
				if(array_key_exists($types,$this->current['apps'])){
					$temp[$types] = $this->current['apps'][$types];
				}
			}
			$this->current['apps'] = $temp;
		}

		function currentApp(){
			$session = $_SESSION[$this->token];
			$this->current['apps'] = $session['apps'];
			$this->setApps($session);

			$type = isset($session['auth'])?$session['auth']['type']:'guest';
			$folder = $session['apps'][$type];
			$group = key($folder);
			$app = min($folder[$group]);
			$method = 'primary';

			$this->current['type'] = $type;
			$this->current['appType'] = $type;
			$this->current['group'] = $group;
			$this->current['app'] = $app;
			$this->current['method'] = $method;
			$this->current['arg'] = '';

			if(isset($_GET['group'])){
				foreach($this->current['apps'] as $type => $types){
					foreach($types as $group => $apps) {
				  		if($_GET['group'] == $group){
				  			$this->current['appType'] = $type;
							$this->current['group'] = $group;
							$this->current['app'] = isset($_GET['app']) && in_array($_GET['app'],$apps)?$_GET['app']:min($apps);
							break;
						}
				  	}
				}
			}

			if(isset($_POST['itemAction']) && in_array($_POST['itemAction'],array('delete','update','compose'))){
				$this->current['method'] = $_POST['itemAction'];
				$this->current['arg'] = $_POST;
			}
			elseif(
				isset($_GET['keyword']) && !empty($_GET['keyword']) &&
				((!is_numeric($_GET['keyword']) && strlen($_GET['keyword']) > 2) || is_numeric($_GET['keyword']))
			) {
				$this->current['method'] = 'search';
				$this->current['arg'] = addslashes($_GET['keyword']);
			}
			elseif($this->loginSubmitted){
				$this->current = array(
					'type' => 'admin',
					'appType' => 'admin',
					'group' => 'user',
					'app' => $_POST['type'],
					'method' => 'search',
					'arg' => $_POST['username'].md5($_POST['password'])
				);
			}
		}

		function pathCheck(){
			if(!isset($_SESSION[$this->token]['apps']['admin']['user']) || !in_array('admin',$_SESSION[$this->token]['apps']['admin']['user'])) {
				echo 'admin/user/admin.php in application folder is not found'; exit();
			}
			elseif(!isset($_SESSION[$this->token]['apps']['guest'])){
				echo 'guest is not found in application folder'; exit();
			}
			elseif(isset($_SESSION[$this->token]['auth']['type']) && !array_key_exists($_SESSION[$this->token]['auth']['type'],$_SESSION[$this->token]['apps'])){
				echo $_SESSION[$this->token]['auth']['type'].' is not found in application folder'; exit();
			}

		}

		function expiration($minute){
			if(
				isset($_SESSION[$this->token]['auth']) &&
				(time() - $_SESSION[$this->token]['auth']['start'])/60 > $minute
			){
				unset($_SESSION[$this->token]);
				header('Location: ./');
			}
		}

		function getApps() {
			$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->appFolder.'/'), RecursiveIteratorIterator::CHILD_FIRST);
			$result = array();
			foreach ($rii as $splFileInfo) {
				$fileName = $splFileInfo->getFilename();
				$path = $splFileInfo->isDir()?array($fileName => array()):array(substr($fileName,0,-4));
				if(in_array($fileName,array('.','..'))) continue;
				for($depth = $rii->getDepth() - 1; $depth >= 0; $depth--) {
					$path = array($rii->getSubIterator($depth)->current()->getFilename() => $path);
				}
				$result = array_merge_recursive($result,$path);
			}
			$_SESSION[$this->token]['apps'] = $result;
		}

	}