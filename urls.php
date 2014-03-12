<?php
include_once('bootstrap.php');
/**
 * @see https://github.com/sugamasao/Shiori
 */
(new \rhaco\Flow())->execute([
	'error_template'=>'error.html',
	'plugins'=>[
		'\rhaco\flow\plugin\HtmlFilter',
		'\rhaco\flow\plugin\TwitterBootstrap3Helper',
	],
	'patterns'=>[
		''=>[
			'name'=>'index',
			'template'=>'index.html',
			'action'=>function(){
				$paginator = new \rhaco\Paginator(10);
				
				return [
					'bookmarks'=>\model\Bookmark::find_all(\rhaco\Q::order('id'),$paginator),
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
				$req = new \rhaco\Request();
				
				if($req->is_post()){
					(new \model\Bookmark())->url($req->in_vars('url'))->save();
				}
			},
			'after'=>'new',
			'post_after'=>'index',
			'error_template'=>'new.html',
		],
		'dt'=>['action'=>'rhaco.Dt'],
	]
]);
