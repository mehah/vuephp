<?php
namespace src\service;

use src\dao\CityDAO;

class CityService {

	public static function deletarCidades(Array $cidades): bool {
		$ids = array_map(function ($o) {
			return $o->id;
		}, $cidades);
		
		return CityDAO::deletarCidadesPorId($ids);
	}
}