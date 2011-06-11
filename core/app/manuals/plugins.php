<?php
$sec = 'plugins';
$extra_title .= 'Plugins';
$headers[$sec] = array("General information about plugins","What are plugins and what should you be carefull of.");

$h_content[$sec][] = array(1,"Introduction: What is a plugin?","
	What is a plugin and what can you do with it?<br>
	A plugin is an addition to the normal installation of an application, NewsOffice in this case. It can be a photo gallery, a counter for something or whatever you want. That's the beauty of a plugins, they can have all kinds of features which you can then use in NewsOffice.<br>
");
$h_content[$sec][] = array(1,"Warning: Be carefull","
	Plugins do not have to be created by Newanz, builder of NewsOffice, but can also be build by other people. A plugin will have access to all your files and data you have saved inside NewsOffice and maybe even outside that. Be very carefull about which plugins you install, because not all will have the good intensions they promise. When you find a plugin, examin it first to see if it does what it promises to do.<br>
	<br>
	If you learned about good or bad plugins or want to know more about avaliable plugins go to the <a href='".$_SESSION[install_id]['updater']['info_current']['app-link-support']."'>support forums</a> and please share your knowledge with the other people.<br>
");
$h_content[$sec][] = array(1,"Where to find plugins?","
	At the time of writing, ".no_format_date('2009-04-18').", no (official) plugins are known to exist yet. That is because this is the Beta version and not everyone knows about the new structure this version uses. But we hope that when people create plugins or find them they share it with us on the <a href='".$_SESSION[install_id]['updater']['info_current']['app-link-support']."'>support forums</a>.
");
$h_content[$sec][] = array(1,"How do I install them?","
	We hope that everyone who builds plugins and shares them also creates it's own installer(s) with manuals. It's up to the creator to decide.<br>
	<br>
	But we recommend plugins are installed in the designated plugins folder, for your installation: <span class='important'>".$no_config['dir_plugins']."</span>
");
$h_content[$sec][] = array(1,"Allow or deny access to plugins","
	If you don't want every user to be able to access your plugins you can set rights for these users on their parent user group. Depending on the creator(s) of the plugin(s) permission are or are not avaliable in the User group permission editor.
");
?>