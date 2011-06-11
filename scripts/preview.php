<?php
include('script_redirector.php');
$pId = no_clear_url($_GET['id']);
$pVersion = no_clear_url($_GET['version']);
$pTheme = no_clear_url($_GET['theme']);
$pTheme = $_SESSION[install_id]['writer']['content']['theme'];
$pView = no_clear_url($_GET['view']);

if($no_config['set_html']=='html')
{
	$html_fix = ' /';
	echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>";
}
else
{
	echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'>";
}
echo "
<html>
	<head>
		<title>NewsOffice preview</title>";
if(!empty($no_config['set_external_css']))
{
	echo "\n\t\t<link rel='stylesheet' href='".$no_config['set_external_css']."' type='text/css'".$html_fix.">";
}

echo "
		<meta http-equiv='content-type' content='text/html; charset=iso-8859-1'".$html_fix.">
	</head>
<body>
";
/*if(empty($pId) || empty($pVersion) || empty($pTheme))
{
	echo "<h1>No preview</h1>Could not show preview as one or more values are missing.<br>(id, version and theme)";
	exit();
}
else*/
{
	/*
	//Get publish information
	$publish = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr','READ_ONLY');
		$publish->readfile();
		$publish->search(array('news_id'=>$pId));
		$pInfo = $publish->content[0];
		if($publish->amount_rows<=0 || empty($pInfo))
		{
			echo "<h1>Not found</h1>No information was found to preview this news post.";
			exit();
		}
	$publish->close();
	//Get news information
	$news = new newanz_nzr(newsoffice_directory.$no_config['dir_news'].$pId.'.nzr','READ_ONLY');
		$news->readfile();
		$news->search(array('updated'=>$pVersion));
		$info = $news->content[0];
		if($news->amount_rows<=0 || empty($info))
		{
			echo "<h1>Not found</h1>No information was found to preview this news post.";
			exit();
		}
		$info['news_id'] = $pInfo['news_id'];
		$info['date'] = $pInfo['date'];
		$info['time'] = $pInfo['time'];
	$news->close();
	*/
	
	//Options: small, full
	$allowed_views = array('small','full');
	if(in_array($pView,$allowed_views)==false)
	{
		$pView = 'full';
	}
	/*
	$nContent = new no_convert_content(array('theme'=>$pTheme),$pView,true);
		//$nContent->set_info(array($info));
		$nContent->set_info($_SESSION[install_id]['writer']['content']);
		//$nContent->set_categories($categories);
		$nContent->set_categories($_SESSION[install_id]['writer']['content']['category']);
		$nContent->convert();
	echo $nContent->get_content();
	*/
	
//Get theme information
	$openTheme = new newanz_nzr(newsoffice_directory.$no_config['dir_themes'].$pTheme.'.nzr','READ_ONLY');
		$openTheme->readfile();
		$openTheme->rekey(array('object'));
		$no_theme = $openTheme->content;
	$openTheme->close();
	
	foreach($_SESSION[install_id]['writer']['content'] as $key=>$value)
	{
		$new_ses[$key] = str_replace("\n",'<br'.$html_fix.'>',$value);
	}
	$_SESSION[install_id]['writer']['content'] = $new_ses;
	
	$nContent = new no_convert_content($pView);
		$nContent->set_theme($no_theme);
		$nContent->set_info($_SESSION[install_id]['writer']['content']);
		$nContent->set_categories($_SESSION[install_id]['writer']['content']['category']);
		$nContent->convert();
		echo $nContent->get_content();
}
echo "
<div>
	<div>Switch to: <a href='?id=".$pId."&amp;theme=".$pTheme."&amp;version=".$pVersion."&amp;view=small'";
if($pView=='small')
{
	echo " style='font-weight: bold;'";
}
echo ">News page view</a> - <a href='?id=".$pId."&amp;theme=".$pTheme."&amp;version=".$pVersion."&amp;view=full'";
if($pView=='full')
{
	echo " style='font-weight: bold;'";
}
echo ">Full view</a></div>
	<div style='font-size: 80%;'>The comment amount is not the actual number</div>
</div>
";
?>

</body>
</html>