<?php require 'config.php';$err='';$info='';
if(user())go(user()['role']==='client'?'index.php':'admin/index.php');
if(isset($_GET['registered']))$info='Account created. You can now log in.';
if(isset($_GET['reset']))$info='Your password was changed. You can now log in.';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $em=strtolower(trim($_POST['email']??''));
 if(throttled($em))$err='Too many failed attempts. Please wait 15 minutes and try again.';
 else{
  $s=$pdo->prepare('SELECT u.*,r.role_name FROM users u JOIN roles r ON r.role_id=u.role_id WHERE u.email=?');$s->execute([$em]);$r=$s->fetch();
  if($r&&password_verify($_POST['password']??'',$r['password_hash'])){
   clear_attempts($em);session_regenerate_id(true);
   $_SESSION['u']=['id'=>$r['user_id'],'name'=>$r['full_name'],'email'=>$r['email'],'role'=>$r['role_name']];
   go($r['role_name']==='client'?'index.php':'admin/index.php');}
  log_fail($em);$err='Wrong email or password.';}}
$title='Login';include 'includes/header.php';?>
<form method="post" class="card"><h2>Login</h2>
<?php if($info):?><p class="alert ok"><?=e($info)?></p><?php endif;?><?php if($err):?><p class="alert err"><?=e($err)?></p><?php endif;?>
<input name="email" type="email" placeholder="Email" required autocomplete="username"><input name="password" type="password" placeholder="Password" required autocomplete="current-password"><button>Login</button>
<p><small><a href="forgot.php">Forgot password?</a> &middot; <a href="register.php">Create an account</a></small></p></form>
<?php include 'includes/footer.php';
