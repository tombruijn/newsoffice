<?php
function no_load_permissions($group='')
{
	global $no_config;
	//Plugin permissions are saved inhere as well. So no extra loading!
	$openR = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-permissions.nzr','READ_ONLY');
		$openR->readfile();
		if(!empty($group)) //Return only permissions of a specified group
		{
			$openR->search(array('group_id'=>$group));
		}
		return $openR->content;
	$openR->close();
}//End function

//Searches if a user is allowed to do such a thing!
function no_is_allowed($object,$group='')
{
	$result = true; //Default value
	global $no_config;
	global $users; //Get user information
	//Select logged in user it's group when no group is selected
		if(empty($group))
		{
			$group = $users[user]['role'];
		}
	
	if(empty($group)) //No group selected, this can't happen, return not allowed
	{
		$result = false;
	}
	else
	{
		$openR = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-permissions.nzr','READ_ONLY');
			$openR->readfile();
			$openR->search(array('group_id'=>$group,'object'=>$object));
			if($openR->amount_rows>0)
			{
				$result = false;
			}
		$openR->close();
	}
	return $result;
}//End function

function no_clear_html($string)
{
	$string = htmlentities($string, ENT_QUOTES);
	$string = strip_tags($string);
	$string = preg_replace("#&(.*?);#"," ", $string); //Strange html things fix
	return $string;
}//End function

function no_clear_url($string)
{
	$string = str_replace('/','',$string);
	$string = htmlentities($string, ENT_QUOTES);
	return $string;
}//End function

function no_clear_ins($string)
{//Remove this function
	echo 123;
	echo $string;
	return $string;
}//End function

function no_convert_field($string,$override='')
{
//	$string = html_entity_decode($string, ENT_QUOTES);
	if(!empty($override))
	{
		$string = str_replace('<br>',"\n",$string);
	}
	$string = htmlentities($string, ENT_QUOTES);
	return $string;
}

function no_link_build($url,$id,$overwrite='')
{
	global $no_config;
	$link = '?';
	if($no_config['set_phpget']=='true')
	{
		$keys = array_keys($_GET);
		$i['keys'] = 0;
		foreach($_GET as $get)
		{
			if($keys[$i['keys']]!=='n-page' && $keys[$i['keys']]!=='n-id' && $keys[$i['keys']]!=='n-user' && $keys[$i['keys']]!=='n-cat')
			{
				$link .= $keys[$i['keys']]."=".htmlentities($get,ENT_QUOTES);
			}
			if($i['keys']!==count($_GET)-1)
			{
				$link .= "&amp;";
			}
			$i['keys']++;
		}
		$link .= "&amp;";
	}
	$link .= $url.'='.$id;
	if(empty($overwrite) && $url!=='n-cat' && !empty($_GET['n-cat']) && is_numeric($_GET['n-cat']))
	{
		$link .= "&amp;n-cat=".$_GET['n-cat'];
	}
	return $link;
}//End function

function no_format_date($date)
{
	global $no_config;
	if(!empty($date))
	{
		$date = explode("-", $date);
		$result = date($no_config['format_date'], mktime(0,0,0, $date[1], $date[2], $date[0]));
	}
	return $result;
}//End function

function no_format_time($time)
{
	global $no_config;
	if(!empty($time))
	{
		$time = explode(":", $time);
		$result = date($no_config['format_time'], mktime($time[0],$time[1],0,date('m'),date('d'),date('Y')));
	}
	return $result;
}//End function

function no_group_color($group)
{
	global $user_groups;
	if(!empty($user_groups[$group]['color']))
	{
		$string = " class='important' style='color: #".$user_groups[$group]['color'].";'";
	}
	return $string;
}//End function

class no_convert_content
{
	private $preview = 'no';
	private $theme_selected;
	private $theme_info = array();
	private $info;
	private $content;
	private $mode;
	public function __construct($mode,$preview=false)
	{
		//Set theme
		$this->mode = $mode;
		$this->preview = $preview;
	}//End function
	
	public function set_theme($object)
	{
		$this->theme_info = $object;
	}//End function

