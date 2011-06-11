<?php
$page_content = "<h1>NewsOffice Updater</h1>
Use this page to check if you have the latest NewsOffice version installed.<br>
<h2>Updater result</h2>
<div class='block'>".$_SESSION[install_id]['updater']['result']['message']."</div>";

if($_SESSION[install_id]['updater']['result']['result']=='error')
{
	$page_content .= "<div class='important'>We have been informed the updater might not be accurate. <a href='".$_SESSION[install_id]['updater']['info_current']['app-link-site']."'>Check our website</a> to keep updated with the latest changes.</div>";
}

if(array_key_exists('updater_recheck',$_POST)==true)
{
	$page_content .= "<div class='important'>";
	if($_SESSION[install_id]['updater']['backup']['message']==$_SESSION[install_id]['updater']['result']['message'])
	{
		$page_content .= "No change since last check.";
	}
	else
	{
		$page_content .= "A change occured, the box above this one should contain new data.";
	}
	$page_content .= "</div>";
}
$page_content .= "<input type='submit' name='updater_recheck' value='Check for updates'><br>
<div class='less_important'>Upon login and every hour an update check is done. Press the button to recheck manually.</div>
<br>
<h2>Your installed version";
if($_SESSION[install_id]['updater']['info_latest']['app-id']==$_SESSION[install_id]['updater']['info_current']['app-id'])
{
	$page_content .= " (Latest)";
}
$page_content .= "</h2>";
	//Load changelog
	$file_used = "changelog.php";
	if(file_exists($file_used)==true)
	{
		$changelog_content = file_get_contents($file_used);
		$changelog_content = explode('?>',$changelog_content);
		$changelog_content = str_replace("\n",'<br>',$changelog_content[1]);
		$page_content .= "Read the <a href='#changelog' onclick=\"document.getElementById('changelog').style.display = 'block'; return false;\">changelog of this version</a>.<br>
<div id='changelog'><div class='important_message_closer' title='Hide changelog.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide changelog.' onclick=\"document.getElementById('changelog').style.display = 'none'; return false;\"></div>".$changelog_content."</div>";
	}
$page_content .= "
<div class='block'>
	<table>
		<col style='width: 150px;'>
		<tr>
			<td class='subject'>
				Application:
			</td>
			<td>
				".$_SESSION[install_id]['updater']['info_current']['app-name']."
			</td>
		</tr>
		<tr>
			<td class='subject'>
				Version:
			</td>
			<td>
				".$_SESSION[install_id]['updater']['info_current']['app-version']." ".ucfirst($_SESSION[install_id]['updater']['info_current']['app-type'])."
			</td>
		</tr>
		<tr>
			<td class='subject'>
				Released:
			</td>
			<td>
		";
		if(substr_count($_SESSION[install_id]['updater']['info_current']['app-date'],'n')>0)
		{
			$page_content .= $_SESSION[install_id]['updater']['info_current']['app-date'];
		}
		else
		{
			$page_content .= no_format_date($_SESSION[install_id]['updater']['info_current']['app-date']);
		}
		$page_content .= "
			</td>
		</tr>
		<tr>
			<td class='subject'>
				Known bugs:
			</td>
			<td>
	";
	if($_SESSION[install_id]['updater']['info_current']['app-bugs']<'0')
	{
		$page_content .= '0';
	}
	$page_content .= $_SESSION[install_id]['updater']['info_current']['app-bugs']." - <a href='http://newanz.com/contact/' target='_blank'>Report</a>
			</td>
		</tr>
		<tr>
			<td class='subject'>
				Known (security) leaks:
			</td>
			<td>
	";
	if($_SESSION[install_id]['updater']['info_current']['app-leaks']<'0')
	{
		$page_content .= '0';
	}
	$page_content .= $_SESSION[install_id]['updater']['info_current']['app-leaks']." - <a href='http://newanz.com/contact/' target='_blank'>Report</a>
		</td>
		</tr>
	</table>
</div>
";
if($_SESSION[install_id]['updater']['info_latest']['app-id']!==$_SESSION[install_id]['updater']['info_current']['app-id'])
{
	$page_content .= "<h2>Latest version</h2>";
	if(empty($_SESSION[install_id]['updater']['info_latest']))
	{
		$page_content .= "<div class='error'>No information avaliable. You might not be connected to the internet or an error occured on the Newanz server.</div>";
	}
	else
	{
		$latest_info = $_SESSION[install_id]['updater']['info_latest'];
		$page_content .= "<div class='block'>
			<table>
				<col style='width: 150px;'>
				<tr>
					<td class='subject'>
						Application:
					</td>
					<td>
						".$latest_info['app-name']."
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Version:
					</td>
					<td>
						".$latest_info['app-version']." ".ucfirst($latest_info['app-type'])."
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Released:
					</td>
					<td>
				";
		if(substr_count($latest_info['app-date'],'n')>0)
		{
			$page_content .= $latest_info['app-date'];
		}
		else
		{
			$page_content .= no_format_date($latest_info['app-date']);
		}
		$page_content .= "
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Known bugs:
					</td>
					<td>
						".$latest_info['app-bugs']."
					</td>
				</tr>
				<tr>
					<td class='subject'>
						Known (security) leaks:
					</td>
					<td>
						".$latest_info['app-leaks']."
					</td>
				</tr>
			</table>
		</div>";
	}
}
?>