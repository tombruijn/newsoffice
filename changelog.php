<?php
//Security feature
$var = newsoffice_directory;
if(!empty($var))
{
	exit('Sorry, you are not allowed to read the changelog of this installation.');
}
?>NewsOffice 2.0.20 Beta; Tiny maintenance release.
Released on: 2010-09-18 (yyyy-mm-dd).
Report any bugs you encounter at <a href='http://newanz.com/contact/'>Newanz.com</a>

BUGS:
- Cross site scripting fix.

Changed files:
- index.php
- core/functions-global.php