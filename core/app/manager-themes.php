<?php
$page_content = "
<div class='block-right'>
	Actions for selected: <input type='submit' name='delete' value=' Delete '>
	<input type='hidden' name='delete-type' value='themes'>
</div>

<h1>Themes</h1>
Edit the theme(s) you will use to display your news on your news page in your style.<br>
<br>
<table>
	<tr>
		<th style='width: 20px;'>
			&nbsp;
		</th>
		<th>
			<a href='".url_build('manager-themes-editor')."' class='new'>Create new theme</a>
		</th>
	</tr>";
$themes = glob(newsoffice_directory.$no_config['dir_themes']."*.nzr");
if(!empty($themes))
{
	$items['total'] = count($themes);
	if($no_config['acp_items_page']=='all')
	{
		$show_start = 0;
		$show_limit = $no_config['acp_items_page'];
	}
	else
	{
		$show_start = ($page-1)*$no_config['acp_items_page'];
		$show_limit = $no_config['acp_items_page'];
		if($page>1)//Should work
		{
			$show_limit++;
		}
	}
	for($i=$show_start; $i<$show_limit; $i++)
	{
		if(!empty($themes[$i]))
		{
			$openTheme = new newanz_nzr($themes[$i]);
				$openTheme->readfile();
				$openTheme->rekey(array('object'));
				$info = $openTheme->content;
			if(!empty($info['name']['value']))
			{
				$theme_file = preg_replace("#".newsoffice_directory.$no_config['dir_themes']."(.*?).nzr#",'\\1', $themes[$i]);
				$page_content .= "<tr><td><input type='checkbox' name='select[".$theme_file."]' value='".$theme_file."'></td><td><a href='".url_build('manager-themes-editor',$theme_file)."'>".$info['name']['value']."</a></li></ul></td></tr>";
			}
			$openTheme->close();
		}
	}
}
$page_content .= "</table>";

$page_content .= no_show_pages($items['total']);
?>