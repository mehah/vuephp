<?php
namespace src\dao;

use src\modal\City;

class CityDAO {

	public static function deletarCidadesPorId(Array $ids): bool {
		return City::deleteWithFilter(Array(
			"id" => $ids
		));
	}
}

