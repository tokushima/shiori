<?php
/**
 * 新規作成
 */
$b = new \chaco\Browser();

//URLに不備がある場合はエラー表示が行われる
$b->do_post(test_map_url('urls::create'));
meq('class="alert alert-danger"',$b->body());


// URLを登録し、一覧画面へ遷移する
$b->vars('url','http://localhost/rspec_test');
$b->do_post(test_map_url('urls::create'));
mneq("class='alert alert-danger'",$b->body());

// URLを登録し、一覧画面へ遷移する
eq(test_map_url('urls::index'),$b->url());
