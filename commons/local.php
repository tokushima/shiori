<?php
\ebi\Conf::set([
	'ebi.Dao'=>[
		'connection'=>[
			'*'=>[
				'type'=>\ebi\SqliteConnector::class,
				'name'=>'db.sqlite3',
				'host'=>dirname(__DIR__).'/work/'
			],
		]
	]
]);

