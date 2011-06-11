<?php
$show_form = true;
$page_content = "<h1>Writer - ";
if(!empty($id))
{
	//Load news file
	$openN = new newanz_nzr(newsoffice_directory.$no_config['dir_news'].$id.'.nzr');
		$openN->readfile();
		$openN->sort(array('updated'),'date_desc','0,1');
		$fkey = array_keys($openN->content); //Get first key
		$news = $openN->content[$fkey[0]];
		$selected_version = $fkey[0];
	$openN->close();
	//Load extra info
	$openP = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr');
		$openP->readfile();
		$openP->search(array('news_id'=>$id));
		$news['publish'] = $openP->content[0]['published'];
		$news['date'] = explode('-',$openP->content[0]['date']);
		$news['time'] = explode(':',$openP->content[0]['time']);
	$openP->close();
	$page_content .= "Edit post";
}
else
{//Set default date and time
	$news['date'] = array(date('Y'),date('m'),date('d'));
	$news['time'] = array(date('H'),date('i'));
	$page_content .= "New post";
}
$page_content .= "</h1>";
//Get categories
$openCa = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories.nzr');
	$openCa->readfile();
	$categories = $openCa->content;
$openCa->close();
//Get links from news post to categories
$openCl = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories-link.nzr');
	$openCl->readfile();
	$openCl->search(array('news_id'=>$id));
	$openCl->rekey(array('category_id'));
	$cat_link = $openCl->content;
$openCl->close();

