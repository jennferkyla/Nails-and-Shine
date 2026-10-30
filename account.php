<?php require 'config.php';need();$u=user();$m1='';$m2='';$e2='';
if(isset($_POST['profile'])){$n=trim($_POST['name']??'');if($n){$pdo->prepare('UPDATE users SET full_name=?,phone=? WHERE user_id=?')->execute([$n,trim($_POST['phone']??''),$u['id']]);$_SESSION['u']['name']=$n;$u=user();$m1='Profile saved.';}}
if(isset($_POST['pw'])){$q=$pdo->prepare('SELECT password_hash FROM users WHERE user_id=?');$q->execute([$u['id']]);$h=$q->fetchColumn();$p=$_POST['new']??'';
 if(!password_verify($_POST['current']??'',(string)$h))$e2='Current password is wrong.';elseif(strlen($p)<8)$e2='New password must be at least 8 characters.';elseif($p!==($_POST['new2']??''))$e2='New passwords do not match.';
 else{$pdo->prepare('UPDATE users SET password_hash=? WHERE user_id=?')->execute([password_hash($p,PASSWORD_DEFAULT),$u['id']]);$m2='Password changed.';}}
$q=$pdo->prepare('SELECT full_name,email,phone FROM users WHERE user_id=?');$q->execute([$u['id']]);$me=$q->fetch();
$title='My account';include 'includes/header.php';?>
<h1>My Account</h1>
<form method="post" class="card"><h2>Profile</h2><?php if($m1):?><p class="alert ok"><?=e($m1)?></p><?php endif;?>
<input name="name" value="<?=e($me['full_name'])?>" required maxlength="100"><input value="<?=e($me['email'])?>" disabled><input name="phone" value="<?=e($me['phone'])?>" placeholder="Mobile number" maxlength="20"><button name="profile" value="1">Save profile</button></form>
<form method="post" class="card"><h2>Change password</h2><?php if($m2):?><p class="alert ok"><?=e($m2)?></p><?php endif;?><?php if($e2):?><p class="alert err"><?=e($e2)?></p><?php endif;?>
<input name="current" type="password" placeholder="Current password" required autocomplete="current-password"><input name="new" type="password" placeholder="New password (at least 8 characters)" required autocomplete="new-password"><input name="new2" type="password" placeholder="Confirm new password" required autocomplete="new-password"><button name="pw" value="1">Change password</button></form>
<?php include 'includes/footer.php';
