<?php
if(class_exists('Session')){
  trigger_error("Class 'Session' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class Session{
    private static $_instance;
		
		static public function init(){
		  ini_set('session.cookie_domain','.'.API::$config['DOMAIN']['name']);
			session_start();
			if(!(isset($_SESSION[API::$config['GENERAL']['session_prefix']]) && is_array($_SESSION[API::$config['GENERAL']['session_prefix']]))){
				$_SESSION[API::$config['GENERAL']['session_prefix']] = array();
			}
			self::$_instance = &$_SESSION[API::$config['GENERAL']['session_prefix']];
		}
		
		static public function property($name='',$value=null,$reset=false){			
			if($name && ($value || $reset)){ self::$_instance[$name] = $value; return 0; }
			if($name){ return isset(self::$_instance[$name]) ? self::$_instance[$name] : null; }
		}
  }
}
?>
