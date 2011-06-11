<?php
$page_content = "<h1>Settings</h1>";
$show_form = true;
$new_no_config = $no_config; //Do not use real array as it might get damaged
if($_POST['save'])
{
	//Valid URL?
	if(empty($_POST['acp_url']) || filter_var($_POST['acp_url'], FILTER_VALIDATE_URL)==false)
	{
		$error[] = 'You have not specified a valid URL for your installation. Please enter a valid URL or uploads and such will not be able to load.';
	}
	//Valid email address?
	if(empty($_POST['acp_email']) || filter_var($_POST['acp_email'], FILTER_VALIDATE_EMAIL)==false)
	{
		$error[] = 'The email address you have specified is incorrect, please enter a valid email address and try again.';
	}
	
	//No errors occured so save the settings
	if(empty($error))
	{
		$show_form = false;
		//Save file
		$saveConfig = new newanz_nzr(newsoffice_directory.'config.php','MULTIPLE_SAVES_FRIENDLY');
			$saveConfig->readfile();
			$nzr_keep = $saveConfig->set_store(); //To prevent loss of comments
				$saveConfig->rekey(array('object'));
				$converted_settings = $saveConfig->content;
			$saveConfig->set_import($nzr_keep); //To prevent loss of comments
			foreach($_POST as $ckey=>$cvalue)
			{
				if($ckey!=='save') //Do not save the save button
				{
					if(array_key_exists($ckey,$converted_settings)==true) //Does setting key exists?
					{
						$saveConfig->save(array('value'=>$cvalue),array('object'=>$ckey),1);
					}
					else
					{//Add new settings value
						$saveConfig->save(array('value'=>$cvalue,'object'=>$ckey),'new');
					}
				}
			}
		$result = $saveConfig->close();
		if($result==true)
		{
			$page_content .= "<h2>Save succesfull</h2>Your settings have been saved succesfully. The new settings are in affect immediately.";
		}
		else
		{
			$page_content .= "<h2>Save unsuccesfull</h2>Your settings could not be saved, please check if everything is working properly on the <a herf='".url_build('system-status')."'>System status</a> in the Support section.";
		}
		$page_content .= "<br><br><a href='".url_build('settings-main')."'>&laquo; Go back</a>";
	}
	else
	{
		//Return entered values
		foreach($_POST as $ckey=>$cvalue)
		{
			$new_no_config[$ckey] = $cvalue;
		}
	}
}

