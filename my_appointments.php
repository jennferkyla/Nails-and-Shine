<?php require 'config.php';need('client');$u=user();$err='';
if(isset($_POST['cancel'])){$a=$pdo->prepare('SELECT start_time,status FROM appointments WHERE appointment_id=? AND user_id=?');$a->execute([(int)$_POST['cancel'],$u['id']]);$r=$a->fetch();
 if($r&&in_array($r['status'],['pending','confirmed'],true)&&strtotime($r['start_time'])-time()>=(int)setting('cancel_hours',24)*3600)$pdo->prepare("UPDATE appointments SET status='cancelled' WHERE appointment_id=?")->execute([(int)$_POST['cancel']]);
 else $err='This appointment can no longer be cancelled online (policy: at least '.(int)setting('cancel_hours',24).' hours before). Please contact the studio at '.setting('phone').'.';}
$q=$pdo->prepare('SELECT a.*,s.name,t.name tname FROM appointments a JOIN services s ON s.service_id=a.service_id LEFT JOIN technicians t ON t.technician_id=a.technician_id WHERE a.user_id=? ORDER BY a.start_time DESC');$q->execute([$u['id']]);
$title='My Appointments';include 'includes/header.php';?>
<h1>My Appointments</h1><?php if($err):?><p class="alert err"><?=e($err)?></p><?php endif;?>
<table><tr><th>Service</th><th>Technician</th><th>When</th><th>Status</th><th></th></tr>
<?php foreach($q as $r):?><tr><td><?=e($r['name'])?></td><td><?=e($r['tname']??'-')?></td><td><?=date('M j, Y g:i A',strtotime($r['start_time']))?></td>
<td><span class="tag"><?=e($r['status'])?></span><?php if((float)$r['deposit_amount']>0&&in_array($r['status'],['pending','confirmed'],true)):?><br><small><?=$r['deposit_paid']?'Reservation fee received':'Reservation fee '.peso($r['deposit_amount']).' - unpaid'?></small><?php endif;?></td>
<td><?php if(in_array($r['status'],['pending','confirmed'],true)):?><form method="post"><button name="cancel" value="<?=$r['appointment_id']?>" onclick="return confirm('Cancel this appointment?')">Cancel</button></form><?php endif;?></td></tr><?php endforeach;?></table>
<?php include 'includes/footer.php';
