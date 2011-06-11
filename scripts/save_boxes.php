<?php
include('script_redirector.php');
$object_id = no_clear_url($_GET['box']);
$object_type = no_clear_url($_GET['type']);
$object_value = no_clear_url($_GET['value']);

if($object_type=='sidebar')
{
	$allowed_options = array('on','off');
	if(in_array($object_value,$allowed_options)==true)
	{
		$saveBox = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-settings.nzr');
			$saveBox->readfile();
			//Delete previous records
			$saveBox->delete(
				array(
					'user_id'=>$_SESSION[install_id]['user']['id'],
					'object'=>$object_type.'_'.$object_id
				)
			);
			//Save new value
			$saveBox->save(
				array(
					'user_id'=>$_SESSION[install_id]['user']['id'],
					'object'=>$object_type.'_'.$object_id,
					'value'=>'box='.$object_value
				),
				'new'
			);
		$saveBox->close();
	}
}
elseif($object_type=='advanced_editor')
{
	$allowed_options = array('on','off');
	if(in_array($object_value,$allowed_options)==true)
	{
		$saveBox = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-settings.nzr');
			$saveBox->readfile();
			//Delete previous records
			$saveBox->delete(
				array(
					'user_id'=>$_SESSION[install_id]['user']['id'],
					'object'=>$object_type.'_'.$object_id
				)
			);
		$saveBox->close();
		$saveBox = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-settings.nzr');
			$saveBox->readfile();
			//Save new value
			$saveBox->save(
				array(
					'user_id'=>$_SESSION[install_id]['user']['id'],
					'object'=>$object_type.'_'.$object_id,
					'value'=>'box='.$object_value
				),
				'new'
			);
		$saveBox->close();
	}
}
elseif($object_type=='reset')
{
	unset($_SESSION[install_id]['important_messages']);
	unset($_SESSION[install_id]['messages']);
	echo "reset";
	$saveBox = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-settings.nzr');
		$saveBox->readfile();
		$saveBox->delete(array('user_id'=>$_SESSION[install_id]['user']['id'],'value'=>'box=off')); //Delete all registered boxes from file
	$saveBox->close();
}
else
{
	//For 2 types different session key: important_messages/messages
	unset($session_key);
	if($object_type=='important_message')
	{
		$session_key = 'important_messages';
	}
	elseif($object_type=='message')
	{
		$session_key = 'messages';
	}

	if(!empty($session_key))
	{
		if($object_type=='important_message')
		{
			$_SESSION[install_id][$session_key]['status'][$object_id] = 'off';

			if(!empty($_SESSION[install_id][$session_key]['registered']))
			{
				$found_key = array_search($object,$_SESSION[install_id][$session_key]['registered']);
				unset($_SESSION[install_id][$session_key]['registered'][$found_key]);
			}

			//Important messages only; hide the container
			if(count($_SESSION[install_id][$session_key]['registered'])<=0)
			{
				echo "no-others";
			}
		}
		elseif($object_type=='message')
		{
			$saveBox = new newanz_nzr(newsoffice_directory.$no_config['dir_info'].'users-settings.nzr');
				$saveBox->readfile();
				$saveBox->save(
					array(
						'user_id'=>$_SESSION[install_id]['user']['id'],
						'object'=>$session_key.'_'.$object_id,
						'value'=>'box=off'
					),
					'new'
				);
			$saveBox->close();
		}
	}
}
?>