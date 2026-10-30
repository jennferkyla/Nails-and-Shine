<?php require 'config.php';$tok=(string)($_GET['token']??($_POST['token']??''));$row=null;$err='';
if(preg_match('/^[a-f0-9]{64}$/',$tok)){$q=$pdo->prepare('SELECT * FROM password_resets WHERE token_hash=? AND used=0 AND expires_at>NOW()');$q->execute([hash('sha256',$tok)]);$row=$q->fetch();}
if($row&&$_SERVER['REQUEST_METHOD']==='POST'){
 $p=$_POST['password']??'';
 if(strlen($p)<8)$err='Password must be at least 8 characters.';
 elseif($p!==($_POST['password2']??''))$err='Passwords do not match.';
 else{$pdo->prepare('UPDATE users SET password_hash=? WHERE user_id=?')->execute([password_hash($p,PASSWORD_DEFAULT),$row['user_id']]);
  $pdo->prepare('UPDATE password_resets SET used=1 WHERE user_id=?')->execute([$row['user_id']]);go('login.php?reset=1');}}
$title='Set a new password';include 'includes/header.php';?>
<?php if(!$row):?><div class="card"><h2>Link not valid</h2><p>This reset link is invalid or has expired.</p><a class="btn" href="forgot.php">Request a new link</a></div>
<?php else:?><form method="post" class="card"><h2>Set a new password</h2><?php if($err):?><p class="alert err"><?=e($err)?></p><?php endif;?>
<input type="hidden" name="token" value="<?=e($tok)?>"><input name="password" type="password" placeholder="New password (at least 8 characters)" required autocomplete="new-password"><input name="password2" type="password" placeholder="Confirm new password" required autocomplete="new-password"><button>Save password</button></form><?php endif;?>
<?php include 'includes/footer.php';
