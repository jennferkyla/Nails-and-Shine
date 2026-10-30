<?php $title='Privacy Notice';include 'includes/header.php';$sn=setting('studio_name');?>
<div class="legal"><h1>Privacy Notice</h1>
<p><?=e($sn)?> respects your privacy and follows the Data Privacy Act of 2012 (Republic Act No. 10173). This notice explains how we handle your personal information.</p>
<h2>What we collect</h2><p>Your name, email address, mobile number (optional), your appointment details (service, date, time, add-ons, nail technician), reviews you write, and your account login (password is stored in encrypted form).</p>
<h2>Why we collect it</h2><p>To book and manage your appointments, to contact you about them (confirmations, reminders, cancellations), to let you manage your account, and to improve our service.</p>
<h2>Who can see it</h2><p>Only the owner and the nail technicians of <?=e($sn)?> can see your booking details. We do not sell your information. Emails are sent through our email provider (Gmail), which processes them to deliver the message. Your review (with your name) may be shown on our website.</p>
<h2>How long we keep it</h2><p>We keep your information for as long as your account is active or as needed to run the business. You may ask us to delete your account and information.</p>
<h2>Your rights</h2><p>You may ask to see, correct, or delete your personal information, to object to its use, and to complain to the National Privacy Commission (privacy.gov.ph) if you believe your rights were violated.</p>
<h2>How we protect it</h2><p>Passwords are encrypted, access to the admin pages is restricted, and our website uses security checks on every form.</p>
<h2>Contact</h2><p><?=e(setting('owner_name'))?>, <?=e($sn)?>, <?=e(setting('address'))?>. Mobile: <?=e(setting('phone'))?>.</p></div>
<?php include 'includes/footer.php';
