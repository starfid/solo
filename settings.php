<?php

	$setting = array(
		'database' 	=> array(
			'rdbms'		=> 'mysql',
			'host'		=> '127.0.0.1',
			'port'		=> '3306',
			'username'	=> 'root',
			'password'	=> 'your_db_password',
			'name'		=> 'your_db_name',
		),
		'personal'	=> array(
			'token'		=> 'your_secret_word',
			'expired'	=> 30,
			'label'		=> 'library',
			'desc'		=> 'This app is bla bla bla',
			'keyword'	=> 'app,internet,library',
			'support'	=> 'support@example.com',
		),
		'folder'	=> array(
			'system'	=> 'system',
			'apps'		=> 'apps',
			'cache'		=> 'cache'
		),
		'priv'		=> array(
			'admin'		=> array('admin','services'),
			'operator'	=> array('operator','services'),
			'member'	=> array('member','services'),
			'guest'		=> array('guest','services')
		),
		'timezone'	=> 'Asia/Jakarta'
	);