<?php
include_once('bootstrap.php');
/**
 * @see https://github.com/sugamasao/Shiori
 */
(new \ebi\Flow())->execute([
	'error_template'=>'error.html',
	'plugins'=>[
		'\ebi\flow\plugin\HtmlFilter',
		'\ebi\flow\plugin\TwitterBootstrap3Helper',
	],
	'patterns'=>[
		''=>[
			'name'=>'index',
			'template'=>'index.html',
			'action'=>function(){
				$paginator = new \ebi\Paginator(10);
				
				return [
					'bookmarks'=>\model\Bookmark::find_all(\ebi\Q::order('id'),$paginator),
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
			'action'=>function(){
				$req = new \ebi\Request();
				
				if($req->is_post()){
					(new \model\Bookmark())->url($req->in_vars('url'))->save();
				}
			},
			'after'=>'new',
			'post_after'=>'index',
			'error_template'=>'new.html',
		],
		'dt'=>['action'=>'ebi.Dt'],
	]
]);
