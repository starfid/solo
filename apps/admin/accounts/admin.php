<?php
	class Application implements Primary, Search {
		protected $sql;

		function primary(){
			$this->sql[] = "select
					id as itemKey,
					username as itemTitle,
					realname as itemInfo,
					username,
					realname
				from user
				limit 20
			";
			return($this);
		}
		function search($arg){
			$this->sql[] = "select
					id as itemKey,
					username as itemTitle,
					realname as itemInfo,
					username,
					realname
				from user where concat(username,pwd) = '".$arg."' or username = '".$arg."'
			";
			return($this);
		}
	}
?>