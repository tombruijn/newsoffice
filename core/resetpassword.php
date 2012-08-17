<?php
$login_title = "Reset password | NewsOffice";
$login_content = "<div class='main'>";
if(empty($id))
{
	$login_content .= "<h1>Reset password</h1>
	We can't find the id in the url. Please use the link send to you in your reset password email.";
}
else
{
	$show_content = true;
	$login_content .= "<h1>Reset password</h1>";
	
	$openU = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr');
		$openU->readfile();
		$nzr_keep = $openU->set_store();
		$openU->search(array('password'=>'reset|'.$id),false,1);
		if($openU->amount_rows>0)
		{
			$found_info = $openU->content[0];
		}
	if(empty($found_info))
	{
		$login_content .= "I can not find any user with a requested password change with this id. Please use the link send to you in your reset password email.";
	}
	else
	{
		if($_POST['n_reset'])
		{
			if(empty($_POST['password_new']) || empty($_POST['password_confirm']) || $_POST['password_new']!==$_POST['password_confirm'])
			{
				$error[] = 'You forgot to fill in a new password or the passwords you entered did not match';
			}
			if(empty($error))
			{
				$show_content = false;
				$login_title = "Password change succesfull | NewsOffice";
				$login_content .= "Password succesfully changed.<br>
				<br>
				A confirmation email has been send to <span class='important'>".$found_info['email']."</span> from <span class='important'>".$no_config['acp_email']."</span> with your new password.";
				//Save
				$openU->set_import($nzr_keep);
				$openU->save(array('password'=>md5(sha1($_POST['password_new'].install_id).install_id)),array('password'=>'reset|'.$id),1);
				
				//Send email
				$subject = "NewsOffice mail: Password succesfully reset";
				$message = "Dear ".$found_info['username']." or ".$found_info['display-name'].",\n\nThis email is sent to you because you have an account registered on the NewsOffice application at ".$no_config['acp_url'].".\nYou or an Administrator has changed your password.\n\nYour new login details are:\nUsername: ".$found_info['username']."\nPassword: ".$_POST['password_new']."\nThank you for using NewsOffice,\n\nNewsOffice Administrator at ".$no_config['acp_url'].".";
				$header = "From: NewsOffice <".$no_config['acp_email'].">\r\n"; //optional headerfields
				//NewsOffice 2.0.6 Beta Workaround
					ini_set('sendmail_from', $no_config['acp_email']); //Possible windows fix for sending mails
				$to = $found_info['email'];
				mail($to,$subject,$message,$header);
			}
		}

		if($show_content==true)
		{
			if(!empty($error))
			{
				$login_content .= "</div>
				
				<div class='error'><h1>Errors</h1><ul>";
				foreach($error as $error_object)
				{
					$login_content .= "<li>".$error_object."</li>";
				}
				$login_content .= "</ul>
				</div>
				<div class='main'>";
			}
			
			$login_content .= "
<form action='' method='post'>
				<h2>Your new password</h2>
				<input type='password' name='password_new' class='login-box'><br>
				<h2>Confirm your new password</h2>
				<input type='password' name='password_confirm' class='login-box'><br>
				<div style='text-align: center;'><input type='submit' name='n_reset' value=' Change password '></div>
</form>
			";
		}
	}
	$openU->close();
}
$login_content .= "
	</div>
	<div class='main'>
		<a href='".url_build('dashboard-main')."'>« Go back to the login screen</a>
	</div>
";
?>