if(array_key_exists('preview',$_POST)==true)
{
	$_POST["content"] = strtr($_POST["content"], array("\r\n"=>'',"\r"=>'',"\n"=>''));
	$_POST["description"] = strtr($_POST["description"], array("\r\n"=>'',"\r"=>'',"\n"=>''));
	$news = $_POST;
	$_SESSION[install_id]['writer']['content'] = $news;
	$_SESSION[install_id]['writer']['content']['date'] = $news['date-year'].'-'.$news['date-month'].'-'.$news['date-day'];
	$_SESSION[install_id]['writer']['content']['time'] = $news['time-hour'].'-'.$news['time-minute'];
	$news['date'] = array($news['date-year'],$news['date-month'],$news['date-day']);
	$news['time'] = array($news['time-hour'],$news['time-minute']);
	$page_content .= "
	<script type='text/javascript'>
		previewWindow = window.open('".$no_config['acp_url'].$no_config['dir_scripts']."preview.php','Preview: News Post');
	</script>
	";
}
elseif(array_key_exists('save',$_POST)==true || array_key_exists('saveandselect',$_POST)==true || array_key_exists('preview',$_POST)==true)
{
	$_POST["content"] = strtr($_POST["content"], array("\r\n"=>'',"\r"=>'',"\n"=>''));
	$_POST["description"] = strtr($_POST["description"], array("\r\n"=>'',"\r"=>'',"\n"=>''));
	if(empty($_POST['name']))
	{
		$error1 = 'name';
	}
	if(empty($_POST['content']))
	{
		$error2 = 'content';
	}
	//Check for errors
	if(empty($error1) && empty($error2))
	{
		$show_form = false;
		if(array_key_exists('saveandselect',$_POST)==true)
		{
			//Save global information
			$saveP = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'published.nzr');
				$saveP->readfile();
				if(empty($id))
				{
					$smode1 = 'new';
					$smode2 = '';
				}
				else
				{
					$smode1 = array('news_id'=>$id);
					$smode2 = 1;//Only edit one record
				}
				$saveP->save(
					array(
						'published'=>$_POST['publish'],
						'date'=>date("Y-m-d",mktime(0,0,0,$_POST['date-month'],$_POST['date-day'],$_POST['date-year'])),
						'time'=>date("H:i",mktime($_POST['time-hour'],$_POST['time-minute'],0,date('m'),date('d'),date('Y')))
					),
					$smode1,
					$smode2
				);
				if(empty($id))
				{
					$newid = $saveP->insert_id;
				}
			$saveP->close();
		}
		if(empty($id))
		{
			//Create a new news .nzr file
			$news_file = newsoffice_directory.$no_config['dir_news'].$newid.'.nzr';
			$saveNew = new newanz_nzr($news_file,'create');
				$saveNew->create_file(array('version','updated','published','id','name','user_id','date','time','description','content'));
				$saveNew->set_primary_keys(array('version'));
			$saveNew->close();
		}
		else
		{
			$news_file = newsoffice_directory.$no_config['dir_news'].$id.'.nzr';
		}
		
		$saveN = new newanz_nzr($news_file);
			$saveN->readfile();
			$amount_versions = $saveN->amount_rows;
			if(empty($id))
			{
				//New and first save of the file
				$saveN->save(
					array(
						'version'=>'published',
						'updated'=>date('Y-m-d_H:i:s'),
						'id'=>$newid,
						'name'=>$_POST['name'],
						'user_id'=>$_POST['user_id'],
						'date'=>date("Y-m-d",mktime(0,0,0,$_POST['date-month'],$_POST['date-day'],$_POST['date-year'])),
						'time'=>date("H:i",mktime($_POST['time-hour'],$_POST['time-minute'],0,date('m'),date('d'),date('Y'))),
						'description'=>$_POST['description'],
						'content'=>$_POST['content']
					),
					'new'
				);
				$amount_versions++;
			}
			else
			{
				if(array_key_exists('save',$_POST)==true)
				{
					//New version creation
					$saveN->save(
						array(
							'version'=>'draft',
							'updated'=>date('Y-m-d_H:i:s'),
							'id'=>$id,
							'name'=>$_POST['name'],
							'user_id'=>$_POST['user_id'],
							'description'=>$_POST['description'],
							'content'=>$_POST['content']
						),
						'new'
					);
					$amount_versions++;
				}
				else
				{
					//Remove other published versions (change version to draft)
					$saveN->save(
						array('version'=>'draft'),
						array('version'=>'published')
					);
					//Save published version
					$saveN->save(
						array(
							'version'=>'published',
							'updated'=>date('Y-m-d_H:i:s'),
							'id'=>$id,
							'name'=>$_POST['name'],
							'user_id'=>$_POST['user_id'],
							'description'=>$_POST['description'],
							'content'=>$_POST['content']
						),
						array('updated'=>$selected_version),
						1
					);
				}
			}
			//More versions than allowed?
			if($amount_versions>$no_config['acp_amount_version'] && !empty($no_config['acp_amount_version']))
			{
				//More than allowed, delete the amount to many
				$amount_deleted = $amount_versions-$no_config['acp_amount_version'];
				if($amount_deleted>0)
				{
					//Delete the oldest draft
					$saveN->sort(array('updated'),'date_asc');
					$saveN->delete(array('version'=>'draft'),$amount_deleted);
				}
			}
		$saveN->close();
		
		if(empty($id))
		{
			$id = $newid;
		}
		//Save selected categories
		$saveC = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'categories-link.nzr','MULTIPLE_SAVES_FRIENDLY');
			$saveC->readfile();
			$saveC->delete(array('news_id'=>$id)); //Delete all previous entries
			if(!empty($_POST['category']))
			{
				foreach($_POST['category'] as $category)
				{
					$saveC->save(
						array(
							'category_id'=>$category,
							'news_id'=>$id
						),
						'new'
					);//Save selected categories to file
				}
			}
		$saveC->close(); //Actual save here
		
		$page_content .= "News post <span class='important'>".$_POST['name']."</span> has been succesfully saved.";
		if($_POST['publish']=='publish')
		{
			$page_content .= "The post is published so that means it is visible on your news page.";
		}
		elseif($_POST['publish']=='unpublish')
		{
			$page_content .= "The post is unpublished, meaning it will not be visible on your news page. Do not forget about it!";
		}
		$page_content .= "<br><br><a href='".url_build('editor-news', $id)."'>&laquo; Go back to writer</a> | <a href='".url_build('manager-news')."'>Go to manager &raquo;</a>";
	}
	$news = $_POST;
	$news['date'] = array($news['date-year'],$news['date-month'],$news['date-day']);
	$news['time'] = array($news['time-hour'],$news['time-minute']);
}
//Uploads gallery
elseif($_POST['uploads-add'])
{
	$show_form = false;
	$_SESSION[install_id]['writer']['content'] = $_POST;
	$_SESSION[install_id]['writer']['saved'] = '1';

	$page_content = "<h1>Upload gallery</h1>Select uploads you want to include in your news post. Then click on the select button and you will be brought back to your news post. Your uploads will be added to the end of your news post in the <span class='important'>content textarea</span>.<br>
	<div class='less_important'>If you do not select any upload press the add button aswell, nothing will be added to your news post.</div>
	<br>";
	
	$openUp = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'uploads.nzr');
		$openUp->readfile();
	if($openUp->amount_rows<=0)
	{
		$page_content .= "<div class='error'><h2>No uploads added</h2>
		No uploads are added to this installation, press the upload button below to upload a new file.<br>
		<br>
		<input type='submit' name='return' value='&laquo; Go back'> | <input type='submit' name='uploads-new' value='Upload a new file'></div>";
	}
	else
	{
		$openUp->sort(array('id'),'num_desc');
		$page_content .= "
		<div class='block-right'>
			<input type='submit' name='upload-news' value=' Add the selected uploads to your news (if any) '>
		</div>
		<table>";
		foreach($openUp->content as $upload)
		{
			$i['cols']++;
			if($i['cols']==1)
			{
				$page_content .= "<tr>";
			}
			$page_content .= "<td>";
			if(file_exists(newsoffice_directory.$no_config['dir_uploads'].$upload['file'])==true && !empty($upload['file']))
			{
				//File name
				$file_vars = explode('_',$upload['file']);
				unset($file_name);
				foreach($file_vars as $file_var)
				{
					if($file_var!==$file_vars[0])
					{
						$file_name .= $file_var;
					}
				}
				if(empty($file_name))
				{
					$file_name = $upload['file'];
				}
				//Upload info
				$upload_types['images'] = array('jpg','jpeg','gif','bmp','png','dib','jpe','jfif','tiff','tif','rle','raw');
				$upload_type = explode('.',$upload['file']);
				if(in_array(strtolower($upload_type[count($upload_type)-1]),$upload_types['images'])==true)
				{
					$type = 'image';
					//Image sizes
					$imagedata = getimagesize(newsoffice_directory.$no_config['dir_uploads'].$upload['file']);
					$w = $imagedata[0];
					$h = $imagedata[1];

					$max_var = "200";
					$min_var = "100";
					
					while($w > $max_var || $h > $max_var)
					{
						$w = $w / "2";
						$h = $h / "2";
					}
					
					$w = $w."px";
					$h = $h."px";
				
					//Images
					$page_content .= "<div class='inside' style='height: ".($h)."; margin-right: 5px; text-align: center;'><a href='".$no_config['acp_url'].$no_config['dir_uploads'].$upload['file']."' target='_blank'><img src='".$no_config['acp_url'].$no_config['dir_uploads'].$upload['file']."' alt='Upload: ".$upload['id']."' style='width: ".$w."; height: ".$h.";'></a>
					</div>
					<div class='block' style='margin-right: 5px;'>
						Image: <a href='".$no_config['acp_url'].$no_config['dir_uploads'].$upload['file']."' target='_blank'>".$file_name."</a><br>
						Size width: ".$imagedata[0]." pixels<br>
						Size height: ".$imagedata[1]." pixels<br>
						Uploaded: ".no_format_date(date("Y-m-d", filemtime(newsoffice_directory.$no_config['dir_uploads'].$upload['file'])))."
					</div>";
				}
				else
				{
					//Files
					$type = 'file';
					$page_content .= "<div class='block' style='margin-right: 5px;'>File: <a href='".$no_config['acp_url'].$no_config['dir_uploads'].$upload['file']."' target='_blank'>".$file_name."</a><br>
					Uploaded: ".no_format_date(date("Y-m-d", filemtime(newsoffice_directory.$no_config['dir_uploads'].$upload['file'])))."</div>";
				}
			}
			else
			{
				$type = 'not found file';
				$page_content .= "<div class='error' style='margin-right: 5px;'>Upload not found.</div>";
			}
			$page_content .= "<div class='inside' style='margin-right: 5px;'><input type='checkbox' name='upload[".$upload['id']."]' value='".$upload['id']."'>Select the <span class='important'>".$type."</span> above</div>";
			
			$page_content .= "</td>";
			if($i['cols']>=4 || $upload['id']==$uploads[count($uploads)-1]['id'])
			{
				$page_content .= "</tr>";
				$i['cols'] = 0;
			}
		}
		$page_content .= "</table>
		<div class='block-right'>
			<input type='submit' name='upload-news' value=' Add the selected uploads to your news (if any) '>
		</div>";
	}
	$openUp->close();
}
//Uploads
elseif($_POST['uploads-new'] || $_POST['uploads-new-send'])
{
	if($_SESSION[install_id]['writer']['saved']!=='1')
	{
		$_SESSION[install_id]['writer']['saved'] = '1';
		$_SESSION[install_id]['writer']['id'] = $id;
		$_SESSION[install_id]['writer']['content'] = $_POST;
	}
	//Get uploader content
	ob_start();
		$hack_uploader = true;
		include(newsoffice_directory.$no_config['dir_app']."editor-uploads.php");
		echo $page_content;
		$page_content = ob_get_contents();
	ob_end_clean();
	$show_form = false;
}

