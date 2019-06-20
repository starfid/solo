<?php
	class Model extends Application {
		private $link;
		public $result;

		function __construct($database,$current,$token) {
			try {
				$this->link = new PDO($database['rdbms'].":hostname=".$database['host'].";dbname=".$database['name'].";port=".$database['port'],$database['username'],$database['password']);
				$this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch(PDOException $e) {
				echo $e->getMessage(); exit;
			}
			$currentMethod = $current['method'];
			$method = in_array($current['method'],array_map('strtolower',array_keys(class_implements($this))))?$this->$currentMethod($current['arg']):$this->init();
			isset($this->sql) && $method->query($current,$token);
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
							$this->result['columns'][$meta['name']] = "";
						}
						$this->result['columns']['itemTitle'] = ucwords($current['app']);
						$this->result['columns']['itemInfo'] = "New Entry";
					}
				}
				catch(PDOException $e) {
					$this->result['error'] = $e->getMessage();
				}
			}
			//print_r($this->result);
			unset($this->sql);
		}
		

	}