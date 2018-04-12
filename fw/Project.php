<?php
namespace fw;

final class Project extends Core {

	public static $name = 'VUE_PHP';

	public static $defaultModule = 'home';
	
	public static $chatset = 'UTF-8';
	
	private static $liveView = false;
	
	public static function initLiveView() {
		self::$JS_FILES[] = 'fw/js/vue.liveView.js';
		self::$liveView = true;
	}
	
	public static function liveViewEnabled() {
		return self::$liveView;
	}

	public static function importJavascriptPlugin(String $path): void {
		self::$JS_FILES[] = 'public_html/'.$path;
	}
	
	public static function registerJavascript(String $path): void {
		self::$JS_FILES[] = ':public_html/'.$path;
	}
	
	public static function importCSS(String $path): void {
		self::$CSS_FILES[] = 'public_html/'.$path;
	}
}

