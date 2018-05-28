<?php
include('autoload.php');

/**
 * @see https://github.com/sugamasao/Shiori
 */
\ebi\Flow::app([
	'error_template'=>'error.html',
	'plugins'=>[
		'ebi.flow.plugin.HtmlFilter',
	],
	'patterns'=>[
		''=>[
			'name'=>'index',
			'template'=>'index.html',
			'action'=>function(){
				$paginator = new \ebi\Paginator(10);
				
				return [
					'bookmarks'=>\myapp\model\Bookmark::find_all(\ebi\Q::order('id'),$paginator),
					'paginator'=>$paginator,
				];
			},
		],
		'new'=>[
			'name'=>'new',
			'template'=>'new.html',
		],
		'create'=>[
			'name'=>'create',
			'action'=>'myapp.App::create',
			'after'=>'new',
			'post_after'=>'index',
			'error_template'=>'new.html',
		],
		'dt'=>['action'=>'ebi.Dt'],
	]
]);