	public function set_categories($info=array())
	{
		global $no_config;
		//Get category information
		$cats = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories.nzr','READ_ONLY');
			$cats->readfile();
			$cats->rekey(array('id'));

			//Get category links information
			if($this->preview==false)
			{
				$catlinks = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories-link.nzr','READ_ONLY');
					$catlinks->readfile();
					$catlinks->search(array('news_id'=>$this->info['id']));
					$categories = '';
					if($catlinks->amount_rows>0)
					{
						foreach($catlinks->content as $object)
						{
							$categories .= "<a href='".no_link_build('n-cat',$object['category_id'])."'>".$cats->content[$object['category_id']]['name']."</a>";
							if($object['category_id']!==$catlinks->content[$catlinks->amount_rows-1]['category_id'])
							{
								$categories .= ", ";
							}
						}
					}
					else
					{
						//! Replace this with a user input
						$categories = 'None';
					}
				$catlinks->close();
			}
			else
			{
				//This will be gone with the version system
				if(!empty($info))
				{
					//foreach($catlinks->content as $category)
					foreach($info as $category)
					{
						$categories .= "<a href='".no_link_build('n-cat',$category)."'>".$cats->content[$category]['name']."</a>";
						if($category!==$info[count($info)-1])
						{
							$categories .= ", ";
						}
					}
				}
			}
		$cats->close();
		$this->categories = $categories;
	}//End function	
	
	public function find_page($string)
	{
		if($no_config['set_amount_posts']=='all')
		{
			$backpage = '1';
		}
		else
		{
			global $no_config;
			global $category;
			$openInfo = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr');
				$openInfo->readfile();
				//Only load published news posts
				foreach($openInfo->content as $object)
				{
					if($object['published']=='yes' || ($object['published']=='time' && (($object['date']<date('Y-m-d')) || ($object['date']==date('Y-m-d') && $object['time']<date('H:i')))))
					{
						$newsObjects[$object['news_id']] = $object;
					}
				}
				//Filter news posts on category
				if(!empty($category))
				{
					global $openCatLinks;
					$openCatLinks->search(array('category_id'=>$category));
					foreach($openCatLinks->content as $cObject)
					{
						//Remove non-matched ID from the newsObjects Array.
						$newsObjectsCategories[$cObject['news_id']] = $newsObjects[$cObject['news_id']];
					}
					$newsObjects = $newsObjectsCategories;
				}
				$openInfo->content = $newsObjects;
				$newsAmount = count($newsObjects);
				//Order?
				if($no_config['set_news_order']=='latest')
				{
					$show_order = 'date_desc';
				}
				else
				{
					$show_order = 'date_asc';
				}
				$openInfo->sort(array('date','time'),$show_order,$show_limit);
				$i_item = 0;
				$i_page = 0;
				$page = 1;
				$dates = array_keys($openInfo->content);
				while(empty($backpage)==true && $i<2000) //Only loop while the backpage is not found and a little security feature
				{
					$object = $openInfo->content[$dates[$i_item]];
					$i_item++; $i_page++;
					if($i_page>$no_config['set_amount_posts'])
					{//Next page
						$page++;
						$i_page = 0;
					}
					if($object['news_id']==$this->info['id'])
					{//News posts found so use the current page as id
						$backpage = $page;
					}
				}
			$openInfo->close();
		}
		$string = str_replace('<!--no-$backpage-->',$backpage,$string);
		return $string;
	}//End function
	
	public function set_info($info)
	{
		if(is_array($info)==true)
		{
			$this->info = $info;
		}
		else
		{
			$this->message = $info;
		}
	}//End function
	
	public function content_convert($string)
	{
		global $no_config;
		
		//Uploads
		$string = $this->convert_uploads($string);
		
		//Fix for <br> tag
		$string = $this->br_fix($string);
		return $string;
	}//End function
	
