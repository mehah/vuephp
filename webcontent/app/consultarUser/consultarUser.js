App("#app");

this.data.usersCheckeds = [];

this.methods.selecionarTodos = function(e) {
	if(!e.target.checked) {
		this.usersCheckeds = [];
	} else {
		for(var i in this.users) {
			this.usersCheckeds.push(this.users[i]);	
		}	
	}
};

this.methods.remover= function(user) {
	var currentApp = this;
	Modal.messageConfirm("Tem certeza que deseja remover o usuário '"+user.name+"'?", function() {
		currentApp.$deletar(user, function(data) {
			Modal.message(data[1]);
			if(data[0]) {
				this.users = this.users.filter(function (item) {
				    return user.id !== item.id;
				});
			}
		});
	});
};

this.methods.deletarTodos = function() {
	if(this.usersCheckeds.length === 0) {
		Modal.message('Selecione pelo menos um usuário.');
		return;
	}
	
	var currentApp = this;
	Modal.messageConfirm("Tem certeza que deseja remover todos registros selecionados?", function() {
		currentApp.$deletarSelecionados(currentApp.usersCheckeds, function(data) {
			Modal.message(data[1]);
			if(data[0]) {
				var usersCheckeds = this.usersCheckeds;
				
				for(var i in this.usersCheckeds) {
					Vue.delete(this.users, this.users.indexOf(this.usersCheckeds[i]));
				}
				
				this.usersCheckeds = [];
			}
		});
	});
};
