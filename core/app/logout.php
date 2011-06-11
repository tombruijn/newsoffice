<?php
if(array_key_exists('n_logout',$_POST)==true)
{
	noUser::logout();
	$page_content = "<h1>Logout</h1>You have been succesfully logged out from NewsOffice.<br>
	You should be redirect, if not <a href='".$no_config['acp_url']."'>click here</a>.<br>
	<script type='text/javascript'>window.location = '".$no_config['acp_url']."'</script>";
}
else
{
	$page_content = "<h1>Logout</h1>
	Are you sure you want to logout?<br>
	<div class='less_important'>You can only use any of the functions of this panel, or the news page(s), untill you have logged in again.</div>
	<br>
	<input type='submit' value=' Logout from NewsOffice ' name='n_logout'>";
}
?>