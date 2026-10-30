<?php require 'config.php';need('client');$u=user();$msg='';
$c=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id=? AND status='completed'");$c->execute([$u['id']]);$ok=$c->fetchColumn()>0;
$ex=$pdo->prepare('SELECT * FROM reviews WHERE user_id=?');$ex->execute([$u['id']]);$my=$ex->fetch();
if($ok&&$_SERVER['REQUEST_METHOD']==='POST'){$r=max(1,min(5,(int)$_POST['rating']));$cm=trim($_POST['comment']);
 if($my)$pdo->prepare('UPDATE reviews SET rating=?,comment=? WHERE user_id=?')->execute([$r,$cm,$u['id']]);
 else $pdo->prepare('INSERT INTO reviews(user_id,rating,comment) VALUES(?,?,?)')->execute([$u['id'],$r,$cm]);
 $msg='Thank you for your review!';$ex->execute([$u['id']]);$my=$ex->fetch();}
$title='Write a Review';include 'includes/header.php';?>
<h1>Write a Review</h1>
<?php if(!$ok):?><p>You can leave a review once your appointment has been completed. Thank you for your patience!</p>
<?php else:?><form method="post" class="card"><p class="ok"><?=e($msg)?></p><p>How was your experience?</p>
<div class="stars"><?php for($i=5;$i>=1;$i--):?><input type="radio" id="r<?=$i?>" name="rating" value="<?=$i?>" <?=($my['rating']??5)==$i?'checked':''?>><label for="r<?=$i?>">★</label><?php endfor;?></div>
<textarea name="comment" rows="4" placeholder="Tell us about your nails and the service" required><?=e($my['comment']??'')?></textarea>
<button>Submit review</button></form><?php endif;?>
<?php include 'includes/footer.php';
