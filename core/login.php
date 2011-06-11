<?php
$username = $_SESSION[install_id]['user']['username'];
$password = $_SESSION[install_id]['user']['password'];
$show_form = true;
//Check for login
	$allowed = no_is_allowed('newsoffice',$users[$username]['role']);
	if(!empty($username) && !empty($password))
	{
		if(
			!empty($users[$username]['password']) && 
			!empty($users[$username]['password']) && 
			md5(sha1(md5(install_id.$users[$username]['password']).install_id).install_id)==$password
		)
		{
			if($allowed==false)
			{
				//Not allowed
				$login_error = 'You are not allowed to this page, you do not have the correct permission.';
			}
			else
			{
				//Login is correct
				$show_form = false;
			}
		}
		else
		{
			//Corrupt session data
			session_destroy();
			$login_error = 'Your session data is incorrect.<br>Please <a href="?name=login">login again</a>.';
		}
	}

//Show welcome page
if(empty($username) || empty($password) || $show_form==true)
{
	$login_render = new noThemeExternal();
	//Show alternate pages
	if($name=='recovery')
	{
		include(newsoffice_directory.$no_config['dir_core'].'recovery.php');
	}
	elseif($name=='reset')
	{
		include(newsoffice_directory.$no_config['dir_core'].'resetpassword.php');
	}
	elseif($name=='register')
	{
		include(newsoffice_directory.$no_config['dir_core'].'register.php');
	}
	elseif($name=='help')
	{
		include(newsoffice_directory.$no_config['dir_core'].'help.php');
	}
	elseif($name=='activate')
	{
		include(newsoffice_directory.$no_config['dir_core'].'activate.php');
	}
	elseif(empty($users[$username]['password']) || $users[$username]['password']!==$password)
	{
		//Login screen
		if(array_key_exists('n_login',$_POST)==true)
		{
			$username = $_POST['n_username'];
			$password = md5(sha1($_POST['n_password'].install_id).install_id);
			if(!empty($users[$username]['password']) && !empty($users[$username]['password']) && $users[$username]['password']==$password)
			{
				$allowed = no_is_allowed('newsoffice',$users[$username]['role']);
				if($allowed==false)
				{
					$login_error = 'You are not allowed to login, you do not have the correct permission.';
				}
				else
				{
					$show_form = false;
					$_SESSION['tmp_no']['login'] = 'new';
					$_SESSION[install_id]['user']['id'] = $users[$username]['id'];
					$_SESSION[install_id]['user']['username'] = $username;
					$_SESSION[install_id]['user']['password'] = md5(sha1(md5(install_id.$password).install_id).install_id);
					$_SESSION[install_id]['user']['role'] = $users[$username]['role'];
				}
			}
			//NewsOffice 2.0.3 Beta New feature, manual reset for host owner
			//Manual reset
			elseif(empty($users[$username]['password']) && empty($_POST['n_password']) && !empty($users[$username]['id']))
			{
				$login_error = "Manual override detected for password.<br>Please <a href='".url_build('reset',$users[$username]['id'])."'>reset your password</a>.";
			}
			elseif(!empty($users[$username]['password']) && !empty($users[$username]['password']) && $users[$username]['password']=='activate|'.$password)
			{
				$login_error = 'Your account has not yet been activated.';
			}
			else
			{
				$login_error = 'Your Username or Password is not correct.<br>Please try again.';
			}
		}
		if($show_form==true)
		{
			$login_content .= "
			<div class='main'>
				<form action='' method='post' name='login'>
					<h2>Username</h2>
						<input type='text' name='n_username' class='login-box' value='".$_POST['n_username']."'>
					<h2>Password</h2>
						<input type='password' name='n_password' class='login-box'><br>
					<br>
					<div style='text-align: center;'><input type='submit' name='n_login' value=' Login to NewsOffice '></div>
				</form>
			</div>
			";

			if(!empty($login_error))
			{
				$login_content .= "<div class='error'>".$login_error."</div>";
			}

			$login_content .= "
			<div class='main'>
				<ul>
					<li><a href='".url_build('recovery')."'>Forgot password?</a></li>
					<li><a href='".url_build('register')."'>Register</a></li>
					<li><a href='".url_build('help')."'>Help?</a></li>
				</ul>
			</div>
			";
		}
	}

	if($show_form==true)
	{
		if(empty($login_title))
		{
			$login_render->set_title('Login to',false);
		}
		else
		{
			$login_render->set_title($login_title);
		}
		$login_render->set_logo(true);
		if($name=='register' || $name=='recovery' || $name=='help')
		{
			$login_render->set_width_large();
		}
		else
		{
			$login_render->set_onload("onload='document.login.n_username.focus();'");
		}
		$login_render->set_content($login_content);
		$login_render->show();
		exit();
	}
}
?>