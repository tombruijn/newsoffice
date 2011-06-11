<?php
$notes['file'] = $no_config['dir_info'].'notes.txt';
if(file_exists($notes['file'])==true)
{
	$nzr_file = fopen($notes['file'],"r");
	$notes['content'] = fread($nzr_file, filesize($notes['file']));
	fclose($nzr_file);
}
else
{
	$nzr_file = fopen($notes['file'],"w+");
	if($nzr_file==true)
	{
		$result = "<div class='important'>New notes file created.</div>";
		fwrite($nzr_file, ' ');
	}
	fclose($nzr_file);
}

$page_content .= "<h1>Notes</h1>";

if($_POST['save'])
{
	$nzr_file = fopen($notes['file'],"w+");
	fwrite($nzr_file, $_POST['content']);
	fclose($nzr_file);
	$notes['content'] = $_POST['content'];
	$result = "<div class='important'>Your notes are saved (".date($no_config['format_time']).").</div>";
}

$page_content .= "Write your notes here, but remember everybody can read and write these notes. Usefull, but be carefull who has access.<br>".$result."<br>
<textarea name='content' id='notes' rows='100000' cols='100000' class='large'>".no_convert_field($notes['content'],true)."</textarea><br>
<br>
<a href='".url_build('dashboard-main')."'>&laquo; Go back</a> | <input type='submit' name='save' value='Save'>";
?>