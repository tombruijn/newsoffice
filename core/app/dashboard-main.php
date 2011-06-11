<?php
$page_content = "<h1>Dashboard</h1>
Welcome <span ".no_group_color($users[user]['role']).">".$users[user]['display-name']."</span> to this NewsOffice installation.";
if(no_is_allowed('editor-news')==true)
{
	$messages[] = "Do you want to <a href='".url_build('editor-news')."'>write some news</a>?";
	$messages[] = "Try out NewsOffice's new WYSIWYG editor by <a href='".url_build('editor-news')."'>writing some news</a>.";
	$messages[] = "In the mood for some <a href='".url_build('editor-news')."'>writing</a>?";
	$messages[] = "Your news page is craving for news. <a href='".url_build('editor-news')."'>Feed it</a>!";
	$messages[] = "It's never too early or late too <a href='".url_build('editor-news')."'>write some news</a>.";
	$messages[] = "An <a href='".url_build('editor-news')."'>active news page</a> will keep your visitors coming back for more updates.";
	$messages[] = "This just in: <a href='".url_build('editor-news')."'>Write some news</a>!";
	$messages[] = "Pretty please? Your news page wants some <a href='".url_build('editor-news')."'>more news</a> to show.";
	$messages[] = "Ah you're finally here. We have been waiting for you. <a href='".url_build('editor-news')."'>Your editor</a> is all set.";
	$messages[] = "Did you know NewsOffice now features a full blooded <a href='".url_build('editor-news')."'>What You See Is What You Get Editor</a>?";
	$messages[] = "You never guess what I just <a href='".url_build('editor-news')."'>found</a>!";
	$messages[] = "I heard a rumor your news posts are feeling lonely. How about you give them <a href='".url_build('editor-news')."'>a new friend</a>?";
	$messages[] = "If you ever run into trouble with NewsOffice go to the <a href='".url_build('support-main')."'>Support page</a> for some much needed assistance.";
	$messages[] = "Using Internet Explorer? NewsOffice works best with the <a href='http://www.firefox.com' target='_blank'>Firefox</a> and <a href='http://www.opera.com' target='_blank'>Opera</a> browsers.";
	$messages[] = "Using Internet Explorer? NewsOffice works best with the <a href='http://www.firefox.com' target='_blank'>Firefox</a> and <a href='http://www.opera.com' target='_blank'>Opera</a> browsers.";
	$messages[] = "Using Internet Explorer? NewsOffice works best with the <a href='http://www.firefox.com' target='_blank'>Firefox</a> and <a href='http://www.opera.com' target='_blank'>Opera</a> browsers.";
	$messages[] = "Using Internet Explorer? NewsOffice works best with the <a href='http://www.firefox.com' target='_blank'>Firefox</a> and <a href='http://www.opera.com' target='_blank'>Opera</a> browsers.";
	$messages[] = "Using Internet Explorer? NewsOffice works best with the <a href='http://www.firefox.com' target='_blank'>Firefox</a> and <a href='http://www.opera.com' target='_blank'>Opera</a> browsers.";
	$messages[] = "Using Internet Explorer? NewsOffice works best with the <a href='http://www.firefox.com' target='_blank'>Firefox</a> and <a href='http://www.opera.com' target='_blank'>Opera</a> browsers.";
	$page_content .= " <br><br>".$messages[rand(0,count($messages)-1)]."<br>";
}

//Installer file still exists
$install_file = newsoffice_directory.$no_config['dir_core'].'install.php';
if(file_exists($install_file)==true && user==1)
{
	//Delete installer file
	if($_POST['delete-installer'])
	{
		$page_content .= "<br>";
		if(@unlink($install_file)==true)
		{
			$page_content .= "<div class='status_ok'><h2>Deletion succesfull</h2>The installer file was succesfully deleted.</div>";
		}
		else
		{
			$page_content .= "<div class='error'><h2>Deletion unsuccesfull</h2>The installer file could not be deleted. Login, through FTP, to your webhosting and delete the file manually.</div>";
		}
	}
	//Show message?
	$box_id = 'installer-exists';
	if(no_check_box('messages',$box_id)==true)
	{
		$page_content .= "
		<div class='error' id='".$box_id."'>
			<div class='important_message_closer' title='Hide this important message.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide this important message.' onclick='message_hider(\"".$box_id."\");'></div>
			<h2>Warning</h2>
			The installation file <span class='important'>".$install_file."</span> does still exists.<br>
			This might compromise your installation to outsiders, we advise you to delete the installation file.<br>
			<div class='less_important'>The installation file can be downloaded from the <a href='".$_SESSION[install_id]['updater']['info_current']['app-link-download']."'>Newanz website</a> at any time.</div>
			<br>
			You could attempt to delete the installer file using the button below. You will get a message if it went succesfull or not.<br>
			<br>
			<input type='submit' name='delete-installer' value='Try automatic deletion'>
		</div>";
	}
}

