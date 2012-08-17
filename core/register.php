<?php
$login_title = "Register account | NewsOffice";
$login_content = "<div class='main'><h1>Register</h1>";
$show_content = true;
if($_POST['n_register'])
{
	//Checkup
	$openU = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr');
		$openU->readfile();
		$nzr_keep = $openU->set_store();
		//Find username
			$openU->search(array('username'=>$_POST['n_username']));
			if($openU->amount_rows>0)
			{
				$found_username = true;
			}
		//Find display-name
			$openU->set_import($nzr_keep);
			$openU->search(array('display-name'=>$_POST['n_username']));
			if($openU->amount_rows>0)
			{
				$found_displayname = true;
			}
		//Find email
			$openU->set_import($nzr_keep);
			$openU->search(array('email'=>$_POST['n_email']));
			if($openU->amount_rows>0)
			{
				$found_email = true;
			}
	$openU->close();
	//Valid username?
		if(empty($_POST['n_username']) || $found_username==true || $found_displayname==true)
		{
			$error[] = 'Your username is not valid or is already in use.';
		}
	//Valid email?
		if(empty($_POST['n_email']) || filter_var($_POST['n_email'], FILTER_VALIDATE_EMAIL)==false || $found_email==true)
		{
			$error[] = 'Your email address is not valid or is already in use.';
		}
	//Valid password?
		if(empty($_POST['n_password_new']) || $_POST['n_password_new']!==$_POST['n_password_confirm'])
		{
			$error[] = 'You did not enter a password or they did not match.';
		}
		else
		{
			$password = md5(sha1($_POST['n_password_new'].install_id).install_id);
		}
	
	//Everything okay?
	if(empty($error))
	{
		//Save user
		$saveU = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr');
			$saveU->readfile();
			$saveU->save(array(
				'username'=>$_POST['n_username'],
				'display-name'=>$_POST['n_username'],
				'email'=>$_POST['n_email'],
				'password'=>'activate|'.$password,
				'role'=>$no_config['set_default_group']
			),'new');

		if($saveU->result==false)
		{
			$error[] = "Could not save user information to the file. The file is not writable. Please contact the administrator; He/She can make the file writable. The file in question can be found in the Administration panel under: Support -> System Status.";
		}
		else
		{
			//Send email
			$subject = "NewsOffice mail: Account registered!";
			$message = "Dear ".$_POST['n_username'].",

This email has been sent to you because you have just registered an account on the NewsOffice application at ".$no_config['acp_url'].".

This is your login information which you will need to login to NewsOffice.
Username: ".$_POST['n_username']."
Password: ".$_POST['n_password_new']."

To login you first need to activate your account by going to this website:
".$no_config['acp_url']."?name=activate&id=".md5(sha1($password.install_id).install_id)."

You can login on this website: ".$no_config['acp_url']."

Thank you for using NewsOffice,

NewsOffice Administrator at ".$no_config['acp_url'].".";
			$to = $_POST['n_email'];
			$header = "From: NewsOffice <".$no_config['acp_email'].">\r\n"; //optional headerfields
			//NewsOffice 2.0.6 Beta Workaround
				ini_set('sendmail_from', $no_config['acp_email']); //Possible fix for Windows for sending mails
			if(mail($to,$subject,$message,$header)==false)
			{
				$error[] = "Could not send the registration email. This is needed to activate your account. Please contact the administrator; He/She can activate your acount. Your account is registered however.";
			}
			else
			{
				$show_content = false;
				$login_content .= "Your account has been succesfully registered and you can now login to place comments and, if active, more.<br><br>
				An email has been send to <span class='important'>".$_POST['n_email']."</span> from <span class='important'>".$no_config['acp_email']."</span> with your login details.</div>
				<div class='main'>
				<a href='".url_build('dashboard-main')."'>&laquo; Go back to the login screen</a>";
				$login_content .= "</div>";
			}
		}
		$saveU->close();
	}
}

if($show_content==true)
{
	$login_content .= "
		<div class='less_important'>With an account you can edit your comments and, when approved by an Administrator, have access to the management features of NewsOffice.</div>
	</div>

	<div class='main'>
		<a href='".url_build('dashboard-main')."'>&laquo; Go back to the login screen</a>
	</div>
	";
	
	if(!empty($error))
	{
		$login_content .= "<div class='error'>One or more errors occured while registering.<ul>";
		foreach($error as $object)
		{
			$login_content .= "<li>".$object."</li>";
		}
		$login_content .= "</ul></div>";
	}
	
	$login_content .= "
	<div class='main'>
		<form action='' method='post'>
			<h2>Username</h2>
			<input type='text' name='n_username' value='".no_convert_field($_POST['n_username'])."' class='login-box'><br>
			<h2>Email address</h2>
			<input type='text' name='n_email' value='".no_convert_field($_POST['n_email'])."' class='login-box'><br>
			<h2>Password</h2>
			<input type='password' name='n_password_new' class='login-box'><br>
			<h2>Confirm password</h2>
			<input type='password' name='n_password_confirm' class='login-box'><br>
			<div style='text-align: center;'><input type='submit' name='n_register' value=' Register '></div>
		</form>
	</div>

	<div class='main'>
		<h2>Notes</h2>
		<div class='less_important'>
		<ul>
			<li>To use your account you will have to activate it.</li>
			<li>Click on the link in the email you will recieve to activate your account.</li>
			<li>The email sent to you might be from a non-existing email account. Please check your spam box when you are not recieving an email.</li>
		</ul>
		</div>
	</div>
	";
}
?>