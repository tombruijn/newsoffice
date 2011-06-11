<?php
echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
	<head>
		<meta http-equiv='content-type' content='text/html; charset=iso-8859-1'>
		<meta name='Language' content='English'>

		<title>".$app['title']."</title>
		
		<link rel='stylesheet' href='".$no_config['acp_selected_theme_dir']."/theme_style.css' type='text/css'>
		<link rel='shortcut icon' href='".$no_config['acp_selected_theme_dir_images']."newsoffice.ico'>
		
		<!-- NewsOffice 2.0.12 Beta: JQuery implementation! -->
		<script type='text/javascript' src='".$no_config['dir_scripts']."jquery.js'></script>		
		<!-- NewsOffice 2.0.12 Beta: TinyMCE implementation! -->
		<script type='text/javascript' src='".$no_config['dir_scripts']."tiny_mce/tiny_mce.js'></script>		
		
		<!-- NewsOffice 2.0.3 Beta: New included JavaScript libary -->
		<script type='text/javascript' src='".$no_config['dir_scripts']."js-01.js'></script>		
		<!-- NewsOffice 2.0.4 Beta: Manual overwrite of url's for the helpbox -->
		<script type='text/javascript'>
			var sidebar_width = '270px'; //Overwrite this value (in another file with javascript) in your own themes/scripts/plugins if you want to change the width of the sidebar
			var dir_scripts = '".$no_config['dir_scripts']."'; //Directory of the scripts library
			var set_html = '".$no_config['set_html']."';
			//Add links to the helpbox
			function helpbox_addlinks()
			{
				//Add links
				document.getElementById('helpbox_manuals').setAttribute('href','".url_build("manuals-main")."');
				document.getElementById('helpbox_support').setAttribute('href','".url_build("support-main")."');
				document.getElementById('helpbox_update').setAttribute('href','".url_build("updater")."');
				document.getElementById('helpbox_website').setAttribute('href','http://newanz.com/');
					document.getElementById('helpbox_website').setAttribute('target','_blank'); //New page, we don't want to throw you out of NewsOffice itself.
			}
			
";
?>
tinyMCE.init({
<?php
	echo str_replace('<br>',"\n",$no_config['acp_tinymce']);
?>
});
<?php
echo "
		</script>
	</head>
<body id='body'>
<form action='' name='main_form' method='post' enctype='multipart/form-data'>
<div class='user-info'>";

$permission_edit_own_profile = no_is_allowed('users-profile-own');
if($permission_edit_own_profile==true)
{
	echo "<a href='".url_build('users-profile-own')."'".no_group_color($users[user]['role']).">";
}
echo $users[user]['display-name'];
if($permission_edit_own_profile==true)
{
	echo "</a>";
}
echo "<br>
<a href='".url_build('logout')."'>Logout &raquo;</a></div>
<div class='topnav'>
	<a href='".url_build('support-main')."' class='topnav_link' onclick='helpbox_build(); return false;' onfocus='this.blur();' id='helpbox_link'>Need help?</a> 
";

//Show the "Advanced" button
if(defined('ADVANCED_OPTIONS')==true)
{
	echo "<a href='#Click here to activate advanced editing options.' title='Click here to activate advanced editing options.' class='topnavExp_link";
	if(no_check_box('sidebar',ADVANCED_OPTIONS)==true)
	{
		echo "_active";
	}
	echo "' onclick='advanced_boxes_hider(\"".ADVANCED_OPTIONS."\"); return false;' onfocus='this.blur();' id='advanced_link'>Advanced</a>";
}
echo "
</div>";
?>