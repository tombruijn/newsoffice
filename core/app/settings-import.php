<?php
$page_content .= "<h1>Import</h1>";
$page_content .= "Not included in this version. <a href='http://newanz.com/news/76/release-plans-for-newsoffice.html'>Read more about it here.</a>";
/*
$loaded_libraries = get_loaded_extensions();
$compatible_versions = array('newsoffice-2-0-black');
if($_POST['import'])
{
	if(in_array('zip',$loaded_libraries)==false)
	{
		$error = "The PHP zip extension not loaded, could not backup these files.";
	}
	else
	{
		$backup_file = $dir['data']."newsoffice_import.zip";
		if(file_exists($backup_file)==true)
		{
			unlink($backup_file);
		}
		
		//Upload the file
		if(move_uploaded_file($_FILES['import_file']['tmp_name'], $backup_file)==false)
		{
			$error[] = "Unable upload your imported backup to ".$backup_file.". Please check the <a href='".url_build('system-status')."'>system status</a> and if you have selected your back-up file.<br>";
		}
		else
		{
			//Read the zip file
			$import_target = $backup_file;
			if(!is_resource($zip = zip_open($import_target)))
			{
				$import_target = realpath($backup_file);
			}
			
			if(is_resource($zip = zip_open($import_target)))
			{
				//Repeat action for every file in the zip file
				while($zip_entry = zip_read($zip))
				{
					if(zip_entry_open($zip, $zip_entry, "r"))
					{
						//Read all files into an array
						$import_file = zip_entry_name($zip_entry);
						$import_file = str_replace('\\','/',$import_file);
						$import_content = zip_entry_read($zip_entry,zip_entry_filesize($zip_entry));
						$import_file_info = explode('/',$import_file);
						if($import_file_info[0]==$import_file)
						{
							$import_file_info[0] = './';
						}
						if($import_file=='backup.nzr')
						{
							$import_version_info = $import_content;
						}
						else
						{
							$import_files[$import_file_info[0]][] = array($import_file, $import_content);
						}
					}
					zip_entry_close($zip_entry);
				}
				zip_close($zip);
			}
		}
	}
	if(in_array($import_version_info,$compatible_versions)==false)
	{
		$error[] = "You have an incompatible back-up from another version of NewsOffice.<br>Your NewsOffice version id is: <span class='important'>".$app_id."</span> and the back-up is from NewsOffice version <span class='important'>".$import_version_info."</span>.";
	}
	elseif(!empty($import_files))
	{
		//Clean up
		$clean_up_files = glob($dir['data'].'import_*');
		if(!empty($clean_up_files))
		{
			foreach($clean_up_files as $clean_up_file)
			{
				unlink($clean_up_file);
			}
		}
		$array_keys = array_keys($import_files);
		$i['import_dir'] = -1;
		if($_POST['import_option_1']=='delete')
		{
			//Delete existing files
			$delete_dirs = array($dir['news'],$dir['comments'],$dir['uploads']);
			foreach($delete_dirs as $delete_dir)
			{
				$delete_files = glob($delete_dir.'*');
				if(!empty($delete_files))
				{
					foreach($delete_files as $delete_file)
					{
						unlink($delete_file);
					}
				}
			}
		}
		if(!empty($import_files))
		{
			foreach($import_files as $import_files_serie)
			{
				$i['import_dir']++;
				$import_dir = $array_keys[$i['import_dir']];
				if($_POST['import_option_1']=='delete')
				{
					foreach($import_files_serie as $import_file)
					{
						$new_import_file = newsoffice_directory.$dir[$import_dir].str_replace($import_dir.'/','',$import_file[0]);
						$new_import_content = $import_file[1];
						unset($zip_import_file);
						if(is_writable(newsoffice_directory.$dir[$import_dir])==true)
						{
							$zip_import_file = fopen($new_import_file, "w+");
							if($zip_import_file!==false)
							{
								chmod($new_import_file,0777);
								if(fwrite($zip_import_file, $new_import_content)==false)
								{
									$result[] = "<span class='status_false'>Error: Not able to write file: <span class='important'>".$new_import_file."</span></span>";
								}
								else
								{
									fclose($zip_import_file);
									$result[] = "File <span class='important'>".$import_file[0]."</span> is succesfully imported!";
								}
							}
							else
							{
								$result[] = "<span class='status_false'>Error: File <span class='important'>".$import_file[0]."</span> could not be created.</span>";
							}
						}
					}
				}
				//Add the import files into the existing files
				elseif($_POST['import_option_1']=='save')
				{
					//Save temp files
					foreach($import_files_serie as $import_file)
					{
						if($import_file[0]!=='config.php')
						{
							$new_import_file = newsoffice_directory.$dir['data'].'import_'.$import_dir.'_'.str_replace($import_dir.'/','',$import_file[0]);
						}
						else
						{
							$new_import_file = newsoffice_directory.$dir['data'].'import_'.str_replace($import_dir.'/','',$import_file[0]);
						}
						if(file_exists($new_import_file)==true)
						{
							unlink($new_import_file);
						}
						$new_import_content = $import_file[1];
						if(is_writable(newsoffice_directory.$dir['data'])==true)
						{
							$zip_import_file = fopen($new_import_file, "w+");
							if($zip_import_file!==false)
							{
								chmod($new_import_file,0777);
								if(fwrite($zip_import_file, $new_import_content)==false)
								{
									$error[] = "Error: Not able to write temporary file: <span class='important'>".$new_import_file."</span>";
								}
								else
								{
									fclose($zip_import_file);
								}
							}
							else
							{
								$error[] = "Error: Temporary file <span class='important'>".$new_import_file."</span> could not be created.";
							}
						}
					}
				}
			}
			//Continue reading files and put them into the correct files
			if($_POST['import_option_1']=='save' && empty($error))
			{
				//Import Settings
				if($_POST['import_option_2']=='yes')
				{
					$import_config_file = newsoffice_directory.$dir['data'].'import_config.php';
					if(file_exists($import_config_file)==true)
					{
						$current_app_version = $app_version;
						$current_app_id = $app_id;
						include($import_config_file);
						$import_config_file_content = '<?php
//NewsOffice
$app_version = "'.$current_app_version.'";
$app_id = "'.$current_app_id.'";
install_id = "'.install_id.'";
$app_url = "'.$app_url.'";
$app_email = "'.$app_email.'";

//Settings
$settings_posts = "'.$settings_posts.'";
$settings_html = "'.$settings_html.'";
$settings_news_order = "'.$settings_news_order.'";
$settings_comments_order = "'.$settings_comments_order.'";
$settings_comments = "'.$settings_comments.'";
$settings_avatars = "'.$settings_avatars.'";
$settings_phpget = "'.$settings_phpget.'";
$settings_experiment = "'.$settings_experiment.'";
$settings_default_group = "'.$settings_default_group.'";
$settings_active_comments = "'.$settings_active_comments.'";

//Formats
$format_date = "'.$format_date.'";
$format_time = "'.$format_time.'";

//Directories
$dir["core"] = "'.$dir["core"].'";
$dir["app"] = $dir["core"]."'.str_replace($dir['core'],'',$dir["app"]).'";
$dir["data"] = "'.$dir["data"].'";
$dir["news"] = $dir["data"]."'.str_replace($dir['data'],'',$dir["news"]).'";
$dir["comments"] = $dir["data"]."'.str_replace($dir['data'],'',$dir["comments"]).'";
$dir["info"] = $dir["data"]."'.str_replace($dir['data'],'',$dir["info"]).'";
$dir["plugins"] = $dir["data"]."'.str_replace($dir['data'],'',$dir["plugins"]).'";
$dir["uploads"] = $dir["data"]."'.str_replace($dir['data'],'',$dir["uploads"]).'";
$dir["emoticons"] = $dir["data"]."'.str_replace($dir['data'],'',$dir["emoticons"]).'";
$dir["themes"] = $dir["data"]."'.str_replace($dir['data'],'',$dir["themes"]).'";
$dir["no-themes"] = "'.str_replace($dir['data'],'',$dir["no-themes"]).'";
$dir["images"] = "'.$dir["images"].'";
?>';
						$overwrite_config = "config.php";
						if(is_writeable($overwrite_config)==true)
						{
							$file_open = fopen($overwrite_config, "w+");
							fwrite($file_open, $import_config_file_content);
							fclose($file_open);
							$result[] = $overwrite_config." succesfully saved!";
						}
						else
						{
							$error[] = "Could not save the config.php file.";
						}
					}
				}
				//Import users
				//User groups
					$import_user_groups = newanz_nzr_reader(newsoffice_directory.$dir['data'].'import_info_users-groups.nzr','index|id');
					$user_groups = newanz_nzr_reader(newsoffice_directory.$dir['info'].'users-groups.nzr','index|id');
					if(!empty($user_groups))
					{
						foreach($user_groups as $user_group)
						{
							if($user_group['id']>$i['last_group'])
							{
								$i['last_group'] = $user_group['id'];
							}
						}
					}
					if(!empty($import_user_groups))
					{
						foreach($import_user_groups as $import_user_group)
						{
							$i['last_group']++;
							$id_comp['user_groups'][$import_user_group['id']] = array('new' => $i['last_group'], 'import' => $import_user_group['id']);
							$import_nzr_user_groups_content .= '
"'.$i['last_group'].'""'.$import_user_group['name'].'""'.$import_user_group['description'].'""'.$import_user_group['color'].'";';
						}
						$overwrite_file = $dir['info']."users-groups.nzr";
						if(is_writeable($overwrite_file)==true)
						{
							$file_open = fopen($overwrite_file, "a+");
							fwrite($file_open, $import_nzr_user_groups_content);
							fclose($file_open);
							$result[] = $overwrite_file." succesfully saved!";
						}
						else
						{
							$error[] = "Could not save the ".$overwrite_file." file.";
						}
					}
				//Users
					$import_users = newanz_nzr_reader(newsoffice_directory.$dir['data'].'import_info_users.nzr','index|id');
					$current_users = newanz_nzr_reader(newsoffice_directory.$dir['info'].'users.nzr','index|id');
					if(!empty($current_users))
					{
						foreach($current_users as $current_user)
						{
							if($current_user['id']>$i['last_user'])
							{
								$i['last_user'] = $current_user['id'];
							}
						}
					}
					if(!empty($import_users))
					{
						foreach($import_users as $import_user)
						{
							$i['last_user']++;
							$id_comp['users'][$import_user['id']] = array('new' => $i['last_user'], 'import' => $import_user['id']);
							$import_nzr_users_content .= '
"'.$i['last_user'].'""'.$import_user['username'].'""'.$import_user['display-name'].'""'.$import_user['password'].'""'.$import_user['email'].'""'.$id_comp['user_groups'][$import_user['role']]['new'].'""'.$import_user['avatar'].'""'.$import_user['description'].'";';
						}
						$overwrite_file = $dir['info']."users.nzr";
						if(is_writeable($overwrite_file)==true)
						{
							$file_open = fopen($overwrite_file, "a+");
							fwrite($file_open, $import_nzr_users_content);
							fclose($file_open);
							$result[] = $overwrite_file." succesfully saved!";
						}
						else
						{
							$error[] = "Could not save the ".$overwrite_file." file.";
						}
					}
				//Permissions
					$import_permissions = newanz_nzr_reader(newsoffice_directory.$dir['data'].'import_info_users-permissions.nzr','index|id');
					if(!empty($import_permissions))
					{
						foreach($import_permissions as $import_permission)
						{
							$import_nzr_groups_permissions_content .= '
"'.$id_comp['user_groups'][$import_permission['group_id']]['new'].'""'.$import_permission['object'].'""'.$import_permission['allowed'].'";';
						}
						$overwrite_file = $dir['info']."users-permissions.nzr";
						if(is_writeable($overwrite_file)==true)
						{
							$file_open = fopen($overwrite_file, "a+");
							fwrite($file_open, $import_nzr_groups_permissions_content);
							fclose($file_open);
							$result[] = $overwrite_file." succesfully saved!";
						}
						else
						{
							$error[] = "Could not save the ".$overwrite_file." file.";
						}
					}
				
				//Import News
				$import_publish = newanz_nzr_reader(newsoffice_directory.$dir['data'].'import_info_published.nzr','index|index','news_id');
				$news_files = glob(newsoffice_directory.$dir['news'].'*.nzr');
				$news_files = str_replace(newsoffice_directory.$dir['news'],'',$news_files);
				$news_files = str_replace('.nzr','',$news_files);
				$import_id['news'] = ($news_files[count($news_files)-1])+1;
				$import_news_files = glob(newsoffice_directory.$dir['data'].'import_news_*.nzr');
				if(!empty($import_news_files))
				{
					foreach($import_news_files as $object)
					{
						$import_id['news']++;
						while(file_exists(newsoffice_directory.$dir['news'].$import_id['news'].'.nzr')==true)
						{
							$import_id['news']++;
						}
						$import_info = newanz_nzr_reader($object,'index|id'); $import_info = $import_info[0];
						$id_comp['news'][str_replace(newsoffice_directory.$dir['data'].'import_news_','',str_replace('.nzr','',$object))] = array('new' => $import_id['news'], 'import' => $import_info['id']);
						$import_nzr_content = '[id][name][user_id][description][content];
"'.$import_id['news'].'""'.$import_info['name'].'""'.$id_comp['users'][$import_info['user_id']]['new'].'""'.$import_info['description'].'""'.$import_info['content'].'";';
						//Save
						$overwrite_file = $dir['news'].$import_id['news'].".nzr";
						if(is_writeable($dir['news'])==true)
						{
							$file_open = fopen($overwrite_file, "w+");
							fwrite($file_open, $import_nzr_content);
							fclose($file_open);
							$result[] = $overwrite_file." succesfully saved!";
						}
						else
						{
							$error[] = "Could not save the ".$overwrite_file." file.";
						}
						
						//Publish information
						$import_nzr_publish_content .= '
"'.$import_id['news'].'""'.$import_publish[$import_info['id']]['published'].'""'.$import_publish[$import_info['id']]['date'].'""'.$import_publish[$import_info['id']]['time'].'";';
					}
					//Save publish information
					$overwrite_file = $dir['info']."published.nzr";
					if(is_writeable($overwrite_file)==true)
					{
						$file_open = fopen($overwrite_file, "a+");
						fwrite($file_open, $import_nzr_publish_content);
						fclose($file_open);
						$result[] = $overwrite_file." succesfully saved!";
					}
					else
					{
						$error[] = "Could not save the ".$overwrite_file." file.";
					}
				}

				//Import Categories
				$current_categories = newanz_nzr_reader(newsoffice_directory.$dir['info'].'categories.nzr','index|index','id');
				$import_categories = newanz_nzr_reader(newsoffice_directory.$dir['data'].'import_info_categories.nzr','index|index','id');
				if(!empty($current_categories))
				{
					foreach($current_categories as $object_category)
					{
						if($object_category['id']>$i['last_cat'])
						{
							$i['last_cat'] = $object_category['id'];
						}
					}
				}
				if(!empty($import_categories))
				{
					foreach($import_categories as $object_category)
					{
						$i['last_cat']++;
						$id_comp['categories'][$object_category['id']] = array('new' => $i['last_cat'], 'import' => $object_category['id']);
						$import_nzr_categories_content .= '
"'.$i['last_cat'].'""'.$object_category['name'].'""'.$object_category['description'].'";';
					}
					//Save
					$overwrite_file = $dir['info']."categories.nzr";
					if(is_writeable($overwrite_file)==true)
					{
						$file_open = fopen($overwrite_file, "a+");
						fwrite($file_open, $import_nzr_categories_content);
						fclose($file_open);
						$result[] = $overwrite_file." succesfully saved!";
					}
					else
					{
						$error[] = "Could not save the ".$overwrite_file." file.";
					}
				}
				//Import Category links
				$import_categories_links = newanz_nzr_reader(newsoffice_directory.$dir['data'].'import_info_categories-link.nzr','index|id');
				if(!empty($import_categories_links))
				{
					foreach($import_categories_links as $import_categories_link)
					{
						$import_nzr_categories_link_content .= '
"'.$id_comp['categories'][$import_categories_link['cat-id']]['new'].'""'.$id_comp['news'][$import_categories_link['news-id']]['new'].'";';
					}
					//Save
					$overwrite_file = $dir['info']."categories-link.nzr";
					if(is_writeable($overwrite_file)==true)
					{
						$file_open = fopen($overwrite_file, "a+");
						fwrite($file_open, $import_nzr_categories_link_content);
						fclose($file_open);
						$result[] = $overwrite_file." succesfully saved!";
					}
					else
					{
						$error[] = "Could not save the ".$overwrite_file." file.";
					}
				}
				//Import Comments links
				$import_comments_info = newanz_nzr_reader(newsoffice_directory.$dir['data'].'import_info_comments.nzr','index|id');
				if(!empty($import_comments_info))
				{
					foreach($import_comments_info as $import_comments_object)
					{
						$comment_id++;
						while(file_exists(newsoffice_directory.$dir['comments'].$comment_id.'.nzr')==true)
						{
							$comment_id++;
						}
						$id_comp['comments'][$import_comments_object['comment_id']] = array('new' => $comment_id, 'import' => $import_comments_object['comment_id']);
						$import_nzr_comments_info_content .= '
"'.$comment_id.'""'.$id_comp['news'][$import_comments_object['news_id']]['new'].'""'.$import_comments_object['date'].'""'.$import_comments_object['time'].'";';
					}
					//Save
					$overwrite_file = $dir['info']."comments.nzr";
					if(is_writeable($overwrite_file)==true)
					{
						$file_open = fopen($overwrite_file, "a+");
						fwrite($file_open, $import_nzr_comments_info_content);
						fclose($file_open);
						$result[] = $overwrite_file." succesfully saved!";
					}
					else
					{
						$error[] = "Could not save the ".$overwrite_file." file.";
					}
					$file_open = fopen($dir['info'].'data.php', "w+");
					fwrite($file_open, '<?php $comment_id = "'.$comment_id.'"; ?>');
					fclose($file_open);
				}
				
				//Import Comment files
				$import_comments = glob(newsoffice_directory.$dir['data'].'import_comments_*.nzr');
				if(!empty($import_comments))
				{
					foreach($import_comments as $import_object)
					{
						$import_info = newanz_nzr_reader($import_object,'index|id');
						$import_info = $import_info[0];
						$import_nzr_comments = '[id][user_id][content];
"'.$id_comp['comments'][$import_info['id']]['new'].'""'.$id_comp['users'][$import_info['user_id']]['new'].'""'.$import_info['content'].'";';
						//Save
						$overwrite_file = $dir['comments'].$id_comp['comments'][$import_info['id']]['new'].".nzr";
						if(is_writeable($dir['comments'])==true)
						{
							$file_open = fopen($overwrite_file, "w+");
							fwrite($file_open, $import_nzr_comments_info_content);
							fclose($file_open);
							$result[] = $overwrite_file." succesfully saved!";
						}
						else
						{
							$error[] = "Could not save the ".$overwrite_file." file.";
						}
					}
				}
				
				//Themes
				$import_themes = glob(newsoffice_directory.$dir['data'].'import_themes_*.nzr');
				if(!empty($import_themes))
				{
					foreach($import_themes as $import_theme)
					{
						$import_theme_new = $dir['themes'].str_replace(newsoffice_directory.$dir['data'].'import_themes_','',$import_theme);
						while(file_exists($import_theme_new)==true)
						{
							$import_theme_new = $dir['themes'].md5($import_theme_new.date('Y-m-dHiss XD')).'.nzr';
						}
						$overwrite_file = $import_theme_new;
						if(is_writeable($dir['themes'])==true)
						{
							if(rename($import_theme,$import_theme_new)==false)
							{
								$error[] = "Could not save the ".$overwrite_file." file.";
							}
							else
							{
								$result[] = $overwrite_file." succesfully saved!";
							}
						}
						else
						{
							$error[] = "Could not save the ".$overwrite_file." file.";
						}
					}
				}
				//Emoticons
				$import_objects = glob(newsoffice_directory.$dir['data'].'import_emoticons_*');
				if(!empty($import_objects))
				{
					foreach($import_objects as $import_object)
					{
						$import_object_new = $dir['emoticons'].str_replace(newsoffice_directory.$dir['data'].'import_emoticons_','',$import_object);
						$import_object_ext = explode('.',$import_object_new);
						$import_object_ext = $import_object_ext[count($import_object_ext)-1];
						while(file_exists($import_object_new)==true)
						{
							$import_object_new = $dir['emoticons'].md5($import_theme_new.date('Y-m-dHiss XD')).'.'.$import_object_ext;
						}
						if(is_writeable($dir['emoticons'])==true)
						{
							if(rename($import_object,$import_object_new)==false)
							{
								$error[] = "Could not save the ".$import_object_new." file.";
							}
							else
							{	
								$id_comp['emoticons'][str_replace(newsoffice_directory.$dir['data'].'import_emoticons_','',$import_object)] = array('new' => str_replace($dir['emoticons'],'',$import_object_new), 'import' => str_replace(newsoffice_directory.$dir['data'].'import_emoticons_','',$import_object));
								$result[] = $import_object_new." succesfully saved!";
							}
						}
						else
						{
							$error[] = "Could not save the ".$import_object_new." file.";
						}
					}
					$import_objects_records = newanz_nzr_reader($dir['data'].'import_info_emoticons.nzr','index|id');
					if(!empty($import_objects_records))
					{
						unset($import_nzr_record_content);
						foreach($import_objects_records as $import_objects_record)
						{
							$import_nzr_record_content .= '
"'.$import_objects_record['name'].'""'.$import_objects_record['tag'].'""'.$id_comp['emoticons'][$import_objects_record['file']]['new'].'"';
						}
						$file_open = fopen($dir['info'].'emoticons.nzr', "a+");
						fwrite($file_open, $import_nzr_record_content);
						fclose($file_open);
					}
				}
				//Uploads
				$import_objects = glob(newsoffice_directory.$dir['data'].'import_uploads_*');
				if(!empty($import_objects))
				{
					foreach($import_objects as $import_object)
					{
						$import_object_new = $dir['uploads'].str_replace(newsoffice_directory.$dir['data'].'import_uploads_','',$import_object);
						$import_object_ext = explode('.',$import_object_new);
						$import_object_ext = $import_object_ext[count($import_object_ext)-1];
						while(file_exists($import_object_new)==true)
						{
							$import_object_new = $dir['uploads'].md5($import_theme_new.date('Y-m-dHiss XD')).'.'.$import_object_ext;
						}
						if(is_writeable($dir['uploads'])==true)
						{
							if(rename($import_object,$import_object_new)==false)
							{
								$error[] = "Could not save the ".$import_object_new." file.";
							}
							else
							{	
								$id_comp['uploads'][str_replace(newsoffice_directory.$dir['data'].'import_uploads_','',$import_object)] = array('new' => str_replace($dir['uploads'],'',$import_object_new), 'import' => str_replace(newsoffice_directory.$dir['data'].'import_uploads_','',$import_object));
								$result[] = $import_object_new." succesfully saved!";
							}
						}
						else
						{
							$error[] = "Could not save the ".$import_object_new." file.";
						}
					}
					$import_objects_records = newanz_nzr_reader($dir['data'].'import_info_uploads.nzr','index|id');
					if(!empty($import_objects_records))
					{
						unset($import_nzr_record_content);
						foreach($import_objects_records as $import_objects_record)
						{
							$import_nzr_record_content .= '
"'.$import_objects_record['id'].'""'.$id_comp['uploads'][$import_objects_record['file']]['new'].'"';
						}
						$file_open = fopen($dir['info'].'uploads.nzr', "a+");
						fwrite($file_open, $import_nzr_record_content);
						fclose($file_open);
					}
				}
				//Plugin permissions
				$import_objects_records = newanz_nzr_reader($dir['data'].'import_info_plugin-permissions.nzr','index|id');
				if(!empty($import_objects_records))
				{
					unset($import_nzr_record_content);
					foreach($import_objects_records as $import_objects_record)
					{
						$import_nzr_record_content .= '
"'.$import_objects_records['name'].'""'.$import_objects_records['object'].'""'.$import_objects_records['description'].'";';
					}
					$file_open = fopen($dir['info'].'plugin-permissions.nzr', "a+");
					fwrite($file_open, $import_nzr_record_content);
					fclose($file_open);
				}
				
				//Nav settings
					//Categories
					$current_objects_records = newanz_nzr_reader($dir['info'].'nav-cat.nzr','index|index','id');
					$import_objects_records = newanz_nzr_reader($dir['data'].'import_info_nav-cat.nzr','index|id');
					if(!empty($import_objects_records))
					{
						unset($import_nzr_record_content);
						foreach($import_objects_records as $import_objects_record)
						{
							if($import_objects_record['id']>'6')
							{
								$import_save_id = $import_objects_record['id'];
								while(array_key_exists($import_save_id,$current_objects_records)==true)
								{
									$import_save_id++;
								}
								$id_comp['nav-cat'][$import_objects_record['id']] = array('new' => $import_save_id, 'import' => $import_objects_record['id']);
								$import_nzr_record_content .= '
"'.$import_save_id.'""'.$import_objects_record['name'].'""'.$import_objects_record['nav-id'].'""'.$import_objects_record['first-url'].'";';
							}
						}
						$file_open = fopen($dir['info'].'nav-cat.nzr', "a+");
						fwrite($file_open, $import_nzr_record_content);
						fclose($file_open);
					}
					//Nav's
					$current_objects_records = newanz_nzr_reader($dir['info'].'nav.nzr','index|index','url');
					$current_objects_records_2 = newanz_nzr_reader($dir['info'].'nav.nzr','index|index','id');
					$import_objects_records = newanz_nzr_reader($dir['data'].'import_info_nav.nzr','index|id');
					if(!empty($import_objects_records))
					{
						unset($import_nzr_record_content);
						foreach($import_objects_records as $import_objects_record)
						{
							if(array_key_exists($import_objects_record['url'],$current_objects_records)==false)
							{
								$import_save_id = $import_objects_record['id'];
								while(array_key_exists($import_save_id,$current_objects_records_2)==true)
								{
									$import_save_id++;
								}
								$import_nzr_record_content .= '
"'.$import_save_id.'""'.$import_objects_record['name'].'""'.$import_objects_record['nav-id'].'""'.$import_objects_record['first-url'].'";';
							}
						}
						$file_open = fopen($dir['info'].'nav.nzr', "a+");
						fwrite($file_open, $import_nzr_record_content);
						fclose($file_open);
					}
			}
		}
		//Clean up
		$import_delete_files = glob(newsoffice_directory.$dir['data'].'import_*');
		if(!empty($import_delete_files))
		{
			foreach($import_delete_files as $import_delete_file)
			{
				unlink($import_delete_file);
			}
			unlink($backup_file);
		}
	}
	
	if(empty($error))
	{
		$page_content .= "<div class='status_ok'>Import succesfully completed.</div>
		<br>
		<div class='block'>
		<h2>Import results</h2>
		See the progress of each file in the .zip archive you just imported.<br>
		<ul>";
		if(!empty($result))
		{
			foreach($result as $result_object)
			{
				$page_content .= "<li>".$result_object."</li>";
			}
		}
		$page_content .= "</ul></div><a href='".url_build('settings-import')."'>&laquo; Go back</a>";
	}
	else
	{
		$page_content .= "<div class='status_false'>Something went wrong in the back-up process.</div>
		<br>
		<div class='error'><ul>";
		if(!empty($error))
		{
			foreach($error as $error_object)
			{
				$page_content .= "<li>".$error_object."</li>";
			}
		}
		$page_content .= "</ul></div><a href='".url_build('settings-import')."'>&laquo; Go back</a>";
	}
}
else
{
	$page_content .= "Import previously made back-ups from NewsOffice.<br>
	Select your back-up file and press import. If something goes wrong you will be informed.<br>
	<div class='error'>Back-ups have to be made in NewsOffice version 2.0 Beta or higher.<br>
		<div class='less_important'>A converter for NewsOffice 1 Serie might be added later on.</div>
	</div>
	<h2>Import file</h2>
	Select a valid NewsOffice back-up .zip file on your computer.<br><br>
	<input type='file' name='import_file'><br>
	
	<h2>Options</h2>
	How to import your back-up files?<br><br>";
	
	$import_options[] = array("save","<label for='save'>Save current news, comments, users, etc. <span class='important'>(Recommended)</span>","Select if you want to import these settings aswell.</label><br><input type='checkbox' name='import_option_2' id='import_option_2' value='yes'><label for='import_option_2'>Import application settings (required for imported user passwords to work, but we advise you to update your <a href='".url_build('settings-main')."'>settings</a> after import).</label>", true); //True = default
	$import_options[] = array('delete',"<label for='delete'>Total reset to back-up state.</label>","All you current news, comments, categories, etc. will be deleted and the back-up will overwrite all your settings. It will bring back the total state of the back-up. Passwords are also imported from this back-up, you might be logged out! This means you can only login with the login details from the back-up state."); 
	
	foreach($import_options as $import_option)
	{
		$page_content .= "<input type='radio' name='import_option_1' id='".$import_option[0]."' value='".$import_option[0]."'";
		if($_POST['import_option_1']==$import_option[0] || (empty($_POST['import_option_1']) && $import_option[3]==true))
		{
			$page_content .= " checked";
		}
		$page_content .= "> ".$import_option[1]."<br>";
		if(!empty($import_option[2]))
		{
			$page_content .= "<div class='less_important' style='padding-left: 20px;'>".$import_option[2]."</div>";
		}
	}
	
	$page_content .= "<br>
	<input type='submit' name='import' value='Import back-up file'>";
}
*/
?>