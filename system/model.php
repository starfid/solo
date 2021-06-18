<?php
	class Model extends Application {
		private $link;
		public $result;

		function __construct($database,$current,$token) {
			try {
				$this->link = new PDO($database['rdbms'].":hostname=".$database['host'].";charset=utf8;dbname=".$database['name'].";port=".$database['port'],$database['username'],$database['password']);
				$this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch(PDOException $e) {
				echo $e->getMessage(); exit;
			}
			$currentMethod = $current['method'];
			$availMethods = $current['methods'];

			if(!in_array('primary',$availMethods)) {
				echo 'Primary interface is required to be implemented in Application class'; exit();
			}

			$method = in_array($current['method'],$availMethods)?$this->$currentMethod($current['arg']):$this->primary();
			if(isset($this->sql)){
				$method->query($current,$token);
			}
			else{
				$this->result['error'] = 'sql variable is not declared.<br />No data available';
			}

			//run primary or search after post
			if(in_array($currentMethod,array('compose','update','delete')) && !isset($this->result['error'])){
				if(in_array('search',$availMethods) && isset($_GET['keyword']) && strlen($_GET['keyword'])>2){
					$this->search(stripslashes($_GET['keyword']))->query($current,$token);
				}
				else {
					$this->primary()->query($current,$token);
				}
			}
		}
		
		public function query($current,$token) {
			foreach($this->sql as $sql) {
				try {
					if(isset($_SESSION[$token]['auth'])){
						$sql = str_replace('{AUTH}',$_SESSION[$token]['auth']['username'],$sql);
					}
					$exec = $this->link->query($sql);
					if(substr(preg_replace("/\PL/u",'',strtolower(trim($sql))),0,6)=="select") {
						$this->result['match'] = $exec->fetchAll(PDO::FETCH_ASSOC);
						$this->result['count'] = $exec->rowCount();
						foreach(range(0, $exec->columnCount() - 1) as $column_index) {
							$meta = $exec->getColumnMeta($column_index);
							$this->result['columns'][] = $meta['name'];
						}
					}
				}
				catch(PDOException $e) {
					$this->result['error'] = $e->getMessage();
				}
			}
			unset($this->sql);
		}
		

	}
