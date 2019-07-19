<?php
	class Application implements Primary, Search, Compose, Update, Delete {
		protected $sql;

		function primary(){
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
		function compose($arg){
			$this->sql[] = "insert into contact(
					name,
					address
				) values(
					'".$arg['name']."',
					'".$arg['address']."'
				)
			";
			return($this);
		}
		function update($arg){
			$this->sql[] = "update contact set
				name = '".$arg['name']."',
				address = '".$arg['address']."'
			where id = '".$arg['itemKey'][0]."'";
			return($this);
		}
		function delete($arg){
			foreach($arg['itemKey'] as $id){
				$this->sql[] = "delete from contact where id = '".$id."'";
			}
			return($this);
		}
	}
?>