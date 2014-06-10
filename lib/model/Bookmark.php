<?php
namespace model;
/**
 * @var string $url @['require'=>true]
 */
class Bookmark extends \ebi\Dao{
	protected $id;
	protected $url;
	protected $created_at;
	protected $updated_at;
	
	protected function __verify_url__(){
		return (empty($this->url) || preg_match('/^http[s]*:\/\/.+$/',$this->url));
	}
}
