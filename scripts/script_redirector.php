<?php
if(defined('newsoffice_mode')==false)
{
	define('newsoffice_mode','script'); //Required to "hack" into the load mechanic of NewsOffice.
}
include('../core/clean_boot.php'); //Send request for basic information without loading the requested page all over again.
?>