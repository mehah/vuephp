<?php
namespace fw;

final class Project extends Core {

	public static $name = 'VUE_PHP';

	public static $defaultModule = 'home';

	public static $chatset = 'UTF-8';

	public static $checkModification = true;

	private static $liveReload = false;

	public static function initLiveReload() {
		self::$FW_JS_FILES[] = 'fw/js/vue.liveView.js';
		self::$liveReload = true;
	}

	public static function liveReloadEnabled() {
		return self::$liveReload;
	}

	public static function isLocalHost() {
		$whitelist = array(
			'127.0.0.1',
			'::1'
		);
		
		return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
	}
}

