<?php
namespace src\utils;

use DOMDocument;

final class CepUtil {

	public static function recuperarLogradouro(String $cep) {
		// Our new data
		$data = array(
			'relaxation' => $cep,
			'tipoCEP' => 'ALL',
			'semelhante' => 'N'
		);
		// Create a connection
		$url = 'http://www.buscacep.correios.com.br/sistemas/buscacep/resultadoBuscaCepEndereco.cfm';
		$ch = curl_init($url);
		// Form data string
		$postString = http_build_query($data, '', '&');
		// Setting our options
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// Get the response
		$response = curl_exec($ch);
		curl_close($ch);
		
		libxml_use_internal_errors(true);
		$d = new DOMDocument();
		$d->loadHTML($response);
		$table = $d->getElementsByTagName('table')->item(0);
		
		$tr = $table->childNodes->item(3);
		
		$nomeLogradouro = $tr->childNodes->item(1)->textContent;
		$nomeBairro = $tr->childNodes->item(3)->textContent;
		$columnMunUF = explode('/', $tr->childNodes->item(5)->textContent);
		
		$nomeMunicipio = $columnMunUF[0];
		$uf = $columnMunUF[1];
		
		$pos = strpos($uf, '-');
		if ($pos !== false) {
			$uf = trim(substr($uf, 0, pos));
		}
	}
}

