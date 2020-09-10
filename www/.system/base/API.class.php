<?php
if(class_exists('API')){
  trigger_error("Class 'API' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class API{
    public static $config;
    public static $time;
    public static $tmpl;
    public static $isAjax;
    public static $user;
    public static $page;
    public static $_json;
    public static $lang;
    public static $db;
    
    function init(){
      self::$time = time();
      
      /* Configurate */
      self::$config = array();
      if(file_exists(DOCUMENT_SYSTEM.'config.php')){
        include (DOCUMENT_SYSTEM.'config.php');
        self::$config = array_merge(self::$config,$config);
      }
      self::$config['DOMAIN']['name'] = strtolower(preg_replace('{^www\.}si','',trim($_SERVER['HTTP_HOST'])));
      self::$config['GENERAL']['eofurl'] = '.html';
      
      /* Other Init*/
      self::$_json = new Services_JSON(1);
      self::$tmpl = null;
      self::$page = null;
      self::$lang = null;
      
			self::$db = new DataBase(API::$config['GENERAL']['db_path']);
      
      self::$isAjax = ( ($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['ajax']) && $_POST['ajax']==1) );
    }
    
    public static function isUsingHTTPS(){ return (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'); }
    public static function urlencode($str){	return rawurlencode($str); }
		public static function urldecode($str){ return rawurldecode($str); }
		public static function setcookie($name,$value) { setcookie($name, $value, self::$time+(3600*24*30*12), '/', '.'.self::$config['DOMAIN']['name']); }
  
    public static function userAgentOSDetect(){
      $agent = isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : '';

      if(preg_match('/Linux/',$agent)) $os = 'Linux';
      elseif(preg_match('/Win/',$agent)) $os = 'Windows';
      elseif(preg_match('/Mac/',$agent)) $os = 'Mac';
      else $os = 'UnKnown';
      
      return $os;
    }
    
    public static function sendEmail($params,$template){
			$template = strtr($template,$params);
			
			$headers   = array();
			//$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=\"UTF-8\"";
			$headers[] = "From: ".$params['{sender_name}']." <".$params['{sender_email}'].">";
			//$headers[] = "To: ".$params['{to_name}']." <".$params['{to_email}'].">";
			//$headers[] = "Subject: ".$params['{subject}']."";
			//$headers[] = "X-Mailer: PHP/".phpversion();
			//$headers[] = "X-Priority: 1";
			//$headers[] = "X-MSMail-Priority: Normal";
			/*
			print_r($template);
			print_r($params);
			print_r($headers);
		  */ 

      $send_status = mail($params['{to_email}'], $params['{subject}'], $template, implode("\r\n", $headers));
			return $send_status;
		}
		
	public static function GetIP()
	{
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
		{
		    if (array_key_exists($key, $_SERVER) === true)
		    {
		        foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
		        {
		            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
		            {
		                return $ip;
		            }
		        }
		    }
		}
	}
    
    public static function notificate_about_destroy_note($id, $email, $created, $timelive){
      $params = array(
        '{sender_email}' => API::$config['NOTIFICATE']['sender_email'],
        '{sender_name}' => API::$config['NOTIFICATE']['sender_name'],
        '{to_email}' => $email,
        '{subject}' => API::$config['NOTIFICATE']['subject'],
        
        '{note_id}' => $id,
      );
      
      // $template = "
      //   <html>
      //     <head>
      //       <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
      //     </head>
      //     <body>
      //   The private note, what you created on our service, it identety is '{note_id}', has been removed.<br />
      //   You can check it by <a href=\"https://privtext.com/note/{note_id}.html\">this link</a>.
      //   </body>
      //   </html>
      // ";

      $template = "
        <html>
          <head>
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
          </head>
          <body>
        The private note, what you created on our service, it identety is '{note_id}', has been removed.<br />
        You can check it by <a href=\"https://privtext.com/{note_id}\">this link</a>.
        </body>
        </html>
      ";
      
      return self::sendEmail($params, $template);
    }
    
  }
}
?>
