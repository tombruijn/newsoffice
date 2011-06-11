<?php
$page_content = "
<div class='block-right'>
	Actions for selected: <input type='submit' name='delete' value=' Delete '>
	<input type='hidden' name='delete-type' value='categories'>
</div>

<h1>Category manager</h1>
In the Category manager you can manager the categories news posts are placed in.<br>

<table>
	<tr>
		<th style='width: 20px;'>&nbsp;</th>
		<th>
			Name
		</th>
		<th>
			Added news posts
		</th>
	</tr>
	<tr class='title'>
		<td style='width: 20px;'></td>
		<td>
			<a href='".url_build('editor-category')."' class='new'>New</a>
		</td>
	</tr>";
//Load categories information
$categories = new newanz_nzr($no_config['dir_info'].'categories.nzr');
	$categories->readfile();
	$items['total'] = $categories->amount_rows;
	if($no_config['acp_items_page']=='all')
	{
		$show_limit = '';
	}
	else
	{
		$show_limit = (($page-1)*$no_config['acp_items_page']).','.$no_config['acp_items_page'];
	}
	$categories->sort(array('name'),'',$show_limit);
	$items['shown'] = $categories->amount_rows;
if($categories->amount_rows<=0)
{
	$page_content .= "
	<tr>
		<td></td>
		<td colspan='2'>
			<span class='less_important'>";
		if($page==1)
		{
			$page_content .= "No categories made yet.";
		}
		else
		{
			$page_content .= "No categories found, try return to the <a href='".url_build($name,$id)."'>firstpage</a>.";
		}
		$page_content .= "</span>
		</td>
	</tr>";
}
else
{
	//Load links
	$links = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories-link.nzr');
		$links->readfile();
		$nzr_keep = $links->set_store();
	$links->close();
	foreach($categories->content as $info)
	{
		//Check for amount of posts inside category
		$check = new newanz_nzr();
		$check->set_import($nzr_keep);
			$check->search(array('category_id'=>$info['id'])); //Only this category
		$page_content .= "
		<tr>
			<td>
				<input type='checkbox' value='".$info['id']."' name='select[".$info['id']."]'>
			</td>
			<td>
				<a href='".url_build('editor-category',$info['id'])."'>".$info['name']."</a>
			</td>
			<td>
				".$check->amount_rows."
			</td>
		</tr>";
		$check->close();
	}
}
$categories->close();
$page_content .= "</table>";

$page_content .= no_show_pages($items['total']);
?>