	public function convert_tags($string)
	{
		global $no_config;
		global $users;
		//First global tags
		$string = str_replace('[emoticons]',"",$string);
			$string = str_replace('[newsoffice]',"<a href='".$no_config['acp_url']."'>NewsOffice panel</a>",$string);
			$string = str_replace('[register]',"<a href='".$no_config['acp_url']."?name=register'>Register</a>",$string);
			$string = str_replace('[forgot-password]',"<a href='".$no_config['acp_url']."?name=recovery'>Forgot password?</a>",$string);
			$string = str_replace('[current-date]',no_format_date(date('Y-m-d')),$string);
			$string = str_replace('[current-time]',no_format_date(date('H:i')),$string);
			$string = str_replace('[logout]',"<form action='' method='post'><input type='submit' name='no_logout' value='".no_clear_html($this->theme_info['theme_name-logout']['value'])."'".$this->html_fix."></form>",$string);
			//Show current user
				$string = str_replace('[user]',"<a href='".no_link_build('n-user',$users[user]['id'])."' ".no_group_color($users[user]['role']).">".$users[user]['display-name']."</a>",$string);
		
		//Second the custom tags
		//Title
			$string = str_replace('[title]',$this->info['name'],$string);
		//Date and time
			$string = str_replace('[date]',no_format_date($this->info['date']),$string);
			$string = str_replace('[time]',no_format_time($this->info['time']),$string);
		//Description = Change when mode is small and the description is not empty
			if($this->mode=='small' || $this->mode=='full')
			{
				if(substr_count($string,'[description]')>0 && $this->mode=='small')
				{
					$string = str_replace('[description]',$this->info['description'],$string);
				}
				elseif(substr_count($string,'[description+]')>0 && ($this->mode=='small' || $this->mode=='full'))
				{
					$additional = ''; if($this->mode=='full') { $additional = '<br>';	}
					$string = str_replace('[description+]',$this->info['description'].$additional,$string);
				}
			}
			if($this->mode=='small')
			{
				if(substr_count($string,'[content]')>0)
				{
					
					if(empty($this->info['description']))
					{
						$string = str_replace("[content]",$this->info['content'],$string);
					}
					else
					{
						$string = str_replace("[content]",'',$string);
					}
				}
			}
			elseif($this->mode=='full')
			{
				$string = str_replace('[content]',$this->info['content'],$string);
			}

		//Show author of comment/news post
		$user_id = $this->info['user_id'];
		if($this->mode=='user_page')
		{
			$user_id = $this->info['id'];
			if(substr_count($string,'[group]')>0)
			{
				global $user_groups;
				$string = str_replace('[group]',"<span ".no_group_color($users[$user_id]['role']).">".$user_groups[$users[$user_id]['role']]['name']."</span>",$string);
			}
			$string = str_replace('[message]',"<a href='".no_link_build('n-page','home')."'>".$this->theme_info['theme_message-1']['value']."</a>",$string);
		}
		
			$string = str_replace('[author]',"<a href='".no_link_build('n-user',$users[$user_id]['id'])."' ".no_group_color($users[$user_id]['role']).">".$users[$user_id]['display-name']."</a>",$string);
		//Categories?
			if(substr_count($string,'[categories]')>0)
			{
				$string = str_replace('[categories]',$this->categories,$string);
			}
		//Mode specific tags
			if($this->mode=='small')
			{
				$string = str_replace('[message]',"<a href='".no_link_build('n-id',$this->info['id'])."'>".$this->theme_info['theme_message-3']['value']."</a>",$string);
			}
			elseif($this->mode=='full')
			{
				$string = str_replace('[message]',"<a href='".no_link_build('n-page','<!--no-$backpage-->')."'>".$this->theme_info['theme_message-1']['value']."</a>",$string);
			}
			//Comments tag is used, display amount of comments
			if(substr_count($string,'[comments]')>0)
			{
				//Get comments information
				$comments = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr','READ_ONLY');
					$comments->readfile();
					$comments->search(array('news_id'=>$this->info['id']));
					$string = str_replace('[comments]',$comments->amount_rows,$string);
				$comments->close();
			}
			
		//Run based on key and value in array
		if(!empty($this->info) && is_array($this->info))
		{
			foreach($this->info as $key=>$value)
			{
				$string = str_replace('['.$key.']',$value,$string);
			}
		}
		return $string;
	}//End function
	
	private function br_fix($string)
	{
		$string = preg_replace("#</h(.[0-9]?)><br(.*?)>#","</h\\1>", $string); //</h#> fix for (X)HTML
		$string = preg_replace("#<hr(.*?)><br(.*?)>#","<hr\\1>", $string); //<hr> fix for (X)HTML
		return $string;
	}//End function

