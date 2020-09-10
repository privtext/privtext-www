<?php
if(class_exists('ProofHandler')){
  trigger_error("Class 'ProofHandler' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class ProofHandler extends BaseObject{
    function __construct(){
      parent::__construct();
    }
    
    public function getPrefix($offset = 0){
      $prefix = substr(md5(API::$config['SECURITY']['hashSuffix'].((int)(date('U')/300) - $offset)), 0, 32);
      return $prefix;
    }
    
    public function checkProof($proof){
      for($offset = 0; $offset < 3; $offset++){
        $prefix = $this->getPrefix($offset);
        if( substr($proof, 0, strlen($prefix)) != $prefix){ continue; }
        if(hash('sha256', $proof) >= API::$config['SECURITY']['minerTarget']){ return false; }
        try {
          $proof_md5 = md5($proof);
          $timestamp = date('Y-m-d H:i:s',API::$time);
          API::$db->query("INSERT INTO `used_tokens` (`id`, `timestamp`) VALUES ('".API::$db->escape($proof_md5)."','".API::$db->escape($timestamp)."')");
          return true;
        } catch (Exception $ex){
          error_log($ex->getMessage());
          return false;
        }
      }
      return false;
    }
    
  }
}
?>
