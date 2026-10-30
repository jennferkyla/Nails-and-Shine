<?php require 'config.php';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'&&empty($_POST['website'])){
 $n=trim($_POST['name']??'');$m=strtolower(trim($_POST['email']??''));$p=$_POST['password']??'';
 if(!$n||!filter_var($m,FILTER_VALIDATE_EMAIL))$err='Please enter your name and a valid email.';
 elseif(strlen($p)<8)$err='Password must be at least 8 characters.';
 elseif($p!==($_POST['password2']??''))$err='Passwords do not match.';
 elseif(empty($_POST['consent']))$err='Please agree to the Privacy Notice to continue.';
 else try{$pdo->prepare("INSERT INTO users(role_id,full_name,email,phone,password_hash) SELECT role_id,?,?,?,? FROM roles WHERE role_name='client'")->execute([$n,$m,trim($_POST['phone']??''),password_hash($p,PASSWORD_DEFAULT)]);
  go('login.php?registered=1');}catch(PDOException $x){$err='That email is already registered. Try logging in.';}}
$title='Register';include 'includes/header.php';?>
<form method="post" class="card"><h2>Create account</h2><?php if($err):?><p class="alert err"><?=e($err)?></p><?php endif;?>
<input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="display:none">
<input name="name" placeholder="Full name" required maxlength="100"><input name="email" type="email" placeholder="Email" required maxlength="100">
<input name="phone" placeholder="Mobile number (optional)" maxlength="20"><input name="password" type="password" placeholder="Password (at least 8 characters)" required autocomplete="new-password"><input name="password2" type="password" placeholder="Confirm password" required autocomplete="new-password">
<label style="display:flex;gap:8px;align-items:flex-start;margin:8px 0"><input type="checkbox" name="consent" value="1" style="margin-top:5px"> <span>I agree to the <a href="privacy.php" target="_blank">Privacy Notice</a>.</span></label>
<button>Register</button></form>
<?php include 'includes/footer.php';
