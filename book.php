<?php require 'config.php';require 'mailer.php';$u=user();$msg='';$note='';$ok=false;
$sv=$pdo->query('SELECT * FROM services WHERE is_active=1 ORDER BY service_id')->fetchAll();
$ad=$pdo->query('SELECT * FROM addons WHERE is_active=1 ORDER BY addon_id')->fetchAll();
$techs=tech_list();
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!empty($_POST['website']))exit;
 $sid=(int)($_POST['service']??0);$date=$_POST['date']??'';$t=$_POST['time']??'';$tid=(int)($_POST['technician']??0);
 $gname=trim($_POST['gname']??'');$gemail=trim($_POST['gemail']??'');$gphone=trim($_POST['gphone']??'');
 $q=$pdo->prepare('SELECT name,duration_min,price FROM services WHERE service_id=? AND is_active=1');$q->execute([$sid]);$sv1=$q->fetch();
 $em=$u['email']??$gemail;
 if(!$sv1||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!preg_match('/^\d{2}:\d{2}$/',$t))$msg='Please choose a service, date, and time.';
 elseif(!$u&&(!$gname||!filter_var($gemail,FILTER_VALIDATE_EMAIL)))$msg='Please enter your name and a valid email address.';
 elseif(!$u&&empty($_POST['consent']))$msg='Please agree to the Privacy Notice to continue.';
 else{
  $c=$pdo->prepare("SELECT COUNT(*) FROM appointments a LEFT JOIN users uu ON uu.user_id=a.user_id WHERE COALESCE(uu.email,a.guest_email)=? AND a.status IN('pending','confirmed') AND a.start_time>NOW()");$c->execute([$em]);
  if((int)$c->fetchColumn()>=3)$msg='You already have 3 upcoming appointments. Please wait until one is completed or cancel one first.';
  else{
   $pdo->beginTransaction();
   $pdo->query('SELECT technician_id FROM technicians FOR UPDATE')->fetchAll();
   $tech=pick_tech((int)$sv1['duration_min'],$date,$t,$tid);
   if(!$tech){$pdo->rollBack();$msg='Sorry, that time was just taken. Please pick another time.';}
   else{
    $st=strtotime("$date $t");$en=$st+(int)$sv1['duration_min']*60;
    $dep=setting('deposit_enabled')==='1'?(float)setting('deposit_amount',0):0;
    $pdo->prepare('INSERT INTO appointments(user_id,guest_name,guest_email,guest_phone,service_id,technician_id,start_time,end_time,deposit_amount) VALUES(?,?,?,?,?,?,?,?,?)')
     ->execute([$u['id']??null,$u?null:$gname,$u?null:$gemail,$u?null:($gphone?:null),$sid,$tech,date('Y-m-d H:i:s',$st),date('Y-m-d H:i:s',$en),$dep]);
    $aid=$pdo->lastInsertId();$total=(float)$sv1['price'];
    foreach((array)($_POST['addons']??[]) as $x){$p=$pdo->prepare('SELECT price FROM addons WHERE addon_id=? AND is_active=1');$p->execute([(int)$x]);$pr=$p->fetchColumn();
     if($pr!==false){$pdo->prepare('INSERT IGNORE INTO appointment_addons(appointment_id,addon_id) VALUES(?,?)')->execute([$aid,(int)$x]);$total+=(float)$pr;}}
    $pdo->commit();
    $tn=$pdo->prepare('SELECT name FROM technicians WHERE technician_id=?');$tn->execute([$tech]);$tname=(string)$tn->fetchColumn();
    $depTxt='';
    if($dep>0)$depTxt="Reservation fee: ".peso($dep)."\nPlease send it via GCash to ".setting('gcash_number')." (".setting('gcash_name').") and show your receipt to us. ".setting('deposit_note');
    $sent=send_mail($em,'Booking received - '.setting('studio_name'),"Hi".($u?' '.$u['name']:($gname?' '.$gname:'')).",\n\nThank you for booking with ".setting('studio_name').".\n\nService: ".$sv1['name']."\nNail technician: ".$tname."\nDate and time: ".date('l, F j, Y g:i A',$st)."\nEstimated total: ".peso($total)." (final price depends on the design)\nStatus: Pending - we will email you once it is confirmed.\n".($depTxt?"\n".$depTxt."\n":'')."\nLocation: ".setting('address')."\nContact: ".setting('phone')."\n\nSee you soon!");
    $ok=true;$msg='Booked! '.date('l, F j, Y g:i A',$st).' with '.$tname.'. Estimated total: '.peso($total).' (final price depends on the design). '.($sent?'A confirmation was sent to '.$em.'.':'We could not send the confirmation email, but your booking is saved.');
    $note=$depTxt;
   }}}}
