<?php
$page_content = "
<div class='block-right'>
	Actions for selected: <input type='submit' name='delete' value=' Delete '>
	<input type='hidden' name='delete-type' value='uploads'>
</div>

<h1>Uploads manager</h1>
The uploads manager allows you to upload files which you can then include into your news posts.<br>
<div>Current size of uploads directory: ";
$install_size = no_dir_size(newsoffice_directory.$no_config['dir_uploads']);
$page_content .= "<span class='important'>".$install_size['size_size']." ".$install_size['size_type']."</span><br>";
if(!empty($install_size['message']))
{
	$page_content .= $install_size['message'];
}
$page_content .= "</div>
<br>
<table>
	<tr>
		<th style='width: 20px;'>&nbsp;</th>
		<th>
			Name
		</th>
		<th style='width: 5%;'>
			ID
		</th>
		<th>
			View
		</th>
		<th>
			Status
		</th>
	</tr>
	<tr>
		<td style='width: 20px;'></td>
		<td colspan='3'>
			<a href='".url_build('editor-uploads')."' class='new'>New</a>
		</td>
	</tr>";
$openUp = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'uploads.nzr');
	$openUp->readfile();
	$items['total'] = $openUp->amount_rows;
	if($no_config['acp_items_page']=='all')
	{
		$show_limit = '';
	}
	else
	{
		$show_limit = (($page-1)*$no_config['acp_items_page']).','.$no_config['acp_items_page'];
	}
	$openUp->sort(array('id'),'num_desc',$show_limit);
	$items['shown'] = $openUp->amount_rows;
if($items['total']<=0)
{
	$page_content .= "<tr>
		<td></td>
		<td colspan='4'>
			<span class='less_important'>No uploads placed yet.</span>
		</td>
	</tr>";
}
else
{
	foreach($openUp->content as $info)
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
		$page_content .= "<tr>
			<td>
				<input type='checkbox' value='".$info['id']."' name='select[".$info['id']."]'>
			</td>
			<td>
				<a href='".url_build('editor-uploads',$info['id'])."'>".$file_name."</a>
			</td>
			<td>
				".$info['id']."
			</td>
			<td>
				<a href='".$no_config['acp_url'].$no_config['dir_uploads'].$info['file']."' target='_blank'>View</a>
			</td>
			<td>
		";
		if(!empty($info['file']) && file_exists($no_config['dir_uploads'].$info['file'])==true)
		{
			//$page_content .= "<span class='status_ok'>File is uploaded</span>";
		}
		else
		{
			$page_content .= "<span class='status_false'>No file is uploaded</span>";
		}
		$page_content .= "</td>
		</tr>";
	}
}
$openUp->close();
$page_content .= "</table>";

$page_content .= no_show_pages($items['total']);
?>