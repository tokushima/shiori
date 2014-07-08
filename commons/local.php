<?php
date_default_timezone_set('Asia/Tokyo');


\ebi\Conf::set([
	'ebi.Dao'=>[
		'connection'=>[
			'*'=>['host'=>dirname(__DIR__).'/work/db','dbname'=>'local.db'],
		]
	],
	'ebi.Log'=>[
		'level'=>'warn',
		'file'=>dirname(__DIR__).'/work/shiori.log',
		'stdout'=>true,
	],
	'ebi.Flow'=>[
		'exception_log_level'=>'warn',
	]
]);


