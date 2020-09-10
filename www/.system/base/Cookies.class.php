<?php
if(class_exists('Cookies')){
  trigger_error("Class 'Cookies' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class Cookies{
    private static $_list_dict;
    function init(){ self::$_list_dict = $_COOKIE;}
    static public function property($name='',$value=null,$reset=false){			
			if($name && ($value || $reset)){ API::setcookie($name,$value); self::$_list_dict[$name] = $value; return 0; }
			if($name){ return isset(self::$_list_dict[$name]) ? self::$_list_dict[$name] : null; }
		}
  }
}
?>
