<?php
//define("newsoffice_execute", 'TRUE');
include('clean_boot.php');
$_SESSION[install_id]['NzrError']['found'] = 'false';
unset($_SESSION[install_id]['important_messages']['registered']);

//Updater
if(
	!empty($new_id) &&
	!empty($no_config['acp_version_id']) &&
	$new_id!==$no_config['acp_version_id']
)
{
	//Update to new version number
	$saveUpdate = new newanz_nzr(newsoffice_directory.'config.php');
		$saveUpdate->readfile();
		$saveUpdate->save(array('value'=>$new_id),array('object'=>'acp_version_id'),1);
		if($saveUpdate->result==true)
		{
			include($no_config['dir_scripts'].'inlineupdater.php');
			$no_version = no_id_convert($new_id);
			//Load changelog
			$file_used = "changelog.php";
			if(file_exists($file_used)==true)
			{
				$changelog_content = file_get_contents($file_used);
				$changelog_content = explode('?>',$changelog_content);
				$changelog_content = str_replace("\n",'<br>',$changelog_content[1]);
				$updater_changelog = "<br><a href='#changelog' onclick=\"document.getElementById('changelog').style.display = 'block'; return false;\">Show the changelog</a><span class='less_important'>, this can always be read in the updater section.</span><br><div id='changelog'>".$changelog_content."</div>";
			}
			$_SESSION[install_id]['important_messages']['registered'][] = 'tmp_updated';
			$important_message .= "<div class='important' id='tmp_updated'><div class='important_message_closer' title='Hide this important message.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide this important message.' onclick='important_message_hider(\"tmp_updated\");'></div>Succesfully updated to the new NewsOffice version ".$no_version['version']." ".ucfirst($no_version['type'])." ".$no_version['type_version'].".".$updater_changelog."</div>";
			unset($_SESSION[install_id]['updater']);
			unset($_SESSION[install_id]['system-status']);
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
			$openConfig->close();
		}
		else
		{
			$important_message .= "<div class='error'>Could not update version number of NewsOffice through the updater. Your <span class='important'>config.php</span> file is not writable.</div>";
		}
	$saveUpdate->close();
}
//Manual recheck of updater
	if($_POST['updater_recheck'] || $updated==true)
	{
		$tmp_result = $_SESSION[install_id]['updater']['result'];
		unset($_SESSION[install_id]['updater']);
		$_SESSION[install_id]['updater']['backup'] = $tmp_result;
	}


$no_install = true;
if(defined('install_id')==false)
{
	if(defined('load_config')==true) //This is as good as any thing I can think of...
	{
		header('Location: scripts/updater.php');
	}
	$no_install = false;
	$install_render = new noThemeExternal();
	//Needs installation
	$install_file = $no_config['dir_core'].'install.php';
	if(file_exists($install_file)==false)
	{
		//Woeps!
		$install_content .= "<h1>Error</h1>
		An major error has occured while trying to load NewsOffice.<br>
		The configuration file says NewsOffice is not yet installed, but can't find the installer file.<br>
		<br>
		Required installer file: ".$install_file."<br>
		
		<h2>Here is what you can do</h2>
		<ul>
			<li>Download the NewsOffice installation package again from <a href='http://newsoffice.newanz.com'>http://newsoffice.newanz.com</a></li>
			<li>Check if you copied the installer file to your installation.</li>
			<li>Manually adjust the configuration file.</li>
			<li>Ask for help on the NewsOffice support forums at <a href='http://newsoffice.newanz.com'>http://newsoffice.newanz.com</a></li>
		</ul>
		
		Good luck,<br>
		NewsOffice development team from <a href='http://newanz.com'>Newanz.com</a><br>
		";		
	}
	else
	{
		//Load installer
		include($install_file);
	}
		if(empty($install_title))
		{
			$install_render->set_title('Login to',false);
		}
		else
		{
			$install_render->set_title($install_title);
		}
		$install_render->set_logo(true);
		$install_render->set_logo_message(app_version_full);
		$install_render->set_width_large(600);
		$install_render->set_content($install_content);
		$install_render->show();
	exit();
}

//If empty url vars
	if(empty($name)){ $name = 'dashboard-main'; }

/*
----------------------------------------------------------------------
Updater
	This function checks if you have the latest version of NewsOffice
	and if not it shows an Important message.
----------------------------------------------------------------------
*/
$allowedUpdateCheck = true;
if($no_config["set_updates"]!=="true" && empty($_POST['updater_recheck']) && $name!=="updater")
{
	$allowedUpdateCheck = false;
	$_SESSION[install_id]['updater'] = array(
		"result"=>array("black"=>"")
	);
}

