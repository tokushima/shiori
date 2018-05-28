<?php

try{
	(new \myapp\model\Bookmark())->set_props(['url'=>''])->save();
	fail('URLの値が空の場合は例外が発生する');	
}catch(\ebi\Exceptions $e){
}

try{
	(new \myapp\model\Bookmark())->save();
	fail('URLの値がnullの場合は例外が発生する');
}catch(\ebi\Exceptions $e){
}

try{
	(new \myapp\model\Bookmark())->set_props(['url'=>'example.com'])->save();
	fail('URLの値がhttps等が存在しない場合は例外が発生する');
}catch(\ebi\Exceptions $e){
}

$cnt = \myapp\model\Bookmark::find_count();
(new \myapp\model\Bookmark())->set_props(['url'=>'http://example.com'])->save();
eq($cnt+1,\myapp\model\Bookmark::find_count(),'URLの値がhttpから始まっている場合は保存できる');

$cnt = \myapp\model\Bookmark::find_count();
(new \myapp\model\Bookmark())->set_props(['url'=>'https://example.com'])->save();
eq($cnt+1,\myapp\model\Bookmark::find_count(),'URLの値がhttpsから始まっている場合は保存できる');
