this.el = "#content";

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
	this.showCofirmModal("Tem certeza que deseja remover o usuário '"+user.name+"'?", function() {
		this.deletar(user, function(data) {
			if(data) {
				this.users = this.users.filter(function (item) {
				    return user.id !== item.id;
				});
			}
		});
	});
};

this.methods.deletarTodos = function() {
	this.showCofirmModal("Tem certeza que deseja remover todos registros selecionados?", function() {
		this.deletarSelecionados(this.usersCheckeds, function(data) {
			if(data) {
				var usersCheckeds = this.usersCheckeds;
				
				for(var i in this.usersCheckeds) {
					Vue.delete(this.users, this.users.indexOf(this.usersCheckeds[i]));
				}
				
				this.usersCheckeds = [];
			}
		});
	});
};