	public function convert($extra='')
	{
		global $no_config;
		//Default start
		$string = $this->content_convert($this->convert_tags($this->theme_info['theme_start']['value']));
		
		//XHTML fix
			if($no_config['set_html']=='xhtml')
			{
				$this->html_fix = ' /';
			}
		
		//Convert content
		//News page and full view for news posts
		if($this->mode=='small' || $this->mode=='full')
		{
			$string .= $this->content_convert($this->convert_tags($this->theme_info['theme_news']['value']));
		}
		//User page
		elseif($this->mode=='user_page')
		{
			$string .= $this->content_convert($this->convert_tags($this->theme_info['theme_author']['value']));
		}
		//Comments
		elseif($this->mode=='comments')
		{
			$string .= $this->content_convert($this->convert_tags($this->theme_info['theme_comments']['value']));
		}
		//Login form
		elseif($this->mode=='login_form')
		{
			$this->info = array(
				'username'=>"<input type='text' name='no_username' id='no_username' class='no_field' value='".no_convert_field($_POST['no_usernames'])."'".$this->html_fix.">",
				'password'=>"<input type='password' name='no_password' id='no_password' class='no_field' value=''".$this->html_fix.">",
				'submit'=>"<input type='submit' name='no_login' id='no_login' class='no_submit' value='".no_clear_html($this->theme_info['theme_name-login']['value'])."'".$this->html_fix.">"
			);
			$string .= $this->content_convert($this->convert_tags("<form method='post' name='no_login_form' id='no_login_form' action=''>".$this->theme_info['theme_login-form']['value']."</form>"));
		}
		//Place a comment form
		elseif($this->mode=='comments_form')
		{
			if(defined('no_save_error')==true)
			{
				$error = no_save_error;
			}
			$this->info = array(
				'error'=>$error,
				'submit'=>"<input type='submit' name='no_save_comment' id='no_save_comment' class='no_submit' value='".$this->theme_info['theme_message-5']['value']."'".$this->html_fix.">",
				'logout'=>"<input type='submit' name='no_logout' id='no_logout' class='no_logout' value='Logout'".$this->html_fix.">",
				'content'=>"<script type='text/javascript' src='".$no_config['acp_url'].$no_config['dir_scripts']."jquery.js'></script><script type='text/javascript' src='".$no_config['acp_url'].$no_config['dir_scripts']."tiny_mce/tiny_mce.js'></script><script type='text/javascript'>tinyMCE.init({".str_replace('<br>',"\n",$no_config['public_tinymce'])."});</script><textarea name='no_content' style='width: 100%; height: 150px;' rows='1000' cols='1000' class='mceEditor' id='no_content'>".$_POST['no_content']."</textarea>"
			);
			$string .= $this->br_fix($this->convert_tags("<form method='post' name='no_comment_form' id='no_comment_form' action='#no_comment_form'><a name='no_comment_form'></a>".$this->theme_info['theme_comments-form']['value']."</form>"));
		}
		//Custom message
		else
		{
			$string .= $this->convert_tags($this->message);
		}
		//Default end
		$string .= $this->content_convert($this->convert_tags($this->theme_info['theme_end']['value']));
		if($no_config['set_html']=='xhtml')
		{
			$string = str_replace('<br>','<br />',$string);
		}
		//Return to class
		$this->content = $string;
	}//End function
	
