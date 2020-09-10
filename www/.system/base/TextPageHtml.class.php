<?php
if(class_exists('TextPageHtml')){
  trigger_error("Class 'TextPageHtml' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class TextPageHtml extends BasePage{
    public function __construct($template_name){
      parent::__construct($template_name);
    }
  }
}
?>
