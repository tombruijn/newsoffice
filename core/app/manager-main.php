<?php
$page_content = "<h1>Manager</h1>
In the News manager you can manage the news posts placed, comments, categories and uploads. Stated below here are some small details about those subjects.<br>
Only the manager(s) which you have permission to are displayed below.<br>";

if(no_is_allowed('manager-news')==true)
{
	$info = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr');
		$info->readfile();
			$count_news = $info->amount_rows;
		$total = $info->set_store(); //Save information
			//Get all the published news posts
			$info->search(array('published'=>'yes'));
			$count_publish = $info->amount_rows;
		$info->set_import($total); //Return information
			//Get all the unpublished news posts
			$info->search(array('published'=>'no'));
			$count_unpublish = $info->amount_rows;
		$info->set_import($total); //Return information
			//Get all the on time published news posts
			$info->search(array('published'=>'time'));
			$count_publish_time = $info->amount_rows;
	$info->close();
	$page_content .= "
	<div class='block'>
		<h2>News posts - <a href='".url_build('manager-news')."'>Open &raquo;</a></h2>
		<table>
			<tr>
				<td class='subject'>
					Total posts
				</td>
				<td>
					".$count_news."
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Published posts
				</td>
				<td>
					".$count_publish."
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Published time posts
				</td>
				<td>
					".$count_publish_time."
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Unpublished posts
				</td>
				<td>
					".$count_unpublish."
				</td>
			</tr>
		</table>
	</div>
	";
}

if(no_is_allowed('manager-categories')==true)
{
	$info = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories.nzr');
		$info->readfile();
		$count_categories = $info->amount_rows;
	$info->close();
	$page_content .= "
	<div class='block'>
		<h2>Categories - <a href='".url_build('manager-categories')."'>Open &raquo;</a></h2>
		You can add news posts to categories so you can manage them better.<br>
		<table>
			<tr>
				<td class='subject'>
					Total categories
				</td>
				<td>
					".$count_categories."
				</td>
			</tr>
		</table>
	</div>
	";
}

if(no_is_allowed('manager-comments')==true)
{
	$info = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr');
		$info->readfile();
		$count_comments = $info->amount_rows;
	$info->close();
	$page_content .= "
	<div class='block'>
		<h2>Comments - <a href='".url_build('manager-comments')."'>Open &raquo;</a></h2>
		Comments are messages placed on your news post(s) by registered users.<br>
		<table>
			<tr>
				<td class='subject'>
					Total comments
				</td>
				<td>
					".$count_comments."
				</td>
			</tr>
		</table>
	</div>
	";
}

if(no_is_allowed('manager-uploads')==true)
{
	$info = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'uploads.nzr');
		$info->readfile();
		$count_uploads = $info->amount_rows;
	$info->close();
	$page_content .= "
	<div class='block'>
		<h2>Uploads - <a href='".url_build('manager-uploads')."'>Open &raquo;</a></h2>
		Uploads are files that have been added to NewsOffice. You can display these files in your news posts.<br>
		<table>
			<tr>
				<td class='subject'>
					Total uploads
				</td>
				<td>
					".$count_uploads."
				</td>
			</tr>
		</table>
	</div>
	";
}

if(no_is_allowed('manager-themes')==true)
{
	$count_themes = count(glob(newsoffice_directory.$no_config['dir_themes'].'*.nzr'));
	$page_content .= "
	<div class='block'>
		<h2>Themes - <a href='".url_build('manager-themes')."'>Open &raquo;</a></h2>
		Themes are used to display your news on your news page(s). Edit them to fit into your website.<br>
		<table>
			<tr>
				<td class='subject'>
					Total themes
				</td>
				<td>
					".$count_themes."
				</td>
			</tr>
		</table>
	</div>
	";
}
?>