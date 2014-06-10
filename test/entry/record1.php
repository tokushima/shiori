<?php
/**
 * レコードが1件の場合
 */
(new \model\Bookmark())->set_props(['url'=>'http://localhost/rspec_test'])->save();
\model\Bookmark::commit();

$b = new \testman\Browser();
$b->do_get(test_map_url('urls::index'));
eq(200,$b->status());
eq('http://localhost/rspec_test',$b->xml()->find_get('table/tbody/tr/td/a')->value());
mneq('class="pagination"',$b->body());
