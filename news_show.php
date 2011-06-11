<?php
error_reporting(0);
@session_start();//Manual load of session.
//Define correct directories
if(defined("newsoffice_directory")==false)
{
	define("newsoffice_directory", str_replace("news_show.php", '', __FILE__).'/');
}
if(defined("newsoffice_dir_core")==false)
{
	define("newsoffice_dir_core", 'core/');
}
//Load configuration and libaries
if(defined("newsoffice_mode")==false)
{
	define("newsoffice_mode", 'news_page');
}
include(newsoffice_directory.newsoffice_dir_core.'clean_boot.php'); //Send request for basic information without loading the requested page all over again.

//Filter theme url
$theme_selected = no_clear_url($theme_selected);

//Default theme
if(empty($theme_selected))
{
	$themes = glob(newsoffice_directory.$no_config['dir_themes'].'*.nzr');
	$theme_selected = str_replace('.nzr','',str_replace(newsoffice_directory.$no_config['dir_themes'],'',$themes[0]));
}
//Get theme information
	$openTheme = new newanz_nzr(newsoffice_directory.$no_config['dir_themes'].$theme_selected.'.nzr','READ_ONLY');
		$openTheme->readfile();
		$openTheme->rekey(array('object'));
		$no_theme = $openTheme->content;
	$openTheme->close();

