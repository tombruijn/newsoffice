<?php
include('../config.php');
if($app_url==$_GET['app_url']) { exit(); } //Anti register globals
header('Location: '.$app_url.'');
?>