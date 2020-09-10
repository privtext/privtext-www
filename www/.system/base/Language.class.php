<?php
if(class_exists('Language')){
  trigger_error("Class 'Language' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class Language extends BaseObject{
    private $_default_language;

    function __construct(){
      $this->property('lang.url',Input::$_requested[1]);
      $this->property('lang.intourl', '');
    }
    
  }
}
?>
