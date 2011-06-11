<?php
$sec = 'creatingplugins';
$extra_title .= 'Creating your own plugins';
$headers[$sec] = array("Creating your own plugins","You can create your own plugins to use in NewsOffice. You can also share these with other users. How to make a plugin is described in this manual. Read it and start programming!");

$h_content[$sec][] = array(1,"Introduction","
	Why would you create a plugin?<br>
	Well when you think we missed a feature or you really want to add something special to NewsOffice you could create a plugin instead of rewritting the whole code.<br>
	<br>
	This manual is written so you can understand how NewsOffice works and how you can \"manipulate\"/use it.
");
$h_content[$sec][] = array(1,"Where to save your plugin","
	From the config.php file NewsOffice retreives a special variable called <span class='important'>".'$no_config[\'dir_plugins\']'."</span>. You can use the directory where this variable points too. The idea is for every plugin to have it's own plugin sub-directory inside that directory. So plugins won't interfere with eachother. For example: data/plugins/myplugin/myfile.php<br>
	<br>
	For more information about where not to save your plugin read, the: Don'ts: Creating plugins.
");
$h_content[$sec][] = array(1,"Don'ts: While creating plugins","
	We really discourage the useage of the <span class='important'>newsoffice_dir_app</span> directory. This is ment for the main features of NewsOffice only. Also, don't let your plugin overwrite files in this folder to \"Improve\" NewsOffice. Any updates, patches, etc. will disgard them and might possibly \"break\" NewsOffice as you intended it to work.<br>
	<br>
	Also, please try not to delete any files or columns in the <span class='important'>.nzr</span> files in the <span class='important'>".$no_config['dir_data']."</span> as the standard functions might need them and if you delete them the functions might not work anymore.<br>
	Adding columns to .nzr files is now supported.");
$h_content[$sec][] = array(1,"Include it in the navigation","
	For you to actually use the plugin you create you should be able to open it from the NewsOffice interface. NewsOffice uses the left sided navigation. This navigation is devided up into categories and pages. Pages are attached to categories to make navigating easier.<br>
	<br>
	In the <span class='important'>".'$no_config[\'dir_info\']'."</span> directory two <span class='important'>.nzr</span> files exist that control these categories and pages. <br>
	<ul>
		<li><span class='important'>nav.nzr</span>
			<ul>
				<li>This file contains each page to link to.</li>
				<li>All pages should be registered in this file, else NewsOffice will not be able to show the correct section in the navigation. Users might get lost.</li>
				<li>As you might not want to clutter in the navigation of NewsOffice you can use the cloak column in the nav.nzr file. When entering a value in the cloak column NewsOffice will not show the page in the navigation. The url you enter there has to exist in the url column you enter there. NewsOffice will then highlight the page with the same url in the navigation.</li>
				<li>Pages are linked to categories by entering a valid id in the <span class='important'>[nav_cat]</span> column.</li>
			</ul>
		</li>
		<li><span class='important'>nav-cat.nzr</span>
			<ul>
				<li>This file contains each category for the navigation.</li>
				<li>ID's lower than <span class='important'>6</span> are <span class='important'>not allowed</span>, this is for the import function. ID's lower than 6 will not be imported.</li>
				<li>The id's that are entered here should match with the id's linked to from the <span class='important'>nav.nzr</span> file.</li>
			</ul>
		</li>
	</ul>
	So to add your plugin to the navigation you can add it to an exisiting category or add a new one. Add your plugin page(s) to a valid category. If you create your own installer for your plugin we recommend you use the <span class='important'>.Nzr class</span> to add records to these files.
");
$h_content[$sec][] = array(1,"Create plugin permissions","
	Maybe you want to give the user the option to secure your plugin?<br>
	If you don't you will allow everyone with access to the NewsOffice Panel to your plugin.<br>
	<br>
	So how do you secure it?<br>
	In your plugin directory you should create a file called <span class='important'>plugin-permissions.nzr</span>. In this file records can be added to give the user the option to disable or allow your plugin for certain users in the Group manager -> Change permissions.<br>
	<br>
	In the plugin-permissions.nzr file 3 columns exist: name, object and description.<br>
	In .Nzr build up: [name][&lt;object&gt;][description];<br>
	<ul>
		<li>name
			<ul>
				<li>The name the user will see for the permission.</li>
			</ul>
		</li>
		<li>object
			<ul>
				<li>The url to this page/function will be called upon.<br>
					When securing an entire page you can enter a url you use in the nav.nzr file. Then the page will also be \"invisible\" for the user in the navigation.<br>
					You can also enter a value for only securing a particual function. Make sure the name (object) you give that permission isn't also a page in the nav.nzr file.</li>
			</ul>
		</li>
		<li>description
			<ul>
				<li>Description of the permission for the users, with the permission to edit the permissions of user groups.</li>
			</ul>
		</li>
	</ul>
	NewsOffice should read your permissions when you open up the user group permission editor. If not, a .Nzr error will probably be visible (in the source code) with an error id.
");
?>