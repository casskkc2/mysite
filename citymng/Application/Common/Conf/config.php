<?php
return array(
	'DB_TYPE'   => 'mysql', 
	'DB_HOST'   => 'localhost',
	'DB_NAME'   => 'cmdb',
	'DB_USER'   => 'root',
	'DB_PWD'    => '', 
	'DB_PORT'   => 3306,
	'DB_PREFIX' => '',
	
	'TMPL_ACTION_ERROR' 	=> 'Home@Common:error',
	'TMPL_ACTION_SUCCESS'	=> 'Home@Common:error',
	
	'ALLOW_IMG_TYPE' => 'jpg|gif|png|jpeg',
	'UPLOAD_DIR' => 'upload/',
	'DOWNLOAD_DIR' => 'download/', // for image zip
	
	//'HTTP_SERVER' => 'http://' . $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT']=='80' ? '' : ':' . $_SERVER['SERVER_PORT']) . '/citymng/trunk/citymng/',
	'HTTP_SERVER' => 'http://' . $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT']=='80' ? '' : ':' . $_SERVER['SERVER_PORT']) . '/',
	
	'CONFIG_SETTING' => array(
		'config_exp_days'			=> array('key'=>'config_exp_days', 'title'=>'过期天数', 'suffix'=>'天'),
		'config_change_pwd_alert'	=> array('key'=>'config_change_pwd_alert', 'title'=>'是否提示修改密码', 'suffix'=>'0 不提示，1 提示'),
		'config_change_pwd_interval'=> array('key'=>'config_change_pwd_interval', 'title'=>'提示修改密码的间隔', 'suffix'=>'天'),
	),
);