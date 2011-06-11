<?php
$page_content = "<h1>Deleter</h1>
Are you sure you want to delete the following? If deleted, they are deleted permanently.<br>
<br>";

$del_ids = $_POST['select'];
$del_type = $_POST['delete-type'];

//Remember values
$page_content .= "<input type='hidden' name='delete-type' value='".$del_type."'>";
if(!empty($del_ids))
{
	foreach($del_ids as $del_id)
	{
		$page_content .= "<input type='hidden' name='select[".$del_id."]' value='".$del_id."'>";
	}
}
switch($del_type)
{
//Done
case 'themes':
	if(!empty($del_ids))
	{
		$page_content .= "<h2>Themes</h2>News pages that use this theme will automaticly switch to the first, alfabeticly seen, theme.";
		$question = "Are you sure you want to delete the following themes?";
		foreach($del_ids as $del_id)
		{
			$file_used = newsoffice_directory.$no_config['dir_themes'].$del_id.'.nzr';
			if(array_key_exists('delete-yes',$_POST)==true)
			{
				if(file_exists($file_used)==true)
				{
					if(@unlink($file_used)==true)
					{
						$results[] = "Succesfully deleted the <span class='important'>".$del_id."</span> theme.";
					}
					else
					{
						$error[] = "Could not delete the <span class='important'>".$del_id."</span> theme.";
					}
				}
			}
			else
			{
				$info = new newanz_nzr($file_used);
					$info->readfile();
					if($info->result==false)
					{
						$error[] = "Could not find the <span class='important'>".$del_id."</span> theme.";
					}
					else
					{
						$info->rekey(array('object'));
						$questions[] = $info->content['name']['value'].", <span class='less_important'>".$info->content['description']['value']."</span>";
					}
				$info->close();
			}
		}
	}
break;
/*****************************************************************************/
//Done
case 'uploads':
	if(!empty($del_ids))
	{
		$page_content .= "<h2>Uploads</h2>";
		$question = "Are you sure you want to delete the following uploads?";
		$info = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'uploads.nzr');
			$info->readfile();
			$nzr_keep = $info->set_store();
			$delete = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'uploads.nzr','MULTIPLE_SAVES_FRIENDLY');
			$delete->set_import($nzr_keep);
			$info->rekey(array('id'));
			
		foreach($del_ids as $del_id)
		{
			$file_name = '';
			$file_vars = explode('_',$info->content[$del_id]['file']);
			foreach($file_vars as $file_var)
			{//Remove some NewsOffice prefixes
				if($file_var!==$file_vars[0])
				{
					$file_name .= $file_var;
				}
			}
			if(empty($file_name))
			{//Alright, use total name
				$file_name = $info->content[$del_id]['file'];
			}
			$file_used = $no_config['dir_uploads'].$info->content[$del_id]['file'];
			if(array_key_exists('delete-yes',$_POST)==true)
			{
				$action = true;
				if(file_exists($file_used)==true)
				{
					$action = @unlink(newsoffice_directory.$file_used);
				}
				if($action==true)
				{
					$delete->delete(array('id'=>$del_id),1);
					$results[] = "Succesfully deleted the <span class='important'>".$file_name."</span> upload.";
				}
				else
				{
					$error[] = "Could not delete the <span class='important'>".$file_name."</span> upload.";
				}
			}
			else
			{
				$questions[] = "<a href='".$no_config['acp_url'].$file_used."'>".$file_name."</a>";
			}
		}
		$delete->close();
		$info->close();
	}
break;
/*****************************************************************************/
//Done
case 'categories':
	if(!empty($del_ids))
	{
		$page_content .= "<h2>Categories</h2>";
		$question = "Are you sure you want to delete the following categories?";
		$info_main = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories.nzr');
			$info_main->readfile();
			$nzr_keep_main = $info_main->set_store();
			$info_main->rekey(array('id'));
			
		$delete_link = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories-link.nzr','MULTIPLE_SAVES_FRIENDLY');
			$delete_link->readfile();
			
		$delete_main = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories.nzr','MULTIPLE_SAVES_FRIENDLY');
			$delete_main->set_import($nzr_keep_main);
			
		foreach($del_ids as $del_id)
		{
			if(array_key_exists('delete-yes',$_POST)==true)
			{
				//Delete category
				$delete_main->delete(array('id'=>$del_id),1);
				//Delete links
				$delete_link->delete(array('category_id'=>$del_id));
				
				$results[] = "Succesfully deleted the <span class='important'>".$info_main->content[$del_id]['name']."</span> category.";
			}
			else
			{
				$questions[] = $info_main->content[$del_id]['name'];
			}
		}
		$info_main->close();
		$delete_main->close();
		$delete_link->close();
	}
