<?php
$page_content = "<h1>Users</h1>
This is the users section of NewsOffice. You can manage your own profile here and, when you have enough rights, you can manage other users and permissions too.
<ul>";

if(no_is_allowed('users-profile-own')==true)
{
	$page_content .= "<li>
		<a href='".url_build('users-profile-own')."'>Edit your own profile</a>
		<ul>
			<li>Change your display name, description and password here.</li>
		</ul>
	</li>";
}
if(no_is_allowed('users-create-user')==true)
{
	$page_content .= "<li>
		<a href='".url_build('users-create-user')."'>Create a new user account</a>
		<ul>
			<li>Create a new user account for a new person which will get access to this installation.</li>
		</ul>
	</li>";
}
if(no_is_allowed('users-create-group')==true)
{
	$page_content .= "<li>
		<a href='".url_build('users-create-group')."'>Create a new user group</a>
		<ul>
			<li>Create users groups so you can manager your users better. Every group can have different permissions.</li>
		</ul>
	</li>";
}
if(no_is_allowed('users-manager-users')==true)
{
	$page_content .= "<li>
		<a href='".url_build('users-manager-users')."'>Manage users accounts</a>
		<ul>
			<li>Manage existing users; you can edit their profiles, change their group, etc.</li>
		</ul>
	</li>";
}
if(no_is_allowed('users-manager-groups')==true)
{
	$page_content .= "<li>
		<a href='".url_build('users-manager-groups')."'>Manage user groups</a>
		<ul>
			<li>Manage existing groups; edit their descriptions and permissions.</li>
		</ul>
	</li>";
}
$page_content .= "</ul>";
?>