<?php
include(DOCUMENT_SYSTEM.'/base/ProofHandler.class.php');

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if(class_exists('ApiPagePhp')){
  trigger_error("Class 'ApiPagePhp' shouldn't be defined twice",E_USER_NOTICE);
}else{
  class ApiPagePhp extends BasePage{
  
    public $LENGTH_ID = 6;//8;
  
    public function __construct(){
      $method = 'user_ajax_'.Input::get('action');
      if(Input::get_method() == 'POST'){
        if(method_exists('ApiPagePhp',$method)){
          $this->$method(Input::get('action'));
        }else{
          print "Access denied!";
        }
      }else{
       print "Access denied!";
      }
    }
    
    private function user_ajax_get_proof_password($action){
      $ph = new ProofHandler();
      $data = array(
        'action' => $action,
        'prefix' => $ph->getPrefix(),
        'target' => API::$config['SECURITY']['minerTarget'],
        'algorithm' => 'sha256',
        'ajax_status' => 'ok'
      );
      print API::$_json->encode($data);
    }
    
    private function user_ajax_delete_note($action){
      $data = array(
        'action' => $action,
        'ajax_status' => 'ok'
      );
      $noteid = Input::get('noteid');
      @list($timelive,$created,$noticemail) = @API::$db->row(API::$db->query("SELECT `timelive`,`created`,`noticemail` FROM `notes` WHERE `id`='".API::$db->escape($noteid)."';"), false);      
      API::$db->query("DELETE FROM `notes` WHERE `id`='".API::$db->escape($noteid)."';");
      if($noticemail){
        API::notificate_about_destroy_note($noteid, $noticemail, $created, $timelive);
      }
      
      print API::$_json->encode($data);
    }
    
    private function user_ajax_read_note($action){
      $ph = new ProofHandler();
      $proof = Input::get('proof');
      $noteid = Input::get('noteid');
      
      if($ph->checkProof($proof) == false){
        print API::$_json->encode(array('ajax_status' => 'error', 'error_type' => 'invalid-proof-password', 'message' => 'invalid proof-of-work'));
        exit;
      }
      
      if(is_null($noteid)){
        print API::$_json->encode(array('ajax_status' => 'error', 'error_type' => 'empty-noteid', 'message' => 'NoteId is empty'));
        exit;
      }
      
      @list($note,$timelive,$created,$noticemail) = @API::$db->row(API::$db->query("SELECT `data`,`timelive`,`created`,`noticemail` FROM `notes` WHERE `id`='".API::$db->escape($noteid)."';"), false);

      if(!trim($note) || ($timelive && ($timelive + $created <= API::$time))){
        print API::$_json->encode(array('ajax_status' => 'error', 'error_type' => 'nodata', 'message' => 'NoteId is unregistered'));
        exit;
      } 
      
      if($note){
        if(/*!$timelive || */($timelive && ($timelive + $created <= API::$time))){
          API::$db->query("DELETE FROM `notes` WHERE `id`='".API::$db->escape($noteid)."';");
          if($noticemail){
            API::notificate_about_destroy_note($noteid, $noticemail, $created, $timelive);
          }
        }
      }

      
      
      print API::$_json->encode(
          array(
            'ajax_status' => 'ok',
            'action' => $action,
            'note' => $note,
            'timelive' => $timelive,
            'timelive_seconds_to_end' => ($timelive?(($timelive+$created)-API::$time):0),
          )
        );
      exit();
      
    }

    private function user_ajax_read_notedata($action){
      $noteid = Input::get('noteid');
      
      if(is_null($noteid)){
        print API::$_json->encode(array('ajax_status' => 'error', 'error_type' => 'empty-noteid', 'message' => 'NoteId is empty'));
        exit;
      }
      
      @list($note,$timelive,$created,$noticemail) = @API::$db->row(API::$db->query("SELECT `data`,`timelive`,`created`,`noticemail` FROM `notes` WHERE `id`='".API::$db->escape($noteid)."';"), false);

      if(!trim($note) || ($timelive && ($timelive + $created <= API::$time))){
        print API::$_json->encode(array('ajax_status' => 'error', 'error_type' => 'nodata', 'message' => 'NoteId is unregistered'));
        exit;
      } 
      
      print API::$_json->encode(
          array(
            'ajax_status' => 'ok',
            'action' => $action,
            'note_id' => $noteid
          )
        );
      exit();
      
    }
    
    private function user_ajax_save_note($action){
      $ph = new ProofHandler();
      
      $note = Input::get('note');
      $proof = Input::get('proof');
      
      $timelive = Input::get('timelive');
      $noticemail = Input::get('noticemail');
      /*
      if($ph->checkProof($proof) == false){
        print API::$_json->encode(array('ajax_status' => 'error', 'error_type' => 'invalid-proof-password', 'message' => 'invalid proof-of-work'));
        exit;
      }
      */
      if(is_null($note)){
        print API::$_json->encode(array('ajax_status' => 'error', 'error_type' => 'nodata', 'message' => 'Note is empty'));
        exit;
      }
      
      $id = generateRandomString($this->LENGTH_ID);
      API::$db->query("INSERT INTO `notes` (`id`, `data`, `timelive`, `noticemail`, `created`)
        VALUES ('".API::$db->escape($id)."','".API::$db->escape($note)."',".($timelive?"'".API::$db->escape($timelive)."'":'NULL').",'".API::$db->escape($noticemail)."','".API::$db->escape(API::$time)."');");

      print API::$_json->encode(
          array(
            'ajax_status' => 'ok',
            'action' => $action,
            'id' => $id,
            #'url' => API::$lang->property('current.intourl').'/note/'.$id.'.html',
            'url' => API::$lang->property('current.intourl').'/'.$id,
            'timelive' => $timelive,
            'timelive_seconds_to_end' => ($timelive?$timelive:0),
          )
        );
      exit();
    }
    
  }
  
  
  
}
?>
