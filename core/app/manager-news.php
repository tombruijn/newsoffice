<?php
$page_content = "
<div class='block-right'>
	Actions for selected: <input type='submit' name='delete' value=' Delete '>
	<input type='hidden' name='delete-type' value='news'>
</div>

<h1>News manager</h1>
In the News manager you can manager the news posts placed. Publish, Unpublish, edit or delete them.<br>

<table>
	<tr>
		<th style='width: 20px;'>&nbsp;</th>
		<th>
			Title
		</th>
		<th>
			Published
		</th>
		<th>
			Author
		</th>
		<th>
			Date
		</th>
	</tr>
	<tr class='title'>
		<td style='width: 20px;'></td>
		<td>
			<a href='".url_build('editor-news')."' class='new'>New</a>
		</td>
		<td colspan='3'>
			&nbsp;
		</td>
	</tr>";
$news = new newanz_nzr($no_config['dir_info'].'published.nzr');
	$news->readfile();
	$items['total'] = $news->amount_rows;
	if($no_config['acp_items_page']=='all')
	{
		$show_limit = '';
	}
	else
	{
		$show_limit = (($page-1)*$no_config['acp_items_page']).','.$no_config['acp_items_page'];
	}
	$news->sort(array('date','time'),'date_desc',$show_limit);
	$items['shown'] = $news->amount_rows;
if($news->amount_rows<=0)
{
	$page_content .= "<tr>
		<td></td>
		<td colspan='4'>
			<span class='less_important'>No news placed yet.</span>
		</td>
	</tr>";
}
else
{
	foreach($news->content as $info)
	{
		$openN = new newanz_nzr($no_config['dir_news'].$info['news_id'].'.nzr');
			$openN->readfile();
			$openN->search(array('version'=>'published'),false,1);
			$news_content = $openN->content[0];
		$openN->close();
		//Published?
		$publish_state = $info['published'];
		if($publish_state=='time')
		{
			$publish_state = "<span class='status_false'>On time</span>";
		}
		elseif($publish_state=='yes')
		{
			$publish_state = "<span class='status_ok'>".ucfirst($publish_state)."</span>";
		}
		else
		{
			$publish_state = "<span class='status_false'>No</span>";
		}
		
		$page_content .= "
		<tr>
			<td>
				<input type='checkbox' value='".$info['news_id']."' name='select[".$info['news_id']."]'>
			</td>
			<td>
				<a href='".url_build('editor-news',$info['news_id'])."'>".$news_content['name']."</a>
			</td>
			<td>
				".$publish_state."
			</td>
			<td>
				<a href='".url_build('users-profile',$news_content['user_id'])."' ".no_group_color($users[$news_content['user_id']]['role']).">".$users[$news_content['user_id']]['display-name']."</a>
			</td>
			<td>
				".no_format_date($info['date'])." at ".no_format_time($info['time'])."
			</td>
		</tr>";
	}
}
$news->close();
$page_content .= "</table>";

$page_content .= no_show_pages($items['total']);
?>