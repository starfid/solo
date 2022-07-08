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
			
			/*Remove this anytime*/
			$this->sql[] = "create table if not exists user(
				id int(9) not null auto_increment primary key,
				entry datetime not null,
				realname varchar(255) not null,
				username varchar(255) not null,
				pwd varchar(255) not null
			)";
			$this->sql[] = "insert into user(entry,realname,username,pwd)
				select
					now(),
					'Administrator',
					'admin',
					'".md5('admin')."'
				where not exists (select * from user)";
			/*******************/
			
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
