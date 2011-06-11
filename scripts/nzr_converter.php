<?php
/********************************************************************
			Converter
				Converts .Nzr files from .Nzr Beta 1 to Beta 2
				Only for NewsOffice!
********************************************************************/
class NzrConverter
{
	function constructer()
	{
		//Load configuration -> settings
		$openConfig = new newanz_nzr(newsoffice_directory.'config.php');
			$openConfig->readfile();
			$openConfig->rekey(array('object'));
			foreach($openConfig->content as $ckey=>$cvalue)
			{
				$no_config[$ckey] = $cvalue['value'];
			}
		$openConfig->close();
		$this->no_config = $no_config;
	}//End function

	public function exceptions($string)
	{
		//Previously by NewsOffice handled exceptions which are now handeled in .Nzr itself.
			$string = html_entity_decode($string,ENT_QUOTES);
			$string = str_replace('<!--nzr-convert-;-->',';',$string);
			$string = str_replace('<!--nzr-convert-dot-comma-->',';',$string);
			$string = str_replace('<!--nzr-convert-slash-->','\\',$string);
			$string = str_replace(htmlentities('<!--nzr-convert-slash-->',ENT_QUOTES),'\\',$string);
		return $string;
	}//End function
	
	public function notes()
	{
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'notes.txt');
			$new = str_replace("<br>","\n",$this->exceptions($object->original));
		$object->save_file($new);
		if($object->result==true)
		{
			$result = array(true,"Notes converted succesfully.");
		}
		else
		{//Problem
			$result = array(false,'An .Nzr error occured while converting the notes.txt file. See the source code of this HTML webpage for the errors.');
		}
		return $result;
	}//End function
	
	/********************************************************************
				Configuration file
	********************************************************************/
	public function config()
	{
		global $app_version;
		global $app_id;
		global $app_install_id;
		$continue = false;
		if(!empty($app_version) && !empty($app_id))
		{//These variables are no longer used in 2.0.7 Beta, the version the old config.php (not .nzr based) went out of business
			$continue = true;
		}

		//Do need convertion?
		if($continue==true)
		{
			//Read existing values
				include(newsoffice_directory.'config.php');
			//Start build up of new format
				$object = new newanz_nzr(newsoffice_directory.'config.php','create');
					$object->create_file(array('object','value'),true);
				$object->close();
				
				$object = new newanz_nzr(newsoffice_directory.'config.php');
					$object->readfile();
					$object->set_comment('keys','<?php if(defined(\'newsoffice_execute\')==false) { exit(); } ?>');
					$object->set_primary_keys(array('object'));
				$object->close();
			
				$object = new newanz_nzr(newsoffice_directory.'config.php','MULTIPLE_SAVES_FRIENDLY');
					$object->readfile();
					$options['acp_version_id'] = $app_id;
					$options['acp_install_id'] = $app_install_id;
					$options['acp_url'] = $app_url;
					$options['acp_email'] = $app_email;
					$options['acp_items_page'] = 20;
					$options['acp_amount_version'] = 5;
					$options['set_amount_posts'] = $settings_posts;
					$options['set_html'] = $settings_html;
					$options['set_news_order'] = $settings_news_order;
					$options['set_comments_order'] = $settings_comments_order;
					$options['set_comments_active'] = $settings_comments;
					$options['set_comments_limit'] = 3;
					$options['set_comments_html'] = 'false';
					$options['set_default_group'] = $settings_default_group;
					$options['set_phpget'] = $settings_phpget;
					$options['set_experiment'] = $settings_experiment;
					$options['set_external_css'] = '';
					$options['set_change_title'] = 'true';
					$options['format_date'] = $format_date;
					$options['format_time'] = $format_time;
					
					$options['dir_data'] = $dir['data'];
					$options['dir_nzr'] = $dir['data'].'nzr/';
					$options['dir_news'] = $dir['news'];
					$options['dir_archives'] = $dir['data'].'archives/';
					$options['dir_comments'] = $dir['comments'];
					$options['dir_info'] = $dir['info'];
					$options['dir_manuals'] = $dir['data'].'manuals/';
					$options['dir_plugins'] = $dir['plugins'];
					$options['dir_uploads'] = $dir['uploads'];
					$options['dir_emoticons'] = $dir['emoticons'];
					$options['dir_themes'] = $dir['themes'];
					$options['dir_tmp'] = $dir['data'].'tmp/';
					$options['dir_no-themes'] = $dir['no-themes'];
					$options['dir_images'] = $dir['images'];
					$options['dir_manuals'] = $dir['data'].'manuals/';
					
					foreach($options as $key=>$option)
					{
						$new_options[$key] = $this->exceptions($option);
					}
				
					foreach($new_options as $key=>$option)
					{
						$object->save(array('object'=>$key,'value'=>$option),'new');
					}
			//Result?
			if($object->close()==true)
			{//Succes
				$this->constructer();
				return array(true,"Configuration file converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
			
		}
		else
		{
			return array('not_needed','Configuration file did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				News Links
	********************************************************************/
	public function news_links()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'published.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			$auto_keys = $object->return_auto_keys();
			if(empty($auto_keys))
			{
				$continue = true;
			}

		//Do need convertion?
		if($continue==true)
		{
			//Set new primary key
			$object->set_auto_keys(array('news_id'));
			$object->set_primary_keys(array('published'));
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = $value;
					}
					$object->save($save,array('news_id'=>$save['news_id']),1);
				}
			}
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"News links converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','News links did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				News
	********************************************************************/
	public function news()
	{
		//Handle news files
		$files = glob(newsoffice_directory.$this->no_config['dir_news'].'*.nzr');
		if(!empty($files))
		{
			foreach($files as $file)
			{
				//Read existing values
				$object = new newanz_nzr($file,'MULTIPLE_SAVES_FRIENDLY');
					$object->readfile();
					$continue = false;
					if(in_array('user-id',$object->keys)==true)
					{
						$continue = true;
					}
				
				//Do need convertion?
				if($continue==true)
				{
					foreach($object->content as $line_key=>$values)
					{
						foreach($values as $key=>$value)
						{
							$new_values[$key] = NzrConverter::exceptions($value);
						}
						$object->save($new_values,array($key=>$value));
					}
					$object->alter_keys(array('user-id'=>'user_id'));
					$object->add_keys(array('version'=>'published','updated'=>date('Y-m-d_H:i:s'),'published'=>'','date'=>'','time'=>''));
					//Result?
					if($object->result==true && $result[0]!==false)
					{//Succes
						$result = array(true,"News posts converted succesfully.");
					}
					else
					{//Problem
						$result = array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
					}
				}
				else
				{
					$result = array('not_needed','News posts did not need convertion.');
				}
				$object->close();
			}
			return $result;
		}
	}//End function
	
	/********************************************************************
				Categories
	********************************************************************/
	public function categories()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'categories.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			$auto_keys = $object->return_auto_keys();
			if(empty($auto_keys))
			{
				$continue = true;
			}

		//Do need convertion?
		if($continue==true)
		{
			//Set new primary key
			$object->set_auto_keys(array('id'));
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = NzrConverter::exceptions($value);
					}
					$object->save($save,array('id'=>$save['id']),1);
				}
			}
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"Categories converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','Categories did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				Categories Links
	********************************************************************/
	public function categories_links()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'categories-link.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			if(in_array('cat-id',$object->keys)==true && in_array('news-id',$object->keys)==true)
			{
				$continue = true;
			}

		//Do need convertion?
		if($continue==true)
		{
			$object->alter_keys(array('cat-id'=>'category_id','news-id'=>'news_id'));
			$object->set_primary_keys(array('category_id','news_id'));
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"Categories links converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','Categories links did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				Comments
	********************************************************************/
	public function comments()
	{
		//Handle theme files
		$files = glob(newsoffice_directory.$this->no_config['dir_comments'].'*.nzr');
		if(!empty($files))
		{
			foreach($files as $file)
			{
				//Read existing values
				$object = new newanz_nzr($file);
					$object->readfile();
					$continue = false;
					if(in_array('user-id',$object->keys)==true)
					{
						$continue = true;
					}
				
				//Do need convertion?
				if($continue==true)
				{
					foreach($object->content as $line_key=>$values)
					{
						foreach($values as $key=>$value)
						{	
							$new_values[$key] = NzrConverter::exceptions($value);
						}
						$object->save($new_values,array($key=>$value));
					}
					$object->alter_keys(array('user-id'=>'user_id'));
					//Result?
					if($object->result==true)
					{//Succes
						$result = array(true,"Comments converted succesfully");
					}
					else
					{//Problem
						$result = array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
					}
				}
				else
				{
					$result = array('not_needed','Comments did not need convertion.');
				}
				$object->close();
			}
			return $result;
		}
	}//End function
	
	/********************************************************************
				Comments Links
	********************************************************************/
	public function comments_links()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'comments.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			$auto_keys = $object->return_auto_keys();
			if(empty($auto_keys))
			{
				$continue = true;
			}

		//Do need convertion?
		if($continue==true)
		{
			//Set new primary key
			$object->set_auto_keys(array('comment_id','news_id'));
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = $value;
					}
					$object->save($save,array('comment_id'=>$save['comment_id']),1);
				}
			}
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"Comment links converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','Comment links did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				Themes
	********************************************************************/
	public function themes()
	{
		//Handle theme files
		$theme_files = glob(newsoffice_directory.$this->no_config['dir_themes'].'*.nzr');
		if(!empty($theme_files))
		{
			foreach($theme_files as $theme_file)
			{
				//Read existing values
				$openTheme = new newanz_nzr($theme_file);
					$openTheme->readfile();
					$theme_content = $openTheme->content[0];
					$continue['themes'] = false;
					if(in_array('object',$openTheme->keys)==false && in_array('value',$openTheme->keys)==false)
					{
						$continue['themes'] = true;
					}
				$openTheme->close();
				//Do need convertion?
				if($continue['themes']==true)
				{
					//Clean file, create a new one
					$saveNew = new newanz_nzr($theme_file,'create');
						$saveNew->create_file(array('object','value'),true);
					$saveNew->close();
					
					//Save modified values
					$saveTheme = new newanz_nzr($theme_file,'MULTIPLE_SAVES_FRIENDLY');
						$saveTheme->readfile();
						foreach($theme_content as $key=>$object)
						{
							$object = str_replace('[display-name]','[author]',$object);
							$object = str_replace('[content+]','[content]',$object);
							//Set in save que
							$saveTheme->save(array('object'=>$key,'value'=>NzrConverter::exceptions($object)),'new');
						}
						
					//Additional new values
						$saveTheme->save(array('object'=>'theme_error-comment-failure','value'=>"Your comment could not be saved. Please try again or contact your Administrator."),'new');
						$saveTheme->save(array('object'=>'theme_message-7','value'=>"Succesfully logged in. Welcome [user]."),'new');
						$saveTheme->save(array('object'=>'theme_message-8','value'=>"Your login details do not match any known user. Please try again."),'new');
						$saveTheme->save(array('object'=>'theme_message-9','value'=>"Succesfully logged out. Come back again, [user]."),'new');
						$saveTheme->save(array('object'=>'theme_name-login','value'=>"Login"),'new');
						$saveTheme->save(array('object'=>'theme_name-logout','value'=>"Logout"),'new');
						
					//Result?
					if($saveTheme->close()==true && $result[0]!==false)
					{//Succes
						$result = array(true,"Themes converted succesfully.");
					}
					else
					{//Problem
						$result = array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
					}
				}
				else
				{
					$result = array('not_needed','Themes did not need convertion.');
				}
			}
		}
		return $result;
	}//End function
	
	/********************************************************************
				Emoticons
	********************************************************************/
	public function emoticons()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'emoticons.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = true;

		//Do need convertion?
		if($continue==true)
		{
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = NzrConverter::exceptions($value);
					}
					$object->save($save,array('tag'=>$save['tag']),1);
				}
			}
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"Emoticons converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','Emoticons did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				Uploads
	********************************************************************/
	public function uploads()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'uploads.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			$auto_keys = $object->return_auto_keys();
			if(empty($auto_keys))
			{
				$continue = true;
			}

		//Do need convertion?
		if($continue==true)
		{
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = NzrConverter::exceptions($value);
					}
					$object->save($save,array('id'=>$save['id']),1);
				}
			}
			$object->set_auto_keys(array('id'));
			$object->add_keys(array('name'=>''));
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"Uploads converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','Uploads did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				Users
	********************************************************************/
	public function users()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'users.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			$auto_keys = $object->return_auto_keys();
			if(empty($auto_keys))
			{
				$continue = true;
			}

		//Do need convertion?
		if($continue==true)
		{
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = NzrConverter::exceptions($value);
					}
					$object->save($save,array('id'=>$save['id']),1);
				}
			}
			$object->set_auto_keys(array('id'));
			$object->set_primary_keys(array('username'));
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"Users converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','Users did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				User groups
	********************************************************************/
	public function users_groups()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'users-groups.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			$auto_keys = $object->return_auto_keys();
			if(empty($auto_keys))
			{
				$continue = true;
			}

		//Do need convertion?
		if($continue==true)
		{
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = NzrConverter::exceptions($value);
					}
					$object->save($save,array('id'=>$save['id']),1);
				}
			}
			$object->set_auto_keys(array('id'));
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"User groups converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','User groups did not need convertion.');
		}
	}//End function
	/********************************************************************
				User groups permissions
	********************************************************************/
	public function users_groups_permissions()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'users-permissions.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			if(in_array('group_id',$object->keys)==false)
			{
				$continue = true;
			}

		//Do need convertion?
		if($continue==true)
		{
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = NzrConverter::exceptions($value);
					}
					$object->save($save,array('group-id'=>$save['group-id'],'object'=>$save['object']),1);
				}
			}
			$object->alter_keys(array('group-id'=>'group_id'));
			$object->set_primary_keys(array('group_id','object'));
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,"User groups permissions converted succesfully.");
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the theme files. See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','User groups permissions did not need convertion.');
		}
	}//End function
	
	/********************************************************************
				Administration Control Panel files
	********************************************************************/
	public function acpFiles()
	{
		$result['acp_nav_cat'] = $this->acpFile_nav_cat();
		$result['acp_nav'] = $this->acpFile_nav();
		return $result;
	}
	
	public function acpFile_nav_cat()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'nav-cat.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			$auto_keys = $object->return_auto_keys();
			if(empty($auto_keys))
			{
				$continue = true;
			}
			
		//Do need convertion?
		if($continue==true)
		{
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						$save[$key] = NzrConverter::exceptions($value);
					}
					$object->save($save,array('id'=>$save['id']),1);
				}
			}
			$object->set_auto_keys(array('id'));
			$object->set_primary_keys(array('nav-id'));
			$object->alter_keys(array('nav-id'=>'nav_id','first-url'=>'first_url'));
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,'Administration Control Panel file: "nav-cat.nzr" succesfully converted.');
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the Administration Control Panel file: "nav-cat.nzr". See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','Administration Control Panel file: "nav-cat.nzr" did not need convertion.');
		}
	}//End function
	
	public function acpFile_nav()
	{
		//Read existing values
		$object = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'nav.nzr','MULTIPLE_SAVES_FRIENDLY');
			$object->readfile();
			$continue = false;
			$auto_keys = $object->return_auto_keys();
			if(empty($auto_keys))
			{
				$continue = true;
			}
			
		//Do need convertion?
		if($continue==true)
		{
			$object->add_keys(array('cloak'=>''));
			if($object->amount_rows>0)
			{
				foreach($object->content as $values)
				{
					$save = array();
					foreach($values as $key=>$value)
					{
						if($key=='name')
						{
							if(substr_count($value,'|cloak|')>0)
							{
								$value = str_replace('|cloak|','',$value);
								
								switch($values['url'])
								{
								case 'editor-category':
									$cloak = 'manager-categories';
								break;  
								case 'editor-comments':
									$cloak = 'manager-comments';
								break;
								case 'manager-themes-editor':
									$cloak = 'manager-themes';
								break;
								case 'editor-uploads':
									$cloak = 'manager-uploads';
								break;
								case 'users-create-group':
									$cloak = 'users-manager-groups';
								break;
								case 'users-profile':
									$cloak = 'users-manager-users';
								break;
								case 'users-editor-groups':
									$cloak = 'users-manager-groups';
								break;
								case 'users-groups-permissions':
									$cloak = 'users-manager-groups';
								break;
								case 'manuals-read':
									$cloak = 'manuals-main';
								break;
								default:
									$cloak = '';
								}
								$save['cloak'] = $cloak;
							}
						}
						if($key!=='cloak')
						{
							$save[$key] = NzrConverter::exceptions($value);
						}
					}
					$object->save($save,array('url'=>$save['url']),1);
				}
			}
			//Fix
			$object->save(array('name'=>'Integration','url'=>'settings-integration'),array('url'=>'settings-placement'),1);
			$object->set_primary_keys(array('name','nav-cat'));
			$object->alter_keys(array('nav-cat'=>'nav_cat'));
			//Result?
			if($object->close()==true)
			{//Succes
				return array(true,'Administration Control Panel file: "nav.nzr" succesfully converted.');
			}
			else
			{//Problem
				return array(false,'An .Nzr error occured while converting the Administration Control Panel file: "nav.nzr". See the source code of this HTML webpage for the errors.');
			}
		}
		else
		{
			return array('not_needed','Administration Control Panel file: "nav.nzr" did not need convertion.');
		}
	}//End function
}//End class

/********************************************************************
				Run
********************************************************************/
/*
$nzr = new NzrConverter();
	$result['config'] = $nzr->config();
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
	$result = array_merge($result,$nzr->acpFiles());

	echo "<h1>Result</h1><ul>";
	foreach($result as $object)
	{
		echo "<li";
		if($object[0]==true && $object[0]!=='not_needed')
		{
			echo " style='color: green;'";
		}
		elseif($object[0]=='not_needed')
		{
			echo " style='color: orange;'";
		}
		elseif($object[0]==false)
		{
			echo " style='color: red;'";
		}
		echo ">".$object[1]."</li>";
	}
	echo "</ul>";
*/
?>