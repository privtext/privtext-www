<?php
if(class_exists('BaseObject')){
  trigger_error("Class 'BaseObject' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class BaseObject{
    protected $_data;
    public function __construct(){
		  $this->_data = array();	
	  }
	  public function property($name='',$value=null,$reset=false){
		  if($name && ($value || $reset)){ $this->_data[$name] = $value; return 0; }
		  if($name){ return isset($this->_data[$name]) ? $this->_data[$name] : null; }
		  return $this->_data;	
	  }
  }
}
?>
