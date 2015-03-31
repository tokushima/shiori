<?php

try{
	(new \model\Bookmark())->set_props(['url'=>''])->save();
	fail('URLの値が空の場合は例外が発生する');	
}catch(\Exception $e){
}

try{
	(new \model\Bookmark())->save();
	fail('URLの値がnullの場合は例外が発生する');
}catch(\ebi\Exceptions $e){
}

try{
	(new \model\Bookmark())->set_props(['url'=>'example.com'])->save();
	fail('URLの値がhttps等が存在しない場合は例外が発生する');
}catch(\ebi\Exceptions $e){
}

$cnt = \model\Bookmark::find_count();
(new \model\Bookmark())->set_props(['url'=>'http://example.com'])->save();
eq($cnt+1,\model\Bookmark::find_count(),'URLの値がhttpから始まっている場合は保存できる');

$cnt = \model\Bookmark::find_count();
(new \model\Bookmark())->set_props(['url'=>'https://example.com'])->save();
eq($cnt+1,\model\Bookmark::find_count(),'URLの値がhttpsから始まっている場合は保存できる');
