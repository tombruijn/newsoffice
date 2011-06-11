<?php
if($id=='1' && user!=='1')
{
	$page_content .= "<h1>Not allowed</h1>You are not allowed to edit the root user.<br>
	<br>
	<a href='".url_build('users-manager-users')."'>&laquo; Go back</a>";
}
else
{
	$info = array();
	if(!empty($id))
	{
		$info = $users[$id];
	}
	elseif($name!=='users-create-user')
	{
		$info = $users[user];
	}

	if($info['id']==user)
	{
		$mode = 'self';
		if($name!=='users-profile-own')
		{
			header("Location: ".url_build('users-profile-own'));
			exit(); //JIC
		}
		$back_link = "users-main";
	}
	else
	{
		if($name=='users-create-user')
		{
			$mode = 'new';
			$back_link = "users-main";
		}
		else
		{
			$mode = 'edit';
			$back_link = "users-manager-users";
		}
	}
	$page_content = "<h1>";
	if($mode=='self')
	{
		$page_content .= "Your profile";
	}
	elseif($mode=='new')
	{
		$page_content .= "Create new user";
	}
	else
	{
		$page_content .= "Edit ".$info['username']."'s profile";
	}
	$page_content .= "</h1>";
	$show_editor = true;
	//Saving
	if($_POST['save'])
	{
		if(empty($_POST['username']))
		{
			$_POST['username'] = $info['username'];
		}
		//Checkups on names
		$nzr_keep = $cUsers->set_store();
		$checkup = new newanz_nzr();
			//Find display name
			$checkup->set_import($nzr_keep);
			$checkup->search(array('display-name'=>$_POST['display-name']));
			$checkup->rekey(array('id'));
			if(!empty($checkup->content) && (($mode!=='self' && array_key_exists($id,$checkup->content)==false) || ($mode=='self' && array_key_exists(user,$checkup->content)==false)))
			{
				$displayname_found = true;
			}
			else
			{
				if($checkup->amount_rows>1)
				{
					$displayname_found = true;
				}
				else
				{
					$displayname_found = false;
				}
			}
			
			//Find username
			$checkup->set_import($nzr_keep);
			$checkup->search(array('username'=>$_POST['username']));
			$checkup->rekey(array('id'));
			if(!empty($checkup->content) && (($mode!=='self' && array_key_exists($id,$checkup->content)==false) || ($mode=='self' && array_key_exists(user,$checkup->content)==false)))
			{
				$username_found = true;
			}
			else
			{
				if($checkup->amount_rows>1)
				{
					$username_found = true;
				}
				else
				{
					$username_found = false;
				}
			}
			
			//Find username
			$checkup->set_import($nzr_keep);
			$checkup->search(array('email'=>$_POST['email']));
			$checkup->rekey(array('id'));
			if(!empty($checkup->content) && (($mode!=='self' && array_key_exists($id,$checkup->content)==false) || ($mode=='self' && array_key_exists(user,$checkup->content)==false)))
			{
				$email_found = true;
			}
			else
			{
				if($checkup->amount_rows>1)
				{
					$email_found = true;
				}
				else
				{
					$email_found = false;
				}
			}
		$checkup->close();
		
		if($mode=='new' && (empty($_POST['new_password']) || empty($_POST['confirm_password']) || $_POST['new_password']!==$_POST['confirm_password']))
		{
			//Problem with password
			$error[] = "You have not added an password to this new user or the (new) passwords did not match.";
		}
		if(empty($info) && (empty($_POST['username']) || $username_found==true))
		{
			//Problem with username
			$error[] = 'The username you have selected is empty or already in use.';
		}
		if(empty($_POST['display-name']) || $displayname_found==true)
		{
			//Problem with display name
			$error[] = 'The Display name you have selected is empty or already in use.';
		}
		if(function_exists('filter_var')==true)
		{
			$valid_email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
		}
		else
		{
			$valid_email = strstr($_POST['email'],'@');
		}
		if(empty($_POST['email']) || $valid_email==false)
		{
			//Problem with email
			$error[] = 'Your email address is not valid, please enter a valid email address.';
		}
		if($email_found==true)
		{
			$error[] = "The email address you entered is already in use.";
		}
		//Passwords checkup
		if($mode=='self' && !empty($_POST['current_password']) && md5(sha1($_POST['current_password'].install_id).install_id)!==$users[user]['password'])
		{
			//Current passwords did not match
			$error[] = 'The current password you entered is not equal to what we have registered as your password. Please try again.';
		}
		elseif($mode!=='new' && ((!empty($_POST['new_password']) || !empty($_POST['confirm_password'])) && $_POST['new_password']!==$_POST['confirm_password']))
		{
			//New entered passwords are not the same
			$error[] = 'The newly entered passwords do not match or are left empty. Please enter them again. They have to be the same.';
		}
		elseif(!empty($_POST['new_password']) && !empty($_POST['confirm_password']) && $_POST['new_password']==$_POST['confirm_password'])
		{
			//Everything went okay
			$new_password = md5(sha1($_POST['new_password'].install_id).install_id);
		}
		else
		{
			//No change in password
			$new_password = $info['password'];
			$no_change = true;
		}
		$_POST["description"] = strtr($_POST["description"], array("\r\n"=>'',"\r"=>'',"\n"=>''));
		if(empty($error))
		{
			if($mode=='edit' && $_POST['activate'])
			{
				$new_password = str_replace('activate|','',$new_password);
			}
			$show_editor = false;
			$saveU = new newanz_nzr($no_config['dir_info'].'users.nzr');
				$saveU->readfile();
			if($mode=='new')
			{
				$set1 = 'new';
				$set2 = '';//Limit has no affect here
			}
			else
			{
				if($mode=='self')
				{
					$set1 = array('id'=>user);
					$_POST['role'] = $users[user]['role'];
				}
				else
				{
					$set1 = array('id'=>$id);
				}
				$set2 = 1; //Just affect one record
			}
			$saveU->save(
				array(
					'username' => $_POST['username'],
					'display-name' => $_POST['display-name'],
					'password' => $new_password,
					'email' => $_POST['email'],
					'role' => $_POST['role'],
					'avatar' => $_POST['avatar'],
					'description' => $_POST['description']
				),
				$set1,
				$set2);
			if($mode=='self')
			{
				$link_back = 'users-profile-own';
				$page_content .= "Your profile has been succesfully saved.";
			}
			elseif($mode=='new')
			{
				$link_back = 'users-profile';
				$link_id = $saveU->insert_id;
				$page_content .= "User <span class='important'>".$_POST['username']."</span> has succesfully been created.";
			}
			else
			{
				$link_back = 'users-profile';
				$link_id = $id;
				$page_content .= "The profile of ".$_POST['username']." has succesfully been updated.";
			}
			$saveU->close();
			$page_content .= "<br>";

			if($mode=='self' && $no_change!==true)
			{
				//Reset session
				$_SESSION[install_id]['user']['password'] = md5(sha1(md5(install_id.$new_password).install_id).install_id);
				$page_content .= "<div class='important'>Your password has been succesfully changed and is now in effect.</div>";
			}
			elseif($mode=='edit' && !empty($new_password) && $no_change!==true)
			{
				$page_content .= "<div class='important'>The user's password has been succesfully changed and is now in effect.</div>";
			}
			else
			{
				$page_content .= "<br>";
			}
			//Activate message
			if($mode=='edit' && $_POST['activate'])
			{
				$page_content .= "<div class='important'>The user's account is succesfully activated.</div>";
			}
			$page_content .= "<a href='".url_build($link_back,$link_id)."'> &laquo; Return to User editor</a> | <a href='".url_build($back_link)."'>Go back &raquo;</a>";
		}
		else
		{
			if(!empty($_POST['username']))
			{
				$info['username'] = $_POST['username'];
			}
			$info['display-name'] = $_POST['display-name'];
			$info['email'] = $_POST['email'];
			$info['role'] = $_POST['role'];
			$info['avatar'] = $_POST['avatar'];
			$info['description'] = $_POST['description'];
		}
	}//End save function

	if($show_editor==true)
	{
		$page_content .= "Make changes to this profile and press save to apply them.<br>";
		
		if(!empty($error))
		{
			$page_content .= "<div class='error'><h2>Errors</h2>One or more errors occurred, read them below and fix them to save this user profile.<ul>";
			foreach($error as $error_message)
			{
				$page_content .= "<li>".$error_message."</li>";
			}
			$page_content .= "</ul></div>";
		}
		
		$page_content .="
		<table>
			<tr>
				<td class='subject'>
					Username
				</td>
				<td>";
	if($mode=='new')
	{
		$page_content .= "<input type='text' name='username' value='".no_convert_field($info['username'])."'>";
	}
	else
	{
		$page_content .= $info['username'];
	}

	$page_content .= "
					<div class='less_important'>This name is used to login into the NewsOffice Panel.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Display name
				</td>
				<td>
					<input type='text' name='display-name' value='".no_convert_field($info['display-name'])."'>
					<div class='less_important'>This name is displayed in public profiles, news posts and comments.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Email
				</td>
				<td>
					<input type='text' name='email' value='".no_convert_field($info['email'])."'>
					<div class='less_important'>Valid address required.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Group
				</td>
				<td>";
		
		if($mode=='self')
		{
			$page_content .= "<span".no_group_color($users[user]['role']).">".$user_groups[$users[user]['role']]['name']."</span>";
		}
		else
		{
			$page_content .= "<select name='role'>";
			if(!empty($user_groups))
			{
				foreach($user_groups as $user_group)
				{
					$page_content .= "<option value='".$user_group['id']."'";
					if(($mode!=='new' && $info['role']==$user_group['id']) || ($mode=='new' && $user_group['id']==$no_config['set_default_group']))
					{
						$page_content .= " selected";
					}
					$page_content .= no_group_color($user_group['id']).">".$user_group['name']."</option>";
				}
			}
			$page_content .= "</select>";
		}
		$page_content .= "</td>
			</tr>
			<tr>
				<td class='subject'>
					Avatar url
				</td>
				<td>
					<input type='text' name='avatar' value='".no_convert_field($info['avatar'])."'>
					<div class='less_important'>Optional. Shows an image in your profile.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Profile description
				</td>
				<td>
					<textarea name='description' rows='10' cols='10' class='mceEditor'>".no_convert_field($info['description'],true)."</textarea>
					<div class='less_important'>Optional.</div>
				</td>
			</tr>
		</table>";
		
		if(substr_count($info['password'],'activate')>0)
		{
			$page_content .= "
			<div class='important'>
				<input type='checkbox' name='activate' value='activate'> Activate this account.<br>
				<div class='less_important'>This account has not yet been activated, check this checkbox and you will activate it.</div>
			</div>";
		}
		
		$page_content .= "
		<div class='block'>
			<h2>";
		if($mode=='new')
		{
			$page_content .= "Set password";
		}
		else
		{
			$page_content .= "Change password";
		}
		$page_content .= "</h2>";
		if($mode!=='new')
		{
			$page_content .= "Leave these fields empty if you do not want to change the password.<br>";
		}
		//Already reset is progress?
		if(substr_count($info['password'],'reset')>0)
		{
			$page_content .= "<div class='important'>This user has used the <span class='important'>Forgot password</span> function, but has not reset his/her password yet. Changing the password will undo the <span class='important'>Forgot password</span> request.</div>";
		}
		$page_content .= "<br><table>";
		if($mode=='self') //Users have to confirm their own passwords
		{
			$page_content .= "
				<tr>
					<td class='subject'>
						<label for='current_password'>Current password:</label>
					</td>
					<td>
						<input type='password' name='current_password' id='current_password'>
						<div class='less_important'>Your current password is required to confirm it is really you.</div>
					</td>
				</tr>
			";
		}
		$page_content .= "
				<tr>
					<td class='subject'>
						<label for='new_password'>New password:</label>
					</td>
					<td>
						<input type='password' name='new_password' id='new_password'>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						<label for='confirm_password'>Confirm password:</label>
					</td>
					<td>
						<input type='password' name='confirm_password' id='confirm_password'>
						<div class='less_important'>Enter ";
		if($mode=='self')
		{
			$page_content .= "your";
		}
		else
		{
			$page_content .= "the";
		}
		$page_content .= " new password again to avoid mistakes.</div>
					</td>
				</tr>
			</table>
		</div>
		";
		if($mode=='self')
		{
			$page_content .= "<div class='block'><h2>Messages</h2>
			<a href='#resetmessages' onclick='reset_messages(); return false;' class='fake_link' title='Click to reset your messages.'>Reset messages</a>, warning/important messages and information blocks<br>
			<div class='less_important'>Resetting will show all the messages, which still matter, that you have closed while beeing logged in.</div></div>";
		}
		$page_content .= "<a href='".url_build($back_link)."'>&laquo; Go back</a> | <input type='submit' name='save' value=' Save profile '>";
	}
}
?>