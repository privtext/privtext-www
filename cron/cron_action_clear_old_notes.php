<?php
  $q = API::$db->query("SELECT `id`, `created`, `timelive`, `noticemail` FROM `notes` WHERE `timelive` IS NOT NULL AND `timelive`>0 AND `created`+`timelive`<='".API::$time."';");
  while($n = API::$db->row($q)){
    API::$db->query("DELETE FROM `notes` WHERE `id`='".API::$db->escape($n['id'])."';");
    if($n['noticemail']){
      API::notificate_about_destroy_note($n['id'], $n['noticemail'], $n['created'], $n['timelive']);
    }
  }
?>
