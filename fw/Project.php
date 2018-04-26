<?php
namespace fw;

final class Project extends Core {

	public static $name = 'VUE_PHP';
	
	public static $chatset = 'UTF-8';

	public static $checkModification = true;

	private static $liveReload = false;

	public static function initLiveReload() {
		if(!self::$checkModification) {
			throw new \Exception('Não é possível inicializar LiveReload, pois a checagem de modificações se encontra desabilitada.');
		}
		
		self::$FW_JS_FILES[] = 'fw/js/vue.liveReload.js';
		self::$liveReload = true;
	}

	public static function liveReloadEnabled() {
		return self::$checkModification && self::$liveReload;
	}

	public static function isLocalHost() {
		$whitelist = array(
			'127.0.0.1',
			'::1'
		);
		
		return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
	}
}