//Show latest news message?
$box_id = 'latest-news';
if(no_check_box('messages',$box_id)==true)
{
	$nzr = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr');
	$nzr->readfile();
	
	$page_content .= "
		<div class='block' id='".$box_id."'>
			<div class='important_message_closer' title='Hide this important message.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide this important message.' onclick='message_hider(\"".$box_id."\");'></div>
			<h2>Latest news</h2><ol>";
	if($nzr->amount_rows<=0)
	{
		$page_content .= "<li>No news posts placed yet. <a href='".url_build('editor-news')."'>Add one?</a></li>";
	}
	else
	{
		$nzr->sort(array('date','time'),'date_desc','0,5');
		foreach($nzr->content as $object)
		{
			//Get news information
			$news_object = new newanz_nzr(newsoffice_directory.$no_config['dir_news'].$object['news_id'].'.nzr');
			$news_object->readfile();
			if($news_object->result==true)
			{
				$news_object->search(array('version'=>'published'),false,1);
				$news = $news_object->content[0];
				$page_content .= "<li><div><a href='".url_build('editor-news',$news['id'])."'>".$news['name']."</a> by <span".no_group_color($users[$news['user_id']]['role']).">".$users[$news['user_id']]['display-name']."</span></div><div class='less_important'>On ".no_format_date($object['date'])." at ".no_format_time($object['time'])."</div></li>";
			}
			$news_object->close();
		}
	}
	$page_content .= "</ol></div>";
	$nzr->close();
}

//Show latest comments message?
$box_id = 'latest-comments';
if(no_check_box('messages',$box_id)==true)
{
	$nzr = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr');
	$nzr->readfile();
	
	$page_content .= "
	<div class='block' id='".$box_id."'>
		<div class='important_message_closer' title='Hide this important message.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide this important message.' onclick='message_hider(\"".$box_id."\");'></div>
		<h2>Latest comments</h2><ol>";
	
	if($nzr->amount_rows<=0)
	{
		$page_content .= "<li>No comments placed yet.</li>";
	}
	else
	{
		$nzr->sort(array('date','time'),'date_desc','0,5');
		foreach($nzr->content as $object)
		{
			//Get comment information
			$comment_object = new newanz_nzr(newsoffice_directory.$no_config['dir_comments'].$object['comment_id'].'.nzr');
			$comment_object->readfile();
			if($comment_object->result==true)
			{
				//Parent news item
					$news_object = new newanz_nzr(newsoffice_directory.$no_config['dir_news'].$object['news_id'].'.nzr');
					$news_object->readfile();
					$news_object->search(array('version'=>'published'),false,1);
					$news = $news_object->content[0];
					$news_object->close();
				$comment = $comment_object->content;
				$comment = $comment[0];
				$page_content .= "<li><div>By <a href='".url_build('editor-comments',$comment['id'])."'".no_group_color($users[$comment['user_id']]['role']).">".$users[$comment['user_id']]['display-name']."</a> on <a href='".url_build('editor-news',$news['id'])."'>".$news['name']."</a></div><div class='less_important'>On ".no_format_date($object['date'])." at ".no_format_time($object['time'])."</div></li>";
			}
			$comment_object->close();
		}
	}
	$page_content .= "</ol></div>";
	$nzr->close();
}

//Show latest Newanz news
$box_id = 'latest-news-newanz';
if(no_check_box('messages',$box_id)==true)
{	//Get news
	$newanz_news = new newanz_nzr('http://newsoffice.newanz.com/news.php?newsoffice_version='.$no_config['acp_version_id'],'LOOSE');
		$object = $newanz_news->original;
		if(substr_count($object,'<html')<=0 && !empty($object))
		{
			$page_content .= "
				<div class='block' id='".$box_id."'>
					<div class='important_message_closer' title='Hide this important message.'><img src='".$no_config['acp_selected_theme_dir_images']."box_close.gif' alt='Hide this important message.' onclick='message_hider(\"".$box_id."\");'></div>".$object."</div>";
		}
	$newanz_news->close();
}
?>