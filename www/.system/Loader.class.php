<?php
include(DOCUMENT_SYSTEM.'/base/API.class.php');
include(DOCUMENT_SYSTEM.'/base/Session.class.php');
include(DOCUMENT_SYSTEM.'/base/Cookies.class.php');
include(DOCUMENT_SYSTEM.'/base/BaseObject.class.php');
include(DOCUMENT_SYSTEM.'/base/JSON.class.php');
include(DOCUMENT_SYSTEM.'/base/User.class.php');
include(DOCUMENT_SYSTEM.'/base/Input.class.php');
include(DOCUMENT_SYSTEM.'/base/Xtemplate.class.php');
include(DOCUMENT_SYSTEM.'/base/Page.class.php');
include(DOCUMENT_SYSTEM.'/base/Language.class.php');
include(DOCUMENT_SYSTEM.'/base/DataBase.class.php');

if(class_exists('Loader')){
  trigger_error("Class 'Loader' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class Loader{
    function __construct(){
      API::init();
      
      Session::init();
      Cookies::init();
      
      $this->autorun();
      
      Input::init(API::$config['DOMAIN']['default-lang'], API::$config['DOMAIN']['default-file']);
      
      if(isset($_SERVER['argv']) && count($_SERVER['argv'])>1){
        include(DOCUMENT_ROOT.'/../cron/cron_action_'.Input::get('action').'.php');
        exit;
      }
      
      
      API::$user = new User();
      API::$lang = new Language();
      API::$page = new Page();
      API::$page->out();
    }
    
    private function autorun(){
      set_time_limit(0);
      
			if (ini_set('display_errors',API::$config['GENERAL']['trace_mode'])){
				error_reporting(E_ALL);
			}
			
			if (extension_loaded('mbstring')){
				mb_internal_encoding("UTF-8");
				mb_language("uni");
			}

			if (function_exists('date_default_timezone_set')){
				date_default_timezone_set('GMT');
			}
		}
		
  }
}
?>