	public function convert_uploads($string)
	{
		global $no_config;
		//Uploads
		if(substr_count($string, '[upload/')>0)
		{
			$upload_types['images'] = array('jpg','jpeg','gif','bmp','png','dib','jpe','jfif','tiff','tif','rle','raw');
			$uploads = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'uploads.nzr');
				$uploads->readfile();
				$uploads->rekey(array('id'));
			$values = preg_split("#\[upload/#", $string, -1);

			foreach($values as $value)
			{
				//Find the ] and delete everything behind it.
				$value = explode('<br>',$value);
				$value = str_replace(strstr($value[0],']'),'',$value[0]);
				if(empty($uploads->content[$value]) || file_exists(newsoffice_directory.$no_config['dir_uploads'].$uploads->content[$value]['file'])==false)
				{
					$overwrite = $this->theme_info['theme_error-4']['value'];
				}
				else
				{
					$upload_type = explode('.',$uploads->content[$value]['file']);
					if(in_array(strtolower($upload_type[count($upload_type)-1]),$upload_types['images'])==true)
					{
						//Images
						$overwrite = "<a href='".$app_url.$no_config['dir_uploads'].$uploads->content[$value]['file']."' target='_blank'><img src='".$no_config['acp_url'].$no_config['dir_uploads'].$uploads->content[$value]['file']."' alt='Upload: ".$uploads->content[$value]['id']."'></a>";
					}
					else
					{
						//Files
						$overwrite = "<a href='".$no_config['acp_url'].$no_config['dir_uploads'].$uploads->content[$value]['file']."' target='_blank'>".$uploads->content[$value]['file']."</a>";
					}
				}
				$string = str_replace('[upload/'.$value.']',$overwrite,$string);
			}
		}
		return $string;
	}
	
	public function get_content()
	{
		//Return to user
		return $this->content;
	}//End function
}//End class

function no_show_pages($total,$page=1)
{
	global $no_config;
	global $name;
	global $id;
	global $no_theme;
	if($no_config['acp_items_page']!=='all')
	{
		if(newsoffice_mode=='acp')
		{
			$total_pages = ceil($total/$no_config['acp_items_page']);
		}
		else
		{
			$total_pages = ceil($total/$no_config['set_amount_posts']);
		}
		if($total_pages>1)
		{
			$string = "<div class='pages'>";
			if(newsoffice_mode=='acp')
			{
				$string .= "Page";
			}
			else
			{
				$string .= $no_theme['theme_message-4']['value'];
			}
			$string .= ": ";
			if($page==1)
			{}
			elseif($page!==1 && $page<=$total_pages)
			{
				$string .= "<a href='";
				if(newsoffice_mode=='acp')
				{
					$string .= url_build($name,$id,$page-1);
				}
				else
				{
					$string .= no_link_build('n-page',$page-1);
				}
				$string .= "'>";
				if(newsoffice_mode=='acp')
				{
					$string .= "&laquo; Previous";
				}
				else
				{
					$string .= $no_theme['theme_message-1']['value'];
				}
				$string .= "</a> | ";
			}
			for($i=1; $i<=$total_pages; $i++)
			{
				$string .= "<a href='";
				if(newsoffice_mode=='acp')
				{
					$string .= url_build($name,$id,$i);
				}
				else
				{
					$string .= no_link_build('n-page',$i);
				}
				$string .= "'";
				if($i==$page)
				{
					if(newsoffice_mode=='acp')
					{
						$string .= " class='important'";
					}
					else
					{
						$string .= " class='active_page_link'";
					}
				}
				$string .= ">".$i."</a>";
				if($i<$total_pages)
				{
					$string .= ", ";
				}
				elseif($total_pages>$page)
				{
					$string .= " | <a href='";
					if(newsoffice_mode=='acp')
					{
						$string .= url_build($name,$id,$page+1);
					}
					else
					{
						$string .= no_link_build('n-page',$page+1);
					}
					$string .= "'>";
					if(newsoffice_mode=='acp')
					{
						$string .= "Next &raquo;";
					}
					else
					{
						$string .= $no_theme['theme_message-2']['value'];
					}
					$string .= "</a>";
				}
			}
			return $string."</div>";
		}
	}
}//End function

class noUser
{
	function __construct()
	{
		//echo 444;
	}//End function
	
	public function isLoggedIn()
	{
		//Get global information
			global $users;
		//On default a user is not logged in
			$result = false;
		//Get information from session
			$userid = $_SESSION[install_id]['user']['id'];
			$username = $_SESSION[install_id]['user']['username'];
			$password = $_SESSION[install_id]['user']['password'];
		//Is the information in the session actually filled?
		if(!empty($userid) && !empty($username) && !empty($password))
		{
			//Does the userid exists?
			if(!empty($users[$userid]))
			{
				//Does the username and password match with that in the record?
				if($users[$userid]['username']==$username && noUser::pwEncodeSession($users[$userid]['password'])==$password)
				{
					//User is logged in
					$result = true;
				}
			}
		}
		return $result;
	}//End function
	
