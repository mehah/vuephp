<?php
namespace http\HttpSession;


use fw\Core;

class HttpSession {
	private $attr = Array();
	private $controller = Array();
	
	public function __construct() {}
	
	public function getAttribute($index)
	{		
		if(array_key_exists($index, $this->attr) === false)
			return NULL;
		
		return $this->attr[$index];
	}
	
	public function setAttribute($index, $value)
	{
		$this->attr[$index] = $value;
	}
	
	public function destroy()
	{	
		unset($_SESSION[Core::$PROJECT_NAME]);
	}
}