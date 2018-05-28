<?php
namespace myapp;

class App extends \ebi\flow\Request{
	/**
	 * @http_method post
	 * @request string $url @['trquire'=>true]
	 */
	public function create(){
		(new \myapp\model\Bookmark())->url($this->in_vars('url'))->save();
	}
}