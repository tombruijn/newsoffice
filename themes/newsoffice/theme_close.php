<?php
echo "
		</div>
	</div>
	<!-- End content table -->
	<!-- Footer fix -->
		<div id='page_fix'></div>
</div>

<!-- Start footer -->
<div class='page_footer'>
	<div class='footer_content'>
";
//Show updater message
if($_SESSION[install_id]['updater']['result']['result']=='new' && $_SESSION[install_id]['updater']['result']['inform']==true && (($_SESSION[install_id]['updater']['result']['type']!=='final' && $no_config['set_experiment']=='true') || $_SESSION[install_id]['updater']['result']['type']=='final'))
{
	echo "<div style='float: left;'>Get ".$_SESSION[install_id]['updater']['info_latest']['app-name']." <a href='".$_SESSION[install_id]['updater']['info_latest']['app-link-download']."'>".$_SESSION[install_id]['updater']['info_latest']['app-version']." ".ucfirst($_SESSION[install_id]['updater']['info_latest']['app-type'])."</a>!</div>";
}
elseif($_SESSION[install_id]['updater']['result']['black']==true)
{
	echo "<div style='float: left;'>Development mode</div>";

}
echo "
		<!-- Do not remove the Powered by and Copyright lines! -->
		<div>Powered by <a href='".$_SESSION[install_id]['updater']['info_latest']['app-link-site']."'>".app_version_name."</a>";
if(no_is_allowed('updater')==true)
{
	echo " ".app_version_number;
}
echo ". Copyright © ".date('Y')." <a href='http://newanz.com/'>Newanz</a>.</div>
	</div>
</div>
<!-- End footer -->

<!-- End layout -->";
?>