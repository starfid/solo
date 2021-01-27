<?php

	session_start();

	require('settings.php');
	date_default_timezone_set($setting['timezone']);

	require($setting['folder']['system'].'/interface.php');
	require($setting['folder']['system'].'/controller.php');

	$get = new Controller($setting);
	$get->currentApp();

	require($setting['folder']['apps'].'/'.$get->current['appType'].'/'.$get->current['group'].'/'.$get->current['app'].'.php');
	$get->availMethods();

	require($setting['folder']['system'].'/model.php');
	$data = new Model($setting['database'],$get->current,$setting['personal']['token']);

	$get->loginSubmitted && $get->authentication($data->result);
	$get->current['method']=='link' && $data->result['count']==1 && $get->linkMethod($data->result);

	require($setting['folder']['system'].'/view.php');
	unset($setting['database'],$setting['priv']);
	new View($setting,$get->current,$data->result);
