<?php
$show_editor = true;
if($id=='1')
{
	$show_editor = false;
	$page_content .= "<h1>Not allowed</h1>You are not allowed to edit the Root group permissions. Users added to this group will have permission to everything.<br>
	<br>
	<a href='".url_build('users-manager-groups')."'>&laquo; Go back</a>";
}
elseif($id==$user['role'])
{
	$show_editor = false;
	$page_content .= "<h1>Not allowed</h1>You are not allowed to edit your own group permissions.<br>
	<br>
	<a href='".url_build('users-manager-groups')."'>&laquo; Go back</a>";
}
elseif(!empty($id))
{
	$page_content = "<h1>Changing groups permissions</h1>";

	$info = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-permissions.nzr');
		$info->readfile();
		$info->search(array('group_id'=>$id));
		$info->rekey(array('object'));
		$loaded_permissions = $info->content;
	$info->close();
	
	//Loading our permissions
	include(newsoffice_directory.$no_config['dir_core'].'permissions.php');
	//Loading plugin permissions
	$plugin_perm_files = glob(newsoffice_directory.$no_config['dir_plugins'].'*/plugin-permissions.nzr');
	if(!empty($plugin_perm_files))
	{
		foreach($plugin_perm_files as $plugin_perm_file)
		{
			$plugin_info = new newanz_nzr($plugin_perm_file);
				$plugin_info->readfile();
				if($plugin_info->amount_rows>0)
				{
					$plugin_info->rekey(array('object'));
					$plugin_permissions[] = $plugin_info->content;
				}
			$plugin_info->close();
		}
	}
}

//Saving
if($_POST['save'])
{
	$show_editor = false;
	$saveP = new newanz_nzr($no_config['dir_info'].'users-permissions.nzr','MULTIPLE_SAVES_FRIENDLY');
		$saveP->readfile();
		//Delete all previous permissions of this group. This is also usefull for permissions that may no longer exists (think of: deleted plugins).
		$saveP->delete(
			array(
				'group_id' => $id
			)
		);
	foreach($no_sec as $sec)
	{
		foreach($no_perm[$sec] as $perm)
		{
			unset($object);
			foreach($perm[2] as $objector)
			{
				if($objector!==$perm[2][0])
				{
					$object .= '_seperator_';
				}
				$object .= $objector;
			}
			if(array_key_exists($object,$_POST['permission'])==false)
			{
				foreach($perm[2] as $objector)
				{
					$saveP->save(
						array(
							'group_id' => $id,
							'object' => $objector,
							'allowed' => '0'
						),
						'new'
					);
				}
			}
		}
	}
	
	if(!empty($plugin_permissions))
	{
		foreach($plugin_permissions as $plugin_permission_contents)
		{
			if(!empty($plugin_permission_contents))
			{
				foreach($plugin_permission_contents as $perm)
				{
					if(array_key_exists($perm['object'],$_POST['permission'])==false)
					{
						$saveP->save(
							array(
								'group_id' => $id,
								'object' => $perm['object'],
								'allowed' => '0'
							),
							'new'
						);
					}
				}
			}
		}
	}
	$saveP->close(); //Write all saves
	
	$page_content .= "The group permissions have been succesfully saved.<br>
	<br>
	<a href='".url_build('users-groups-permissions',$id)."'>&laquo; Go back to editor</a> | <a href='".url_build('users-manager-groups')."'>Go to groups manager &raquo; </a><br>";
}//End save function

if($show_editor==true && !empty($id))
{
	$page_content .= "Change the permissions of this group. Tick the checkboxes of the permissions you wish to grant them access to. For each new group the default permissions allows the users in this group to place comments, login to newsoffice and edit their own profile.<br><br>
	<h2>NewsOffice main permissions</h2>";
	$page_content .= "<table>";
	foreach($no_sec as $sec)
	{
		$page_content .= "
		<tr>
			<td style='width: 20px;'></td>
			<td></td>
		</tr>
		<tr>
			<th colspan='2'>".$no_perm_info[$sec][0]."<br><div class='less_important'>".$no_perm_info[$sec][1]."</div></th>
		</tr>";
		foreach($no_perm[$sec] as $perm)
		{
			$object = '';
			$values = '';
			foreach($perm[2] as $objector)
			{
				if(array_key_exists($objector,$loaded_permissions)==false)
				{
					if(empty($values))
					{
						$values = " checked";
					}
				}
				if($objector!==$perm[2][0])
				{
					$object .= '_seperator_';
				}
				$object .= $objector;
			}
		
			$page_content .= "
			<tr>
				<td>
					<input type='checkbox' name='permission[".$object."]' value='".$object."' id='".$object."'".$values.">
				</td>
				<td><label for='".$object."'>".$perm[0]."</label><br>
				<div class='less_important'>".$perm[1]."</div>
				</td>
			</tr>";
		}
	}
	$page_content .= "</table><a href='".url_build('users-manager-groups')."'>&laquo; Go back</a> | <input type='submit' name='save' value=' Save permissions '>";
	if(!empty($plugin_permissions))
	{
		$page_content .= "<h2>Plugin permissions</h2>
		These are permissions that one or more plugins have entered in your installation. Their default status is on allowed you can change this by <span class='important'>ticking</span> the checkbox.<br>
		<table>";
		foreach($plugin_permissions as $plugin_permission_contents)
		{
			if(!empty($plugin_permission_contents))
			{
				foreach($plugin_permission_contents as $perm)
				{
					$page_content .= "
					<tr>
						<td style='width: 20px;'>
							<input type='checkbox' name='permission[".$perm['object']."]' value='".$perm['object']."'";
					
					if(array_key_exists($perm['object'],$loaded_permissions)==false)
					{
						$page_content .= " checked";
					}
					
					$page_content .= ">
						</td>
						<td>".$perm['name']."<br>
							<div class='less_important'>".$perm['description']."</div>
						</td>
					</tr>";
				}
			}
		}
		$page_content .= "</table>";
		$page_content .= "<a href='".url_build('users-manager-groups')."'>&laquo; Go back</a> | <input type='submit' name='save' value=' Save permissions '>";
	}
}
?>