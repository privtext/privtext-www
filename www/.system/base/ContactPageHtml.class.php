<?php
if(class_exists('ContactPageHtml')){
  trigger_error("Class 'ContactPageHtml' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class ContactPageHtml extends BasePage{
    public function __construct($template_name){
      parent::__construct($template_name);
      
      
      if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $no_errors = 1;
        $params = array('user_name','user_email','message_subject','user_message','captcha');
        foreach($params as $fi){
          $$fi = Input::get($fi);
          if(!$$fi){
            $no_errors = 0;
            API::$tmpl->parse('main.'.$fi.'_empty');
          }
          
          if($fi == 'captcha'){
            if($$fi && $_SESSION["wbch:captcha"] != $$fi){
              $no_errors = 0;
              API::$tmpl->parse('main.'.$fi.'_invalid');
            }
          }
        }
        
        if($no_errors){
          $params = array(
            '{sender_email}' => API::$config['CONTACTFORM']['sender_email'],
            '{sender_name}' => API::$config['CONTACTFORM']['sender_name'],
            '{to_email}' => API::$config['CONTACTFORM']['contact_email'],
            '{subject}' => API::$config['CONTACTFORM']['subject'],
            
            'user_name' => $user_name,
            'user_email' => $user_email,
            'message_subject' => $message_subject,
            'user_message' => $user_message,
          );
          
          $template = "
            CONTACT FORM: <br />
            Name: {user_name} <br />
            Email: {user_email} <br />
            Subject: {message_subject} <br />
            Body: {user_message}
          ";
          
          API::sendEmail($params, $template);
          API::$tmpl->parse('main.sucess_send_mail');
        }else{
          foreach($params as $fi){
            API::$tmpl->assign(array('REPAIR_'.strtoupper($fi) => $$fi));
          }
        }
      
      }
      
    }
  }
}
?>
