<?php
date_default_timezone_set('Asia/Tokyo');


\rhaco\Conf::set([
	'rhaco.Dao'=>[
		'connection'=>[
			'*'=>['host'=>dirname(__DIR__),'dbname'=>'local.db'],
		]
	],
	'rhaco.Log'=>[
		'level'=>'warn',
		'file'=>dirname(__DIR__).'/work/rhaco.log',
		'stdout'=>true,
	],
	'rhaco.Flow'=>[
		'exception_log_level'=>'warn',
//		'app_url'=>'http://localhost:8080',
	]
]);


