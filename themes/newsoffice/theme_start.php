<?php
echo "<!-- Start layout -->
<div class='page_main'>
	<!-- Start header -->
		<div class='page_header' style=\"background-image: url('".$no_config['acp_url'].$no_config['acp_selected_theme_dir_images']."logo-newsoffice.gif'); background-repeat: no-repeat;\"></div>
		<div class='page_border'></div>
	<!-- End header -->
	<div class='page_content'>
		<div class='page_fico".$fico_overwrite_class."'>
			<div class='fico_nav'>
				".$nav_content."
			</div>
		</div>
		<div class='content_content".$content_overwrite_class."' id='content_content'>
";
?>