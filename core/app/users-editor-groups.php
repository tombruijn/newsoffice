<?php
$show_editor = true;
if(($id=='1' && $users[user]['role']!=='1') || ($id==$users[user]['role'] && $users[user]['role']!=='1'))
{
	$page_content .= "<h1>Not allowed</h1>
	You are not allowed to edit this group.<br>
	<br>
	<a href='".url_build('users-manager-groups')."'>&laquo; Go back</a>";
}
else
{
	if(!empty($id))
	{
		$info = $user_groups[$id];
	}
	if($name=='users-create-group')
	{
		$mode = 'new';
		$back_link = "users-main";
	}
	else
	{
		$mode = 'edit';
		$back_link = "users-manager-groups";
	}
	$page_content = "<h1>";
	if($mode=='new')
	{
		$page_content .= "Create new user group";
	}
	else
	{
		$page_content .= "Edit user group ";
	}
	$page_content .= "</h1>";

	//Saving
	if($_POST['save'])
	{
		if(empty($_POST['name']))
		{
			//Problem with name
			$error = "A name is required for saving this user group";
		}
		else
		{
			$show_editor = false;
			$saveG = new newanz_nzr($no_config['dir_info'].'users-groups.nzr');
				$saveG->readfile();
			if($mode=='new')
			{
				$set1 = 'new';
				$set2 = '';//Limit has no affect here
			}
			else
			{
				$set1 = array('id'=>$id);
				$set2 = 1; //Just affect one record
			}
			$_POST["description"] = strtr($_POST["description"], array("\r\n"=>'',"\r"=>'',"\n"=>''));
			$saveG->save(
				array(
					'name' => $_POST['name'],
					'color' => $_POST['color'],
					'description' => $_POST['description']
				),
				$set1,
				$set2
			);
			if($mode=='new')
			{
				$link_back = 'users-editor-groups';
				$link_id = $saveG->insert_id;
				$page_content .= "User group <span class='important'>".$_POST['name']."</span> succesfully created.";
			}
			else
			{
				$link_back = 'users-editor-groups';
				$link_id = $id;
				$page_content .= "User group <span class='important'>".$_POST['name']."</span> has succesfully been updated.";
			}
			$page_content .= "<br>
			<br>
			<a href='".url_build($link_back,$link_id)."'>&laquo; Return to User group editor</a> | <a href='".url_build('users-manager-groups')."'>Go to the User groups manager &raquo;</a><br>
			<br>";
			
			if($id!=='1')
			{
				$page_content .= "<a href='".url_build('users-groups-permissions',$link_id)."'>Change the permissions of this user group &raquo;</a><br>";
			}
			
			//Save default permissions for new group
			if($mode=='new')
			{
				include($no_config['dir_core'].'permissions.php');
				$saveP = new newanz_nzr($no_config['dir_info'].'users-permissions.nzr');
					$saveP->readfile();
				foreach($no_sec as $sec)
				{
					foreach($no_perm[$sec] as $perm)
					{
						foreach($perm[2] as $object)
						{
							if(in_array($object,$default_permissions)==false)
							{//Save, save and save again. Nzr caches these results so it will be less drastic on performance.
								$saveP->save(
									array(
										'group_id' => $link_id,
										'object' => $object,
										'allowed' => "0"
									),
									'new'
								);
							}
						}
					}
				}
				$saveP->close();
			}
		}
		$saveG->close();		
		if(!empty($error))
		{
			$info['name'] = $_POST['name'];
			$info['color'] = $_POST['color'];
			$info['description'] = $_POST['description'];
			$error = "<div class='no_error'>".$error."</div>";
		}
	}//End save function

	if($show_editor==true)
	{
		$page_content .= "Make changes to this user group and press save to apply them.<br>
		<br>
		".$error."
		<table>
			<tr>
				<td class='subject'>
					Name
				</td>
				<td>
					<input type='text' name='name' id='name' value='".no_convert_field($info['name'])."' onchange='color_preview();' onblur='color_preview();'>
					<div class='less_important'>This name will be displayed on user profiles.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Color
				</td>
				<td>
					#<input type='text' name='color' id='color' value='".no_convert_field($info['color'])."' maxlength='6' onchange='color_preview();' onblur='color_preview();'> Preview: <span id='color_preview'>Color preview</span>
					<div class='less_important'>Color used for user display names and group names.<br>Use hexadecimal color codes only.</div>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Description
				</td>
				<td>
					<textarea name='description' rows='10' cols='10' class='mceEditor'>".no_convert_field($info['description'],true)."</textarea>
					<div class='less_important'>Optional</div>
				</td>
			</tr>
		</table>
		<script type='text/javascript'>color_preview();</script>
		<a href='".url_build($back_link)."'>&laquo; Go back</a> | <input type='submit' name='save' value=' Save profile '>";
	}
}
?>