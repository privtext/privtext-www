<?php
if(class_exists('BasePage')){
  trigger_error("Class 'BasePage' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class BasePage extends BaseObject{
    public $_config = array();
    function __construct($template_name=''){
      parent::__construct();
      $template = str_replace(dirname($template_name).'/','',$template_name);
      $template = str_replace('{^/+}','',$template);
      
      $template_dir = preg_replace('{/+$}','',str_replace($template,'',$template_name));
      $template_dir = "$template_dir/";
      
      API::$tmpl = new XTemplate($template, DOCUMENT_TEMPLATES.$template_dir);

      $pagephp = preg_replace('{^(.+?)/page-(.+?).xtpl$}i','$1/$2.php',$template_name);
      if(file_exists(DOCUMENT_PAGECONFIG.$pagephp)){
        require_once(DOCUMENT_PAGECONFIG.$pagephp);
        $this->_config = $page;
      }
      
      $variables = array();
      foreach(array_keys($this->_config) as $key){
        if(!is_array($this->_config[$key]) && !is_object($this->_config[$key])
           && !is_resource($this->_config[$key])){
           $variables['PAGE_'.strtoupper($key)] = $this->_config[$key];
        }
      }
      
      
      $variables['PAGE_LINKID'] = preg_replace('{^(?:/?v\d+/)?/?'.API::$lang->property('lang.url').'?/|\.php$}si','',$pagephp);
      $variables['PAGE_LINKID'] = preg_replace('{/+}si','-',$variables['PAGE_LINKID']);
      
      $variables['SITEHOST_NAME'] = API::$config['DOMAIN']['name'];
      $variables['USERAGENT_OS_NAME'] = API::userAgentOSDetect();
      $variables['DATE_CURRENT_YEAR'] = date('Y');
      
      $variables['LANGUAGE'] = API::$lang->property('lang.url');
      
      $variables['GOOGLE_ANALYTIC_ID'] = isset(API::$config['DOMAIN']['google-analytic-id']) && API::$config['DOMAIN']['google-analytic-id'] ? API::$config['DOMAIN']['google-analytic-id'] : null;
      
      if(isset(API::$config['DOMAIN']['support-email-name'][API::$lang->property('lang.url')]) &&
          API::$config['DOMAIN']['support-email-name'][API::$lang->property('lang.url')]){
        $variables['PAGE_SUPPORT_EMAIL'] = API::$config['DOMAIN']['support-email-name'][API::$lang->property('lang.url')].'@'.API::$config['DOMAIN']['support-email-hostname'];
      }
      
      if(Input::$_requested[2] == 'index'){
        if(isset($_COOKIE['openedLeft']) && $_COOKIE['openedLeft'] == 1){
          $variables['NOTE_SETTINGS_CLASS_body'] = 'slideToRight';
          $variables['NOTE_SETTINGS_CLASS_underBg'] = 'showed';
          $variables['NOTE_SETTINGS_CLASS_wrapLeftSettings'] = 'opened';
          $variables['NOTE_SETTINGS_CLASS_settingsBottom'] = 'display: none;';
        }
      }
      
      $this->assign_template_vars($variables);


      if(!API::$isAjax){
        
        API::$tmpl->parse('bottom.mode_normal_jslib');
        API::$tmpl->parse('header.mode_normal_jslib');
        
      }

    }
    
    function assign_template_vars($vars){
      if(API::$tmpl){
        API::$tmpl->assign($vars);
      }
    }
    
    function out(){
      if(API::$tmpl){
        if(API::$isAjax){
          API::$tmpl->parse('ajax');
          print API::$tmpl->out('ajax');
        }else{
          API::$tmpl->parse('header');
          API::$tmpl->parse('top');
          API::$tmpl->parse('main');
          API::$tmpl->parse('bottom');
          
          print API::$tmpl->out('header');
          print API::$tmpl->out('top');
          print API::$tmpl->out('main');
          print API::$tmpl->out('bottom');
        }
      }
    }
  }
}
?>
