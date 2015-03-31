<?php
/**
 * レコードが11件の場合
 */
for($i=0;$i<11;$i++){
	(new \model\Bookmark())->set_props(['url'=>'http://localhost/rspec_test'])->save();
}
\model\Bookmark::commit();

$b = new \testman\Browser();
$b->do_get(url('index::index'));
eq(200,$b->status());
meq('class="pagination"',$b->body());

$i = 0;
foreach($b->xml()->find('table/tbody/tr') as $tr){
	eq('http://localhost/rspec_test',$tr->find_get('td/a')->in_attr('href'));
	$i++;
}
eq(10,$i);
