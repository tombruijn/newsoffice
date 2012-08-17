<?php
$login_title = "Activate account | NewsOffice";
$login_content = "<div class='main'><h1>Activate account</h1>";
$show_content = true;
if(!empty($id))
{
	//Checkup
	$openU = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr');
		$openU->readfile();
		foreach($openU->content as $object)
		{
			if(substr_count($object['password'],'activate|')>0)
			{
				$pw = str_replace('activate|','',$object['password']);
				if(md5(sha1($pw.install_id).install_id)==$id)
				{
					$activate_user = $object;
					$activate_user['new_password'] = $pw;
				}
			}
		}
		if(empty($activate_user))
		{
			$error = "No user could be found with this activation ID.";
		}
		else
		{
			$openU->save(array('password'=>$activate_user['new_password']),array('id'=>$activate_user['id']),1);
			if($openU->result==true)
			{
				$login_content .= "Your account has been succesfully activated, <span".no_group_color($activate_user['role']).">".$activate_user['username']."</span>.<br>
					<br>
					You can now login to NewsOffice.<br>";
				//Send email
				$subject = "NewsOffice mail: Account activated!";
				$message = "Dear ".$activate_user['username'].",\n\nThis email is sent because you have just activated your account on the NewsOffice application at ".$no_config['acp_url'].".\n\nYour login information has been sent in a previous email.\n\nYou can login on this website: ".$no_config['acp_url']."\n\nThank you for using NewsOffice,\n\nNewsOffice Administrator at ".$no_config['acp_email'].".";
				$header = "From: NewsOffice <".$no_config['acp_email'].">\r\n"; //optional headerfields
				//NewsOffice 2.0.6 Beta Workaround
					ini_set('sendmail_from', $no_config['acp_email']); //Possible windows fix for sending mails
				$to = $activate_user['email'];
				mail($to,$subject,$message,$header);
			}
			else
			{
				$error = "Error, The user could not be saved. Contact your Administrator.";
			}
		}
	$openU->close();
}
else
{
	$error = "Error, no id given.";
}

if(!empty($error))
{
	$login_content .= "</div><div class='error'>".$error;
}

$login_content .= "</div>
	<div class='main'>
	<a href='".url_build('dashboard-main')."'>&laquo; Go back to the login screen</a>";
?>