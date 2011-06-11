<?php
$show_form = true;
$page_content = "<h1>Uploads editor - ";
if($hack_uploader==true) //Loading this editor from the news editor/writer
{
	unset($id);
	$back_link = 'editor-news';
	$back_id = no_clear_url($_GET['id']);
}
else
{
	$back_link = 'manager-uploads';	
}
if(!empty($id))
{
	$openUp = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'uploads.nzr');
		$openUp->readfile();
		$openUp->search(array('id'=>$id),1);
		$info = $openUp->content[0];
	$openUp->close();
	$page_content .= "Edit upload";
}
else
{
	$page_content .= "New upload";
}
$page_content .= "</h1>";

if($_POST['save-upload'] || $_POST['uploads-new-send'])
{
	//Error?
	if(empty($_FILES))
	{
		//Too big
		$error1 = "<div class='error'>No file selected or something went wrong sending the file to NewsOffice. Please select a file and try again.</div>";
	}
	elseif($_FILES['upload']['error']!==0)
	{
		//Yes -_-... show error messages
		if($_FILES['upload']['error']==1 || $_FILES['upload']['error']==2)
		{
			//Too big
			$error1 = "<div class='error'>This file is too big to be uploaded. The maximum file size upload size is: <span class='important'>".ini_get('upload_max_filesize')."</span>
			<div class='less_important'>G = GigaByte. M = MegaByte. K = KiloByte.<br>
			This limit is set on your server and not in NewsOffice itself.</div>
			</div>";
		}
		elseif($_FILES['upload']['error']==3)
		{
			//Partially uploaded
			$error1 = "<div class='error'>Your file was only partially uploaded. Please try again.</div>";
		}
		elseif($_FILES['upload']['error']==4)
		{
			//Not found
			$error1 = "<div class='error'>No file was uploaded/selected. Please select your upload and try again.</div>";
		}
		elseif($_FILES['upload']['error']>5)
		{
			//Not found
			$error1 = "<div class='error'>No temporary directory was found on your server to store the upload<br>
			Or<br>
			PHP has no rights to write to the disk.<br>
			Or<br>
			The uploaded was stopped by extension. This extension might not be allowed on your server.<br>
			<br>
			Please contact your webhost or server owner.</div>";
		}
		else
		{
			$error1 = "<div class='error'>This file can not be uploaded. NewsOffice didn't catch the version number so it can not tell you what went wrong. Please try again.</div>";
		}
	}
	if(empty($error1))
	{
		$show_form = false;
		
		//Name and upload the selected file
		$file_name = user.date('YmdHis').'_'.str_replace(' ','',$_FILES['upload']['name']);
		move_uploaded_file($_FILES['upload']['tmp_name'], newsoffice_directory.$no_config['dir_uploads'].$file_name);
		chmod(newsoffice_directory.$no_config['dir_uploads'].$file_name,0777);
		//Save upload in "registery" .nzr file
		$saveUp = new newanz_nzr($no_config['dir_info'].'uploads.nzr');
			$saveUp->readfile();
			$new_id = $id;
			if(!empty($new_id))
			{
				$saveUp->save(array('file'=>$file_name),array('id'=>$id),1);
			}
			else
			{
				$saveUp->save(array('file'=>$file_name),'new',1);
				$new_id = $saveUp->insert_id;
			}
		$file_syntax = "<a href='".$no_config['acp_url'].$no_config['dir_uploads'].$file_name."'><img src='".$no_config['acp_url'].$no_config['dir_uploads'].$file_name."' alt='".$app_url.$file_name."'></a>";
		if($saveUp->result==true)
		{
			$page_content .= "Upload <span class='important'>".$_FILES['upload']['name']."</span> has been succesfully saved.<br><br>";
		}
		else
		{
			$page_content .= "<div class='error'>Upload <span class='important'>".$_FILES['upload']['name']."</span> could not be saved. A .Nzr error occured.<br>
			Check the <a href='".url_build('system-status')."'>system status</a> page to see if anything is wrong.</div>";
		}
		if($hack_uploader==true)
		{
			$_SESSION[install_id]['writer']['uploads'][] = $new_id;
			$page_content .= "<input type='submit' name='uploads-new-upload' value='&laquo; Go back to news editor'>";
		}
		else
		{
			$page_content .= "<a href='".url_build('editor-uploads', $new_id)."'>&laquo; Go back to editor</a> | <a href='".url_build('manager-uploads')."'>Go to manager &raquo;</a>";
		}
		$saveUp->close();
	}
}

if($show_form==true)
{
	$page_content .= "This editor allows you to upload new file and edit already uploaded files. You can include them into your news using the News writer.<br>
	".$error1."
	<br>
	<table>";
	if(!empty($id))
	{
		$file_name = '';
		$file_vars = explode('_',$info['file']);
		foreach($file_vars as $file_var)
		{//Remove some NewsOffice prefixes
			if($file_var!==$file_vars[0])
			{
				$file_name .= $file_var;
			}
		}
		if(empty($file_name))
		{//Alright, use total name
			$file_name = $info['file'];
		}
		$page_content .= "
		<tr>
			<td class='subject'>
				File
			</td>
			<td>
				".$file_name."
			</td>
		</tr>
		<tr>
			<td class='subject'>
				Upload ID
			</td>
			<td>
				".$info['id']."
			</td>
		</tr>
		";
	}
	$page_content .= "
		<tr>
			<td class='subject'>
				File
			</td>
			<td>
				<input type='file' name='upload'><br>
				<div class='less_important'>";
	if(!empty($info['file']) && file_exists($no_config['dir_uploads'].$info['file'])==true)
	{
		$page_content .= "<a href='".$no_config['acp_url'].$no_config['dir_uploads'].$info['file']."' target='_blank'><span class='status_ok'>File is uploaded.</span></a>";
	}
	elseif(!empty($id))
	{
		$page_content .= "<span class='status_false'>No file is uploaded.</span>";
	}
	$page_content .= "
				</div>
			</td>
		</tr>
	</table>
	<br>
	<a href='".url_build($back_link,$back_id)."'>&laquo; Go back</a> | <input type='submit' name='";
	if($hack_uploader==true)
	{
		$page_content .= "uploads-new-send";
	}
	else
	{
		$page_content .= "save-upload";
	}
	$page_content .= "' value=' Upload '>";
}
?>