if(function_exists("newsoffice_news_show_run")==false)
{
function newsoffice_news_show_run($theme_selected,$selected_category='')
{
	global $no_config;
	global $no_theme;
	global $users;
	$mode = 'normal';
	if(defined('nMode')==true)
	{
		$mode = nMode;
		if($mode=='latest_news')
		{
			$openCatLinks = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories-link.nzr');
				$openCatLinks->readfile();
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
					$removeNews = array();
					$openCatLinks->search(array('category_id'=>$category));
					foreach($openCatLinks->content as $cObject)
					{
						//Remove non-matched ID from the newsObjects Array.
						$newsObjectsCategories[$cObject['news_id']] = $newsObjects[$cObject['news_id']];
					}
					$newsObjects = $newsObjectsCategories;
				}
				$openInfo->content = $newsObjects;
				$openInfo->sort(array('date','time'),'date_desc','0,'.nHeadlineItems);

				if($openInfo->amount_rows>0)
				{
					echo "<div class='newsOfficeHeadlinesBox'>";
					foreach($openInfo->content as $object)
					{
						$openNews = new newanz_nzr(newsoffice_directory.$no_config['dir_news'].$object['news_id'].'.nzr');
							$openNews->readfile();
							$openNews->search(array('version'=>'published'),false,1);
							$news = $openNews->content[0];
							echo "<div class='newsOfficeHeadlines' id='newsOfficeHeadline".$news['id']."'><a href='".newanz_nzr::decode(nNewsPageUrl)."?n-id=".$news['id']."'>".$news['name']."</a></div>";
						$openNews->close();
					}
					echo "</div>";
				}
			$openInfo->close();
			$openCatLinks->close();
		}
	}
	if($mode=='normal')
	{	
	$page = no_clear_url($_GET['n-page']); if(empty($page) || $page=='home'){ $page = 1; } //Default value
	$id = no_clear_url($_GET['n-id']);
	$user = no_clear_url($_GET['n-user']);
	if(!empty($_GET['n-cat']))
	{
		$category = no_clear_url($_GET['n-cat']);
	}
	elseif(!empty($selected_category))
	{//Only use the "normal" selected category when there is no user override
		$category = $selected_category;
	}

	//Login
	if(array_key_exists('no_login',$_POST)==true)
	{
		$nContent = new no_convert_content('message');
			$nContent->set_theme($no_theme);
		if(noUser::login(array('username'=>$_POST['no_username'],'password'=>$_POST['no_password']))==true)
		{
			//Login succes
			$nContent->set_info($no_theme['theme_message-7']['value']);
		}
		else
		{
			//Login failed
			$nContent->set_info($no_theme['theme_message-8']['value']);
		}
		$nContent->convert();
		echo $nContent->get_content();
	}
	//Logout
	if(array_key_exists('no_logout',$_POST)==true && noUser::isLoggedIn()==true)
	{
		//Show message
		$nContent = new no_convert_content('message');
			$nContent->set_theme($no_theme);
			$nContent->set_info($no_theme['theme_message-9']['value']);
		$nContent->convert();
		echo $nContent->get_content();
		//Logout
			noUser::logout();
	}
	//Get permissions
	if(noUser::isLoggedIn()==true)
	{
		$user_permissions = no_load_permissions();
	}

	//Save comment
	if(array_key_exists('no_save_comment',$_POST)==true)
	{
		if($no_config['set_comments_limit']!=='none' && empty($_SESSION[install_id]['comments']['last']))
		{
			$_SESSION[install_id]['comments']['last'] = time()-310;
		}
		if($no_config['set_comments_limit']!=='none' && $_SESSION[install_id]['comments']['last']>=(time()-($no_config['set_comments_limit']*60)))
		{
			define('no_save_error',$no_theme['theme_error-6']['value']);
		}
		elseif(empty($_POST['no_content']))
		{
			define('no_save_error',$no_theme['theme_error-5']['value']);
		}
		else
		{
			$saveComment = new noComment();
				$saveComment->save(array(
					'news_id'=>$id,
					'user_id'=>user,
					'content'=>$_POST['no_content']
				));
				if($saveComment->result==true)
				{//Succes
					//Show message
					$nContent = new no_convert_content('message');
						$nContent->set_theme($no_theme);
						$nContent->set_info($no_theme['theme_message-6']['value']);
						$nContent->convert();
						echo $nContent->get_content();

					$_SESSION[install_id]['comments']['last'] = time();
					unset($_POST['no_content']); //This to hide it from the content box
				}
				else
				{//Failure
					define('no_save_error',$no_theme['theme_error-comment-failure']['value']);
				}
			$saveComment->close();
		}
	}
	//XHTML FIX
	if($no_config['set_html']=='xhtml'){ $html_fix = ' /'; }

	/*****************************************************************
					Actual show of content
	*****************************************************************/

	//Show author profile
	if(!empty($user))
	{
		echo "<div class='newsofficeUserPage'>";
		//Get own profile
		if(empty($user))
		{
			$user = user;
		}
		if(!empty($users[$user]['avatar']))
		{
			$users[$user]['avatar'] = "<img src='".$users[$user]['avatar']."' class='no_avatar'".$html_fix.">";
		}
		$nContent = new no_convert_content('user_page');
			$nContent->set_theme($no_theme);
			$nContent->set_info($users[$user]);
			$nContent->convert();
			echo $nContent->get_content();
		echo "</div>";
	}
	//Show only one news post
	elseif(!empty($id))
	{
		echo "<div class='newsofficeNewsPost'>";
		$openInfo =new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr');
			$openInfo->readfile();
			//Only load published news posts
			foreach($openInfo->content as $object)
			{
				if($object['news_id']==$id && ($object['published']=='yes' || ($object['published']=='time' && (($object['date']<date('Y-m-d')) || ($object['date']==date('Y-m-d') && $object['time']<date('H:i'))))))
				{
					$newsObjects[$object['news_id']] = $object;
				}
			}
			if(count($newsObjects)<=0)
			{
				$nContent = new no_convert_content('message');
				$nContent->set_theme($no_theme);
				$nContent->set_info($no_theme['theme_error-1']['value']);
				$nContent->convert();
				echo $nContent->get_content();
			}
			else
			{
				$nContent = new no_convert_content('full');
				$openNews = new newanz_nzr(newsoffice_directory.$no_config['dir_news'].$id.'.nzr');
					$openNews->readfile();
					$openNews->search(array('version'=>'published'));
					$object = $openNews->content[0];
					$info = array_merge($openNews->content[0],$newsObjects[$id]);
					$nContent->set_info($info);
				//Javascript save
					$jschange = $info['name'];

				$nContent->set_theme($no_theme);
				$openCatLinks = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories-link.nzr');
					$openCatLinks->readfile();
					$openCatLinks->search(array('news_id'=>$info['id']));
				$nContent->set_categories($_SESSION[install_id]['writer']['content']['category']);
				$nContent->convert();
				echo $nContent->find_page($nContent->get_content()); //Replace page key with new key
				$openNews->close();
		
			//Load comments
			if($no_config['set_comments_active']=='true')
			{
				$openComments = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr');
					$openComments->readfile();
					$openComments->search(array('news_id'=>$id));
				if($openComments->amount_rows<=0)
				{
					$nContent = new no_convert_content('message');
						$nContent->set_theme($no_theme);
						$nContent->set_info($no_theme['theme_error-3']['value']);
						$nContent->convert();
						echo $nContent->get_content();
				}
				else
				{
					//Order?
					if($no_config['set_comments_order']=='latest')
					{
						$show_order = 'date_desc';
					}
					else
					{
						$show_order = 'date_asc';
					}
					$openComments->sort(array('date','time'));
					foreach($openComments->content as $comment)
					{
						$openComment = new newanz_nzr(newsoffice_directory.$no_config['dir_comments'].$comment['comment_id'].'.nzr');
							$openComment->readfile();
						$nContent = new no_convert_content('comments');
							$nContent->set_theme($no_theme);
							$info = array_merge($openComment->content[0],$comment);
							$nContent->set_info($info);
							$nContent->convert();
							echo $nContent->get_content();
						$openComment->close();
					}
				}
				$openComments->close();
				
				if(noUser::isLoggedIn()==true)
				{
					if(no_is_allowed('comments')==false)
					{//Show not allowed message
						$nContent = new no_convert_content('message');
							$nContent->set_theme($no_theme);
							$nContent->set_info($no_theme['theme_error-7']['value']);
							$nContent->convert();
							echo $nContent->get_content();
					}
					else
					{//Show comment form
						$nContent = new no_convert_content('comments_form');
							$nContent->set_theme($no_theme);
							$nContent->convert();
							echo $nContent->get_content();
					}
				}
				else
				{//Show login form
					$nContent = new no_convert_content('login_form');
						$nContent->set_theme($no_theme);
						$nContent->convert();
						echo $nContent->get_content();
				}
			}
			$openInfo->close();
		}
		echo "</div>";
	}
	//Show news pages
	else
	{
		echo "<div class='newsofficeNewsPage'>";
		$openCatLinks = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories-link.nzr');
			$openCatLinks->readfile();
		$openInfo = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr');
			$openInfo->readfile();
			//Only load published news posts
			$newsObjects = array();
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
				$removeNews = array();
				$openCatLinks->search(array('category_id'=>$category));
				$newsObjectsCategories = array();
				foreach($openCatLinks->content as $cObject)
				{
					//Remove non-matched ID from the newsObjects Array.
					$newsObjectsCategories[$cObject['news_id']] = $newsObjects[$cObject['news_id']];
				}
				$newsObjects = $newsObjectsCategories;
			}
			$newsAmount = count($newsObjects);
			$items['total'] = $newsAmount;
			//Limit?
			if($no_config['set_amount_posts']=='all')
			{
				$show_limit = '';
			} 
			else
			{
				$show_limit = (($page-1)*$no_config['set_amount_posts']).','.$no_config['set_amount_posts'];
			}
			//Order?
			if($no_config['set_news_order']=='latest')
			{
				$show_order = 'date_desc';
			}
			else
			{
				$show_order = 'date_asc';
			}
			$openInfo->content = $newsObjects;
			$openInfo->sort(array('date','time'),$show_order,$show_limit);
			$items['shown'] = $openInfo->amount_rows;
			if($openInfo->amount_rows<=0)
			{
				$nContent = new no_convert_content('message');
				$nContent->set_info($no_theme['theme_error-1']['value']);
				$nContent->set_theme($no_theme);
				$nContent->convert();
				echo $nContent->get_content();
			}
			else
			{
				foreach($openInfo->content as $object)
				{
					$openNews = new newanz_nzr(newsoffice_directory.$no_config['dir_news'].$object['news_id'].'.nzr');
						$openNews->readfile();
						$openNews->search(array('version'=>'published'),false,1);
						$info = array_merge($openNews->content[0],$object);
					//Start Content displayer
					if($openInfo->amount_rows>0)
					{
						$nContent = new no_convert_content('small');
						$nContent->set_info($info);
					}
					else
					{
						$nContent = new no_convert_content('message');
						$nContent->set_info($no_theme['theme_error-1']['value']);
					}
					$nContent->set_theme($no_theme);
					
					$nContent->set_categories();
					$nContent->convert();
					echo $nContent->get_content();
					$openNews->close();
				}
			}
		$openInfo->close();
		$openCatLinks->close();
		echo "</div>";
		
		if(
			(!empty($category) && empty($selected_category))
			||
			(!empty($category) && !empty($selected_category) && $category!==$selected_category)
		)
		{
			echo "<div class='newsofficeNewsPageCategory'>";
			$nPages = new no_convert_content('message');
				$nPages->set_theme($no_theme);
				$nPages->set_info("<a href='".no_link_build('n-page','home',true)."'>".$no_theme['theme_message-1']['value']."</a>");
				$nPages->convert();
				echo $nPages->get_content();
			if($page!==1)
			{
				$jschange = $no_theme['theme_message-4']['value'].' '.$page;
			}
			echo "</div>";
		}
		if($items['total']>$no_config['set_amount_posts'])
		{
			echo "<div class='newsofficeNewsPagePagination'>";
			$nPages = new no_convert_content('message');
				$nPages->set_theme($no_theme);
				$nPages->set_info(no_show_pages($items['total'],$page));
				$nPages->convert();
				echo $nPages->get_content();
			if($page!==1)
			{
				$jschange = $no_theme['theme_message-4']['value'].' '.$page;
			}
			echo "</div>";
		}
	}

	if($no_config['set_change_title']=='true' && !empty($jschange))
	{
		echo "
		<script type='text/javascript'>
			if(document.title!=='')//Only add the seperator to the title when the title is not empty
			{
				document.title += ' | ';
			}
			document.title += '".$jschange."';
		</script>";
	}
	echo "<div class='newsofficeCopyright' style='text-align: center;'>Powered by <a href='".$no_config['acp_url']."'>NewsOffice</a>.</div>";
	}
}//End
}
newsoffice_news_show_run($theme_selected,$selected_category);
?>