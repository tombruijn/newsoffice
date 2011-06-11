<?php
if(version_compare(PHP_VERSION, '5.0.0','<'))
{
	echo "
	<div class='error'>
		<h1>Older PHP version</h1>
		You are using PHP version ".PHP_VERSION.". Lower than the recommended PHP 5+.<br>
		NewsOffice won't work with ".PHP_VERSION.". Sorry. Please update to a newer (safer) version of PHP.<br>
	</div>";
	exit();
}

//Pages
	$installer_pages = array('intro','license','system-status','settings','admin-account','finish');
//Set default
	if(empty($_POST['current_step']))
	{
		$_POST['current_step'] = 2;
	}

//End
	if($_POST['finish'])
	{
		header('Location:'.$_SERVER['PHP_SELF']);
	}

//Get step
	$name = $installer_pages[$_POST['current_step']-2];

$i['pages'] = 0;
foreach($installer_pages as $installer_page)
{
	$i['pages']++;
	if($name==$installer_page)
	{
		$current_step = $i['pages'];
	}
	if($installer_page==$installer_pages[count($installer_pages)-1])
	{
		$last_page = $i['pages'];
	}
}

//Steps
if($name=='intro')
{
	$show_step = true;
	if($_POST['step'])
	{
		$name = $installer_pages[$current_step];
		$show_step = false;
		$current_step++;
	}
	
	if($show_step==true)
	{
		$install_title = "Welcome to NewsOffice";
		$icontent = "
			<h1>Welcome to NewsOffice</h1>
			NewsOffice is a light weight news manager for your website without a database. You are about to install it on this website.
			There are <span class='important'>".count($installer_pages)."</span> steps which you will need to complete in order to succesfully install NewsOffice.<br>
			<br>
			NewsOffice is build by <a href='http://newanz.com/'>Newanz</a>. You will get a message about updates, but on the Newanz website you can follow the development and even try un-stable versions.
		</div>
		
		<div class='error'>
			<h1>Warning: Beta version</h1>
			This version is a Beta version. In other words: it's not completly done and is not fully tested.<br>
			This is the ".app_version_number." release of the NewsOffice 2.0 version and it's almost complete.<br>
			<br>
			<h2>No back-up and Import functions</h2>
			The back-up and import functions are deactivated in this version. They will be activated in a later version.
		</div>
		
		<div class='main'>
			<h1>Features</h1>
			<ul>
				<li><a href='http://tinymce.moxiecode.com/' target='_blank'>TinyMCE</a> WYSIWYG editor
					<ul>
						<li>New sice 2.0.12 Beta!</li>
					</ul>
				</li>
				<li>News manager
					<ul>
						<li>Manage news posts, add them to categories, let visitors comment, add uploads.</li>
					</ul>
				</li>
				<li>Themes
					<ul>
						<li>Implement your news page(s) within your website with a theme that fits to your website.</li>
					</ul>
				</li>
				<li>More control
					<ul>
						<li>With each new version of NewsOffice you get more and more control over your news pages as the features are improved.</li>
					</ul>
				</li>
			</ul>
		</div>
		";
	}
}
if($name=='license')
{
	$_SESSION['newsoffice_install'][install_id] = md5(sha1(date('Y-m-d H:i:s MDS').$_SESSION['newsoffice_install'][install_id].date('Y-m-d H:i:s MDS')));
	$show_step = true;
	if($_POST['step'] && $_POST['current_step']==$current_step+1)
	{
		if($_POST['agreement'])
		{
			$name = $installer_pages[$current_step];
			$show_step = false;
			$current_step++;
			$_SESSION['newsoffice_install']['agreement'] = date('Y-m-d H:i:s');
		}
		else
		{
			$error[] = 'You have to agree with the Terms of Use in order to install NewsOffice.';
		}
	}
	
	if($show_step==true)
	{
		$termsofuse_file = 'license.txt';
		$termsofuse = new newanz_nzr($termsofuse_file);
		if(empty($termsofuse->original))
		{
			$termsofuse->original = 'Please go to Newanz, at http://newanz.com, for the latest version of these Terms of Use.';
		}
		$install_title = "Terms of Use";
		$icontent .= "<h1>Terms of Use</h1>
		Read the Terms of Use stated below. You have to agree to these Terms of Use in order to use the application.<br>
		Changing the content of the textarea below does not affect the rules you accept to agree with.<br>
		The same rules can be found in the <a href='".$termsofuse_file."' class='important' target='_blank'>".$termsofuse_file."</a> file and on the <a href='http://newanz.com/terms-of-use-for-applications/' target='_blank'>Newanz.com website</a>.<br>
		<br>
		<textarea name='termsofuse' rows='10000' cols='10000'>".$termsofuse->original."</textarea>
		<br>
		<br>
		<input type='checkbox' name='agreement' id='agreement' value='I agree with these terms of use'><label for='agreement' style='display: inline; float: none; width: 300px;'>I agree with these Terms of Use</label><br>
		</div>
		";
	}
}
if($name=='system-status')
{
	$_SESSION['newsoffice_install'][install_id] = md5(sha1(date('Y-m-d H:i:s MDS')));

	$show_step = true;
	if($_POST['step'] && $_POST['current_step']==$current_step+1)
	{
		$name = $installer_pages[$current_step];
		$show_step = false;
		$current_step++;
	}
	
	if($show_step==true)
	{
		$install_title = "System check";
		$icontent = "<h1>System check</h1>
		Below a list of system directories and files that need to be writable. If everything is alright all all values in the status colomn are on Correct.<br>We don't recommend continuing without all the results below beeing Correct.<br><br>";
		$sstatus = new noSystemStatus();
			$sstatus->checkup();
			$messages  = $sstatus->result();			
			$icontent .= $messages."</div>";
			$errors = $sstatus->get_errors();
			if(!empty($errors))
			{
				$icontent .= $errors."
				<div class='main'>
					<h1>From Errors to Correct</h1>
					To fix all the errors you might get you need to CHMOD your files to a CHMOD value which makes it writable.<br>
					Login to your website through a FTP connection and CHMOD it to readable, writable and excutable for everyone: CHMOD value 777.<br>
					<br>
					You only need to CHMOD the files and directories (including sub-directories and files in it) that are listed above.<br>
					<br>
					Error 1: The directory or file itself is not writable.<br>
					Error 2: The directory itself and the files inside the directory are not writable.<br>
					Error 3: The files inside the directory are not writable.<br>
					<br>
					Press refresh to recheck. If you are asked about resubmitting the form; press yes.
				</div>
				";
			}

		if(version_compare(PHP_VERSION, '5.0.0','<'))
		{
			$icontent .= "
			<div class='error'>
				<h1>Older PHP version</h1>
				You are using PHP version ".PHP_VERSION.". Lower than the recommended PHP 5+.<br>
				Some features might not be supported.<br>
				The features we know that aren't supported are:<br>
				<ul>
					<li>Back-up function</li>
					<li>Import function</li>
				</ul>
			</div>";
		}
	}
}
if($name=='settings')
{
	$_SESSION['newsoffice_install'][install_id] = md5(sha1(date('Y-m-d H:i:s MDS').$_SESSION['newsoffice_install'][install_id].date('Y-m-d H:i:s MDS')));

	$show_step = true;
	if($_POST['step'] && $_POST['current_step']==$current_step+1)
	{
		if(!empty($_POST['settings']))
		{
			//Checkup
			if(filter_var($_POST['settings']['url'], FILTER_VALIDATE_URL)==false) //URL
			{
				$error[] = "Installation URL is not a correct URL. Please enter the correct URL to this installation, as in your address bar.";
			}
			if(filter_var($_POST['settings']['email'], FILTER_VALIDATE_EMAIL)==false) //Email
			{
				$error[] = "Email address is not valid, please enter a valid email address, even when it's non-existing.";
			}
		}
		
		if(empty($error))
		{
			$_SESSION['newsoffice_install']['url'] = $_POST['settings']['url'];
			$_SESSION['newsoffice_install']['email'] = $_POST['settings']['email'];
			$_SESSION['newsoffice_install']['format_date'] = $_POST['settings']['format_date'];
			$_SESSION['newsoffice_install']['format_time'] = $_POST['settings']['format_time'];
			$_SESSION['newsoffice_install']['html'] = $_POST['settings']['html'];
			$name = $installer_pages[$current_step];
			$show_step = false;
			$current_step++;
		}
	}
	
	if($show_step==true)
	{
		if(empty($_POST['settings']))
		{
			//URL
			$default['thisurl'] = 'http';
			if($_SERVER["HTTPS"]=="on")
			{
				$default['thisurl'] .= "s";
			}
			$default['thisurl'] .= "://";
			if($_SERVER["SERVER_PORT"] != "80")
			{
				$default['thisurl'] .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			}
			else
			{
				$default['thisurl'] .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			if(empty($_SESSION['newsoffice_install']['url'])){ $default['thisurl'] = str_replace('index.php','',str_replace(strrchr($default['thisurl'],'?'),'',$default['thisurl'])); }
			else{ $default['thisurl'] = $_SESSION['newsoffice_install']['url']; }
			//Email
			if(empty($_SESSION['newsoffice_install']['email'])){ $default['email'] = 'no-reply@yourdomain.com'; }
			else{ $default['email'] = $_SESSION['newsoffice_install']['email']; }
			//Date
			if(empty($_SESSION['newsoffice_install']['format_date'])){ $default['date'] = 'd/m/Y'; }
			else{ $default['date'] = $_SESSION['newsoffice_install']['format_date']; }
			//Time
			if(empty($_SESSION['newsoffice_install']['format_time'])){ $default['time'] = 'H:i'; }
			else{ $default['time'] = $_SESSION['newsoffice_install']['format_time']; }
			//HTML
			if(empty($_SESSION['newsoffice_install']['html'])){ $default['html'] = 'html'; }
			else{ $default['html'] = $_SESSION['newsoffice_install']['html']; }
		}
		else
		{
			//URL
			$default['thisurl'] = $_POST['settings']['url'];
			//Email
			$default['email'] = $_POST['settings']['email'];
			//Date
			$default['date'] = $_POST['settings']['format_date'];
			//Time
			$default['time'] = $_POST['settings']['format_time'];
			//HTML
			$default['html'] = $_POST['settings']['html'];
		}
	
		$install_title = "Settings";
		$icontent = "<h1>Settings</h1>
		Please fill in the correct values for these settings below. These settings are required to correctly configure this installation of NewsOffice. If you are not sure what to enter, leave the default value.<br>
		<br>
		<table>
			<tr>
				<td class='subject'>
					<label for='url'>Installation URL:</label>
				</td>
				<td>
					<input type='text' id='url' name='settings[url]' value='".no_convert_field($default['thisurl'])."' class='text'>
					<div class='less_important'>Correct URL value pointing to this website.<br>Example: http://www.yourwebsite.com/newsoffice/</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					<label for='email'>Email address:</label>
				</td>
				<td>
					<input type='text' id='email' name='settings[email]' value='".no_convert_field($default['email'])."' class='text'>
					<div class='less_important' style='height: 20px;'>This email address will only be used for sending users emails.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					<label for='date'>Date format:</label>
				</td>
				<td>
					<input type='text' id='date' name='settings[format_date]' value='".no_convert_field($default['date'])."' class='text'>
					<div class='less_important' style='height: 20px;'>PHP date() format used to display your date. Examples: d/m/Y, m/d/Y, dS M Y.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					<label for='time'>Time format:</label>
				</td>
				<td>
					<input type='text' id='time' name='settings[format_time]' value='".no_convert_field($default['time'])."' class='text'>
					<div class='less_important' style='height: 20px;'>PHP date() format used to display your time. Examples: H:i:s, H:i.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					<label for='html'>HTML version:</label>
				</td>
				<td>
					<select id='html' name='settings[html]'>";
		$options['html'][] = array('html', "HTML");
		$options['html'][] = array('xhtml', "XHTML");
		foreach($options['html'] as $option)
		{
			$icontent .= "<option value='".$option[0]."'";
			if($default['html']==$option[0])
			{
				$icontent .= " selected";
			}
			$icontent .= ">".$option[1]."</option>";
		}
		$icontent .= "</select>
					<div class='less_important' style='height: 20px;'>Do you use HTML or XHTML on the website you want to display your news?</div>
				</td>
			</tr>
		</table>
		</div>
		<div class='important'>
			You can always change these settings inside NewsOffice. There, these and more settings are avaliable.
		</div>
		";
	}
}
if($name=='admin-account')
{
	$_SESSION['newsoffice_install'][install_id] = md5(sha1(date('Y-m-d H:i:s MDS').$_SESSION['newsoffice_install'][install_id].date('Y-m-d H:i:s MDS')));

	$show_step = true;
	if($_POST['step'] && $_POST['current_step']==$current_step+1)
	{
		if(!empty($_POST['uservalues']))
		{
			//Checkup
			//Email
			if(function_exists('filter_var')==true)
			{
				if(filter_var($_POST['uservalues']['email'], FILTER_VALIDATE_EMAIL)==false)
				{
					$error[] = "Email address is not valid, please enter a valid email address, even when it's non-existing.";
				}
			}
			else
			{
				if(strstr($_POST['uservalues']['email'],'@')==false || strstr($_POST['uservalues']['email'],'.')==false || strstr($_POST['uservalues']['email'],' ')==true)
				{
					$error[] = "Email address is not valid, please enter a valid email address, even when it's non-existing.";
				}
			}
			//Passwords
			if(empty($_POST['uservalues']['password_new']) || $_POST['uservalues']['password_new']!==$_POST['uservalues']['password_confirm'])
			{
				$error[] = "You did not enter a password or the passwords you entered did not match.";
			}
		}
		
		if(empty($error))
		{
			$name = $installer_pages[$current_step];
			$show_step = false;
			$current_step++;
			$_SESSION['newsoffice_install']['root_user']['username'] = $_POST['uservalues']['username'];
			$_SESSION['newsoffice_install']['root_user']['email'] = $_POST['uservalues']['email'];
			$_SESSION['newsoffice_install']['root_user']['password'] = $_POST['uservalues']['password_new'];
		}
		else
		{
			$default['username'] = $_POST['uservalues']['username'];
			$default['email'] = $_POST['uservalues']['email'];
		}
	}
	else
	{
		if(empty($_SESSION['newsoffice_install']['root_user']['username'])){ $default['username'] = 'admin'; } 
		else{ $default['username'] = $_SESSION['newsoffice_install']['root_user']['username']; }
		if(empty($_SESSION['newsoffice_install']['root_user']['email'])){ $default['email'] = $_SESSION['newsoffice_install']['email']; }
		else{ $default['email'] = $_SESSION['newsoffice_install']['root_user']['email']; }
	}
	
	if($show_step==true)
	{
		$install_title = "Creating the Root account";
		$icontent = "<h1>Creating the Root account</h1>
		The Root account is the account which will always exists and have all the rights in the installation. This is a required account and you will now have to enter it's login details.<br>
		<br>
		<table>
			<tr>
				<td class='subject'>
					<label for='username'>Username</label>
				</td>
				<td>
					<input type='text' id='username' name='uservalues[username]' value='".$default['username']."' class='text'>
					<div class='less_important' style='height: 20px;'>With this name you will have to login.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					<label for='email'>Email address</label>
				</td>
				<td>
					<input type='text' id='email' name='uservalues[email]' value='".$default['email']."' class='text'>
					<div class='less_important' style='height: 20px;'>Valid email required.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					<label for='pw1'>Password</label>
				</td>
				<td>
					<input type='password' id='pw1' name='uservalues[password_new]' class='text'>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					<label for='pw2'>Confirm password</label>
				</td>
				<td>
					<input type='password' id='pw2' name='uservalues[password_confirm]' class='text'>
					<div class='less_important' style='height: 20px;'>The above two passwords need to match.</div>
				</td>
			</tr>
		</table>
		</div>
		<div class='important'>You can always change these details in your profile. Here you can also add an avatar, profile description, display name, etc.</div>";
	}
}

if($name=='finish')
{
	$app_install_id = 
	md5
	(
		sha1
		(
			date('Y-m-d H:i:s MDS').
			$_SESSION['newsoffice_install'][install_id].
			$_SESSION['newsoffice_install']['agreement'].
			$_SESSION['newsoffice_install']['email'].
			$_SESSION['newsoffice_install']['url'].
			$_SESSION['newsoffice_install']['html'].
			$_SESSION['newsoffice_install']['format_date'].
			$_SESSION['newsoffice_install']['format_time'].
			$_SESSION['newsoffice_install']['root_user']['username'].
			$_SESSION['newsoffice_install']['root_user']['email'].
			$_SESSION['newsoffice_install'][install_id].
			date('Y-m-d H:i:s MDS')
		)
	);

	//_______________________________________________________________________________________________________
	//	START SAVING
	//		- NewsOffice settings
	//		- Root user
	//_______________________________________________________________________________________________________
	
	$config = new newanz_nzr('config.php','MULTIPLE_SAVES_FRIENDLY');
		$config->readfile();
		//Save settings
			define('install_id',$app_install_id);
			$settings = array(
				'acp_version_id'=>$no_config['acp_version_id'],
				'acp_install_id'=>$app_install_id,
				'acp_url'=>$_SESSION['newsoffice_install']['url'],
				'acp_email'=>$_SESSION['newsoffice_install']['email'],
				'set_html'=>$_SESSION['newsoffice_install']['html'],
				'format_date'=>$_SESSION['newsoffice_install']['format_date'],
				'format_time'=>$_SESSION['newsoffice_install']['format_time'],
			);
			foreach($settings as $key=>$value)
			{
				$config->save(array('value'=>$value),array('object'=>$key));
			}
	if($config->close()==false)
	{
		$error[] = "Your settings could not be saved, please check if everything is working properly on step 3: System status.";
	}
	
	if(empty($error))
	{
		//User
		$root_user = new newanz_nzr($no_config['dir_info'].'users.nzr');
			$root_user->readfile();
			$root_user->delete(array('id'=>'1'));
			$root_user->delete(array('username'=>$_SESSION['newsoffice_install']['root_user']['username']));
			$root_user->save(array(
				'id'=>'1',
				'username'=>$_SESSION['newsoffice_install']['root_user']['username'],
				'display-name'=>$_SESSION['newsoffice_install']['root_user']['username'],
				'email'=>$_SESSION['newsoffice_install']['root_user']['email'],
				'password'=>noUser::pwEncodeRecord($_SESSION['newsoffice_install']['root_user']['password']),
				'role'=>'1',
				'description'=>"Root Administrator of this NewsOffice installation"
			),'new');
			if($root_user->result==false)
			{
				$error[] = "The Root administrator account could not be saved, please check if everything is working properly on step 3: System status.";
			}
		$root_user->close();
	}
	
	if(empty($error))
	{
		$install_title = "Installation complete";
		$icontent = "<h1>Installation complete</h1>
		The installation of NewsOffice has been succesfully completed.<br>
		To login, press the Finish button below to continue to the NewsOffice login screen.<br>
		<div class='less_important'>Use the account details you entered in this installer.</div>
		</div>
		<!--
		<div class='main'>
			<h1>Register NewsOffice</h1>
			You can now register NewsOffice to help us improve it. Using your register ID we will be able to improve our support.-->
		";
	}
	else
	{
		$install_title = "Installation interrupted";
		$icontent = "<h1>Installation interrupted</h1>
		The installation of NewsOffice has been interrupted.<br>
		Please read and fix the errors stated above.<br>
		<br>
		<a href='?name=intro'>&laquo; Go back, restart installation</a><br>
		<div class='less_important'>Most values, no passwords, are saved and will be automaticly entered in their matching fields.<br>
		Close your browser to restart the whole installation process.</div>
		</div>
		";
	}
}

//Error
if(empty($icontent))
{
	$install_title = "Page not found";
	$icontent = "<h1>Page not found</h1>
	This installer page is not found.<br>
	<br>
	<a href='?name=".$installer_pages[0]."'>&laquo; Go back</a><br>
	</div>";
}

$install_content .= "<form name='login' method='post' action=''>";
if(!empty($error))
{
	$install_content .= "<div class='main'></div><div class='error'><h2>Errors</h2>One or more errors occured. Please check the errors stated below.<ul>";
	foreach($error as $error_object)
	{
		$install_content .= "<li>".$error_object."</li>";
	}
	$install_content .= "</ul></div>";
}
$install_content .= "
	<div class='main'>
";

if(in_array($name,$installer_pages)==true)
{
	$install_content .= "<div style='color: #666666; font-size: 10px; text-align: right;'>Step ".$current_step." of ".$last_page."</div>";
}

$install_content .= $icontent."
	<div class='main' style='text-align: right;'>
";
	if($name==$installer_pages[count($installer_pages)-1])
	{
		if(empty($error))
		{
			$install_content .= "<input type='submit' name='finish' value='Finish, Login to NewsOffice'>";
		}
		else
		{
			$install_content .= "Fix errors first.";
		}
	}
	else
	{
		$install_content .= "<input type='hidden' name='current_step' value='".($current_step+1)."'><input type='submit' name='step' value='Continue to step ".($current_step+1)." &raquo;'>";
	}
$install_content .= "
	</div>
</form>";
?>