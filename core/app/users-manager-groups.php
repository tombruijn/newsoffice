<?php
$page_content = "
<div class='block-right'>
	Actions for selected: <input type='submit' name='delete' value=' Delete '>
	<input type='hidden' name='delete-type' value='user-groups'>
</div>

<h1>User groups manager</h1>
Manage the User groups that you have created, change their names, descriptions and permissions.<br>

<table>
	<tr>
		<th style='width: 20px;'>&nbsp;</th>
		<th>
			Name
		</th>
		<th>
			Members
		</th>
		<th>
			Permissions
		</th>
	</tr>
	<tr class='title'>
		<td style='width: 20px;'></td>
		<td>
			<a href='".url_build('users-create-group')."' class='new'>New</a>
		</td>
		<td colspan='2'>
			&nbsp;
		</td>
	</tr>";
$nzr_keep = $cUsers->set_store();
foreach($user_groups as $info)
{
	$page_content .= "
	<tr>
		<td>
			<input type='checkbox' value='".$info['id']."' name='select[".$info['id']."]'>
		</td>
		<td>
			<a href='".url_build('users-editor-groups',$info['id'])."'".no_group_color($info['id']).">".$info['name']."</a>
		</td>
		<td>";
	$openU = new newanz_nzr();
		$openU->set_import($nzr_keep);
		$openU->search(array('role'=>$info['id']));
	$page_content .= $openU->amount_rows."
		</td>
		<td>
	";
	if($info['id']!=='1')
	{
		$page_content .= "<a href='".url_build('users-groups-permissions',$info['id'])."'>Change &raquo;</a>";
	}
	$page_content .= "&nbsp;</td>
	</tr>";
	$openU->close();
}
$page_content .= "</table>";
?>