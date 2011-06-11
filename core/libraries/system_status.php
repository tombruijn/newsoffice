<?php
class noSystemStatus
{
	private $dirs = array();
	private $files = array();
	private $errors = array();
	private $failure = '';
	function __construct()
	{
		global $no_config;
		$this->config = $no_config;
		/*
		----------------------------------------------------------------------
				System status global check
				List of system files and directories
				
				$thing = array($name,$location,$self_writable,$sub_files_writable);
		----------------------------------------------------------------------
		*/
		
		$dirs[] = array('Core directory',newsoffice_directory.$this->config['dir_core'],false);
		$dirs[] = array('Core application directory',newsoffice_directory.$this->config['dir_app'],false);
		$dirs[] = array('Core libraries directory',newsoffice_directory.$this->config['dir_lib'],false);
		$dirs[] = array('Scripts libraries directory',newsoffice_directory.$this->config['dir_scripts'],false);
		
		$dirs[] = array('Data directory',newsoffice_directory.$this->config['dir_data'],true,true);
		$dirs[] = array('Info directory',newsoffice_directory.$this->config['dir_info'],true,true);
		
		$dirs[] = array('News files directory',newsoffice_directory.$this->config['dir_news'],true,true);
		$dirs[] = array('News archives directory',newsoffice_directory.$this->config['dir_archives'],true,true);
		
		$dirs[] = array('Comment files directory',newsoffice_directory.$this->config['dir_comments'],true,true);
		$dirs[] = array('Uploads directory',newsoffice_directory.$this->config['dir_uploads'],true,true);
		$dirs[] = array('News page theme files directory',newsoffice_directory.$this->config['dir_themes'],true,true);
		$dirs[] = array('Plugin installations directory',newsoffice_directory.$this->config['dir_plugins'],true,false);
		
		$dirs[] = array('Temporary directory',newsoffice_directory.$this->config['dir_tmp'],true,true);
		$dirs[] = array('.Nzr directory',newsoffice_directory.$this->config['dir_nzr'],true,true);
		
		$dirs[] = array('NewsOffice themes directory',newsoffice_directory.$this->config['dir_no-themes'],true,true);
		$dirs[] = array('NewsOffice themes images directory',newsoffice_directory.$this->config['acp_selected_theme_dir_images'],true,true);
		
		$files[] = array('Main configuration file',newsoffice_directory.'config.php');
		//$files[] = array('Version changelog file',newsoffice_directory.'changelog.php');
		
		$this->dirs = $dirs;
		$this->files = $files;
	}//End function
	
	public function checkup()
	{
		$error = array();
		$status_checked = array();
		foreach($this->dirs as $key=>$dir)
		{
			if($dir[2]==true)
			{
				//if(in_array(str_replace('/','',$dir[1]),$status_checked)==false)
				{
					if(is_writable($dir[1])==false)
					{
						$error['dir'][$key]['main'] = $dir[1];
					}
				}
				if(!empty($dir[3]) && $dir[3]==true)
				{
					$sub_files = glob($dir[1].'*',GLOB_MARK);
					if(!empty($sub_files))
					{
						foreach($sub_files as $sub_file)
						{
							if(in_array(str_replace('/','',$sub_file),$status_checked)==false)
							{
								if(is_writable($sub_file)==false && is_dir($sub_file)==false)
								{
									$error['dir'][$key]['sub'][] = $sub_file;
								}
							}
							$status_checked[] = str_replace('/','',$sub_file);
						}
					}
				}
			}
			$status_checked[] = str_replace('/','',$dir[1]);
		}
		
		foreach($this->files as $key=>$file)
		{
			if(is_writable($file[1])==false)
			{
				$error['file'][$key]['main'] = $file[1];
			}
		}
		$this->errors = $error;
	}//End function
	
	public function result()
	{
		$result = $this->messages();
		return $result;
	}//End function
	
	public function get_errors()
	{
		if(!empty($this->failure))
		{
			$this->failure = "<div class='error'><h2>Errors</h2><table>".$this->failure."</table></div>";
		}
		return $this->failure;
	}//End function
	
	private function messages()
	{
		$error = $this->errors;
		$result = "
		<table>
			<tr>
				<th>Name</th>
				<th>Location</th>
				<th>Status</th>
			</tr>";
			
		foreach($this->files as $key=>$file)
		{
			$result .= "<tr>";
			$this->files[$key][1] = str_replace(newsoffice_directory,'',$this->files[$key][1]);
			if(empty($error['file'][$key]['main']))
			{
				$result .= "<td><ul><li>".$this->files[$key][0]."</li></ul></td><td class='less_important'>".$this->files[$key][1]."</td><td><span class='status_ok'>Correct</span></td>";
			}
			else
			{
				$result .= "<td><ul><li>".$this->files[$key][0]."</li></ul></td><td>".$this->files[$key][1]."</td><td><span class='status_false'>Error 1</span></td>";
				$this->failure .= "<tr><td><ul><li>File <span class='important'>".$this->files[$key][1]."</span> is not writable.</li></ul></td></tr>";
			}
			$result .= "</tr>";
		}
			
		foreach($this->dirs as $key=>$dir)
		{
			$result .= "<tr>";
			$this->dirs[$key][1] = str_replace(newsoffice_directory,'',$this->dirs[$key][1]);
			//Error on folder and subs
			if(!empty($error['dir'][$key]['main']) && !empty($error['dir'][$key]['sub']))
			{
				$result .= "<td><ul><li>".$this->dirs[$key][0]."</li></ul></td><td>".$this->dirs[$key][1]."</td><td><span class='status_false'>Error 2</span></td>";
				$this->failure .= "<tr><td><ul><li>Directory <span class='important'>".$this->dirs[$key][1]."</span> and it's sub-files are not writable</li></ul></td></tr>";
			}
			//Subs false
			elseif(empty($error['dir'][$key]['main']) && !empty($error['dir'][$key]['sub']))
			{
				$result .= "<td><ul><li>".$this->dirs[$key][0]."</li></ul></td><td>".$this->dirs[$key][1]."</td><td><span class='status_false'>Error 3</span></td>";
				$this->failure .= "<tr><td><ul><li>Sub files in the <span class='important'>".$this->dirs[$key][1]."</span> directory are not writable.</li></ul></td></tr>";
			}
			//Folder false
			elseif(!empty($error['dir'][$key]['main']))
			{
				$result .= "<td><ul><li>".$this->dirs[$key][0]."</li></ul></td><td>".$this->dirs[$key][1]."</td><td><span class='status_false'>Error 1</span></td>";
				$this->failure .= "<tr><td><ul><li>Directory <span class='important'>".$this->dirs[$key][1]."</span> is not writable.</li></ul></td></tr>";
			}
			//Everything ok
			else
			{
				$result .= "<td><ul><li>".$this->dirs[$key][0]."</li></ul></td><td class='less_important'>".$this->dirs[$key][1]."</td><td><span class='status_ok'>Correct</span></td>";
			}
			
			//Only show sub files status on error
			if(!empty($error['dir'][$key]['sub']))
			{
				foreach($error['dir'][$key]['sub'] as $sub)
				{
					$this->failure .= "<tr><td><ul><li style='margin-left: 20px;'>Sub file: <span class='important'>".$sub."</span> is not writable.</td></tr>";
				}
			}
			$result .= "</tr>";
		}
		$result .= "</table>";
		return $result;
	}//End function
}//End class
?>