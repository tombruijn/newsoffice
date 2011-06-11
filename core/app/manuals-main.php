<?php
$page_content .= "<h1>Manuals</h1>
If you need help understanding NewsOffice and how it works, you can try reading our manuals. Pick your topic and start reading. The manuals are kept as short as possible to avoid very large pages of plain text.<br><br>";

$sec = 'beginner';
$manuals_headers[$sec] = array("Manuals for beginners","Read these manuals when you have never worked with NewsOffice before and you think you might need some help understanding it.");
$manuals[$sec][] = array('newsposts',"Writing news posts","Get a better understanding of the Writer for the news posts.");
$manuals[$sec][] = array('placement',"How do I make my news viewable on my website?","A quick How-To on how to implement your news page(s) into your website.");

$sec = 'medium';
$manuals_headers[$sec] = array("More advanced manuals for beginners","Read these manuals when you have never worked with NewsOffice before and you think you might need some help understanding it.");
$manuals[$sec][] = array('categories',"Using categories","Get a better understanding of how to use categories to your advantage.");
$manuals[$sec][] = array('comments',"User comments","Users can comment on your news posts.");
$manuals[$sec][] = array('uploads',"Adding and using uploads","Uploads are files from your computer which you can add to your news posts.");
$manuals[$sec][] = array('themes',"Themes","You can use themes to improve the integration of your news page(s) into your website.");
$manuals[$sec][] = array('users',"Users, user groups and permissions","Which users registered and which group are they in? Which group has which permissions?");
$manuals[$sec][] = array('settings',"Settings","What can you find in the settings section and how do you configure it.");

$sec = 'plugins';
$manuals_headers[$sec] = array("Manuals for plugins","Read why, and how, to use plugins and how to make plugins yourself.");
$manuals[$sec][] = array('plugins',"General information about plugins","What are plugins and what should you be carefull of using them.");
$manuals[$sec][] = array('creatingplugins',"Creating your own plugins","You can create your own plugins to use in NewsOffice. You can also share these with other users. How to make a plugin is described in this manual. Read it and start programming!");

$sections = array_keys($manuals_headers);
$i['headers'] = -1;
foreach($manuals_headers as $manuals_header)
{
	$i['headers']++;
	$page_content .= "<h2>".$manuals_header[0]."</h2>
	".$manuals_header[1]."<br>
	<ul>";
	foreach($manuals[$sections[$i['headers']]] as $manual)
	{
		$page_content .= "
		<li>
			<a href='".url_build('manuals-read',$manual[0])."'>".$manual[1]."</a><br>
			<div class='less_important'>".$manual[2]."</div>
		</li>";
	}
	$page_content .= "</ul>";
}
?>