if($allowedUpdateCheck==true)
{
	//Fix for first run after login
	if(empty($_SESSION[install_id]['updater']['latest_checkup']))
	{
		$_SESSION[install_id]['updater']['latest_checkup'] = (time()-3600);
	}
	//Time has passed and a check for new updates is now done	
	if($_SESSION[install_id]['updater']['latest_checkup']<=(time()-3600))
	{
		//Load version information
		$openVersionFileOnline = new newanz_nzr('http://newsoffice.newanz.com/newsoffice.nzr','LOOSE');
			$openVersionFileOnline->readfile();
			$newsofficeOnlineCheckupFail = false;
			$nzr_keep = $openVersionFileOnline->set_store();
			//Get information about current version
				$openVersionFileOnline->search(array('app-id'=>$no_config['acp_version_id']));
				$version_current = $openVersionFileOnline->content[0];
			//Get information about latest version
				$openVersionFileOnline->set_import($nzr_keep);
				$openVersionFileOnline->search(array('app-latest'=>'yes'));
				$version_latest = $openVersionFileOnline->content[0];
		$openVersionFileOnline->close();

		if(!empty($version_current))
		{
			$version = no_id_convert($version_current['app-id']);
			$version_current['app-version'] = $version['version'];
			$version_current['app-type'] = $version['type'];
		}
		else
		{
			$version_current['app-id'] = $no_config['acp_version_id'];
			$version = no_id_convert($version_current['app-id']);
			$version_current['app-version'] = $version['version'];
			$version_current['app-type'] = $version['type'];
			$version_current['app-name'] = 'NewsOffice';
			$version_current['app-date'] = 'Unknown, need internet connection.';
			$version_current['app-bugs'] = 'Unknown, need internet connection.';
			$version_current['app-leaks'] = 'Unknown, need internet connection.';
			$version_current['app-link-site'] = 'http://newsoffice.newanz.com/';
			$version_current['app-link-download'] = 'http://newsoffice.newanz.com/?page=download';
			$version_current['app-link-project'] = 'http://newanz.com/item/1/newsoffice.html';
			$version_current['app-link-support'] = 'http://newanz.com/forums/forum/4/forum-newsoffice.html';
		}
		if(!empty($version_latest))
		{
			$version = no_id_convert($version_latest['app-id']);
			$version_latest['app-version'] = $version['version'];
			$version_latest['app-type'] = $version['type'];
		}
		else
		{
			$version_latest['app-id'] = $no_config['acp_version_id'];
			$version_latest['app-name'] = 'NewsOffice';
			$version_latest['app-version'] = 'Unknown, need internet connection.';
			$version_latest['app-date'] = 'Unknown, need internet connection.';
			$version_latest['app-bugs'] = 'Unknown, need internet connection.';
			$version_latest['app-leaks'] = 'Unknown, need internet connection.';
			$version_latest['app-link-site'] = 'http://newsoffice.newanz.com/';
			$version_latest['app-link-download'] = 'http://newsoffice.newanz.com/?page=download';
			$version_latest['app-link-project'] = 'http://newanz.com/item/1/newsoffice.html';
			$version_latest['app-link-support'] = 'http://newanz.com/forums/forum/4/forum-newsoffice.html';
			$version_latest['updater_broken'] = true;
		}
		$_SESSION[install_id]['updater']['info_current'] = $version_current;
		$_SESSION[install_id]['updater']['info_latest'] = $version_latest;
	}

	if($_SESSION[install_id]['updater']['latest_checkup']<=(time()-3600))
	{
		$_SESSION[install_id]['updater']['result'] = newsoffice_updater($_SESSION[install_id]['updater']['info_current'],$_SESSION[install_id]['updater']['info_latest']);
		$_SESSION[install_id]['updater']['latest_checkup'] = time();
	}
}
/*
----------------------------------------------------------------------
		System status global check
		List of system files and directories
----------------------------------------------------------------------
*/
if(empty($_SESSION[install_id]['system-status']['latest_checkup']) || $_POST['system-status_recheck'])
{
	$_SESSION[install_id]['system-status']['latest_checkup'] = time()-3600;
}
if($_SESSION[install_id]['system-status']['latest_checkup']<=(time()-3600))
{
	$sstatus = new noSystemStatus();
		$sstatus->checkup();
		$_SESSION[install_id]['system-status']['result'] = $sstatus->result();			
		$errors = $sstatus->get_errors();
		if(!empty($errors))
		{
			$_SESSION[install_id]['system-status']['errors'] = $errors;
		}
	$_SESSION[install_id]['system-status']['latest_checkup'] = time();
}
if($name=='system-status')
{
	define('system_status_result',$_SESSION[install_id]['system-status']['result']);
	if(!empty($_SESSION[install_id]['system-status']['errors']))
	{
		define('system_status_errors',$_SESSION[install_id]['system-status']['errors']);
	}
}
//_____________________________________________________________________________________________________________________________________________
//		Loading correct page
//_____________________________________________________________________________________________________________________________________________
if(substr_count($name,'plugin-')>0)
{
	//Plugins
	$plugin_objects = str_replace('plugin-','',$name);
	$plugin_objects = explode('_',$plugin_objects);
	foreach($plugin_objects as $plugin_object)
	{
		if($plugin_object==$plugin_objects[count($plugin_objects)-1])
		{
		}
		else
		{
			$selected_plugin_dir .= $plugin_object.'/';
		}
	}
	$selected_plugin_dir =  newsoffice_directory.$no_config['dir_plugins'].$selected_plugin_dir;
	$selected_plugin_file = $selected_plugin_dir.$plugin_objects[count($plugin_objects)-1].'.php';
	if(file_exists($selected_plugin_dir)==true && is_dir($selected_plugin_dir)==true && file_exists($selected_plugin_file)==true)
	{
		$load_file = $selected_plugin_file;
	}
}
else
{
	//Normal files
	$load_file = newsoffice_directory.$no_config['dir_app'].$name.'.php';
}
//Load file
if(file_exists($load_file)==true)
{
	//Load file if exists
	if(no_is_allowed($name)==true)
	{
		if(array_key_exists('delete',$_POST)==true || array_key_exists('delete-yes',$_POST)==true)
		{
			include(newsoffice_directory.$no_config['dir_app'].'deleter.php');
		}
		else
		{
			include($load_file);
		}
	}
	else
	{
		$not_allowed = true;
	}
}
//Include nav + title generator
include(newsoffice_directory.$no_config['dir_core'].'nav.php');

