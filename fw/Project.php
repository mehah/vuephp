<?php
namespace fw;

final class Project extends Core {

	public static $name = 'VUE_PHP';

	public static $defaultModule = 'home';
	
	public static $chatset = 'UTF-8';

	public static function importJavascriptPlugin(String $path): void {
		array_push(self::$JS_FILES, 'public_html/'.$path);
	}
}

