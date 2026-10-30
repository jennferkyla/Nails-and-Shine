<?php require 'config.php';require 'mailer.php';$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'&&empty($_POST['website'])){
 $em=strtolower(trim($_POST['email']??''));
 $s=$pdo->prepare('SELECT user_id,full_name FROM users WHERE email=?');$s->execute([$em]);$r=$s->fetch();
 if($r){$c=$pdo->prepare('SELECT COUNT(*) FROM password_resets WHERE user_id=? AND created_at>DATE_SUB(NOW(),INTERVAL 1 HOUR)');$c->execute([$r['user_id']]);
  if((int)$c->fetchColumn()<3){$tok=bin2hex(random_bytes(32));
   $pdo->prepare('INSERT INTO password_resets(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 1 HOUR))')->execute([$r['user_id'],hash('sha256',$tok)]);
   send_mail($em,'Reset your password - '.setting('studio_name'),'Hi '.$r['full_name'].",\n\nUse this link to set a new password (valid for 1 hour):\n".SITE_URL.'/reset.php?token='.$tok."\n\nIf you did not ask for this, you can ignore this email.");}}
 $msg='If that email is registered, we sent a link to reset the password. Please check your inbox and spam folder.';}
$title='Forgot password';include 'includes/header.php';?>
<form method="post" class="card"><h2>Forgot password</h2><?php if($msg):?><p class="alert ok"><?=e($msg)?></p><?php endif;?>
<input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="display:none">
<p>Enter your email and we will send you a link to set a new password.</p><input name="email" type="email" placeholder="Email" required><button>Send reset link</button>
<p><small><a href="login.php">Back to login</a></small></p></form>
<?php include 'includes/footer.php';