//_____________________________________________________________________________________________________________________________________________
//		Load important messages
//_____________________________________________________________________________________________________________________________________________
//System status
$box_id = 'system-status';
if(!empty($_SESSION[install_id]['system-status']['errors']) && $name!==$box_id)
{
	if(no_check_box('important_messages',$box_id)==true)
	{
		$important_message .= "
		<div class='error' id='".$box_id."'>
			<div class='important_message_closer' title='Hide this important message.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide this important message.' onclick='important_message_hider(\"".$box_id."\");'></div>
			<h2>System status</h2>
			The automatic check-up on the system status has found an error.<br>
		";
		if(no_is_allowed('system-status')==true)
		{
			$important_message .= "
				Please go to the <a href='".url_build('system-status')."'>System status</a> page to fix this problem.
			";
		}
		else
		{
			$important_message .= "Contact an Administrator to inform them about this error.";
		}
		$important_message .= "<br>
			NewsOffice might show some unexpected behavior otherwise.<br>
			<div class='less_important'>Upon every login and every hour an automatic check-up is done.</div>
		</div>";
	}
}
//Updater results
$box_id = 'updater-result';
if($_SESSION[install_id]['updater']['result']['inform']==true)
{
	if(no_check_box('important_messages',$box_id)==true)
	{
		$important_message .= "<div class='updater' id='".$box_id."'>
		<div class='important_message_closer' title='Hide this important message.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide this important message.' onclick='important_message_hider(\"".$box_id."\");'></div>
		".$_SESSION[install_id]['updater']['result']['message']."
		</div>";
	}
}
//.Nzr errors
$box_id = '.nzr-error';
if(defined('nzr_error')==true)
{
	if(no_check_box('important_messages',$box_id)==true)
	{
		$important_message .= "<div class='important' id='".$box_id."'>
		<div class='important_message_closer' title='Hide this important message.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide this important message.' onclick='important_message_hider(\"".$box_id."\");'></div>
		One or more errors occured while reading one or more .Nzr type files. Please look into the source code of this page and look for &quot;.Nzr Error&quot; for more details.</div>";
	}
}

//Application title
$app['title'] .= ' | '.app_version_name;

//Fetch error
if(!empty($not_allowed) && $not_allowed==true)
{
	$page_content = "<h1>Not allowed</h1>";
}
//Not allowed to enter newsoffice at all!
elseif(no_is_allowed('newsoffice')==false)
{
	session_destroy();
	header("Location: ".$no_config['acp_url']);
	exit();
}
elseif(empty($page_content))
{
	$page_content = "<h1>Page not found</h1>
	This page could not be found or the file that should generate it returned no content.<br>
	<br>
	Use the navigation on the left (default setting) to find your way (back) to the page you were trying to go to. I suggest starting with the Dashboard. Also inform your Administrator about this problem. He/she will surely appriciate it.<br>
	If you are an Administrator and you do not know what caused this: inform <a href='http://newanz.com/contact/'>Newanz</a>, developer of NewsOffice. Reporting this will improve new versions of NewsOffice.<br>";
}
//Load theme and content
echo newsoffice_copyright; //Do not remove this as it's in confilict of the Terms of Use for Applications you agreed to be bound with.
include($no_config['dir_core'].'app-start.php');
include($no_config['acp_selected_theme_dir']."theme_start.php");
if(empty($not_allowed) || $not_allowed==false)
{
	if(!empty($important_message))
	{
		echo "<div class='important_messages' id='important_messages'>".$important_message."</div>";
	}
}
echo $page_content;
include($no_config['acp_selected_theme_dir']."theme_close.php");
include($no_config['dir_core'].'app-close.php');
?>