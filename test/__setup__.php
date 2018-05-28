<?php
// DBのデータをテスト毎に空にしておく
\myapp\model\Bookmark::find_delete();
\myapp\model\Bookmark::commit();
