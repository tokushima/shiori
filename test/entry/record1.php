<?php
/**
 * レコードが1件の場合
 */
(new \myapp\model\Bookmark())->set_props(['url'=>'http://localhost/rspec_test'])->save();
\myapp\model\Bookmark::commit();

$b = new \testman\Browser();
$b->do_get('index::index');
eq(200,$b->status());
eq('http://localhost/rspec_test',$b->xml()->find_get('table/tbody/tr/td/a')->value());
mneq('class="pagination"',$b->body());
