<?php
// DBのデータをテスト毎に空にしておく
\model\Bookmark::find_delete();
\model\Bookmark::commit();
