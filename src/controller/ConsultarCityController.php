<?php
namespace src\controller;

use src\modal\City;
use src\service\CityService;

class ConsultarCityController extends MainController {

	public function init(): void {
		parent::init();
		
		$this->setData('entitys', City::all());
	}

	public function deletar(City $entity): bool {
		return $entity->delete();
	}

	public function deletarSelecionados(Array $entitys): bool {
		return CityService::deletarCidades($entitys);
	}
}

