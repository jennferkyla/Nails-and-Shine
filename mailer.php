<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function send_mail($to,$subject,$body){
 $GLOBALS['mail_err']='';
 $dir=__DIR__.'/PHPMailer/';
 if(!is_file($dir.'PHPMailer.php')){$GLOBALS['mail_err']='PHPMailer folder not found';return false;}
 if(MAIL_PASS===''){$GLOBALS['mail_err']='Email password is not set in config.local.php';return false;}
 require_once $dir.'Exception.php';require_once $dir.'PHPMailer.php';require_once $dir.'SMTP.php';
 try{$m=new PHPMailer(true);$m->isSMTP();$m->Host='smtp.gmail.com';$m->SMTPAuth=true;$m->Username=MAIL_USER;$m->Password=MAIL_PASS;
  $m->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;$m->Port=587;$m->CharSet='UTF-8';
  if(in_array($_SERVER['SERVER_NAME']??'',['localhost','127.0.0.1'],true))$m->SMTPOptions=['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]];
  $m->setFrom(MAIL_USER,setting('studio_name',MAIL_NAME));$m->addAddress($to);$m->Subject=$subject;$m->Body=$body;$m->isHTML(false);$m->send();return true;}
 catch(Exception $e){$GLOBALS['mail_err']=$e->getMessage();return false;}}
