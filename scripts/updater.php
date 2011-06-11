<?php
define("newsoffice_execute", 'TRUE');
define('newsoffice_directory','../');

$step = $_POST['step'];
if(empty($step) || $step==$_GET['step'])
{
	$step = 1;
}
if($step!=='finish')
{
	@session_start();
	include(newsoffice_directory.'config.php');
	if(file_exists(newsoffice_directory.$dir['core'].'newanz-nzr.php')==true)
	{
		include(newsoffice_directory.$dir['core'].'newanz-nzr.php');
	}
	include('nzr_converter.php');
	if(empty($app_id) || empty($app_version))
	{
		define("newsoffice_mode", 'updater');
		include(newsoffice_directory.'core/clean_boot.php');
		$url = $no_config['acp_url'];
		if(empty($url))
		{
			//URL
			$url = 'http';
			if($_SERVER["HTTPS"]=="on")
			{
				$url .= "s";
			}
			$url .= "://";
			if($_SERVER["SERVER_PORT"] != "80")
			{
				$url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			}
			else
			{
				$url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			$url = str_replace('scripts/updater.php','',$url);
		}
		header('Location: '.$url);
		exit();
	}
}
else
{
	define("newsoffice_mode", 'updater');
	include(newsoffice_directory.'core/clean_boot.php');
	@session_destroy();
	$updater_content = "<div class='main'>If you are not beeing redirected that means the URL you specified in your installation is not correct.<br>Go to your installation directory manually and/or remove the <strong>scripts/updater.php</strong> part from the current URL.</div>";
	header('Location: '.$no_config['acp_url']);
}

if($step==1)
{
	$updater_content = "
	<div class='main'>
		<h1>Updater</h1>
		You are about to update to <span class='important'>NewsOffice 2.0.7 Beta</span>.<br>
		You copied the update files into your installation directory which resulted into you seeing this page.<br>
		Now we just need to convert some files to make the new version work.
	</div>
	
	<div class='main'>
		<h1>Automated script run</h1>
		Converting directories to the correct read/write settings.<br>
		<div class='less_important'>If nothing is displayed below, no convertions were needed.</div>
		<ul>
	";
	$fix_directories = array($dir['data'].'archives/',$dir['data'].'tmp/',$dir['data'].'nzr/',$dir['data'].'manuals/');
	foreach($fix_directories as $fix_dir)
	{
		$fix_dird = realpath(newsoffice_directory.$fix_dir);
		if(is_writable($fix_dird)==false)
		{
			$updater_content .= "<li>CHMOD 0777 <span class='important'>".$fix_dir."</span>: - ";
			if(@chmod($fix_dird,0777)==true)
			{
				$updater_content .= "<span class='status_ok'>Succes";
			}
			else
			{
				$updater_content .= "<span class='status_false'>Failure";
			}
			$updater_content .= "</span></li>";
		}
	}
	$updater_content .= "</ul>
	</div>
	
	<div class='main'>
		<h1>Checkup</h1>
		Check the following settings below:<br>
	";
	
	$system_dirs[] = array("Configuration file",'config.php',true);
	$system_dirs[] = array("Main data directory:",$dir["data"],true,true);
	$system_dirs[] = array("Main information directory:",$dir["info"],true,true);
	$system_dirs[] = array("News files directory:",$dir["news"],true,true);
	$system_dirs[] = array("News archives files directory:",$dir["data"].'archives/',true,true);
	$system_dirs[] = array("Temporary files directory:",$dir["data"].'tmp/',true,true);
	$system_dirs[] = array(".Nzr files directory:",$dir["data"].'nzr/',true,true);
	$system_dirs[] = array("Manuals files directory:",$dir['data'].'manuals/',true,true);
	$system_dirs[] = array("Comments files directory:",$dir["comments"],true,true);
	$system_dirs[] = array("Plugins directory:",$dir["plugins"],true,true);
	$system_dirs[] = array("Uploads files directory:",$dir["uploads"],true,true);
	$system_dirs[] = array("Emoticons files directory:",$dir["emoticons"],true,true);
	$system_dirs[] = array("News page themes files directory:",$dir["themes"],true,true);
	$system_dirs[] = array("NewsOffice themes files directory:",$dir["no-themes"],true,true);
	$system_dirs[] = array("NewsOffice themes images directory:",$dir["no-themes"].'newsoffice/'.$dir['images'],true,true);
	
	$updater_content .= "
	<table>
		<col style='width: 250px;'>
		<tr>
			<th>
				Name
			</th>
			<th>
				Directory/File
			</th>
			<th>
				Status
			</th>
		</tr>";
	$sys_status_checked = array();
	foreach($system_dirs as $system_dir)
	{
		unset($error_type);
		$updater_content .= "
		<tr>
			<td>
				<ul><li>".$system_dir[0]."</li></ul>
			</td>
			<td>
				".$system_dir[1]."
			</td>
			<td>
		";
		if($system_dir[2]==false)
		{
			$updater_content .= "-";
		}
		else
		{
			if(in_array(str_replace('/','',$system_dir[1]),$sys_status_checked)==false)
			{
				if(is_writable(newsoffice_directory.$system_dir[1])==false)
				{
					$error_type = 'directory';
					$system_errors[] = array('directory',"System directory/file <span class='important'>".$system_dir[1]."</span> is not writable.");
				}
			}
			if($system_dir[3]==true)
			{
				$system_sub_files = glob($system_dir[1].'*');
				if(!empty($system_sub_files))
				{
					foreach($system_sub_files as $system_sub_file)
					{
						if(in_array(str_replace('/','',$system_sub_file),$sys_status_checked)==false)
						{
							if(is_writable(newsoffice_directory.$system_sub_file)==false)
							{
								$error_type = 'sub_file';
								$system_errors[] = array('sub_file',"System file <span class='important'>".$system_sub_file."</span> is not writable.");
							}
						}
						$sys_status_checked[] = str_replace('/','',$system_sub_file);
					}
				}
			}
			
			if($error_type=='directory')
			{
				$updater_content .= "<span class='status_false'>Error 1: This directory/file is not writable</span>";
			}
			elseif($error_type=='sub_file')
			{
				$updater_content .= "<span class='status_false'>Error 2: This directory and it's subfiles are not writable.</span>";
			}
			else
			{
				$updater_content .= "<span class='status_ok'>Correct</span>";
			}
		}
		
		$updater_content .= "
			</td>
		</tr>";
		$sys_status_checked[] = str_replace('/','',$system_dir[1]);
	}
	$updater_content .= "</table>";

	if(!empty($system_errors))
	{
		$updater_content .= "</div><div class='error'><h2>Errors</h2><ul>";
		foreach($system_errors as $system_error)
		{
			$updater_content .= "<li>".$system_error[1]."</li>";
		}
		$updater_content .= "</ul></div>
		<div class='main'>
			<h1>From Errors to Correct</h1>
			To fix all the errors you might get you need to CHMOD your files to a CHMOD value which makes it writable.<br>
			Login to your website through a FTP connection and CHMOD it to readable, writable and excutable for everyone: CHMOD value 777.<br>
			<br>
			You only need to CHMOD the files and directories (including sub-directories and files in it) that are listed above.<br>
			<br>
			Error 1: The directory or file itself is not writable.<br>
			Error 2: The directory itself and the files inside the directory are not writable.<br>
			Error 3: The files inside the directory are not writable.<br>
			<br>
			Press refresh to recheck. If you are asked about resubmitting the form; press yes.
		</div>
		<div class='block'>";
	}
	else
	{
		unset($_SESSION[$app_install_id]['system-status']);
	}
	$updater_content .= $status_content."</div>";
	
	/*
	$sstatus = new noSystemStatus();
		$sstatus->checkup();
		$messages  = $sstatus->result();			
		$errors = $sstatus->get_errors();
		*/
	
	if(!empty($errors))
	{
		$updater_content .= "</div>".$errors."<div class='main'>";
	}
	
	$updater_content .= $messages."
	</div>
	";
}
elseif($step==2)
{
	$updater_content .= "
	<div class='main'>
	";
	
$nzr = new NzrConverter();
	$result['config'] = $nzr->config();
	if($result['config'][0]==true)
	{
		$result['news_links'] = $nzr->news_links();
		$result['news'] = $nzr->news();
		$result['categories'] = $nzr->categories();
		$result['categories_links'] = $nzr->categories_links();
		$result['comments'] = $nzr->comments();
		$result['comments_links'] = $nzr->comments_links();
		$result['themes'] = $nzr->themes();
		$result['users'] = $nzr->users();
		$result['users_groups'] = $nzr->users_groups();
		$result['users_groups_permissions'] = $nzr->users_groups_permissions();
		$result['uploads'] = $nzr->uploads();
		$result['emoticons'] = $nzr->emoticons(); //Runs always
		$result['notes'] = $nzr->notes(); //Runs always
		$result = array_merge($result,$nzr->acpFiles());
	}

	$updater_content .= "<h1>.Nzr file converter result</h1><ul>";
	foreach($result as $object)
	{
		$updater_content .= "<li";
		if($object[0]==true && $object[0]!=='not_needed')
		{
			$updater_content .= " style='color: green;'";
		}
		elseif($object[0]=='not_needed')
		{
			$updater_content .= " style='color: orange;'";
		}
		elseif($object[0]==false)
		{
			$updater_content .= " style='color: red;'";
		}
		$updater_content .= ">".$object[1]."</li>";
	}
	$updater_content .= "</ul></div>";
}

$updater_content .= "<div class='main' style='text-align: right;'>";
if($step==1)
{
	$updater_content .= "
	<input type='hidden' name='step' value='".($step+1)."'>
	<input type='submit' value='Next step &raquo;'>
	";
}
else
{
	$updater_content .= "
	<input type='hidden' name='step' value='finish'>
	<input type='submit' value='Finish &raquo;'>
	";
}
$updater_content .= "</div>";

/*$updater_render = new noThemeExternal();
$updater_render->set_title('Updater');
$updater_render->set_logo(true);
$updater_render->set_logo_message('NewsOffice updater');
$updater_render->set_width_large(600);
*/
$string = "
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
	<html>
		<head>
			<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
			<title>Updater | NewsOffice</title>
			<link rel='shortcut icon' href='".$app_url.$dir["no-themes"].'newsoffice/'.$dir['images']."newsoffice.ico'>
			<style type='text/css'>
			html,
			body
			{
				margin: 0 auto;
				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				text-align: center;
				
				background-color: #E9E9E9;
			}
			
			form
			{
				margin: 0px;
			}

			a:link,
			a:active,
			a:visited
			{
				color: #444444;
			}

			a:hover
			{
				color: #FFAE00;
				text-decoration: none;
			}
			
			h1
			{
				margin: 2px 0px 2px 0px;
				font-size: 16px;
				font-weight: bold;
			}
			
			h2
			{
				margin: 2px 0px 2px 0px;
				font-size: 12px;
				font-weight: bold;
			}
			.newsoffice_red
			{
				color: #FF0000;
				font-weight: bold;
			}
			
			.main,
			.logo,
			div.important
			{
				width: 600px;
				margin: 0 auto;
				margin-top: 20px;
				padding: 10px;

				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				text-align: left;

				background-color: #FFFFFF;
				border: 1px solid #CCCCCC;
			}
			
			.logo
			{
				position: relative;
				top: 30px;
				margin: 0 auto;
				
				height: 70px;
				text-align: center;
				background-image: url(".$app_url.$dir["no-themes"].'newsoffice/'.$dir['images']."login_logo_newsoffice.gif);
				background-repeat: no-repeat;
				background-position: center center;
				border-bottom: 0px;
			}

			.error
			{
				width: 600px;
				margin: 0 auto;
				margin-top: 20px;
				padding: 10px;

				color: #FFFFFF;
				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				font-weight: bold;
				text-align: left;

				background-color: #FF7700;
				border: 1px solid #560000;
			}
			
			.login-box
			{
				margin-bottom: 3px;
				padding: 2px;
				width: 100%;
				border: 1px solid #CCCCCC;
			}
			
			.important
			{
				font-weight: bold;
			}
			
			div.important
			{
				background-color: #C6F6A1;
				border: 1px solid #666666;
				border-style: dashed;
			}
			
			.less_important
			{
				color: #666666;
				font-size: 10px;
			}
			
			ul,
			ol
			{
				margin: 0px;
				margin-left: 10px;
				padding: 0px;
				padding-left: 10px;

				list-style-type: square;
			}
			
			textarea
			{
				width: 100%;
				height: 400px;
			}
			.text
			{
				width: 100%;
			}
			table
			{
				width: 100%;
				font-size: 100%;
			}
			
			th
			{
				font-weight: bold;
				font-size: 14px;
				text-align: left;
			}
			td
			{
				vertical-align: top;
			}
			td.subject
			{
				width: 120px;
			}
			
			.status_ok
			{
				color: #28C600;
				font-weight: bold;
			}
			.status_false
			{
				color: #FF5500;
				font-weight: bold;
			}
			</style>
		</head>
	<body>
	<form action='' method='post'>
	".$updater_content."
	</form>
	<br>
	<div style='text-align: center; padding-bottom: 10px;'>
		Powered by <a href='http://newsoffice.newanz.com/' title='Go to the NewsOffice official site.'>NewsOffice</a>.
	</div>
</body>
</html>";
echo $string;
//$updater_render->set_content("<form action='' method='post'>".$updater_content."</form>");
//$updater_render->show();
?>