if($show_form==true)
{
	$page_content .= "Here you can change the settings of your NewsOffice installation, make sure everything is correct. If not, NewsOffice can show unexpected behavior.";
	
	if(!empty($error))
	{
		$page_content .= "<div class='error'><h2>Errors</h2>Something went wrong, read the errors below.<br><ul>";
		foreach($error as $object)
		{
			$page_content .= "<li>".$object."</li>";
		}
		$page_content .= "</ul></div>";
	}
	
	$page_content .= "<div class='block'><h2>NewsOffice Panel settings</h2>
		<div class='inside'>
			<table>
				<tr>
					<td class='subject'>
						NewsOffice URL
					</td>
					<td>
						<input type='text' name='acp_url' value='".no_convert_field($new_no_config['acp_url'])."' style='width: 100%;'> <div class='less_important'>Example: http://www.yourwebsite.com/NewsOffice/</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Email address used
					</td>
					<td>
						<input type='text' name='acp_email' value='".no_convert_field($new_no_config['acp_email'])."' style='width: 100%;'>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Date format
					</td>
					<td>
						<input type='text' name='format_date' value='".no_convert_field($new_no_config['format_date'])."' size='10'> <div class='less_important'>Example: d/m/Y = ".date('d/m/Y')."</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Time format
					</td>
					<td>
						<input type='text' name='format_time' value='".no_convert_field($new_no_config['format_time'])."' size='10'> <div class='less_important'>Example: H:i:s = ".date('H:i:s')."</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Default group
					</td>
					<td>
						<select name='set_default_group'>";
			foreach($user_groups as $group)
			{
				$page_content .= "<option value='".$group['id']."'";
				if($group['id']==$new_no_config['set_default_group'])
				{
					$page_content .= " selected";
				}
				$page_content .= no_group_color($group['id']).">".$group['name']."</option>";
			}
			$page_content .= "</select>
						<div class='less_important'>Select the user group where users are automaticly added to when registering.</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class='block'>
	<h2>NewsOffice news page settings</h2>
		<div class='inside'>
			<table style='width: 100%;'>
				<tr>
					<td class='subject'>
						Show # news posts
					</td>
					<td>
						<select name='set_amount_posts'>";
			for($i=0; $i<=100; $i++)
			{
				if($i==0)
				{
					$page_content .= "<option value='all'";
					if($new_no_config['set_amount_posts']=='all')
					{
						$page_content .= " selected";
					}
					$page_content .= ">All</option>";
				}
				else
				{
					$page_content .= "<option value='".$i."'";
					if($new_no_config['set_amount_posts']==$i)
					{
						$page_content .= " selected";
					}
					$page_content .= ">".$i."</option>";
				}
			}
			$page_content .= "</select>
						<div class='less_important'>Select the number of news posts you want to show on each page, on your news page.</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						News order
					</td>
					<td>
						<select name='set_news_order'>";
			$choices[] = array('latest', 'Latest - Oldest');
			$choices[] = array('oldest', 'Oldest - Latest');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice[0]."'";
				if($new_no_config['set_news_order']==$choice[0])
				{
					$page_content .= " selected";
				}
				$page_content .= ">".$choice[1]."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Latest news on top or the oldest?</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Comments order
					</td>
					<td>
						<select name='set_comments_order'>";
			$choices[] = array('latest', 'Latest - Oldest');
			$choices[] = array('oldest', 'Oldest - Latest');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice[0]."'";
				if($new_no_config['set_comments_order']==$choice[0])
				{
					$page_content .= " selected";
				}
				$page_content .= ">".$choice[1]."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Latest comment on top or the oldest?</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Allow comments
					</td>
					<td>
						<select name='set_comments_active'>";
			$choices[] = array('true', 'Yes');
			$choices[] = array('false', 'No');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice[0]."'";
				if($new_no_config['set_comments_active']==$choice[0])
				{
					$page_content .= " selected";
				}
				$page_content .= ">".$choice[1]."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Yes: Comments are allowed and are shown.<br>No: Comments are not allowed and are not shown.</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class='block'>
		<h2>Advanced</h2>
		<div class='inside'>
			<h3>News page</h3>
			<table>
				<tr>
					<td class='subject'>
						Comment time limit
					</td>
					<td>
						<select name='set_comments_limit'>
							<option value='none'>None</option>
							";
			for($i=1; $i<=15; $i++)
			{
				$page_content .= "<option value='".$i."'";
				if($i==$new_no_config['set_comments_limit'])
				{
					$page_content .= " selected";
				}	
				$page_content .= ">".$i."</option>";
			}
			$page_content .= "</select>
						<div class='less_important'>Set the time in minutes for when a user is allowed to post another comment.</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Allow (X)HTML in comments?
					</td>
					<td>
						<select name='set_comments_html'>";
			$choices[] = array('true', 'Yes');
			$choices[] = array('false', 'No');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice[0]."'";
				if($new_no_config['set_comments_html']==$choice[0])
				{
					$page_content .= " selected";
				}
				$page_content .= ">".$choice[1]."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Allow HTML in comments? Recommended: No.<br>This setting only has effect on new comments.</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						HTML version
					</td>
					<td>
						<select name='set_html'>";
			$choices = array('html', 'xhtml');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice."'";
				if($new_no_config['set_html']==$choice)
				{
					$page_content .= " selected";
				}
				$page_content .= ">".strtoupper($choice)."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Which version is important if you want to load your news correctly, according to <a href='http://www.w3.org/'>W3C</a> standards. When changing do also note that this will have to be altered in the TinyMCE configuration as well.</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Change title
					</td>
					<td>
						<select name='set_change_title'>";
			$choices[] = array('true', 'Yes');
			$choices[] = array('false', 'No');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice[0]."'";
				if($new_no_config['set_change_title']==$choice[0])
				{
					$page_content .= " selected";
				}
				$page_content .= ">".ucfirst($choice[1])."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Allow NewsOffice to add the title of the news page to your title bar in your browser. If yes NewsOffice will add: \" | News post title\".</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Already using PHP ".'$_GET'." on your news page?
					</td>
					<td>
						<select name='set_phpget'>";
			$choices[] = array('true', 'Yes');
			$choices[] = array('false', 'No');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice[0]."'";
				if($new_no_config['set_phpget']==$choice[0])
				{
					$page_content .= " selected";
				}
				$page_content .= ">".$choice[1]."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Does the url of your news page already contains: <span class='important'>?var=value&amp;var2=value2</span> (PHP <a href='http://php.net/manual/en/reserved.variables.get.php'>".'$_GET'."</a>) ?</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Admin TinyMCE configuration code
					</td>
					<td>
						<textarea name='acp_tinymce' rows='10' cols='10' style='width: 100%; height: 100px;'>".no_convert_field($new_no_config['acp_tinymce'],true)."</textarea>
						<div class='less_important'>Only used in the Administration panel, what you are in now. See the <a href='http://wiki.moxiecode.com/index.php/TinyMCE:Configuration'>TinyMCE configuration documentation</a> for more information.</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Public TinyMCE configuration code
					</td>
					<td>
						<textarea name='public_tinymce' rows='10' cols='10' style='width: 100%; height: 100px;'>".no_convert_field($new_no_config['public_tinymce'],true)."</textarea>
						<div class='less_important'>Used for user comments etc. See the <a href='http://wiki.moxiecode.com/index.php/TinyMCE:Configuration'>TinyMCE configuration documentation</a> for more information.</div>
					</td>
				</tr>
			</table>
		</div>
		
		<div class='inside'>
			<h3>NewsOffice Panel</h3>
			<table>
				<tr>
					<td class='subject'>
						# items per page
					</td>
					<td>
						<select name='acp_items_page'>
							<option value='all'>All</option>";
			for($i=10; $i<=100; $i += 10)
			{
				$page_content .= "<option value='".$i."'";
				if($i==$new_no_config['acp_items_page'])
				{
					$page_content .= " selected";
				}	
				$page_content .= ">".$i."</option>";
			}
			$page_content .= "</select>
						<div class='less_important'>Select how many news posts, comments, categories, uploads, themes, users and user groups you want to display on a page in the NewsOffice panel.</div>
					</td>
				</tr>
		<!--
				<tr>
					<td class='subject'>
						Save # versions
					</td>
					<td>
						<select name='acp_amount_version'>
							";
			for($i=1; $i<=15; $i++)
			{
				$page_content .= "<option value='".$i."'";
				if($i==$new_no_config['acp_amount_version'])
				{
					$page_content .= " selected";
				}	
				$page_content .= ">".$i."</option>";
			}
			$page_content .= "</select>
						<div class='less_important'>Select how many versions of your news posts are saved. These can be used to revert to another version, or to save drafts while writing. When the amount you select is reached the oldest version is deleted.</div>
					</td>
				</tr>
		-->
				<tr>
					<td class='subject'>
						Load external CSS
					</td>
					<td>
						<input type='text' name='set_external_css' value='".no_convert_field($new_no_config['set_external_css'])."' style='width: 100%;'> <div class='less_important'>Used in the preview function. Example: http://www.yourwebsite.com/style.css</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Check for updates?
					</td>
					<td>
						<select name='set_updates'>";
			$choices[] = array('true', 'Yes');
			$choices[] = array('false', 'No');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice[0]."'";
				if($choice[0]==$new_no_config['set_updates'])
				{
					$page_content .= " selected";
				}	
				$page_content .= ">".$choice[1]."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Get informed about new versions.</div>
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Experimental versions
					</td>
					<td>
						<select name='set_experiment'>";
			$choices[] = array('true', 'Yes');
			$choices[] = array('false', 'No');
			foreach($choices as $choice)
			{
				$page_content .= "<option value='".$choice[0]."'";
				if($choice[0]==$new_no_config['set_experiment'])
				{
					$page_content .= " selected";
				}	
				$page_content .= ">".$choice[1]."</option>";
			} unset($choices);
			$page_content .= "</select>
						<div class='less_important'>Get informed about experimental versions that are released for the public.<br>When using experimental versions you will get informed even when this is turned off.</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<input type='submit' value='Save settings' name='save'>";
}
?>