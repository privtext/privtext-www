<?php
if(class_exists('User')){
  trigger_error("Class 'User' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class User extends BaseObject{
    function __construct(){ }
    
    function get_ip_address(){
	      if ( getenv('REMOTE_ADDR') ) $user_ip = getenv('REMOTE_ADDR');
	      elseif ( getenv('HTTP_FORWARDED_FOR') ) $user_ip = getenv('HTTP_FORWARDED_FOR');
	      elseif ( getenv('HTTP_X_FORWARDED_FOR') ) $user_ip = getenv('HTTP_X_FORWARDED_FOR');
	      elseif ( getenv('HTTP_X_COMING_FROM') ) $user_ip = getenv('HTTP_X_COMING_FROM');
	      elseif ( getenv('HTTP_VIA') ) $user_ip = getenv('HTTP_VIA');
	      elseif ( getenv('HTTP_XROXY_CONNECTION') ) $user_ip = getenv('HTTP_XROXY_CONNECTION');
	      elseif ( getenv('HTTP_CLIENT_IP') ) $user_ip = getenv('HTTP_CLIENT_IP');
	      $user_ip = trim($user_ip);
	      if ( empty($user_ip) ) return false;
	      if ( !preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $user_ip) ) return false;
	      return $user_ip;
    }
    
  }
}
?>
