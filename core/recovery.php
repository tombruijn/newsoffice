<?php
$login_title = "Forgot password | NewsOffice";
$show_content = true;
$login_content = "<div class='main'><h1>Forgot password</h1>";
if($_POST['n_reset'])
{
	$login_title = "Forgot password email send | NewsOffice";
	$openU = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr');
		$openU->readfile();
		$nzr_keep = $openU->set_store();
		$openU->search(array('email'=>$_POST['email']),false,1);
		if($openU->amount_rows>0)
		{
			$found_info = $openU->content[0];
			$openU->set_import($nzr_keep);
			$pw = 'reset|'.md5(sha1(install_id.date('Y-m-d H:i:s').'Some random text'.$found_info['display-name'].$found_info['avatar'].$found_info['description']).install_id.date('Y-m-d H:i:s').'Some random text'.$found_info['display-name'].$found_info['avatar'].$found_info['description']);
			$openU->save(array('password'=>$pw),array('email'=>$_POST['email']),1);
			
			$login_content .= "There is an account registered with this email address.<br>
				<br>
				Username: <span class='important'>".$found_info['username']."</span><br>
				Email address: <span class='important'>".$_POST['email']."</span>
			</div>
			
			<div class='main'>";
			//Send email
			$subject = "NewsOffice mail: Password reset";
			$message = "Dear ".$found_info['username'];
			if($found_info['username']!==$found_info['display-name'])
			{
				$message .= " or ".$found_info['display-name'];
			}
			$message .= ",\n\nThis email is send because you have an account registered on the NewsOffice application at ".$no_config['acp_url']." and you or an Administrator has requested a password change.\n\nTo reset your password go to the link below and enter your new password. Then you will be able to login again with your new password and your username.\n\n".$no_config['acp_url']."?name=reset&id=".$found_info['reset_password']."\n\nThank you for using NewsOffice,\n\nNewsOffice Administrator at ".$no_config['acp_url'].".";
			$message = wordwrap($message, 70);
			$header = "From: NewsOffice <".$no_config['acp_email'].">\r\n"; //optional headerfields
			//NewsOffice 2.0.6 Beta Workaround
				ini_set('sendmail_from', $no_config['acp_email']); //Possible windows fix for sending mails
			if(mail($found_info['email'],$subject,$message,$header)!==false)
			{
				//Actually save the new information
				$login_content .= "<h1>An email has been send</h1>
					An email has been send to <span class='important'>".$_POST['email']."</span> from <span class='important'>".$no_config['acp_email']."</span> with a confirmation code in a link which you will have to open in order to reset your password.
				</div>
				
				<div class='main'>
					<a href='".url_build('dashboard-main')."'>&laquo; Go back to the login screen</a>
				";
			}
			else
			{
				$login_content .= "<h1>No email has been send</h1>
					An email could not be send to <span class='important'>".$_POST['email']."</span> from <span class='important'>".$no_config['acp_email']."</span>. Something went wrong while sending the email through PHP. Try again and/or contact your server Administrator.
				";
			}
		}
		else
		{
			$login_content .= "There is no account registered with this email address.<br>
			<br>
			Email address: ".$_POST['email'];
		}
		
		$login_content .= "
		</div>
		
		<div class='main'>
			<a href='".url_build('dashboard-main')."'>&laquo; Go back to the login screen</a>
		";
	$openU->close();
	$show_content = false;
}

if($show_content==true)
{
	$login_content .= "
		To change your password please fill in your email address below. An id to reset your password will be send to your email address.
	</div>
	
	<div class='main'>
		<h2>Your email address</h2>
		<input type='text' name='email' value='".no_convert_field($_POST['email'])."' class='login-box'><br>
		<div class='less_important'>You can only use an email address registered to your account.</div>
		<div style='text-align: center;'><input type='submit' name='n_reset' value=' Start password change '></div>
	</div>
	
	<div class='main'>
		<a href='".url_build('dashboard-main')."'>&laquo; Go back to the login screen</a>
	</div>
	
	<div class='main'>
		<h2>Notes</h2>
		<div class='less_important'>
		<ul>
			<li>Click on the link in the email you will recieve to return to this screen and change your password.</li>
			<li>If you forgot your password we can not send you your original password as it is encrypted and can not be reverted into your password.</li>
			<li>The email send to you could be from an non-existing email account. Please check your spam box when you are not recieving an email.</li>
		</ul>
		</div>
	</div>
	";
}
?>