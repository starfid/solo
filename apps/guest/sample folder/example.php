<?php
	class Application implements Primary, Search  {
		protected $sql;

		function primary(){

			/*Remove this anytime*/
			$this->sql[] = "create table if not exists contact(
				id int(9) not null auto_increment primary key,
				name varchar(255) not null,
				address varchar(255) not null
			)";
			$this->sql[] = "insert into contact(name,address)
				select * from (
					(select 'Steve Jobs', 'One Infinite Loop, Cupertino') union
					(select 'Mark Zuckerberg', 'One Hacker Way, Menlo Park') union
					(select 'Bill Gates', ' One Microsoft Way, Redmond') union
					(select 'Elon Musk', 'One Tesla Road, Austin')
				) as samples
				where not exists (select * from contact)";
			/*******************/

			$this->sql[] = "select
					id as itemKey,
					name as itemTitle,
					address as itemInfo,
					name,
					address
				from contact
				limit 20
			";
			return($this);
		}
		function search($arg){
			$this->sql[] = "select
					id as itemKey,
					name as itemTitle,
					address as itemInfo,
					name,
					address
				from contact
				where name like '%".$arg."%' or address like '%".$arg."%'
			";
			return($this);
		}
	}
?>
