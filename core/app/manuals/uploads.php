<?php
$sec = 'uploads';
$extra_title .= 'Uploads';
$headers[$sec] = array("Uploads","Uploads are files from your computer which you can add to your news posts.");

$h_content[$sec][] = array(1,"Introduction: What is an upload?","
	An upload is nothing more than a file you uploaded from your computer to the internet, in this case your NewsOffice installation.<br>
	Uploads can be any files: images, archives, documents, spreadsheets, presentations, etc. <br>
");
$h_content[$sec][] = array(2,"Why use them?","
	When you are writing your news post(s) a picture can mean a thousand words. If you have the possiblity to add a picture in stead of writing a thousand words you rather add the picutre, right?<br>
	<br>
	But it can also be very usefull when you need to share documents and files.<br>
");
$h_content[$sec][] = array(2,"How to use them?","
	In the news post writer there are two buttons which allows you to add your uploads, but you can also upload files without importing them in your news post(s) and that is in the Manager -> Uploads manager.<br>
	<br>
	Then when you want to add an upload manually, it's added automaticly in the news post writer, you just add <span class='important'>[upload/#]</span> to the description or content textarea of your news post. You will have to replace # with the ID of the upload, which can be found in the Manager -> Uploads. So it will be like this [upload/1].<br>
");
$h_content[$sec][] = array(1,"All files: size","
	The file size limit can be different for each website. If you are not sure what the maximum file size is you can upload you should contact your webhosting. The default settings is on 2 MegaByte.
");
$h_content[$sec][] = array(1,"Images: size","
	NewsOffice automaticly shrinks very large images to a \"normal\" view size in your news posts, but keep in mind that the larger an image the more your visitors have to download.
");
?>