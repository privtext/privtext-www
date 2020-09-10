<?php
if(class_exists('Error404PageHtml')){
  trigger_error("Class 'Error404PageHtml' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class Error404PageHtml extends BasePage{
    public function __construct($template_name){
      parent::__construct($template_name);
      
      if(file_exists(DOCUMENT_SYSTEM.'404.php')){
        include(DOCUMENT_SYSTEM.'404.php');
      } else {
        if (substr(php_sapi_name(), 0, 3) == 'cgi') {
          header("Status: 404 Not Found");
        }else{
          header("HTTP/1.0 404 Not Found");
        }
        $this->assign_template_vars(array(
          'REQUESTED_URI' => $_SERVER['REQUEST_URI'],
          'HTTP_HOST' => $_SERVER['HTTP_HOST'],
        ));
      }
    }
  }
}
?>
