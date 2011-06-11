<?php
$page_content = "
<div class='block-right'>
	Actions for selected: <input type='submit' name='delete' value=' Delete '>
	<input type='hidden' name='delete-type' value='comments'>
</div>

<h1>Comments manager</h1>
In the Comments manager you can manager the comments placed placed on your news. Edit or delete them.<br>

<table>
	<tr>
		<th style='width: 20px;'>&nbsp;</th>
		<th>
			Comment
		</th>
		<th>
			News post
		</th>
		<th>
			Author
		</th>
		<th>
			Date
		</th>
	</tr>";
$comments = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr');
	$comments->readfile();
	$items['total'] = $comments->amount_rows;
	if($no_config['acp_items_page']=='all')
	{
		$show_limit = '';
	}
	else
	{
		$show_limit = (($page-1)*$no_config['acp_items_page']).','.$no_config['acp_items_page'];
	}
	$comments->sort(array('date','time'),'date_desc',$show_limit);
	$items['shown'] = $comments->amount_rows;
if($comments->amount_rows<=0)
{
	$page_content .= "
	<tr>
		<td></td>
		<td colspan='4'>
			<span class='less_important'>";
		if($page==1)
		{
			$page_content .= "No comments made yet. Comments can only be made from the news page(s).";
		}
		else
		{
			$page_content .= "No comments found, try return to the <a href='".url_build($name,$id)."'>firstpage</a>.";
		}
		$page_content .= "</span>
		</td>
	</tr>";
}
else
{
	foreach($comments->content as $info)
	{
		$openC = new newanz_nzr($no_config['dir_comments'].$info['comment_id'].'.nzr');
			$openC->readfile();
			$comment = $openC->content;
			$comment = $comment[0];
			
		$openN = new newanz_nzr($no_config['dir_news'].$info['news_id'].'.nzr');
			$openN->readfile();
			$news = $openN->content;
			$news = $news[0];
		$page_content .= "
		<tr>
			<td>
				<input type='checkbox' value='".$comment['id']."' name='select[".$comment['id']."]'>
			</td>
			<td>
				<a href='".url_build('editor-comments',$comment['id'])."'>Open</a>
			</td>
			<td>
				<a href='".url_build('editor-news',$news['id'])."'>".$news['name']."</a>
			</td>
			<td>
				<a href='".url_build('users-profile',$comment['user_id'])."' ".no_group_color($users[$comment['user_id']]['role']).">".$users[$comment['user_id']]['display-name']."</a>
			</td>
			<td>
				".no_format_date($info['date'])." at ".no_format_time($info['time'])."
			</td>
		</tr>";
		$openC->close();
		$openN->close();
	}
}
$comments->close();
$page_content .= "</table>";

$page_content .= no_show_pages($items['total']);
?>