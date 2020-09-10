<?php
if(class_exists('NotePageHtml')){
  trigger_error("Class 'NotePageHtml' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class NotePageHtml extends BasePage{
    public function __construct($template_name){
      parent::__construct($template_name);
      
      $noteid = isset(Input::$_requested[3]) && Input::$_requested[3] ? Input::$_requested[3] : null;
      
      API::$tmpl->assign(
        array(
          'PAGE_NOTE_ID' => $noteid
        )
      );
      
      if(API::$db->count(API::$db->query("SELECT `id` FROM `notes` WHERE `id`='".API::$db->escape($noteid)."';"))){
      	API::$tmpl->parse('main.no_emptynote');
      }else{
      	API::$tmpl->parse('main.emptynote');
      }
    }
  }
}
?>
