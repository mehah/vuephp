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
	this.deletar(user, function() {
		this.users = this.users.filter(function (item) {
		    return user.id !== item.id;
		});
	});
};

this.methods.deletarTodos = function() {
	this.deletarSelecionados(this.usersCheckeds, function() {
		var usersCheckeds = this.usersCheckeds;
		
		for(var i in this.usersCheckeds) {
			Vue.delete(this.users, this.users.indexOf(this.usersCheckeds[i]));
		}
		
		this.usersCheckeds = [];
	});
};
