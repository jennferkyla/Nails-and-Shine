<?php include 'includes/header.php';
$sv=$pdo->query('SELECT * FROM services WHERE is_active=1 ORDER BY service_id')->fetchAll();
$by=[];foreach($sv as $s)$by[$s['category']][]=$s;
$ad=$pdo->query('SELECT * FROM addons WHERE is_active=1 ORDER BY addon_id')->fetchAll();
$ds=$pdo->query('SELECT * FROM designs ORDER BY created_at DESC')->fetchAll();
$groups=[];foreach($ds as $d){$k=mb_strtolower(trim($d['style']));$groups[$k]['name']??=trim($d['style']);$groups[$k]['items'][]=$d;}
ksort($groups);
$rv=$pdo->query('SELECT r.*,u.full_name FROM reviews r LEFT JOIN users u USING(user_id) ORDER BY r.created_at DESC LIMIT 6')->fetchAll();
$techs=tech_list();?>

<section class="hero"><h1><?=e(setting('studio_name'))?></h1><p class="script">Where every set tells a story</p><a class="btn" href="book.php">Book Now</a></section>

<h2 class="center script-title">Pricelist</h2>
<div class="price-wrap">
 <div>
  <?php foreach($by as $cat=>$items):?>
  <div class="pcard"><div class="bar"><?=e($cat==='Nail Services'?'Nail Services (Plain)':$cat)?></div><ul>
   <?php foreach($items as $s):?><li><a href="book.php?service=<?=$s['service_id']?>"><?=e($s['name'])?></a><small><?=$s['duration_min']?> min</small><i></i><b><?=peso($s['price'])?></b></li><?php endforeach;?>
  </ul></div><?php endforeach;?>
  <p class="note">Prices will depend on the intricacy and detail of the nail inspo.</p>
 </div>
 <div class="pcard"><div class="bar">Add Ons</div><ul class="hearts">
  <?php foreach($ad as $a):?><li><span><?=e($a['name'])?></span><i></i><b><?=peso($a['price'])?></b></li><?php endforeach;?>
 </ul></div>
</div>
<p class="center"><a class="btn" href="book.php">Book an appointment</a></p>

<?php if($techs):?><h1>Our Nail Technicians</h1><div class="techs"><?php foreach($techs as $t):?><div class="tcard"><?=e($t['name'])?></div><?php endforeach;?></div><?php endif;?>

<h1>Design Gallery</h1>
<div class="filters"><button type="button" data-f="all">All</button><?php foreach($groups as $k=>$g):?><button type="button" data-f="<?=e($k)?>"><?=e($g['name'])?></button><?php endforeach;?></div>
<?php foreach($groups as $k=>$g):?>
<section class="gal" data-style="<?=e($k)?>"><h2><?=e($g['name'])?></h2>
 <div class="grid"><?php foreach($g['items'] as $d):?><figure><img src="<?=BASE.'/'.e($d['image_path'])?>" alt="<?=e($d['title'])?>" loading="lazy">
 <figcaption><?=e($d['title'])?><br><a class="btn" href="book.php?service=<?=(int)$d['service_id']?>">Book Now</a></figcaption></figure><?php endforeach;?></div>
</section><?php endforeach;?>

<h1>Reviews</h1><?php if(!$rv):?><p class="muted">No reviews yet.</p><?php endif;?><?php foreach($rv as $r):?><div class="card"><?=str_repeat('★',(int)$r['rating'])?> <b><?=e($r['full_name']??'Guest')?></b><p><?=e($r['comment'])?></p></div><?php endforeach;?>

<h1>About &amp; Contact</h1>
<div class="card wide"><p><b><?=e(setting('studio_name'))?></b><br>Owner: <?=e(setting('owner_name'))?><br>Address: <?=e(setting('address'))?><br>Contact number: <?=e(setting('phone'))?><br>
<?php if(setting('facebook_url')):?>Facebook: <a href="<?=e(setting('facebook_url'))?>" target="_blank" rel="noopener"><?=e(setting('facebook_name','Facebook'))?></a><br><?php elseif(setting('facebook_name')):?>Facebook: <?=e(setting('facebook_name'))?><br><?php endif;?>
Hours: <?=e(setting('hours_text'))?></p>
<p>Studio policy: please cancel at least <?=(int)setting('cancel_hours',24)?> hours before your appointment so that another client can take the time.<?php if(setting('deposit_enabled')==='1'):?> A reservation fee of <?=peso(setting('deposit_amount',0))?> is required to secure your slot.<?php endif;?></p></div>

<div id="lb"><img alt=""></div>
<script>
document.querySelectorAll('.filters button').forEach(b=>b.onclick=()=>document.querySelectorAll('.gal').forEach(g=>g.style.display=(b.dataset.f==='all'||g.dataset.style===b.dataset.f)?'':'none'));
const lb=document.getElementById('lb');document.querySelectorAll('figure img').forEach(i=>i.onclick=()=>{lb.firstChild.src=i.src;lb.style.display='flex'});lb.onclick=()=>lb.style.display='none';
</script><?php include 'includes/footer.php';