	public function pwEncodeRecord($string)
	{
		//How the passwords are encoded in the .nzr users file
		$string = md5(sha1($string.install_id).install_id);
		return $string;
	}//End function
	
	public function pwEncodeSession($string)
	{
		//How the passwords are encoded in the session
		$string = md5(sha1(md5(install_id.$string).install_id).install_id);
		return $string;
	}//End function
	
	public function login($info)
	{
		//Get global information
			global $cUsers;
		//On default the login fails
			$result = false;
		//Rekey to search on username (cUsers is created in the core/clean_boot.php file)
			$cUsers->rekey(array('username'));
			$users = $cUsers->content;

		//Is the information in the function actually filled?
		if(!empty($info['username']) && !empty($info['password']))
		{
			//Does the username exists?
			if(!empty($users[$info['username']]))
			{
				//Does the username and password match with that in the record?
				if($users[$info['username']]['username']==$info['username'] && $users[$info['username']]['password']==noUser::pwEncodeRecord($info['password']))
				{
					//User is logged in
					$result = true;
					$_SESSION['tmp_no']['login'] = 'new';
					$_SESSION[install_id]['user']['id'] = $users[$info['username']]['id'];
					$_SESSION[install_id]['user']['username'] = $users[$info['username']]['username'];
					$_SESSION[install_id]['user']['password'] = noUser::pwEncodeSession(noUser::pwEncodeRecord($info['password']));
					$_SESSION[install_id]['user']['role'] = $users[$info['username']]['role'];
					//Set logged in user
					if(defined('user')==false)
					{
						define('user',$users[$info['username']]['id']);
					}
				}
			}
		}
		return $result;
	}//End function
	
	public function logout()
	{
		unset($_SESSION[install_id]);
	}//End function
}//End class

class noComment
{
	private $info;
	private $comment;
	public $result;
	private $no_config;
	function __construct()
	{
		global $no_config;
		$this->no_config = $no_config;
		$this->info = new newanz_nzr(newsoffice_directory.$this->no_config['dir_info'].'comments.nzr','MULTIPLE_SAVES_FRIENDLY');
			$this->info->readfile();
		$this->result = true;
	}
	public function save($info)
	{
		//Save global information
			//On empty, use current date and time
			if(empty($info['comment_id']) && (empty($info['date']) || empty($info['time'])))
			{
				$info['date'] = date('Y-m-d');
				$info['time'] = date('H:i');
			}
			if(empty($info['comment_id']))
			{
				$this->info->save(array(
					'news_id'=>$info['news_id'],
					'date'=>$info['date'],
					'time'=>$info['time']
				),
				'new');
				$mode = 'new';
				$info['comment_id'] = $this->info->insert_id;
			}
			else
			{
				$this->info->save(array(
					'date'=>$info['date'],
					'time'=>$info['time']
				),
				array('comment_id'=>$info['comment_id']),
				1);
				$mode = 'exists';
			}
		//Save content information
		if($mode=='new')
		{
			//Create a new comments .nzr file
			$saveNew = new newanz_nzr(newsoffice_directory.$this->no_config['dir_comments'].$info['comment_id'].'.nzr','create');
				$saveNew->create_file(array('id','user_id','content'));
				if($saveNew->result==false)
				{
					$this->result = $saveNew->result;
				}
			$saveNew->close();
		}
		//Allow html and whatever language?
		if($this->no_config['set_comments_html']=='false')
		{//No, remove it
			$info['content'] = strip_tags($info['content']);
		}
		$this->comment = new newanz_nzr(newsoffice_directory.$this->no_config['dir_comments'].$info['comment_id'].'.nzr');
			$this->comment->readfile();
			$this->comment->save(array('id'=>$info['comment_id'],'user_id'=>$info['user_id'],'content'=>$info['content']),'new');
			if($this->comment->result==false)
			{
				$this->result = $this->comment->result;
			}
		$this->comment->close();
	}
	public function close()
	{
		$result = $this->info->close();
		if($result==false)
		{
			$this->result = $result;
		}
	}
}//End class
?>