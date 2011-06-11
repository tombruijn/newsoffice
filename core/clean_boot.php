<?php
session_start();
if(!empty($_SESSION['tmp_no']) && $_SESSION['tmp_no']['login']=='new')
{
	unset($_SESSION['tmp_no']);
	session_regenerate_id();
}
//Define mode
if(defined('newsoffice_mode')==false) //Change path as scripts have another run directory
{
	define("newsoffice_mode", 'acp');
}

if(defined('newsoffice_directory')==false)
{
	if(newsoffice_mode=='script' || newsoffice_mode=='updater') //Change path as scripts have another run directory
	{
		define("newsoffice_directory", '../');
	}
	else
	{
		define("newsoffice_directory", './');//realpath('../'));
	}
}
if(defined('newsoffice_dir_scripts')==false)
{
	define("newsoffice_dir_scripts", 'scripts/');
}
if(defined('newsoffice_dir_core')==false) //Set path when it's not defined?
{
	define("newsoffice_dir_core", 'core/');
}
if(defined('newsoffice_dir_app')==false)
{
	define("newsoffice_dir_app", newsoffice_dir_core.'app/');
	define("newsoffice_dir_lib", newsoffice_dir_core.'libraries/');
	define("newsoffice_execute", 'TRUE');
}
//Load Newanz .Nzr Libary
	require_once(newsoffice_directory.newsoffice_dir_core.'newanz-nzr.php');
	
//Load configuration -> settings
	$openConfig = new newanz_nzr(newsoffice_directory.'config.php');
		$openConfig->readfile();
		if($openConfig->result==true)
		{
			$openConfig->rekey(array('object'));
			foreach($openConfig->content as $ckey=>$cvalue)
			{
				$no_config[$ckey] = $cvalue['value'];
			}
		}
		elseif(defined('load_config')==false)
		{
			define('load_config','FALSE');
		}
		//Manual add-ons
		$no_config['dir_core'] = newsoffice_dir_core;
		$no_config['dir_app'] = newsoffice_dir_app;
		$no_config['dir_lib'] = newsoffice_dir_lib;
		$no_config['dir_scripts'] = newsoffice_dir_scripts;
	$openConfig->close();
//Define unique installation id
if(!empty($no_config['acp_install_id']))
{
	define("install_id",$no_config['acp_install_id']);
}
if(defined("install_id")==true && !empty($_SESSION[install_id]) && !empty($_SESSION[install_id]['updater']) && $_SESSION[install_id]['updater']['result']['black']==true)
{//Only show errors in development mode
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
}
else
{//Normal mode, so no errors
	error_reporting(0);
}
/*
----------------------------------------------------------------------
Load functions and libaries
	Includes libaries and settings that are used througout NewsOffice.
----------------------------------------------------------------------
*/
	require_once(newsoffice_directory.$no_config['dir_core'].'functions-global.php');
//Load Administration Panel libaries and settings only
	if(newsoffice_mode=='acp' || newsoffice_mode=='script' || newsoffice_mode=='updater')
	{
		require_once(newsoffice_directory.$no_config['dir_core'].'functions-newsoffice.php');
		require_once(newsoffice_directory.newsoffice_dir_lib.'system_status.php');
		require_once(newsoffice_directory.newsoffice_dir_lib.'themes_external.php');
		
		$no_version = no_id_convert($no_config['acp_version_id']); //Give back workable array
		$app_name = str_replace('o','O',ucfirst($no_version['name']));
		$app_number = $no_version['version'];
		if($no_version['type']!=='final')
		{
			$app_number .= ' '.ucfirst($no_version['type']);
		}
		if(array_key_exists('build',$no_version)==true)
		{
			$app_number .= ' (Build '.$no_version['build'].')';
		}
		$app_full = $app_name.' '.$app_number;
		define("app_version_full",$app_full);
		define("app_version_name",$app_name);
		define("app_version_number",$app_number);
	}
//Load additional data
	//Get information from URL
		if(!empty($_GET['name'])) { $name = no_clear_url($_GET['name']); }
		if(!empty($_GET['id'])) { $id = no_clear_url($_GET['id']); }
		if(!empty($_GET['title'])) { $title = no_clear_url($_GET['title']); }
		if(!empty($_GET['page'])) { $page = no_clear_url($_GET['page']); }
			if(empty($page)){ $page = 1; } //Default value
	//Theme settings
		$no_config['acp_selected_theme_name'] = 'newsoffice';
		$no_config['acp_selected_theme_dir'] = $no_config['dir_no-themes'].$no_config['acp_selected_theme_name'].'/';
		$no_config['acp_selected_theme_dir_images'] = $no_config['acp_selected_theme_dir'].'images/';

/*
----------------------------------------------------------------------
User information
	Includes information about the registered users and user groups.
	This is used later on in NewsOffice to show author information,
	etc.
----------------------------------------------------------------------
*/
//Users
$cUsers = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr','READ_ONLY');
	$cUsers->readfile();
//User groups
$cGroups = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-groups.nzr','READ_ONLY');
	$cGroups->readfile();
	$cGroups->rekey(array('id'));
	$user_groups = $cGroups->content;
//Login check for administration panel
if(newsoffice_mode=='updater')
{
	
}
elseif((newsoffice_mode=='acp' && defined('install_id')==true) || newsoffice_mode=='script' || defined('newsoffice_mode')==false)
{
	//Temporary usage of usernames as keys for the login function
		$cUsers->rekey(array('username'));
		$users = $cUsers->content;
	//Load permissions
		$user_permissions = no_load_permissions();
	//Require login details
		require_once(newsoffice_directory.$no_config['dir_core'].'login.php');
}
//Return
	$cUsers->rekey(array('id'));
	$users = $cUsers->content;
//Define users
if(!empty($_SESSION[install_id]['user']['id']))
{
	define('user',$_SESSION[install_id]['user']['id']);
}
	$permisisons = no_load_permissions($users[user]['role']);
?>