<?php
namespace fw;

final class Project extends Core {

	private static $name = 'VUE_PHP';

	private static $chatset = 'UTF-8';

	private static $checkModification = true;

	private static $liveReload = false;

	public static function setLiveReload($liveReload) {
		Project::$liveReload = $liveReload;
	}

	public static function initLiveReload() {
		if (! self::$checkModification) {
			throw new \Exception('Não é possível inicializar LiveReload, pois a checagem de modificações se encontra desabilitada.');
		}
		
		self::$FW_JS_FILES[] = 'fw/js/vue.liveReload.js';
		self::$liveReload = true;
	}

	public static function liveReloadEnabled() {
		return self::$checkModification && self::$liveReload;
	}

	public static function getName(): string {
		return Project::$name;
	}

	public static function getChatset(): string {
		return Project::$chatset;
	}

	public static function getCheckModification(): bool {
		return Project::$checkModification;
	}

	public static function setName(string $name): void {
		Project::$name = $name;
	}

	public static function setChatset(string $chatset): void {
		Project::$chatset = $chatset;
	}

	public static function setCheckModification(bool $checkModification): void {
		Project::$checkModification = $checkModification;
	}

	public static function isLocalHost() {
		$whitelist = array(
			'127.0.0.1',
			'::1'
		);
		
		return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
	}

	public static function getContextPath(): String {
		return self::$CONTEXT_PATH;
	}
	
	public static function registerReponseCode(int $code, string $path): void {
		parent::$pageCodeResponse[$code] = $path;
	}
}

