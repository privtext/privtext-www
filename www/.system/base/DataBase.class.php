<?php
if(class_exists('DataBase')){
	trigger_error("Class DataBase can not declare more than one times",E_USER_NOTICE); 
}else{
	final class DataBase extends BaseObject{
		private $_i;	
		public function __construct($access){
			$this->_i = null;
			$this->property('connect.i',null,true);
			if(preg_match('{^([\w]+)://(.+?):(.+?)@(.+?):(.+?)/(.+?)#(.*?)$}se',trim($access),$data)){
				$this->property('connect.data',$data);
				$this->property('connect.protocol',$data[1]);
				
				if(strtolower($this->property('connect.protocol')) !== 'mysql'){
					trigger_error("Unknown protocol for DataBase Connection",E_USER_USER);
				}
				
				$this->property('connect.user',$data[2]);
				$this->property('connect.password',$data[3]);
				$this->property('connect.host',$data[4]);
				$this->property('connect.port',$data[5]);
				$this->property('connect.db',$data[6]);
				$this->property('connect.prefix',$data[7]);
			}else{
				trigger_error("Bad mysql access format",E_USER_USER);
			}
		}
		
		private function connect(){
			if($this->_i){ trigger_error("DataBase can not connect more than one times",E_USER_NOTICE); }
			$this->_i = @mysql_connect($this->property('connect.host').':'.$this->property('connect.port'),$this->property('connect.user'),$this->property('connect.password'))
				or trigger_error("Can't connect to DataBase",E_USER_ERROR);
			mysql_select_db($this->property('connect.db'), $this->_i)
				or trigger_error("Can't select DataBase",E_USER_ERROR);
			$this->query("SET NAMES 'utf8'");
		}
		
		public function query($query){
			if(!$this->_i){ $this->connect(); }
			$result = mysql_query($query,$this->_i);
			if(!$result || strlen($error = mysql_error($this->_i))){
			  //trigger_error("DataBase query error: $query \r\n".$error,E_USER_ERROR);
			  return false;
			}
			return $result;
		}
		
		public function row(&$result,$is_assoc=true){
			return $is_assoc ? mysql_fetch_assoc($result) : mysql_fetch_row($result);
		}
		
		public function count(&$result){
			return mysql_num_rows($result);
		}
		
		public function fetch($query,$is_assoc=true){
			$result = $this->query($query);
			return $this->count($result) ? $this->row($result,$is_assoc) : array();		
		}
			
		public function fetchAll($query,$is_assoc=true){
			$result = $this->query($query);
			$rows = array();
			if($this->count($result)){ while($row = $this->row($result,$is_assoc)){ $rows[] = $row; } }
			return $rows;
		}
			
		public function inserted_id(){
			if(!$this->_i){ $this->connect(); }
			return mysql_insert_id($this->_i);
		}
		
		public function escape(&$v){
			if(!$this->_i){ $this->connect(); }
			return mysql_real_escape_string($v,$this->_i);
		}
		
		public function __destruct(){
			if($this->_i) return mysql_close($this->_i);
			return 0;
		}
			
	}
}
?>