break;
/*****************************************************************************/
//Done
case 'comments':
	if(!empty($del_ids))
	{
		$page_content .= "<h2>Comment(s)</h2>";
		$question = "Are you sure you want to delete the following comment(s)?";
		$info_main = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr');
			$info_main->readfile();
			$nzr_keep_main = $info_main->set_store();
			$info_main->rekey(array('comment_id'));
			
		$delete_link = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr','MULTIPLE_SAVES_FRIENDLY');
			$delete_link->readfile();
			
		foreach($del_ids as $del_id)
		{
			$file_used = newsoffice_directory.$no_config['dir_comments'].$del_id.'.nzr';
			$info_more = new newanz_nzr($file_used);
				$info_more->readfile();
			if(array_key_exists('delete-yes',$_POST)==true)
			{
				//Delete comment
				$action = true;
				if(file_exists($file_used)==true)
				{
					$action = @unlink($file_used);
				}
				if($action==true)
				{
					//Delete links
					$delete_link->delete(array('comment_id'=>$del_id));
					//Show message
					$results[] = "Succesfully deleted the comment made by <span".no_group_color($users[$info_more->content[0]['user_id']]['role']).">".$users[$info_more->content[0]['user_id']]['display-name']."</span> on ".no_format_date($info_main->content[$del_id]['date'])." at ".$info_main->content[$del_id]['time'].".";
				}
				else
				{
					$error[] = "Could not delete the comment made by <span".no_group_color($users[$info_more->content[0]['user_id']]['role']).">".$users[$info_more->content[0]['user_id']]['display-name']."</span> on ".no_format_date($info_main->content[$del_id]['date'])." at ".$info_main->content[$del_id]['time'].".";
				}
			}
			else
			{
				$questions[] = "Made by <span".no_group_color($users[$info_more->content[0]['user_id']]['role']).">".$users[$info_more->content[0]['user_id']]['display-name']."</span> on ".no_format_date($info_main->content[$del_id]['date'])." at ".$info_main->content[$del_id]['time'].".";
			}
			$info_more->close();
		}
		$info_main->close();
		$delete_link->close();
	}
break;
/*****************************************************************************/
//Done
case 'news':
	if(!empty($del_ids))
	{
		$page_content .= "<h2>News post(s)</h2>";
		$question = "Are you sure you want to delete the following news post(s)? All comments made on these news posts will also be deleted.";
		$info_main = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr');
			$info_main->readfile();
			$nzr_keep_main = $info_main->set_store();
			$info_main->rekey(array('news_id'));
		
		if(array_key_exists('delete-yes',$_POST)==true)
		{
			//Delete primary information
			$delete_link = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr','MULTIPLE_SAVES_FRIENDLY');
				$delete_link->readfile();
			//Delete comments
			$delete_comments = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr','MULTIPLE_SAVES_FRIENDLY');
				$delete_comments->readfile();
		}
			
		foreach($del_ids as $del_id)
		{
			$file_used = newsoffice_directory.$no_config['dir_news'].$del_id.'.nzr';
			$info_more = new newanz_nzr($file_used);
				$info_more->readfile();
				$info_more->search(array('version'=>'published'));
			if(array_key_exists('delete-yes',$_POST)==true)
			{
				//Delete comment
				$action = true;
				if(file_exists($file_used)==true)
				{
					$action = @unlink($file_used);
				}
				if($action==true)
				{
					//Delete links
					$delete_link->delete(array('news_id'=>$del_id));
					$delete_comments->delete(array('news_id'=>$del_id));
					//Show message
					$results[] = "Succesfully deleted the news post ".$info_more->content[0]['name'].".";
				}
				else
				{
					$error[] = "Could not delete the news post ".$info_more->content[0]['name'].".";
				}
			}
			else
			{
				$questions[] = $info_more->content[0]['name'];
			}
			$info_more->close();
		}
		$info_main->close();
		if(array_key_exists('delete-yes',$_POST)==true)
		{
			$delete_link->close();
			$delete_comments->close();
			//Delete comment files from non-existing comments
				$comments_files = glob(newsoffice_directory.$no_config['dir_comments'].'*.nzr');
				if(!empty($comments_files))
				{
					$comments_fix = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'comments.nzr');
						$comments_fix->readfile();
						$comments_fix->rekey(array('comment_id'));
						$comments_exists = array_keys($comments_fix->content);
						$comments_exists = array_flip($comments_exists);
						foreach($comments_files as $comment_file)
						{
							$comment_filename = str_replace(newsoffice_directory.$no_config['dir_comments'],'',$comment_file);
							$comment_filename = str_replace('.nzr','',$comment_filename);
							if(array_key_exists($comment_filename,$comments_exists)==false)
							{
								//Delete non-existing comment
								@unlink($comment_file);
							}
						}
					$comments_fix->close();
				}
		}
	}
