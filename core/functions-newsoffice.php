<?php
function url_build($name='',$id='',$pages='')
{
	$url = ''; //Because users can enter wrong url's, this isn't used here: $app_url;
	if(!empty($name))
	{
		$url .= "?name=".$name;
	}
	if(!empty($id))
	{
		$url .= "&amp;id=".$id;
	}
	if(!empty($pages) && $pages!==1)
	{
		$url .= "&amp;page=".$pages;
	}
	return $url;
}//End function

function no_id_convert($string)
{
	$array = explode('-',$string);
	$application = '';
	$version = '';
	$type = '';
	$type_build = '';
	foreach($array as $value)
	{
		if(is_numeric($value)==true)
		{
			if(empty($type))
			{
				$numbers[] = $value;
			}
			else
			{
				$type_build .= $value;
			}
		}
		elseif(is_string($value)==true)
		{
			if($value==$array[0])
			{
				$application = $value;
			}
			elseif($value==$array[count($array)-1] || $value==$array[count($array)-2])
			{
				$type .= $value;
			}
		}
	}
	
	if(!empty($numbers))
	{
		foreach($numbers as $number)
		{
			$version .= $number;
			if($number!==$numbers[count($numbers)-1])
			{
				$version .= '.';
			}
		}
	}
	
	$combined = array('name'=>$application,'type'=>$type,'version'=>$version);
	if(!empty($type_build))
	{
		$combined['build'] = $type_build;
	}
	return $combined;
}//End function

function newsoffice_updater($local_info,$latest_info)
{
	global $no_config;
/*	
	DEVELOPMENT
	$latest_info['app-id'] = 'newsoffice-2-0-6-beta';
	$local_info['app-id'] = 'newsoffice-2-0-5-beta';
*/
	$latest_version = no_id_convert($latest_info['app-id']);
	$current_version = no_id_convert($local_info['app-id']);
	if(empty($latest_info) || empty($local_info) || $latest_info['updater_broken']==true)
	{//Nothing to compare as there is no data
		$result = 'error';
	}
	elseif($latest_info['app-id']==$local_info['app-id'])
	{
		//You have latest version
		$result = 'up-to-date';
	}
	else
	{
		//You do not have latest version, let's find out which one you do have
		//Check for existing types
		$avaliable_types = array('final','rc','beta','black');
		if(in_array($latest_version['type'],$avaliable_types)==true && in_array($current_version['type'],$avaliable_types)==true)
		{
			//Both are accepted types, let's go
			$found_Ltype = array_search($latest_version['type'],$avaliable_types)+1;
			$found_Ctype = array_search($current_version['type'],$avaliable_types)+1;

			if($found_Ctype==count($avaliable_types))
			{
				$black = true;
			}
			
			//New version?
			if($latest_version['version']>$current_version['version'])
			{
				if($found_Ltype!==1 && $no_config['set_experiment']=='true')
				{
					//New version
					$result = 'new';
				}
				else
				{
					//No new version
					$result = 'up-to-date';
				}
			}
			//You have a new-er version
			elseif($latest_version['version']<$current_version['version'])
			{
				$result = 'new-er';
			}
			//Current version is final
			elseif($found_Ctype==1)
			{
				//Want to be informed about developments
				if($found_Ltype!==1 && $no_config['set_experiment']=='true')
				{
					$result = 'new';
				}
				//Does not want to be informed
				else
				{
					$result = 'up-to-date';
				}
			}
			//You are using a development version
			else
			{
				//Latest development version is larger than currently installed
				if($found_Ltype<$found_Ctype)
				{
					$result = 'new';
				}
				//Latest development version is smaller than currently installed
				else
				{
					//Inform
					if($no_config['set_experiment']=='true')
					{
						$result = 'new';
						$message = "A new development version is ready. ".$latest_info['app-name'];
					}
					//Does not want to be informed
					else
					{
						$result = 'up-to-date';
					}
				}
			}
		}
	}
	
	if($result=='up-to-date')
	{
		$inform = false;
		$message = "Congratulations you have the latest version!";
	}
	elseif($result=='new')
	{
		$inform = true;
		if(empty($message))
		{
			$message = "A new version is avaliable! ".$latest_info['app-name']." ".$latest_version['version']." ".ucfirst($latest_version['type']).". <a href='".$latest_info['app-link-download']."'>Download now!</a>";
		}
	}
	elseif($result=='new-er')
	{
		$inform = true;
		$development = true;
		$message = "You have a new-er version than is registered. Only official Newanz testers should have these.<br>Please <a href='http://newanz.com/contact'>contact Newanz</a>.";
	}
	else
	{
		$inform = true;
		$message = "An error occured in the update process, you might not be connected to the internet.<br>
		For a manual check up if you have the latest version go to the Newanz <a href='".$_SESSION[install_id]['updater']['info_latest']['app-link-site']."'>NewsOffice website</a>.<br>
		<br>
		Details of your installed version:<br>
		Application: ".$local_info['app-name']."<br>
		Version: ".$current_version['version']."<br>
		Type: ".ucfirst($current_version['type'])."<br>";
	}
	
	//Inform only when it's a final version and the option is on 
	//or
	//When you are not using a final version
	if(!empty($found_Ltype) && !empty($found_Ctype) && (($found_Ltype!==1 && $no_config['set_experiment']=='true') || $found_Ctype!==0))
	{
		$message .= "<br>Don't want to be <a href='".url_build('settings-main')."'>informed about Experimental versions?</a>";
	}
	
	$total = array('result'=>$result, 'inform'=>$inform, 'message'=>$message,'development'=>$development,'black'=>$black);
	return $total;
}//End function

