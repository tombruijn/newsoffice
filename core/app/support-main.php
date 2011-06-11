<?php
$page_content = "<h1>NewsOffice support</h1>
Welcome to the NewsOffice support section.<br>
NewsOffice is created by Newanz and here you can get support.<br>
<br>
Look at the <a href='".url_build('system-status')."'>System status</a> to see if your installation of NewsOffice is working properly.<br>
See if you have the latest NewsOffice version by using the <a href='".url_build('updater')."'>Updater</a>.<br>
<br>
<h2>Need help?</h2>
Need help figuring out how NewsOffice works?<br>
Go to the <a href='".url_build('manuals-main')."'>Manuals</a> and read how it works.<br>
<br>

<h2>Support</h2>
If you are having trouble with your NewsOffice installation, please visit the Newanz website for help.
<ul>
	<li><a href='".$_SESSION[install_id]['updater']['info_current']['app-link-site']."'>NewsOffice application website</a></li>
	<li><a href='".$_SESSION[install_id]['updater']['info_current']['app-link-project']."'>NewsOffice Project file</a></li>
	<li><a href='".$_SESSION[install_id]['updater']['info_current']['app-link-support']."'>NewsOffice Support forums</a></li>
</ul>
";
?>