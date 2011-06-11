<?php
$page_content .= "<h1>Back-up</h1>";
$page_content .= "Not included in this version. <a href='http://newanz.com/news/76/release-plans-for-newsoffice.html'>Read more about it here.</a>";
/*
$loaded_libraries = get_loaded_extensions();

if($_POST['backup'])
{
	if(in_array('zip',$loaded_libraries)==false)
	{
		$error = "The PHP zip extension not loaded, could not backup these files.";
	}
	else
	{
		$backup_file = $dir['data']."newsoffice_backup.zip";
		$download_link = $app_url.$backup_file;
		
		if(file_exists($backup_file)==true)
		{
			unlink($backup_file);
		}
		$zip = new ZipArchive;
		if($zip->open($backup_file, ZIPARCHIVE::CREATE)==false)
		{
		    $error = "Cannot create zip file ".$backup_file;
		}
		else
		{
			//Backup files
			$backup_dirs[] = array('news',$dir['news'], true); //true means dir and all objects in it
			$backup_dirs[] = array('comments',$dir['comments'], true);
			$backup_dirs[] = array('info',$dir['info'], true);
			$backup_dirs[] = array('uploads',$dir['uploads'], true);
			$backup_dirs[] = array('emoticons',$dir['emoticons'], true);
			$backup_dirs[] = array('themes',$dir['themes'], true);
			$backup_objects['./'] = array('config.php');
			
			foreach($backup_dirs as $backup_dir)
			{
				if($backup_dir[2]==true)
				{
					$backup_select = glob($backup_dir[1].'*');
					$backup_objects[$backup_dir[0]] = $backup_select;
				}
			}
			$dir_keys = array_keys($backup_objects);
			if(!empty($backup_objects))
			{
				$i['dir_keys'] = -1;
				foreach($backup_objects as $backup_objects_serie)
				{
					$i['dir_keys']++;
					if(!empty($backup_objects_serie))
					{
						foreach($backup_objects_serie as $backup_object)
						{
							unset($inside_file);
							$inside_file = explode('/', $backup_object);
							$inside_file = $dir_keys[$i['dir_keys']].'/'.$inside_file[count($inside_file)-1];
							$inside_file = str_replace('.//','',$inside_file);
							$zip->addFromString($inside_file, "");
							if($zip->open($backup_file)===true)
							{
								$zip->addFile($backup_object, $inside_file);
							}
							else
							{
								$error .= "<div>Failed to add <span class='important'>".$backup_object[0]."</span> to <span class='newsoffice_important'>".$file_name."</div>";
							}
						}
					}
				}
			}
		}
		//Version id
		$zip->addFromString('backup.nzr', $app_id);
		$zip->close();
	}
	
	if(empty($error))
	{
		$page_content .= "<div class='status_ok'>Back-up succesfully created.</div>
		<br>
		If the download not automaticly starts, please <a href='".$download_link."'>click here to start the download</a>.<br>
		<script type='text/javascript'>setTimeout('window.location = \"".$download_link."\"', 2000)</script>";
	}
	else
	{
		$page_content .= "<div class='status_false'>Something went wrong in the back-up process.</div>
		<br>
		<div class='error'>".$error."</div>";
	}
}
else
{
	$page_content .= "Here you can make back-ups for this NewsOffice installation.<br>
	These back-ups can only be imported in NewsOffice 2.0 Beta or higher.<br>
	<div class='less_important'>The more objects (news posts, comments, categories and uploads) you have the longer it takes to create a backup. Uploads take the longest.</div>
	<br>
	<input type='submit' name='backup' value='Start back-up process'>";
}*/
?>