break;
/*****************************************************************************/
//Done
case 'users':
	if(!empty($del_ids))
	{
		$page_content .= "<h2>User account(s)</h2>";
		$question = "Are you sure you want to delete the following user account(s)?";
		$info_main = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr');
			$info_main->readfile();
			$nzr_keep_main = $info_main->set_store();
			$info_main->rekey(array('id'));
		
		$delete_main = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr','MULTIPLE_SAVES_FRIENDLY');
			$delete_main->set_import($nzr_keep_main);
		if(array_key_exists('delete-yes',$_POST)==true)
		{
			//Delete user settings
			$delete_links = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-settings.nzr','MULTIPLE_SAVES_FRIENDLY');
				$delete_links->readfile();
		}
			
		foreach($del_ids as $del_id)
		{
			if($del_id=='1' || $del_id==1)
			{
				if(array_key_exists('delete-yes',$_POST)==true)
				{
					$results[] = "You can not delete the Root account: <span".no_group_color($info_main->content[$del_id]['role']).">".$info_main->content[$del_id]['username']."</span>.";
				}
				else
				{
					$questions[] = "You can not delete the Root account: <span".no_group_color($info_main->content[$del_id]['role']).">".$info_main->content[$del_id]['username']."</span>.<div class='less_important'>If you go through with the deletion this account will be skipped and remain in this installation.</div>";
				}
			}
			else
			{
				if(array_key_exists('delete-yes',$_POST)==true)
				{
					//Delete account
					$delete_main->delete(array('id'=>$del_id),1);
					//Delete settings
					$delete_links->delete(array('user_id'=>$del_id));
					
					$results[] = "Succesfully deleted the <span".no_group_color($info_main->content[$del_id]['role']).">".$info_main->content[$del_id]['username']."</span> user account.";
				}
				else
				{
					$questions[] = "<span".no_group_color($info_main->content[$del_id]['role']).">".$info_main->content[$del_id]['username']."</span>";
				}
			}
		}
		$info_main->close();
		$delete_main->close();
		if(array_key_exists('delete-yes',$_POST)==true)
		{
			$delete_links->close();
		}
	}
break;
/*****************************************************************************/
//Done
case 'user-groups':
	if(!empty($del_ids))
	{
		$page_content .= "<h2>User group(s)</h2>";
		$question = "Are you sure you want to delete the following user group(s)?";
		$info_main = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-groups.nzr');
			$info_main->readfile();
			$nzr_keep_main = $info_main->set_store();
			$info_main->rekey(array('id'));
		
		$delete_main = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-groups.nzr','MULTIPLE_SAVES_FRIENDLY');
			$delete_main->set_import($nzr_keep_main);
			
		//Delete permission
		$delete_links = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-permissions.nzr','MULTIPLE_SAVES_FRIENDLY');
			$delete_links->readfile();
		//Set users to default group
		$set_users = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users.nzr','MULTIPLE_SAVES_FRIENDLY');
			$set_users->readfile();
			
		foreach($del_ids as $del_id)
		{
			if($del_id==$no_config['set_default_group'])
			{
				if(array_key_exists('delete-yes',$_POST)==true)
				{
					$results[] = "You can not delete the default user group: <span".no_group_color($del_id).">".$info_main->content[$del_id]['name']."</span>.";
				}
				else
				{
					$questions[] = "You can not delete the default user group: <span".no_group_color($del_id).">".$info_main->content[$del_id]['name']."</span>.<div class='less_important'>If you go through with the deletion this user group will be skipped and remain in this installation.</div>";
				}
			}
			else
			{
				if(array_key_exists('delete-yes',$_POST)==true)
				{
					$delete_main->delete(array('id'=>$del_id),1);
					$delete_links->delete(array('group_id'=>$del_id)); //Delete permissions
					$set_users->save(array('role'=>$no_config['set_default_group']),array('role'=>$del_id)); //Set users to default group
					
					$results[] = "Succesfully deleted the <span".no_group_color($del_id).">".$info_main->content[$del_id]['name']."</span> user group.";
				}
				else
				{
					$questions[] = "<span".no_group_color($del_id).">".$info_main->content[$del_id]['name']."</span>";
				}
			}
		}
		$info_main->close();
		$delete_main->close();
		$delete_links->close();
		$set_users->close();
	}
break;

/*****************************************************************************/
default:
	echo "No delete type found.";
/*****************************************************************************/
}

if(!empty($questions) || !empty($results))
{
	$array = $results;
	if(!empty($questions))
	{
		$array = $questions;
		$page_content .= $question;
	}
	$page_content .= "<ul>";
	foreach($array as $object)
	{
		$page_content .= "<li>".$object."</li>";
	}
	$page_content .= "</ul>";
	if(!empty($results))
	{
		$page_content .= "<br><input type='submit' name='delete-no' value=' &laquo; Go back '>";
	}
}

if(empty($error))
{
	$page_content .= "<br>";
	if(!empty($questions))
	{
		$page_content .= "<input type='submit' name='delete-yes' value=' Yes, I want to delete this '> | <input type='submit' name='delete-no' value=' No, I do not want to delete this '>";
	}
	elseif(empty($results))
	{
		$page_content .= "<div class='error'>No items where selected. There is nothing selected to delete.</div><hr><input type='submit' name='delete-no' value=' &laquo; Cancel and go back '>";
	}
}
else
{
	$page_content .= "<div class='error'><h2>Errors</h2>One or more errors have occured.<ul>";
	foreach($error as $object)
	{
		$page_content .= "<li>".$object."</li>";
	}
	$page_content .= "</ul><hr><input type='submit' name='delete-no' value=' &laquo; Do not delete and go back '></div>";
}
?>