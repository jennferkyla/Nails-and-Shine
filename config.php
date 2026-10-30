<?php
require_once __DIR__.'/config.local.php';
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
if(ENV==='production'){ini_set('display_errors','0');ini_set('log_errors','1');ini_set('error_log',__DIR__.'/logs/error.log');}else{ini_set('display_errors','1');}
define('IS_CLI',PHP_SAPI==='cli');
if(!IS_CLI){
 $https=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off');
 session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$https,'httponly'=>true,'samesite'=>'Lax']);
 ini_set('session.use_strict_mode','1');
 session_start();
 header('X-Frame-Options: SAMEORIGIN');header('X-Content-Type-Options: nosniff');header('Referrer-Policy: strict-origin-when-cross-origin');
}
try{
 $pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
 $pdo->exec("SET time_zone='+08:00'");
}catch(PDOException $x){error_log($x->getMessage());if(!IS_CLI)http_response_code(500);exit(ENV==='production'?'The site is temporarily unavailable. Please try again later.':'Database connection failed: '.$x->getMessage());}

function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function peso($n){return '₱'.number_format((float)$n);}
function user(){return $_SESSION['u']??null;}
function go($p){header('Location: '.BASE.'/'.$p);exit;}
function need($role=null){$u=user();if(!$u)go('login.php');
 if($role==='staff'&&!in_array($u['role'],['admin','technician'],true))go('index.php');
 if($role&&$role!=='staff'&&$u['role']!==$role)go('index.php');}
function my_tech(){global $pdo;$u=user();if(!$u||$u['role']!=='technician')return 0;$q=$pdo->prepare('SELECT technician_id FROM technicians WHERE user_id=?');$q->execute([$u['id']]);return (int)$q->fetchColumn();}

// ---- settings (editable by the owner in Admin > Settings) ----
function setting($k,$d=''){global $pdo;static $c=null;if($c===null){$c=[];foreach($pdo->query('SELECT setting_key,setting_value FROM settings') as $r)$c[$r['setting_key']]=$r['setting_value'];}return $c[$k]??$d;}
function save_setting($k,$v){global $pdo;$pdo->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$k,(string)$v]);}

// ---- CSRF protection (token is added automatically to every POST form) ----
function csrf_token(){if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
if(!IS_CLI){
 if($_SERVER['REQUEST_METHOD']==='POST'&&!hash_equals(csrf_token(),(string)($_POST['_csrf']??''))){http_response_code(400);exit('This form has expired. Please go back, refresh the page, and try again.');}
 $csrfTok=csrf_token();
 ob_start(function($h)use($csrfTok){return preg_replace('/(<form\b[^>]*\bmethod\s*=\s*["\']?post["\']?[^>]*>)/i','$1<input type="hidden" name="_csrf" value="'.$csrfTok.'">',$h);});
}

// ---- login throttling ----
function throttled($em){global $pdo;$ip=$_SERVER['REMOTE_ADDR']??'';
 $a=$pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE email=? AND attempted_at>DATE_SUB(NOW(),INTERVAL 15 MINUTE)');$a->execute([$em]);
 $b=$pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip=? AND attempted_at>DATE_SUB(NOW(),INTERVAL 15 MINUTE)');$b->execute([$ip]);
 return $a->fetchColumn()>=5||$b->fetchColumn()>=20;}
function log_fail($em){global $pdo;$pdo->prepare('INSERT INTO login_attempts(email,ip) VALUES(?,?)')->execute([$em,$_SERVER['REMOTE_ADDR']??'']);}
function clear_attempts($em){global $pdo;$pdo->prepare('DELETE FROM login_attempts WHERE email=?')->execute([$em]);}

// ---- technicians and availability ----
function tech_list($all=false){global $pdo;return $pdo->query('SELECT * FROM technicians'.($all?'':' WHERE is_active=1').' ORDER BY sort_order,technician_id')->fetchAll();}
function tosecs($t){$p=explode(':',$t);return (int)$p[0]*3600+(int)$p[1]*60;}
function slots_for($tid,$date,$dur){global $pdo;
 $q=$pdo->prepare('SELECT open_time,close_time FROM technician_hours WHERE technician_id=? AND weekday=? AND is_open=1');$q->execute([$tid,(int)date('N',strtotime($date))]);$h=$q->fetch();
 if(!$h)return [];
 $step=max(5,(int)setting('slot_step',30))*60;$buf=(int)setting('buffer_min',15)*60;
 $minStart=time()+(int)setting('min_notice_hours',2)*3600;$day=strtotime($date);
 if($day>strtotime('+'.(int)setting('max_advance_days',60).' days'))return [];
 $b=$pdo->prepare("SELECT start_time,end_time FROM appointments WHERE technician_id=? AND status IN('pending','confirmed') AND DATE(start_time)=?
  UNION ALL SELECT start_time,end_time FROM blocked_slots WHERE (technician_id=? OR technician_id IS NULL) AND DATE(start_time)<=? AND DATE(end_time)>=?");
 $b->execute([$tid,$date,$tid,$date,$date]);$busy=$b->fetchAll();
 $open=$day+tosecs($h['open_time']);$close=$day+tosecs($h['close_time']);$out=[];
 for($a=$open;$a+$dur*60<=$close;$a+=$step){$e2=$a+$dur*60;if($a<$minStart)continue;
  foreach($busy as $x){if($a<strtotime($x['end_time'])+$buf&&$e2+$buf>strtotime($x['start_time']))continue 2;}
  $out[]=date('H:i',$a);}
 return $out;}
function open_slots($sid,$date,$tid=0){global $pdo;
 $s=$pdo->prepare('SELECT duration_min FROM services WHERE service_id=? AND is_active=1');$s->execute([$sid]);$d=(int)$s->fetchColumn();if(!$d)return [];
 $act=array_map('intval',array_column(tech_list(),'technician_id'));
 $ids=$tid?(in_array($tid,$act,true)?[$tid]:[]):$act;$all=[];
 foreach($ids as $i)foreach(slots_for($i,$date,$d) as $t)$all[$t]=1;
 $r=array_keys($all);sort($r);return $r;}
function day_load($tid,$date){global $pdo;$q=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE technician_id=? AND status IN('pending','confirmed') AND DATE(start_time)=?");$q->execute([$tid,$date]);return (int)$q->fetchColumn();}
function pick_tech($dur,$date,$time,$tid){
 $ids=array_map('intval',array_column(tech_list(),'technician_id'));
 if($tid){if(!in_array($tid,$ids,true))return 0;$ids=[$tid];}
 $best=0;$load=PHP_INT_MAX;
 foreach($ids as $i){if(!in_array($time,slots_for($i,$date,$dur),true))continue;$l=day_load($i,$date);if($l<$load){$best=$i;$load=$l;}}
 return $best;}
