<?php
if(class_exists('Input')){
  trigger_error("Class 'Input' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class Input{
    public static $_instance;
		public static $_requested;
		public $_params;
		
		public function Input(){
			$this->_params = array_merge($_GET,$_POST);
			$this->get_input_argv();
			$arr = array();
			foreach($this->_params as $key => $value){
				$arr[preg_replace("{^amp;}","",$key)] = $value;
			}
			$this->_params = &$arr;
			
			if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
				foreach(array_keys($this->_params) as $k){
					$v = $this->_params[$k];
					unset($this->_params[$k]);
					$this->_params[stripslashes($k)] = stripslashes($v);
				}
			}
		}
		
		private function get_input_argv(){
			if(isset($_SERVER['argv']) && count($_SERVER['argv'])>1){
				foreach($_SERVER['argv'] as $key => $vl){
					if($key==0) continue;
					preg_match("/^(.+?)=(.+?)$/",$vl,$inp);
					$this->_params[$inp[1]] = $inp[2];
				}
			}
		}
		
		public function get_value($name=null){
			if(!$name) return $this->_params;
			return isset($this->_params[$name]) ? $this->_params[$name] : null;
		}
		
		public function set_value($name,$value){
			return $this->_params[$name] = $value;
		}
		
		public function md5key($key){
			return scrypt($this->_params,$key);
		}
		
		public static function init($default_lang = 'en',$default_file = 'index'){	
			self::$_instance = new Input();
			self::$_requested = self::explodeRequestUri();
			self::$_requested[1] = isset(self::$_requested[1]) && self::$_requested[1] ? self::$_requested[1] : $default_lang;
			if(preg_match('{^('.implode('|',API::$config['DOMAIN']['languages-list']).')$}si',self::$_requested[1])){
			  self::$_requested[2] = isset(self::$_requested[2]) && self::$_requested[2] ? self::$_requested[2] : $default_file;
			}else{
        array_splice(self::$_requested,1,0,$default_lang);
			}
		}
		
		public static function get($name=null){
			return self::$_instance->get_value($name);
		}
		
		public static function set($name,$value){
			return self::$_instance->set_value($name,$value);
		}
		
		public static function key($key){
			return self::$_instance->md5key($key);
		}
		
		public static function get_method(){
			return strtoupper($_SERVER['REQUEST_METHOD']);
		}
		
		public static function getRequestedType(){
		  $type = 'html';
		  $uri = self::$_requested; 
		  unset($uri[1]); // delete language
		  if(!preg_match('{\\.html$}', preg_replace('{\?.*?$}i','',$_SERVER['REQUEST_URI'])) && $_SERVER['REQUEST_URI'] != '/'){ $type = 'php'; }
		  return strtolower($type);
		}
		
		public static function explodeRequestUri(){
		  $url = trim($_SERVER['REQUEST_URI']);
		  $url = preg_replace('{\?.*?$}','',$url);
		  $url = preg_replace('{(\\'.API::$config['GENERAL']['eofurl'].'|.php)$}','',$url);
			return explode("/",$url);
		}
  }
}
?>
