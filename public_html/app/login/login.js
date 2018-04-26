App("#app");

this.methods.entrar = function() {
	this.$entrar(this.login, function(data) {
		if (this.logged) {
			this.redirect('/');
		} else {
			Modal.message('Usuário ou senha invalido(s).');
		}
	});
};