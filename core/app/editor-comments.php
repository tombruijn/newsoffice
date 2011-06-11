<?php
$show_form = true;
$page_content = "<h1>Comment editor</h1>";
if(!empty($id))
{
	//Get comment information
	$openC = new newanz_nzr(newsoffice_directory.$no_config['dir_comments'].$id.'.nzr');
		$openC->readfile();
		$info = $openC->content[0];
	$openC->close();
	//Get publish information
	$openCp = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr');
		$openCp->readfile();
		$openCp->search(array('comment_id'=>$id),false,1);
		$publish = $openCp->content[0];
	$openCp->close();
	//Save comment
	if($_POST['save'])
	{
		if(empty($_POST['content']))
		{
			$error1 = 'content';
		}
		//Check for errors
		if(empty($error1))
		{
			$show_form = false;
			
			//Save comment information
			$saveC = new newanz_nzr(newsoffice_directory.$no_config['dir_comments'].$id.'.nzr');
				$saveC->readfile();
				$_POST["content"] = strtr($_POST["content"], array("\r\n"=>'',"\r"=>'',"\n"=>''));
				$saveC->save(array('content'=>$_POST['content']),array('id'=>$id),1);
			if($saveC->result==true)
			{
				$page_content .= "The comment from <span class='important'>".$users[$info['user_id']]['username']." - <span".no_group_color($users[$info['user_id']]['role']).">".$users[$info['user_id']]['display-name']."</span></span> has been succesfully saved.<br><br>";
			}
			else
			{
				$page_content .= "<div class='error'>The comment from <span class='important'>".$users[$info['user_id']]['username']." - <span".no_group_color($users[$info['user_id']]['role']).">".$users[$info['user_id']]['display-name']."</span></span> could not be saved. A .Nzr error occured.<br>
				Check the <a href='".url_build('system-status')."'>system status</a> page to see if anything is wrong.</div>";
			}
			$page_content .= "<a href='".url_build('editor-comments', $id)."'>&laquo; Go back to the comment editor</a> | <a href='".url_build('manager-comments')."'>Go to manager &raquo;</a>";
			$saveC->close();
		}
		$info['content'] = $_POST['content'];
	}

	if($show_form==true)
	{
		$page_content .= "In the comment editor you are able to edit existing comments placed on your news posts.<br>";
		$page_content .= "<br>
		<table>
			<tr>
				<td class='subject'>
					Author
				</td>
				<td>
					".$users[$info['user_id']]['username']." - <span".no_group_color($users[$info['user_id']]['role']).">".$users[$info['user_id']]['display-name']."</span>
				</td>
			</tr>
			<tr>
				<td class='subject'>
					Date and time
				</td>
				<td>
					".no_format_date($publish['date'])." at ".no_format_time($publish['time'])."
				</td>
			</tr>
			<tr>
				<td class='subject' colspan='2'>
					Content
				</td>
			</tr>
			<tr>
				<td colspan='2'>";
			if($error1=='content')
			{
				$page_content .= "<div class='error'>Content is required.</div>";
			}
		$page_content .= "
					<textarea name='content' rows='10' cols='10' class='mceEditor'>".no_convert_field($info['content'],true)."</textarea>
				</td>
			</tr>
		</table>
		<a href='".url_build('manager-comments')."'>&laquo; Go back to manager</a> | <input type='submit' name='save' value=' Save comment '>";
	}
}
?>