if($show_form==true)
{
	//Advanced options are used here
	define("ADVANCED_OPTIONS", 'editor-news');
	$page_content .= "In the writer you are able to edit existing news posts or create new posts. You are also able to include smilies and uploaded files.<br>In the upper right corner you can see a button that says either <span class='important'>Simple</span> or <span class='important'>Advanced</span>. Press it to switch to another edit mode.";

	if($_SESSION[install_id]['writer']['saved']=='1')
	{
		if($_SESSION[install_id]['writer']['id']!==$id)
		{
			//unset($_SESSION[install_id]['writer']);
		}
		unset($cat_link);
		$news['name'] = $_SESSION[install_id]['writer']['content']['name'];
		$news['user_id'] = $_SESSION[install_id]['writer']['content']['author'];
		
		$news['date'][0] = $_SESSION[install_id]['writer']['content']['date-year'];
		$news['date'][1] = $_SESSION[install_id]['writer']['content']['date-month'];
		$news['date'][2] = $_SESSION[install_id]['writer']['content']['date-day'];
		
		$news['time'][0] = $_SESSION[install_id]['writer']['content']['time-hour'];
		$news['time'][1] = $_SESSION[install_id]['writer']['content']['time-minute'];
		
		$news['description'] = $_SESSION[install_id]['writer']['content']['description'];
		$news['content'] = $_SESSION[install_id]['writer']['content']['content'];
		$news['publish'] = $_SESSION[install_id]['writer']['content']['publish'];
		if($_POST['upload-news'] || $_POST['uploads-new-upload'])
		{
			if($_POST['upload-news'])
			{
				$uploads_files = $_POST['upload'];
			}
			elseif($_POST['uploads-new-upload'])
			{
				$uploads_files = $_SESSION[install_id]['writer']['uploads'];
			}
			
			//Add uploads to content
			if(!empty($uploads_files))
			{
				foreach($uploads_files as $upload)
				{
					$news['content'] .= " [upload/".$upload."]";
				}
			}
		}
		unset($_SESSION[install_id]['writer']['saved']);
	}
$page_content .= "
<div class='sidebar' id='sidebar_".ADVANCED_OPTIONS."'>";
	
	/****************************************************************
	LOADING EXTRA OPTIONS FOR NEWS POST
	****************************************************************/
	//Add a description to the news post
		$advExtCon .= "<div class='inside'><a href='#description_editor' id='add_description_link' onclick='advanced_options_show(\"description_editor\");'>&laquo; ";
		if(no_check_box('advanced_editor','description_editor')==true)
		{
			$advExtCon .= "Hide description";
		}
		else
		{
			$advExtCon .= "Add description";
		}
		$advExtCon .= "</a></div><div class='inside'><h3>Preview</h3>Theme: <select name='theme'>";
		$themes = glob(newsoffice_directory.$no_config['dir_themes']."*.nzr");
		if(!empty($themes))
		{
			foreach($themes as $theme)
			{
				$openT = new newanz_nzr($theme, 'READ_ONLY');
					$openT->readfile();
					$openT->rekey(array('object'));
					$theme_info = $openT->content;
					$theme_file = preg_replace("#".newsoffice_directory.$no_config['dir_themes']."(.*?).nzr#",'\\1', $theme);
					$advExtCon .= "<option value='".$theme_file."'";
					if($_POST['theme']==$theme_file)
					{
						$advExtCon .= " selected";
					}
					$advExtCon .= ">".$theme_info['name']['value']."</option>";
				$openT->close();
			}
		}
		$advExtCon .= "</select><input type='submit' name='preview' value=' Preview '></div>";
	$page_content .= no_advanced_box("Extra",'news_extra',$advExtCon);
	
	/****************************************************************
	LOADING AUTHOR, DATE AND TIME AND PUBLISH STATE OPTIONS
	****************************************************************/
	$advInfCon .= "
			<table class='advanced_options'>
				<tr class='advanced_option'>
					<td class='subject'>
						Author
					</td>
					<td>
						<select name='user_id' tabindex='11'>";
						foreach($users as $user)
						{
							$advInfCon .= "<option value='".$user['id']."'".no_group_color($user['role']);
							if((empty($news['user_id']) && user==$user['id']) || (!empty($news['user_id']) && $user['id']==$news['user_id']))
							{
								$advInfCon .= " selected";
							}
							$advInfCon .= ">".$user['username']."</option>";
						}
						$advInfCon .= "</select>
					</td>
				</tr>
				<tr class='advanced_option'>
					<td class='subject'>
						Date
					</td>
					<td>
						<select name='date-year' tabindex='6'>";
						for($y=date('Y')+1; $y>=(date('Y')-20); $y--)
						{
							$advInfCon .= "<option value='".$y."'";
							if($news['date'][0]==$y)
							{
								$advInfCon .= " selected";
							}
							$advInfCon .= ">".$y."</option>";
						}
						$advInfCon .= "</select>
						<select name='date-month' tabindex='7'>";
						for($m=1; $m<=12; $m++)
						{
							$advInfCon .= "<option value='".$m."'";
							if($news['date'][1]==$m)
							{
								$advInfCon .= " selected";
							}
							$advInfCon .= ">".date("M", mktime(0,0,0,$m,1,date('Y')))."</option>";
						}
						$advInfCon .= "</select>
						<select name='date-day' tabindex='8'>";
						for($d=1; $d<=31; $d++)
						{
							$advInfCon .= "<option value='".$d."'";
							if($news['date'][2]==$d)
							{
								$advInfCon .= " selected";
							}
							$advInfCon .= ">";
							if(strlen($d)==1)
							{
								$advInfCon .= "0";
							}
							$advInfCon .= $d."</option>";
						}
						$advInfCon .= "</select>
						<div class='less_important'>(YYYY-MM-DD)</div>
					</td>
				</tr>
				<tr class='advanced_option'>
					<td class='subject'>
						Time
					</td>
					<td>
						<select name='time-hour' tabindex='9'>";
						for($h=0; $h<=23; $h++)
						{
							$advInfCon .= "<option value='".$h."'";
							if($news['time'][0]==$h)
							{
								$advInfCon .= " selected";
							}
							$advInfCon .= ">";
							if(strlen($h)==1)
							{
								$advInfCon .= "0";
							}
							$advInfCon .= $h."</option>";
						}
						$advInfCon .= "</select> : 
						<select name='time-minute' tabindex='10'>";
						for($m=0; $m<=59; $m++)
						{
							$advInfCon .= "<option value='".$m."'";
							if($news['time'][1]==$m)
							{
								$advInfCon .= " selected";
							}
							$advInfCon .= ">";
							if(strlen($m)==1)
							{
								$advInfCon .= "0";
							}
							$advInfCon .= $m."</option>";
						}
						$advInfCon .= "</select>
						<div class='less_important'>(HH:ii)</div>
					</td>
				</tr>
				<tr class='advanced_option'>
					<td class='subject'>
						Publishing<br>
						<div class='less_important'>Publishing news will put it on your news page.</div>
					</td>
					<td>
						";
						$i['tabindex'] = '12';
						$add_ops = array('yes'=>'Publish','no'=>'Unpublish','time'=>'On time');
						foreach($add_ops as $add_key=>$add_op)
						{
							$i['tabindex']++;
							$advInfCon .= "<input type='radio' name='publish' value='".$add_key."' tabindex='".$i['tabindex']."'";
							if(
								(!empty($news['publish']) && $news['publish']==$add_key)
								||
								(empty($news['publish']) && $add_key=='yes')
							)
							{
								$advInfCon .= " checked";
							}
							$advInfCon .= "> ".$add_op."<br>";
						}
						$advInfCon .= "<div class='less_important'>On time: Shows on selected date and time.</div>
					</td>
				</tr>
			</table>";
	$page_content .= no_advanced_box("Information",'news_info',$advInfCon);
	/****************************************************************
	LOADING CATEGORIES FOR NEWS POST, LOADED INTO FUNCTION BELOW
	****************************************************************/
	if(!empty($categories))
	{
		$max_cols = 1;
		$advCatCon .= "Select one or more categories to place your news post in.<br>
		<div class='inside'>
		<table>
			<col style='width: ".(100/$max_cols)."%;'>";
		$i['tabindex'] = '23';
		foreach($categories as $category)
		{
			$i['tabindex']++;
			if($i['categories']==0)
			{
				$advCatCon .= "<tr>";
			}
			$i['categories']++;
			$advCatCon .= "<td>
					<input type='checkbox' name='category[]' value='".$category['id']."' id='cat_".$category['id']."'";
			if(!empty($cat_link) && array_key_exists($category['id'],$cat_link)==true)
			{
				$advCatCon .= " checked";
			}
			$advCatCon .= " tabindex='".$i['tabindex']."'> <label for='cat_".$category['id']."'>".$category['name']."</label>
				</td>";
			if($i['categories']>=$max_cols)
			{
				$advCatCon .= "</tr>";
				$i['categories'] = 0;
			}
		}
		$advCatCon .= "</table></div>";
	}
	$page_content .= no_advanced_box("Categories",'news_categories',$advCatCon);
	$page_content .= "</div>
<div class='mainsection' id='mainsection_".ADVANCED_OPTIONS."'>";

	$page_content .= "<br>
	<h2>Title</h2>
	";
	if($error1=='name')
	{
		$page_content .= "<div class='error'>Title is required.</div>";
	}
	$page_content .= " 
	<input type='text' name='name' value='".no_convert_field($news['name'])."' tabindex='1' onkeypress='if(event.keyCode==13){ document.getElementById(\"save";
	if(!empty($id))//Fix for new posts
	{
		$page_content .= "andselect";
	}
	$page_content .= "\").click(); return false; }' style='width: 100%;'>";
	
	//Form for editing
	$page_content .= "<h2>Content</h2>";
	if($error2=='content')
	{
		$page_content .= "<div class='error'>Content is required.</div>";
	}
	$page_content .= "
	<div class='block'>
		<div>
			<a name='actions'></a>
			<input type='submit' name='uploads-add' value='Add upload' tabindex='20'> | 
			<input type='submit' name='uploads-new' value='Upload new file' tabindex='21'>
		</div>
		<textarea name='content' id='content' rows='10' cols='10' class='mceEditor' tabindex='2'>".no_convert_field($news['content'],true)."</textarea>
		<div class='less_important'>[upload/#] are uploads.</div>
	</div>
	";
	$advDesCon .= "
		<a name='description_editor'></a>
		<textarea name='description' id='description' rows='10' cols='10' class='mceEditor' tabindex='3'>".no_convert_field($news['description'],true)."</textarea>
		<div class='less_important'>Optional. You are able to add uploads in this textarea as well. This will need to be done manually: [upload/#].</div>
	";
	$page_content .= no_advanced_box("Description",'description_editor',$advDesCon,true);

	$page_content .= "</div>
	<a href='".url_build('manager-news')."'>&laquo; Cancel</a>";
	
	/* //! Do not allow this version system in this version. I will activate it in the new version */
	/*
	//Do not allow on new post creation
	if(!empty($id))
	{
		$page_content .= " | <input type='submit' id='save' name='save' value=' Save ' tabindex='4'>";
	}
	*/
	
	$page_content .= " | <input type='submit' id='saveandselect' name='saveandselect' value=' Save ";
	
	/* //! Do not allow this version system in this version. I will activate it in the new version */
	/*if(!empty($id))
	{//Don't bother the user with this message with the new post
		$page_content .= "and select this version ";
	}*/
	$page_content .= "' tabindex='5'>";
	
	if(no_check_box('sidebar',ADVANCED_OPTIONS)==false)
	{
		$page_content .= "<script type='text/javascript'>advanced_boxes_hider(\"".ADVANCED_OPTIONS."\");</script>";
	}
}

if($_SESSION[install_id]['writer']['saved']=='0')
{
	unset($_SESSION[install_id]['writer']);
}
?>