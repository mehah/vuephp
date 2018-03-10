<?php

namespace src\controller;

use dao\UserDAO;
use modal\User;
use fw\VueApp;

class ManterUserController {	
	public function init(int $id = null) {
	    $user = new User();
	    
	    if($id) {	        
	        $user->id = $id;	        
	        UserDAO::load($user);
	    }
	    
	    $data = VueApp::getData();
	    $data->id = $id;
	    $data->user = $user;
	    
	    return $data;
	}
	
	public function inserir(User $user) {
	    UserDAO::inset($user);
	}
	
	public function alterar(User $user) {
		UserDAO::update($user);
	}
	
	public function deletar(User $user) {
	    UserDAO::update($user);
	}
}