function no_dir_size($selected_dir,$mode='')
{
	/*
		$mode values
		- empty = do nothing with sub directories.
		- sub_directories = Count all sub directories as well.
		- sub_directories_clean = Count all sub directories, but return in bytes format.
	*/
	$dir_size = 0;
	if(file_exists($selected_dir)==true)
	{
		$sub_files = glob($selected_dir.'*');
		if(!empty($sub_files))
		{
			foreach($sub_files as $sub_file)
			{
				if(is_dir($sub_file)==true)
				{
					//It's a directory, what to do with it?
					if($mode=='sub_directories' || $mode=='sub_directories_clean')
					{
						//We are allowed to scan it
						$dir_scan = no_dir_size($sub_file.'/','sub_directories_clean');
						$dir_size += $dir_scan['size_size'];
					}
				}
				else
				{
					//It's a file. Go add it to the total size
					$dir_size += filesize($sub_file);
				}
			}
		}
	}
	//Very small size
	$size_type = "bytes";
	if($dir_size>0 && $mode!=='sub_directories_clean')
	{
		//Kilobytes
		if($dir_size>1024)
		{
			$dir_size = ceil($dir_size / 1024);
			$size_type = "KiB";
			//Inform
			$message = "<span class='status_ok'>Normal, small, size for an installation.</span>";
		}
		//Megabytes
		if($dir_size>1024)
		{
			$dir_size = ceil($dir_size / 1024);
			$size_type = "MiB";
			if($dir_size>50)
			{
				//Hmm....
				$message = "<span class='status_false'>There is just one thing to do. Cleaning time!</span>";
			}
			elseif($dir_size>10)
			{
				//Hmm....
				$message = "<span class='status_false'>Uploads can make your installation bigger. However, if you have no uploads this is probably too big.</span>";
			}
			elseif($dir_size>5)
			{
				//Hmm....
				$message = "<span class='status_false'>Uploads can make your installation bigger. However, if you have no uploads this is probably too big.</span>";
			}
			elseif($dir_size>2)
			{
				//Ok!
				$message = "<span class='status_ok'>This is still a reasonable size for an installation.</span>";
			}			
			else
			{
				//Ok!
				$message = "<span class='status_ok'>This a reasonable size for an installation.</span>";
			}
		}
		//Gigabtyes
		if($dir_size>1024)
		{
			$dir_size = ceil($dir_size / 1024);
			$size_type = "GiB";
			$message = "<span class='error'>You might want to clean up, because this is way, way too much for a web-installation.</span>";
		}
	}
	
	return array('size_size' => $dir_size, 'size_type' => $size_type, 'message' => $message);
}//End function

function no_check_box($box_type,$box_id)
{
	global $no_config;
	if($box_type=='important_messages')
	{
		$_SESSION[install_id][$box_type]['registered'][] = $box_id;
		//Do nothing
		if($_SESSION[install_id][$box_type]['status'][$box_id]!=='off')
		{
			$result = true;
		}
		else
		{
			$result = false;
		}
	}
	else
	{
		$openBox = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-settings.nzr');
			$openBox->readfile();
			$openBox->search(
				array(
					'user_id'=>user,
					'object'=>$box_type.'_'.$box_id
				)
			);
			if($openBox->amount_rows==0)
			{
				//For this user this action is never saved or has been reset. The default value is:
				if(substr_count($box_type,'advanced')>0)
				{//Hide advanced stuff
					$result = false;
				}
				else
				{//Show normal informational boxes
					$result = true;
				}
			}
			else
			{
				//Action has been preformed. What was it?
				if($openBox->content[0]['value']=='box=off')
				{//Box is turned off
					$result = false;
				}
				else
				{//Box is turned on
					$result = true;
				}
			}
		$openBox->close();
	}
	return $result;
}//End function

function no_advanced_box($box_name, $box_id, $box_content, $box_mode=false)
{
	global $no_config;
	$advanced_box = 'off';
	if(no_check_box('advanced_editor',$box_id)==true)
	{
		$advanced_box = 'on';
	}
	$string = "<div class='advanced_box' id='advanced_box_";
		if($box_mode==true)
		{
			$string .= "content_";
		}
	$string .= $box_id."'";
	if($box_mode==true)
	{
		$string .= " style='display: ";
		if($advanced_box=='on')	{ $string .= "block"; }//Box is turned on
		else { $string .= "none"; }//Box is turned off
		$string .= ";'";
	}
	$string .= "><div id='advanced_box_hider_".$box_id."' class='important_message_closer' title='Hide these advanced options.' style='display: ";
	if($advanced_box=='on')	{ $string .= "block"; }//Box is turned on
	else { $string .= "none"; }//Box is turned off
	$string .= ";'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide these advanced options.' onclick='advanced_options_show(\"".$box_id."\");'></div>";
	if(!empty($box_name))
	{
		$string .= "<h2 onclick='advanced_options_show(\"".$box_id."\");'>".$box_name."</h2>";
	}
	$string .= "<div";
		if($box_mode==false)
		{
			$string .= " id='advanced_box_content_".$box_id."'";
		}
	if($box_mode==false)
	{
		$string .= "style='display: ";
		if($advanced_box=='on')	{ $string .= "block"; }//Box is turned on
		else { $string .= "none"; }//Box is turned off
		$string .= ";'";
	}
	$string .= ">".$box_content."</div></div>";
	return $string;
}//End function
?>