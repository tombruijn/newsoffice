<?php
$page_content = "<h1>User manager</h1>
Manage users that have registered on your installation. Edit their accounts or delete them.<br>

<div class='block-right'>
	Actions for selected: <input type='submit' name='delete' value=' Delete '>
	<input type='hidden' name='delete-type' value='users'>
</div>

<table>
	<tr>
		<th style='width: 20px;'>&nbsp;</th>
		<th>
			Username
		</th>
		<th>
			Display name
		</th>
		<th>
			Email
		</th>
		<th>
			Status
		</th>
	</tr>
	<tr class='title'>
		<td style='width: 20px;'></td>
		<td>
			<a href='".url_build('users-create-user')."' class='new'>New</a>
		</td>
		<td colspan='3'>
			&nbsp;
		</td>
	</tr>";
foreach($users as $info)
{
	$page_content .= "
	<tr>
		<td>
			<input type='checkbox' value='".$info['id']."' name='select[".$info['id']."]'>
		</td>
		<td>
			<a href='".url_build('users-profile',$info['id'])."'>".$info['username']."</a>
		</td>
		<td>
			<span".no_group_color($info['role']).">".$info['display-name']."</span>
		</td>
		<td>
			".$info['email']."
		</td>
		<td class='status_";
	if(substr_count($info['password'],'activate')>0)
	{
		$page_content .= "false'><span title=\"This user has registered, but not yet activated his/her account. Automated computers, called bots which place spam, usually don't activate.\">Not activated!</span>";
	}
	elseif(substr_count($info['password'],'reset')>0)
	{
		$page_content .= "false'><span title=\"This user has forgotten his/her password or is changing it\'s password\">Password reset</span>";
	}
	else
	{
		$page_content .= "ok'>OK";
	}
	$page_content .= "&nbsp;
		</td>
	</tr>";
}
$page_content .= "</table>";
?>