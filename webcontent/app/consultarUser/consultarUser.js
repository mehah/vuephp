this.el = "#content";

this.data.usersCheckeds = []

this.methods.selecionarTodos = function(e) {
	if(!e.target.checked) {
		this.usersCheckeds = [];
	} else {
		for(i in this.users) {
			this.usersCheckeds.push(this.users[i]);	
		}	
	}
};

this.methods.deletarTodos = function() {
};
