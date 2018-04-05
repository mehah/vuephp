<?php
namespace fw\http;

use fw\Core;
use fw\UserPrincipal;

class HttpSession {

	private $userPrincipal;

	private $attr = Array();

	public function __construct() {
	}

	public function getAttribute($index) {
		return $this->attr[$index] ?? null;
	}

	public function setAttribute($index,$value) {
		$this->attr[$index] = $value;
	}

	public function destroy() {
		unset($_SESSION[Core::$PROJECT_NAME]);
	}

	public function setUserPrincipal(UserPrincipal $user) {
		if ($this->userPrincipal != null)
			throw new \RuntimeException("Não é possivel mudar o usuário, só quando não houver nenhum vinculado a sessão.");
		
		$this->userPrincipal = $user;
	}

	public function getUserPrincipal(): ?UserPrincipal {
		return $this->userPrincipal;
	}
}