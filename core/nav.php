<?php
$openNc = new newanz_nzr($no_config['dir_info'].'nav-cat.nzr');
	$openNc->readfile();
	$openNc->rekey(array('nav_id'));
	$openNc->sort(array('nav_id'),'num_asc');
	$nav_categories = $openNc->content;
$openNc->close();
$openNp = new newanz_nzr($no_config['dir_info'].'nav.nzr');
	$openNp->readfile();
	$openNp->rekey(array('name'));
	//$openNp->sort(array('name'),'char_asc');
	$nav_pages = $openNp->content;
$openNp->close();

//Loading permissions seperatly, due to performance, in stead of using the is_allowed function which reloads the permission file, a lot.
$openP = new newanz_nzr($no_config['dir_info'].'users-permissions.nzr');
	$openP->readfile();
	$openP->search(array('group_id'=>$users[user]['role']));
	$openP->rekey(array('object'));

if(!empty($nav_pages))
{
	foreach($nav_pages as $nav_page)
	{
		if($nav_page['url']==$name)
		{
			$current_page = $nav_page;
		}
	}
}
$nav_content = '';
if(!empty($nav_categories))
{
	foreach($nav_categories as $nav_category)
	{
		if(array_key_exists($nav_category['first_url'],$openP->content)==false)
		{
			$nav_content .= "<div><a href='".url_build($nav_category['first_url'])."' class='nav_link";
			if($current_page['nav_cat']==$nav_category['id'])
			{
				$nav_content .= "_active";
			}
			$nav_content .= "' title='".no_clear_html($nav_category['name'])."'>".$nav_category['name'];
			if($current_page['nav_cat']==$nav_category['id'])
			{
				$nav_content .= " &raquo; ";
			}
			$nav_content .= "</a></div>";
		}
	}
}
$amountofitemsonnav = 0;
$last_nav = '';
if(!empty($nav_pages))
{
	foreach($nav_pages as $nav_page)
	{
		if($nav_page['nav_cat']==$current_page['nav_cat'])
		{
			if(!empty($nav_page['cloak']))
			{
				//Show no link as it is "Cloaked"
				if($nav_page['url']==$current_page['url'])
				{
					$app['title'] = $nav_page['name'];
					$active_page = $nav_page['cloak'];
				}
			}
			else
			{
				if(array_key_exists($nav_page['url'],$openP->content)==false)
				{
					$html_pages[$nav_page['url']] = "<div><a href='".url_build($nav_page['url'])."' class='nav_link[active-css]' title='".no_clear_html($nav_page['name'])."'>".$nav_page['name']."[active-sign]</a></div>";
					if($nav_page['url']==$current_page['url'])
					{
						$active_page = $nav_page['url'];
						$app['title'] = $nav_page['name'];
					}
					$amountofitemsonnav++;
				}
			}
		}
	}
	if(!empty($html_pages))
	{
		foreach($html_pages as $key=>$html_page)
		{
			if($key!==$active_page)
			{
				$html_page = str_replace('[active-sign]','',$html_page);
				$html_page = str_replace('[active-css]','',$html_page);
			}
			else
			{
				$html_page = str_replace('[active-sign]',' &raquo;',$html_page);
				$html_page = str_replace('[active-css]','_active',$html_page);
			}
			$last_nav .= $html_page;
		}
	}
}
$openP->close();
if(!empty($extra_title))
{
	$app['title'] .= $extra_title;
}
$fico_overwrite_class = '';
$content_overwrite_class = '';
if(!empty($last_nav) && $amountofitemsonnav>1)
{
	$second_nav = "<td class='nav_pages' id='nav_pages'>".$last_nav."</td>";
}
else
{
	$fico_overwrite_class = "_small";
	$content_overwrite_class = "_large";
}

$nav_content = "
<table>
	<tr>
		<td class='nav_sections'>
			".$nav_content."
		</td>
		";
if(!empty($second_nav))
{
	$nav_content .= $second_nav;
}
$nav_content .= "
	</tr>
</table>";
?>