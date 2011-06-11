<?php
$show_form = true;
$page_title = "New category";

//Load this category
$category = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories.nzr');
	$category->readfile();
if(!empty($id))
{
	//Select category
	$category->search(array('id'=>$id));
	if($category->amount_rows>0)
	{
		$info = $category->content[0];
		$page_title = "Edit category";
	}
}
$category->close();
$page_content .= "<h1>".$page_title."</h1>";

if($_POST['save'])
{
	if(empty($_POST['name']))
	{
		$error1 = 'name';
	}
	
	if(empty($error1))
	{
		$show_form = false;
		
		//Load this category
		$save = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories.nzr');
			$save->readfile();
			$_POST["description"] = strtr($_POST["description"], array("\r\n"=>'',"\r"=>'',"\n"=>''));
			if(!empty($id))
			{
				$save->save(
					array(
						'name' => $_POST['name'],
						'description' => $_POST['description']
					),
					array('id' => $id),
					1
				);
			}
			else
			{
				$save->save(
					array(
						'name' => $_POST['name'],
						'description' => $_POST['description']
					),
					'new'
				);
				$id = $save->insert_id;
			}
		$save->close();
		$page_content .= "Category <span class='important'>".$_POST['name']."</span> has been succesfully saved.";
		$page_content .= "<br><br><a href='".url_build('editor-category', $id)."'> &laquo; Go back to editor</a> | <a href='".url_build('manager-categories')."'>Go to manager &raquo; </a>";
	}
	$info['name'] = $_POST['name'];
	$info['description'] = $_POST['description'];
}

if($show_form==true)
{
	$page_content .= "This editor allows you to create or save existing categories. These can be used to manage your news.<br>
	<br>
	<table>
		<tr>
			<td class='subject'>
				Name
			</td>
			<td>
				<input type='text' name='name' value='".no_convert_field($info['name'])."'>";
		if($error1=='name')
		{
			$page_content .= "<div class='error'>Name is required.</div>";
		}
	$page_content .= "
			</td>
		</tr>
		<tr>
			<td class='subject'>
				Description
			</td>
			<td>
				<textarea name='description' rows='10' cols='10' class='mceEditor'>".no_convert_field($info['description'],true)."</textarea>
			</td>
		</tr>
	</table>
	<a href='".url_build('manager-categories')."'>&laquo; Go back to manager</a> | <input type='submit' name='save' value=' Save category '>";
}
?>