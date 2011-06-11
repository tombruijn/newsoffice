<?php
$manual_file = $no_config['dir_app'].'manuals/'.$id.'.php';
if(file_exists($manual_file)==true)
{
	include($manual_file);
}
$i['headers'] = -1;
if(empty($headers))
{
	$page_content .= "<h1>Manual not found</h1>
	The manual you requested could not be found.<br>
	<br>
	<a href='".url_build('manuals-main')."'>&laquo; Go back to all manuals</a><br>";
	$extra_title = 'Not found';
}
else
{
	$sections = array_keys($headers);
	foreach($headers as $header)
	{
		$i['headers']++;
		$page_content .= "<h1>".$header[0]."</h1>".$header[1]."
		<div class='block'>
		<h2>Table of contents</h2>
		<div class='less_important'>Click on the title to go to that section.</div>";
		$i['subs'] = 0;
		foreach($h_content[$sections[$i['headers']]] as $section)
		{
			$i['subs']++;
			$page_content .= str_repeat("<ul>",$section[0])."<li><a href='#".$i['subs']."'>".$section[1]."</a></li>".str_repeat("</ul>",$section[0]);
		}
		$page_content .= "</div><a href='".url_build('manuals-main')."'>&laquo; Go back to all manuals</a><br>
		<hr>";
		$i['subs'] = 0;
		foreach($h_content[$sections[$i['headers']]] as $section)
		{
			$i['subs']++;
			$page_content .= "
				<div style='padding-left: ".(($section[0])*20)."px;'>
					<h".($section[0]+1).">
						<a name='".$i['subs']."'></a>
						".$section[1]."
					</h".($section[0]+1).">
					<div style='padding-left: ".(($section[0]+1)*10)."px;'>
						".$section[2]."
					</div>
				</div>
				<br>";
		}
		$page_content .= "<hr><a href='".url_build('manuals-main')."'>&laquo; Go back to all manuals</a><br>";
	}
}
?>