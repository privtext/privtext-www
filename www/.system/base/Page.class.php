<?php
include(DOCUMENT_SYSTEM.'/base/BasePage.class.php');
if(class_exists('Page')){
  trigger_error("Class 'Page' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class Page extends BaseObject{
    private $_page_handlers = null;
    private $_pageHandler = null;
    private $_404NotFound = array('class'=>'Error404PageHtml', 'tmpl'=>'page-404NotFound.xtpl');
    
    function __construct(){
      $className = 'TextPage';
      
      $this->_page_handlers = array();
      if(file_exists(DOCUMENT_SYSTEM.'page_handlers.php')){
        include (DOCUMENT_SYSTEM.'page_handlers.php');
        $this->_page_handlers = $config;
      }
      
      $dataType = Input::getRequestedType();

      if(Input::$_requested[2] == 'note' && (isset(Input::$_requested[3]) && strlen(Input::$_requested[3]) >= 6) ){
        $dataType = 'html';
      }
      
      if(isset($this->_page_handlers[$dataType][Input::$_requested[2]]) && $this->_page_handlers[$dataType][Input::$_requested[2]]){
        $className = $this->_page_handlers[$dataType][Input::$_requested[2]];
      }
      
      $className .= ucwords($dataType);

      // print("<!-- ");
      // print_r(array(Input::$_requested[2], $dataType));
      // print(" -->");

      if($dataType == 'html'){
        $byTemplate = Input::$_requested;
        if(isset($byTemplate[0]) && !$byTemplate[0]) unset($byTemplate[0]);
        
        if(API::$isAjax){
          $byTemplate[count($byTemplate)] = 'ajax-'.$byTemplate[count($byTemplate)].'.xtpl';
        }else{
          $byTemplate[count($byTemplate)] = 'page-'.$byTemplate[count($byTemplate)].'.xtpl';
        }
        
        $template_name = implode('/',$byTemplate);
        
        /* set xxx-index.xtpl as default in folder */
        if(API::$isAjax){
          $template_name = str_replace('ajax-.xtpl','ajax-index.xtpl',$template_name);
        } else {
          $template_name = str_replace('page-.xtpl','page-index.xtpl',$template_name);
        }
        
        // print("<!-- ");
        // print(Input::$_requested[2]);
        // print(" -->");

        /* RELOAD TEMPLATE FOR NOTE PAGE */
        if(Input::$_requested[2] == 'note'){
          if((isset(Input::$_requested[3]) && Input::$_requested[3]) &&
                !(isset(Input::$_requested[4]) && Input::$_requested[4])){
            $noteid = Input::$_requested[3];
            $template_name = str_replace("/note/page-$noteid",'/page-note',$template_name);
            $template_name = str_replace("/note/ajax-$noteid",'/ajax-note',$template_name);
          }else{
            $template_name = str_replace("/page-note",'/page-note',$template_name);
            $template_name = str_replace("/ajax-note",'/ajax-note',$template_name);
            $className = $this->_404NotFound['class'];
            $template_name = $this->_404NotFound['tmpl'];
          }
        }
        
        $this->startTemplate($className,$template_name);
      }else if ($dataType == 'php'){
        if(file_exists(DOCUMENT_SYSTEM.'/base/'.$className.'.class.php')){
          include(DOCUMENT_SYSTEM.'/base/'.$className.'.class.php');
          new $className();
        }else{
          $this->start404Page();
        }
      }else{
        $this->start404Page();
      }
    }
    
    function startTemplate($className,$template_name){
      if(!file_exists(DOCUMENT_TEMPLATES.$template_name)){
        if($this->_404NotFound['class'] != $className){
          $this->start404Page();
        }else{
          trigger_error("Cannot fount 404 template!", E_USER_ERROR);
        }
      }else{
        if(file_exists(DOCUMENT_SYSTEM.'/base/'.$className.'.class.php')){
          include(DOCUMENT_SYSTEM.'/base/'.$className.'.class.php');
          $this->_pageHandler = new $className($template_name);
        }else{
          $this->start404Page();
        }
      }
    }
    
    function start404Page(){
      $this->startTemplate(
        $this->_404NotFound['class'],
        $this->_404NotFound['tmpl']
      );
    }
    
    function out(){
      if(!is_null($this->_pageHandler)){
        $this->_pageHandler->out();
      }
    }
  }
}
?>
