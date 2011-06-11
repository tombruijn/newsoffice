<?php
$sec = 'categories';
$extra_title .= 'Categories';
$headers[$sec] = array("Using categories","Get a better understanding of how to use categories to your advantage.");

$h_content[$sec][] = array(1,"What are categories?","
	Categories are subjects for your news posts and you can add your news posts to these.<br>
	<br>
	For example: When you write about your new car, you add it to the automobiles category.<br>
	<br>
	Of course you can create your own categories and name them yourself. Like this you can manage your news page better by only showing one particular category.
");
$h_content[$sec][] = array(1,"How to use them","
	You can manage/edit/create categories in the <a href='".url_build('manager-category')."'>Category manager</a>.<br>
	Clicking on the <span class='important'>New</span> link will bring you to the news post editor which enables you to create new categories. After you created a category you can use them in the news manager to add news posts to this category.<br>
	<br>
	On the news post editor there is a special section for categories. In this section you can \"tick\" the checkbox(es) for the category/categories you want to add your news post too. Then, after you save your news post, your news will show up in this category.
");
$h_content[$sec][] = array(2,"Adding multiple categories to a news post","
	You can add a news post to multiple categories and this is easy to do. In the news post editor you can \"tick\" multiple category checkboxes. When you do this and save the news post, the news post is added to more than one category. It's that easy!
");
?>