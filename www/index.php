<?php

  // if(!preg_match('{^/(|index|api|contact|soft|about|privacy|pricing|on|off|note|bin)(\.html||/)$}', $_SERVER['REQUEST_URI'])){
  //   $_SERVER['REQUEST_URI'] = '/note'.$_SERVER['REQUEST_URI'];
  // }

  $_SERVER['REQUEST_URI'] = trim($_SERVER['REQUEST_URI']);

  $pu = explode('/', $_SERVER['REQUEST_URI']);
  if(!in_array(preg_replace('{\.(html|php)}i', '', $pu[1]), array('','index','api','contact','soft','about','privacy','pricing'))){
    $_SERVER['REQUEST_URI'] = '/note'.$_SERVER['REQUEST_URI'];
  }

  define('DS', '/');
  define('DOCUMENT_ROOT',preg_replace('{/+}','/',dirname(__FILE__).'/'));
  define('DOCUMENT_SYSTEM',DOCUMENT_ROOT.'.system/');
  define('DOCUMENT_TEMPLATES',DOCUMENT_SYSTEM.'templates/');
  define('DOCUMENT_PAGECONFIG',DOCUMENT_SYSTEM.'pages/');
  
  include(DOCUMENT_SYSTEM.'Loader.class.php');
  new Loader();
?>
