<?php
if(empty($id))
{
	$mode = 'new';
}
else
{
	$mode = 'edit';
}
$theme_file = newsoffice_directory.$no_config['dir_themes'].$id.".nzr";
if(file_exists($theme_file)==true || $mode=='new')
{
	if($mode=='edit')
	{
		$openTheme = new newanz_nzr($theme_file);
			$openTheme->readfile();
			if($openTheme->amount_rows>0)
			{
				foreach($openTheme->content as $ovalue)
				{
					$info[$ovalue['object']] = $ovalue['value'];
				}
			}
		$openTheme->close();
		$themename = $info['name'];
	}

	//Creating message and error arrays
		$mname = 'theme_message-1'; $messages[$mname] = array('Go back',$info[$mname]);
		$mname = 'theme_message-2'; $messages[$mname] = array('Next',$info[$mname]);
		$mname = 'theme_message-3'; $messages[$mname] = array("Read more",$info[$mname],"Use [comments] to show how many comments are placed on this news post.");
		$mname = 'theme_message-4'; $messages[$mname] = array("Page",$info[$mname],"How do you call your pages?");
		$mname = 'theme_message-5'; $messages[$mname] = array("Comment submit button",$info[$mname],"What should the submit button on the comment form say?");
		$mname = 'theme_message-6'; $messages[$mname] = array("Comment succesfully placed.",$info[$mname]);
		$mname = 'theme_message-7'; $messages[$mname] = array("Login succes.",$info[$mname]);
		$mname = 'theme_message-8'; $messages[$mname] = array("Login failure.",$info[$mname]);
		$mname = 'theme_message-9'; $messages[$mname] = array("Logout.",$info[$mname]);
		$mname = 'theme_name-login'; $messages[$mname] = array("Name login",$info[$mname]);
		$mname = 'theme_name-logout'; $messages[$mname] = array("Name logout",$info[$mname]);
		$ename = 'theme_error-1'; $errors[$ename] = array('No news found',$info[$ename]);
		$ename = 'theme_error-2'; $errors[$ename] = array('No author found',$info[$ename]);
		$ename = 'theme_error-3'; $errors[$ename] = array('No comments found',$info[$ename]);
		$ename = 'theme_error-4'; $errors[$ename] = array('Upload not found',$info[$ename]);
		$ename = 'theme_error-5'; $errors[$ename] = array('Incorrect or double comment',$info[$ename]);
		$ename = 'theme_error-6'; $errors[$ename] = array('Anti-Spam message',$info[$ename]);
		$ename = 'theme_error-7'; $errors[$ename] = array('Not allowed to comment message',$info[$ename]);
		$ename = 'theme_error-comment-failure'; $errors[$ename] = array("Comment place failure.",$info[$ename]);
		
	$show_form = true;
	if($_POST['save'])
	{
		if(empty($_POST['name']))
		{
			$error = "<div class='error'>Name of this theme is required.</div>";
			if(!empty($_POST))
			{
				foreach($_POST as $tkey=>$tvalue)
				{
					if($tkey!=='save')
					{
						$info[$tkey] = $tvalue;
					}
				}
			}
		}
		else
		{
			$show_form = false;
			//Save
			if($mode=='new')
			{
				$id = strtolower(filter_var($_POST['name'], FILTER_SANITIZE_URL)); //Clean name to a usuable file name
				$theme_file = newsoffice_directory.$no_config['dir_themes'].$id.".nzr";
				//Create a new news .nzr file
				$saveNew = new newanz_nzr($theme_file,'create');
					$saveNew->create_file(array('object','value'));
					$saveNew->set_primary_keys(array('object'));
				$saveNew->close();
			}
			$saveTheme = new newanz_nzr($theme_file, 'MULTIPLE_SAVES_FRIENDLY');
				$saveTheme->readfile();
				$nzr_keep = $saveTheme->set_store(); //To prevent loss of comments
					$saveTheme->rekey(array('object'));
					$converted_objects = $saveTheme->content;
				$saveTheme->set_import($nzr_keep); //To prevent loss of comments
				if(!empty($_POST))
				{
					foreach($_POST as $tkey=>$tvalue)
					{
						if($tkey!=='save')
						{
							//New theme || New object
							if($mode=='new' || array_key_exists($tkey,$converted_objects)==false) 
							{
								$savevalues = array('object'=>$tkey,'value'=>$tvalue);
								$saveselect = 'new';
							}
							//Overwrite the old value
							else
							{
								$savevalues = array('value'=>$tvalue);
								$saveselect = array('object'=>$tkey);
							}
							$saveTheme->save(
								$savevalues,
								$saveselect,
								1
							);
						}
					}
				}
			$saveTheme->close();
			
			$page_content = "<h1>Theme saved succesfull</h1>
			Your <span class='important'>".$_POST['name']."</span> theme has been succesfully saved.<br>
			<br>
			<a href='".url_build('manager-themes-editor',$id)."'>&laquo; Go back to theme editor</a> | <a href='".url_build('manager-themes')."'>Go back to theme manager &raquo; </a>";
		}
	}
	
	if($show_form==true)
	{	
		$options = "<a href='".url_build('manager-themes')."'>&laquo; Go back</a> | <input type='submit' name='save' value=' Save theme '>";
		
		$page_content = "<h1>Themes</h1>
		Edit or create a theme here. These themes can be used to display your news.<br>
		Change the appearance of each post, comment, message, etc. here.<br>";
		if($mode=='edit')
		{
			$page_content .= "<br>You are currently editing the <span class='important'>".$info['name']."</span> theme.";
		}
		$page_content .= "<br>".$error."<div class='block'>
		<h2>Theme information</h2>
		<div class='inside'>
		Name:<br>
		<input type='text' name='name' value='".no_convert_field($info['name'])."' style='width: 100%;'><br>
		Description:<br>
		<textarea name='description' rows='10000' cols='10000' class='small'>".no_convert_field($info['description'],true)."</textarea></div></div>
		".$options."<br>

		<h2>Global tags</h2>
		<div class='block'>These tags below can be used in pretty much every form below.
		<table>
			<tr><td class='subject'>[current-date]</td><td>Shows the current date.</td></tr>
			<tr><td class='subject'>[current-time]</td><td>Shows the current time.</td></tr>
			<tr><td class='subject'>[user]</td><td>Shows the username with a link to his/her profile.</td></tr>
			<tr><td class='subject'>[logout]</td><td>Shows a submit button to logout.</td></tr>
			<tr><td class='subject'>[newsoffice]</td><td>Shows a link to this Administration panel for users to register and login.</td></tr>
		</table></div>
		
		<h2>Main build-up</h2>
		<div class='block'><h3>Build-up start</h3>
		This is where every <span class='important'>box</span> starts with. It will be at the top of every post, comment, message, etc.<br>
		<textarea class='small' rows='10000' cols='10000' name='theme_start'>".no_convert_field($info['theme_start'],true)."</textarea><br>
		
		<h3>Build-up end</h3>
		This is where every <span class='important'>box</span> ends with. It will be at the bottom of every post, comment, message, etc.<br>
		<textarea class='small' rows='10000' cols='10000' name='theme_end'>".no_convert_field($info['theme_end'],true)."</textarea></div>
		
		<h2>News posts</h2>
		<div class='block'><h3>Every news post</h3>
		This is how every news post should be displayed.<br>
		<h4>Tags</h4>
		Use these tags to place links and other usefull things.<br>
		<table>
			<tr><td class='subject'>[title]</td><td>Displays the title of your news post.</td></tr>
			<tr><td class='subject'>[date]</td><td>Shows the date of when your news post was written/published.</td></tr>
			<tr><td class='subject'>[time]</td><td>Shows the time of when your news post was written/published.</td></tr>
			<tr><td class='subject'>[categories]</td><td>Displays the categories your news post has been added to.</td></tr>
			<tr><td class='subject'>[author]</td><td>Displays the display name of the author of the news post. The name is displayed in the color of the group the user is added to and the name is a link to his/her profile.</td></tr>
			<tr><td class='subject'>[description]</td><td>Shows the description of the news post in the page mode.</td></tr>
			<tr><td class='subject'>[content]</td><td>Shows the full content of the news.</td></tr>
			<tr><td class='subject'>[message]</td><td>Shows <span class='important'>read more</span> message in the page mode and shows <span class='important'>go back</span> message when reading the news post.</td></tr>
		</table>
		<textarea class='small' rows='10000' cols='10000' name='theme_news'>".no_convert_field($info['theme_news'],true)."</textarea>
		<div class='less_important'>Use the <span class='important'>+</span> in the [description] and [content] tag to make them visible in page and post mode. For example: [description+] and [content+]</div></div>

		<h2>User page</h2>
		<div class='block'>Shows the profile of a user.<br>
		<h4>Tags</h4>
		Use these tags to place links and other usefull things.<br>
		<table>
			<tr><td class='subject'>[author]</td><td>Shows the display name of the user.</td></tr>
			<tr><td class='subject'>[email]</td><td>Shows the email address of the user.</td></tr>
			<tr><td class='subject'>[group]</td><td>Shows the group the user is added to.</td></tr>
			<tr><td class='subject'>[avatar]</td><td>Shows the avatar the user has added to his/her profile.</td></tr>
			<tr><td class='subject'>[description]</td><td>Shows a small description the user has added to his/her profile.</td></tr>
			<tr><td class='subject'>[message]</td><td>Shows the go back message.</td></tr>
		</table>
		<textarea class='small' rows='10000' cols='10000' name='theme_author'>".no_convert_field($info['theme_author'],true)."</textarea></div>
		
		<h2>Comments</h2>
		<div class='block'><h3>Every comment</h3>
		This is how every news post should be displayed.<br>
		<h4>Tags</h4>
		Use these tags to place links and other usefull things.<br>
		<table>
			<tr><td class='subject'>[date]</td><td>Shows the date of when the comment was written.</td></tr>
			<tr><td class='subject'>[time]</td><td>Shows the time of when the comment was written.</td></tr>
			<tr><td class='subject'>[author]</td><td>Displays the display name of the author of the news post. The name is displayed in the color of the group the user is added to and the name is a link to his/her profile.</td></tr>
			<tr><td class='subject'>[content]</td><td>Shows the content of the comment the user placed.</td></tr>
		</table>
		<textarea class='small' rows='10000' cols='10000' name='theme_comments'>".no_convert_field($info['theme_comments'],true)."</textarea></div>
		
		<h2>Forms</h2>
		<div class='block'><h3>Comment form</h3>
		Change the appearance of the comment form, this will your visitors or members use to place a comment.<br>
		<h4>Tags</h4>
		Use these tags to place links and other usefull things.<br>
		<table>
			<tr><td class='subject'>[content]</td><td>Shows a textarea the user can place his comment in.</td></tr>
			<tr><td class='subject'>[submit]</td><td>Shows the submit button to place his/her comment.</td></tr>
			<tr><td class='subject'>[error]</td><td>Shows an error message when something is wrong with the comment.</td></tr>
		</table>
		<textarea class='small' rows='10000' cols='10000' name='theme_comments-form'>".no_convert_field($info['theme_comments-form'],true)."</textarea><br>
		<h3>Login form</h3>
		Change the appearance of the login form, this will your visitors or members use to place a comment.<br>
		<h4>Tags</h4>
		Use these tags to place links and other usefull things.<br>
		<table>
			<tr><td class='subject'>[username]</td><td><span class='important'>Required:</span> Shows a textfield where the username has to be entered in.</td></tr>
			<tr><td class='subject'>[password]</td><td><span class='important'>Required:</span> Shows a textfield where the users password has to be entered in.</td></tr>
			<tr><td class='subject'>[submit]</td><td><span class='important'>Required:</span> Shows the submit button that allows you to login.</td></tr>
			<tr><td class='subject'>[register]</td><td>Shows a link to the register form.</td></tr>
			<tr><td class='subject'>[forgot-password]</td><td>Shows a link to the forgot password form.</td></tr>
		</table>
		<textarea class='small' rows='10000' cols='10000' name='theme_login-form'>".no_convert_field($info['theme_login-form'],true)."</textarea></div>
		
		<h2>Messages</h2>
		<div class='block'><h3>Next/Back/Read more</h3>
		Messages that your visitors get when they visit your news page.
		<div class='inside'>
		<table>";
		
		foreach($messages as $mkey=>$message)
		{
			$page_content .= "<tr>
				<td class='subject'>
					".$message[0]."
				</td>
				<td>
					<input type='text' name='".$mkey."' value='".no_convert_field($message[1])."' style='width: 100%;'>
					<div class='less_important'>".$message[2]."</div>
				</td>
			</tr>";
		}
		$page_content .= "</table>
		</div>
		<h3>Errors</h3>
		The error messages used to inform your visitors/members about an error that occured.<br>
		<div class='inside'>
		<table>	";
		
		foreach($errors as $ekey=>$error)
		{
			$page_content .= "<tr>
				<td class='subject'>
					".$error[0]."
				</td>
				<td>
					<input type='text' name='".$ekey."' value='".no_convert_field($error[1])."' style='width: 100%;'>
				</td>
			</tr>";
		}
		
		$page_content .= "</table></div></div><br>
		".$options;
	}
}
?>