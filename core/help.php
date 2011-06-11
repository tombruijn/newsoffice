<?php
$login_title = "Help | NewsOffice";
$login_content = "
<div class='main'>
	<h1>Help</h1>
	Need help or having a question?<br> Click on a question or statement below.
	<ul>
		<li><a href='#forgot'>I forgot my username or password!</a></li>
		<li><a href='#email'>I have reset my password, but I am not getting an email.</a></li>
		<li><a href='#register'>Why register?</a></li>
	</ul>
</div>

<div class='main'>
	<a href='".url_build('dashboard-main')."'>&laquo; Go back to the login screen</a>
</div>

<div class='main'>
	<h2><a name='forgot'>I forgot my username or password!</a></h2>
	Please go to this page, <a href='".url_build('recovery')."'>Forgot password?</a>, where you can reset your password.<br>
	<div class='less_important'>You do need to remember the email address you registered with, this is to prevent stealing accounts.</div>
</div>

<div class='main'>
	<h2><a name='email'>I have reset my password, but I am not getting an email.</a></h2>
	The email send to you might be from an non-existing email account. Please check your spam box when you are not recieving an email.
</div>

<div class='main'>
	<h2><a name='register'>Why register?</a></h2>
	When you register you have your own username where you can place comments with. When an Administrator gives you acces you can also create, edit, delete news posts and much more!
	<div class='less_important'>You can not login on this page with your account if it's an Commenter account, an Administrator will have to grant you access to the other features.</div>
</div>
";
?>