$title='Book an appointment';include 'includes/header.php';?>
<form method="post" class="card" style="max-width:560px"><h2>Book an appointment</h2>
<?php if($msg):?><p class="alert <?=$ok?'ok':'err'?>"><?=e($msg)?></p><?php endif;?>
<?php if($note):?><p class="alert"><?=nl2br(e($note))?></p><?php endif;?>
<input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="display:none">
<label for="s">Service</label>
<select name="service" id="s" required><?php foreach($sv as $s):?><option value="<?=$s['service_id']?>" data-p="<?=$s['price']?>" <?=(int)($_GET['service']??0)===(int)$s['service_id']?'selected':''?>><?=e($s['name'])?> - <?=peso($s['price'])?> (<?=$s['duration_min']?> min)</option><?php endforeach;?></select>
<label>Nail technician</label>
<div class="pills"><label><input type="radio" name="technician" value="0" checked><span>Any available</span></label><?php foreach($techs as $t):?><label><input type="radio" name="technician" value="<?=$t['technician_id']?>"><span><?=e($t['name'])?></span></label><?php endforeach;?></div>
<label for="d">Date</label>
<input type="date" name="date" id="d" min="<?=date('Y-m-d')?>" max="<?=date('Y-m-d',strtotime('+'.(int)setting('max_advance_days',60).' days'))?>" required>
<label for="t">Time</label>
<select name="time" id="t" required><option value="">Pick a date first</option></select>
<p><b>Add-ons</b> (optional)</p><div class="chk"><?php foreach($ad as $a):?><label><input type="checkbox" name="addons[]" value="<?=$a['addon_id']?>" data-p="<?=$a['price']?>"> <?=e($a['name'])?> (+<?=peso($a['price'])?>)</label><?php endforeach;?></div>
<p class="total">Estimated total: <b id="tot"></b></p>
<?php if(setting('deposit_enabled')==='1'):?><p class="alert">A reservation fee of <?=peso(setting('deposit_amount',0))?> is required to secure your slot. Payment instructions will be shown after you book.</p><?php endif;?>
<?php if(!$u):?><label for="gn">Your name</label><input name="gname" id="gn" maxlength="100"><label for="ge">Email</label><input name="gemail" id="ge" type="email" maxlength="100"><label for="gp">Mobile number (optional)</label><input name="gphone" id="gp" maxlength="20">
<label style="display:flex;gap:8px;align-items:flex-start"><input type="checkbox" name="consent" value="1" style="margin-top:5px"> <span>I agree to the <a href="privacy.php" target="_blank">Privacy Notice</a>.</span></label>
<small>Or <a href="login.php">login</a> / <a href="register.php">register</a></small><?php endif;?>
<button>Confirm booking</button></form>
<script>
const s=document.getElementById('s'),d=document.getElementById('d'),t=document.getElementById('t'),tot=document.getElementById('tot');
const tech=()=>document.querySelector('input[name=technician]:checked').value;
async function load(){if(!d.value)return;t.innerHTML='<option value="">Loading...</option>';
 try{const r=await (await fetch('api/slots.php?service='+s.value+'&date='+d.value+'&technician='+tech())).json();
 t.innerHTML=r.length?r.map(x=>`<option>${x}</option>`).join(''):'<option value="">No open times - try another date or technician</option>';}
 catch(e){t.innerHTML='<option value="">Could not load times</option>';}}
function calc(){let n=+s.selectedOptions[0].dataset.p;document.querySelectorAll('.chk input:checked').forEach(c=>n+=+c.dataset.p);tot.textContent='₱'+n.toLocaleString();}
s.onchange=()=>{load();calc()};d.onchange=load;document.querySelectorAll('input[name=technician]').forEach(r=>r.onchange=load);
document.querySelectorAll('.chk input').forEach(c=>c.onchange=calc);calc();
</script><?php include 'includes/footer.php';
