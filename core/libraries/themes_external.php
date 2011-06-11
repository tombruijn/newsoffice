<?php
class noThemeExternal
{
	private $allowed_content_types = array('main','error');
	private $title = 'NewsOffice';
	private $width = '320';
	private $logo = false;
	private $logo_message = '';
	private $onload = '';
	private $content = array();
	
	function __construct()
	{
		global $no_config;
		$this->config = $no_config;
	}//End function
	
	public function set_title($string,$mode=true)
	{
		$this->title = $string;
		if($mode==true)
		{
			$this->title .= ' | ';
		}
		else
		{
			$this->title .= ' ';
		}
		$this->title .= 'NewsOffice';
	}//End function
	
	public function set_width_large($width='')
	{
		if(empty($width))
		{
			$width = 450;
		}
		$this->width = $width;
	}//End function
	
	public function set_logo()
	{
		$this->logo = true;
	}//End function
	public function set_logo_message($string)
	{
		$this->logo_message = $string;
	}//End function
	
	public function set_onload($string)
	{
		$this->onload = $string;
	}//End function
	
	public function set_content($object)
	{
		if(count($this->content)<=0)
		{
			$string .= "
			<div class='logo' title='".no_clear_html($this->title)."'>
				<div style='float: right;'>".$this->logo_message."</div>
			</div>";
		}
		$string .= $object;
		$this->content[] = $string;
	}//End function
	
	public function show()
	{
		if(defined('newsoffice_copyright')==true)
		{
			echo newsoffice_copyright;
		}
		echo $this->theme_start();
		if(!empty($this->content))
		{
			foreach($this->content as $content)
			{
				echo $content;
			}
		}
		echo $this->theme_end();
	}//End function

	private function theme_start()
	{
		$string .= "
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
	<html>
		<head>
			<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
			<title>".$this->title."</title>
			<link rel='shortcut icon' href='".$this->config['acp_selected_theme_dir'].$this->config['dir_images']."newsoffice.ico'>
			<style type='text/css'>
			html,
			body
			{
				margin: 0 auto;
				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				text-align: center;
				
				background-color: #E9E9E9;
			}
			
			form
			{
				margin: 0px;
			}

			a:link,
			a:active,
			a:visited
			{
				color: #444444;
			}

			a:hover
			{
				color: #FFAE00;
				text-decoration: none;
			}
			
			h1
			{
				margin: 2px 0px 2px 0px;
				font-size: 16px;
				font-weight: bold;
			}
			
			h2
			{
				margin: 2px 0px 2px 0px;
				font-size: 12px;
				font-weight: bold;
			}
			.newsoffice_red
			{
				color: #FF0000;
				font-weight: bold;
			}
			
			.main,
			.logo,
			div.important
			{
				width: ".$this->width."px;
				margin: 0 auto;
				margin-top: 20px;
				padding: 10px;

				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				text-align: left;

				background-color: #FFFFFF;
				border: 1px solid #CCCCCC;
			}
			
			.logo
			{
				position: relative;
				top: 30px;
				margin: 0 auto;
				
				height: 70px;
				text-align: center;
				background-image: url(".$this->config['acp_url'].$this->config['acp_selected_theme_dir_images']."login_logo_newsoffice.gif);
				background-repeat: no-repeat;
				background-position: center center;
				border-bottom: 0px;
			}

			.error
			{
				width: ".$this->width."px;
				margin: 0 auto;
				margin-top: 20px;
				padding: 10px;

				color: #FFFFFF;
				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				font-weight: bold;
				text-align: left;

				background-color: #FF7700;
				border: 1px solid #560000;
			}
			
			.login-box
			{
				margin-bottom: 3px;
				padding: 2px;
				width: 100%;
				border: 1px solid #CCCCCC;
			}
			
			.important
			{
				font-weight: bold;
			}
			
			div.important
			{
				background-color: #C6F6A1;
				border: 1px solid #666666;
				border-style: dashed;
			}
			
			.less_important
			{
				color: #666666;
				font-size: 10px;
			}
			
			ul,
			ol
			{
				margin: 0px;
				margin-left: 10px;
				padding: 0px;
				padding-left: 10px;

				list-style-type: square;
			}
			
			textarea
			{
				width: 100%;
				height: 400px;
			}
			.text
			{
				width: 100%;
			}
			table
			{
				width: 100%;
				font-size: 100%;
			}
			
			th
			{
				font-weight: bold;
				font-size: 14px;
				text-align: left;
			}
			td
			{
				vertical-align: top;
			}
			td.subject
			{
				width: 120px;
			}
			
			.status_ok
			{
				color: #28C600;
				font-weight: bold;
			}
			.status_false
			{
				color: #FF5500;
				font-weight: bold;
			}
			</style>
		</head>
	<body";
		if(!empty($this->onload))
		{
			$string .= ' '.$this->onload;
		}
		$string .= ">";
		return $string;
	}//End function
	
	private function theme_end()
	{
		$string .= "
	<br>
	<div style='text-align: center; padding-bottom: 10px;'>
		Powered by <a href='http://newsoffice.newanz.com/' title='Go to the NewsOffice official site.'>NewsOffice</a>.
	</div>
</body>
</html>";
		return $string;
	}//End function
}//End class
?>