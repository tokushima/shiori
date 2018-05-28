<?php
/**
 * レコードが11件の場合
 */
for($i=0;$i<11;$i++){
	(new \myapp\model\Bookmark())->set_props(['url'=>'http://localhost/test'.$i])->save();
}
\myapp\model\Bookmark::commit();

$b = new \testman\Browser();
$b->do_get('index::index');
eq(200,$b->status());
meq('class="pagination',$b->body());

$i = 0;
foreach($b->xml()->find('table/tbody/tr') as $tr){
	eq('http://localhost/test'.$i,$tr->find_get('td/a')->in_attr('href'));
	$i++;
}
eq(10,$i);
