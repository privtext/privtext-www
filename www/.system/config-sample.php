<?php
$config = array(
  'DOMAIN' =>  array(
    'designe-version' => 1,
    'languages-list' => array('en'), # available languages
    'default-lang' => 'en', # default language
    'default-file' => 'index', # default page index url
        
    /*---*/
    'support-email-name' => array(
      'en' => 'someone', # email account for each language
    ),
    'support-email-hostname' => 'example.com', # email domain
    /*---*/
        
    'google-analytic-id' => null, # google analytic ID
    
  ),
  
  'SECURITY' => array(
    'hashSuffix' => 'JHGBELEJDAUGHBJKEK',
    'minerTarget' => "0008ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff",
  ),
  
  'GENERAL' => array(
    'db_path' => 'mysql://USER:PASSWORD@HOST:PORT/DBNAME#',
    'session_prefix' => 'privtext',
    'trace_mode' => 0, # enable for debug service
  ),

  'CONTACTFORM' => array(
   'sender_email' => 'noreply@example.com', # noreply email account
   'sender_name' => 'PrivText System Notefy',
   'contact_email' => 'someone@example.com', # manager's email account
   'subject' => 'You have a new contact request',
  ),

  'NOTIFICATE' => array(
    'sender_email' => 'noreply@privtext.com',
    'sender_name' => 'PrivText System Notify',
    'subject' => 'Your note has been self-destroied.',
  ),

);
?>
